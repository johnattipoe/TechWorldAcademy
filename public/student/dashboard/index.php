<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Controllers\CourseController\CourseController;
use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\Enrollment\Enrollment;
use App\Models\LearningProgress\LearningProgress;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

(new StudentMiddleware())->requireStudent();
$courses = (new CourseController())->index();
$courseQuery = $_GET['q'] ?? '';
$courseQuery = is_string($courseQuery) ? mb_substr(trim($courseQuery), 0, 120) : '';
$studentName = (string) ($_SESSION['learning_user_name'] ?? 'Learner');
$enrollments = [];
$enrollmentLoadError = false;
$progressLoadError = false;
$progress = [];
try {
    $enrollments = Enrollment::forStudent(new FirestoreService(FirebaseService::firestore()), (string) $_SESSION['learning_user_id']);
} catch (Throwable $exception) {
    error_log('Learner dashboard could not load course requests: ' . $exception->getMessage());
    $enrollmentLoadError = true;
}
if (!$enrollmentLoadError) {
    try {
        $progress = LearningProgress::forStudent(new FirestoreService(FirebaseService::firestore()), (string) $_SESSION['learning_user_id']);
    } catch (Throwable $exception) {
        error_log('Learner dashboard could not load progress: ' . $exception->getMessage());
        $progressLoadError = true;
    }
}
$activeCount = count(array_filter($enrollments, static fn(array $enrollment): bool => ($enrollment['status'] ?? '') === 'approved'));
$pendingCount = count(array_filter($enrollments, static fn(array $enrollment): bool => ($enrollment['status'] ?? '') === 'requested'));
$completedCount = count($progress);
$continueCourse = null;
foreach ($courses as $candidate) {
    $candidateId = (string) ($candidate['id'] ?? '');
    if (($enrollments[$candidateId]['status'] ?? '') === 'approved') { $continueCourse = $candidate; break; }
}
$recentProgress = array_values($progress);
usort($recentProgress, static function (array $left, array $right): int {
    $leftDate = $left['completedAt'] ?? null;
    $rightDate = $right['completedAt'] ?? null;
    $leftTime = $leftDate instanceof DateTimeInterface ? $leftDate->getTimestamp() : 0;
    $rightTime = $rightDate instanceof DateTimeInterface ? $rightDate->getTimestamp() : 0;
    return $rightTime <=> $leftTime;
});
$recentProgress = array_slice($recentProgress, 0, 3);
$matchingCourses = array_values(array_filter($courses, static function (array $course) use ($courseQuery): bool {
    if ($courseQuery === '') return true;
    $searchable = (string) ($course['title'] ?? '') . ' ' . (string) ($course['description'] ?? '') . ' ' . (string) ($course['level'] ?? '');
    return stripos($searchable, $courseQuery) !== false;
}));
$recommendedCourses = $courseQuery !== '' ? $matchingCourses : array_values(array_filter($matchingCourses, static function (array $course) use ($enrollments): bool {
    $status = $enrollments[(string) ($course['id'] ?? '')]['status'] ?? '';
    return $status === '' || $status === 'rejected';
}));
$flash = $_SESSION['learning_flash'] ?? null;
unset($_SESSION['learning_flash']);
page_start('My learning', 'Your personal learning home, course progress and recommended learning.');
require dirname(__DIR__, 3) . '/app/Views/learning/dashboard/index.php';
page_end();
