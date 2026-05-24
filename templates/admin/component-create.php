<?php require base_path('templates/header.php'); ?>

<main class="p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Add Component</h1>

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
                    <input type="text" name="name" class="border rounded px-3 py-2 text-sm" required>
                </div>

                <div class="flex flex-col gap-2 mb-4">
                    <label class="text-sm font-medium">Type</label>
                    <select name="type" id="componentType" class="border rounded px-3 py-2 text-sm" required onchange="updateFields()">
                        <option value="">Select type...</option>
                        <option value="cpu">CPU</option>
                        <option value="gpu">GPU</option>
                        <option value="ram">RAM</option>
                        <option value="ssd">SSD</option>
                        <option value="hdd">HDD</option>
                    </select>
                </div>

                <div id="cpuFields" class="hidden flex flex-col gap-4 mb-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Cores</label>
                        <input type="number" name="cpu_cores" class="border rounded px-3 py-2 text-sm" min="1">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Frequency (GHz)</label>
                        <input type="number" name="cpu_frequency" class="border rounded px-3 py-2 text-sm" step="0.1" min="0.1">
                    </div>
                </div>

                <div id="gpuFields" class="hidden flex flex-col gap-2 mb-4">
                    <label class="text-sm font-medium">VRAM (GB)</label>
                    <input type="number" name="gpu_vram" class="border rounded px-3 py-2 text-sm" min="1">
                </div>

                <div id="ramFields" class="hidden flex flex-col gap-2 mb-4">
                    <label class="text-sm font-medium">Capacity (GB)</label>
                    <input type="number" name="ram_capacity" class="border rounded px-3 py-2 text-sm" min="1">
                </div>

                <div id="storageFields" class="hidden flex flex-col gap-2 mb-4">
                    <label class="text-sm font-medium">Capacity (GB)</label>
                    <input type="number" name="storage_capacity" class="border rounded px-3 py-2 text-sm" min="1">
                </div>

                <div class="flex flex-col gap-2 mb-6">
                    <label class="text-sm font-medium">Price ($)</label>
                    <input type="number" name="price" class="border rounded px-3 py-2 text-sm" step="0.01" min="0" required>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                        Create Component
                    </button>
                    <a href="/L/course/public/admin/components.php" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 text-sm">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function updateFields() {
    const type = document.getElementById('componentType').value;
    document.getElementById('cpuFields').classList.add('hidden');
    document.getElementById('gpuFields').classList.add('hidden');
    document.getElementById('ramFields').classList.add('hidden');
    document.getElementById('storageFields').classList.add('hidden');

    switch(type) {
        case 'cpu': document.getElementById('cpuFields').classList.remove('hidden'); break;
        case 'gpu': document.getElementById('gpuFields').classList.remove('hidden'); break;
        case 'ram': document.getElementById('ramFields').classList.remove('hidden'); break;
        case 'ssd':
        case 'hdd': document.getElementById('storageFields').classList.remove('hidden'); break;
    }
}
</script>

<?php require base_path('templates/footer.php'); ?>