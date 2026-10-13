<main id="order-page" class="container mx-auto px-8 py-8">
    <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-4xl font-bold">Order #<?= $order['id'] ?></h1>
            <div class="px-4 py-2 rounded font-semibold" style="background-color: <?= $currentStatus['bg'] ?>; color: <?= $currentStatus['text'] ?>;">
                <?= $currentStatus['label'] ?>
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded grid grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-gray-600 text-sm">Order Date</p>
                <p class="font-bold"><?= date('M d, Y', strtotime($order['created_at'])) ?></p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Order Time</p>
                <p class="font-bold"><?= date('H:i', strtotime($order['created_at'])) ?></p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Total Amount</p>
                <p class="font-bold text-lg" style="color: #6a8a63;">€<?= number_format($order['total_price'], 2) ?></p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Items</p>
                <p class="font-bold"><?= count($order['items']) ?> Item<?= count($order['items']) !== 1 ? 's' : '' ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-8">
        <div class="col-span-2">
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Order Items</h2>

                <?php foreach ($order['items'] as $item): ?>
                    <div class="border-b pb-6 mb-6 last:border-b-0">
                        <div class="flex justify-between gap-4 mb-4">
                            <?php if ($item['image']): ?>
                                <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['server_name']) ?>" class="w-24 h-24 object-cover rounded">
                            <?php else: ?>
                                <div class="w-24 h-24 bg-gray-200 rounded flex items-center justify-center">No image</div>
                            <?php endif; ?>

                            <div class="flex-1">
                                <h3 class="text-xl font-bold"><?= htmlspecialchars($item['server_name']) ?></h3>
                                <p class="text-gray-600 text-sm">Server ID: #<?= $item['server_id'] ?></p>
                                <?php if (!empty($item['server_description'])): ?>
                                    <p class="text-sm mt-2"><?= nl2br(htmlspecialchars($item['server_description'])) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="ml-auto flex shrink-0 items-center gap-2 text-sm">
                                <span class="text-gray-600">Server price:</span>
                                <span class="font-semibold">€<?= number_format((float) $item['server_base_price'], 2) ?></span>
                            </div>
                        </div>

                        <?php if (!empty($item['components'])): ?>
                            <div class="mb-4 bg-gray-50 p-3 rounded">
                                <p class="font-semibold text-sm mb-2">Components:</p>
                                <ul class="space-y-1 text-sm text-gray-700">
                                    <?php foreach ($item['components'] as $component): ?>
                                        <li class="flex justify-between gap-4">
                                            <span>+ <?= htmlspecialchars($component['name']) ?>: <span class="font-semibold"><?= htmlspecialchars($component['value']) ?></span></span>
                                            <span class="shrink-0 font-medium">€<?= number_format((float) $component['price'], 2) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-span-1">
            <div class="bg-white border rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Customer Information</h2>
                <div class="space-y-3 text-sm">
                    <div><p class="text-gray-600">Full Name</p><p class="font-semibold"><?= htmlspecialchars($order['name']) ?></p></div>
                    <div><p class="text-gray-600">Email Address</p><p class="font-semibold"><?= htmlspecialchars($order['email']) ?></p></div>
                    <?php if ($order['phone']): ?>
                        <div><p class="text-gray-600">Phone Number</p><p class="font-semibold"><?= htmlspecialchars($order['phone']) ?></p></div>
                    <?php endif; ?>
                    <?php if ($order['comment']): ?>
                        <div><p class="text-gray-600">Comments</p><p class="font-semibold text-gray-700"><?= htmlspecialchars($order['comment']) ?></p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white border rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-3 pb-3 border-b"><span class="text-gray-600">Subtotal:</span><span class="font-semibold">€<?= number_format($order['total_price'], 2) ?></span></div>
                <div class="flex justify-between items-center mb-3 pb-3 border-b"><span class="text-gray-600">Shipping:</span><span class="font-semibold">€0.00</span></div>
                <div class="flex justify-between items-center text-lg font-bold"><span>Total:</span><span style="color: #6a8a63;">€<?= number_format($order['total_price'], 2) ?></span></div>
            </div>

            <a href="<?= BASE_URL ?>/invoice.php?id=<?= (int) $order['id'] ?>" class="block w-full py-3 font-bold text-center rounded border-2 transition mb-6" style="border-color: #6a8a63; color: #6a8a63;">Download Invoice</a>

            <div class="space-y-3">
                <?php if ($order['status'] === 'pending'): ?>
                    <button onclick="processPayment(<?= $order['id'] ?>)" id="payButton" class="w-full py-3 font-bold rounded text-white transition" style="background-color: #6a8a63; hover:background-color: #5a7a53;">💳 Pay Now</button>
                    <button onclick="cancelOrder(<?= $order['id'] ?>)" id="cancelButton" class="w-full py-3 font-bold rounded border-2 transition" style="border-color: #6a8a63; color: #6a8a63; hover:background-color: #f5f5f5;">✕ Cancel Order</button>
                <?php elseif ($order['status'] === 'paid'): ?>
                    <div class="bg-green-50 border border-green-200 p-4 rounded"><p class="text-sm text-green-700">✓ Payment is marked as received. Your order is awaiting completion.</p></div>
                <?php elseif ($order['status'] === 'processing'): ?>
                    <div class="bg-blue-50 border border-blue-200 p-4 rounded"><p class="text-sm text-blue-700">✓ Your order is being processed<br>Invoice has been sent to your email</p></div>
                <?php elseif ($order['status'] === 'completed'): ?>
                    <div class="bg-green-50 border border-green-200 p-4 rounded"><p class="text-sm text-green-700">✓ Your order has been completed</p></div>
                <?php elseif ($order['status'] === 'cancelled'): ?>
                    <div class="bg-red-50 border border-red-200 p-4 rounded"><p class="text-sm text-red-700">✕ This order has been cancelled</p></div>
                <?php endif; ?>
            </div>

            <a href="<?= BASE_URL ?>/index.php" class="block w-full text-center py-3 font-bold rounded border-2 mt-4 transition" style="border-color: #ccc; color: #666; hover:background-color: #f5f5f5;">← Back to Catalog</a>
        </div>
    </div>
</main>

<script>
async function processPayment(orderId) {
    const payButton = document.getElementById('payButton');
    payButton.disabled = true;
    payButton.textContent = 'Processing...';

    try {
        const response = await fetch(BASE_URL + '/api/orders/pay.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({order_id: orderId})
        });
        const data = await response.json();

        if (data.success) {
            showNotification(data.message, data.invoiceSent ? 'success' : 'warning');
            setTimeout(() => location.reload(), 4000);
        } else {
            showNotification(data.message, 'error');
            payButton.disabled = false;
            payButton.textContent = '💳 Pay Now';
        }
    } catch (error) {
        showNotification(error.message, 'error');
        payButton.disabled = false;
        payButton.textContent = '💳 Pay Now';
    }
}

async function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) {
        return;
    }

    const cancelButton = document.getElementById('cancelButton');
    cancelButton.disabled = true;
    cancelButton.textContent = 'Cancelling...';

    try {
        const response = await fetch(BASE_URL + '/api/orders/cancel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({order_id: orderId})
        });
        const data = await response.json();

        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 8000);
        } else {
            showNotification(data.message, 'error');
            cancelButton.disabled = false;
            cancelButton.textContent = '✕ Cancel Order';
        }
    } catch (error) {
        showNotification(error.message, 'error');
        cancelButton.disabled = false;
        cancelButton.textContent = '✕ Cancel Order';
    }
}
</script>