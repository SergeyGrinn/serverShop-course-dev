<?php require base_path('templates/header.php'); ?>

<main class="p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Edit Server</h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
                <?php foreach ($errors as $error): ?>
                    <p class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white rounded-lg shadow p-6" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Server Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $server['name']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Description</label>
                <textarea name="description" class="w-full border rounded px-3 py-2 h-24" required><?= htmlspecialchars($_POST['description'] ?? $server['description']) ?></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Image</label>
                    <?php if ($server['image']): ?>
                        <img src="../../assets/images/<?= htmlspecialchars($server['image']) ?>" 
                        class="w-32 h-32 object-cover rounded mb-2">
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Leave empty to keep current image</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Base Price (€)</label>
                <input type="number" name="base_price" step="0.01" value="<?= htmlspecialchars($_POST['base_price'] ?? $server['base_price']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="available" <?= (isset($_POST['available']) ? $_POST['available'] : $server['available']) ? 'checked' : '' ?> class="mr-2">
                    <span class="text-sm font-medium">Available for sale</span>
                </label>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Update Server
                </button>
                <a href="servers.php" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<?php require base_path('templates/footer.php'); ?>
