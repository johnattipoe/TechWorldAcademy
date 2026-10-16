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
try {
    $firestore = new FirestoreService(FirebaseService::firestore());
    $studentId = (string) $_SESSION['learning_user_id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) throw new RuntimeException('Form expired. Refresh and try again.');
        $courseId = is_string($_POST['course_id'] ?? null) ? $_POST['course_id'] : '';
        foreach ((new CourseController())->index() as $candidate) if (($candidate['id'] ?? '') === $courseId) { $course = $candidate; break; }
        if (!$course) throw new RuntimeException('Course not found.');
        $certificate = Certificate::issue($firestore, $studentId, (string) ($_SESSION['learning_user_name'] ?? 'Learner'), $course);
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
    }
} catch (Throwable $exception) {
    error_log('Learner certificates could not be loaded: ' . $exception->getMessage());
    $error = in_array($exception->getMessage(), ['Form expired. Refresh and try again.', 'Course not found.', 'Your place in this course must be approved first.', 'This course is not available to your account yet.', 'Payment is required before you can start this course.', 'Your 7-day course trial has ended. Complete payment to continue learning.', 'The trial access window could not be verified. Contact the Academy.', 'This course has no published lessons yet.', 'Complete every published lesson to qualify.', 'Earn at least 70% on each published lesson quiz.', 'Earn at least 70% on each published assignment.', 'This certificate has been revoked. Contact Academy staff.'], true) ? $exception->getMessage() : 'Certificates are temporarily unavailable.';
}
$flash = $_SESSION['learning_flash'] ?? null; unset($_SESSION['learning_flash']);
page_start('My certificates', 'View and verify your TechWorld Academy course certificates.');
?>
<section class="page-hero"><div class="container"><span class="eyebrow">Your learning space</span><h1>My certificates</h1><p>Certificates are available when you complete every published lesson, pass required quizzes with at least 70%, and earn at least 70% on required assignments.</p></div></section>
<section class="section container learning-dashboard-content"><a class="text-link" href="/student/">&larr; Back to dashboard</a><?php if (is_array($flash) && !empty($flash['message'])): ?><div class="learning-alert" role="status"><?= e((string) $flash['message']) ?></div><?php endif; ?><?php if ($error !== ''): ?><div class="learning-alert is-error" role="status"><?= e($error) ?></div><?php endif; ?>
<?php if ($certificates): ?><div class="learning-request-list"><h2>Course certificates</h2><?php foreach ($certificates as $certificate): ?><article class="learning-certificate-card"><div><span class="eyebrow">TechWorld Academy certificate · <?= e(ucfirst((string) ($certificate['status'] ?? 'valid'))) ?></span><h2><?= e((string) ($certificate['courseTitle'] ?? 'Course completion')) ?></h2><p>Awarded to <?= e((string) ($certificate['studentName'] ?? 'Learner')) ?> on <?= e(isset($certificate['issuedAt']) && $certificate['issuedAt'] instanceof DateTimeInterface ? $certificate['issuedAt']->format('M j, Y') : 'a recent date') ?>.</p><strong><?= e((string) ($certificate['certificateCode'] ?? '')) ?></strong></div><a class="button button-primary" href="/certificates/verify/?id=<?= rawurlencode((string) $certificate['id']) ?>">View verification page</a></article><?php endforeach; ?></div><?php endif; ?>
<div class="section-heading"><div><span class="eyebrow">Completion status</span><h2>Courses in progress</h2></div></div><?php if (!$courses && !$certificates): ?><div class="learning-empty"><h2>No approved courses yet</h2><p>Once a course request is approved, you can track its certificate requirements here.</p><a class="button button-primary" href="/student/my-courses/">View my courses</a></div><?php else: ?><div class="learning-request-list"><?php foreach ($courses as $course): [$eligible, $reason] = $eligibility[$course['id']]; ?><article class="learning-request-card"><div><span class="eyebrow">Course certificate</span><h2><?= e((string) $course['title']) ?></h2><p><?= e($reason) ?></p></div><?php if ($eligible): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><input type="hidden" name="course_id" value="<?= e((string) $course['id']) ?>"><button class="button button-primary" type="submit">Issue certificate</button></form><?php else: ?><a class="button button-outline" href="/student/learn/?course=<?= rawurlencode((string) $course['id']) ?>">Continue learning</a><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?></section>
<?php page_end(); ?>
