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

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM components WHERE available = TRUE ORDER BY type");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM components WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($name, $type, $value, $price) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO components (name, type, value, price, available) 
             VALUES (:name, :type, :value, :price, TRUE)"
        );
        return $stmt->execute([
            ':name' => $name,
            ':type' => $type,
            ':value' => is_array($value) ? json_encode($value) : $value,
            ':price' => $price
        ]);
    }

    public function update($id, $name, $type, $value, $price) {
        $stmt = $this->pdo->prepare(
            "UPDATE components SET name = :name, type = :type, value = :value, price = :price WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':type' => $type,
            ':value' => is_array($value) ? json_encode($value) : $value,
            ':price' => $price
        ]);
    }

    public function getComponentDisplay($component) {
        $value = $component['value'];
        
        $data = @json_decode($value, true);
        if (!$data) {
            $data = ['raw' => $value];
        }

        $display = $component['name'];
        
        switch ($component['type']) {
            case 'CPU':
                if (isset($data['cores']) && isset($data['frequency'])) {
                    $display .= " ({$data['cores']} cores, {$data['frequency']} GHz)";
                }
                break;
            case 'GPU':
                if (isset($data['vram'])) {
                    $display .= " ({$data['vram']} GB)";
                }
                break;
            case 'RAM':
                if (isset($data['capacity'])) {
                    $display .= " ({$data['capacity']} GB)";
                }
                break;
            case 'SSD':
            case 'HDD':
                if (isset($data['capacity'])) {
                    $display .= " ({$data['capacity']} GB)";
                }
                break;
        }
        
        return $display;
    }
}