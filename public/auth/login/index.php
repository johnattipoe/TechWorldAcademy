<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Services\LearningAuthService\LearningAuthService;

start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
if (!empty($_SESSION['learning_user_id'])) {
    $role = (string) ($_SESSION['learning_user_role'] ?? 'student');
    header('Location: ' . ($role === 'admin' ? '/admin/' : ($role === 'instructor' ? '/instructor/' : '/student/')));
    exit;
}
$continue = $_GET['continue'] ?? $_POST['continue'] ?? '/student/';
if (!is_string($continue) || !str_starts_with($continue, '/') || str_starts_with($continue, '//') || str_contains($continue, '\\') || preg_match('/[\r\n]/', $continue)) $continue = '/student/';
$message = isset($_GET['expired']) ? 'Your session expired. Please sign in again.' : '';
$error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = (new LearningAuthService())->login($_POST, $_SESSION, request_client_ip());
    http_response_code($result[0]);
    $message = (string) ($result[1]['message'] ?? '');
    $error = empty($result[1]['ok']);
    if (!empty($result[2])) header('Retry-After: ' . (int) $result[2]);
    if (!empty($result[1]['ok']) && isset($result[1]['user']['id'])) {
        session_regenerate_id(true);
        $_SESSION['learning_user_id'] = (string) $result[1]['user']['id'];
        $_SESSION['learning_user_role'] = (string) ($result[1]['user']['role'] ?? 'student');
        $_SESSION['learning_user_name'] = (string) $result[1]['user']['name'];
        $_SESSION['learning_user_email'] = (string) $result[1]['user']['email'];
        $_SESSION['learning_authenticated_at'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $role = (string) $_SESSION['learning_user_role'];
        $destination = $role === 'admin' ? '/admin/' : ($role === 'instructor' ? '/instructor/' : ($continue === '/admin/' ? '/student/' : $continue));
        header('Location: ' . $destination, true, 303);
        exit;
    }
}
page_start('Learner sign in', 'Sign in to your TechWorld Academy learner account.');
require dirname(__DIR__, 3) . '/app/Views/learning/auth/login/index.php';
page_end();
