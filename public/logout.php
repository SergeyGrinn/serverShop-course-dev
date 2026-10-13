<?php

const BASE_PATH = __DIR__ . '/../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
	http_response_code(403);
	exit('Invalid CSRF token');
}

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$sessionName = session_name();
$cookie = session_get_cookie_params();
$cookieOptions = [
	'expires' => time() - 42000,
	'path' => $cookie['path'],
	'secure' => $cookie['secure'],
	'httponly' => $cookie['httponly'],
	'samesite' => $cookie['samesite'] ?? 'Lax',
];
if ($cookie['domain'] !== '') {
	$cookieOptions['domain'] = $cookie['domain'];
}

$_SESSION = [];
setcookie($sessionName, '', $cookieOptions);
unset($_COOKIE[$sessionName]);
session_destroy();
header('Cache-Control: no-store, private, max-age=0');
header('Location: ' . BASE_URL . '/index.php');
exit;
