<?php
function render_learning_footer(): void
{
    $publicSite = rtrim((string) env_value('PUBLIC_WEBSITE_URL', env_value('APP_URL', 'http://127.0.0.1:8000')), '/');
    $learningHomeUrl = !empty($_SESSION['learning_user_id']) ? '/student/' : '/';
?>
</main>
<?php if (empty($_SESSION['learning_user_id'])): ?>
<dialog class="learning-auth-dialog" data-learning-auth aria-labelledby="learning-auth-title">
    <div class="learning-auth-dialog-head">
        <a class="learning-platform-brand" href="/" aria-label="TechWorld Learning home"><img class="brand-mark" src="/assets/icons/techworld-mark.svg" alt="" aria-hidden="true"><span>TechWorld <strong>Learning</strong></span></a>
        <button class="learning-auth-close" type="button" data-auth-close aria-label="Close sign-in window">&times;</button>
    </div>
    <div class="learning-auth-dialog-content">
        <span class="eyebrow">Learn with TechWorld</span>
        <h2 id="learning-auth-title" data-auth-title>Welcome back</h2>
        <p data-auth-description>Sign in to continue your learning journey.</p>
        <div class="learning-auth-tabs" role="tablist" aria-label="Account access">
            <button type="button" role="tab" aria-selected="true" data-auth-tab="login">Sign in</button>
            <button type="button" role="tab" aria-selected="false" data-auth-tab="register">Join for free</button>
        </div>
        <div class="learning-auth-social" aria-label="Social sign-in options">
            <button type="button" data-social-provider="google"><span aria-hidden="true">G</span> Continue with Google</button>
            <button type="button" data-social-provider="facebook"><span aria-hidden="true">f</span> Continue with Facebook</button>
            <button type="button" data-social-provider="apple"><span aria-hidden="true">&#63743;</span> Continue with Apple</button>
            <p class="learning-social-status" data-social-status role="status" aria-live="polite">Social sign-in is not configured yet. Use email, or configure these providers in Firebase Authentication before enabling them.</p>
        </div>
        <div class="learning-auth-divider"><span>or continue with email</span></div>
        <form method="post" action="/login/" class="learning-auth-form" data-auth-form="login">
            <input type="hidden" name="csrf_token" value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>">
            <input type="hidden" name="continue" value="<?= e((string) ($_SERVER['REQUEST_URI'] ?? '/student/')) ?>">
            <div><label for="modal-login-email">Email address</label><input id="modal-login-email" name="email" type="email" autocomplete="username" maxlength="254" required></div>
            <div><label for="modal-login-password">Password</label><input id="modal-login-password" name="password" type="password" autocomplete="current-password" maxlength="200" required></div>
            <button class="button button-primary" type="submit">Sign in <span aria-hidden="true">&rarr;</span></button>
            <a class="learning-modal-forgot" href="/forgot-password/">Forgot your password?</a>
        </form>
        <form method="post" action="/register/" class="learning-auth-form" data-auth-form="register" hidden>
            <input type="hidden" name="csrf_token" value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>">
            <input type="hidden" name="continue" value="<?= e((string) ($_SERVER['REQUEST_URI'] ?? '/student/')) ?>">
            <div><label for="modal-register-name">Full name</label><input id="modal-register-name" name="name" type="text" autocomplete="name" maxlength="100" required></div>
            <div><label for="modal-register-email">Email address</label><input id="modal-register-email" name="email" type="email" autocomplete="email" maxlength="254" required></div>
            <div><label for="modal-register-password">Password</label><input id="modal-register-password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required><small>Use at least 12 characters.</small></div>
            <div><label for="modal-register-confirm">Confirm password</label><input id="modal-register-confirm" name="password_confirmation" type="password" autocomplete="new-password" maxlength="72" required></div>
            <button class="button button-primary" type="submit">Create learner account <span aria-hidden="true">&rarr;</span></button>
        </form>
        <p class="learning-auth-legal">By continuing, you agree to TechWorld Academy’s <a href="<?= e($publicSite) ?>/terms/">Terms of Use</a> and <a href="<?= e($publicSite) ?>/privacy/">Privacy Policy</a>. Having trouble logging in? <a href="<?= e($publicSite) ?>/contact/">Contact learner support</a>.</p>
        <p class="learning-auth-security-note">We use essential cookies to keep sign-in secure. See our <a href="<?= e($publicSite) ?>/privacy/">Privacy Policy</a> for details.</p>
    </div>
</dialog>
<?php endif; ?>
<?php if (!in_array($_COOKIE['site_cookie_consent'] ?? '', ['accepted', 'rejected'], true)): ?>
<section class="cookie-consent-banner" aria-labelledby="cookie-consent-title" data-cookie-consent>
    <div class="cookie-consent-copy">
        <h2 id="cookie-consent-title"><?= e(locale_text('Cookie choices', 'Choix des cookies', 'Opciones de cookies')) ?></h2>
        <p><?= e(locale_text('We use essential cookies for secure sign-in, your language choice and this preference. Accept to confirm, or Cancel to decline optional cookies. Essential cookies remain active so the site can work.', 'Nous utilisons des cookies essentiels pour la connexion sécurisée, votre langue et ce choix. Acceptez ou annulez pour refuser les cookies facultatifs. Les cookies essentiels restent actifs.', 'Usamos cookies esenciales para iniciar sesión de forma segura, recordar el idioma y guardar esta opción. Acepta o cancela para rechazar cookies opcionales. Las cookies esenciales siguen activas.')) ?> <a href="<?= e($publicSite) ?>/privacy/"><?= e(locale_text('Privacy Policy', 'Politique de confidentialité', 'Política de privacidad')) ?></a>.</p>
    </div>
    <form method="post" action="/cookie-consent/" class="cookie-consent-actions">
        <input type="hidden" name="csrf_token" value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>">
        <input type="hidden" name="return_to" value="<?= e((string) ($_SERVER['REQUEST_URI'] ?? '/')) ?>">
        <button type="submit" name="choice" value="rejected" class="cookie-consent-cancel"><?= e(locale_text('Cancel', 'Annuler', 'Cancelar')) ?></button>
        <button type="submit" name="choice" value="accepted" class="cookie-consent-accept"><?= e(locale_text('Accept', 'Accepter', 'Aceptar')) ?></button>
    </form>
</section>
<?php endif; ?>
<button class="back-to-top learning-back-to-top" type="button" aria-label="Back to top" title="Back to top">&#8593;</button>
<div class="chat-assistant learning-chat-assistant" data-chat-assistant>
    <section class="chat-panel" id="chat-panel" aria-labelledby="chat-title" hidden>
        <div class="chat-panel-heading">
            <div><span class="chat-status-mark" aria-hidden="true"></span><div><h2 id="chat-title">TechWorld Learning help</h2><p>Course and account guidance</p></div></div>
            <button class="chat-close" type="button" aria-label="Close chat" data-chat-close>&#215;</button>
        </div>
        <div class="chat-messages" id="chat-messages" role="log" aria-live="polite" aria-relevant="additions text">
            <p class="chat-message chat-message-assistant">Hi! I can help you browse courses, learn about team programs or find support.</p>
        </div>
        <div class="chat-quick-questions" aria-label="Suggested questions">
            <button type="button" data-chat-prompt="courses">Browse courses</button>
            <button type="button" data-chat-prompt="team training">Team learning</button>
            <button type="button" data-chat-prompt="contact support">Contact support</button>
        </div>
        <form class="chat-form" id="chat-form">
            <label class="visually-hidden" for="chat-input">Ask TechWorld a question</label>
            <input id="chat-input" name="message" type="text" maxlength="300" autocomplete="off" placeholder="Type a question…" required>
            <button type="submit" aria-label="Send question"><span aria-hidden="true">&#8593;</span></button>
        </form>
        <p class="chat-privacy-note">Automated guidance only. Your message stays in this browser and is not sent to a server.</p>
    </section>
    <button class="chat-launcher" type="button" id="chat-toggle" aria-expanded="false" aria-controls="chat-panel" aria-label="Open TechWorld chat">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H5l1.8-3.2A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8 11h.01M12 11h.01M16 11h.01"/></svg><span class="visually-hidden">Chat</span>
    </button>
</div>
<footer class="learning-platform-footer">
    <div class="container learning-footer-grid">
        <div class="learning-footer-brand"><a class="learning-platform-brand" href="<?= e($learningHomeUrl) ?>" aria-label="TechWorld Learning home"><img class="brand-mark" src="/assets/icons/techworld-mark.svg" alt="" aria-hidden="true"><span>TechWorld <strong>Learning</strong></span></a><p>Practical technology courses, guided practice and a place to track your progress.</p></div>
        <div><strong>Explore</strong><a href="/#catalogue">Browse courses</a><a href="/?q=Web#catalogue">Web development</a><a href="/?q=Data#catalogue">Data analytics</a><a href="/?q=AI#catalogue">AI and automation</a></div>
        <div><strong>Your learning</strong><?php if (!empty($_SESSION['learning_user_id'])): ?><a href="/student/">Dashboard</a><a href="/student/my-courses/">My courses</a><a href="/student/progress/">Progress</a><a href="/student/certificates/">Certificates</a><?php else: ?><a href="/login/">Sign in</a><a href="/register/">Create an account</a><?php endif; ?></div>
        <div><strong>TechWorld Academy</strong><a href="<?= e($publicSite) ?>/">Main website</a><a href="<?= e($publicSite) ?>/faq/">Help and FAQ</a><a href="<?= e($publicSite) ?>/contact/">Contact the team</a><a href="<?= e($publicSite) ?>/privacy/">Privacy policy</a><a href="<?= e($publicSite) ?>/terms/">Terms of use</a></div>
    </div>
    <div class="container learning-footer-legal"><span>&copy; <?= date('Y') ?> TechWorld Learning</span><span>Learn. Practise. Progress.</span></div>
</footer>
<script src="/assets/js/navigation/navigation.js" defer></script>
<script src="/assets/js/main/main.js" defer></script>
<script src="/assets/js/form-validation/form-validation.js" defer></script>
<script src="/assets/js/compiled/main.js" defer></script>
</body>
</html>
<?php
}
?>
