<?php

require_once '../../src/Config/db.php';
require_once '../../src/Models/Order.php';
require_once '../../src/Models/Cart.php';
require_once '../../src/Helpers/Response.php';

session_start();

if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    Response::json(['success' => false, 'message' => 'Request must be POST']);
    exit;
}

// Validate and sanitize input data
$name = trim($_POST['name'] ?? ''); 
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$comment = trim($_POST['comment'] ?? '');

if (empty($name) || empty($email)) {
    http_response_code(400); 
    Response::json([
        'success' => false, 
        'message' => 'Name and email are required'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    Response::json([
        'success' => false, 
        'message' => 'Invalid email format'
    ]);
    exit;
}

$cartModel = new Cart($pdo);

$cart = $cartModel->getOrCreateCart($_SESSION['session_id'] ?? session_id());

$cartItems = $cartModel->getItems($cart['id']);

if (empty($cartItems)) {
    http_response_code(400);
    Response::json([
        'success' => false,
        'message' => 'Cart is empty'
    ]);
    exit;
}

// Calculate total price
$totalPrice = array_sum(array_column($cartItems, 'total_price'));

$orderData = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'session_id' => $_SESSION['session_id'] ?? session_id(),
    'total_price' => $totalPrice,
    'name' => $name,
    'email' => $email,
    'phone' => $phone ?: null,
    'comment' => $comment ?: null,
];



try {
    $orderModel = new Order($pdo);
    
    $orderId = $orderModel->create($orderData, $cartItems);
    
    if (!$orderId) {
        throw new Exception('Failed to create order');
    }
    
    $cartModel->clearCart($cart['id']);
    
    http_response_code(201);
    Response::json([
        'success' => true,
        'orderId' => $orderId,
        'message' => 'Order successfully created'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    Response::json([
        'success' => false,
        'message' => 'Error creating order: ' . $e->getMessage()
    ]);
}
