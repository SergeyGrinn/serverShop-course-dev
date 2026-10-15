<?php

/**
 * Data-access model for shopping carts and their items, keyed by PHP session.
 */
class Cart {
    private $pdo;

    /**
     * Stores the database connection used by all cart queries.
     *
     * @param PDO $pdo Active database connection.
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Returns the cart bound to the session, creating it so every visitor always has one.
     *
     * @param string $session_id Current PHP session id.
     * @return array Cart row (at least id and session_id).
     */
    public function getOrCreateCart($session_id){ // Get existing cart for session or create new one
        $stmt = $this->pdo->prepare("SELECT * FROM carts WHERE session_id = :session_id"); // Try to find cart for current session
        $stmt->execute([':session_id' => $session_id]); // If cart exists, return it. Otherwise, create new cart and return it
        $cart = $stmt->fetch();
        if (!$cart) { // If no cart found, create new one
            $stmt = $this->pdo->prepare("INSERT INTO carts (session_id) VALUES (:session_id)");
            $stmt->execute([':session_id' => $session_id]);
            $cart = ['id' => $this->pdo->lastInsertId(), 'session_id' => $session_id]; 
        }
        return $cart;
    }

    /**
     * Loads cart items with server info and the details of each chosen component.
     *
     * @param int $cart_id Cart identifier.
     * @return array List of items, each with a 'components' array.
     */
    public function getItems($cart_id) {
    $stmt = $this->pdo->prepare("
        SELECT ci.*, s.name, s.image
        FROM cart_items ci
        JOIN servers s ON ci.server_id = s.id
        WHERE ci.cart_id = :cart_id
    ");
    $stmt->execute([':cart_id' => $cart_id]);
    $items = $stmt->fetchAll();

    foreach ($items as &$item) {
        $ids = json_decode($item['component_ids'], true);
        if (!empty($ids)) {
            // One "?" per id keeps the IN() list parameterized (no SQL injection)
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt2 = $this->pdo->prepare("
                SELECT name, value, type, price
                FROM components
                WHERE id IN ($placeholders)
            ");
            $stmt2->execute($ids);
            $item['components'] = $stmt2->fetchAll();
        } else {
            $item['components'] = [];
        }
    }

    return $items;
}

    /**
     * Validates a configuration and recalculates its price on the server,
     * so prices sent by the browser are never trusted.
     *
     * @param mixed $serverId Server id from the request.
     * @param array $componentIds Selected component ids from the request.
     * @return array ['component_ids' => int[], 'total_price' => float].
     * @throws InvalidArgumentException If the server/components are invalid, unavailable or duplicated by type.
     */
    public function calculateConfiguredItem($serverId, array $componentIds): array {
        $serverId = filter_var($serverId, FILTER_VALIDATE_INT);
        if ($serverId === false || $serverId < 1) {
            throw new InvalidArgumentException('Invalid server selected');
        }

        $serverStmt = $this->pdo->prepare('SELECT base_price FROM servers WHERE id = :id AND available = TRUE');
        $serverStmt->execute([':id' => $serverId]);
        $server = $serverStmt->fetch();
        if (!$server) {
            throw new InvalidArgumentException('Server is unavailable');
        }

        // Cast every id to a positive int and reject duplicates
        $selectedIds = [];
        foreach ($componentIds as $componentId) {
            if ($componentId === null || $componentId === '') {
                continue;
            }

            $componentId = filter_var($componentId, FILTER_VALIDATE_INT);
            if ($componentId === false || $componentId < 1 || in_array($componentId, $selectedIds, true)) {
                throw new InvalidArgumentException('Invalid component selection');
            }
            $selectedIds[] = $componentId;
        }

        $totalPrice = (float) $server['base_price'];
        if ($selectedIds) {
            // Named placeholders keep the query safe from SQL injection
            $placeholders = [];
            $params = [':server_id' => $serverId];
            foreach ($selectedIds as $index => $componentId) {
                $placeholder = ':component_' . $index;
                $placeholders[] = $placeholder;
                $params[$placeholder] = $componentId;
            }

            $componentStmt = $this->pdo->prepare('
                SELECT c.id, c.type, c.price
                FROM components c
                INNER JOIN server_components sc ON sc.component_id = c.id
                WHERE sc.server_id = :server_id
                  AND c.available = TRUE
                  AND c.id IN (' . implode(', ', $placeholders) . ')
            ');
            $componentStmt->execute($params);
            $components = $componentStmt->fetchAll();

            // A mismatch means some component is unavailable or not compatible with this server
            if (count($components) !== count($selectedIds)) {
                throw new InvalidArgumentException('One or more components are unavailable for this server');
            }

            $selectedTypes = [];
            foreach ($components as $component) {
                if (in_array($component['type'], $selectedTypes, true)) {
                    throw new InvalidArgumentException('Only one component per type can be selected');
                }
                $selectedTypes[] = $component['type'];
                $totalPrice += (float) $component['price'];
            }
        }

        return [
            'component_ids' => $selectedIds,
            'total_price' => round($totalPrice, 2),
        ];
    }

    /**
     * Adds a configured server to the cart; components are stored as JSON.
     *
     * @param int $cart_id Target cart id.
     * @param int $server_id Chosen server id.
     * @param array $component_ids Validated component ids.
     * @param float $total_price Price calculated on the server.
     * @return void
     */
     public function addItem($cart_id, $server_id, $component_ids, $total_price) {
        $stmt = $this->pdo->prepare("
            INSERT INTO cart_items (cart_id, server_id, component_ids, total_price)
            VALUES (:cart_id, :server_id, :component_ids, :total_price)
        ");
        $stmt->execute([
            ':cart_id' => $cart_id,
            ':server_id' => $server_id,
            ':component_ids' => json_encode($component_ids),
            ':total_price' => $total_price
        ]);
    }

    /**
     * Removes an item only if its cart belongs to the given session.
     *
     * @param int $item_id Cart item id.
     * @param string $session_id Current session id.
     * @return bool True if an item was deleted.
     */
    public function removeItem($item_id, $session_id) {
        // Joining on session_id prevents deleting items from other users' carts
        $stmt = $this->pdo->prepare("
            DELETE ci
            FROM cart_items ci
            INNER JOIN carts c ON c.id = ci.cart_id
            WHERE ci.id = :item_id AND c.session_id = :session_id
        ");

        $stmt->execute([
            ':item_id' => $item_id,
            ':session_id' => $session_id
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Deletes all items from a cart, e.g. after an order is placed.
     *
     * @param int $cart_id Cart identifier.
     * @return bool True on success.
     */
    public function clearCart($cart_id) {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        return $stmt->execute([':cart_id' => $cart_id]);
    }
}