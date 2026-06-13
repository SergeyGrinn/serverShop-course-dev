<?php

require_once base_path('src/Models/Server.php');
require_once base_path('src/Models/Component.php');
require_once base_path('src/Models/ServerComponent.php');
require_once base_path('src/Middleware/Admin.php');

class ServerController {
    private $serverModel;
    private $componentModel;
    private $serverComponentModel;
    private $pdo;

    public function __construct($pdo) {
        Admin::check();
        $this->pdo = $pdo;
        $this->serverModel = new Server($pdo);
        $this->componentModel = new Component($pdo);
        $this->serverComponentModel = new ServerComponent($pdo);
    }

    public function index() {
        $servers = $this->serverModel->getAllServersAdmin();
        require base_path('templates/admin/servers-list.php');
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $uploadPath = BASE_PATH . 'public/assets/images/' . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
                $image = $filename;
            }
            $base_price = $_POST['base_price'] ?? '';

            $errors = [];

            if (empty($name)) $errors[] = 'Name is required';
            if (empty($description)) $errors[] = 'Description is required';
            if (empty($base_price) || !is_numeric($base_price)) $errors[] = 'Valid price is required';

            if (empty($errors)) {
                $this->serverModel->create($name, $description, $image, $base_price);
                header('Location: ' . BASE_URL . '/public/admin/servers.php');
                exit;
            }
        }

        require base_path('templates/admin/server-create.php');
    }

    public function edit($id) {
        $server = $this->serverModel->getByIdAdmin($id);
        
        if (!$server) {
            header('Location: ' . BASE_URL . '/public/admin/servers.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = $server['image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $uploadPath = BASE_PATH . 'public/assets/images/' . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
                $image = $filename;
            }
            $base_price = $_POST['base_price'] ?? '';
            $available = isset($_POST['available']) ? 1 : 0;

            $errors = [];

            if (empty($name)) $errors[] = 'Name is required';
            if (empty($description)) $errors[] = 'Description is required';
            if (empty($base_price) || !is_numeric($base_price)) $errors[] = 'Valid price is required';

            if (empty($errors)) {
                $this->serverModel->update($id, $name, $description, $image, $base_price, $available);
                header('Location: ' . BASE_URL . '/public/admin/servers.php');
                exit;
            }
        }

        require base_path('templates/admin/server-edit.php');
    }

    public function delete($id) {
        $this->serverModel->delete($id);
        header('Location: ' . BASE_URL . '/public/admin/servers.php');
        exit;
    }

    public function components($id) {
    $server = $this->serverModel->getByIdAdmin($id);
    
    if (!$server) {
        header('Location: ' . BASE_URL . '/public/admin/servers.php');
        exit;
    }

    // Создание нового компонента
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sub_action']) && $_POST['sub_action'] === 'create_component') {
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? '';
        $price = $_POST['price'] ?? 0;
        $value = '';

        switch ($type) {
            case 'cpu':
                $value = "{$_POST['cpu_cores']} cores, {$_POST['cpu_frequency']} GHz";
                break;
            case 'gpu':
                $value = "{$_POST['gpu_vram']} GB VRAM";
                break;
            case 'ram':
                $value = "{$_POST['ram_capacity']} GB";
                break;
            case 'ssd':
            case 'hdd':
                $value = "{$_POST['storage_capacity']} GB";
                break;
        }

        $this->componentModel->create($name, $type, $value, $price);
        header('Location: ' . BASE_URL . '/public/admin/servers.php?action=components&id=' . $id);
        exit;
    }

    // Сохранение привязки компонентов к серверу
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $component_ids = $_POST['components'] ?? [];
        $current_components = $this->serverComponentModel->getServerComponents($id);
        
        foreach ($current_components as $comp_id) {
            if (!in_array($comp_id, $component_ids)) {
                $this->serverComponentModel->detachComponent($id, $comp_id);
            }
        }
        
        foreach ($component_ids as $comp_id) {
            if (!in_array($comp_id, $current_components)) {
                $this->serverComponentModel->attachComponent($id, $comp_id);
            }
        }

        header("Location: servers.php?action=components&id={$id}");
        exit;
    }

    $allComponents = $this->componentModel->getAll();
    $serverComponents = $this->serverComponentModel->getServerComponents($id);
    $componentModel = $this->componentModel;
    
    require base_path('templates/admin/server-components.php');
}
}
