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
page_start('Verify certificate', 'Verify a TechWorld Academy course completion certificate.');
?>
<section class="page-hero certificate-screen-only"><div class="container"><span class="eyebrow">Certificate verification</span><h1><?= $valid ? 'Certificate verified' : ($revoked ? 'Certificate revoked' : 'Certificate not found') ?></h1><p><?= $valid ? 'This record confirms a TechWorld Academy course completion certificate.' : ($revoked ? 'This certificate was revoked and is no longer valid.' : 'We could not verify a certificate with that reference.') ?></p></div></section>
<?php if ($valid || $revoked): ?>
<section class="section container learning-dashboard-content"><article class="learning-certificate-card certificate-print-area"><div><span class="eyebrow">TechWorld Academy | <?= $valid ? 'Verified award' : 'Revoked award' ?></span><h2><?= e((string) ($certificate['courseTitle'] ?? 'Course completion')) ?></h2><p>Awarded to <strong><?= e((string) ($certificate['studentName'] ?? 'Learner')) ?></strong></p><p>Issued <?= e(isset($certificate['issuedAt']) && $certificate['issuedAt'] instanceof DateTimeInterface ? $certificate['issuedAt']->format('F j, Y') : 'on record') ?></p><strong>Certificate code: <?= e((string) $certificate['certificateCode']) ?></strong><?php if ($revoked): ?><p>Revoked <?= e(isset($certificate['revokedAt']) && $certificate['revokedAt'] instanceof DateTimeInterface ? $certificate['revokedAt']->format('F j, Y') : '') ?>.</p><?php endif; ?></div><span class="learning-request-status <?= $valid ? 'status-approved' : 'status-rejected' ?>"><?= $valid ? 'Verified' : 'Revoked' ?></span><div class="certificate-print-actions"><button class="button button-primary" type="button" data-print-certificate>Print / Save as PDF</button></div></article></section>
<?php else: ?><section class="section container"><a class="button button-outline" href="/">Return to TechWorld Academy</a></section><?php endif; ?>
<?php page_end(); ?>
