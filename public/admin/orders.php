<?php

const BASE_PATH = __DIR__ . '/../../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Controllers/Admin/OrderController.php';

$controller = new OrderController($pdo);
$controller->index();
