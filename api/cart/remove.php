<?php
session_start();

const BASE_PATH = __DIR__ . '/../../';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Cart.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$item_id = $data['item_id'] ?? null;

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'No item specified']);
    exit;
}

$cartModel = new Cart($pdo);
$cartModel->removeItem($item_id);

echo json_encode(['success' => true, 'message' => 'Item removed from cart']);