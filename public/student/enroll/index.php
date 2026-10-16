<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Controllers\StudentEnrollmentController\StudentEnrollmentController;
use App\Middleware\StudentMiddleware\StudentMiddleware;

(new StudentMiddleware())->requireStudent();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use a course request form to continue.');
}
$result = (new StudentEnrollmentController())->request($_POST, $_SESSION, request_client_ip());
$_SESSION['learning_flash'] = ['message' => $result[1], 'success' => (bool) $result[2]];
if (!empty($result[3])) header('Retry-After: ' . (int) $result[3]);
header('Location: /student/my-courses/', true, 303);
exit;
