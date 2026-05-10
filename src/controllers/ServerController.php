<?php

require_once base_path('src/Models/Server.php');
require_once base_path('src/Config/db.php');

class ServerController {
    private $serverModel;

    public function __construct() {
        global $pdo;
        $this->serverModel = new Server($pdo);
    }

    public function index() { // Display server catalog
        $servers = $this->serverModel->getAllServers();
        require_once base_path('templates/header.php');
        require_once base_path('templates/catalog.php');
        require_once base_path('templates/footer.php');
    }
}