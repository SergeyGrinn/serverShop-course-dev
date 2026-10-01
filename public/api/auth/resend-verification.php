<?php

const BASE_PATH = __DIR__ . '/../../../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/User.php';
require_once BASE_PATH . 'src/Services/Mailer.php';
require_once BASE_PATH . 'src/Helpers/Response.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    Response::json(['success' => false, 'message' => 'Request must be POST']);
}

if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    Response::json(['success' => false, 'message' => 'Invalid CSRF token']);
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    Response::json(['success' => false, 'message' => 'Authentication required']);
}

$userModel = new User($pdo);
$user = $userModel->findById($_SESSION['user_id']);

if (!$user) {
    http_response_code(404);
    Response::json(['success' => false, 'message' => 'User not found']);
}

if ($user['role'] === 'admin') {
    Response::json(['success' => true, 'message' => 'Email verification is not required for administrator accounts']);
}

if ($user['email_verified_at'] !== null) {
    Response::json(['success' => true, 'message' => 'Email is already verified']);
}

$token = bin2hex(random_bytes(32));
$userModel->setVerificationToken($user['id'], $token);

if (!Mailer::sendVerificationEmail($user['email'], $user['username'], $token)) {
    http_response_code(500);
    Response::json([
        'success' => false,
        'message' => 'Email could not be sent. Check the local mail configuration.'
    ]);
}

Response::json([
    'success' => true,
    'message' => 'Verification email sent'
]);
