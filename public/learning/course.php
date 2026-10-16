<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Controllers\CourseController\CourseController;
use App\Models\Lesson\Lesson;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

$rawCourseId = $_GET['id'] ?? '';
$courseId = is_string($rawCourseId) ? trim($rawCourseId) : '';
$course = null;
if ($courseId !== '' && strlen($courseId) <= 1500) {
    foreach ((new CourseController())->index() as $candidate) {
        if (isset($candidate['id']) && hash_equals((string) $candidate['id'], $courseId)) {
            $course = $candidate;
            break;
        }
    }
}

if ($course === null) {
    http_response_code(404);
    page_start('Course not found', 'This course is not available in the current catalogue.');
echo '<section class="learning-course-not-found container"><span class="learning-home-eyebrow">COURSE CATALOGUE</span><h1>We couldn’t find that course.</h1><p>It may have moved or is no longer available.</p><a class="learning-home-primary" href="/learning/#catalogue">Browse available courses <span aria-hidden="true">&#8594;</span></a></section>';
    page_end();
    exit;
}

$title = (string) ($course['title'] ?? 'Technology course');
$description = (string) ($course['description'] ?? 'Build practical technology skills with a guided learning path.');
$level = (string) ($course['level'] ?? 'All levels');
$duration = (string) ($course['duration'] ?? 'Flexible');
$publishedLessons = [];
if (env_value('FIREBASE_PROJECT_ID') && env_value('FIREBASE_CREDENTIALS')) {
    try {
        $publishedLessons = Lesson::forCourse(new FirestoreService(FirebaseService::firestore()), (string) ($course['id'] ?? ''), true);
    } catch (Throwable $exception) {
        error_log('Public course outline could not be loaded: ' . $exception->getMessage());
    }
}
$courseVisual = strtolower($title);
$visualLabel = 'TECH';
foreach (['database' => 'SQL', 'data' => 'DATA', 'cyber' => 'SEC', 'cloud' => 'CLOUD', 'python' => 'PY', 'ai' => 'AI', 'web' => '&lt;/&gt;', 'design' => 'UX', 'software' => 'CODE', 'support' => 'IT', 'network' => 'NET', 'mobile' => 'APP', 'marketing' => 'MKT', 'product' => 'PM'] as $match => $label) {
    if (str_contains($courseVisual, $match)) { $visualLabel = $label; break; }
}

page_start($title, $description);
?>
<nav class="learning-course-breadcrumb container" aria-label="Breadcrumb"><a href="/learning/#catalogue">Courses</a><span aria-hidden="true">/</span><span aria-current="page"><?= e($title) ?></span></nav>
<section class="learning-course-detail-hero">
    <div class="container learning-course-detail-grid">
        <div class="learning-course-detail-copy">
            <span class="learning-home-eyebrow">TECHWORLD COURSE</span><?= course_card_badges($course) ?>
            <h1><?= e($title) ?></h1>
            <p><?= e($description) ?></p>
            <div class="learning-course-detail-meta"><span><small>LEVEL</small><strong><?= e($level) ?></strong></span><span><small>ESTIMATED DURATION</small><strong><?= e($duration) ?></strong></span><span><small>FORMAT</small><strong>Online learning</strong></span></div>
            <div class="learning-course-detail-actions">
                <?php if (!empty($_SESSION['learning_user_id'])): ?>
                    <?php if (($course['accessType'] ?? '') === 'paid' && (float) ($course['price'] ?? 0) <= 0): ?><a class="learning-home-primary" href="/contact/?course=<?= rawurlencode($title) ?>">Ask about course pricing <span aria-hidden="true">&#8594;</span></a><?php else: ?><form method="post" action="<?= ($course['accessType'] ?? 'free') === 'paid' ? '/checkout/' : '/student/enroll/' ?>"><input type="hidden" name="csrf_token" value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>"><input type="hidden" name="course_id" value="<?= e((string) ($course['id'] ?? '')) ?>"><button class="learning-home-primary" type="submit"><?php if (($course['accessType'] ?? 'free') === 'paid'): ?>Pay <?= e((string) ($course['currency'] ?? 'GHS')) ?> <?= e(number_format((float) ($course['price'] ?? 0), 2)) ?> with Mobile Money<?php elseif (($course['accessType'] ?? 'free') === 'free_trial'): ?>Start your 7-day free trial<?php elseif (($course['accessType'] ?? 'free') === 'free'): ?>Enroll for free<?php else: ?>Request a place<?php endif; ?> <span aria-hidden="true">&#8594;</span></button></form><?php endif; ?>
                <?php else: ?>
                    <?php $courseContinue = '/learn/course/?id=' . rawurlencode((string) ($course['id'] ?? '')); ?>
                    <a class="learning-home-primary" href="/register/?continue=<?= rawurlencode($courseContinue) ?>">Create an account to continue <span aria-hidden="true">&#8594;</span></a><a class="learning-home-secondary" href="/login/?continue=<?= rawurlencode($courseContinue) ?>">Already have an account? Sign in</a>
                <?php endif; ?>
            </div>
            <p class="learning-course-review-note"><?php if (($course['accessType'] ?? 'free') === 'paid' && (float) ($course['price'] ?? 0) > 0): ?>Pay <?= e((string) ($course['currency'] ?? 'GHS')) ?> <?= e(number_format((float) ($course['price'] ?? 0), 2)) ?> securely with Paystack Mobile Money before starting.<?php elseif (($course['accessType'] ?? 'free') === 'paid'): ?>The Academy is setting the course price. Contact us for details before enrolling.<?php elseif (($course['accessType'] ?? 'free') === 'free_trial'): ?>Start learning immediately with a 7-day trial. Payment is required to continue after the trial ends.<?php elseif (($course['accessType'] ?? 'free') === 'free'): ?>This course is free. Enroll and start learning immediately.<?php else: ?>Course requests are reviewed by the TechWorld Academy team. Access to course lessons is provided after approval.<?php endif; ?></p>
        </div>
        <div class="learning-course-detail-visual <?= e((string) ($course['color'] ?? 'icon-blue')) ?>" aria-hidden="true"><div class="learning-detail-orbit"></div><div class="learning-detail-mark"><?= $visualLabel === '&lt;/&gt;' ? '&lt;/&gt;' : e($visualLabel) ?></div><span>LEARN BY DOING</span></div>
    </div>
</section>
<section class="learning-course-detail-content container">
    <div><div class="learning-course-overview"><span class="learning-home-eyebrow">COURSE OVERVIEW</span><h2>Build skills through focused practice.</h2><p><?= e($description) ?></p><p>Course materials and activities are added by the TechWorld Academy team. Once your place is approved, your learner dashboard will show the lessons and activities available for this course.</p></div>
        <section class="learning-course-outline" aria-labelledby="course-outline-title"><h2 id="course-outline-title">Course outline</h2><?php if ($publishedLessons): ?><ol><?php foreach ($publishedLessons as $lesson): ?><li><span><?= (int) ($lesson['position'] ?? 1) ?></span><strong><?= e((string) ($lesson['title'] ?? 'Lesson')) ?></strong><small><?= (int) ($lesson['durationMinutes'] ?? 0) ?> min</small></li><?php endforeach; ?></ol><?php else: ?><p>The lesson outline will appear here when the course team publishes it. Approved learners can access lesson materials from their dashboard.</p><?php endif; ?></section>
    </div>
    <aside class="learning-course-steps"><h2>How course access works</h2><ol><li><span>1</span><div><strong>Create your learner account</strong><p>Set up a secure profile to keep your requests and learning progress together.</p></div></li><li><span>2</span><div><strong>Request a place</strong><p>Our team reviews course requests and updates the status in your account.</p></div></li><li><span>3</span><div><strong>Learn and track progress</strong><p>Approved learners can access published lessons and course activities.</p></div></li></ol></aside>
</section>
<section class="learning-course-more"><div class="container"><h2>Explore more courses</h2><p>Compare topics and choose another skill to explore.</p><a class="learning-home-primary" href="/learning/#catalogue">Back to all courses <span aria-hidden="true">&#8594;</span></a></div></section>
<?php page_end(); ?>
