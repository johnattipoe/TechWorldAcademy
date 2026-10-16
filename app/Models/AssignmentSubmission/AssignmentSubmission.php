<?php
declare(strict_types=1);

namespace App\Models\AssignmentSubmission;

use App\Services\FirestoreService\FirestoreService;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class AssignmentSubmission
{
    public static function validateResponse($value): string
    {
        if (!is_string($value)) throw new InvalidArgumentException('Enter your assignment response.');
        $response = trim($value);
        if (mb_strlen($response) < 20 || mb_strlen($response) > 10000) throw new InvalidArgumentException('Your response must be between 20 and 10,000 characters.');
        return $response;
    }

    public static function submit(FirestoreService $firestore, string $studentId, array $course, array $lesson, array $assignment, string $response): void
    {
        $id = hash('sha256', $studentId . ':' . (string) $assignment['id']);
        $existing = $firestore->get('academy_assignment_submissions', $id);
        $fields = [
            'studentId' => $studentId,
            'studentName' => (string) ($_SESSION['learning_user_name'] ?? 'Learner'),
            'courseId' => (string) $course['id'], 'courseTitle' => (string) $course['title'],
            'lessonId' => (string) $lesson['id'], 'lessonTitle' => (string) $lesson['title'],
            'assignmentId' => (string) $assignment['id'], 'assignmentTitle' => (string) $assignment['title'],
            'response' => $response, 'status' => 'submitted', 'submittedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
            'feedback' => '', 'score' => null, 'reviewedAt' => null,
        ];
        if ($existing) $firestore->update('academy_assignment_submissions', $id, $fields);
        else $firestore->create('academy_assignment_submissions', $id, $fields);
    }

    public static function forStudent(FirestoreService $firestore, string $studentId): array
    {
        return $firestore->listByField('academy_assignment_submissions', 'studentId', $studentId, 100);
    }

    public static function review(FirestoreService $firestore, string $id, $score, $feedback): void
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $id)) throw new InvalidArgumentException('Invalid submission.');
        $score = filter_var($score, FILTER_VALIDATE_INT);
        if (!is_int($score) || $score < 0 || $score > 100) throw new InvalidArgumentException('Score must be from 0 to 100.');
        if (!is_string($feedback) || mb_strlen(trim($feedback)) > 2000) throw new InvalidArgumentException('Feedback must be no longer than 2,000 characters.');
        $firestore->update('academy_assignment_submissions', $id, [
            'score' => $score, 'feedback' => trim($feedback), 'status' => 'reviewed',
            'reviewedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
        ]);
    }
}
