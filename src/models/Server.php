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
}