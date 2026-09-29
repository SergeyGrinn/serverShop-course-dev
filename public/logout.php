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

$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/index.php');
exit;
