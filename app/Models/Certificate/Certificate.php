<?php
declare(strict_types=1);

namespace App\Models\Certificate;

use App\Models\Assignment\Assignment;
use App\Models\LearningProgress\LearningProgress;
use App\Models\Lesson\Lesson;
use App\Models\Quiz\Quiz;
use App\Services\FirestoreService\FirestoreService;
use App\Services\FirestoreService\FirestoreConflictException;
use App\Services\LearningAccess\LearningAccess;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use InvalidArgumentException;

final class Certificate
{
    public static function eligibility(FirestoreService $firestore, string $studentId, array $course): array
    {
        try {
            LearningAccess::assertApproved($firestore, $studentId, (string) $course['id']);
        } catch (RuntimeException $exception) {
            return [false, $exception->getMessage()];
        }
        $lessons = Lesson::forCourse($firestore, (string) $course['id'], true);
        if (!$lessons) return [false, 'This course has no published lessons yet.'];
        $progress = LearningProgress::forStudent($firestore, $studentId);
        $attempts = $firestore->listByField('academy_quiz_attempts', 'studentId', $studentId, 100);
        $submissions = $firestore->listByField('academy_assignment_submissions', 'studentId', $studentId, 100);
        foreach ($lessons as $lesson) {
            $lessonId = (string) $lesson['id'];
            if (!isset($progress[$lessonId]) || ($progress[$lessonId]['courseId'] ?? '') !== $course['id']) return [false, 'Complete every published lesson to qualify.'];
            $quiz = Quiz::forLesson($firestore, $lessonId);
            if ($quiz && ($quiz['courseId'] ?? '') === $course['id']) {
                $best = 0;
                foreach ($attempts as $attempt) if (($attempt['quizId'] ?? '') === $quiz['id'] && ($attempt['courseId'] ?? '') === $course['id']) $best = max($best, (int) ($attempt['total'] ?? 0) > 0 ? (int) round(100 * (int) ($attempt['score'] ?? 0) / (int) $attempt['total']) : 0);
                if ($best < 70) return [false, 'Earn at least 70% on each published lesson quiz.'];
            }
            $assignment = Assignment::forLesson($firestore, $lessonId);
            if ($assignment && ($assignment['courseId'] ?? '') === $course['id']) {
                $reviewed = false;
                foreach ($submissions as $submission) if (($submission['assignmentId'] ?? '') === $assignment['id'] && ($submission['status'] ?? '') === 'reviewed' && (int) ($submission['score'] ?? -1) >= 70) { $reviewed = true; break; }
                if (!$reviewed) return [false, 'Earn at least 70% on each published assignment.'];
            }
        }
        return [true, 'You meet the course completion requirements.'];
    }

    public static function issue(FirestoreService $firestore, string $studentId, string $studentName, array $course): array
    {
        [$eligible, $reason] = self::eligibility($firestore, $studentId, $course);
        if (!$eligible) throw new RuntimeException($reason);
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $id = hash('sha256', $studentId . ':' . (string) $course['id']);
        if ($existing = $firestore->get('academy_certificates', $id)) {
            if (($existing['status'] ?? '') !== 'valid') throw new RuntimeException('This certificate has been revoked. Contact Academy staff.');
            return $existing;
        }
        $code = 'TWA-' . strtoupper(bin2hex(random_bytes(8)));
        $fields = [
            'studentId' => $studentId,
            'studentName' => $studentName,
            'courseId' => (string) $course['id'],
            'courseTitle' => (string) $course['title'],
            'certificateCode' => $code,
            'status' => 'valid',
            'issuedAt' => $now,
        ];
        try { $firestore->create('academy_certificates', $id, $fields); }
        catch (FirestoreConflictException) {
            $existing = $firestore->get('academy_certificates', $id) ?? throw new RuntimeException('Certificate creation could not be confirmed.');
            if (($existing['status'] ?? '') !== 'valid') throw new RuntimeException('This certificate has been revoked. Contact Academy staff.');
            return $existing;
        }
        return ['id' => $id, 'certificateCode' => $code, 'status' => 'valid', 'studentName' => $studentName, 'courseId' => $course['id'], 'courseTitle' => $course['title'], 'issuedAt' => $now];
    }

    public static function forStudent(FirestoreService $firestore, string $studentId): array
    {
        return $firestore->listByField('academy_certificates', 'studentId', $studentId, 100);
    }

    public static function verify(FirestoreService $firestore, string $id): ?array
    {
        if (!preg_match('/^[A-Za-z0-9_-]{10,150}$/', $id)) return null;
        return $firestore->get('academy_certificates', $id);
    }

    public static function revoke(FirestoreService $firestore, string $id, $reason): void
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $id)) throw new InvalidArgumentException('Invalid certificate reference.');
        if (!is_string($reason) || mb_strlen(trim($reason)) < 10 || mb_strlen(trim($reason)) > 500) throw new InvalidArgumentException('Enter a revocation reason between 10 and 500 characters.');
        $certificate = $firestore->get('academy_certificates', $id);
        if (!$certificate) throw new RuntimeException('Certificate was not found.');
        if (($certificate['status'] ?? '') !== 'valid') throw new RuntimeException('Certificate is already revoked.');
        $firestore->update('academy_certificates', $id, [
            'status' => 'revoked',
            'revocationReason' => trim($reason),
            'revokedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
        ]);
    }
}
