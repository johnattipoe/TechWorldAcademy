<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Services\PasswordRecoveryService\PasswordRecoveryService;

start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$token = is_string($_REQUEST['token'] ?? null) ? $_REQUEST['token'] : '';
$message = '';
$complete = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$status, $message] = (new PasswordRecoveryService())->reset($_POST, $_SESSION, request_client_ip());
    http_response_code($status);
    $complete = $status === 200;
}
page_start('Choose a new password', 'Set a new password for your TechWorld Academy learner account.');
?>
<section class="section container learning-auth-section"><div class="learning-auth-card"><span class="eyebrow">Account recovery</span><h1>Choose a new password</h1><p>Use at least 12 characters. Reset links expire after one hour and can only be used once.</p><?php if ($message !== ''): ?><div class="learning-alert <?= $complete ? '' : 'is-error' ?>" role="status" aria-live="polite"><?= e($message) ?></div><?php endif; ?><?php if (!$complete): ?><form method="post" action="/reset-password/" class="learning-auth-form"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><input type="hidden" name="token" value="<?= e($token) ?>"><div><label for="new-password">New password</label><input id="new-password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required></div><div><label for="new-password-confirm">Confirm new password</label><input id="new-password-confirm" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" maxlength="72" required></div><button class="button button-primary" type="submit">Update password</button></form><?php endif; ?><p class="learning-auth-switch"><a href="/login/">Return to sign in</a></p></div></section>
<?php page_end(); ?>
