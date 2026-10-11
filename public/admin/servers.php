<?php

const BASE_PATH = __DIR__ . '/../../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Server.php';
require_once BASE_PATH . 'src/Controllers/Admin/ServerController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postMaxSize = trim((string) ini_get('post_max_size'));
    if (preg_match('/^(\d+(?:\.\d+)?)\s*([KMG])?$/i', $postMaxSize, $matches)) {
        $unitMultiplier = match (strtoupper($matches[2] ?? '')) {
            'G' => 1024 ** 3,
            'M' => 1024 ** 2,
            'K' => 1024,
            default => 1,
        };
        $postMaxBytes = (int) ((float) $matches[1] * $unitMultiplier);

        if ($postMaxBytes > 0 && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $postMaxBytes) {
            http_response_code(413);
            exit('PHP rejected the request before the file type could be checked. Increase post_max_size to inspect this upload.');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

$action = $_GET['action'] ?? 'list';
$controller = new ServerController($pdo);

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'edit':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller->edit($id);
        }
        break;
    case 'components':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller->components($id);
        }
        break;
    case 'delete':
        $id = $_POST['id'] ?? null;
        if ($id) {
            $controller->delete($id);
        }
        break;
    default:
        $controller->index();
        break;
    case 'toggle-availability':
        $id = $_POST['id'] ?? null;

        if ($id) {
            $controller->toggleAvailability($id);
        }
        break;
}
