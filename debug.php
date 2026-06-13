<?php
/**
 * Debug script to check if system is configured correctly
 */

echo "<h1>System Debug Check</h1>";

// Check 1: Database connection
echo "<h2>1. Database Connection</h2>";
try {
    require_once 'src/Config/db.php';
    echo "✓ Database connection OK<br>";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

// Check 2: Models exist
echo "<h2>2. Models</h2>";
$models = ['Order', 'Cart', 'User'];
foreach ($models as $model) {
    $file = "src/Models/{$model}.php";
    echo "$model: " . (file_exists($file) ? "✓ exists" : "✗ missing") . "<br>";
}

// Check 3: Helpers exist
echo "<h2>3. Helpers</h2>";
$helpers = ['Response', 'Validator'];
foreach ($helpers as $helper) {
    $file = "src/Helpers/{$helper}.php";
    echo "$helper: " . (file_exists($file) ? "✓ exists" : "✗ missing") . "<br>";
}

// Check 4: API Endpoints exist
echo "<h2>4. API Endpoints</h2>";
$endpoints = [
    'api/orders/create.php',
    'api/orders/pay.php',
    'api/orders/cancel.php',
    'api/orders/list.php',
];
foreach ($endpoints as $endpoint) {
    echo "$endpoint: " . (file_exists($endpoint) ? "✓ exists" : "✗ missing") . "<br>";
}

// Check 5: Templates exist
echo "<h2>5. Templates</h2>";
$templates = [
    'templates/checkout.php',
    'templates/header.php',
    'templates/footer.php',
];
foreach ($templates as $template) {
    echo "$template: " . (file_exists($template) ? "✓ exists" : "✗ missing") . "<br>";
}

// Check 6: Session
echo "<h2>6. Session Test</h2>";
session_start();
$_SESSION['test'] = 'test';
echo "Session ID: " . session_id() . "<br>";
echo "Session test: " . ($_SESSION['test'] === 'test' ? "✓ OK" : "✗ Failed") . "<br>";

// Check 7: Response class
echo "<h2>7. Response Class Test</h2>";
try {
    require_once 'src/Helpers/Response.php';
    echo "✓ Response class loaded<br>";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p>If all checks are OK, the system should work correctly.</p>";
?>
