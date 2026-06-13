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
            $stmt2 = $this->pdo->prepare("SELECT name, value, type FROM components WHERE id IN ($placeholders)");
            $stmt2->execute($ids);
            $item['components'] = $stmt2->fetchAll();
        } else {
            $item['components'] = [];
        }
    }

    return $items;
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

    public function removeItem($item_id) {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE id = :id");
        $stmt->execute([':id' => $item_id]);
    }

    public function clearCart($cart_id) {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        return $stmt->execute([':cart_id' => $cart_id]);
    }
}