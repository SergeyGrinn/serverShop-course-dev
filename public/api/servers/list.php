<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';

header ('Content-Type: application/json');

$price_from = $_GET['price_from'] ?? null;
$price_to = $_GET['price_to'] ?? null;
$ram = $_GET['ram'] ?? null;
$storage = $_GET['storage'] ?? null;
$cpu_cores = $_GET['cpu_cores'] ?? null;

$where = ['s.available = 1'];
$params = [];

if ($price_from){
    $where[] = 's.base_price >= :price_from';
    $params[':price_from'] = $price_from;
}
if ($price_to){
    $where[] = 's.base_price <= :price_to';
    $params[':price_to'] = $price_to;
}

whereStr = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT * FROM servers WHERE {whereStr} ORDER BY id DESC");
$stmt->execute($params);
$servers = $stmt->fetchAll();

echo json_encode(['success' => true, 'servers' => $servers]);