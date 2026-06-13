<?php

const BASE_PATH = __DIR__ . '/../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Cart.php';

session_start();

if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

$cartModel = new Cart($pdo);
$cart = $cartModel->getOrCreateCart($_SESSION['session_id'] ?? session_id());
$cartItems = $cartModel->getItems($cart['id']);

if (empty($cartItems)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$totalPrice = array_sum(array_column($cartItems, 'total_price'));

require base_path('templates/header.php');
require base_path('templates/checkout.php');
require base_path('templates/footer.php');