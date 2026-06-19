<?php

define('ROOT_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);

/**
 * PUBLIC_PATH: Path to public folder (where web server serves files from)
 * 
 * Example: /xampp/htdocs/L/course/public/
 */
define('PUBLIC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR);

/**
 * SRC_PATH: Path to src folder (where PHP classes are)
 * 
 * Example: /xampp/htdocs/L/course/src/
 */
define('SRC_PATH', ROOT_PATH . 'src' . DIRECTORY_SEPARATOR);

/**
 * TEMPLATES_PATH: Path to templates folder
 * 
 * Example: /xampp/htdocs/L/course/templates/
 */
define('TEMPLATES_PATH', ROOT_PATH . 'templates' . DIRECTORY_SEPARATOR);

/**
 * DATABASE_PATH: Path to database folder
 * 
 * Example: /xampp/htdocs/L/course/database/
 */
define('DATABASE_PATH', ROOT_PATH . 'database' . DIRECTORY_SEPARATOR);

/**
 * ASSETS_PATH: Path to public assets (images, css, js)
 * 
 * Example: /xampp/htdocs/L/course/public/assets/
 */
define('ASSETS_PATH', PUBLIC_PATH . 'assets' . DIRECTORY_SEPARATOR);

/**
 * LOGS_PATH: Path to logs folder
 * 
 * Example: /xampp/htdocs/L/course/logs/
 */
define('LOGS_PATH', ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR);

// ====== Determine web paths (for use in HTML/JavaScript) ======

/**
 * APP_URL: Base URL of application - use in HTML href="" and JavaScript fetch()
 * 
 * Explanation: We check REQUEST_URI to determine the base URL
 * If user accessed: http://localhost/L/course/public/index.php
 * REQUEST_URI would be: /L/course/public/index.php
 * We extract the base part: /L/course/
 * 
 * This allows project to work even if:
 * - Moved to different folder: http://localhost/coursework/public/index.php
 * - Different domain: http://myserver.com/public/index.php
 * - Subdirectory: http://example.com/projects/server-shop/public/index.php
 */

// Get the directory where public folder is located
$scriptName = $_SERVER['SCRIPT_NAME'];

$publicPos = strpos($scriptName, '/public/');

if ($publicPos !== false) {
    define('APP_URL', substr($scriptName, 0, $publicPos) . '/');
} else {
    define('APP_URL', dirname($scriptName) . '/');
}

/**
 * PUBLIC_URL: URL to public folder for CSS, JS, images
 * 
 * Example: http://localhost/L/course/public/
 * Use like: <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/main.css">
 */
define('PUBLIC_URL', APP_URL . 'public/');

/**
 * API_URL: Base URL for API endpoints
 * 
 * Example: http://localhost/L/course/api/
 * Use like: fetch('<?= API_URL ?>orders/create.php', { ... })
 */
define('API_URL', APP_URL . 'api/');

/**
 * ASSETS_URL: URL to assets folder
 * 
 * Example: http://localhost/L/course/public/assets/
 * Use like: <img src="<?= ASSETS_URL ?>images/server.jpg">
 */
define('ASSETS_URL', PUBLIC_URL . 'assets/');

// ====== Helper function ======

/**
 * Helper function to get full file path
 * 
 * Usage: 
 *   require_once base_path('src/Models/Order.php');
 * 
 * Instead of:
 *   require_once ROOT_PATH . 'src/Models/Order.php';
 */
if (!function_exists('base_path')) {
    function base_path($path = '') {
        return ROOT_PATH . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

/**
 * Helper function to get URL for asset
 * 
 * Usage:
 *   <img src="<?= asset('images/server.jpg') ?>">
 * 
 * Instead of:
 *   <img src="<?= ASSETS_URL ?>images/server.jpg">
 */
if (!function_exists('asset')) {
    function asset($path = '') {
        return ASSETS_URL . ltrim($path, '/');
    }
}

/**
 * Helper function to get API URL
 * 
 * Usage:
 *   fetch('<?= api_url('orders/create.php') ?>', { ... })
 * 
 * Instead of:
 *   fetch('<?= API_URL ?>orders/create.php', { ... })
 */
if (!function_exists('api_url')) {
    function api_url($path = '') {
        return API_URL . ltrim($path, '/');
    }
}

/**
 * Helper function to redirect to app URL
 * 
 * Usage:
 *   redirect('index.php');  // Goes to /public/index.php
 *   redirect('admin/orders.php'); // Goes to /public/admin/orders.php
 */
if (!function_exists('redirect')) {
    function redirect($path = '') {
        $url = PUBLIC_URL . ltrim($path, '/');
        header('Location: ' . $url);
        exit;
    }
}

// ====== Debug Mode (optional) ======

/**
 * Set to true to see all path definitions
 * Useful when testing after moving project to new location
 */
if (isset($_GET['debug_paths']) && $_GET['debug_paths'] === 'true') {
    echo '<pre>';
    echo "ROOT_PATH: " . ROOT_PATH . "\n";
    echo "PUBLIC_PATH: " . PUBLIC_PATH . "\n";
    echo "SRC_PATH: " . SRC_PATH . "\n";
    echo "TEMPLATES_PATH: " . TEMPLATES_PATH . "\n";
    echo "APP_URL: " . APP_URL . "\n";
    echo "PUBLIC_URL: " . PUBLIC_URL . "\n";
    echo "API_URL: " . API_URL . "\n";
    echo "ASSETS_URL: " . ASSETS_URL . "\n";
    echo '</pre>';
    exit;
}
