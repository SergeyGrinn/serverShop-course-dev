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

    public function create($name, $description, $image, $base_price) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO servers (name, description, image, base_price, available) 
             VALUES (:name, :description, :image, :base_price, TRUE)"
        );
        return $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':image' => $image,
            ':base_price' => $base_price
        ]);
    }

    public function update($id, $name, $description, $image, $base_price, $available) {
        $stmt = $this->pdo->prepare(
            "UPDATE servers SET name = :name, description = :description, 
             image = :image, base_price = :base_price, available = :available 
             WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':description' => $description,
            ':image' => $image,
            ':base_price' => $base_price,
            ':available' => $available
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM servers WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}