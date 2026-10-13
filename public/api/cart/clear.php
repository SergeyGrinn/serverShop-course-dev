<?php
session_start();

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Cart.php';
require_once BASE_PATH . 'src/Helpers/Response.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    Response::json(['success' => false, 'message' => 'Request must be POST']);
}

if (!csrf_verify($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    Response::json(['success' => false, 'message' => 'Invalid CSRF token']);
}

$cartModel = new Cart($pdo);
$cart = $cartModel->getOrCreateCart(session_id());
$cartModel->clearCart($cart['id']);

Response::json(['success' => true, 'message' => 'Cart cleared']);