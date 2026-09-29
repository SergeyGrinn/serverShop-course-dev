<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Core/functions.php';

header('Content-Type: application/json');

if (!csrf_verify($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$consent = ($data['consent'] ?? '') === 'accepted' ? 'accepted' : 'declined';

setcookie('cookie_consent', $consent, [
    'expires' => time() + 31536000,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => false,
    'samesite' => 'Lax'
]);

echo json_encode(['success' => true]);