<main class="max-w-md mx-auto px-5 py-16">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <h1 class="text-2xl font-bold mb-6">Register</h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
                <?php foreach ($errors as $error): ?>
                    <p class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm font-medium">Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       class="border border-gray-300 rounded px-3 py-2 text-sm">
            </div>
            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       class="border border-gray-300 rounded px-3 py-2 text-sm">
            </div>
            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password"
                       class="border border-gray-300 rounded px-3 py-2 text-sm">
            </div>
            <div class="flex flex-col gap-2 mb-6">
                <label class="text-sm font-medium">Confirm Password</label>
                <input type="password" name="confirm_password"
                       class="border border-gray-300 rounded px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full text-white py-2 rounded-lg font-semibold" style="background-color: #308020;">
                Register
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-4">
            Already have an account? <a href="login.php" class="text-green-700 hover:underline">Login</a>
        </p>
    </div>
</main>