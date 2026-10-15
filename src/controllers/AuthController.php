<?php

require_once base_path('src/Models/User.php');
require_once base_path('src/Models/Cart.php');
require_once base_path('src/Services/Mailer.php');
require_once base_path('src/Helpers/Response.php');

/**
 * Handles registration, login and logout for both HTML forms and the JSON API.
 */
class AuthController {
    private $userModel;
    private $pdo;

    /**
     * Sets up the controller with the database and the user model.
     *
     * @param PDO $pdo Active database connection.
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    /**
     * Shows the registration form and processes its submission.
     *
     * @return void
     */
    public function register() {
        $registerRedirect = $this->safeLoginRedirect($_POST['redirect'] ?? $_GET['redirect'] ?? null);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confPassword = $_POST['confirm_password'] ?? '';

            $errors = [];

            // CSRF check: the form must carry the token stored in the session
            if (!csrf_verify($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Invalid CSRF token';
            }

            // Server-side validation; client-side checks can be bypassed
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
                // Random token proves ownership of the email address; the user model hashes the password
                $verificationToken = bin2hex(random_bytes(32));
                $this->userModel->create($name, $email, $password, $verificationToken);
                Mailer::sendVerificationEmail($email, $name, $verificationToken);
                header('Location: ' . BASE_URL . '/login.php?redirect=' . rawurlencode($registerRedirect));
                exit;
            }
        }

        require base_path('templates/header.php');
        require base_path('templates/register.php');
        require base_path('templates/footer.php');
    }

    /**
     * Shows the login form and authenticates the submitted credentials.
     *
     * @return void
     */
    public function login() {
        $loginRedirect = $this->safeLoginRedirect($_POST['redirect'] ?? $_GET['redirect'] ?? null);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // CSRF check before touching credentials
            if (!csrf_verify($_POST['csrf_token'] ?? null)) {
                $error = 'Invalid CSRF token';
            } else {
                $user = $this->userModel->findByEmail($email);

            // password_verify compares against the stored hash; no plain-text passwords
            if ($user && password_verify($password, $user['password'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['email_verified_at'] = $user['email_verified_at'];

            // Attach the guest cart to the logged-in user so it is not lost
            $cartModel = new Cart($this->pdo);
            $cart = $cartModel->getOrCreateCart(session_id());
            $stmt = $this->pdo->prepare("UPDATE carts SET user_id = :user_id WHERE id = :cart_id");
            $stmt->execute([
                ':user_id' => $user['id'],
                ':cart_id' => $cart['id']
            ]);

                // Unverified customers are kept off protected pages until they confirm their email
                $destination = $user['role'] !== 'admin' && $user['email_verified_at'] === null
                    ? 'index.php'
                    : $loginRedirect;
                header('Location: ' . BASE_URL . '/' . $destination);
                exit;
            }

                // Same message for unknown email and wrong password to avoid user enumeration
                $error = 'Invalid email or password';
            }
        }

        require base_path('templates/header.php');
        require base_path('templates/login.php');
        require base_path('templates/footer.php');
    }

    /**
     * Restricts post-login redirects to a whitelist to prevent open redirects.
     *
     * @param mixed $redirect Requested redirect target.
     * @return string Safe relative page name.
     */
    private function safeLoginRedirect($redirect): string {
        if (!is_string($redirect)) {
            return 'index.php';
        }

        $redirect = ltrim($redirect, '/');
        return in_array($redirect, ['checkout.php', 'my-orders.php'], true)
            ? $redirect
            : 'index.php';
    }

    /**
     * JSON login endpoint; starts a fresh session and moves the guest cart to the user.
     *
     * @return void
     */
    public function loginApi(): void {
        $data = $this->readApiPost();
        $email = $this->stringValue($data['email'] ?? null);
        $password = $this->stringValue($data['password'] ?? null);

        if ($email === '' || $password === '') {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'Email and password are required']);
        }

        // Generic 401 message so attackers cannot tell which part was wrong
        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            Response::json(['success' => false, 'message' => 'Invalid email or password']);
        }

        // Regenerate the session id after login to prevent session fixation
        $oldSessionId = session_id();
        if (!session_regenerate_id(true)) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Could not start a secure session']);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['email_verified_at'] = $user['email_verified_at'];
        $_SESSION['session_id'] = session_id();

        // The cart follows the regenerated session id and is linked to the user
        $cartStmt = $this->pdo->prepare(
            'UPDATE carts SET user_id = :user_id, session_id = :new_session_id WHERE session_id = :old_session_id'
        );
        $cartStmt->execute([
            ':user_id' => $user['id'],
            ':new_session_id' => session_id(),
            ':old_session_id' => $oldSessionId,
        ]);

        Response::json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => (int) $user['id'],
                'name' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'email_verified' => $user['email_verified_at'] !== null || $user['role'] === 'admin',
            ],
        ]);
    }

    /**
     * JSON registration endpoint with server-side validation and email verification.
     *
     * @return void
     */
    public function registerApi(): void {
        $data = $this->readApiPost();
        $name = trim($this->stringValue($data['name'] ?? null));
        $email = trim($this->stringValue($data['email'] ?? null));
        $password = $this->stringValue($data['password'] ?? null);
        $confirmation = $this->stringValue($data['confirm_password'] ?? null);

        // Validate all input on the server before creating the account
        if ($name === '' || $email === '' || $password === '' || $confirmation === '') {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'Name, email, password, and confirmation are required']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'Invalid email format']);
        }
        if (strlen($password) < 8) {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'Password must be at least 8 characters']);
        }
        if ($password !== $confirmation) {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'Passwords do not match']);
        }
        // 409 Conflict: the email is already registered
        if ($this->userModel->findByEmail($email)) {
            http_response_code(409);
            Response::json(['success' => false, 'message' => 'Email already taken']);
        }

        // Account is created with a random verification token that is sent by email
        $verificationToken = bin2hex(random_bytes(32));
        $this->userModel->create($name, $email, $password, $verificationToken);
        $emailSent = Mailer::sendVerificationEmail($email, $name, $verificationToken);

        http_response_code(201);
        Response::json([
            'success' => true,
            'message' => $emailSent
                ? 'Registration successful. Check your email to verify your account.'
                : 'Registration successful, but the verification email could not be sent.',
            'verificationEmailSent' => $emailSent,
        ]);
    }

    /**
     * JSON logout endpoint: clears session data and expires the session cookie.
     *
     * @return void
     */
    public function logoutApi(): void {
        $this->readApiPost();

        $_SESSION = [];
        // Expire the cookie in the browser as well, not only the server-side data
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookie['path'],
                'domain' => $cookie['domain'],
                'secure' => $cookie['secure'],
                'httponly' => $cookie['httponly'],
                'samesite' => $cookie['samesite'] ?? 'Lax',
            ]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        Response::json(['success' => true, 'message' => 'Logout successful']);
    }

    /**
     * Starts the session, enforces POST + CSRF and returns the parsed request body.
     *
     * @return array Request data from form fields or a JSON body.
     */
    private function readApiPost(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            Response::json(['success' => false, 'message' => 'Request must be POST']);
        }

        $data = $_POST;
        if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $decoded = json_decode(file_get_contents('php://input'), true);
            if (!is_array($decoded)) {
                http_response_code(400);
                Response::json(['success' => false, 'message' => 'Invalid JSON request']);
            }
            $data = $decoded;
        }

        // CSRF token may come from a header (fetch/AJAX) or from the body
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($data['csrf_token'] ?? null);
        if (!is_string($csrfToken) || !csrf_verify($csrfToken)) {
            http_response_code(403);
            Response::json(['success' => false, 'message' => 'Invalid CSRF token']);
        }

        return $data;
    }

    /**
     * Coerces a request value to a string so arrays or null cannot break validation.
     *
     * @param mixed $value Raw request value.
     * @return string The value if it is a string, otherwise an empty string.
     */
    private function stringValue($value): string {
        return is_string($value) ? $value : '';
    }
}