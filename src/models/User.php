<?php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($name, $email, $password, $verificationToken) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, email, password, email_verification_token, email_verified_at)
             VALUES (:username, :email, :password, :verification_token, NULL)"
        );
        $stmt->execute([
            ':username' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':verification_token' => $verificationToken
        ]);
    }

    public function findByVerificationToken($token) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE email_verification_token = :token"
        );
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    public function verifyEmail($id) {
        $stmt = $this->pdo->prepare(
            "UPDATE users
             SET email_verified_at = NOW(), email_verification_token = NULL
             WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    public function updateProfile($id, $username, $email, $mobilePhone) {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET username = :username, email = :email, mobile_phone = :mobile_phone WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':username' => $username,
            ':email' => $email,
            ':mobile_phone' => $mobilePhone !== '' ? $mobilePhone : null
        ]);
    }

    public function getAdminInvoiceDetails() {
        $stmt = $this->pdo->query("SELECT invoice_company_name, invoice_registration_number, invoice_vat_number,
            invoice_address, invoice_postal_code, invoice_city, invoice_country, invoice_phone,
            invoice_email, invoice_bank_account FROM users WHERE role = 'admin' ORDER BY id LIMIT 1");
        return $stmt->fetch();
    }

    public function updateInvoiceDetails($id, array $details) {
        $stmt = $this->pdo->prepare("UPDATE users SET
            invoice_company_name = :company_name,
            invoice_registration_number = :registration_number,
            invoice_vat_number = :vat_number,
            invoice_address = :address,
            invoice_postal_code = :postal_code,
            invoice_city = :city,
            invoice_country = :country,
            invoice_phone = :phone,
            invoice_email = :email,
            invoice_bank_account = :bank_account
            WHERE id = :id AND role = 'admin'");
        return $stmt->execute([
            ':id' => $id,
            ':company_name' => $details['company_name'] ?: null,
            ':registration_number' => $details['registration_number'] ?: null,
            ':vat_number' => $details['vat_number'] ?: null,
            ':address' => $details['address'] ?: null,
            ':postal_code' => $details['postal_code'] ?: null,
            ':city' => $details['city'] ?: null,
            ':country' => $details['country'] ?: null,
            ':phone' => $details['phone'] ?: null,
            ':email' => $details['email'] ?: null,
            ':bank_account' => $details['bank_account'] ?: null,
        ]);
    }

    public function updatePassword($id, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':password' => $hashedPassword
        ]);
    }

    public function setVerificationToken($id, $token) {
        $stmt = $this->pdo->prepare(
            "UPDATE users
             SET email_verification_token = :token, email_verified_at = NULL
             WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':token' => $token
        ]);
    }
}