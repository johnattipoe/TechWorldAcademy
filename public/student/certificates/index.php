<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Controllers\CourseController\CourseController;
use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\Certificate\Certificate;
use App\Models\Enrollment\Enrollment;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

(new StudentMiddleware())->requireStudent();
start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$firestore = null;
$certificates = $courses = $eligibility = [];
$error = '';
$message = '';
$eligibleCount = 0;
$inProgressCount = 0;
try {
    $firestore = new FirestoreService(FirebaseService::firestore());
    $studentId = (string) $_SESSION['learning_user_id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) throw new RuntimeException('Form expired. Refresh and try again.');
        $courseId = is_string($_POST['course_id'] ?? null) ? $_POST['course_id'] : '';
        $course = null;
        foreach ((new CourseController())->index() as $candidate) if (($candidate['id'] ?? '') === $courseId) { $course = $candidate; break; }
        if (!$course) throw new RuntimeException('Course not found.');
        Certificate::issue($firestore, $studentId, (string) ($_SESSION['learning_user_name'] ?? 'Learner'), $course);
        $_SESSION['learning_flash'] = ['success' => true, 'message' => 'Your certificate is ready.'];
        header('Location: /student/certificates/'); exit;
    }
    $certificates = Certificate::forStudent($firestore, $studentId);
    $enrollments = Enrollment::forStudent($firestore, $studentId);
    $alreadyAwarded = array_fill_keys(array_map(static fn(array $item): string => (string) ($item['courseId'] ?? ''), $certificates), true);
    foreach ((new CourseController())->index() as $course) {
        if (isset($alreadyAwarded[(string) $course['id']])) continue;
        if (($enrollments[$course['id']]['status'] ?? '') !== 'approved') continue;
        $courses[] = $course;
        $eligibility[$course['id']] = Certificate::eligibility($firestore, $studentId, $course);
        if ($eligibility[$course['id']][0]) $eligibleCount++;
        else $inProgressCount++;
    }
} catch (Throwable $exception) {
    error_log('Learner certificates could not be loaded: ' . $exception->getMessage());
    $error = in_array($exception->getMessage(), ['Form expired. Refresh and try again.', 'Course not found.', 'Your place in this course must be approved first.', 'This course is not available to your account yet.', 'Payment is required before you can start this course.', 'Your 7-day course trial has ended. Complete payment to continue learning.', 'The trial access window could not be verified. Contact the Academy.', 'This course has no published lessons yet.', 'Complete every published lesson to qualify.', 'Earn at least 70% on each published lesson quiz.', 'Earn at least 70% on each published assignment.', 'This certificate has been revoked. Contact Academy staff.'], true) ? $exception->getMessage() : 'Certificates are temporarily unavailable.';
}
$flash = $_SESSION['learning_flash'] ?? null; unset($_SESSION['learning_flash']);
page_start('My certificates', 'View and verify your TechWorld Academy course certificates.');
?>
<section class="page-hero certificate-portfolio-hero">
    <div class="container">
        <span class="eyebrow">Your learning achievements</span>
        <h1>My certificates</h1>
        <p>Celebrate what you have learned. Review your awards, verify certificates, and see what remains to qualify for your next one.</p>
        <div class="certificate-portfolio-actions">
            <a class="button button-primary" href="/student/my-courses/">Explore my courses <span aria-hidden="true">&rarr;</span></a>
            <a class="certificate-dashboard-link" href="/student/">&larr; Back to dashboard</a>
        </div>
    </div>
</section>

<section class="section container learning-dashboard-content certificate-portfolio">
    <?php if (is_array($flash) && !empty($flash['message'])): ?>
        <div class="learning-alert" role="status"><?= e((string) $flash['message']) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="learning-alert is-error" role="status"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="certificate-summary" aria-label="Certificate summary">
        <article class="certificate-summary-card">
            <span class="certificate-summary-icon" aria-hidden="true">&#9733;</span>
            <div><strong><?= count($certificates) ?></strong><span>Certificates awarded</span></div>
        </article>
        <article class="certificate-summary-card">
            <span class="certificate-summary-icon certificate-summary-icon-ready" aria-hidden="true">&#10003;</span>
            <div><strong><?= $eligibleCount ?></strong><span>Ready to claim</span></div>
        </article>
        <article class="certificate-summary-card">
            <span class="certificate-summary-icon certificate-summary-icon-progress" aria-hidden="true">&#8594;</span>
            <div><strong><?= $inProgressCount ?></strong><span>Courses in progress</span></div>
        </article>
    </div>

    <section class="certificate-section" aria-labelledby="certificate-awards-title">
        <div class="certificate-section-heading">
            <div><span class="eyebrow">Your achievements</span><h2 id="certificate-awards-title">Awarded certificates</h2></div>
            <span><?= count($certificates) ?> total</span>
        </div>
        <?php if ($certificates): ?>
            <div class="certificate-award-grid">
                <?php foreach ($certificates as $certificate): ?>
                    <?php $certificateStatus = (string) ($certificate['status'] ?? 'valid'); $isValid = $certificateStatus === 'valid'; ?>
                    <article class="certificate-award-card<?= $isValid ? '' : ' is-revoked' ?>">
                        <div class="certificate-miniature">
                            <div class="certificate-miniature-inner">
                                <div class="certificate-miniature-header">
                                    <img src="/assets/icons/techworld-mark.svg" alt="" aria-hidden="true">
                                    <span>TECHWORLD<br><strong>ACADEMY</strong></span>
                                    <span class="certificate-miniature-status"><?= $isValid ? 'VERIFIED' : 'REVOKED' ?></span>
                                </div>
                                <span class="certificate-miniature-kicker">CERTIFICATE OF COMPLETION</span>
                                <p class="certificate-miniature-presented">This is to certify that</p>
                                <strong class="certificate-miniature-name"><?= e((string) ($certificate['studentName'] ?? 'Learner')) ?></strong>
                                <span class="certificate-miniature-rule" aria-hidden="true"></span>
                                <p class="certificate-miniature-description">has successfully completed the course</p>
                                <h3><?= e((string) ($certificate['courseTitle'] ?? 'Course completion')) ?></h3>
                                <div class="certificate-miniature-footer">
                                    <span><small>DATE AWARDED</small><strong><?= e(isset($certificate['issuedAt']) && $certificate['issuedAt'] instanceof DateTimeInterface ? $certificate['issuedAt']->format('F j, Y') : 'Date on record') ?></strong></span>
                                    <span class="certificate-miniature-seal" aria-hidden="true"><?= $isValid ? '&#10003;' : '&#10005;' ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="certificate-award-code"><span>Certificate code</span><strong><?= e((string) ($certificate['certificateCode'] ?? 'Unavailable')) ?></strong></div>
                        <a class="button <?= $isValid ? 'button-primary' : 'button-outline' ?>" href="/certificates/verify/?id=<?= rawurlencode((string) $certificate['id']) ?>">Open certificate <span aria-hidden="true">&rarr;</span></a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="certificate-empty-state">
                <span class="certificate-empty-icon" aria-hidden="true">&#9734;</span>
                <div><h3>Your first certificate is waiting</h3><p>Complete an approved course and its required assessments to earn a certificate that you can share and verify.</p></div>
                <a class="button button-outline" href="/student/my-courses/">Browse my courses</a>
            </div>
        <?php endif; ?>
    </section>

    <section class="certificate-section" aria-labelledby="certificate-progress-title">
        <div class="certificate-section-heading">
            <div><span class="eyebrow">Your next milestone</span><h2 id="certificate-progress-title">Certificate progress</h2></div>
            <span><?= count($courses) ?> approved <?= count($courses) === 1 ? 'course' : 'courses' ?></span>
        </div>
        <?php if ($courses): ?>
            <div class="certificate-progress-list">
                <?php foreach ($courses as $course): ?>
                    <?php [$eligible, $reason] = $eligibility[$course['id']]; ?>
                    <article class="certificate-progress-card<?= $eligible ? ' is-ready' : '' ?>">
                        <div class="certificate-progress-mark" aria-hidden="true"><?= $eligible ? '&#10003;' : '&#9675;' ?></div>
                        <div class="certificate-progress-copy">
                            <span class="eyebrow"><?= $eligible ? 'Requirements complete' : 'Keep learning' ?></span>
                            <h3><?= e((string) $course['title']) ?></h3>
                            <p><?= e($reason) ?></p>
                        </div>
                        <?php if ($eligible): ?>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="course_id" value="<?= e((string) $course['id']) ?>">
                                <button class="button button-primary" type="submit">Claim certificate <span aria-hidden="true">&rarr;</span></button>
                            </form>
                        <?php else: ?>
                            <a class="button button-outline" href="/student/learn/?course=<?= rawurlencode((string) $course['id']) ?>">Continue course <span aria-hidden="true">&rarr;</span></a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="certificate-empty-state certificate-empty-progress">
                <div><h3>No approved courses to track yet</h3><p>When a course is approved for your account, its certificate requirements will appear here.</p></div>
                <a class="button button-outline" href="/student/my-courses/">View my courses</a>
            </div>
        <?php endif; ?>
    </section>

    <aside class="certificate-requirements" aria-labelledby="certificate-requirements-title">
        <span class="certificate-requirements-icon" aria-hidden="true">&#9432;</span>
        <div>
            <h2 id="certificate-requirements-title">How to earn a certificate</h2>
            <p>Complete every published lesson in the course, earn at least 70% on each published quiz, and score at least 70% on each required assignment. Your course progress above shows the next step.</p>
        </div>
        <a href="/student/progress/">Review lesson progress <span aria-hidden="true">&rarr;</span></a>
    </aside>
</section>
<?php page_end(); ?>
