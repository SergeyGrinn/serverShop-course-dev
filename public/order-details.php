<?php

const BASE_PATH = __DIR__ . '/../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Order.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId || $orderId < 1) {
    http_response_code(400);
    exit('Invalid order ID');
}

$order = (new Order($pdo))->get($orderId);
if (!$order) {
    http_response_code(404);
    exit('Order not found');
}

$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
$isOwner = $isAdmin;

if (!$isOwner && isset($_SESSION['user_id']) && $order['user_id'] !== null) {
    $isOwner = (int) $order['user_id'] === (int) $_SESSION['user_id'];
} elseif (!$isOwner && $order['user_id'] === null) {
    $currentSessionId = $_SESSION['session_id'] ?? session_id();
    $isOwner = hash_equals((string) $order['session_id'], (string) $currentSessionId);
}

if (!$isOwner) {
    http_response_code(403);
    exit('Access denied');
}

$statusColors = [
    'pending' => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Awaiting Payment'],
    'processing' => ['bg' => '#d1ecf1', 'text' => '#0c5460', 'label' => 'Processing'],
    'completed' => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Completed'],
    'cancelled' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Cancelled'],
];
$currentStatus = $statusColors[$order['status']] ?? $statusColors['pending'];
$backUrl = $isAdmin ? BASE_URL . '/admin/orders.php' : BASE_URL . '/my-orders.php';
$backLabel = $isAdmin ? 'Back to Order Management' : 'Back to Order History';

require_once BASE_PATH . 'templates/order-details.php';