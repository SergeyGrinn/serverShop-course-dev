<?php

class Admin {
    public static function check() {
        session_start();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /L/course/public/index.php');
            exit;
        }
    }
}
