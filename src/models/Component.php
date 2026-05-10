<?php

class Component {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getByServer($server_id) {
        $stmt = $this->pdo->prepare("
            SELECT c.* FROM components c
            JOIN server_components sc ON c.id = sc.component_id
            WHERE sc.server_id = :server_id
            ORDER BY c.type
        ");
        $stmt->execute([':server_id' => $server_id]);
        return $stmt->fetchAll();
}
}