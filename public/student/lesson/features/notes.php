<?php
declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/app/bootstrap.php';

use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreConflictException;
use App\Services\FirestoreService\FirestoreService;
use App\Services\LearningAccess\LearningAccess;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

$respond = static function (int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    $respond(405, ['success' => false, 'message' => 'Use POST to save lesson notes.']);
}

(new StudentMiddleware())->requireStudent();
start_app_session();

$csrfToken = $_POST['csrf_token'] ?? null;
if (!is_string($csrfToken) || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $csrfToken)) {
    $respond(419, ['success' => false, 'message' => 'Your session expired. Refresh the lesson and try again.']);
}

$courseId = $_POST['course'] ?? null;
$lessonId = $_POST['lesson'] ?? null;
$note = $_POST['note'] ?? null;
if (!is_string($courseId) || !preg_match('/^[A-Za-z0-9_-]{1,150}$/', $courseId)
    || !is_string($lessonId) || !preg_match('/^[A-Za-z0-9_-]{1,150}$/', $lessonId)
    || !is_string($note) || mb_strlen($note) > 5000) {
    $respond(422, ['success' => false, 'message' => 'Check the note and try again. Notes can be up to 5,000 characters.']);
}

try {
    $studentId = (string) $_SESSION['learning_user_id'];
    $firestore = new FirestoreService(FirebaseService::firestore());

    try {
        LearningAccess::assertApproved($firestore, $studentId, $courseId);
    } catch (RuntimeException $exception) {
        if (in_array($exception->getMessage(), [
            'This course is not available to your account yet.',
            'Payment is required before you can start this course.',
            'The trial access window could not be verified. Contact the Academy.',
            'Your 7-day course trial has ended. Complete payment to continue learning.',
        ], true)) {
            $respond(403, ['success' => false, 'message' => 'You no longer have access to this course.']);
        }
        throw $exception;
    }

    $lesson = $firestore->get('academy_lessons', $lessonId);
    if (!$lesson || ($lesson['courseId'] ?? '') !== $courseId || empty($lesson['published'])) {
        $respond(404, ['success' => false, 'message' => 'This lesson is unavailable.']);
    }

    $noteId = hash('sha256', $studentId . ':' . $courseId . ':' . $lessonId);
    $existing = $firestore->get('academy_lesson_notes', $noteId);
    if ($existing && (($existing['studentId'] ?? '') !== $studentId
        || ($existing['courseId'] ?? '') !== $courseId
        || ($existing['lessonId'] ?? '') !== $lessonId)) {
        throw new RuntimeException('Lesson note ownership validation failed.');
    }

    if (trim($note) === '') {
        if ($existing) $firestore->delete('academy_lesson_notes', $noteId);
        $respond(200, ['success' => true, 'message' => 'Note cleared.']);
    }

    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $fields = [
        'studentId' => $studentId,
        'courseId' => $courseId,
        'lessonId' => $lessonId,
        'note' => $note,
        'updatedAt' => $now,
    ];

    if ($existing) {
        $firestore->update('academy_lesson_notes', $noteId, $fields);
    } else {
        $fields['createdAt'] = $now;
        try {
            $firestore->create('academy_lesson_notes', $noteId, $fields);
        } catch (FirestoreConflictException) {
            $firestore->update('academy_lesson_notes', $noteId, $fields);
        }
    }

    $respond(200, ['success' => true, 'message' => 'Note saved.']);
} catch (Throwable $exception) {
    error_log('Learner lesson note could not be saved: ' . $exception->getMessage());
    $respond(500, ['success' => false, 'message' => 'Your note could not be saved right now. Please try again.']);
}
