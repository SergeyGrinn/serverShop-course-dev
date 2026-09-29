<?php
require base_path('templates/header.php');
$hasBuiltInHardware = isset($_POST['has_built_in_hardware']);
$hardwareValues = [
    'ram' => $_POST['ram'] ?? '',
    'storage' => $_POST['storage'] ?? '',
    'cpu_cores' => $_POST['cpu_cores'] ?? '',
    'gpu_vram' => $_POST['gpu_vram'] ?? '',
];
?>

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

        <form method="POST" class="notification-validation-form bg-white rounded-lg shadow p-6" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Server Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Description</label>
                <textarea name="description" class="w-full border rounded px-3 py-2 h-24" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2 font-medium">
                    <input type="checkbox" name="has_built_in_hardware" value="1" class="built-in-hardware-toggle" <?= $hasBuiltInHardware ? 'checked' : '' ?>>
                    <span>Has built-in hardware</span>
                </label>
            </div>

            <div class="built-in-hardware-fields grid grid-cols-2 gap-4 mb-6 <?= $hasBuiltInHardware ? '' : 'hidden' ?>">
                <div>
                    <label class="block text-sm font-medium mb-2" for="default-ram">RAM (GB)</label>
                    <input id="default-ram" type="number" name="ram" min="0.001" step="0.001" value="<?= htmlspecialchars((string) $hardwareValues['ram']) ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" for="default-cpu-cores">CPU cores</label>
                    <input id="default-cpu-cores" type="number" name="cpu_cores" min="1" step="1" value="<?= htmlspecialchars((string) $hardwareValues['cpu_cores']) ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" for="default-gpu-vram">GPU memory (GB)</label>
                    <input id="default-gpu-vram" type="number" name="gpu_vram" min="1" step="1" value="<?= htmlspecialchars((string) $hardwareValues['gpu_vram']) ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" for="default-storage">Storage (GB)</label>
                    <input id="default-storage" type="number" name="storage" min="1" step="1" value="<?= htmlspecialchars((string) $hardwareValues['storage']) ?>" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Image</label>
                <div class="custom-file-picker">
                    <input id="server-image-create" type="file" name="image" accept="image/*" class="custom-file-input">
                    <label for="server-image-create" class="custom-file-button">Choose File</label>
                    <span class="custom-file-name">No file chosen</span>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Base Price (€)</label>
                <input type="number" name="base_price" step="0.01" min="0" value="<?= htmlspecialchars($_POST['base_price'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Create Server
                </button>
                <a href="servers.php" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<?php require base_path('templates/footer.php'); ?>
