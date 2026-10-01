<?php require_once base_path('templates/header.php'); ?>

<main class="p-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-1">Admin</p>
            <h1 class="text-3xl font-bold">Order Management</h1>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left">Order</th>
                        <th class="px-5 py-3 text-left">Customer</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Total</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-500">No orders yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-5 py-3 font-semibold">#<?= (int) $order['id'] ?></td>
                                <td class="px-5 py-3">
                                    <div><?= htmlspecialchars($order['name']) ?></div>
                                    <?php if (!empty($order['account_username'])): ?>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($order['account_username']) ?></div>
                                    <?php else: ?>
                                        <div class="text-xs text-gray-500">Guest</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3"><?= htmlspecialchars($order['email']) ?></td>
                                <td class="px-5 py-3"><?= htmlspecialchars(date('M j, Y H:i', strtotime($order['created_at']))) ?></td>
                                <td class="px-5 py-3 font-semibold">€<?= number_format((float) $order['total_price'], 2) ?></td>
                                <td class="px-5 py-3">
                                    <span class="order-status order-status-<?= htmlspecialchars($order['status']) ?>">
                                        <?= htmlspecialchars(ucfirst($order['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <a href="<?= BASE_URL ?>/order-details.php?id=<?= (int) $order['id'] ?>" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once base_path('templates/footer.php'); ?>
