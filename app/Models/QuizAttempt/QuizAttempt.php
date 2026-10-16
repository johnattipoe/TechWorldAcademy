<?php
declare(strict_types=1);

namespace App\Models\QuizAttempt;

use App\Services\FirestoreService\FirestoreService;
use DateTimeImmutable;
use DateTimeZone;

final class QuizAttempt
{
    public static function forStudent(FirestoreService $firestore, string $studentId): array
    {
        $attempts = $firestore->listByField('academy_quiz_attempts', 'studentId', $studentId, 100);
        usort($attempts, static fn(array $a, array $b): int => ($b['submittedAt'] ?? new DateTimeImmutable('@0')) <=> ($a['submittedAt'] ?? new DateTimeImmutable('@0')));
        return $attempts;
    }

    public static function submit(FirestoreService $firestore, string $studentId, array $course, array $lesson, array $quiz, array $answers): string
    {
        $questions = $quiz['questions'] ?? [];
        $total = count($questions);
        $score = 0;
        for ($i = 0; $i < $total; $i++) {
            if (isset($answers[$i]) && is_string($answers[$i]) && hash_equals((string) ($questions[$i]['correct'] ?? ''), $answers[$i])) $score++;
        }
        return $firestore->add('academy_quiz_attempts', [
            'studentId' => $studentId,
            'courseId' => (string) $course['id'],
            'courseTitle' => (string) $course['title'],
            'lessonId' => (string) $lesson['id'],
            'lessonTitle' => (string) $lesson['title'],
            'quizId' => (string) $quiz['id'],
            'quizTitle' => (string) $quiz['title'],
            'score' => $score,
            'total' => $total,
            'submittedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
        ]);
    }
}
