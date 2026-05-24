<?php require base_path('templates/header.php'); ?>

<main class="p-8">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">Server Components</h1>
        <p class="text-gray-600 mb-6">Manage components for: <span class="font-semibold"><?= htmlspecialchars($server['name']) ?></span></p>

        <div class="grid grid-cols-2 gap-8">
            <!-- Existing Components -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Current Components</h2>
                
                <form method="POST" id="componentsForm">
                    <div class="space-y-3">
                        <?php
                        $componentsByType = [];
                        foreach ($allComponents as $component) {
                            $type = $component['type'];
                            if (!isset($componentsByType[$type])) {
                                $componentsByType[$type] = [];
                            }
                            $componentsByType[$type][] = $component;
                        }
                        ?>

                        <?php foreach (['cpu', 'gpu', 'ram', 'ssd', 'hdd'] as $type): ?>
                            <?php if (isset($componentsByType[$type])): ?>
                            <div class="border-t pt-3">
                                <h3 class="font-medium text-sm mb-2"><?= $type ?></h3>
                                <div class="space-y-2">
                                    <?php foreach ($componentsByType[$type] as $component): ?>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="components[]" value="<?= $component['id'] ?>" 
                                            <?= in_array($component['id'], $serverComponents) ? 'checked' : '' ?> 
                                            class="mr-2">
                                        <span class="text-sm"><?= htmlspecialchars($componentModel->getComponentDisplay($component)) ?></span>
                                        <span class="text-gray-500 text-xs ml-2">$<?= number_format($component['price'], 2) ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-6 flex gap-2">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                            Save Components
                        </button>
                        <a href="/L/course/public/admin/servers.php" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 text-sm">
                            Back
                        </a>
                    </div>
                </form>
            </div>

            <!-- Add New Component -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Add New Component</h2>
                
                <form method="POST" action="/L/course/public/admin/components.php?action=create" id="createComponentForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Component Name</label>
                            <input type="text" name="name" class="w-full border rounded px-3 py-2 text-sm" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Type</label>
                            <select name="type" id="componentType" class="w-full border rounded px-3 py-2 text-sm" required onchange="updateComponentFields()">
                                <option value="">Select type...</option>
                                <option value="cpu">CPU</option>
                                <option value="gpu">GPU</option>
                                <option value="ram">RAM</option>
                                <option value="ssd">SSD</option>
                                <option value="hdd">HDD</option>
                            </select>
                        </div>

                        <!-- CPU Fields -->
                        <div id="cpuFields" class="hidden space-y-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Cores</label>
                                <input type="number" name="cpu_cores" class="w-full border rounded px-3 py-2 text-sm" min="1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Frequency (GHz)</label>
                                <input type="number" name="cpu_frequency" class="w-full border rounded px-3 py-2 text-sm" step="0.1" min="0.1">
                            </div>
                        </div>

                        <!-- GPU Fields -->
                        <div id="gpuFields" class="hidden">
                            <label class="block text-sm font-medium mb-1">VRAM (GB)</label>
                            <input type="number" name="gpu_vram" class="w-full border rounded px-3 py-2 text-sm" min="1" step="1">
                        </div>

                        <!-- RAM Fields -->
                        <div id="ramFields" class="hidden">
                            <label class="block text-sm font-medium mb-1">Capacity (GB)</label>
                            <input type="number" name="ram_capacity" class="w-full border rounded px-3 py-2 text-sm" min="1" step="1">
                        </div>

                        <!-- Storage Fields -->
                        <div id="storageFields" class="hidden">
                            <label class="block text-sm font-medium mb-1">Capacity (GB)</label>
                            <input type="number" name="storage_capacity" class="w-full border rounded px-3 py-2 text-sm" min="1" step="1">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Price ($)</label>
                            <input type="number" name="price" class="w-full border rounded px-3 py-2 text-sm" step="0.01" min="0" required>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                            Create Component
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
function updateComponentFields() {
    const type = document.getElementById('componentType').value;
    document.getElementById('cpuFields').classList.add('hidden');
    document.getElementById('gpuFields').classList.add('hidden');
    document.getElementById('ramFields').classList.add('hidden');
    document.getElementById('storageFields').classList.add('hidden');

    switch(type) {
        case 'cpu':
            document.getElementById('cpuFields').classList.remove('hidden');
            break;
        case 'gpu':
            document.getElementById('gpuFields').classList.remove('hidden');
            break;
        case 'ram':
            document.getElementById('ramFields').classList.remove('hidden');
            break;
        case 'ssd':
        case 'hdd':
            document.getElementById('storageFields').classList.remove('hidden');
            break;
    }
}
</script>

<?php require base_path('templates/footer.php'); ?>

