<?php

require_once base_path('src/Models/User.php');
require_once base_path('src/Services/Mailer.php');

class ProfileController {
    private $userModel;

    public function __construct($pdo) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        $this->userModel = new User($pdo);
    }

    public function index() {
        $user = $this->userModel->findById($_SESSION['user_id']);
        $errors = [];
        $success = null;

        if (!$user) {
            session_destroy();
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (!csrf_verify($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Invalid CSRF token';
            }
            if ($username === '') {
                $errors[] = 'Name is required';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }

            $emailChanged = strcasecmp($email, $user['email']) !== 0;
            if ($emailChanged && $this->userModel->findByEmail($email)) {
                $errors[] = 'Email already taken';
            }

            if (empty($errors)) {
                $verificationToken = $emailChanged ? bin2hex(random_bytes(32)) : null;
                $this->userModel->updateProfile($user['id'], $username, $email);

                if ($emailChanged) {
                    $this->userModel->setVerificationToken($user['id'], $verificationToken);
                    $user['email_verified_at'] = null;
                    Mailer::sendVerificationEmail($email, $username, $verificationToken);
                }

                $user = $this->userModel->findById($user['id']);
                $_SESSION['user_name'] = $user['username'];
                $success = $emailChanged
                    ? 'Profile updated. Please verify your new email address.'
                    : 'Profile updated.';
            }
        }

        require_once base_path('templates/profile.php');
    }
}
