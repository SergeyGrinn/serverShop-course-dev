<?php require base_path('templates/header.php'); ?>

<main class="p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Manage Servers</h1>
            <a href="servers.php?action=create" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Add New Server
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">ID</th>
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">Price</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servers as $server): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3"><?= $server['id'] ?></td>
                        <td class="px-6 py-3">
                            <a href="<?= BASE_URL ?>/configurator.php?id=<?= $server['id'] ?>"
                            target="_blank"
                            class="hover:text-green-700 hover:underline">
                                <?= htmlspecialchars($server['name']) ?>
                            </a>
                        </td>
                        <td class="px-6 py-3">€<?= number_format($server['base_price'], 2) ?></td>
                        <td class="px-6 py-3">
                            <span class="px-3 py-1 rounded text-white text-sm" style="background-color: <?= $server['available'] ? '#22c55e' : '#ef4444' ?>;">
                                <?= $server['available'] ? 'Available' : 'Unavailable' ?>
                            </span>
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="servers.php?action=components&id=<?= $server['id'] ?>" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                Components
                            </a>
                            <a href="servers.php?action=edit&id=<?= $server['id'] ?>" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                Edit
                            </a>
                            <form method="POST" action="servers.php?action=delete" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="id" value="<?= $server['id'] ?>">
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
