<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$publicPos = strpos($script, '/public/');
$basePath = $publicPos !== false ? substr($script, 0, $publicPos + 7) : dirname($script);
define('BASE_URL', $protocol . '://' . $host . $basePath);