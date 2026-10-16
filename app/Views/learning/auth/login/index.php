<section class="section container learning-auth-section">
    <div class="learning-auth-card">
        <span class="eyebrow">Welcome back</span>
        <h1>Sign in to learn</h1>
        <p>Continue to your TechWorld Academy learner dashboard.</p>
        <?php if ($message !== ''): ?><div class="learning-alert <?= $error ? 'is-error' : '' ?>" role="status" aria-live="polite"><?= e($message) ?></div><?php endif; ?>
        <form method="post" action="/login/" class="learning-auth-form">
            <input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>">
            <input type="hidden" name="continue" value="<?= e($continue) ?>">
            <div><label for="login-email">Email address</label><input id="login-email" name="email" type="email" autocomplete="username" maxlength="254" value="<?= e((string) ($_POST['email'] ?? '')) ?>" required></div>
            <div><label for="login-password">Password</label><input id="login-password" name="password" type="password" autocomplete="current-password" maxlength="200" required></div>
            <button class="button button-primary" type="submit">Sign in <span aria-hidden="true">&rarr;</span></button>
        </form>
        <p class="learning-auth-switch">New to TechWorld Academy? <a href="/register/">Create a learner account</a></p>
        <p class="learning-auth-privacy"><a href="/forgot-password/">Forgot your password?</a> | Need other help? <a href="/contact/">Contact the Academy</a>.</p>
    </div>
</section>
