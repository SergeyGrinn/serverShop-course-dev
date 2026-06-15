<?php
session_start();

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Cart.php';

header('Content-Type: application/json');

$cartModel = new Cart($pdo);
$cart = $cartModel->getOrCreateCart(session_id());
$items = $cartModel->getItems($cart['id']);

//var_dump($cart);
//var_dump($items);

echo json_encode(['success' => true, 'items' => $items]);

