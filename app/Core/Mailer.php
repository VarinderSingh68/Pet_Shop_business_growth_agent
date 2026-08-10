<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Sends mail via SMTP when MAIL_MODE=smtp and credentials are set, otherwise
 * (MAIL_MODE=log, or missing config) writes every message to mail_logs so the
 * whole app works — and stays testable in the admin Mail Log — with zero SMTP.
 */
final class Mailer
{
    public static function send(string $toEmail, string $toName, string $subject, string $html, ?string $text = null): bool
    {
        $mode = config('mail.mode', 'log');
        $sent = false;
        $error = null;

        if ($mode === 'smtp' && config('mail.host') !== '') {
            try {
                $sent = self::sendSmtp($toEmail, $toName, $subject, $html, $text);
            } catch (PHPMailerException $e) {
                $error = $e->getMessage();
                logger_channel('mail')->error('SMTP send failed', ['error' => $error, 'to' => $toEmail]);
            }
        }

        Database::instance()->insert('mail_logs', [
            'to_email' => $toEmail,
            'to_name' => $toName,
            'subject' => $subject,
            'body_html' => $html,
            'status' => $sent ? 'sent' : ($mode === 'smtp' ? 'failed' : 'logged'),
            'error' => $error,
            'created_at' => now(),
        ]);

        return $sent || $mode !== 'smtp';
    }

    private static function sendSmtp(string $toEmail, string $toName, string $subject, string $html, ?string $text): bool
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = (string) config('mail.host');
        $mailer->SMTPAuth = true;
        $mailer->Username = (string) config('mail.username');
        $mailer->Password = (string) config('mail.password');
        $mailer->SMTPSecure = (string) config('mail.encryption');
        $mailer->Port = (int) config('mail.port');

        $mailer->setFrom((string) config('mail.from_address'), (string) config('mail.from_name'));
        $mailer->addAddress($toEmail, $toName);
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $html;
        $mailer->AltBody = $text ?? strip_tags($html);

        return $mailer->send();
    }
}
