<?php

require_once base_path('src/Models/User.php');

class AuthController {
    private $userModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confPassword = $_POST['confirm_password'] ?? '';

            $errors = [];

            if (empty($name)) $errors[] = 'Name is required';
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[^@]+@[^@]+\.[a-z]{2,6}$/i', $email)) {
                $errors[] = 'Invalid email format';
            }
            if (empty($password)) $errors[] = 'Password is required';
            if (empty($confPassword)) $errors[] = 'Confirmation password is required';
            if (!empty($password) && !empty($confPassword) && $password !== $confPassword) $errors[] = 'Passwords do not match';
            if (!empty($email) && $this->userModel->findByEmail($email)) $errors[] = 'Email already taken';

            if (empty($errors)) {
                $this->userModel->create($name, $email, $password);
                header('Location: ' . BASE_URL . '/login.php');
                exit;
            }
        }

        require base_path('templates/header.php');
        require base_path('templates/register.php');
        require base_path('templates/footer.php');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

            $cartModel = new Cart($this->pdo);
            $cart = $cartModel->getOrCreateCart(session_id());
            $stmt = $this->pdo->prepare("UPDATE carts SET user_id = :user_id WHERE id = :cart_id");
            $stmt->execute([
                ':user_id' => $user['id'],
                ':cart_id' => $cart['id']
            ]);

                header('Location: ' . BASE_URL . '/index.php');
                exit;
            }

            $error = 'Invalid email or password';
        }

        require base_path('templates/header.php');
        require base_path('templates/login.php');
        require base_path('templates/footer.php');
    }
}