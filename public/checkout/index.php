<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Controllers\CheckoutController\CheckoutController;
use App\Middleware\StudentMiddleware\StudentMiddleware;

(new StudentMiddleware())->requireStudent();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use a course checkout form to continue.');
}
$result = (new CheckoutController())->start($_POST, $_SESSION);
if (is_string($result[2] ?? null) && $result[2] !== '') {
    header('Location: ' . $result[2], true, 303);
    exit;
}
$_SESSION['learning_flash'] = ['message' => $result[1], 'success' => false];
header('Location: /student/my-courses/', true, 303);
exit;
