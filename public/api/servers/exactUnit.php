<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Controllers/ServerController.php';

header('Content-Type: application/json; charset=utf-8');

(new ServerController())->exactUnitApi($_GET['server_id'] ?? $_GET['id'] ?? null);
