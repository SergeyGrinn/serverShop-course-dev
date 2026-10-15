<?php

/**
 * Sends transactional emails (invoice, account verification) through PHPMailer.
 */
class Mailer {
	/**
	 * Emails the order invoice as a PDF attachment.
	 *
	 * @param string $email Recipient address.
	 * @param string $name Recipient name.
	 * @param int $orderId Order id, used in subject and file name.
	 * @param string $pdf Binary PDF content.
	 * @return bool True if the email was sent.
	 */
	public static function sendInvoiceEmail($email, $name, $orderId, $pdf): bool {
		// Do not send to malformed addresses or without an invoice
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

	/**
	 * Emails the account verification link.
	 *
	 * @param string $email Recipient address.
	 * @param string $name Recipient name.
	 * @param string $token Random verification token.
	 * @return bool True if the email was sent.
	 */
	public static function sendVerificationEmail($email, $name, $token): bool {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}

		// urlencode keeps the token safe inside the query string
		$verificationUrl = BASE_URL . '/verify.php?token=' . urlencode($token);
		$subject = 'Verify your Server Catalog account';
		$message = "Hello {$name},\n\n"
			. "Please verify your account by opening this link:\n"
			. $verificationUrl . "\n\n"
			. "If you did not register, you can ignore this email.";

		return self::send($email, $name, $subject, $message);
	}

	/**
	 * Configures PHPMailer from environment settings and delivers one message.
	 *
	 * @param string $email Recipient address.
	 * @param string $name Recipient name.
	 * @param string $subject Message subject.
	 * @param string $message Plain-text body.
	 * @param string|null $attachmentName Attachment file name, if any.
	 * @param string|null $attachment Binary PDF attachment, if any.
	 * @return bool True on success, false if delivery failed (error is logged).
	 */
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
			// SMTP settings come from the environment so credentials are not stored in code
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
			// Plain text avoids HTML/XSS injection through the user's name
			$mailer->isHTML(false);

			if ($attachment !== null && $attachmentName !== null) {
				$mailer->addStringAttachment($attachment, $attachmentName, 'base64', 'application/pdf');
			}

			return $mailer->send();
		} catch (Throwable $error) {
			// Mail failure must not break the user flow; log it and report false
			error_log('Email delivery failed: ' . $error->getMessage());
			return false;
		}
	}

	/**
	 * Reads a configuration value from the environment.
	 *
	 * @param string $name Variable name.
	 * @param string $default Value used when the variable is not set.
	 * @return string The value or the default.
	 */
	private static function env(string $name, string $default = ''): string {
		$value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
		return is_string($value) ? $value : $default;
	}
}
