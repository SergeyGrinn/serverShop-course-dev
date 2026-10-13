<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/src/Helpers/Validator.php';

final class PhoneNumberTest extends TestCase
{
    public function testNormalizesInternationalNumber(): void
    {
        self::assertSame('+37128980754', Validator::normalizePhoneNumber('+371 (28) 980-754'));
    }

    public function testNormalizesLocalNumberWithoutCountryPrefix(): void
    {
        self::assertSame('37128980754', Validator::normalizePhoneNumber('371 289 80754'));
    }

    public function testRejectsLettersAndMoreThanFifteenDigits(): void
    {
        self::assertNull(Validator::normalizePhoneNumber('371ABC123'));
        self::assertNull(Validator::normalizePhoneNumber('1234567890123456'));
    }

    public function testAllowsAnEmptyOptionalPhone(): void
    {
        self::assertSame('', Validator::normalizePhoneNumber('  '));
    }
}