<?php
declare(strict_types=1);

namespace App\Services\PasswordRecoveryService;

use App\Models\LearningUser\LearningUser;
use App\Services\EmailService\EmailService;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\RateLimitService\RateLimitService;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

final class PasswordRecoveryService
{
    public function request(array $input, array $session, string $ip): array
    {
        if (!$this->csrf($input, $session)) return [419, 'Your form expired. Refresh and try again.'];
        try {
            $limit = (new RateLimitService())->consume($ip, 'password-reset-request', 5, 3600);
            if (!$limit['allowed']) return [429, 'If an account matches that address, password reset instructions will be sent.'];
            $email = is_string($input['email'] ?? null) ? mb_strtolower(trim($input['email'])) : '';
            if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 254) {
                $firestore = new FirestoreService(FirebaseService::firestore());
                $user = LearningUser::findByEmail($firestore, $email);
                if ($user && ($user['status'] ?? 'active') === 'active' && ($user['role'] ?? '') === 'student') {
                    $token = bin2hex(random_bytes(32));
                    $tokenId = hash('sha256', $token);
                    $firestore->create('academy_password_resets', $tokenId, [
                        'userId' => (string) $user['id'],
                        'expiresAt' => new DateTimeImmutable('+1 hour', new DateTimeZone('UTC')),
                        'used' => false,
                        'createdAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
                    ]);
                    $base = $this->baseUrl();
                    $sent = $base !== '' && (new EmailService())->send(
                        $email,
                        'Reset your TechWorld Academy password',
                        "A password reset was requested for your learner account. This link expires in one hour:\n\n" . $base . '/reset-password/?token=' . rawurlencode($token) . "\n\nIf you did not request this, you can ignore this email."
                    );
                    if (!$sent) {
                        $firestore->delete('academy_password_resets', $tokenId);
                        error_log('Password recovery email could not be sent. Check MAIL_FROM, APP_URL and email delivery configuration.');
                    }
                }
            }
            return [200, 'If an account matches that address, password reset instructions will be sent.'];
        } catch (Throwable $exception) {
            error_log('Password recovery request failed: ' . $exception->getMessage());
            return [200, 'If an account matches that address, password reset instructions will be sent.'];
        }
    }

    public function reset(array $input, array $session, string $ip): array
    {
        if (!$this->csrf($input, $session)) return [419, 'Your form expired. Refresh and try again.'];
        try {
            $limit = (new RateLimitService())->consume($ip, 'password-reset-submit', 10, 3600);
            if (!$limit['allowed']) return [429, 'Too many attempts. Request a new reset link later.'];
            $token = is_string($input['token'] ?? null) ? $input['token'] : '';
            $password = is_string($input['password'] ?? null) ? $input['password'] : '';
            $confirmation = is_string($input['password_confirmation'] ?? null) ? $input['password_confirmation'] : '';
            if (strlen($password) < 12 || strlen($password) > 72) return [422, 'Use a password between 12 and 72 bytes.'];
            if (!hash_equals($password, $confirmation)) return [422, 'The passwords do not match.'];
            if (!preg_match('/^[a-f0-9]{64}$/', $token)) return [400, 'This reset link is invalid or expired. Request a new one.'];
            $firestore = new FirestoreService(FirebaseService::firestore());
            $tokenId = hash('sha256', $token);
            $reset = $firestore->get('academy_password_resets', $tokenId);
            if (!$reset || !empty($reset['used']) || !(($reset['expiresAt'] ?? null) instanceof \DateTimeInterface) || $reset['expiresAt'] <= new DateTimeImmutable('now', new DateTimeZone('UTC'))) return [400, 'This reset link is invalid or expired. Request a new one.'];
            $user = $firestore->get('academy_users', (string) ($reset['userId'] ?? ''));
            if (!$user || ($user['status'] ?? 'active') !== 'active' || ($user['role'] ?? '') !== 'student') return [400, 'This reset link is invalid or expired. Request a new one.'];
            $firestore->update('academy_password_resets', $tokenId, ['used' => true, 'usedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC'))]);
            $firestore->update('academy_users', (string) $user['id'], ['passwordHash' => password_hash($password, PASSWORD_DEFAULT), 'passwordChangedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC'))]);
            foreach ($firestore->listByField('academy_password_resets', 'userId', (string) $user['id'], 100) as $otherReset) {
                if (($otherReset['id'] ?? '') !== $tokenId && empty($otherReset['used'])) {
                    try { $firestore->update('academy_password_resets', (string) $otherReset['id'], ['used' => true, 'usedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC'))]); }
                    catch (Throwable $exception) { error_log('An older password reset token could not be invalidated.'); }
                }
            }
            return [200, 'Your password has been updated. You can now sign in.'];
        } catch (Throwable $exception) {
            error_log('Password reset failed: ' . $exception->getMessage());
            return [503, 'Password reset is temporarily unavailable. Request a new link and try again.'];
        }
    }

    private function baseUrl(): string
    {
        $platformUrl = (string) (\env_value('LEARNING_PLATFORM_URL') ?? '');
        if ($platformUrl === '' && \env_value('APP_ENV', 'local') !== 'production') $platformUrl = 'http://127.0.0.1:8001';
        $configured = rtrim($platformUrl !== '' ? $platformUrl : (string) (\env_value('APP_URL') ?? \env_value('RENDER_EXTERNAL_URL') ?? ''), '/');
        if (filter_var($configured, FILTER_VALIDATE_URL) && in_array(parse_url($configured, PHP_URL_SCHEME), ['http', 'https'], true)) return $configured;
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if (!preg_match('/^[A-Za-z0-9.-]+(?::[0-9]{1,5})?$/', $host)) return '';
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        return ($secure ? 'https://' : 'http://') . $host;
    }

    private function csrf(array $input, array $session): bool
    {
        return isset($input['csrf_token'], $session['csrf_token']) && hash_equals((string) $session['csrf_token'], (string) $input['csrf_token']);
    }
}
