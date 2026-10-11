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
                    <?php foreach ($orders as $order): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold">#<?= (int) $order['id'] ?></td>
                            <td class="px-5 py-3 text-sm">
                                <?= htmlspecialchars(date('M d, Y H:i', strtotime($order['created_at']))) ?>
                            </td>
                            <td class="px-5 py-3"><?= (int) $order['items_count'] ?> item<?= (int) $order['items_count'] !== 1 ? 's' : '' ?></td>
                            <td class="px-5 py-3 font-semibold">€<?= number_format((float) $order['total_price'], 2) ?></td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded text-sm font-semibold" style="background-color: <?= $order['current_status']['bg'] ?>; color: <?= $order['current_status']['text'] ?>;">
                                    <?= htmlspecialchars($order['current_status']['label']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= BASE_URL ?>/order-details.php?id=<?= (int) $order['id'] ?>" class="inline-block px-3 py-1 rounded-lg font-semibold text-white" style="background-color: <?= ($_SESSION['user_role'] ?? '') === 'admin' ? '#2b78b8' : '#308020' ?>;">Details</a>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <button type="button" class="my-order-action-pay px-3 py-1 rounded-lg font-semibold text-white" style="background-color: <?= ($_SESSION['user_role'] ?? '') === 'admin' ? '#2b78b8' : '#308020' ?>;" data-order-id="<?= (int) $order['id'] ?>">Pay Now</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

        <script>
        document.querySelectorAll('.my-order-action-pay').forEach(function (button) {
            button.addEventListener('click', async function () {
                const orderId = this.dataset.orderId;
                this.disabled = true;
                this.textContent = 'Processing...';

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

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to pay for this order');
                    }

                    showNotification(data.message, data.invoiceSent ? 'success' : 'warning');
                    setTimeout(function () { window.location.reload(); }, 3500);
                } catch (error) {
                    showNotification(error.message, 'error');
                    this.disabled = false;
                    this.textContent = 'Pay Now';
                }
            });
        });
        </script>