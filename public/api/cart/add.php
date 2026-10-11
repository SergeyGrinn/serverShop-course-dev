<?php
session_start();

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Request must be POST']);
    exit;
}

if (!csrf_verify($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$serverId = $data['server_id'] ?? null;
$componentIds = $data['component_ids'] ?? [];
if (!is_array($componentIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid component selection']);
    exit;
}

$cartModel = new Cart($pdo);
$configuredItem = null;
try {
    $configuredItem = $cartModel->calculateConfiguredItem($serverId, $componentIds);
} catch (InvalidArgumentException $error) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
    exit;
}

$cart = $cartModel->getOrCreateCart(session_id());
$cartModel->addItem(
    $cart['id'],
    $serverId,
    $configuredItem['component_ids'],
    $configuredItem['total_price']
);

echo json_encode([
    'success' => true,
    'message' => 'Added to cart',
    'total_price' => $configuredItem['total_price'],
]);