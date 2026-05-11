<main class="max-w-6xl mx-auto px-5 py-8">
    <div class="flex gap-8">

    <!-- Server Info -->
        <div class="w-80 flex-shrink-0">
            <img src="/L/course/public/assets/images/<?= htmlspecialchars($server['image']) ?>"
                 alt="<?= htmlspecialchars($server['name']) ?>"
                 class="w-full rounded-lg border border-gray-200">
            <h1 class="text-xl font-bold mt-4"><?= htmlspecialchars($server['name']) ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($server['description']) ?></p>
        </div>

    <!-- Hidden base price -->
        <input type="hidden" id="base-price" value="<?= $server['base_price'] ?>">

    <!-- Components + Total -->
        <div class="flex-1" id="configurator-app">

    <!-- CPU -->
        <?php if (isset($grouped['cpu'])): ?>
            <div class="bg-white rounded-lg border border-gray-200 p-5 mb-4">
                <h3 class="font-semibold mb-3">CPU</h3>
                <?php foreach ($grouped['cpu'] as $cpu): ?>
                    <label class="flex items-center gap-3 py-2 cursor-pointer <?= !$cpu['available'] ? 'opacity-40' : '' ?>">
                        <input type="radio" name="cpu" value="<?= $cpu['id'] ?>"
                            data-price="<?= $cpu['price'] ?>"
                            data-bind="click: function() { $root.selectComponent('cpu', <?= $cpu['price'] ?>) }"
                            <?= !$cpu['available'] ? 'disabled' : '' ?>>
                        <span class="flex-1"><?= htmlspecialchars($cpu['value']) ?></span>
                        <span class="text-green-700 font-semibold">+<?= number_format($cpu['price'], 0, '.', ' ') ?> €</span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- RAM -->
        <?php if (isset($grouped['ram'])): ?>
            <div class="bg-white rounded-lg border border-gray-200 p-5 mb-4">
                <h3 class="font-semibold mb-3">RAM</h3>
                <?php foreach ($grouped['ram'] as $ram): ?>
                    <label class="flex items-center gap-3 py-2 cursor-pointer <?= !$ram['available'] ? 'opacity-40' : '' ?>">
                        <input type="radio" name="ram" value="<?= $ram['id'] ?>"
                            data-price="<?= $ram['price'] ?>"
                            data-bind="click: function() { $root.selectComponent('ram', <?= $ram['price'] ?>) }"
                            <?= !$ram['available'] ? 'disabled' : '' ?>>
                        <span class="flex-1"><?= htmlspecialchars($ram['value']) ?></span>
                        <span class="text-green-700 font-semibold">+<?= number_format($ram['price'], 0, '.', ' ') ?> €</span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- External SSD -->
        <?php if (isset($grouped['ssd'])): ?>
            <div class="bg-white rounded-lg border border-gray-200 p-5 mb-4">
                <h3 class="font-semibold mb-3">External SSD</h3>
                <?php foreach ($grouped['ssd'] as $ssd): ?>
                    <label class="flex items-center gap-3 py-2 cursor-pointer <?= !$ssd['available'] ? 'opacity-40' : '' ?>">
                        <input type="radio" name="ssd" value="<?= $ssd['id'] ?>"
                            data-price="<?= $ssd['price'] ?>"
                            data-bind="click: function() { $root.selectComponent('ssd', <?= $ssd['price'] ?>) }"
                            <?= !$ssd['available'] ? 'disabled' : '' ?>>
                        <span class="flex-1"><?= htmlspecialchars($ssd['value']) ?></span>
                        <span class="text-green-700 font-semibold">+<?= number_format($ssd['price'], 0, '.', ' ') ?> €</span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- Total Price -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 flex items-center justify-between">
            <h2 class="text-xl font-bold">Total: <span data-bind="text: totalPrice"></span> €</h2>
            <button class="text-white px-6 py-2 rounded-lg font-semibold" style="background-color: #308020;" id="add-to-cart">Add to Cart</button>
        </div>

        </div>
    </div>
</main>

<script src="/L/course/public/js/configurator.js"></script>