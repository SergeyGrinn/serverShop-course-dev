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
        if (!$user) {
            $_SESSION = [];
            session_destroy();
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        $profile = [
            'user' => $user,
            'errors' => [],
            'success' => null,
            'warning' => null,
            'formUsername' => $user['username'],
            'formEmail' => $user['email'],
            'formMobilePhone' => $user['mobile_phone'] ?? '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['profile_action'] ?? '') === 'change_password') {
                $this->changePassword($profile);
            } else {
                $this->updateProfile($profile);
            }
        }

        require_once base_path('templates/profile.php');
    }

    private function updateProfile(array &$profile): void {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobilePhone = trim($_POST['mobile_phone'] ?? '');
        $profile['formUsername'] = $username;
        $profile['formEmail'] = $email;
        $profile['formMobilePhone'] = $mobilePhone;

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $profile['errors'][] = 'Invalid CSRF token';
        }
        if ($username === '') {
            $profile['errors'][] = 'Name is required';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profile['errors'][] = 'Invalid email format';
        }
        if (strlen($mobilePhone) > 30) {
            $profile['errors'][] = 'Mobile phone must not exceed 30 characters';
        }

        $emailChanged = strcasecmp($email, $profile['user']['email']) !== 0;
        $requiresEmailVerification = $emailChanged && $profile['user']['role'] !== 'admin';
        if ($emailChanged && $this->userModel->findByEmail($email)) {
            $profile['errors'][] = 'Email already taken';
        }
        if (!empty($profile['errors'])) {
            return;
        }

        $user = $profile['user'];
        $this->userModel->updateProfile($user['id'], $username, $email, $mobilePhone);

        if ($requiresEmailVerification) {
            $verificationToken = bin2hex(random_bytes(32));
            $this->userModel->setVerificationToken($user['id'], $verificationToken);
            if (!Mailer::sendVerificationEmail($email, $username, $verificationToken)) {
                $profile['warning'] = 'Profile saved, but the verification email could not be sent. Use the resend button in the notice above.';
            }
        }

        $profile['user'] = $this->userModel->findById($user['id']);
        $_SESSION['user_name'] = $profile['user']['username'];
        $_SESSION['email_verified_at'] = $profile['user']['email_verified_at'];
        $profile['success'] = $requiresEmailVerification
            ? 'Profile updated. Please verify your new email address.'
            : 'Profile updated.';
    }

    private function changePassword(array &$profile): void {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $profile['errors'][] = 'Invalid CSRF token';
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $profile['user']['password'])) {
            $profile['errors'][] = 'Current password is incorrect';
        }
        if (strlen($newPassword) < 8) {
            $profile['errors'][] = 'New password must be at least 8 characters';
        }
        if ($newPassword !== $confirmPassword) {
            $profile['errors'][] = 'New passwords do not match';
        }

        if (!empty($profile['errors'])) {
            return;
        }

        $this->userModel->updatePassword($profile['user']['id'], $newPassword);
        $profile['user'] = $this->userModel->findById($profile['user']['id']);
        $profile['success'] = 'Password updated.';
    }
}
