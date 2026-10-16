<?php
declare(strict_types=1);

namespace App\Models\LearningProgress;

use App\Services\FirestoreService\FirestoreService;
use App\Services\FirestoreService\FirestoreConflictException;
use DateTimeImmutable;
use DateTimeZone;

final class LearningProgress
{
    public static function forStudent(FirestoreService $firestore, string $studentId): array
    {
        $records = $firestore->listByField('academy_progress', 'studentId', $studentId, 100);
        $byLesson = [];
        foreach ($records as $record) {
            if (!empty($record['lessonId'])) $byLesson[(string) $record['lessonId']] = $record;
        }
        return $byLesson;
    }

    public static function complete(FirestoreService $firestore, string $studentId, string $courseId, string $lessonId, string $courseTitle = '', string $lessonTitle = ''): void
    {
        $id = hash('sha256', $studentId . ':' . $lessonId);
        $fields = [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'lessonId' => $lessonId,
            'courseTitle' => $courseTitle,
            'lessonTitle' => $lessonTitle,
            'status' => 'completed',
            'completedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
        ];
        if ($firestore->get('academy_progress', $id)) {
            $firestore->update('academy_progress', $id, $fields);
            return;
        }
        try { $firestore->create('academy_progress', $id, $fields); }
        catch (FirestoreConflictException) { $firestore->update('academy_progress', $id, $fields); }
    }
}
