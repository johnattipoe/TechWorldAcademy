<?php
declare(strict_types=1);

namespace App\Middleware\AdminMiddleware;

use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use Throwable;

final class AdminMiddleware
{
    public function authenticated(): bool
    {
        \start_app_session();
        $userId = $_SESSION['learning_user_id'] ?? null;
        $authenticatedAt = (int) ($_SESSION['learning_authenticated_at'] ?? 0);
        if (!is_string($userId) || $userId === '' || !in_array(($_SESSION['learning_user_role'] ?? ''), ['student', 'instructor', 'admin'], true) || $authenticatedAt < 1) return false;
        if (time() - $authenticatedAt > 28800) {
            $this->clearLearningSession();
            return false;
        }

        try {
            $user = (new FirestoreService(FirebaseService::firestore()))->get('academy_users', $userId);
            if (!$user || ($user['status'] ?? 'active') !== 'active') {
                $this->clearLearningSession();
                return false;
            }
            if (($user['role'] ?? '') !== 'admin') return false;
            $_SESSION['learning_user_role'] = 'admin';
            $_SESSION['learning_user_name'] = (string) ($user['name'] ?? $_SESSION['learning_user_name'] ?? 'Administrator');
            $_SESSION['learning_user_email'] = (string) ($user['email'] ?? $_SESSION['learning_user_email'] ?? '');
            return true;
        } catch (Throwable $exception) {
            error_log('Admin account verification failed: ' . $exception->getMessage());
            http_response_code(503);
            return false;
        }
    }

    public function logout(): void
    {
        \start_app_session();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    private function clearLearningSession(): void
    {
        unset($_SESSION['learning_user_id'], $_SESSION['learning_user_role'], $_SESSION['learning_user_name'], $_SESSION['learning_user_email'], $_SESSION['learning_authenticated_at']);
        session_regenerate_id(true);
    }
}
