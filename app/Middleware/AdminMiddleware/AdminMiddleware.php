<?php
declare(strict_types=1);

namespace App\Middleware\AdminMiddleware;

use App\Middleware\AuthMiddleware\AuthMiddleware;

final class AdminMiddleware
{
    private AuthMiddleware $auth;

    public function __construct()
    {
        $this->auth = new AuthMiddleware();
    }

    public function authenticated(): bool
    {
        \start_app_session();
        if (!$this->auth->isAuthenticated('admin_authenticated') || empty($_SESSION['admin_authenticated_at'])) return false;
        if (time() - (int) $_SESSION['admin_authenticated_at'] > 28800) {
            unset($_SESSION['admin_authenticated'], $_SESSION['admin_authenticated_at']);
            session_regenerate_id(true);
            return false;
        }
        return true;
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
}
