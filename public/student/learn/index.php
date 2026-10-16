<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Controllers\CourseController\CourseController;
use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\LearningProgress\LearningProgress;
use App\Models\Lesson\Lesson;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\LearningAccess\LearningAccess;

(new StudentMiddleware())->requireStudent();
$courseId = is_string($_GET['course'] ?? null) ? trim($_GET['course']) : '';
$course = null;
foreach ((new CourseController())->index() as $candidate) {
    if (($candidate['id'] ?? '') === $courseId) {
        $course = $candidate;
        break;
    }
}

$lessons = [];
$progress = [];
$error = '';
$completedCount = 0;
$nextLesson = null;
$progressPercent = 0;

if (!$course) {
    http_response_code(404);
    $error = 'We could not find that course. Return to your learning dashboard to choose another course.';
} else {
    try {
        $firestore = new FirestoreService(FirebaseService::firestore());
        LearningAccess::assertApproved($firestore, (string) $_SESSION['learning_user_id'], $courseId);
        $lessons = Lesson::forCourse($firestore, $courseId, true);
        $progress = LearningProgress::forStudent($firestore, (string) $_SESSION['learning_user_id']);

        foreach ($lessons as $lesson) {
            $lessonId = (string) ($lesson['id'] ?? '');
            $record = $progress[$lessonId] ?? null;
            $isComplete = is_array($record)
                && ($record['courseId'] ?? '') === $courseId
                && ($record['status'] ?? '') === 'completed';
            if ($isComplete) {
                $completedCount++;
            } elseif ($nextLesson === null) {
                $nextLesson = $lesson;
            }
        }
        $progressPercent = $lessons ? (int) round(($completedCount / count($lessons)) * 100) : 0;
    } catch (Throwable $exception) {
        http_response_code(403);
        $error = in_array($exception->getMessage(), [
            'This course is not available to your account yet.',
            'Payment is required before you can start this course.',
            'Your 7-day course trial has ended. Payment is required to continue learning.',
        ], true) ? $exception->getMessage() : 'Course lessons are temporarily unavailable. Please try again in a few minutes.';
    }
}

$courseTitle = (string) ($course['title'] ?? 'Course learning');
$firstLesson = $lessons[0] ?? null;
$learningAction = $nextLesson ?? $firstLesson;
$courseLevel = (string) ($course['level'] ?? 'All levels');
$courseDuration = (string) ($course['duration'] ?? 'Self-paced');
$helpUrl = rtrim((string) env_value('PUBLIC_WEBSITE_URL', env_value('APP_URL', 'http://127.0.0.1:8000')), '/') . '/contact/';

page_start($courseTitle . ' learning', 'Study ' . $courseTitle . ' lessons, track course progress and continue learning.');
?>
<section class="learning-course-hero">
    <div class="container learning-course-hero-inner">
        <div class="learning-course-hero-copy">
            <a class="learning-course-back" href="/student/" aria-label="Back to my learning"><span aria-hidden="true">&larr;</span> My learning</a>
            <span class="eyebrow">Your course workspace</span>
            <h1><?= e($courseTitle) ?></h1>
            <p><?= e((string) ($course['description'] ?? 'Build practical skills with guided lessons and activities.')) ?></p>
            <div class="learning-course-facts" aria-label="Course details">
                <span><strong><?= e($courseLevel) ?></strong> level</span>
                <span><strong><?= e($courseDuration) ?></strong> estimated duration</span>
                <?php if ($lessons): ?><span><strong><?= count($lessons) ?></strong> published lessons</span><?php endif; ?>
            </div>
        </div>
        <aside class="learning-course-progress-card" aria-label="Course progress">
            <div class="learning-course-progress-heading"><span>Your progress</span><strong><?= $progressPercent ?>%</strong></div>
            <div class="learning-course-progress-track" role="progressbar" aria-label="Course completion" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $progressPercent ?>"><span style="width: <?= $progressPercent ?>%"></span></div>
            <?php if ($lessons): ?>
                <p><?= $completedCount ?> of <?= count($lessons) ?> lessons completed</p>
                <?php if ($learningAction): ?><a class="button button-primary learning-course-resume" href="/student/lesson/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode((string) $learningAction['id']) ?>"><?= $completedCount > 0 ? 'Continue learning' : 'Start learning' ?> <span aria-hidden="true">&rarr;</span></a><?php endif; ?>
            <?php else: ?>
                <p>Your progress will appear here once lessons are published.</p>
            <?php endif; ?>
        </aside>
    </div>
</section>

<section class="container learning-course-workspace" aria-labelledby="learning-curriculum-title">
    <?php if ($error !== ''): ?>
        <div class="learning-course-alert" role="alert">
            <span class="learning-course-alert-icon" aria-hidden="true">!</span>
            <div><h2>We can’t open this course yet</h2><p><?= e($error) ?></p>
                <?php if ($course && in_array(($course['accessType'] ?? ''), ['paid', 'free_trial'], true) && (float) ($course['price'] ?? 0) > 0): ?>
                    <form method="post" action="/checkout/"><input type="hidden" name="csrf_token" value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>"><input type="hidden" name="course_id" value="<?= e($courseId) ?>"><button class="button button-primary" type="submit">Pay <?= e((string) ($course['currency'] ?? 'GHS')) ?> <?= e(number_format((float) $course['price'], 2)) ?> with Mobile Money</button></form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="learning-course-workspace-heading">
            <div><span class="eyebrow">Course curriculum</span><h2 id="learning-curriculum-title">Your lessons</h2><p>Work through each lesson at your own pace. Your completed lessons are saved to your account.</p></div>
            <?php if ($lessons): ?><span class="learning-curriculum-count"><?= $completedCount ?> / <?= count($lessons) ?> complete</span><?php endif; ?>
        </div>

        <?php if (!$lessons): ?>
            <article class="learning-curriculum-empty">
                <div class="learning-empty-art" aria-hidden="true"><span>&#128218;</span><i></i><i></i><i></i></div>
                <div class="learning-empty-copy"><span class="learning-course-status"><i></i> Enrollment approved</span><h3>Lessons are being prepared</h3><p>Your place in <strong><?= e($courseTitle) ?></strong> is confirmed. The course team hasn’t published its lessons yet. You can return to your dashboard while they finish preparing the materials.</p><div class="learning-course-empty-actions"><a class="button button-primary" href="/student/">Back to my learning</a><a class="text-link" href="<?= e($helpUrl) ?>" target="_blank" rel="noopener noreferrer">Contact support <span aria-hidden="true">&#8599;</span></a></div><small>Once lessons are published, they’ll appear here automatically.</small></div>
            </article>
        <?php else: ?>
            <ol class="learning-curriculum-list">
                <?php foreach ($lessons as $index => $lesson):
                    $lessonId = (string) ($lesson['id'] ?? '');
                    $record = $progress[$lessonId] ?? null;
                    $done = is_array($record) && ($record['courseId'] ?? '') === $courseId && ($record['status'] ?? '') === 'completed';
                    $isNext = !$done && $nextLesson !== null && $lessonId === (string) ($nextLesson['id'] ?? '');
                    $lessonDescription = trim((string) ($lesson['summary'] ?? ''));
                    if ($lessonDescription === '') {
                        $lessonDescription = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($lesson['content'] ?? ''))) ?? '');
                        if (function_exists('mb_strlen') && mb_strlen($lessonDescription) > 150) $lessonDescription = mb_substr($lessonDescription, 0, 147) . '…';
                        elseif (strlen($lessonDescription) > 150) $lessonDescription = substr($lessonDescription, 0, 147) . '…';
                    }
                    $lessonNumber = (int) ($lesson['position'] ?? ($index + 1));
                ?>
                    <li class="learning-curriculum-item<?= $done ? ' is-complete' : '' ?><?= $isNext ? ' is-next' : '' ?>">
                        <span class="learning-curriculum-marker" aria-hidden="true"><?php if ($done): ?>&#10003;<?php else: ?><?= $lessonNumber ?><?php endif; ?></span>
                        <div class="learning-curriculum-main"><div class="learning-curriculum-meta"><span>Lesson <?= $lessonNumber ?></span><span aria-hidden="true">&middot;</span><span><?= max(1, (int) ($lesson['durationMinutes'] ?? 1)) ?> min</span><?php if ($done): ?><span class="learning-curriculum-state">Completed</span><?php elseif ($isNext): ?><span class="learning-curriculum-state is-current">Up next</span><?php endif; ?></div><h3><?= e((string) ($lesson['title'] ?? 'Lesson ' . $lessonNumber)) ?></h3><?php if ($lessonDescription !== ''): ?><p><?= e($lessonDescription) ?></p><?php endif; ?></div>
                        <a class="learning-curriculum-open" href="/student/lesson/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode($lessonId) ?>" aria-label="<?= $done ? 'Review' : 'Open' ?> lesson <?= $lessonNumber ?>: <?= e((string) ($lesson['title'] ?? 'Lesson')) ?>"><?= $done ? 'Review' : ($isNext ? 'Start lesson' : 'Open lesson') ?> <span aria-hidden="true">&rarr;</span></a>
                    </li>
                <?php endforeach; ?>
            </ol>
            <?php if ($completedCount === count($lessons)): ?><div class="learning-course-complete" role="status"><span aria-hidden="true">&#10003;</span><div><strong>Course lessons completed</strong><p>You’ve completed every published lesson in this course. Check your assessments and certificates for any remaining course activities.</p></div><a class="text-link" href="/student/certificates/">View certificates &rarr;</a></div><?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php page_end(); ?>
