<?php
session_start();

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Request must be GET']);
	exit;
}

$cartModel = new Cart($pdo);
$cart = $cartModel->getOrCreateCart(session_id());
$items = $cartModel->getItems($cart['id']);

//var_dump($cart);
//var_dump($items);

echo json_encode(['success' => true, 'items' => $items]);

