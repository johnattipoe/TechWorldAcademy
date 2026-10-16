<?php
declare(strict_types=1);

namespace App\Middleware\InstructorMiddleware;

use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

final class InstructorMiddleware
{
    public function requireInstructor(): void
    {
        \start_app_session();
        $userId = $_SESSION['learning_user_id'] ?? null;
        $authenticatedAt = (int) ($_SESSION['learning_authenticated_at'] ?? 0);
        if (!is_string($userId) || $userId === '' || ($_SESSION['learning_user_role'] ?? '') !== 'instructor' || $authenticatedAt < 1) {
            header('Location: /login/?continue=' . rawurlencode('/instructor/'));
            exit;
        }
        if (time() - $authenticatedAt > 28800) {
            unset($_SESSION['learning_user_id'], $_SESSION['learning_user_role'], $_SESSION['learning_user_name'], $_SESSION['learning_user_email'], $_SESSION['learning_authenticated_at']);
            session_regenerate_id(true);
            header('Location: /login/?expired=1&continue=' . rawurlencode('/instructor/'));
            exit;
        }
        try {
            $account = (new FirestoreService(FirebaseService::firestore()))->get('academy_users', $userId);
            if (!$account || ($account['role'] ?? '') !== 'instructor' || ($account['status'] ?? 'active') !== 'active') {
                unset($_SESSION['learning_user_id'], $_SESSION['learning_user_role'], $_SESSION['learning_user_name'], $_SESSION['learning_user_email'], $_SESSION['learning_authenticated_at']);
                session_regenerate_id(true);
                header('Location: /login/?access=updated');
                exit;
            }
        } catch (\Throwable $exception) {
            error_log('Instructor account access check failed: ' . $exception->getMessage());
            http_response_code(503);
            exit('Instructor access cannot be verified right now. Please try again later.');
        }
    }
}
