<?php require '../templates/header.php'; ?>

<!-- content -->

<main>
    <div class="container">

        <aside class="filter">
            <h2>Filter</h2>
            <div class="filter-group">
                <label>Price: 
                <span id="price-value">0</span> €</label>
                <input type="range" min="0" max="300" value="0"
                    oninput="document.getElementById('price-value').textContent = this.value">
            </div>
            <div class="filter-group">
                <label>RAM</label>
                <select>
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
            <div class="filter-group">
                <label>Storage</label>
                <select>
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
            <div class="filter-group">
                <label>CPU Cores</label>
                <select>
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
        <section class="catalog">
            <div class="server-card">
                    <img src="/L/course/public/assets/images/server-placeholder.png" alt="Server">                <div class="server-card-info">
                    <h2>Placeholder Server</h2>
                    <p>CPU: null, RAM: null, Storage: null</p>
                    <span class="price">€0/month</span>
                </div>
            <a href="/configurator" class="btn">Configure</a>
            </div>
            <div class="server-card">
                    <img src="/L/course/public/assets/images/delloptiplex.png" alt="Server">                <div class="server-card-info">
                    <h2>Dell OptiPlex 7050 Micro</h2>
                    <p>CPU: 4 Cores, RAM: 8 GB, Storage: 128 GB SSD</p>
                    <span class="price">€50/month</span>
                </div>
            <a href="/configurator" class="btn">Configure</a>
            </div>
    </section>
    </div>
</main>

<?php require '../templates/footer.php'; ?>