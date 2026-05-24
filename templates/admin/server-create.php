<?php require base_path('templates/header.php'); ?>

<main class="p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Create Server</h1>

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
                <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Description</label>
                <textarea name="description" class="w-full border rounded px-3 py-2 h-24" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Image</label>
                <input type="file" name="image" accept="image/*" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Base Price ($)</label>
                <input type="number" name="base_price" step="0.01" value="<?= htmlspecialchars($_POST['base_price'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Create Server
                </button>
                <a href="/L/course/public/admin/servers.php" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<?php require base_path('templates/footer.php'); ?>
