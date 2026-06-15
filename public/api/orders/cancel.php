<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Order.php';
require_once BASE_PATH . 'src/Helpers/Response.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    Response::json(['success' => false, 'message' => 'Request must be POST']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

if (!isset($data['order_id']) || empty($data['order_id'])) {
    http_response_code(400);
    Response::json(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$orderId = intval($data['order_id']);


$orderModel = new Order($pdo);
$order = $orderModel->get($orderId);


if (!$order) {
    http_response_code(404);
    Response::json(['success' => false, 'message' => 'Order not found']);
    exit;
}

$isOwner = false;
if (isset($_SESSION['user_id']) && $order['user_id'] == $_SESSION['user_id']) { // Allow cancellation (logged-in)
    $isOwner = true;
} elseif ($order['session_id'] === ($_SESSION['session_id'] ?? session_id())) { // Allow cancellation (guest)
    $isOwner = true;
}

if (!$isOwner) {
    http_response_code(403);
    Response::json(['success' => false, 'message' => 'You can only cancel your own orders']);
    exit;
}


if ($order['status'] !== 'pending') {
    http_response_code(400);
    Response::json([
        'success' => false, 
        'message' => 'Cannot cancel order with status: ' . $order['status']
    ]);
    exit;
}

try {
    
    $success = $orderModel->updateStatus($orderId, 'cancelled');
    
    if (!$success) {
        throw new Exception('Failed to update order status');
    }
    
    http_response_code(200);
    Response::json([
        'success' => true,
        'message' => 'Order successfully cancelled'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    Response::json([
        'success' => false,
        'message' => 'Error cancelling order: ' . $e->getMessage()
    ]);
}
