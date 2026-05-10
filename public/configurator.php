<?php

const BASE_PATH = __DIR__ . '/../';

require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Server.php';
require_once BASE_PATH . 'src/Models/Component.php';
require_once BASE_PATH . 'src/Controllers/ConfiguratorController.php';

$controller = new ConfiguratorController();
$controller->index();