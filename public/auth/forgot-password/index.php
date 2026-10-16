<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Services\PasswordRecoveryService\PasswordRecoveryService;

start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$message = '';
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$status, $message] = (new PasswordRecoveryService())->request($_POST, $_SESSION, request_client_ip());
    http_response_code($status);
    $sent = $status === 200;
}
page_start('Reset your password', 'Request a secure password reset link for your learner account.');
?>
<section class="section container learning-auth-section"><div class="learning-auth-card"><span class="eyebrow">Account recovery</span><h1>Forgot your password?</h1><p>Enter the email address for your learner account. If it matches an account, we’ll send a one-time reset link.</p><?php if ($message !== ''): ?><div class="learning-alert <?= $sent ? '' : 'is-error' ?>" role="status" aria-live="polite"><?= e($message) ?></div><?php endif; ?><?php if (!$sent): ?><form method="post" action="/forgot-password/" class="learning-auth-form"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><div><label for="recovery-email">Email address</label><input id="recovery-email" name="email" type="email" autocomplete="email" maxlength="254" required></div><button class="button button-primary" type="submit">Send reset link</button></form><?php endif; ?><p class="learning-auth-switch"><a href="/login/">Return to sign in</a></p></div></section>
<?php page_end(); ?>
