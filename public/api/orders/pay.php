<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Order.php';
require_once BASE_PATH . 'src/Models/User.php';
require_once BASE_PATH . 'src/Models/Cart.php';
require_once BASE_PATH . 'src/Helpers/Response.php';
require_once BASE_PATH . 'src/Services/InvoiceGenerator.php';
require_once BASE_PATH . 'src/Services/Mailer.php';

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    Response::json(['success' => false, 'message' => 'Request must be POST']);
    exit;
}

if (!csrf_verify($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    Response::json(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

if (!isset($data['order_id']) || empty($data['order_id'])) {
    http_response_code(400);
    Response::json(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$orderId = intval($data['order_id']);

$orderModel = new Order($pdo);
$order = $orderModel->get($orderId);

if (!$order) {
    http_response_code(404);
    Response::json(['success' => false, 'message' => 'Order not found']);
    exit;
}

$isOwner = isset($_SESSION['user_id'])
    && $order['user_id'] !== null
    && (int) $order['user_id'] === (int) $_SESSION['user_id'];

if (!$isOwner) {
    http_response_code(403);
    Response::json(['success' => false, 'message' => 'You can only pay for your own orders']);
    exit;
}

if ($order['status'] !== 'pending') {
    http_response_code(400);
    Response::json([
        'success' => false, 
        'message' => 'Cannot pay for order with status: ' . $order['status']
    ]);
    exit;
}

try {
    $success = $orderModel->updateStatus($orderId, 'processing');
    
    if (!$success) {
        throw new Exception('Failed to update order status');
    }

    $invoiceSent = false;
    try {
        $paidOrder = $orderModel->get($orderId);
        $invoiceSeller = (new User($pdo))->getAdminInvoiceDetails() ?: [];
        $invoicePdf = InvoiceGenerator::renderPdf($paidOrder, $invoiceSeller);
        $invoiceSent = Mailer::sendInvoiceEmail(
            $paidOrder['email'],
            $paidOrder['name'],
            $orderId,
            $invoicePdf
        );
        if (!$invoiceSent) {
            error_log('Invoice email could not be sent for order #' . $orderId);
        }
    } catch (Throwable $error) {
        error_log('Invoice generation/email failed for order #' . $orderId . ': ' . $error->getMessage());
    }

    $cartModel = new Cart($pdo);
    $cart = $cartModel->getOrCreateCart($_SESSION['session_id'] ?? session_id());
    $cartModel->clearCart($cart['id']);
    
    http_response_code(200);
    Response::json([
        'success' => true,
        'message' => $invoiceSent
            ? 'Payment received successfully. The invoice was sent to your email.'
            : 'Payment received and order is processing, but the invoice email could not be sent.',
        'invoiceSent' => $invoiceSent,
        'orderId' => $orderId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    Response::json([
        'success' => false,
        'message' => 'Error processing payment: ' . $e->getMessage()
    ]);
}
