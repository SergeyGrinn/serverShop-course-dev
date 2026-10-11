<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Controllers/OrderController.php';
require_once BASE_PATH . 'src/Helpers/Response.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	http_response_code(405);
	Response::json(['success' => false, 'message' => 'Request must be GET']);
}

$controller = new \App\Controllers\OrderController($pdo);
$controller->getApi($_GET['id'] ?? null);