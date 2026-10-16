<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\Enrollment\Enrollment;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

(new StudentMiddleware())->requireStudent();
$enrollmentLoadError = false;
try {
    $enrollments = Enrollment::forStudent(new FirestoreService(FirebaseService::firestore()), (string) $_SESSION['learning_user_id']);
} catch (Throwable $exception) {
    error_log('Learner course list could not be loaded: ' . $exception->getMessage());
    $enrollments = [];
    $enrollmentLoadError = true;
}
$flash = $_SESSION['learning_flash'] ?? null;
unset($_SESSION['learning_flash']);
page_start('My courses', 'Review your TechWorld Academy enrollments and course requests.');
require dirname(__DIR__, 3) . '/app/Views/learning/courses/index.php';
page_end();
