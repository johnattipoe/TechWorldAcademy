<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Controllers\CourseController\CourseController;
use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\Quiz\Quiz;
use App\Models\QuizAttempt\QuizAttempt;
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
$lesson = $quiz = null;
$error = '';
try {
    if (!$course || !preg_match('/^[A-Za-z0-9_-]{1,150}$/', $lessonId)) throw new RuntimeException('Quiz unavailable.');
    $firestore = new FirestoreService(FirebaseService::firestore());
    LearningAccess::assertApproved($firestore, (string) $_SESSION['learning_user_id'], $courseId);
    $lesson = $firestore->get('academy_lessons', $lessonId);
    if (!$lesson || ($lesson['courseId'] ?? '') !== $courseId || empty($lesson['published'])) throw new RuntimeException('Quiz unavailable.');
    $quiz = Quiz::forLesson($firestore, $lessonId);
    if (!$quiz || ($quiz['courseId'] ?? '') !== $courseId) throw new RuntimeException('Quiz unavailable.');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) throw new RuntimeException('Form expired. Refresh and try again.');
        if (!isset($_POST['quiz_id']) || !hash_equals((string) $quiz['id'], (string) $_POST['quiz_id'])) throw new RuntimeException('Quiz changed. Reload and try again.');
        $answers = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];
        $attemptId = QuizAttempt::submit($firestore, (string) $_SESSION['learning_user_id'], $course, $lesson, $quiz, $answers);
        header('Location: /student/quiz/result/?attempt=' . rawurlencode($attemptId)); exit;
    }
} catch (Throwable $exception) {
    http_response_code($exception->getMessage() === 'This course is not available to your account yet.' ? 403 : 404);
    $error = in_array($exception->getMessage(), ['This course is not available to your account yet.', 'Form expired. Refresh and try again.', 'Quiz changed. Reload and try again.'], true) ? $exception->getMessage() : 'This quiz is unavailable.';
}
page_start($quiz ? (string) $quiz['title'] : 'Course quiz', 'Knowledge check for your TechWorld Academy course.');
?>
<section class="section container learning-dashboard-content"><a class="text-link" href="/student/lesson/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode($lessonId) ?>">&larr; <?= e((string) ($lesson['title'] ?? 'Back to lesson')) ?></a>
<?php if ($error !== ''): ?><div class="learning-alert is-error" role="status"><?= e($error) ?></div><?php elseif ($quiz): ?><form class="learning-lesson learning-quiz" method="post"><span class="eyebrow"><?= count((array) $quiz['questions']) ?> questions</span><h1><?= e((string) $quiz['title']) ?></h1><p>Choose one answer for each question, then submit to see your score.</p><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><input type="hidden" name="course" value="<?= e($courseId) ?>"><input type="hidden" name="lesson" value="<?= e($lessonId) ?>"><input type="hidden" name="quiz_id" value="<?= e((string) $quiz['id']) ?>">
<?php foreach ($quiz['questions'] as $index => $question): ?><fieldset class="learning-quiz-question"><legend><?= ($index + 1) ?>. <?= e((string) $question['prompt']) ?></legend><?php foreach (array_values((array) $question['options']) as $optionIndex => $option): $letter = chr(65 + $optionIndex); ?><label><input type="radio" name="answers[<?= (int) $index ?>]" value="<?= $letter ?>" required> <span><strong><?= $letter ?>.</strong> <?= e((string) $option) ?></span></label><?php endforeach; ?></fieldset><?php endforeach; ?><button class="button button-primary" type="submit">Submit quiz</button></form><?php endif; ?></section>
<?php page_end(); ?>
