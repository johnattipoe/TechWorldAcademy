<?php
declare(strict_types=1);

namespace App\Services\EmailService;

final class EmailService
{
    /** Uses the host's configured mail transport; returns false if mail is not configured or rejected. */
    public function send(string $to, string $subject, string $message, ?string $replyTo = null): bool
    {
        $from = \env_value('MAIL_FROM');
        if (!$from || !filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($from, FILTER_VALIDATE_EMAIL)) return false;
        if ($replyTo !== null && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $replyTo = null;
        $subject = trim(str_replace(["\r", "\n"], ' ', $subject));
        if ($subject === '' || strlen($subject) > 180) return false;

        $headers = ['From: ' . $from, 'MIME-Version: 1.0', 'Content-Type: text/plain; charset=UTF-8'];
        if ($replyTo !== null) $headers[] = 'Reply-To: ' . $replyTo;
        return @mail($to, $subject, $message, implode("\r\n", $headers));
    }
}
