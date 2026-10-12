<?php

class Mailer {
	public static function sendInvoiceEmail($email, $name, $orderId, $pdf): bool {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pdf === '') {
			return false;
		}

		$subject = 'Invoice for order #' . (int) $orderId;
		$message = "Hello {$name},\n\n"
			. "Thank you for your order. Your invoice is attached to this email.\r\n\r\n"
			. "Server Catalog";
		$filename = 'invoice-order-' . (int) $orderId . '.pdf';

		return self::send($email, $name, $subject, $message, $filename, $pdf);
	}

	public static function sendVerificationEmail($email, $name, $token): bool {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}

		$verificationUrl = BASE_URL . '/verify.php?token=' . urlencode($token);
		$subject = 'Verify your Server Catalog account';
		$message = "Hello {$name},\n\n"
			. "Please verify your account by opening this link:\n"
			. $verificationUrl . "\n\n"
			. "If you did not register, you can ignore this email.";

		return self::send($email, $name, $subject, $message);
	}

	private static function send(
		string $email,
		string $name,
		string $subject,
		string $message,
		?string $attachmentName = null,
		?string $attachment = null
	): bool {
		$mailer = new PHPMailer\PHPMailer\PHPMailer(true);

		try {
			$host = trim(self::env('MAILER_HOST'));
			if ($host !== '') {
				$mailer->isSMTP();
				$mailer->Host = $host;
				$mailer->Port = (int) self::env('MAILER_PORT', '1025');
				$mailer->SMTPAuth = self::env('MAILER_USERNAME') !== '';
				$mailer->Username = self::env('MAILER_USERNAME');
				$mailer->Password = self::env('MAILER_PASSWORD');

				$encryption = strtolower(self::env('MAILER_ENCRYPTION'));
				if ($encryption === 'tls') {
					$mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
				} elseif ($encryption === 'ssl') {
					$mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
				}
			} else {
				$mailer->isMail();
			}

			$mailer->CharSet = PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8;
			$mailer->setFrom(
				self::env('MAILER_FROM_ADDRESS', 'servershopcatalog@gmail.com'),
				self::env('MAILER_FROM_NAME', 'Server Catalog')
			);
			$mailer->addAddress($email, $name);
			$mailer->Subject = $subject;
			$mailer->Body = $message;
			$mailer->isHTML(false);

			if ($attachment !== null && $attachmentName !== null) {
				$mailer->addStringAttachment($attachment, $attachmentName, 'base64', 'application/pdf');
			}

			return $mailer->send();
		} catch (Throwable $error) {
			error_log('Email delivery failed: ' . $error->getMessage());
			return false;
		}
	}

	private static function env(string $name, string $default = ''): string {
		$value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
		return is_string($value) ? $value : $default;
	}
}
