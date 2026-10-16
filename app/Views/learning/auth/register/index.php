<section class="section container learning-auth-section">
    <div class="learning-auth-card">
        <span class="eyebrow">Learner account</span>
        <h1>Create your account</h1>
        <p>Set up your learner profile to get ready for courses and learning tools.</p>
        <?php if ($message !== ''): ?><div class="learning-alert <?= $errors ? 'is-error' : '' ?>" role="status" aria-live="polite"><?= e($message) ?></div><?php endif; ?>
        <form method="post" action="/register/" class="learning-auth-form">
            <input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>">
            <input type="hidden" name="continue" value="<?= e($continue) ?>">
            <div><label for="register-name">Full name</label><input id="register-name" name="name" type="text" autocomplete="name" maxlength="100" value="<?= e(is_string($_POST['name'] ?? null) ? $_POST['name'] : '') ?>" required aria-describedby="register-name-error"<?= isset($errors['name']) ? ' aria-invalid="true"' : '' ?>><small class="learning-field-error" id="register-name-error"><?= e((string) ($errors['name'] ?? '')) ?></small></div>
            <div><label for="register-email">Email address</label><input id="register-email" name="email" type="email" autocomplete="email" maxlength="254" value="<?= e(is_string($_POST['email'] ?? null) ? $_POST['email'] : '') ?>" required aria-describedby="register-email-error"<?= isset($errors['email']) ? ' aria-invalid="true"' : '' ?>><small class="learning-field-error" id="register-email-error"><?= e((string) ($errors['email'] ?? '')) ?></small></div>
            <div><label for="register-password">Password</label><input id="register-password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required aria-describedby="register-password-hint register-password-error"<?= isset($errors['password']) ? ' aria-invalid="true"' : '' ?>><small id="register-password-hint">Use at least 12 characters.</small><small class="learning-field-error" id="register-password-error"><?= e((string) ($errors['password'] ?? '')) ?></small></div>
            <div><label for="register-confirm">Confirm password</label><input id="register-confirm" name="password_confirmation" type="password" autocomplete="new-password" maxlength="72" required aria-describedby="register-confirm-error"<?= isset($errors['password_confirmation']) ? ' aria-invalid="true"' : '' ?>><small class="learning-field-error" id="register-confirm-error"><?= e((string) ($errors['password_confirmation'] ?? '')) ?></small></div>
            <button class="button button-primary" type="submit">Create learner account <span aria-hidden="true">&rarr;</span></button>
        </form>
        <p class="learning-auth-switch">Already have an account? <a href="/login/">Sign in</a></p>
        <p class="learning-auth-privacy">Account information is stored securely and used to provide learner access. See our <a href="/privacy/">Privacy Policy</a>.</p>
    </div>
</section>
