<main class="max-w-6xl mx-auto px-5 py-8" id="catalog-app">
    <div class="flex gap-8">

        <!-- Filters -->
        <aside class="w-64 flex-shrink-0 bg-white rounded-lg border border-gray-200 p-5 h-fit">
            <h2 class="text-lg font-bold mb-4">Filter</h2>

            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm text-gray-600">Price</label>
                <div class="flex gap-2 items-center">
                    <input type="number" placeholder="From" min="0"
                        data-bind="value: priceFrom, valueUpdate: 'afterkeydown'"
                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                    <span class="text-gray-400">—</span>
                    <input type="number" placeholder="To" min="0"
                        data-bind="value: priceTo, valueUpdate: 'afterkeydown'"
                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                </div>
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm text-gray-600">RAM</label>
                <select data-bind="value: ram" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="">Any</option>
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
                <select data-bind="value: storage" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="">Any</option>
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
                <select data-bind="value: cpuCores" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="">Any</option>
                    <option value="1">1 Cores</option>
                    <option value="2">2 Cores</option>
                    <option value="4">3 Cores</option>
                    <option value="4">4 Cores</option>
                    <option value="8">5 Cores</option>
                    <option value="8">6 Cores</option>
                    <option value="8">7 Cores</option>
                    <option value="8">8 Cores</option>
                    <option value="16">16 Cores</option>
                    <option value="32">32 Cores</option>
                </select>
            </div>
        </aside>

        <!-- Catalog -->
        <section class="flex-1 content-start">
            <div data-bind="if: servers().length > 0" class="grid grid-cols-2 gap-5">
                <!-- ko foreach: servers -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col gap-3">
                    <img data-bind="attr: { src: '<?= BASE_URL ?>/assets/images/' + image, alt: name }"
                         class="w-full h-44 object-cover rounded-md bg-gray-100">
                    <div>
                        <h2 class="text-lg font-semibold" data-bind="text: name"></h2>
                        <p class="text-sm text-gray-500 mt-1">
                            CPU: <span data-bind="text: default_cpu_cores || 'N/A'"></span> cores | 
                            RAM: <span data-bind="text: default_ram ? default_ram + ' GB' : 'N/A'"></span> | 
                            Storage: <span data-bind="text: default_storage ? default_storage + ' GB' : 'N/A'"></span>
                        </p>
                        <span class="text-green-700 font-bold text-lg mt-2 block" data-bind="text: 'from ' + parseFloat(base_price).toFixed(0) + ' €'"></span>
                    </div>
                    <a data-bind="attr: { href: '<?= BASE_URL ?>/configurator.php?id=' + id }"
                       class="block text-center py-2 px-4 rounded-lg text-white mt-auto" style="background-color: #308020;">Configure</a>
                </div>
                <!-- /ko -->
            </div>
        </section>

    </div>
</main>

<script>
function CatalogViewModel() {
    var self = this;

    self.servers = ko.observableArray([]);
    self.loading = ko.observable(false);
    self.priceFrom = ko.observable('');
    self.priceTo = ko.observable('');
    self.ram = ko.observable('');
    self.storage = ko.observable('');
    self.cpuCores = ko.observable('');

    self.loadServers = function() {
        self.loading(false);

        var params = new URLSearchParams();
        if (self.priceFrom()) params.append('price_from', self.priceFrom());
        if (self.priceTo()) params.append('price_to', self.priceTo());
        if (self.ram()) params.append('ram', self.ram());
        if (self.storage()) params.append('storage', self.storage());
        if (self.cpuCores()) params.append('cpu_cores', self.cpuCores());
        

        fetch(BASE_URL + '/api/servers/list.php?' + params.toString())
            .then(res => res.json())
            .then(data => {
                self.servers(data.servers);
                self.loading(false);
            });
    };

    ko.computed(function() {
        self.priceFrom();
        self.priceTo();
        self.ram();
        self.storage();
        self.cpuCores();
    }).extend({ rateLimit: 500 });

    self.priceFrom.subscribe(self.loadServers);
    self.priceTo.subscribe(self.loadServers);
    self.ram.subscribe(self.loadServers);
    self.storage.subscribe(self.loadServers);
    self.cpuCores.subscribe(self.loadServers);

    self.loadServers();
}

ko.applyBindings(new CatalogViewModel(), document.getElementById('catalog-app'));
</script>