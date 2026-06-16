<?php require base_path('templates/header.php'); ?>

<main class="p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Edit Component</h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
                <?php foreach ($errors as $error): ?>
                    <p class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST">
                <div class="flex flex-col gap-2 mb-4">
                    <label class="text-sm font-medium">Component Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($component['name']) ?>" 
                           class="border rounded px-3 py-2 text-sm" required>
                </div>

                <div class="flex flex-col gap-2 mb-4">
                    <label class="text-sm font-medium">Type</label>
                    <select name="type" class="border rounded px-3 py-2 text-sm" required>
                        <option value="cpu" <?= $component['type'] === 'cpu' ? 'selected' : '' ?>>CPU</option>
                        <option value="gpu" <?= $component['type'] === 'gpu' ? 'selected' : '' ?>>GPU</option>
                        <option value="ram" <?= $component['type'] === 'ram' ? 'selected' : '' ?>>RAM</option>
                        <option value="ssd" <?= $component['type'] === 'ssd' ? 'selected' : '' ?>>SSD</option>
                        <option value="hdd" <?= $component['type'] === 'hdd' ? 'selected' : '' ?>>HDD</option>
                    </select>
                </div>

                <div class="flex flex-col gap-2 mb-4">
                    <label class="text-sm font-medium">Specs</label>
                    <input type="text" name="value" value="<?= htmlspecialchars($component['value']) ?>" 
                           class="border rounded px-3 py-2 text-sm" required>
                </div>

                <div class="flex flex-col gap-2 mb-6">
                    <label class="text-sm font-medium">Price (€)</label>
                    <input type="number" name="price" step="0.01" value="<?= $component['price'] ?>" 
                           class="border rounded px-3 py-2 text-sm" required>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                        Save Changes
                    </button>
                    <a href="<?= BASE_URL ?>/admin/components.php" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 text-sm">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require base_path('templates/footer.php'); ?>