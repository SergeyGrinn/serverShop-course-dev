<?php

const BASE_PATH = __DIR__ . '/../../../';

require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Controllers/AuthController.php';
require_once BASE_PATH . 'src/Config/app.php';

$controller = new AuthController($pdo);
$controller->register();