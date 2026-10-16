<section class="page-hero"><div class="container"><span class="eyebrow">Learner area</span><h1>My courses<br><span>&amp; requests.</span></h1><p>Open your enrolled courses and track requests that are waiting for review.</p></div></section>
<section class="section container learning-dashboard-content">
    <?php if (is_array($flash) && !empty($flash['message'])): ?><div class="learning-alert <?= empty($flash['success']) ? 'is-error' : '' ?>" role="status" aria-live="polite"><?= e((string) $flash['message']) ?></div><?php endif; ?>
    <?php if ($enrollmentLoadError): ?><div class="learning-alert is-error" role="status">Your course requests could not be loaded right now. Please refresh in a moment.</div>
    <?php elseif (!$enrollments): ?>
        <div class="learning-empty"><h2>No courses yet</h2><p>Explore the catalogue. You can enroll directly in free courses or request a place in courses that need review.</p><a class="button button-primary" href="/student/">Explore courses <span aria-hidden="true">&rarr;</span></a></div>
    <?php else: ?>
        <div class="learning-request-list">
            <?php foreach ($enrollments as $enrollment): ?>
                <article class="learning-request-card"><div><span class="eyebrow"><?= ($enrollment['status'] ?? '') === 'approved' ? 'Enrolled course' : 'Course request' ?></span><h2><?= e((string) ($enrollment['courseTitle'] ?? 'Course')) ?></h2><p><?= ($enrollment['status'] ?? '') === 'approved' ? 'Enrolled' : 'Requested' ?> <?= e(isset($enrollment['requestedAt']) && $enrollment['requestedAt'] instanceof DateTimeInterface ? $enrollment['requestedAt']->format('M j, Y') : 'recently') ?></p></div><span class="learning-request-status status-<?= e((string) ($enrollment['status'] ?? 'requested')) ?>"><?= e(ucfirst((string) ($enrollment['status'] ?? 'requested'))) ?></span></article>
                <?php if (($enrollment['status'] ?? '') === 'approved'): ?><p class="resource-safety-note"><a class="button button-primary" href="/student/learn/?course=<?= rawurlencode((string) ($enrollment['courseId'] ?? '')) ?>">Open course lessons</a></p><?php elseif (($enrollment['status'] ?? '') === 'requested'): ?><p class="learning-request-note">Your request is waiting for staff review. This does not grant course access yet.</p><?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
