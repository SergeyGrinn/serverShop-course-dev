<?php

require_once base_path('src/Models/User.php');
require_once base_path('src/Services/Mailer.php');
require_once base_path('src/Helpers/Response.php');

class ProfileController {
    private $userModel;

    public function __construct($pdo, bool $requireAuthentication = true) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($requireAuthentication && !isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        $this->userModel = new User($pdo);
    }

    public function getApi(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            Response::json(['success' => false, 'message' => 'Request must be GET']);
        }

        $user = $this->getAuthenticatedApiUser();
        Response::json([
            'success' => true,
            'profile' => [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'mobile_phone' => $user['mobile_phone'],
                'role' => $user['role'],
                'email_verified_at' => $user['email_verified_at'],
            ],
        ]);
    }

    public function updateApi(): void {
        $data = $this->readApiPost();
        $user = $this->getAuthenticatedApiUser();
        $username = trim($this->apiString($data['username'] ?? null));
        $email = trim($this->apiString($data['email'] ?? null));
        $mobilePhone = trim($this->apiString($data['mobile_phone'] ?? null));

        if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'A name and valid email are required']);
        }
        if (strlen($username) > 255 || strlen($mobilePhone) > 30) {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'One or more profile fields exceed the allowed length']);
        }

        $emailChanged = strcasecmp($email, $user['email']) !== 0;
        if ($emailChanged && $this->userModel->findByEmail($email)) {
            http_response_code(409);
            Response::json(['success' => false, 'message' => 'Email already taken']);
        }

        $this->userModel->updateProfile($user['id'], $username, $email, $mobilePhone);
        $emailVerificationSent = null;
        if ($emailChanged && $user['role'] !== 'admin') {
            $verificationToken = bin2hex(random_bytes(32));
            $this->userModel->setVerificationToken($user['id'], $verificationToken);
            $emailVerificationSent = Mailer::sendVerificationEmail($email, $username, $verificationToken);
        }

        $updatedUser = $this->userModel->findById($user['id']);
        $_SESSION['user_name'] = $updatedUser['username'];
        $_SESSION['email_verified_at'] = $updatedUser['email_verified_at'];

        Response::json([
            'success' => true,
            'message' => $emailVerificationSent === false
                ? 'Profile updated, but the verification email could not be sent.'
                : ($emailChanged && $user['role'] !== 'admin'
                    ? 'Profile updated. Verify your new email address.'
                    : 'Profile updated.'),
            'emailVerificationSent' => $emailVerificationSent,
            'profile' => [
                'id' => (int) $updatedUser['id'],
                'username' => $updatedUser['username'],
                'email' => $updatedUser['email'],
                'mobile_phone' => $updatedUser['mobile_phone'],
                'role' => $updatedUser['role'],
                'email_verified_at' => $updatedUser['email_verified_at'],
            ],
        ]);
    }

    private function getAuthenticatedApiUser(): array {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            Response::json(['success' => false, 'message' => 'Authentication required']);
        }

        $user = $this->userModel->findById($_SESSION['user_id']);
        if (!$user) {
            http_response_code(401);
            Response::json(['success' => false, 'message' => 'Authentication required']);
        }

        return $user;
    }

    private function readApiPost(): array {
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

        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($data['csrf_token'] ?? null);
        if (!is_string($csrfToken) || !csrf_verify($csrfToken)) {
            http_response_code(403);
            Response::json(['success' => false, 'message' => 'Invalid CSRF token']);
        }

        return $data;
    }

    private function apiString($value): string {
        return is_string($value) ? $value : '';
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
            'formInvoiceDetails' => $this->getInvoiceFormValues($user),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['profile_action'] ?? '') === 'change_password') {
                $this->changePassword($profile);
            } elseif (($_POST['profile_action'] ?? '') === 'save_invoice_details') {
                $this->updateInvoiceDetails($profile);
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

    private function getInvoiceFormValues(array $user): array {
        return [
            'company_name' => $user['invoice_company_name'] ?? '',
            'registration_number' => $user['invoice_registration_number'] ?? '',
            'vat_number' => $user['invoice_vat_number'] ?? '',
            'address' => $user['invoice_address'] ?? '',
            'postal_code' => $user['invoice_postal_code'] ?? '',
            'city' => $user['invoice_city'] ?? '',
            'country' => $user['invoice_country'] ?? '',
            'phone' => $user['invoice_phone'] ?? '',
            'email' => $user['invoice_email'] ?? '',
            'bank_account' => $user['invoice_bank_account'] ?? '',
        ];
    }

    private function updateInvoiceDetails(array &$profile): void {
        if ($profile['user']['role'] !== 'admin') {
            http_response_code(403);
            $profile['errors'][] = 'Only administrators can update invoice details';
            return;
        }

        $fieldNames = [
            'company_name' => 'invoice_company_name',
            'registration_number' => 'invoice_registration_number',
            'vat_number' => 'invoice_vat_number',
            'address' => 'invoice_address',
            'postal_code' => 'invoice_postal_code',
            'city' => 'invoice_city',
            'country' => 'invoice_country',
            'phone' => 'invoice_phone',
            'email' => 'invoice_email',
            'bank_account' => 'invoice_bank_account',
        ];
        $lengthLimits = [
            'company_name' => 255,
            'registration_number' => 100,
            'vat_number' => 100,
            'address' => 255,
            'postal_code' => 30,
            'city' => 100,
            'country' => 100,
            'phone' => 50,
            'email' => 255,
            'bank_account' => 100,
        ];

        $details = [];
        foreach ($fieldNames as $key => $fieldName) {
            $value = $_POST[$fieldName] ?? '';
            $details[$key] = is_string($value) ? trim($value) : '';
        }
        $profile['formInvoiceDetails'] = $details;

        $csrfToken = $_POST['csrf_token'] ?? null;
        if (!is_string($csrfToken) || !csrf_verify($csrfToken)) {
            $profile['errors'][] = 'Invalid CSRF token';
        }
        foreach ($lengthLimits as $key => $limit) {
            $valueLength = function_exists('mb_strlen') ? mb_strlen($details[$key]) : strlen($details[$key]);
            if ($valueLength > $limit) {
                $profile['errors'][] = ucfirst(str_replace('_', ' ', $key)) . " must not exceed {$limit} characters";
            }
        }
        if ($details['email'] !== '' && !filter_var($details['email'], FILTER_VALIDATE_EMAIL)) {
            $profile['errors'][] = 'Invoice email must be valid';
        }
        if (!empty($profile['errors'])) {
            return;
        }

        $this->userModel->updateInvoiceDetails($profile['user']['id'], $details);
        $profile['user'] = $this->userModel->findById($profile['user']['id']);
        $profile['formInvoiceDetails'] = $this->getInvoiceFormValues($profile['user']);
        $profile['success'] = 'Invoice details saved.';
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
