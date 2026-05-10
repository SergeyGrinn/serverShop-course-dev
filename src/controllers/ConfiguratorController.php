<?php
require_once base_path('src/Models/Server.php');
require_once base_path('src/Config/db.php');
require_once base_path('src/Models/Component.php');

class ConfiguratorController {
    private $serverModel;
    private $componentModel;


public function __construct() {
    global $pdo;
    $this->serverModel = new Server($pdo);
    $this->componentModel = new Component($pdo);
}

public function index() { // Display configurator
    $server_id = $_GET['id'] ?? null; // Get selected server ID from query parameter

    if (!$server_id) {
        header('Location: /L/course/public/index.php'); // Redirect to server catalog if no server selected
        exit;
    }

$server = $this->serverModel->getById($server_id); // Get selected server details

    if(!$server) {
        header('Location: /L/course/public/index.php'); // Redirect to server catalog if server not found
        exit;
    }
$components = $this->componentModel->getByServer($server_id); // Get components compatible with selected server

//Group components by type for display
$grouped = [];
foreach ($components as $component) {
    $grouped[$component['type']][] = $component;
}

require_once base_path('templates/header.php');
require_once base_path('templates/configurator.php');
require_once base_path('templates/footer.php');
}
}