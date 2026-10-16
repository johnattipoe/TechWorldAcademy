<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

(new StudentMiddleware())->requireStudent();
$accountName = (string) ($_SESSION['learning_user_name'] ?? 'Learner');
$accountEmail = (string) ($_SESSION['learning_user_email'] ?? '');
$profileMessage = '';
$profileError = false;
start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) throw new RuntimeException('Your form expired. Refresh and try again.');
        $newName = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
        if ($newName === '' || mb_strlen($newName) > 100) throw new RuntimeException('Enter your name using no more than 100 characters.');
        $userId = (string) ($_SESSION['learning_user_id'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/', $userId)) throw new RuntimeException('Your session could not be verified. Please sign in again.');
        (new FirestoreService(FirebaseService::firestore()))->update('academy_users', $userId, ['name' => $newName]);
        $_SESSION['learning_user_name'] = $newName;
        $accountName = $newName;
        $profileMessage = 'Your profile name has been updated.';
    } catch (Throwable $exception) {
        $profileError = true;
        $profileMessage = in_array($exception->getMessage(), ['Your form expired. Refresh and try again.', 'Enter your name using no more than 100 characters.', 'Your session could not be verified. Please sign in again.'], true) ? $exception->getMessage() : 'We could not save your profile just now. Please try again.';
        if ($profileMessage !== $exception->getMessage()) error_log('Learner profile update failed: ' . $exception->getMessage());
    }
}
page_start('Account settings', 'Review your TechWorld Learning account details and security options.');
?>
<section class="page-hero"><div class="container"><span class="eyebrow">Your account</span><h1>Settings</h1><p>Review your account details and manage sign-in security.</p></div></section>
<section class="section container learning-settings-content">
    <a class="text-link" href="/student/">&larr; Back to My learning</a>
    <div class="learning-settings-grid">
        <article class="learning-settings-card"><span class="learning-home-eyebrow">PROFILE</span><h2>Account information</h2><?php if ($profileMessage !== ''): ?><div class="learning-alert <?= $profileError ? 'is-error' : '' ?>" role="status"><?= e($profileMessage) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><label for="profile-name">Name</label><input id="profile-name" name="name" maxlength="100" autocomplete="name" value="<?= e($accountName) ?>" required><?php if ($accountEmail !== ''): ?><p><strong>Email:</strong> <?= e($accountEmail) ?></p><?php endif; ?><p>Email changes require verification. Contact support if you need to update your sign-in address.</p><button class="button button-primary" type="submit">Save name</button></form></article>
        <article class="learning-settings-card"><span class="learning-home-eyebrow">SECURITY</span><h2>Password and sign-in</h2><p>Use the secure password reset flow to update your password.</p><a class="button button-primary" href="/forgot-password/">Reset password</a></article>
        <article class="learning-settings-card"><span class="learning-home-eyebrow">LEARNING</span><h2>Your learning activity</h2><p>Return to your courses, progress, assignments and certificates.</p><a class="button button-outline" href="/student/">Open My learning</a></article>
    </div>
</section>
<?php page_end(); ?>
