<?php

class Mailer {
	public static function sendVerificationEmail($email, $name, $token) {
		$verificationUrl = BASE_URL . '/verify.php?token=' . urlencode($token);
		$subject = 'Verify your Server Catalog account';
		$message = "Hello {$name},\n\n"
			. "Please verify your account by opening this link:\n"
			. $verificationUrl . "\n\n"
			. "If you did not register, you can ignore this email.";
		$headers = "From: servershopcatalog@gmail.com\r\n"
			. "Content-Type: text/plain; charset=UTF-8\r\n";

		return @mail($email, $subject, $message, $headers);
	}
}
