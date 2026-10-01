<?php
require_once __DIR__ . '/../src/Core/bootstrap.php';
require_once __DIR__ . '/../src/Core/functions.php';

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
    const CSRF_TOKEN = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>';
    
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
                    <span class="text-gray-700">Hello, <a href="<?= BASE_URL ?>/profile.php" class="profile-name-link"><?= htmlspecialchars($_SESSION['user_name']) ?></a></span>
                    <form method="POST" action="<?= BASE_URL ?>/logout.php" class="inline-flex">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="no-underline text-gray-700 hover:text-green-700">Logout</button>
                    </form>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <div class="admin-pages-menu">
                            <button type="button" class="admin-pages-trigger" aria-haspopup="true">
                                Manage
                            </button>
                            <div class="admin-pages-overlay" role="menu">
                                <a href="<?= BASE_URL ?>/admin/servers.php" role="menuitem">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="13" rx="2"></rect>
                                        <path d="M8 21h8M12 17v4M7 8h10M7 12h6"></path>
                                    </svg>
                                    <strong>Servers</strong>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/components.php" role="menuitem">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="5" y="5" width="14" height="14" rx="2"></rect>
                                        <path d="M9 2v3m6-3v3M9 19v3m6-3v3M2 9h3m-3 6h3m14-6h3m-3 6h3M9 9h6v6H9z"></path>
                                    </svg>
                                    <strong>Components</strong>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/orders.php" role="menuitem">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M7 3h8l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path>
                                        <path d="M15 3v5h5M8 13l2.5 2.5L16 10"></path>
                                    </svg>
                                    <strong>Orders</strong>
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
    <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') !== 'admin' && empty($_SESSION['email_verified_at'])): ?>
        <div class="verification-notice">
            <span>Verify your account to complete registration and be able to place an order.</span>
            <button id="resend-verification" type="button" data-csrf-token="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                Resend verification email
            </button>
        </div>
    <?php endif; ?>
    <main>
<script>
document.getElementById('theme-toggle').addEventListener('click', function () {
    const isDark = document.documentElement.classList.toggle('dark-theme');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    this.setAttribute('aria-label', isDark ? 'Switch to light theme' : 'Switch to dark theme');
    this.setAttribute('title', isDark ? 'Switch to light theme' : 'Switch to dark theme');
});
</script>
<?php if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') !== 'admin' && empty($_SESSION['email_verified_at'])): ?>
<script>
document.getElementById('resend-verification').addEventListener('click', async function () {
    this.disabled = true;

    try {
        const response = await fetch('<?= BASE_URL ?>/api/auth/resend-verification.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({csrf_token: this.dataset.csrfToken})
        });
        const data = await response.json();
        showNotification(data.message, data.success ? 'success' : 'error');
    } catch (error) {
        showNotification(error.message, 'error');
    } finally {
        this.disabled = false;
    }
});
</script>
<?php endif; ?>