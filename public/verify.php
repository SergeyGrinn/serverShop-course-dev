<?php

const BASE_PATH = __DIR__ . '/../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = $_GET['token'] ?? '';
$verified = false;

if (is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token)) {
    $userModel = new User($pdo);
    $user = $userModel->findByVerificationToken($token);

    if ($user) {
        $verified = $userModel->verifyEmail($user['id']);

        if ($verified && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $user['id']) {
            $_SESSION['email_verified_at'] = date('Y-m-d H:i:s');
        }
    }
}

require_once BASE_PATH . 'templates/header.php';
require_once BASE_PATH . 'templates/verify.php';
require_once BASE_PATH . 'templates/footer.php';
