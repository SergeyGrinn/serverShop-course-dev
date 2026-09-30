<?php

const BASE_PATH = __DIR__ . '/../../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
$controllerFile = BASE_PATH . 'src/Controllers/Admin/ComponentController.php';
require_once $controllerFile;

$controller = new ComponentController($pdo);
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'edit':
        $controller->edit($_GET['id'] ?? null);
        break;
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->delete($_POST['id'] ?? null);
        }
        $controller->index();
        break;
    default:
        $controller->index();
}