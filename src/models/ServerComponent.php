<?php

class ServerComponent {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function attachComponent($server_id, $component_id) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO server_components (server_id, component_id) 
             VALUES (:server_id, :component_id)"
        );
        return $stmt->execute([
            ':server_id' => $server_id,
            ':component_id' => $component_id
        ]);
    }

    public function detachComponent($server_id, $component_id) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM server_components WHERE server_id = :server_id AND component_id = :component_id"
        );
        return $stmt->execute([
            ':server_id' => $server_id,
            ':component_id' => $component_id
        ]);
    }

    public function getServerComponents($server_id) {
        $stmt = $this->pdo->prepare(
            "SELECT component_id FROM server_components WHERE server_id = :server_id"
        );
        $stmt->execute([':server_id' => $server_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
