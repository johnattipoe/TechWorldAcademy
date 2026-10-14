<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Controllers\NewsletterController\NewsletterController;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Use the subscription form to sign up.']);
    exit;
}

start_app_session();
$result = (new NewsletterController())->subscribe($_POST, $_SESSION, request_client_ip());
http_response_code($result[0]);
if (!empty($result[2])) header('Retry-After: ' . (int) $result[2]);
echo json_encode($result[1], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
