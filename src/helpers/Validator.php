<?php
/**
 * Validation helper.
 * Reserved for future form validation methods.
 */
class Validator
{
	public static function normalizePhoneNumber(string $phone): ?string
	{
		$phone = trim($phone);
		if ($phone === '') {
			return '';
		}

		if (!preg_match('/^\+?[0-9][0-9\s().-]*$/D', $phone)) {
			return null;
		}

		$digits = preg_replace('/[^0-9]/', '', $phone);
		if ($digits === '' || strlen($digits) > 15) {
			return null;
		}

		return ($phone[0] === '+' ? '+' : '') . $digits;
	}
}