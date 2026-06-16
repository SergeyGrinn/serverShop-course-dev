<?php

const BASE_PATH = __DIR__ . '/../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Order.php';

session_start();

// Load necessary classes
require_once '../src/Config/db.php';
require_once '../src/Models/Order.php';

// ====== STEP 1: Get order ID from URL ======

// Check if order ID is provided in URL
// Example: /order.php?id=123
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . '/index.php?message=Order not found');
    exit;
}

$orderId = intval($_GET['id']); // Convert to integer to prevent SQL injection

// ====== STEP 2: Load order from database ======

$orderModel = new Order($pdo);
$order = $orderModel->get($orderId);

// If order doesn't exist
if (!$order) {
    header('Location: ' . BASE_URL . '/index.php?message=Order not found');
    exit;
}

// Check ownership: only owner can view order
// If not logged in - check session_id
// If logged in - check user_id
$isOwner = false;
if (isset($_SESSION['user_id']) && $order['user_id'] == $_SESSION['user_id']) {
    $isOwner = true;
} elseif ($order['session_id'] === ($_SESSION['session_id'] ?? session_id())) {
    $isOwner = true;
}

// If trying to view someone else's order - redirect
if (!$isOwner && $order['user_id'] !== null) {
    http_response_code(403); // 403 = Forbidden
    header('Location: ' . BASE_URL . '/index.php?message=Access denied');
    exit;
}

// Define status colors and labels
$statusColors = [
    'pending' => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Awaiting Payment'],
    'processing' => ['bg' => '#d1ecf1', 'text' => '#0c5460', 'label' => 'Processing'],
    'completed' => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Completed'],
    'cancelled' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Cancelled'],
];

$currentStatus = $statusColors[$order['status']] ?? $statusColors['pending'];

// Include header
require_once '../templates/header.php';
?>

<main class="container mx-auto px-8 py-8">
    
    <!-- Order Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-4xl font-bold">Order #<?= $order['id'] ?></h1>
            <div 
                class="px-4 py-2 rounded font-semibold" 
                style="background-color: <?= $currentStatus['bg'] ?>; color: <?= $currentStatus['text'] ?>;"
            >
                <?= $currentStatus['label'] ?>
            </div>
        </div>
        
        <!-- Order Meta Info -->
        <div class="bg-gray-50 p-4 rounded grid grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-gray-600 text-sm">Order Date</p>
                <p class="font-bold">
                    <?= date('M d, Y', strtotime($order['created_at'])) ?>
                </p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Order Time</p>
                <p class="font-bold">
                    <?= date('H:i', strtotime($order['created_at'])) ?>
                </p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Total Amount</p>
                <p class="font-bold text-lg" style="color: #6a8a63;">
                    €<?= number_format($order['total_price'], 2) ?>
                </p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Items</p>
                <p class="font-bold">
                    <?= count($order['items']) ?> Item<?= count($order['items']) !== 1 ? 's' : '' ?>
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-8">
        
        <!-- LEFT: Order Items (2 columns) -->
        <div class="col-span-2">
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Order Items</h2>
                
                <?php foreach ($order['items'] as $item): ?>
                    <div class="border-b pb-6 mb-6 last:border-b-0">
                        <!-- Server Info -->
                        <div class="flex gap-4 mb-4">
                            <?php if ($item['image']): ?>
                                <img 
                                    src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                    alt="<?= htmlspecialchars($item['server_name']) ?>" 
                                    class="w-24 h-24 object-cover rounded"
                                >
                            <?php else: ?>
                                <div class="w-24 h-24 bg-gray-200 rounded flex items-center justify-center">
                                    No image
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-1">
                                <h3 class="text-xl font-bold">
                                    <?= htmlspecialchars($item['server_name']) ?>
                                </h3>
                                <p class="text-gray-600 text-sm">Server ID: #<?= $item['server_id'] ?></p>
                            </div>
                        </div>
                        
                        <!-- Components List -->
                        <?php if (!empty($item['components'])): ?>
                            <div class="mb-4 bg-gray-50 p-3 rounded">
                                <p class="font-semibold text-sm mb-2">Components:</p>
                                <ul class="list-disc list-inside text-sm text-gray-700">
                                    <?php foreach ($item['components'] as $component): ?>
                                        <li>
                                            <?= htmlspecialchars($component['name']) ?>: 
                                            <span class="font-semibold">
                                                <?= htmlspecialchars($component['value']) ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Item Price -->
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Item Price:</span>
                            <span class="text-lg font-bold" style="color: #6a8a63;">
                                €<?= number_format($item['total_price'], 2) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- RIGHT: Customer Info & Actions (1 column) -->
        <div class="col-span-1">
            <!-- Customer Information -->
            <div class="bg-white border rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Customer Information</h2>
                
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Full Name</p>
                        <p class="font-semibold"><?= htmlspecialchars($order['name']) ?></p>
                    </div>
                    
                    <div>
                        <p class="text-gray-600">Email Address</p>
                        <p class="font-semibold"><?= htmlspecialchars($order['email']) ?></p>
                    </div>
                    
                    <?php if ($order['phone']): ?>
                        <div>
                            <p class="text-gray-600">Phone Number</p>
                            <p class="font-semibold"><?= htmlspecialchars($order['phone']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order['comment']): ?>
                        <div>
                            <p class="text-gray-600">Comments</p>
                            <p class="font-semibold text-gray-700"><?= htmlspecialchars($order['comment']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Total Summary -->
            <div class="bg-white border rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-3 pb-3 border-b">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-semibold">€<?= number_format($order['total_price'], 2) ?></span>
                </div>
                
                <div class="flex justify-between items-center mb-3 pb-3 border-b">
                    <span class="text-gray-600">Shipping:</span>
                    <span class="font-semibold">€0.00</span>
                </div>
                
                <div class="flex justify-between items-center text-lg font-bold">
                    <span>Total:</span>
                    <span style="color: #6a8a63;">
                        €<?= number_format($order['total_price'], 2) ?>
                    </span>
                </div>
            </div>
            
            <!-- Actions Based on Status -->
            <div class="space-y-3">
                
                <!-- If Pending: Show Payment Button -->
                <?php if ($order['status'] === 'pending'): ?>
                    <button 
                        onclick="processPayment(<?= $order['id'] ?>)"
                        id="payButton"
                        class="w-full py-3 font-bold rounded text-white transition"
                        style="background-color: #6a8a63; hover:background-color: #5a7a53;"
                    >
                        💳 Pay Now
                    </button>
                    
                    <button 
                        onclick="cancelOrder(<?= $order['id'] ?>)"
                        id="cancelButton"
                        class="w-full py-3 font-bold rounded border-2 transition"
                        style="border-color: #6a8a63; color: #6a8a63; hover:background-color: #f5f5f5;"
                    >
                        ✕ Cancel Order
                    </button>
                
                <!-- If Processing: Show Tracking -->
                <?php elseif ($order['status'] === 'processing'): ?>
                    <div class="bg-blue-50 border border-blue-200 p-4 rounded">
                        <p class="text-sm text-blue-700">
                            ✓ Order paid and processing<br>
                            Tracking number will be sent to your email
                        </p>
                    </div>
                
                <!-- If Completed: Show Info -->
                <?php elseif ($order['status'] === 'completed'): ?>
                    <div class="bg-green-50 border border-green-200 p-4 rounded">
                        <p class="text-sm text-green-700">
                            ✓ Order completed and shipped<br>
                            Check your email for tracking details
                        </p>
                    </div>
                
                <!-- If Cancelled: Show Info -->
                <?php elseif ($order['status'] === 'cancelled'): ?>
                    <div class="bg-red-50 border border-red-200 p-4 rounded">
                        <p class="text-sm text-red-700">
                            ✕ This order has been cancelled
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Back to Catalog -->
            <a 
                href="<?= BASE_URL ?>/index.php"
                class="block w-full text-center py-3 font-bold rounded border-2 mt-4 transition"
                style="border-color: #ccc; color: #666; hover:background-color: #f5f5f5;"
            >
                ← Back to Catalog
            </a>
        </div>
    </div>
</main>

<!-- JavaScript for order actions -->
<script>
/**
 * Process payment for the order
 * Sends request to /api/orders/pay.php to change status to 'processing'
 * 
 * In real application, this would redirect to Stripe/PayPal
 */
async function processPayment(orderId) {
    // Disable button while processing
    const payButton = document.getElementById('payButton');
    payButton.disabled = true;
    payButton.textContent = 'Processing...';
    
    try {
        const response = await fetch(BASE_URL + '/api/orders/pay.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            alert('✓ ' + data.message);
            
            // Reload page to show updated status
            location.reload();
        } else {
            // Show error message
            alert('✗ Error: ' + data.message);
            payButton.disabled = false;
            payButton.textContent = '💳 Pay Now';
        }
    } catch (error) {
        alert('✗ Error: ' + error.message);
        payButton.disabled = false;
        payButton.textContent = '💳 Pay Now';
    }
}

/**
 * Cancel the order
 * Sends request to /api/orders/cancel.php to change status to 'cancelled'
 */
async function cancelOrder(orderId) {
    // Confirm action
    if (!confirm('Are you sure you want to cancel this order?')) {
        return;
    }
    
    // Disable button while processing
    const cancelButton = document.getElementById('cancelButton');
    cancelButton.disabled = true;
    cancelButton.textContent = 'Cancelling...';
    
    try {
        const response = await fetch(BASE_URL + '/api/orders/cancel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            alert('✓ ' + data.message);
            
            // Reload page to show updated status
            location.reload();
        } else {
            // Show error message
            alert('✗ Error: ' + data.message);
            cancelButton.disabled = false;
            cancelButton.textContent = '✕ Cancel Order';
        }
    } catch (error) {
        alert('✗ Error: ' + error.message);
        cancelButton.disabled = false;
        cancelButton.textContent = '✕ Cancel Order';
    }
}
</script>

<?php
// Include footer
require_once '../templates/footer.php';
?>
