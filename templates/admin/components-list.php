<?php require base_path('templates/header.php'); ?>

<main class="p-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Components Library</h1>
            <a href="<?= BASE_URL ?>/admin/components.php?action=create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add Component
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
                <?php foreach ($errors as $error): ?>
                    <p class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Type</th>
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">Specs</th>
                        <th class="px-6 py-3 text-left">Price</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allComponents as $component): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <span class="px-3 py-1 rounded bg-blue-100 text-blue-800 text-sm">
                                <?= $component['type'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-3"><?= htmlspecialchars($component['name']) ?></td>
                        <td class="px-6 py-3 text-sm text-gray-600"><?= htmlspecialchars($component['value']) ?></td>
                        <td class="px-6 py-3">€<?= number_format($component['price'], 2) ?></td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="<?= BASE_URL ?>/admin/components.php?action=edit&id=<?= $component['id'] ?>" 
                               class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                Edit
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/components.php?action=delete" 
                                  onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="id" value="<?= $component['id'] ?>">
                                <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require base_path('templates/footer.php'); ?>