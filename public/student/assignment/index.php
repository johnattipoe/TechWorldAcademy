<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Controllers\CourseController\CourseController;
use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\Assignment\Assignment;
use App\Models\AssignmentSubmission\AssignmentSubmission;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\LearningAccess\LearningAccess;

(new StudentMiddleware())->requireStudent();
start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$courseId = is_string($_REQUEST['course'] ?? null) ? $_REQUEST['course'] : '';
$lessonId = is_string($_REQUEST['lesson'] ?? null) ? $_REQUEST['lesson'] : '';
$course = null;
foreach ((new CourseController())->index() as $candidate) if (($candidate['id'] ?? '') === $courseId) { $course = $candidate; break; }
$lesson = $assignment = $submission = null;
$error = '';
try {
    if (!$course || !preg_match('/^[A-Za-z0-9_-]{1,150}$/', $lessonId)) throw new RuntimeException('Assignment unavailable.');
    $firestore = new FirestoreService(FirebaseService::firestore());
    LearningAccess::assertApproved($firestore, (string) $_SESSION['learning_user_id'], $courseId);
    $lesson = $firestore->get('academy_lessons', $lessonId);
    if (!$lesson || ($lesson['courseId'] ?? '') !== $courseId || empty($lesson['published'])) throw new RuntimeException('Assignment unavailable.');
    $assignment = Assignment::forLesson($firestore, $lessonId);
    if (!$assignment || ($assignment['courseId'] ?? '') !== $courseId) throw new RuntimeException('Assignment unavailable.');
    $submissionId = hash('sha256', (string) $_SESSION['learning_user_id'] . ':' . (string) $assignment['id']);
    $submission = $firestore->get('academy_assignment_submissions', $submissionId);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) throw new RuntimeException('Form expired. Refresh and try again.');
        if (!isset($_POST['assignment_id']) || !hash_equals((string) $assignment['id'], (string) $_POST['assignment_id'])) throw new RuntimeException('Assignment changed. Reload and try again.');
        $response = AssignmentSubmission::validateResponse($_POST['response'] ?? null);
        AssignmentSubmission::submit($firestore, (string) $_SESSION['learning_user_id'], $course, $lesson, $assignment, $response);
        $_SESSION['learning_flash'] = ['success' => true, 'message' => 'Your assignment was submitted.'];
        header('Location: /student/assignment/?course=' . rawurlencode($courseId) . '&lesson=' . rawurlencode($lessonId)); exit;
    }
} catch (Throwable $exception) {
    http_response_code($exception->getMessage() === 'This course is not available to your account yet.' ? 403 : 404);
    $error = in_array($exception->getMessage(), ['This course is not available to your account yet.', 'Form expired. Refresh and try again.', 'Assignment changed. Reload and try again.', 'Enter your assignment response.', 'Your response must be between 20 and 10,000 characters.'], true) ? $exception->getMessage() : 'This assignment is unavailable.';
}
$flash = $_SESSION['learning_flash'] ?? null; unset($_SESSION['learning_flash']);
page_start($assignment ? (string) $assignment['title'] : 'Assignment', 'Submit work for your approved TechWorld Academy course.');
?>
<section class="section container learning-dashboard-content"><a class="text-link" href="/student/lesson/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode($lessonId) ?>">&larr; <?= e((string) ($lesson['title'] ?? 'Back to lesson')) ?></a>
<?php if ($error !== ''): ?><div class="learning-alert is-error" role="status"><?= e($error) ?></div><?php elseif ($assignment): ?><article class="learning-lesson"><span class="eyebrow">Course assignment</span><h1><?= e((string) $assignment['title']) ?></h1><div class="learning-lesson-content"><?= nl2br(e((string) $assignment['instructions'])) ?></div><?php if (is_array($flash) && !empty($flash['message'])): ?><div class="learning-alert" role="status"><?= e((string) $flash['message']) ?></div><?php endif; ?><?php if ($submission && ($submission['status'] ?? '') === 'reviewed'): ?><div class="learning-assignment-feedback"><strong>Staff feedback · <?= (int) ($submission['score'] ?? 0) ?>/100</strong><p><?= nl2br(e((string) ($submission['feedback'] ?? ''))) ?></p></div><?php elseif ($submission): ?><div class="learning-alert" role="status">Your submission is awaiting staff review.</div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><input type="hidden" name="course" value="<?= e($courseId) ?>"><input type="hidden" name="lesson" value="<?= e($lessonId) ?>"><input type="hidden" name="assignment_id" value="<?= e((string) $assignment['id']) ?>"><label for="assignment-response">Your work</label><textarea id="assignment-response" name="response" rows="12" minlength="20" maxlength="10000" required><?= e((string) ($submission['response'] ?? '')) ?></textarea><p class="resource-safety-note">You can resubmit your work. A new submission replaces your previous response and clears its review.</p><button class="button button-primary" type="submit"><?= $submission ? 'Resubmit assignment' : 'Submit assignment' ?></button></form></article><?php endif; ?></section>
<?php page_end(); ?>
