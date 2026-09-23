<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Catalogue</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/css/tailwind-output.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/main.css">
    <?php if ($isAdmin): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/admin.css">
    <?php endif; ?>
    <script src="<?= BASE_URL ?>/js/knockout.js"></script>
    
    <script>
    const BASE_URL = '<?= BASE_URL ?>';
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark-theme');
    }
    </script>
</head>
<body class="site-body <?= $isAdmin ? 'admin-page' : '' ?>">
    <header class="border-b px-8 h-16 flex items-center" style="background-color: #cbe3c5; border-color: #6a8a63;">
        <nav class="w-full flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="font-bold text-lg">Server Catalog</span>
            </div>

            <ul class="flex gap-8 list-none">
                <li><a href="<?= BASE_URL ?>/home.php" class="no-underline text-gray-700 hover:text-green-700">Home</a></li>
                <li><a href="<?= BASE_URL ?>/index.php" class="no-underline text-gray-700 hover:text-green-700">Servers</a></li>
                <li><a href="<?= BASE_URL ?>/contact.php" class="no-underline text-gray-700 hover:text-green-700">Contact</a></li>
            </ul>

            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-700">Hello, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <a href="<?= BASE_URL ?>/logout.php" class="no-underline text-gray-700 hover:text-green-700">Logout</a>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <div class="admin-pages-menu">
                            <button type="button" class="admin-pages-trigger" aria-haspopup="true">
                                Manage
                            </button>
                            <div class="admin-pages-overlay" role="menu">
                                <a href="<?= BASE_URL ?>/admin/servers.php" role="menuitem">
                                    <strong>Servers</strong>
                                    <span>Manage servers</span>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/components.php" role="menuitem">
                                    <strong>Components</strong>
                                    <span>Manage hardware library</span>
                                </a>
                        </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="no-underline text-gray-700 hover:text-green-700">Login</a>
                    <a href="<?= BASE_URL ?>/register.php" class="no-underline text-gray-700 hover:text-green-700">Register</a>
                <?php endif; ?>
                <button id="theme-toggle" type="button" class="theme-toggle" aria-label="Switch to dark theme" title="Switch to dark theme">
                    <svg class="theme-icon theme-icon-moon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M21 12.8A8.5 8.5 0 1 1 11.2 3A6.5 6.5 0 0 0 21 12.8Z"></path>
                    </svg>
                    <svg class="theme-icon theme-icon-sun" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>
                    </svg>
                </button>
                <button id="cart-btn" class="text-white px-4 py-2 rounded-lg" style="background-color: #308020;">Cart</button>
            </div>
        </nav>
    </header>
    <main>
<script>
document.getElementById('theme-toggle').addEventListener('click', function () {
    const isDark = document.documentElement.classList.toggle('dark-theme');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    this.setAttribute('aria-label', isDark ? 'Switch to light theme' : 'Switch to dark theme');
    this.setAttribute('title', isDark ? 'Switch to light theme' : 'Switch to dark theme');
});
</script>