<?php

const BASE_PATH = __DIR__ . '/../../../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';
require_once BASE_PATH . 'src/Config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Request must be GET']);
    exit;
}

$price_from = $_GET['price_from'] ?? null;
$price_to = $_GET['price_to'] ?? null;
$ram_min = $_GET['ram_min'] ?? '';
$ram_max = $_GET['ram_max'] ?? '';
$storage_min = $_GET['storage_min'] ?? '';
$storage_max = $_GET['storage_max'] ?? '';
$cpu_cores = $_GET['cpu_cores'] ?? null;

$rangePairs = [
    'price' => [$price_from, $price_to],
    'RAM' => [$ram_min, $ram_max],
    'storage' => [$storage_min, $storage_max],
];

foreach ($rangePairs as $label => [$minimum, $maximum]) {
    $hasMinimum = $minimum !== null && $minimum !== '';
    $hasMaximum = $maximum !== null && $maximum !== '';

    if (($hasMinimum && !is_numeric($minimum)) || ($hasMaximum && !is_numeric($maximum))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => ucfirst($label) . ' range must be numeric']);
        exit;
    }

    if ($hasMinimum && $hasMaximum && (float) $minimum > (float) $maximum) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Maximum ' . $label . ' must be greater than or equal to minimum.',
        ]);
        exit;
    }
}

$where = ['available = 1'];
$params = [];

if ($price_from){
    $where[] = 'base_price >= :price_from';
    $params[':price_from'] = $price_from;
}
if ($price_to){
    $where[] = 'base_price <= :price_to';
    $params[':price_to'] = $price_to;
}
if ($ram_min !== '' && is_numeric($ram_min)) {
    $where[] = 'default_ram >= :ram_min';
    $params[':ram_min'] = (float) $ram_min;
}
if ($ram_max !== '' && is_numeric($ram_max)) {
    $where[] = 'default_ram <= :ram_max';
    $params[':ram_max'] = (float) $ram_max;
}
if ($storage_min !== '' && is_numeric($storage_min)) {
    $where[] = 'default_storage >= :storage_min';
    $params[':storage_min'] = (int) $storage_min;
}
if ($storage_max !== '' && is_numeric($storage_max)) {
    $where[] = 'default_storage <= :storage_max';
    $params[':storage_max'] = (int) $storage_max;
}
if ($cpu_cores){
    $where[] = 'default_cpu_cores = :cpu_cores';
    $params[':cpu_cores'] = $cpu_cores;
}

$whereStr = implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT * FROM servers WHERE {$whereStr} ORDER BY id DESC");
$stmt->execute($params);
$servers = $stmt->fetchAll();

echo json_encode(['success' => true, 'servers' => $servers]);