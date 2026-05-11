<main class="max-w-6xl mx-auto px-5 py-8">
    <div class="flex gap-8">

        <!-- Filters -->
        <aside class="w-64 flex-shrink-0 bg-white rounded-lg border border-gray-200 p-5 h-fit">
            <h2 class="text-lg font-bold mb-4">Filter</h2>

        <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm text-gray-600">Price</label>
                <div class="flex gap-2 items-center">
                    <input type="number" id="price-from" placeholder="From" min="0"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                    <span class="text-gray-400">—</span>
                    <input type="number" id="price-to" placeholder="To" min="0"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                </div>
        </div>

            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm text-gray-600">RAM</label>
                <select class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="0">Any</option>
                    <option value="512">512 MB</option>
                    <option value="1">1 GB</option>
                    <option value="2">2 GB</option>
                    <option value="4">4 GB</option>
                    <option value="8">8 GB</option>
                    <option value="16">16 GB</option>
                    <option value="32">32 GB</option>
                </select>
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm text-gray-600">Storage</label>
                <select class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="0">Any</option>
                    <option value="16">16 GB</option>
                    <option value="32">32 GB</option>
                    <option value="64">64 GB</option>
                    <option value="128">128 GB</option>
                    <option value="256">256 GB</option>
                    <option value="512">512 GB</option>
                    <option value="1024">1 TB</option>
                    <option value="2048">2 TB</option>
                    <option value="4096">4 TB</option>
                </select>
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm text-gray-600">CPU Cores</label>
                <select class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                    <option>Any</option>
                    <option>1 cores</option>
                    <option>2 cores</option>
                    <option>3 cores</option>
                    <option>4 cores</option>
                    <option>8 cores</option>
                    <option>16 cores</option>
                    <option>32 cores</option>
                </select>
            </div>
        </aside>

        <!-- Catalog -->
        <section class="flex-1 grid grid-cols-2 gap-5 content-start">
            <?php foreach ($servers as $server): ?>
                <div class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col gap-3">
                    <img src="/L/course/public/assets/images/<?= htmlspecialchars($server['image']) ?>"
                         alt="<?= htmlspecialchars($server['name']) ?>"
                         class="w-full h-44 object-cover rounded-md bg-gray-100">
                    <div>
                        <h2 class="text-lg font-semibold"><?= htmlspecialchars($server['name']) ?></h2>
                        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($server['description']) ?></p>
                        <span class="text-green-700 font-bold text-lg mt-2 block">from <?= number_format($server['base_price'], 0, '.', ' ') ?> €</span>
                    </div>
                    <a href="/L/course/public/configurator.php?id=<?= $server['id'] ?>"
                       class="block text-center py-2 px-4 rounded-lg text-white mt-auto" style="background-color: #308020;">Configure</a>
                </div>
            <?php endforeach; ?>
        </section>

    </div>
</main>