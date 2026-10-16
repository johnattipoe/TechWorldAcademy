<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\Enrollment\Enrollment;
use App\Models\LearningProgress\LearningProgress;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

(new StudentMiddleware())->requireStudent();
$notifications = [];
$error = false;
try {
    $firestore = new FirestoreService(FirebaseService::firestore());
    $studentId = (string) $_SESSION['learning_user_id'];
    foreach (Enrollment::forStudent($firestore, $studentId) as $enrollment) {
        $status = (string) ($enrollment['status'] ?? 'requested');
        $notifications[] = [
            'type' => 'course',
            'title' => match ($status) { 'approved' => 'Course request approved', 'rejected' => 'Course request update', default => 'Course request received' },
            'detail' => match ($status) { 'approved' => 'You can now start learning in ' . (string) ($enrollment['courseTitle'] ?? 'your course') . '.', 'rejected' => 'Your request for ' . (string) ($enrollment['courseTitle'] ?? 'a course') . ' was not approved. You can request a place again from the course catalogue.', default => 'Your request for ' . (string) ($enrollment['courseTitle'] ?? 'a course') . ' is being reviewed.' },
            'date' => $enrollment['reviewedAt'] ?? $enrollment['requestedAt'] ?? null,
            'href' => '/student/my-courses/',
            'label' => 'View my courses',
        ];
    }
    foreach (LearningProgress::forStudent($firestore, $studentId) as $record) {
        $notifications[] = [
            'type' => 'progress',
            'title' => 'Lesson completed',
            'detail' => 'You completed ' . (string) ($record['lessonTitle'] ?? 'a lesson') . ' in ' . (string) ($record['courseTitle'] ?? 'your course') . '.',
            'date' => $record['completedAt'] ?? null,
            'href' => '/student/progress/',
            'label' => 'View progress',
        ];
    }
} catch (Throwable $exception) {
    error_log('Learner notifications could not be loaded: ' . $exception->getMessage());
    $error = true;
}
usort($notifications, static function (array $left, array $right): int {
    $leftDate = $left['date'] ?? null;
    $rightDate = $right['date'] ?? null;
    $leftTime = $leftDate instanceof DateTimeInterface ? $leftDate->getTimestamp() : 0;
    $rightTime = $rightDate instanceof DateTimeInterface ? $rightDate->getTimestamp() : 0;
    return $rightTime <=> $leftTime;
});
$notifications = array_slice($notifications, 0, 50);
page_start('Notifications', 'Review course request updates and learning milestones.');
?>
<section class="page-hero"><div class="container"><span class="eyebrow">Your learning space</span><h1>Notifications</h1><p>Course request updates and learning milestones from your activity.</p></div></section>
<section class="section container learning-dashboard-content"><a class="text-link" href="/student/">&larr; Back to My learning</a><?php if ($error): ?><div class="learning-alert is-error" role="status">Notifications are temporarily unavailable. Please try again shortly.</div><?php elseif (!$notifications): ?><div class="learning-empty"><h2>You’re all caught up</h2><p>Course request updates and completed lessons will appear here.</p><a class="button button-primary" href="/student/">Explore your learning</a></div><?php else: ?><div class="learning-notification-list"><?php foreach ($notifications as $notification): ?><article class="learning-notification-card"><span class="learning-notification-mark" aria-hidden="true"><?= $notification['type'] === 'progress' ? '&#10003;' : '&#9670;' ?></span><div><h2><?= e($notification['title']) ?></h2><p><?= e($notification['detail']) ?></p><small><?= e(($notification['date'] ?? null) instanceof DateTimeInterface ? $notification['date']->format('M j, Y') : 'Recent update') ?></small></div><a class="button button-outline" href="<?= e($notification['href']) ?>"><?= e($notification['label']) ?></a></article><?php endforeach; ?></div><?php endif; ?></section>
<?php page_end(); ?>
