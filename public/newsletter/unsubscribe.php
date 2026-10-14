<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Models\NewsletterSubscriber\NewsletterSubscriber;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\NewsletterUnsubscribeService\NewsletterUnsubscribeService;
use App\Services\RateLimitService\RateLimitService;

start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$subscriberId = (string) ($_POST['subscriber'] ?? $_GET['subscriber'] ?? '');
$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
$validLink = NewsletterUnsubscribeService::verify($subscriberId, $token);
$complete = ($_GET['status'] ?? '') === 'done';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
        http_response_code(419);
        $errorMessage = locale_text('This confirmation expired. Open the unsubscribe link from your email again.', 'Cette confirmation a expiré. Ouvrez à nouveau le lien reçu par e-mail.', 'La confirmación ha caducado. Vuelve a abrir el enlace del correo.');
    } elseif (!$validLink) {
        http_response_code(400);
        $errorMessage = locale_text('This unsubscribe link is invalid. Contact the Academy for help.', 'Ce lien de désabonnement est invalide. Contactez l’Académie pour obtenir de l’aide.', 'Este enlace para darse de baja no es válido. Contacta con la Academia.');
    } else {
        try {
            $rate = (new RateLimitService())->consume((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 'newsletter-unsubscribe', 10, 3600);
            if (!$rate['allowed']) {
                http_response_code(429);
                header('Retry-After: ' . (int) $rate['retry_after']);
                $errorMessage = locale_text('Too many attempts. Please try again later.', 'Trop de tentatives. Veuillez réessayer plus tard.', 'Demasiados intentos. Vuelve a probar más tarde.');
            } else {
                NewsletterSubscriber::unsubscribeById(new FirestoreService(FirebaseService::firestore()), $subscriberId);
                header('Location: /newsletter/unsubscribe/?status=done', true, 303);
                exit;
            }
        } catch (Throwable $exception) {
            error_log('Newsletter unsubscribe failed: ' . $exception->getMessage());
            http_response_code(503);
            $errorMessage = locale_text('We could not update your subscription right now. Please try again later.', 'Nous ne pouvons pas modifier votre abonnement pour le moment. Veuillez réessayer plus tard.', 'No podemos actualizar tu suscripción ahora. Vuelve a probar más tarde.');
        }
    }
}

page_start(locale_text('Unsubscribe from updates', 'Se désabonner des actualités', 'Cancelar la suscripción'));
?>
<section class="section container admin-page">
    <div class="admin-login" role="region" aria-labelledby="unsubscribe-title">
        <span class="eyebrow">TechWorld Academy</span>
        <h1 id="unsubscribe-title"><?= e(locale_text('Newsletter preferences', 'Préférences de la newsletter', 'Preferencias del boletín')) ?></h1>
        <?php if ($complete): ?>
            <p><?= e(locale_text('You have been unsubscribed from academy email updates.', 'Votre adresse est désabonnée des actualités de l’Académie.', 'Te has dado de baja de los correos de la Academia.')) ?></p>
        <?php elseif ($errorMessage !== ''): ?>
            <div class="admin-alert is-error" role="alert"><?= e($errorMessage) ?></div>
        <?php elseif (!$validLink): ?>
            <div class="admin-alert is-error" role="alert"><?= e(locale_text('This unsubscribe link is invalid or incomplete. Open the link from your TechWorld Academy email.', 'Ce lien de désabonnement est invalide ou incomplet. Ouvrez le lien reçu par e-mail de TechWorld Academy.', 'Este enlace para darse de baja no es válido o está incompleto. Abre el enlace del correo de TechWorld Academy.')) ?></div>
        <?php else: ?>
            <p><?= e(locale_text('Confirm below to stop receiving occasional academy email updates.', 'Confirmez ci-dessous pour ne plus recevoir les actualités occasionnelles de l’Académie.', 'Confirma abajo para dejar de recibir novedades ocasionales de la Academia.')) ?></p>
            <form method="post" action="/newsletter/unsubscribe/">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="subscriber" value="<?= e($subscriberId) ?>">
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <button class="button button-primary" type="submit"><?= e(locale_text('Confirm unsubscribe', 'Confirmer le désabonnement', 'Confirmar baja')) ?></button>
            </form>
        <?php endif; ?>
        <p><a class="text-link" href="/"><?= e(locale_text('Return to TechWorld Academy', 'Retourner sur TechWorld Academy', 'Volver a TechWorld Academy')) ?></a></p>
    </div>
</section>
<?php page_end(); ?>
