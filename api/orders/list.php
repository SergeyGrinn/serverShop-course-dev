<?php

session_start();


if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

require_once '../../src/Config/db.php';
require_once '../../src/Models/Order.php';
require_once '../../src/Helpers/Response.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); 
    Response::json([
        'success' => false, 
        'message' => 'User must be logged in'
    ]);
    exit;
}

try {
    $orderModel = new Order($pdo);
    
    $orders = $orderModel->getByUser($_SESSION['user_id']);
    
    
    foreach ($orders as &$order) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order['id']]);
        $itemCount = $stmt->fetch();
        $order['items_count'] = $itemCount['count'];
    }
    
    http_response_code(200);
    Response::json([
        'success' => true,
        'orders' => $orders,
        'total' => count($orders)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    Response::json([
        'success' => false,
        'message' => 'Error fetching orders: ' . $e->getMessage()
    ]);
}
