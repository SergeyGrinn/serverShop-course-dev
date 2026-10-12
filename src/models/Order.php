<?php

class Order {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Create new order with items
    public function create($data, $cartItems) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO orders (user_id, session_id, status, total_price, name, email, phone, comment)
                VALUES (:user_id, :session_id, 'pending', :total_price, :name, :email, :phone, :comment)
            ");

            $stmt->execute([
                ':user_id' => $data['user_id'] ?? null,
                ':session_id' => $data['session_id'] ?? null,
                ':total_price' => $data['total_price'],
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'] ?? null,
                ':comment' => $data['comment'] ?? null,
            ]);

            $orderId = $this->pdo->lastInsertId();

            foreach ($cartItems as $item) {
                $serverStmt = $this->pdo->prepare(
                    'SELECT name, description, image, base_price FROM servers WHERE id = :id'
                );
                $serverStmt->execute([':id' => $item['server_id']]);
                $server = $serverStmt->fetch();
                if (!$server) {
                    throw new RuntimeException('Cannot save an order item for a missing server.');
                }

                $componentIds = json_decode((string) ($item['component_ids'] ?? '[]'), true);
                $componentsById = [];
                if (is_array($componentIds) && $componentIds !== []) {
                    $componentIds = array_values(array_unique(array_map('intval', $componentIds)));
                    $placeholders = implode(',', array_fill(0, count($componentIds), '?'));
                    $componentsStmt = $this->pdo->prepare(
                        "SELECT id, name, value, type, price FROM components WHERE id IN ($placeholders)"
                    );
                    $componentsStmt->execute($componentIds);
                    foreach ($componentsStmt->fetchAll() as $component) {
                        $componentsById[(int) $component['id']] = $component;
                    }
                }

                $componentSnapshot = [];
                foreach ($componentIds ?? [] as $componentId) {
                    if (isset($componentsById[$componentId])) {
                        $componentSnapshot[] = $componentsById[$componentId];
                    }
                }

                $stmt = $this->pdo->prepare("
                    INSERT INTO order_items (
                        order_id, server_id, component_ids, total_price,
                        server_name_snapshot, server_description_snapshot, server_image_snapshot,
                        server_base_price_snapshot, components_snapshot
                    )
                    VALUES (
                        :order_id, :server_id, :component_ids, :total_price,
                        :server_name_snapshot, :server_description_snapshot, :server_image_snapshot,
                        :server_base_price_snapshot, :components_snapshot
                    )
                ");

                $stmt->execute([
                    ':order_id' => $orderId,
                    ':server_id' => $item['server_id'],
                    ':component_ids' => $item['component_ids'], // JSON строка
                    ':total_price' => $item['total_price'],
                    ':server_name_snapshot' => $server['name'],
                    ':server_description_snapshot' => $server['description'] ?? '',
                    ':server_image_snapshot' => $server['image'] ?? '',
                    ':server_base_price_snapshot' => $server['base_price'],
                    ':components_snapshot' => json_encode($componentSnapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                ]);
            }

            $this->pdo->commit();

            return $orderId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Get order by ID
    public function get($orderId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return false;
        }

        $stmt = $this->pdo->prepare("
                 SELECT oi.*,
                     COALESCE(oi.server_name_snapshot, s.name, CONCAT('Deleted server #', oi.server_id)) AS server_name,
                     COALESCE(oi.server_description_snapshot, s.description) AS server_description,
                     COALESCE(oi.server_image_snapshot, s.image) AS image,
                     COALESCE(oi.server_base_price_snapshot, s.base_price) AS server_base_price
            FROM order_items oi
                 LEFT JOIN servers s ON oi.server_id = s.id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute([':order_id' => $orderId]);
        $order['items'] = $stmt->fetchAll();

        //For each order item, decode component_ids and fetch component details
        foreach ($order['items'] as &$item) {
            $componentSnapshot = json_decode((string) ($item['components_snapshot'] ?? ''), true);
            if (is_array($componentSnapshot)) {
                $item['components'] = $componentSnapshot;
                continue;
            }

            $componentIds = json_decode($item['component_ids'], true);
            if (!empty($componentIds)) {
                $placeholders = implode(',', array_fill(0, count($componentIds), '?'));
                $stmt = $this->pdo->prepare("
                    SELECT id, name, value, type, price FROM components WHERE id IN ($placeholders)
                ");
                $stmt->execute($componentIds);
                $item['components'] = $stmt->fetchAll();
            } else {
                $item['components'] = [];
            }
        }

        return $order;
    }
    // Update order status
    public function updateStatus($orderId, $status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id' => $orderId,
        ]);
    }

    public function transitionStatus($orderId, $currentStatus, $newStatus) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = :new_status WHERE id = :id AND status = :current_status");
        $stmt->execute([
            ':new_status' => $newStatus,
            ':id' => $orderId,
            ':current_status' => $currentStatus,
        ]);
        return $stmt->rowCount() === 1;
    }

    // Get all orders for a user
    public function getByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM orders 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getByUserWithItemCount($userId) {
        $stmt = $this->pdo->prepare("
            SELECT o.*,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
            FROM orders o
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAllAdmin($status = null) {
        $sql = "
            SELECT o.*, u.username AS account_username
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
        ";

        if ($status !== null) {
            $sql .= " WHERE o.status = :status";
        }

        $sql .= "
            ORDER BY o.created_at DESC, o.id DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        if ($status !== null) {
            $stmt->bindValue(':status', $status);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
