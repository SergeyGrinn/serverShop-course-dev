<?php
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    require_once base_path('templates/components/cartSidebar.php');
}
require_once base_path('templates/components/cookieBanner.php');
?>
</main>
<footer class="border-t px-8 py-5 text-center text-sm">
        <p>&copy; <?= date('Y') ?> Server Catalog. All rights reserved.</p>
    </footer>

<?php if (($_SESSION['user_role'] ?? '') !== 'admin'): ?>
    <script src="<?= BASE_URL ?>/js/cart.js?v=<?= filemtime(__DIR__ . '/../public/js/cart.js') ?>"></script>
<?php endif; ?>
<script src="<?= BASE_URL ?>/js/notifications.js?v=<?= filemtime(__DIR__ . '/../public/js/notifications.js') ?>"></script>

</body>
</html>