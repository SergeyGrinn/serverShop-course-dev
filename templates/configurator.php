<main>
    <div class="configurator">

    <!-- Server Info -->
        <div class="configurator__server">
            <img src="/L/course/public/assets/images/<?= htmlspecialchars($server['image']) ?>" alt="<?= htmlspecialchars($server['name']) ?>">
            <div>
                <h1><?= htmlspecialchars($server['name']) ?></h1>
                <p><?= htmlspecialchars($server['description']) ?></p>
            </div>
        </div>

    <!-- Hidden base price -->
        <input type="hidden" id="base-price" value="<?= $server['base_price'] ?>">

    <!-- Components -->
        <div class="configurator__filters" id="configurator-app">

    <!-- CPU -->
        <?php if (isset($grouped['cpu'])): ?>
            <div class="option-group">
                <h3>CPU</h3>
                <?php foreach ($grouped['cpu'] as $cpu): ?>
                    <label class="option <?= !$cpu['available'] ? 'option--disabled' : '' ?>">
                        <input type="radio" name="cpu" value="<?= $cpu['id'] ?>"
                            data-price="<?= $cpu['price'] ?>"
                            data-bind="click: function() { $root.selectComponent('cpu', <?= $cpu['price'] ?>) }"
                            <?= !$cpu['available'] ? 'disabled' : '' ?>>
                        <?= htmlspecialchars($cpu['value']) ?> — <?= number_format($cpu['price'], 0, '.', ' ') ?> €
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- RAM -->
        <?php if (isset($grouped['ram'])): ?>
            <div class="option-group">
                <h3>RAM</h3>
                <?php foreach ($grouped['ram'] as $ram): ?>
                    <label class="option <?= !$ram['available'] ? 'option--disabled' : '' ?>">
                        <input type="radio" name="ram" value="<?= $ram['id'] ?>"
                            data-price="<?= $ram['price'] ?>"
                            data-bind="click: function() { $root.selectComponent('ram', <?= $ram['price'] ?>) }"
                            <?= !$ram['available'] ? 'disabled' : '' ?>>
                        <?= htmlspecialchars($ram['value']) ?> — <?= number_format($ram['price'], 0, '.', ' ') ?> €
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- SSD -->
        <?php if (isset($grouped['ssd'])): ?>
            <div class="option-group">
                <h3>SSD</h3>
                <?php foreach ($grouped['ssd'] as $ssd): ?>
                    <label class="option <?= !$ssd['available'] ? 'option--disabled' : '' ?>">
                        <input type="radio" name="ssd" value="<?= $ssd['id'] ?>"
                            data-price="<?= $ssd['price'] ?>"
                            data-bind="click: function() { $root.selectComponent('ssd', <?= $ssd['price'] ?>) }"                            <?= !$ssd['available'] ? 'disabled' : '' ?>>
                        <?= htmlspecialchars($ssd['value']) ?> — <?= number_format($ssd['price'], 0, '.', ' ') ?> €
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        

    <!-- Total Price -->
        <div class="configurator__sum">
            <h2>Total: <span data-bind="text: totalPrice"></span> €</h2>
            <button class="btn" id="add-to-cart">Add to Cart</button>
            </div>
        </div>
    </div>
</main>

<script src="/L/course/public/js/configurator.js"></script>