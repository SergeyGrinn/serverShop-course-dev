<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$publicPos = strpos($script, '/public/');
$basePath = $publicPos !== false ? substr($script, 0, $publicPos + 7) : '';
$basePath = str_replace('\\', '/', $basePath);
if ($basePath === '/' || $basePath === '.') {
	$basePath = '';
} else {
	$basePath = rtrim($basePath, '/');
}
define('BASE_URL', $protocol . '://' . $host . $basePath);