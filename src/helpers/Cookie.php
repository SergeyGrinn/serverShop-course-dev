<?php

class Cookie {
    public static function setConsent($value) {
        setcookie('cookie_consent', $value, [
            'expires' => time() + 31536000,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
    }

    public static function get($name, $default = null) {
        return $_COOKIE[$name] ?? $default;
    }
}