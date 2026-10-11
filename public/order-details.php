<?php

const BASE_PATH = __DIR__ . '/../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Controllers/OrderController.php';

$controller = new \App\Controllers\OrderController($pdo);
$controller->details($_GET['id'] ?? null);