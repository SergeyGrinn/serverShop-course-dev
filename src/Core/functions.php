<?php

if (!function_exists('base_path')) {
    function base_path($path = '') {
        if (defined('BASE_PATH')) {
            return BASE_PATH . $path;
        }

        return ROOT_PATH . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return !empty($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}