<?php

class Mailer {
	public static function sendInvoiceEmail($email, $name, $orderId, $pdf): bool {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pdf === '') {
			return false;
		}

		$boundary = '=_Invoice_' . bin2hex(random_bytes(16));
		$subject = 'Invoice for order #' . (int) $orderId;
		$safeName = str_replace(["\r", "\n"], ' ', (string) $name);
		$message = "Hello {$safeName},\r\n\r\n"
			. "Thank you for your order. Your invoice is attached to this email.\r\n\r\n"
			. "Server Catalog";
		$filename = 'invoice-order-' . (int) $orderId . '.pdf';
		$body = "--{$boundary}\r\n"
			. "Content-Type: text/plain; charset=UTF-8\r\n"
			. "Content-Transfer-Encoding: 8bit\r\n\r\n"
			. $message . "\r\n\r\n"
			. "--{$boundary}\r\n"
			. "Content-Type: application/pdf; name=\"{$filename}\"\r\n"
			. "Content-Transfer-Encoding: base64\r\n"
			. "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n"
			. chunk_split(base64_encode($pdf))
			. "\r\n--{$boundary}--\r\n";
		$headers = "From: servershopcatalog@gmail.com\r\n"
			. "MIME-Version: 1.0\r\n"
			. "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

		return @mail($email, $subject, $body, $headers);
	}

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
