<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Models\Certificate\Certificate;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

$certificate = null;
$valid = false;
$revoked = false;
try {
    $id = is_string($_GET['id'] ?? null) ? $_GET['id'] : '';
    $certificate = Certificate::verify(new FirestoreService(FirebaseService::firestore()), $id);
    $valid = is_array($certificate) && ($certificate['status'] ?? '') === 'valid' && !empty($certificate['certificateCode']);
    $revoked = is_array($certificate) && ($certificate['status'] ?? '') === 'revoked';
} catch (Throwable $exception) {
    error_log('Certificate verification lookup failed: ' . $exception->getMessage());
}
if (!$certificate) http_response_code(404);
$appUrl = rtrim((string) env_value('APP_URL', env_value('RENDER_EXTERNAL_URL', '')), '/');
$verificationUrl = $appUrl . '/certificates/verify/?id=' . rawurlencode((string) ($certificate['id'] ?? ''));
page_start('Verify certificate', 'Verify a TechWorld Academy course completion certificate.');
?>
<section class="certificate-verification-page">
    <header class="certificate-verification-heading certificate-screen-only">
        <div class="container">
            <a class="certificate-dashboard-link" href="<?= !empty($_SESSION['learning_user_id']) ? '/student/certificates/' : '/' ?>">&larr; <?= !empty($_SESSION['learning_user_id']) ? 'Back to my certificates' : 'Back to TechWorld Academy' ?></a>
            <span class="eyebrow">Public certificate verification</span>
            <h1><?= $valid ? 'Certificate verified' : ($revoked ? 'Certificate revoked' : 'Certificate not found') ?></h1>
            <p><?= $valid ? 'This certificate matches an official TechWorld Academy award record.' : ($revoked ? 'This award is no longer valid. Its record is retained for verification.' : 'We could not verify a certificate with that reference.') ?></p>
            <?php if ($valid): ?><span class="certificate-verification-status"><span aria-hidden="true">&#10003;</span> Verified award</span><?php elseif ($revoked): ?><span class="certificate-verification-status is-revoked"><span aria-hidden="true">&#10005;</span> Revoked award</span><?php endif; ?>
        </div>
    </header>
    <?php if ($valid || $revoked): ?>
        <section class="certificate-document-wrap container">
            <article class="certificate-document<?= $revoked ? ' is-revoked' : '' ?>" aria-label="<?= $valid ? 'Verified course completion certificate' : 'Revoked course completion certificate' ?>">
                <div class="certificate-document-border" aria-hidden="true"></div>
                <div class="certificate-document-content">
                    <header class="certificate-document-brand">
                        <img src="/assets/icons/techworld-mark.svg" alt="" aria-hidden="true">
                        <span>TECHWORLD<br><strong>ACADEMY</strong></span>
                    </header>
                    <div class="certificate-document-status"><?= $valid ? 'OFFICIALLY VERIFIED' : 'REVOKED CERTIFICATE' ?></div>
                    <div class="certificate-document-heading">
                        <span>AWARD OF ACHIEVEMENT</span>
                        <h2>Certificate<br>of Completion</h2>
                        <div class="certificate-document-flourish" aria-hidden="true"><span></span>&#10022;<span></span></div>
                    </div>
                    <div class="certificate-document-recipient">
                        <p>This certificate is proudly presented to</p>
                        <h1><?= e((string) ($certificate['studentName'] ?? 'Learner')) ?></h1>
                        <span class="certificate-document-rule" aria-hidden="true"></span>
                    </div>
                    <div class="certificate-document-course">
                        <p>in recognition of successfully completing</p>
                        <h2><?= e((string) ($certificate['courseTitle'] ?? 'Course completion')) ?></h2>
                        <p class="certificate-document-citation">Demonstrating dedication, curiosity, and commitment to continued learning.</p>
                    </div>
                    <?php if ($revoked): ?>
                        <p class="certificate-document-revocation">This certificate was revoked<?= isset($certificate['revokedAt']) && $certificate['revokedAt'] instanceof DateTimeInterface ? ' on ' . e($certificate['revokedAt']->format('F j, Y')) : '' ?>.</p>
                    <?php endif; ?>
                    <footer class="certificate-document-footer">
                        <div class="certificate-document-signature">
                            <span class="certificate-document-signature-line"></span>
                            <strong>TechWorld Academy</strong>
                            <small>ISSUING INSTITUTION</small>
                        </div>
                        <div class="certificate-document-seal" aria-label="<?= $valid ? 'Verified certificate' : 'Revoked certificate' ?>">
                            <span><?= $valid ? '&#10003;' : '&#10005;' ?></span>
                            <small><?= $valid ? 'VERIFIED' : 'REVOKED' ?></small>
                        </div>
                        <div class="certificate-document-signature certificate-document-date">
                            <span class="certificate-document-signature-line"></span>
                            <strong><?= e(isset($certificate['issuedAt']) && $certificate['issuedAt'] instanceof DateTimeInterface ? $certificate['issuedAt']->format('F j, Y') : 'Date on record') ?></strong>
                            <small>DATE AWARDED</small>
                        </div>
                    </footer>
                    <div class="certificate-document-code">
                        <span>Certificate ID</span>
                        <strong><?= e((string) ($certificate['certificateCode'] ?? 'Unavailable')) ?></strong>
                        <span class="certificate-document-verify-url">Verify online: <?= e($verificationUrl) ?></span>
                    </div>
                </div>
            </article>
            <div class="certificate-document-actions certificate-screen-only">
                <p>Save or print this landscape certificate. Its public verification page confirms the award status.</p>
                <button class="button button-primary" type="button" data-print-certificate>Print / Save as PDF <span aria-hidden="true">&#8599;</span></button>
            </div>
        </section>
    <?php else: ?>
        <section class="container certificate-not-found">
            <span aria-hidden="true">&#9432;</span>
            <div><h2>No matching certificate</h2><p>Check that the link or certificate reference was copied correctly.</p></div>
            <a class="button button-outline" href="/">Return to TechWorld Academy</a>
        </section>
    <?php endif; ?>
</section>
<?php page_end(); ?>
