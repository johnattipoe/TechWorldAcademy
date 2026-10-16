<?php
declare(strict_types=1);

namespace App\Services\EmailService;

final class EmailService
{
    private const RESEND_ENDPOINT = 'https://api.resend.com/emails';

    /** Sends through Resend when configured, otherwise uses the host's local mail transport. */
    public function send(string $to, string $subject, string $message, ?string $replyTo = null): bool
    {
        $from = \env_value('MAIL_FROM');
        if (!$from || strpbrk($from, "\r\n") !== false || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
        $fromAddress = $from;
        if (preg_match('/<([^<>]+)>/', $from, $matches) === 1) $fromAddress = trim($matches[1]);
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) return false;
        if ($replyTo !== null && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $replyTo = null;
        $subject = trim(str_replace(["\r", "\n"], ' ', $subject));
        if ($subject === '' || strlen($subject) > 180) return false;

        $apiKey = \env_value('RESEND_API_KEY');
        if ($apiKey !== null) return $this->sendWithResend($apiKey, $from, $to, $subject, $message, $replyTo);

        $headers = ['From: ' . $from, 'MIME-Version: 1.0', 'Content-Type: text/plain; charset=UTF-8'];
        if ($replyTo !== null) $headers[] = 'Reply-To: ' . $replyTo;
        return @mail($to, $subject, $message, implode("\r\n", $headers));
    }

    private function sendWithResend(string $apiKey, string $from, string $to, string $subject, string $message, ?string $replyTo): bool
    {
        if (!function_exists('curl_init')) {
            error_log('Resend notification could not be sent because the PHP cURL extension is unavailable.');
            return false;
        }

        $payload = ['from' => $from, 'to' => [$to], 'subject' => $subject, 'text' => $message];
        if ($replyTo !== null) $payload['reply_to'] = $replyTo;
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if (!is_string($json)) return false;

        $handle = curl_init(self::RESEND_ENDPOINT);
        if ($handle === false) return false;
        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        ]);

        $response = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        if ($response === false) {
            error_log('Resend email request failed: ' . curl_error($handle));
            curl_close($handle);
            return false;
        }
        curl_close($handle);

        if ($status < 200 || $status >= 300) {
            error_log('Resend email request was rejected with HTTP status ' . $status . '.');
            return false;
        }
        return true;
    }
}
