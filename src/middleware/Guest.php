<?php

class Guest
{
    public static function check(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /home.php');
            exit;
        }
    }
}