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
            $image = 'emptyimage.png';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $uploadPath = BASE_PATH . 'public/assets/images/' . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
                $image = $filename;
            }
            $base_price = $_POST['base_price'] ?? '';

            $errors = [];
            $hardware = $this->getHardwareDefaults($_POST, $errors);

            if (empty($name)) $errors[] = 'Name is required';
            if (empty($description)) $errors[] = 'Description is required';
            if ($base_price === '' || !is_numeric($base_price) || (float) $base_price < 0) {
                $errors[] = 'Price must be a non-negative number';
            }

            if (empty($errors)) {
                $this->serverModel->create([
                    'name' => $name,
                    'description' => $description,
                    'image' => $image,
                    'base_price' => $base_price,
                    'default_ram' => $hardware['default_ram'],
                    'default_storage' => $hardware['default_storage'],
                    'default_cpu_cores' => $hardware['default_cpu_cores'],
                    'default_gpu_vram' => $hardware['default_gpu_vram'],
                ]);
                header('Location: ' . BASE_URL . '/admin/servers.php');
                exit;
            }
        }

        require base_path('templates/admin/server-create.php');
    }

    public function edit($id) {
        $server = $this->serverModel->getByIdAdmin($id);
        
        if (!$server) {
            header('Location: ' . BASE_URL . '/admin/servers.php');
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
            $available = $server['available'];

            $errors = [];
            $hardware = $this->getHardwareDefaults($_POST, $errors);

            if (empty($name)) $errors[] = 'Name is required';
            if (empty($description)) $errors[] = 'Description is required';
            if ($base_price === '' || !is_numeric($base_price) || (float) $base_price < 0) {
                $errors[] = 'Price must be a non-negative number';
            }

            if (empty($errors)) {
                $this->serverModel->update($id, [
                    'name' => $name,
                    'description' => $description,
                    'image' => $image,
                    'base_price' => $base_price,
                    'available' => $available,
                    'default_ram' => $hardware['default_ram'],
                    'default_storage' => $hardware['default_storage'],
                    'default_cpu_cores' => $hardware['default_cpu_cores'],
                    'default_gpu_vram' => $hardware['default_gpu_vram'],
                ]);
                header('Location: ' . BASE_URL . '/admin/servers.php');
                exit;
            }
        }

        require base_path('templates/admin/server-edit.php');
    }

    private function getHardwareDefaults(array $input, array &$errors) {
        $defaults = [
            'default_ram' => null,
            'default_storage' => null,
            'default_cpu_cores' => null,
            'default_gpu_vram' => null,
        ];

        if (!isset($input['has_built_in_hardware'])) {
            return $defaults;
        }

        $fields = [
            'default_ram' => ['ram', 'RAM'],
            'default_storage' => ['storage', 'Storage'],
            'default_cpu_cores' => ['cpu_cores', 'CPU cores'],
            'default_gpu_vram' => ['gpu_vram', 'GPU memory'],
        ];

        $providedCount = 0;
        foreach ($fields as $column => [$field, $label]) {
            $rawValue = $input[$field] ?? '';
            if ($rawValue === '') {
                continue;
            }

            $value = $this->parseHardwareValue($column, $rawValue, $label, $errors);
            if ($value === null) {
                continue;
            }

            $defaults[$column] = $value;
            $providedCount++;
        }

        if ($providedCount === 0 && empty($errors)) {
            $errors[] = 'Enter at least one built-in hardware value';
        }

        return $defaults;
    }

    private function parseHardwareValue(string $column, string $value, string $label, array &$errors) {
        $parsedValue = null;

        if ($column === 'default_ram') {
            $ramValue = trim($value);
            if (is_numeric($ramValue) && (float) $ramValue > 0 && preg_match('/^\d+(?:\.\d{1,3})?$/', $ramValue)) {
                $parsedValue = (float) $ramValue;
            } else {
                $errors[] = $label . ' must be positive and use at most 3 decimal places';
            }
        } elseif (filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value > 0) {
            $parsedValue = (int) $value;
        } else {
            $errors[] = $label . ' must be a positive whole number';
        }

        return $parsedValue;
    }

    public function toggleAvailability($id) {
        $this->serverModel->toggleAvailability($id);
        header('Location: ' . BASE_URL . '/admin/servers.php');
        exit;
    }
    
    public function delete($id) {
        $this->serverModel->delete($id);
        header('Location: ' . BASE_URL . '/admin/servers.php');
        exit;
    }

    public function components($id) {
    $server = $this->serverModel->getByIdAdmin($id);
    
    if (!$server) {
        header('Location: ' . BASE_URL . '/admin/servers.php');
        exit;
    }

    // Creation of a new component
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sub_action']) && $_POST['sub_action'] === 'create_component') {
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? '';
        $price = $_POST['price'] ?? 0;
        $value = '';

        $errors = [];
        if ($price === '' || !is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Price must be a non-negative number';
        }

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

        if (empty($errors)) {
            $this->componentModel->create($name, $type, $value, $price);
            header('Location: ' . BASE_URL . '/admin/servers.php?action=components&id=' . $id);
            exit;
        }
    }

    // Saving the selected components for the server
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['sub_action'] ?? '') !== 'create_component') {
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
