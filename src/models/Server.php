<?php

class Server {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllServers() {
        $stmt = $this->pdo->query("SELECT * FROM servers WHERE available = TRUE");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM servers
            WHERE id = :id AND available = TRUE"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAllServersAdmin() {
        $stmt = $this->pdo->query("SELECT * FROM servers ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getByIdAdmin($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM servers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $serverData) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO servers
             (name, description, image, base_price, default_ram, default_storage, default_cpu_cores, default_gpu_vram, available)
             VALUES (:name, :description, :image, :base_price, :default_ram, :default_storage, :default_cpu_cores, :default_gpu_vram, TRUE)"
        );
        return $stmt->execute([
            ':name' => $serverData['name'],
            ':description' => $serverData['description'],
            ':image' => $serverData['image'],
            ':base_price' => $serverData['base_price'],
            ':default_ram' => $serverData['default_ram'],
            ':default_storage' => $serverData['default_storage'],
            ':default_cpu_cores' => $serverData['default_cpu_cores'],
            ':default_gpu_vram' => $serverData['default_gpu_vram']
        ]);
    }

    public function update($id, array $serverData) {
        $stmt = $this->pdo->prepare(
            "UPDATE servers SET name = :name, description = :description, 
             image = :image, base_price = :base_price, available = :available,
             default_ram = :default_ram, default_storage = :default_storage,
             default_cpu_cores = :default_cpu_cores, default_gpu_vram = :default_gpu_vram
             WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':name' => $serverData['name'],
            ':description' => $serverData['description'],
            ':image' => $serverData['image'],
            ':base_price' => $serverData['base_price'],
            ':available' => $serverData['available'],
            ':default_ram' => $serverData['default_ram'],
            ':default_storage' => $serverData['default_storage'],
            ':default_cpu_cores' => $serverData['default_cpu_cores'],
            ':default_gpu_vram' => $serverData['default_gpu_vram']
        ]);
    }
    public function toggleAvailability($id) {
            $stmt = $this->pdo->prepare(
                "UPDATE servers 
                SET available = NOT available 
                WHERE id = :id"
            );
            return $stmt->execute([':id' => $id]);
        }
        
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM servers WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    
}