<?php
session_start();

const BASE_PATH = __DIR__ . '/../../';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Cart.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$server_id = $data['server_id'] ?? null;
$component_ids = $data['component_ids'] ?? [];
$total_price = $data['total_price'] ?? 0;

if (!$server_id) {
    echo json_encode(['success' => false, 'message' => 'No server selected']);
    exit;
}

$cartModel = new Cart($pdo);
$cart = $cartModel->getOrCreateCart(session_id());
$cartModel->addItem($cart['id'], $server_id, $component_ids, $total_price);

echo json_encode(['success' => true, 'message' => 'Added to cart']);