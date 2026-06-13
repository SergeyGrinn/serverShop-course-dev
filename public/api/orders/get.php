<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Order.php';

header('Content-Type: application/json');

session_start();

$id = $_GET['id'] ?? null;

if (!$id){
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$orderModel = new Order($pdo);
$order = $orderModel->get($id);

if ($order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

echo json_encode(['success' => true, 'order' => $order]);