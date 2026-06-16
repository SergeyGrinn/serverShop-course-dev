<?php

const BASE_PATH = __DIR__ . '/../../';

require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';
require_once BASE_PATH . 'src/Models/Component.php';
require_once BASE_PATH . 'src/Middleware/Admin.php';

Admin::check();

$componentModel = new Component($pdo);
$action = $_GET['action'] ?? 'list';

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $componentModel->delete($id);
    }
    header('Location: ' . BASE_URL . '/public/admin/components.php');
    exit;
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $price = $_POST['price'] ?? 0;

    $errors = [];

    if (empty($name)) $errors[] = 'Component name is required';
    if (empty($type)) $errors[] = 'Component type is required';
    if (empty($price) || !is_numeric($price)) $errors[] = 'Valid price is required';

    $value = [];

    switch ($type) {
        case 'cpu':
            $cores = $_POST['cpu_cores'] ?? null;
            $frequency = $_POST['cpu_frequency'] ?? null;
            if (!$cores || !$frequency) $errors[] = 'CPU cores and frequency are required';
            $value = "{$cores} cores, {$frequency} GHz";
            break;
        case 'gpu':
            $vram = $_POST['gpu_vram'] ?? null;
            if (!$vram) $errors[] = 'GPU VRAM is required';
            $value = "{$vram} GB VRAM";
            break;
        case 'ram':
            $capacity = $_POST['ram_capacity'] ?? null;
            if (!$capacity) $errors[] = 'RAM capacity is required';
            $value = "{$capacity} GB";
            break;
        case 'ssd':
        case 'hdd':
            $capacity = $_POST['storage_capacity'] ?? null;
            if (!$capacity) $errors[] = 'Storage capacity is required';
            $value = "{$capacity} GB";
            break;
    }

    if (empty($errors)) {
        $componentModel->create($name, $type, $value, $price);
        header('Location: ' . BASE_URL . '/admin/components.php');
        exit;
    }
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $value = $_POST['value'] ?? '';
    $price = $_POST['price'] ?? 0;

    $errors = [];

    if (empty($name)) $errors[] = 'Component name is required';
    if (empty($type)) $errors[] = 'Component type is required';
    if (empty($price) || !is_numeric($price)) $errors[] = 'Valid price is required';

    if (empty($errors)) {
        $componentModel->update($id, $name, $type, $value, $price);
        header('Location: ' . BASE_URL . '/admin/components.php');
        exit;
    }
}

$allComponents = $componentModel->getAll();

if ($action === 'create') {
    require base_path('templates/admin/component-create.php');
} elseif ($action === 'edit') {
    $id = $_GET['id'] ?? null;
    $component = $componentModel->getById($id);
    require base_path('templates/admin/component-edit.php');
} else {
    require base_path('templates/admin/components-list.php');
}