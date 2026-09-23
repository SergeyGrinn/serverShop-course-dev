<?php

$data = json_decode(file_get_contents('php://input'), true);
$consent = ($data['consent'] ?? '') === 'accepted' ? 'accepted' : 'declined';

setcookie('cookie_consent', $consent, [
    'expires' => time() + 31536000,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => false,
    'samesite' => 'Lax'
]);

header('Content-Type: application/json');

echo json_encode(['status' => 'success']);