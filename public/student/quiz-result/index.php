<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\LearningAccess\LearningAccess;

(new StudentMiddleware())->requireStudent();
$attemptId = is_string($_GET['attempt'] ?? null) ? $_GET['attempt'] : '';
$attempt = null;
$error = '';
try {
    if (!preg_match('/^[A-Za-z0-9_-]{1,150}$/', $attemptId)) throw new RuntimeException('Result not found.');
    $firestore = new FirestoreService(FirebaseService::firestore());
    $attempt = $firestore->get('academy_quiz_attempts', $attemptId);
    if (!$attempt || ($attempt['studentId'] ?? '') !== (string) $_SESSION['learning_user_id']) throw new RuntimeException('Result not found.');
    LearningAccess::assertApproved($firestore, (string) $_SESSION['learning_user_id'], (string) $attempt['courseId']);
} catch (Throwable $exception) { http_response_code(404); $error = 'Quiz result not found.'; }
page_start('Quiz result', 'Your saved quiz score.');
?>
<section class="page-hero"><div class="container"><span class="eyebrow">Assessment result</span><h1><?= e((string) ($attempt['quizTitle'] ?? 'Quiz result')) ?></h1><p><?= e((string) ($attempt['courseTitle'] ?? 'Course')) ?> · <?= e((string) ($attempt['lessonTitle'] ?? 'Lesson')) ?></p></div></section><section class="section container learning-dashboard-content"><?php if ($error !== ''): ?><div class="learning-alert is-error" role="status"><?= e($error) ?></div><?php elseif ($attempt): $score = (int) ($attempt['score'] ?? 0); $total = max(1, (int) ($attempt['total'] ?? 1)); ?><div class="learning-empty"><span class="eyebrow">Your score</span><h2><?= $score ?> / <?= $total ?></h2><p><?= (int) round($score * 100 / $total) ?>% correct · Submitted <?= e(isset($attempt['submittedAt']) && $attempt['submittedAt'] instanceof DateTimeInterface ? $attempt['submittedAt']->format('M j, Y g:i a') . ' UTC' : 'recently') ?></p><a class="button button-primary" href="/student/lesson/?course=<?= rawurlencode((string) $attempt['courseId']) ?>&amp;lesson=<?= rawurlencode((string) $attempt['lessonId']) ?>">Return to lesson</a></div><?php endif; ?></section>
<?php page_end(); ?>
