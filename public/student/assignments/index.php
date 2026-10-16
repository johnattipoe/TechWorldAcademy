<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\AssignmentSubmission\AssignmentSubmission;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

(new StudentMiddleware())->requireStudent();
$submissions = [];
$error = false;
try { $submissions = AssignmentSubmission::forStudent(new FirestoreService(FirebaseService::firestore()), (string) $_SESSION['learning_user_id']); }
catch (Throwable $exception) { error_log('Learner assignments could not be loaded: ' . $exception->getMessage()); $error = true; }
page_start('My assignments', 'Review assignment submissions and instructor feedback.');
?>
<section class="page-hero"><div class="container"><span class="eyebrow">Your learning space</span><h1>My assignments</h1><p>Track your submitted course work and review feedback.</p></div></section><section class="section container learning-dashboard-content"><a class="text-link" href="/student/">&larr; Back to dashboard</a><?php if ($error): ?><div class="learning-alert is-error" role="status">Your submissions are temporarily unavailable.</div><?php elseif (!$submissions): ?><div class="learning-empty"><h2>No assignments submitted</h2><p>Published assignments appear inside their course lessons.</p><a class="button button-primary" href="/student/my-courses/">View my courses</a></div><?php else: ?><div class="learning-request-list"><?php foreach ($submissions as $submission): ?><article class="learning-request-card"><div><span class="eyebrow"><?= e((string) ($submission['courseTitle'] ?? 'Course')) ?> · <?= e((string) ($submission['lessonTitle'] ?? 'Lesson')) ?></span><h2><?= e((string) ($submission['assignmentTitle'] ?? 'Assignment')) ?></h2><p><?= e(ucfirst((string) ($submission['status'] ?? 'submitted'))) ?><?php if (($submission['status'] ?? '') === 'reviewed'): ?> · <?= (int) ($submission['score'] ?? 0) ?>/100<?php endif; ?> · Submitted <?= e(isset($submission['submittedAt']) && $submission['submittedAt'] instanceof DateTimeInterface ? $submission['submittedAt']->format('M j, Y') : 'recently') ?></p><?php if (($submission['status'] ?? '') === 'reviewed' && !empty($submission['feedback'])): ?><p><?= nl2br(e((string) $submission['feedback'])) ?></p><?php endif; ?></div><a class="button button-outline" href="/student/assignment/?course=<?= rawurlencode((string) $submission['courseId']) ?>&amp;lesson=<?= rawurlencode((string) $submission['lessonId']) ?>">Open assignment</a></article><?php endforeach; ?></div><?php endif; ?></section>
<?php page_end(); ?>
