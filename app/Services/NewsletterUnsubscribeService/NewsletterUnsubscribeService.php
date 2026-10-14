<?php
declare(strict_types=1);

namespace App\Services\NewsletterUnsubscribeService;

final class NewsletterUnsubscribeService
{
    public static function token(string $subscriberId): ?string
    {
        $secret = env_value('NEWSLETTER_UNSUBSCRIBE_SECRET');
        if (!$secret || strlen($secret) < 32 || !preg_match('/^[a-f0-9]{64}$/', $subscriberId)) return null;
        return hash_hmac('sha256', $subscriberId, $secret);
    }

    public static function verify(string $subscriberId, string $token): bool
    {
        $expected = self::token($subscriberId);
        return $expected !== null && preg_match('/^[a-f0-9]{64}$/', $token) === 1 && hash_equals($expected, $token);
    }

    public static function url(string $subscriberId): ?string
    {
        $token = self::token($subscriberId);
        if ($token === null) return null;
        $base = rtrim((string) env_value('APP_URL', env_value('RENDER_EXTERNAL_URL', '')), '/');
        if (!filter_var($base, FILTER_VALIDATE_URL)) return null;
        return $base . '/newsletter/unsubscribe/?subscriber=' . rawurlencode($subscriberId) . '&token=' . rawurlencode($token);
    }
}
