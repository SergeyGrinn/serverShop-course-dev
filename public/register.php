<?php

const BASE_PATH = __DIR__ . '/../';

require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/User.php';
require_once BASE_PATH . 'src/Models/Cart.php';
require_once BASE_PATH . 'src/Controllers/AuthController.php';

$controller = new AuthController($pdo);
$controller->register();
