<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\QuizAttempt\QuizAttempt;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

(new StudentMiddleware())->requireStudent();
$attempts = [];
$error = false;
try { $attempts = QuizAttempt::forStudent(new FirestoreService(FirebaseService::firestore()), (string) $_SESSION['learning_user_id']); }
catch (Throwable $exception) { error_log('Learner assessments could not be loaded: ' . $exception->getMessage()); $error = true; }
page_start('My assessments', 'Review quiz results for your TechWorld Academy courses.');
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Your learning space</span>
        <h1>My assessments</h1>
        <p>Review the quiz results you have submitted.</p>
    </div>
</section>
<section class="section container learning-dashboard-content">
    <a class="text-link" href="/student/">&larr; Back to dashboard</a><?php if ($error): ?>
        <div class="learning-alert is-error" role="status">Your assessment results are temporarily unavailable.</div><?php elseif (!$attempts): ?>
            <div class="learning-empty">
                <h2>No quiz results yet</h2>
                <p>Published quizzes in your approved courses will appear here after you submit them.</p>
                <a class="button button-primary" href="/student/my-courses/">View my courses</a>
            </div><?php else: ?>
                <div class="learning-request-list"><?php foreach ($attempts as $attempt): ?>
                    <article class="learning-request-card">
                        <div><span class="eyebrow"><?= e((string) ($attempt['courseTitle'] ?? 'Course')) ?> · <?= e((string) ($attempt['lessonTitle'] ?? 'Lesson')) ?></span>
                        <h2><?= e((string) ($attempt['quizTitle'] ?? 'Quiz')) ?></h2><p><?= (int) ($attempt['score'] ?? 0) ?> / <?= (int) ($attempt['total'] ?? 0) ?> correct · <?= e(isset($attempt['submittedAt']) && $attempt['submittedAt'] instanceof DateTimeInterface ? $attempt['submittedAt']->format('M j, Y') : 'Recently') ?></p></div><a class="button button-outline" href="/student/quiz/result/?attempt=<?= rawurlencode((string) $attempt['id']) ?>">View result</a></article><?php endforeach; ?></div><?php endif; ?></section>
<?php page_end(); ?>
