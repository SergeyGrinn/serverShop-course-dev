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
            $image = $_POST['image'] ?? '';
            $base_price = $_POST['base_price'] ?? '';

            $errors = [];

            if (empty($name)) $errors[] = 'Name is required';
            if (empty($description)) $errors[] = 'Description is required';
            if (empty($base_price) || !is_numeric($base_price)) $errors[] = 'Valid price is required';

            if (empty($errors)) {
                $this->serverModel->create($name, $description, $image, $base_price);
                header('Location: /L/course/public/admin/servers.php');
                exit;
            }
        }

        require base_path('templates/admin/server-create.php');
    }

    public function edit($id) {
        $server = $this->serverModel->getByIdAdmin($id);
        
        if (!$server) {
            header('Location: /L/course/public/admin/servers.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = $_POST['image'] ?? '';
            $base_price = $_POST['base_price'] ?? '';
            $available = isset($_POST['available']) ? 1 : 0;

            $errors = [];

            if (empty($name)) $errors[] = 'Name is required';
            if (empty($description)) $errors[] = 'Description is required';
            if (empty($base_price) || !is_numeric($base_price)) $errors[] = 'Valid price is required';

            if (empty($errors)) {
                $this->serverModel->update($id, $name, $description, $image, $base_price, $available);
                header('Location: /L/course/public/admin/servers.php');
                exit;
            }
        }

        require base_path('templates/admin/server-edit.php');
    }

    public function delete($id) {
        $this->serverModel->delete($id);
        header('Location: /L/course/public/admin/servers.php');
        exit;
    }

    public function components($id) {
        $server = $this->serverModel->getByIdAdmin($id);
        
        if (!$server) {
            header('Location: /L/course/public/admin/servers.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $component_ids = $_POST['components'] ?? [];
            $current_components = $this->serverComponentModel->getServerComponents($id);
            
            // Remove unchecked components
            foreach ($current_components as $comp_id) {
                if (!in_array($comp_id, $component_ids)) {
                    $this->serverComponentModel->detachComponent($id, $comp_id);
                }
            }
            
            // Add new components
            foreach ($component_ids as $comp_id) {
                if (!in_array($comp_id, $current_components)) {
                    $this->serverComponentModel->attachComponent($id, $comp_id);
                }
            }

            // Delete components from server if "Remove" button is clicked
            

            header('Location: /L/course/public/admin/servers.php');
            exit;
        }

        $allComponents = $this->componentModel->getAll();
        $serverComponents = $this->serverComponentModel->getServerComponents($id);
        $componentModel = $this->componentModel;
        
        require base_path('templates/admin/server-components.php');
    }
}
