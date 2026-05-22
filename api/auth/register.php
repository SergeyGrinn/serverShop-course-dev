<?php

const BASE_PATH = __DIR . '/../';

require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php.php';
require_once BASE_PATH . 'src/Controllers/AuthController.php';

$controller = new AuthController($pdo);
$controller->register();