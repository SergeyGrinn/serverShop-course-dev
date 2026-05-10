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
}