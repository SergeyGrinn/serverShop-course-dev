<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Catalogue</title>
    <link rel="stylesheet" href="/L/course/public/css/main.css">
    <script src="/L/course/public/js/knockout.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <header class="border-b px-8 h-16 flex items-center" style="background-color: #cbe3c5; border-color: #6a8a63;">
        <nav class="w-full flex items-center justify-between">
            <a href="" class="font-bold text-lg">Server Catalog</a>

            <ul class="flex gap-8 list-none">
                <li><a href="/L/course/public/index.php" class="no-underline text-gray-700 hover:text-green-700">Home</a></li>
                <li><a href="/L/course/public/servers.php" class="no-underline text-gray-700 hover:text-green-700">Servers</a></li>
                <li><a href="/L/course/public/contact.php" class="no-underline text-gray-700 hover:text-green-700">Contact</a></li>
            </ul>

            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-700">Hello, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <div class="flex gap-2">
                            <a href="/L/course/public/admin/servers.php" class="no-underline text-gray-700 hover:text-green-700 font-semibold">Servers</a>
                            <a href="/L/course/public/admin/components.php" class="no-underline text-gray-700 hover:text-green-700 font-semibold">Components</a>
                        </div>
                    <?php endif; ?>
                    <a href="/L/course/public/logout.php" class="no-underline text-gray-700 hover:text-green-700">Logout</a>
                <?php else: ?>
                    <a href="/L/course/public/login.php" class="no-underline text-gray-700 hover:text-green-700">Login</a>
                    <a href="/L/course/public/register.php" class="no-underline text-gray-700 hover:text-green-700">Register</a>
                <?php endif; ?>
                <button id="cart-btn" class="text-white px-4 py-2 rounded-lg" style="background-color: #308020;">Cart</button>
            </div>
        </nav>
    </header>