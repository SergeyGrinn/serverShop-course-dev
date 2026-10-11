<?php require_once base_path('templates/header.php'); ?>

<main class="max-w-6xl mx-auto px-5 py-8">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-gray-500">Order details</p>
            <h1 class="text-3xl font-bold">Order #<?= (int) $order['id'] ?></h1>
        </div>
        <span class="px-3 py-2 rounded font-semibold" style="background-color: <?= $currentStatus['bg'] ?>; color: <?= $currentStatus['text'] ?>;">
            <?= htmlspecialchars($currentStatus['label']) ?>
        </span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="bg-white border border-gray-200 rounded-lg p-6 lg:col-span-2">
            <h2 class="text-xl font-semibold mb-5">Ordered servers</h2>

            <?php foreach ($order['items'] as $item): ?>
                <article class="border-b border-gray-200 pb-5 mb-5 last:border-b-0 last:mb-0 last:pb-0">
                    <div class="flex justify-between gap-4 mb-4">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['server_name']) ?>" class="w-24 h-24 object-cover rounded">
                        <?php endif; ?>
                        <div>
                            <h3 class="text-lg font-semibold"><?= htmlspecialchars($item['server_name']) ?></h3>
                            <p class="text-sm text-gray-500">Server ID: #<?= (int) $item['server_id'] ?></p>
                        </div>
                        <div class="ml-auto flex shrink-0 items-center gap-2 text-sm">
                            <span class="text-gray-500">Server price</span>
                            <span class="font-semibold">€<?= number_format((float) $item['server_base_price'], 2) ?></span>
                        </div>
                    </div>

                    <?php if (!empty($item['server_description'])): ?>
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold mb-1">Description</h4>
                            <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($item['server_description'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($item['components'])): ?>
                        <div class="bg-gray-50 rounded p-3 mb-4">
                            <h4 class="text-sm font-semibold mb-2">Components</h4>
                            <ul class="space-y-1 text-sm text-gray-700">
                                <?php foreach ($item['components'] as $component): ?>
                                    <li class="flex justify-between gap-4">
                                        <span>+ <?= htmlspecialchars($component['name']) ?>: <?= htmlspecialchars($component['value']) ?></span>
                                        <span class="shrink-0 font-medium">€<?= number_format((float) $component['price'], 2) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 mb-4">No additional components.</p>
                    <?php endif; ?>

                </article>
            <?php endforeach; ?>
        </section>

        <aside class="flex flex-col gap-5">
            <section class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Customer information</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-gray-500">Name</dt><dd class="font-semibold"><?= htmlspecialchars($order['name']) ?></dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="font-semibold break-all"><?= htmlspecialchars($order['email']) ?></dd></div>
                    <?php if (!empty($order['phone'])): ?><div><dt class="text-gray-500">Phone</dt><dd class="font-semibold"><?= htmlspecialchars($order['phone']) ?></dd></div><?php endif; ?>
                    <?php if (!empty($order['comment'])): ?><div><dt class="text-gray-500">Comments</dt><dd class="font-semibold"><?= nl2br(htmlspecialchars($order['comment'])) ?></dd></div><?php endif; ?>
                </dl>
            </section>

            <section class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Order summary</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Placed</dt><dd class="font-semibold"><?= htmlspecialchars(date('M j, Y H:i', strtotime($order['created_at']))) ?></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="font-semibold"><?= htmlspecialchars($currentStatus['label']) ?></dd></div>
                    <div class="flex justify-between border-t border-gray-200 pt-3 text-base"><dt class="font-bold">Total</dt><dd class="font-bold">€<?= number_format((float) $order['total_price'], 2) ?></dd></div>
                </dl>
            </section>

            <a href="<?= BASE_URL ?>/invoice.php?id=<?= (int) $order['id'] ?>" class="block w-full py-3 px-4 rounded-lg text-center font-semibold text-white transition" style="background-color: <?= $isAdmin ? '#2b78b8' : '#308020' ?>;">
                Download Invoice
            </a>
            <a href="<?= htmlspecialchars($backUrl) ?>" class="block w-full py-3 px-4 rounded-lg border-2 text-center font-semibold transition" style="border-color: <?= $isAdmin ? '#6f9fc8' : '#6a8a63' ?>; color: <?= $isAdmin ? '#164b83' : '#246719' ?>;">
                <?= htmlspecialchars($backLabel) ?>
            </a>
        </aside>
    </div>
</main>

<?php require_once base_path('templates/footer.php'); ?>