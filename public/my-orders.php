<?php

const BASE_PATH = __DIR__ . '/../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php?redirect=my-orders.php');
    exit;
}

require_once BASE_PATH . 'src/Models/Order.php';

// Get user's orders 

$orderModel = new Order($pdo);
$orders = $orderModel->getByUser($_SESSION['user_id']);

// Get item count for each order
foreach ($orders as &$order) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = :order_id");
    $stmt->execute([':order_id' => $order['id']]);
    $itemCount = $stmt->fetch();
    $order['items_count'] = $itemCount['count'];
}

// Define status colors
$statusColors = [
    'pending' => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Awaiting Payment'],
    'processing' => ['bg' => '#d1ecf1', 'text' => '#0c5460', 'label' => 'Processing'],
    'completed' => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Completed'],
    'cancelled' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Cancelled'],
];

require_once BASE_PATH . 'templates/header.php';
?>

<main class="orders-page">
    <div class="orders-page-heading">
        <div>
            <h1>My Orders</h1>
            <p class="orders-count"><?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?></p>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <section class="orders-empty-state">
            <svg class="orders-empty-icon" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <path d="M13 7.5h15l8 8V40H13a3 3 0 0 1-3-3V10.5a3 3 0 0 1 3-3Z" />
                <path d="M28 8v9h8M17 24h15M17 30h15M17 36h8" />
            </svg>
            <div>
                <h2>No orders yet</h2>
                <p>Your orders and their statuses will appear here.</p>
            </div>
            <a href="<?= BASE_URL ?>/index.php" class="orders-shop-button">Browse Servers</a>
        </section>
    <?php else: ?>
        <div class="orders-table-wrap">
            <table class="orders-table">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left">Order ID</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Items</th>
                        <th class="px-5 py-3 text-left">Total</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order):
                        $currentStatus = $statusColors[$order['status']] ?? $statusColors['pending'];
                    ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold">#<?= (int) $order['id'] ?></td>
                            <td class="px-5 py-3 text-sm">
                                <?= htmlspecialchars(date('M d, Y H:i', strtotime($order['created_at']))) ?>
                            </td>
                            <td class="px-5 py-3"><?= (int) $order['items_count'] ?> item<?= (int) $order['items_count'] !== 1 ? 's' : '' ?></td>
                            <td class="px-5 py-3 font-semibold">€<?= number_format((float) $order['total_price'], 2) ?></td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded text-sm font-semibold" style="background-color: <?= $currentStatus['bg'] ?>; color: <?= $currentStatus['text'] ?>;">
                                    <?= htmlspecialchars($currentStatus['label']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <a href="<?= BASE_URL ?>/order-details.php?id=<?= (int) $order['id'] ?>" class="inline-block px-4 py-2 rounded font-semibold text-white" style="background-color: #6a8a63;">Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php
require_once BASE_PATH . 'templates/footer.php';
?>
