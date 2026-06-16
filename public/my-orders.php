<?php

session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php?redirect=my-orders.php');
    exit;
}

require_once '../src/Config/db.php';
require_once '../src/Models/Order.php';

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

require_once '../templates/header.php';
?>

<main class="container mx-auto px-8 py-8">
    <h1 class="text-4xl font-bold mb-8">My Orders</h1>
    
    <!-- If no orders -->
    <?php if (empty($orders)): ?>
        <div class="bg-white border rounded-lg p-12 text-center">
            <p class="text-xl text-gray-600 mb-4">You haven't placed any orders yet</p>
            <a href="' . BASE_URL . '/index.php"
                class="inline-block px-6 py-2 rounded font-semibold text-white transition"
                style="background-color: #6a8a63; hover:background-color: #5a7a53;"
            >
                Start Shopping
            </a>
        </div>
    
    <!-- If orders exist -->
    <?php else: ?>
        <div class="bg-white border rounded-lg overflow-hidden">
            <!-- Table Header -->
            <div class="grid grid-cols-6 gap-4 bg-gray-100 p-4 font-bold border-b">
                <div>Order ID</div>
                <div>Date</div>
                <div>Items</div>
                <div>Total</div>
                <div>Status</div>
                <div>Action</div>
            </div>
            
            <!-- Orders List -->
            <?php foreach ($orders as $order): 
                $currentStatus = $statusColors[$order['status']] ?? $statusColors['pending'];
            ?>
                <div class="grid grid-cols-6 gap-4 p-4 border-b items-center hover:bg-gray-50 transition">
                    <!-- Order ID -->
                    <div class="font-bold">
                        #<?= $order['id'] ?>
                    </div>
                    
                    <!-- Date -->
                    <div class="text-sm text-gray-600">
                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                        <br>
                        <span class="text-xs text-gray-500">
                            <?= date('H:i', strtotime($order['created_at'])) ?>
                        </span>
                    </div>
                    
                    <!-- Items Count -->
                    <div class="text-center">
                        <span class="inline-block bg-gray-100 px-2 py-1 rounded text-sm font-semibold">
                            <?= $order['items_count'] ?> item<?= $order['items_count'] !== 1 ? 's' : '' ?>
                        </span>
                    </div>
                    
                    <!-- Total Price -->
                    <div class="font-bold" style="color: #6a8a63;">
                        €<?= number_format($order['total_price'], 2) ?>
                    </div>
                    
                    <!-- Status -->
                    <div 
                        class="px-2 py-1 rounded text-sm font-semibold text-center"
                        style="background-color: <?= $currentStatus['bg'] ?>; color: <?= $currentStatus['text'] ?>;"
                    >
                        <?= $currentStatus['label'] ?>
                    </div>
                    
                    <!-- View Button -->
                    <div>
                        <a 
                            href="<?= BASE_URL ?>/order.php?id=<?= $order['id'] ?>"
                            class="inline-block px-4 py-2 rounded font-semibold transition"
                            style="background-color: #6a8a63; color: white; hover:background-color: #5a7a53;"
                        >
                            View
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Summary -->
        <div class="mt-8 p-6 bg-gray-50 rounded grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-gray-600 text-sm">Total Orders</p>
                <p class="text-3xl font-bold"><?= count($orders) ?></p>
            </div>
            
            <div>
                <p class="text-gray-600 text-sm">Total Spent</p>
                <p class="text-3xl font-bold" style="color: #6a8a63;">
                    €<?= number_format(array_sum(array_column($orders, 'total_price')), 2) ?>
                </p>
            </div>
            
            <div>
                <p class="text-gray-600 text-sm">Pending Orders</p>
                <p class="text-3xl font-bold">
                    <?= count(array_filter($orders, fn($o) => $o['status'] === 'pending')) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php
require_once '../templates/footer.php';
?>
