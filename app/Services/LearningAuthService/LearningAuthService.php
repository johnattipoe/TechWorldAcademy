<?php
declare(strict_types=1);

namespace App\Services\LearningAuthService;

use App\Models\LearningUser\LearningUser;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Validators\RegistrationValidator\RegistrationValidator;
use RuntimeException;
use Throwable;

final class LearningAuthService
{
    public function register(array $input, array $session, string $ip): array
    {
        if (!$this->validCsrf($input, $session)) return [419, ['ok' => false, 'message' => 'Your form expired. Refresh the page and try again.']];
        try {
            $limit = (new \App\Services\RateLimitService\RateLimitService())->consume($ip, 'learning-register', 5, 3600);
        } catch (Throwable $exception) {
            error_log('Learning registration rate limiter unavailable: ' . $exception->getMessage());
            return [503, ['ok' => false, 'message' => 'Registration is temporarily unavailable. Please try again later.']];
        }
        if (!$limit['allowed']) return [429, ['ok' => false, 'message' => 'Too many registration attempts. Please try again later.'], $limit['retry_after']];

        [$data, $errors] = RegistrationValidator::validate($input);
        if ($errors) return [422, ['ok' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors]];
        try {
            $user = LearningUser::createStudent(new FirestoreService(FirebaseService::firestore()), $data['name'], $data['email'], $data['password']);
            return [201, ['ok' => true, 'message' => 'Your learner account is ready.', 'user' => $user]];
        } catch (RuntimeException $exception) {
            if (str_contains($exception->getMessage(), 'already exists')) return [409, ['ok' => false, 'message' => 'An account with that email already exists. Try signing in.']];
            error_log('Learning account creation failed: ' . $exception->getMessage());
            return [503, ['ok' => false, 'message' => 'We could not create your account right now. Please try again later.']];
        } catch (Throwable $exception) {
            error_log('Learning account creation failed: ' . $exception->getMessage());
            return [503, ['ok' => false, 'message' => 'We could not create your account right now. Please try again later.']];
        }
    }

    public function login(array $input, array $session, string $ip): array
    {
        if (!$this->validCsrf($input, $session)) return [419, ['ok' => false, 'message' => 'Your form expired. Refresh the page and try again.']];
        try {
            $limit = (new \App\Services\RateLimitService\RateLimitService())->consume($ip, 'learning-login', 10, 3600);
        } catch (Throwable $exception) {
            error_log('Learning login rate limiter unavailable: ' . $exception->getMessage());
            return [503, ['ok' => false, 'message' => 'Sign-in is temporarily unavailable. Please try again later.']];
        }
        if (!$limit['allowed']) return [429, ['ok' => false, 'message' => 'Too many sign-in attempts. Please try again later.'], $limit['retry_after']];

        $rawEmail = $input['email'] ?? '';
        $rawPassword = $input['password'] ?? '';
        $email = is_string($rawEmail) ? mb_strtolower(trim($rawEmail)) : '';
        $password = is_string($rawPassword) ? $rawPassword : '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || $password === '' || strlen($password) > 72) {
            return [422, ['ok' => false, 'message' => 'Enter a valid email address and password.']];
        }
        try {
            $firestore = new FirestoreService(FirebaseService::firestore());
            $user = LearningUser::findByEmail($firestore, $email);
            $bootstrapEmail = mb_strtolower(trim((string) \env_value('ADMIN_EMAIL', '')));
            $bootstrapHash = (string) \env_value('ADMIN_PASSWORD_HASH', '');
            $bootstrapMatch = $bootstrapEmail !== '' && hash_equals($bootstrapEmail, $email)
                && $bootstrapHash !== '' && password_verify($password, $bootstrapHash);
            if ($bootstrapMatch && !$firestore->get('academy_security', 'admin-bootstrap')) {
                $user = LearningUser::promoteBootstrapAdmin(
                    $firestore,
                    $email,
                    $password,
                    trim((string) \env_value('ADMIN_NAME', 'TechWorld Academy Administrator'))
                );
            }
            if (!$user || ($user['status'] ?? 'active') !== 'active' || !password_verify($password, (string) ($user['passwordHash'] ?? ''))) {
                return [401, ['ok' => false, 'message' => 'Email or password is incorrect.']];
            }
            $role = (string) ($user['role'] ?? '');
            if (!in_array($role, ['student', 'instructor', 'admin'], true)) return [403, ['ok' => false, 'message' => 'This account cannot access the learning area.']];
            if (password_needs_rehash((string) $user['passwordHash'], PASSWORD_DEFAULT)) {
                $firestore->update('academy_users', (string) $user['id'], ['passwordHash' => password_hash($password, PASSWORD_DEFAULT)]);
            }
            return [200, ['ok' => true, 'message' => 'Signed in.', 'user' => ['id' => (string) $user['id'], 'name' => (string) ($user['name'] ?? ''), 'email' => (string) ($user['email'] ?? $email), 'role' => $role]]];
        } catch (Throwable $exception) {
            error_log('Learning sign-in failed: ' . $exception->getMessage());
            return [503, ['ok' => false, 'message' => 'Sign-in is temporarily unavailable. Please try again later.']];
        }
    }

    public function logout(array $input, array $session): bool
    {
        return $this->validCsrf($input, $session);
    }

    private function validCsrf(array $input, array $session): bool
    {
        return isset($input['csrf_token'], $session['csrf_token'])
            && hash_equals((string) $session['csrf_token'], (string) $input['csrf_token']);
    }
}
