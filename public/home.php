<?php
const BASE_PATH = __DIR__ . '/../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';

require base_path('templates/header.php');
?>

<main class="flex items-center justify-center" style="height: calc(100vh - 64px);">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-green-600">Home Page - Coming Soon</h1>
        <p class="text-green-500 mt-4">This page is placeholder and under construction</p>
    </div>
</main>

<?php require base_path('templates/footer.php'); ?>