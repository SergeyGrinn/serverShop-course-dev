<?php

if (!function_exists('base_path')) {
    /**
     * Builds an absolute filesystem path inside the project.
     *
     * @param string $path Relative path to append.
     * @return string Absolute path.
     */
    function base_path($path = '') {
        if (defined('BASE_PATH')) {
            return BASE_PATH . $path;
        }

        return ROOT_PATH . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Returns the session CSRF token, generating it on first use.
     *
     * @return string Random hex token to embed in forms.
     */
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
    /**
     * Checks a submitted CSRF token against the session token.
     *
     * @param mixed $token Token received from the request.
     * @return bool True if it matches.
     */
    function csrf_verify($token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // hash_equals gives a constant-time comparison, avoiding timing attacks
        return is_string($token)
            && $token !== ''
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}