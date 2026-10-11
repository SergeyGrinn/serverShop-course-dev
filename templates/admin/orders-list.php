<?php require_once base_path('templates/header.php'); ?>

<main id="orders-app" class="p-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-1">Admin</p>
            <h1 class="text-3xl font-bold">Order Management</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
                <?php foreach ($errors as $error): ?>
                    <p class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-wrap items-end gap-3 mb-5">
            <label class="text-sm font-medium" for="status-filter">Filter by status</label>
            <select id="status-filter" class="border border-gray-300 rounded px-3 py-2" data-bind="value: selectedStatus">
                <option value="">All statuses</option>
                <?php foreach ($statusOptions as $status => $label): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="text-sm font-medium" for="order-sort">Sort by</label>
            <select id="order-sort" class="border border-gray-300 rounded px-3 py-2">
                <option value="newest">Newest first</option>
                <option value="oldest">Oldest first</option>
                <option value="price-asc">Price: low to high</option>
                <option value="price-desc">Price: high to low</option>
            </select>
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
                        <tr data-bind="visible: hasNoMatches">
                            <td colspan="7" class="px-5 py-8 text-center text-gray-500">No orders match this status.</td>
                        </tr>
                        <?php foreach ($orders as $order): ?>
                            <tr class="admin-order-row border-b border-gray-200 hover:bg-gray-50" data-order-id="<?= (int) $order['id'] ?>" data-order-date="<?= (int) strtotime($order['created_at']) ?>" data-order-total="<?= htmlspecialchars((string) $order['total_price'], ENT_QUOTES, 'UTF-8') ?>" data-bind="visible: isStatusVisible('<?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?>')">
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
                                        <?= htmlspecialchars($statusOptions[$order['status']] ?? ucfirst($order['status'])) ?>
                                    </span>
                                    <?php if (isset($statusActions[$order['status']])): ?>
                                        <?php $statusAction = $statusActions[$order['status']]; ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/orders.php<?= $statusFilter !== '' ? '?status=' . rawurlencode($statusFilter) : '' ?>" class="mt-2">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                            <input type="hidden" name="status_filter" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="<?= htmlspecialchars($statusAction['action'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                <?= htmlspecialchars($statusAction['label']) ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
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

<script>
(function () {
    const orderStatuses = <?= json_encode(array_column($orders, 'status'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const initialStatus = <?= json_encode($statusFilter, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    function OrdersFilterViewModel(statuses, selectedStatus) {
        const self = this;
        self.selectedStatus = ko.observable(selectedStatus);
        self.isStatusVisible = function (status) {
            return self.selectedStatus() === '' || self.selectedStatus() === status;
        };
        self.hasNoMatches = ko.computed(function () {
            const filter = self.selectedStatus();
            return !statuses.some(function (status) {
                return filter === '' || filter === status;
            });
        });
    }

    ko.applyBindings(new OrdersFilterViewModel(orderStatuses, initialStatus), document.getElementById('orders-app'));

    const sortSelect = document.getElementById('order-sort');
    const ordersTableBody = document.querySelector('#orders-app table tbody');

    function sortOrders() {
        const sortMode = sortSelect.value;
        const rows = Array.from(ordersTableBody.querySelectorAll('.admin-order-row'));
        rows.sort(function (left, right) {
            const leftDate = Number(left.dataset.orderDate);
            const rightDate = Number(right.dataset.orderDate);
            const leftPrice = Number(left.dataset.orderTotal);
            const rightPrice = Number(right.dataset.orderTotal);
            let comparison = 0;

            if (sortMode === 'oldest') comparison = leftDate - rightDate;
            else if (sortMode === 'price-asc') comparison = leftPrice - rightPrice;
            else if (sortMode === 'price-desc') comparison = rightPrice - leftPrice;
            else comparison = rightDate - leftDate;

            if (comparison !== 0) return comparison;
            const leftId = Number(left.dataset.orderId);
            const rightId = Number(right.dataset.orderId);
            return sortMode === 'oldest' || sortMode === 'price-asc' ? leftId - rightId : rightId - leftId;
        });

        rows.forEach(function (row) {
            ordersTableBody.appendChild(row);
        });
    }

    sortSelect.addEventListener('change', sortOrders);
    sortOrders();
})();
</script>

<?php require_once base_path('templates/footer.php'); ?>
