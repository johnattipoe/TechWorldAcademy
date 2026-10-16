<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
start_app_session();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: /', true, 303);
    exit;
}

$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (!is_string($submittedToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
    http_response_code(403);
    exit('The request could not be verified. Please reload the page and try again.');
}

$choice = $_POST['choice'] ?? '';
if (!in_array($choice, ['accepted', 'rejected'], true)) {
    http_response_code(400);
    exit('Please choose Accept or Cancel.');
}

setcookie('site_cookie_consent', $choice, [
    'expires' => time() + 60 * 60 * 24 * 365,
    'path' => '/',
    'secure' => app_uses_https(),
    'httponly' => true,
    'samesite' => 'Lax',
]);

$returnTo = $_POST['return_to'] ?? '/';
if (!is_string($returnTo) || !str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//') || str_contains($returnTo, '\\') || preg_match('/[\r\n]/', $returnTo)) {
    $returnTo = '/';
}

header('Location: ' . $returnTo, true, 303);
exit;
