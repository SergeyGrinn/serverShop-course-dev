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