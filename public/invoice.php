<?php

const BASE_PATH = __DIR__ . '/../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Order.php';
require_once BASE_PATH . 'src/Models/User.php';
require_once BASE_PATH . 'vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId || $orderId < 1) {
    http_response_code(400);
    exit('Invalid order ID');
}

$order = (new Order($pdo))->get($orderId);
if (!$order) {
    http_response_code(404);
    exit('Order not found');
}

$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
$isOwner = isset($_SESSION['user_id'])
    && $order['user_id'] !== null
    && (int) $order['user_id'] === (int) $_SESSION['user_id'];

if (!$isAdmin && !$isOwner) {
    http_response_code(403);
    exit('Access denied');
}

$statusLabels = [
    'pending' => 'Awaiting Payment',
    'paid' => 'Paid',
    'processing' => 'Processing',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
];
$orderStatus = $statusLabels[$order['status']] ?? ucfirst($order['status']);
$invoiceSeller = (new User($pdo))->getAdminInvoiceDetails() ?: [];

ob_start();
require BASE_PATH . 'templates/invoice.php';
$invoiceHtml = ob_get_clean();

$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($invoiceHtml, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('invoice-order-' . (int) $order['id'] . '.pdf', ['Attachment' => true]);
exit;
