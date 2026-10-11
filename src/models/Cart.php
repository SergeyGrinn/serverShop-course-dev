<?php

class Cart {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

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

    public function removeItem($item_id, $session_id) {
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

    public function clearCart($cart_id) {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        return $stmt->execute([':cart_id' => $cart_id]);
    }
}