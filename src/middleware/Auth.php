<?php

class Auth
{
    public static function check(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
    }
}