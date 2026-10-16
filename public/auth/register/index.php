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
if (!(bool) site_setting('registrationEnabled', true)) {
    http_response_code(403);
    page_start('Registration unavailable', 'New learner account creation is temporarily paused.');
    echo '<section class="section container"><div class="learning-empty"><h1>Registration is temporarily paused</h1><p>Please contact the Academy if you need help accessing a learning account.</p><a class="button button-primary" href="/contact/">Contact the Academy</a></div></section>';
    page_end();
    exit;
}
$continue = $_GET['continue'] ?? $_POST['continue'] ?? '/student/';
if (!is_string($continue) || !str_starts_with($continue, '/') || str_starts_with($continue, '//') || str_contains($continue, '\\') || preg_match('/[\r\n]/', $continue)) $continue = '/student/';
$message = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = (new LearningAuthService())->register($_POST, $_SESSION, request_client_ip());
    http_response_code($result[0]);
    $message = (string) ($result[1]['message'] ?? '');
    $errors = (array) ($result[1]['errors'] ?? []);
    if (!empty($result[2])) header('Retry-After: ' . (int) $result[2]);
    if (!empty($result[1]['ok']) && isset($result[1]['user']['id'])) {
        session_regenerate_id(true);
        $_SESSION['learning_user_id'] = (string) $result[1]['user']['id'];
        $_SESSION['learning_user_role'] = 'student';
        $_SESSION['learning_user_name'] = (string) $result[1]['user']['name'];
        $_SESSION['learning_user_email'] = (string) $result[1]['user']['email'];
        $_SESSION['learning_authenticated_at'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: ' . $continue, true, 303);
        exit;
    }
}
page_start('Create your learner account', 'Create a TechWorld Academy learner account to access your learning dashboard.');
require dirname(__DIR__, 3) . '/app/Views/learning/auth/register/index.php';
page_end();
