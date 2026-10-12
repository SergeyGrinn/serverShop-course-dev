<?php

use PHPUnit\Framework\TestCase;

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return 'test-token';
    }
}

final class RegistrationViewTest extends TestCase
{
    public function testTakenEmailHasDedicatedMessage(): void
    {
        $html = $this->renderRegistrationForm(['Email already taken']);

        self::assertStringContainsString('This email is already taken', $html);
        self::assertStringNotContainsString('Invalid email format', $html);
    }

    public function testInvalidEmailFormatKeepsItsOwnMessage(): void
    {
        $html = $this->renderRegistrationForm(['Invalid email format']);

        self::assertStringContainsString('Invalid email format', $html);
        self::assertStringNotContainsString('This email is already taken', $html);
    }

    private function renderRegistrationForm(array $errors): string
    {
        $previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $previousPost = $_POST ?? [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['email' => 'existing@example.test'];

        ob_start();
        try {
            require dirname(__DIR__) . '/templates/register.php';
            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
            $_POST = $previousPost;
            if ($previousMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $previousMethod;
            }
        }
    }
}