<?php
require_once base_path('templates/components/cartSidebar.php');
require_once base_path('templates/components/cookieBanner.php');
?>
</main>
<footer class="border-t px-8 py-5 text-center text-sm">
        <p>&copy; <?= date('Y') ?> Server Catalog. All rights reserved.</p>
    </footer>

<script src="<?= BASE_URL ?>/js/cart.js"></script>
<script src="<?= BASE_URL ?>/js/notifications.js"></script>

</body>
</html>