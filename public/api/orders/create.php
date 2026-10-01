<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Order.php';
require_once BASE_PATH . 'src/Models/Cart.php';
require_once BASE_PATH . 'src/Models/User.php';
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

if (!csrf_verify($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    Response::json(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

if (isset($_SESSION['user_id'])) {
    $userModel = new User($pdo);
    $currentUser = $userModel->findById($_SESSION['user_id']);

    if (!$currentUser || ($currentUser['role'] !== 'admin' && $currentUser['email_verified_at'] === null)) {
        http_response_code(403);
        Response::json([
            'success' => false,
            'message' => 'Verify your account to complete registration and be able to place an order.'
        ]);
    }
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
    
    //$cartModel->clearCart($cart['id']);
    
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
