<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Services\LearningAuthService\LearningAuthService;

start_app_session();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the sign-out button to end your session.');
}
if (!(new LearningAuthService())->logout($_POST, $_SESSION)) {
    http_response_code(419);
    exit('Your session expired. Refresh the page and try again.');
}
unset($_SESSION['learning_user_id'], $_SESSION['learning_user_role'], $_SESSION['learning_user_name'], $_SESSION['learning_user_email'], $_SESSION['learning_authenticated_at']);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
session_regenerate_id(true);
header('Location: /', true, 303);
exit;
