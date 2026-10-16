<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\LearningProgress\LearningProgress;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

(new StudentMiddleware())->requireStudent();
$records = [];
$error = false;
try { $records = LearningProgress::forStudent(new FirestoreService(FirebaseService::firestore()), (string) $_SESSION['learning_user_id']); }
catch (Throwable $exception) { error_log('Learner progress could not be loaded: ' . $exception->getMessage()); $error = true; }
page_start('My progress', 'Review completed lessons across your approved courses.');
?>
<section class="page-hero"><div class="container"><span class="eyebrow">Your learning space</span><h1>My progress</h1><p>Lessons you have marked complete.</p></div></section><section class="section container learning-dashboard-content"><a class="text-link" href="/student/">&larr; Back to dashboard</a><?php if ($error): ?><div class="learning-alert is-error" role="status">Progress is temporarily unavailable.</div><?php elseif (!$records): ?><div class="learning-empty"><h2>Your progress starts with a lesson</h2><p>Approved courses and completed lessons will show here.</p><a class="button button-primary" href="/student/my-courses/">View my courses</a></div><?php else: ?><div class="learning-request-list"><?php foreach ($records as $record): ?><article class="learning-request-card"><div><span class="eyebrow">Completed</span><h2><?= e((string) ($record['lessonTitle'] ?? $record['courseTitle'] ?? 'Course lesson')) ?></h2><p><?= e((string) ($record['courseTitle'] ?? 'Course')) ?> · <?= e(isset($record['completedAt']) && $record['completedAt'] instanceof DateTimeInterface ? $record['completedAt']->format('M j, Y') : 'Recently') ?></p></div><a class="button button-outline" href="/student/learn/?course=<?= rawurlencode((string) $record['courseId']) ?>">Open course</a></article><?php endforeach; ?></div><?php endif; ?></section>
<?php page_end(); ?>
