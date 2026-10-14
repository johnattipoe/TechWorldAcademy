<?php
declare(strict_types=1);

namespace App\Middleware\AuthMiddleware;

final class AuthMiddleware
{
    public function isAuthenticated(string $sessionKey = 'user_id'): bool
    {
        \start_app_session();
        return isset($_SESSION[$sessionKey]) && $_SESSION[$sessionKey] !== false && $_SESSION[$sessionKey] !== '';
    }

    public function requireAuthentication(string $redirect = '/admin/?view=login', string $sessionKey = 'user_id'): void
    {
        if ($this->isAuthenticated($sessionKey)) return;
        header('Location: ' . $redirect);
        exit;
    }
}
