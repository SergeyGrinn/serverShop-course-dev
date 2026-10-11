<main class="max-w-md mx-auto px-5 py-16">
    <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
        <?php if ($verified): ?>
            <h1 class="text-2xl font-bold mb-4">Email verified</h1>
            <p class="text-gray-600 mb-6">Your account has been successfully verified.</p>
            <a href="<?= BASE_URL ?>/login.php" class="block text-white py-2 rounded-lg font-semibold" style="background-color: #308020;">Login</a>
        <?php else: ?>
            <h1 class="text-2xl font-bold mb-4">Verification failed</h1>
            <p class="text-red-600 mb-6">This verification link is invalid.</p>
            <a href="<?= BASE_URL ?>/index.php" class="block border border-gray-300 py-2 rounded-lg font-semibold">Back to catalog</a>
        <?php endif; ?>
    </div>
</main>