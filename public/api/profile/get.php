<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Controllers/ProfileController.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	http_response_code(405);
	require_once BASE_PATH . 'src/Helpers/Response.php';
	Response::json(['success' => false, 'message' => 'Request must be GET']);
}

(new ProfileController($pdo, false))->getApi();
