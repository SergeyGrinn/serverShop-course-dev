<?php
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$errors = $errors ?? [];
$hasError = function (string $message) use ($submitted, $errors) {
    return $submitted && in_array($message, $errors, true);
};
?>

<main class="max-w-md mx-auto px-5 py-16">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <h1 class="text-2xl font-bold mb-6">Register</h1>

        <form method="POST" class="notification-validation-form" novalidate>
            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm font-medium">Name</label>
                <input type="text" name="name" data-validation-message="Name is required" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       class="border border-gray-300 rounded px-3 py-2 text-sm">
                <?php if ($hasError('Name is required')): ?>
                    <p class="text-red-600 text-sm">Name is required</p>
                <?php endif; ?>
            </div>
            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" data-validation-message="Email is required" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       class="border border-gray-300 rounded px-3 py-2 text-sm">
                <?php foreach (['Email is required', 'Invalid email format', 'Email must have a valid domain extension', 'Email already taken'] as $error): ?>
                    <?php if ($hasError($error)): ?>
                        <p class="text-red-600 text-sm"><?= $error === 'Email is required' ? 'Email is required' : 'Invalid email format' ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="flex flex-col gap-2 mb-4">
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" data-validation-message="Password is required"
                       class="border border-gray-300 rounded px-3 py-2 text-sm">
                <?php if ($hasError('Password is required')): ?>
                    <p class="text-red-600 text-sm">Password is required</p>
                <?php endif; ?>
            </div>
            <div class="flex flex-col gap-2 mb-6">
                <label class="text-sm font-medium">Confirm Password</label>
                <input type="password" name="confirm_password" data-validation-message="Confirmation password is required"
                       class="border border-gray-300 rounded px-3 py-2 text-sm">
                <?php foreach (['Confirmation password is required', 'Passwords do not match'] as $error): ?>
                    <?php if ($hasError($error)): ?>
                        <p class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
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