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
                $stmt = $this->pdo->prepare("
                    INSERT INTO order_items (order_id, server_id, component_ids, total_price)
                    VALUES (:order_id, :server_id, :component_ids, :total_price)
                ");

                $stmt->execute([
                    ':order_id' => $orderId,
                    ':server_id' => $item['server_id'],
                    ':component_ids' => $item['component_ids'], // JSON строка
                    ':total_price' => $item['total_price'],
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
            SELECT oi.*, s.name as server_name, s.image
            FROM order_items oi
            JOIN servers s ON oi.server_id = s.id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute([':order_id' => $orderId]);
        $order['items'] = $stmt->fetchAll();

        //For each order item, decode component_ids and fetch component details
        foreach ($order['items'] as &$item) {
            $componentIds = json_decode($item['component_ids'], true);
            if (!empty($componentIds)) {
                $placeholders = implode(',', array_fill(0, count($componentIds), '?'));
                $stmt = $this->pdo->prepare("
                    SELECT id, name, value, type FROM components WHERE id IN ($placeholders)
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
}
