<?php require_once base_path('templates/header.php'); ?>

<main class="profile-main">
    <div class="profile-layout">
        <div class="flex items-end justify-between gap-4 mb-5">
            <div>
                <h1 class="text-3xl font-bold">My Profile</h1>
            </div>
            <?php if ($profile['user']['role'] !== 'admin'): ?>
                <a href="<?= BASE_URL ?>/my-orders.php" class="profile-order-link">Order History</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($profile['errors'])): ?>
            <div class="bg-red-50 border border-red-200 rounded p-4">
            <?php foreach ($profile['errors'] as $error): ?>
                    <p class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($profile['success']): ?>
            <div class="bg-green-50 border border-green-200 rounded p-4">
            <p class="text-green-700 text-sm"><?= htmlspecialchars($profile['success']) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($profile['warning']): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
            <p class="text-yellow-800 text-sm"><?= htmlspecialchars($profile['warning']) ?></p>
            </div>
        <?php endif; ?>

        <div class="grid gap-4 md:grid-cols-3">
            <section class="bg-white rounded-lg border border-gray-200 p-6 md:col-span-1">
                <h2 class="font-semibold mb-4">Account status</h2>
                <div class="mb-4">
                    <p class="text-sm text-gray-500">Email verification</p>
                    <?php if ($profile['user']['role'] === 'admin'): ?>
                        <p class="text-blue-700 font-semibold">Not required for administrators</p>
                    <?php elseif ($profile['user']['email_verified_at']): ?>
                        <p class="text-green-700 font-semibold">Verified</p>
                        <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($profile['user']['email_verified_at']) ?></p>
                    <?php else: ?>
                        <p class="text-yellow-700 font-semibold">Not verified</p>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Registration date</p>
                    <p class="font-semibold"><?= htmlspecialchars(date('M j, Y', strtotime($profile['user']['created_at']))) ?></p>
                </div>
            </section>

            <section class="bg-white rounded-lg border border-gray-200 p-6 md:col-span-2">
                <h2 class="font-semibold mb-5">Personal information</h2>
                <form method="POST" class="notification-validation-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="flex flex-col gap-2 mb-4">
                        <label for="profile-username" class="text-sm font-medium">Name</label>
                        <input id="profile-username" type="text" name="username" required value="<?= htmlspecialchars($profile['formUsername']) ?>" class="border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="flex flex-col gap-2 mb-6">
                        <label for="profile-email" class="text-sm font-medium">Email</label>
                        <input id="profile-email" type="email" name="email" required value="<?= htmlspecialchars($profile['formEmail']) ?>" class="border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="flex flex-col gap-2 mb-6">
                        <label for="profile-mobile-phone" class="text-sm font-medium">Mobile phone</label>
                        <input id="profile-mobile-phone" type="tel" name="mobile_phone" maxlength="30" value="<?= htmlspecialchars($profile['formMobilePhone']) ?>" class="border border-gray-300 rounded px-3 py-2">
                    </div>

                    <button type="submit" class="text-white px-5 py-2 rounded-lg font-semibold" style="background-color: #308020;">Save Changes</button>
                </form>
            </section>

            <section class="bg-white rounded-lg border border-gray-200 p-6 md:col-span-3">
                <h2 class="font-semibold mb-5">Change password</h2>
                <form method="POST" class="notification-validation-form grid gap-4 md:grid-cols-3" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="profile_action" value="change_password">

                    <div class="flex flex-col gap-2">
                        <label for="current-password" class="text-sm font-medium">Current password</label>
                        <input id="current-password" type="password" name="current_password" required autocomplete="current-password" class="border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="new-password" class="text-sm font-medium">New password</label>
                        <input id="new-password" type="password" name="new_password" required minlength="8" autocomplete="new-password" class="border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="confirm-new-password" class="text-sm font-medium">Confirm new password</label>
                        <input id="confirm-new-password" type="password" name="confirm_password" required minlength="8" autocomplete="new-password" class="border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="md:col-span-3">
                        <button type="submit" class="text-white px-5 py-2 rounded-lg font-semibold" style="background-color: #308020;">Update Password</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</main>

<?php require_once base_path('templates/footer.php'); ?>