<?php
session_start();

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Cart.php';

header('Content-Type: application/json');

$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

if (!csrf_verify($csrf_token)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$item_id = filter_var($data['item_id'] ?? null, FILTER_VALIDATE_INT);

if ($item_id === false || $item_id < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No item specified']);
    exit;
}

$cartModel = new Cart($pdo);
$removed = $cartModel->removeItem($item_id, session_id());

if (!$removed) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Item not found in current cart']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Item removed from cart']);