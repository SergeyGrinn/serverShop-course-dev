<?php

require_once base_path('src/Models/Server.php');
require_once base_path('src/Models/Component.php');
require_once base_path('src/Config/db.php');
require_once base_path('src/Helpers/Response.php');

class ServerController {
    private $serverModel;
    private $componentModel;

    public function __construct() {
        global $pdo;
        $this->serverModel = new Server($pdo);
        $this->componentModel = new Component($pdo);
    }

    public function index() { // Display server catalog
        $servers = $this->serverModel->getAllServers();
        require_once base_path('templates/header.php');
        require_once base_path('templates/catalog.php');
        require_once base_path('templates/footer.php');
    }
    
    public function componentsApi($requestedServerId): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            Response::json(['success' => false, 'message' => 'Request must be GET']);
        }
        
        $serverId = filter_var($requestedServerId, FILTER_VALIDATE_INT);
        if ($serverId === false || $serverId < 1) {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'Invalid server ID']);
        }
        
        $server = $this->serverModel->getById($serverId);
        if (!$server) {
            http_response_code(404);
            Response::json(['success' => false, 'message' => 'Server not found']);
        }
        
        $components = $this->componentModel->getByServer($serverId);
        Response::json([
            'success' => true,
            'server_id' => $serverId,
            'components' => $components,
        ]);
    }
    
    public function exactUnitApi($requestedServerId): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            Response::json(['success' => false, 'message' => 'Request must be GET']);
        }
        
        $serverId = filter_var($requestedServerId, FILTER_VALIDATE_INT);
        if ($serverId === false || $serverId < 1) {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'Invalid server ID']);
        }
        
        $server = $this->serverModel->getById($serverId);
        if (!$server) {
            http_response_code(404);
            Response::json(['success' => false, 'message' => 'Server not found']);
        }
        
        Response::json([
            'success' => true,
            'server' => [
                'id' => (int) $server['id'],
                'name' => $server['name'],
                'base_price' => (float) $server['base_price'],
                'cpu_cores' => $server['default_cpu_cores'],
                'ram_gb' => $server['default_ram'],
                'gpu_vram_gb' => $server['default_gpu_vram'],
                'storage_gb' => $server['default_storage'],
            ],
        ]);
    }
}