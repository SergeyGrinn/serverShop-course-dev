<?php require_once base_path('templates/header.php'); ?>

<main class="max-w-xl mx-auto px-5 py-12">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <h1 class="text-2xl font-bold mb-6">My Profile</h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
                <?php foreach ($errors as $error): ?>
                    <p class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 rounded p-3 mb-4">
                <p class="text-green-700 text-sm"><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <div class="mb-6 p-4 rounded border <?= $user['email_verified_at'] ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' ?>">
            <?php if ($user['email_verified_at']): ?>
                <p class="text-green-700 text-sm">Email verified.</p>
            <?php else: ?>
                <p class="text-yellow-800 text-sm">Email is not verified yet.</p>
            <?php endif; ?>
        </div>

        <form method="POST" class="notification-validation-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="flex flex-col gap-2 mb-4">
                <label for="profile-username" class="text-sm font-medium">Name</label>
                <input id="profile-username" type="text" name="username" required value="<?= htmlspecialchars($user['username']) ?>" class="border border-gray-300 rounded px-3 py-2">
            </div>

            <div class="flex flex-col gap-2 mb-6">
                <label for="profile-email" class="text-sm font-medium">Email</label>
                <input id="profile-email" type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>" class="border border-gray-300 rounded px-3 py-2">
            </div>

            <button type="submit" class="w-full text-white py-2 rounded-lg font-semibold" style="background-color: #308020;">Save Changes</button>
        </form>
    </div>
</main>

<?php require_once base_path('templates/footer.php'); ?>