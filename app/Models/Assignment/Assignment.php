<?php
declare(strict_types=1);

namespace App\Models\Assignment;

use App\Services\FirestoreService\FirestoreService;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class Assignment
{
    public static function validate(array $input): array
    {
        $data = [
            'lessonId' => trim((string) ($input['lesson_id'] ?? '')),
            'title' => trim((string) ($input['title'] ?? '')),
            'instructions' => trim((string) ($input['instructions'] ?? '')),
            'published' => isset($input['published']) && (string) $input['published'] === '1',
        ];
        $errors = [];
        if ($data['lessonId'] === '' || strlen($data['lessonId']) > 150) $errors['lesson_id'] = 'Choose a valid lesson.';
        if ($data['title'] === '' || mb_strlen($data['title']) > 150) $errors['title'] = 'Add an assignment title up to 150 characters.';
        if (mb_strlen($data['instructions']) < 20 || mb_strlen($data['instructions']) > 5000) $errors['instructions'] = 'Instructions must be between 20 and 5,000 characters.';
        return [$data, $errors];
    }

    public static function forLesson(FirestoreService $firestore, string $lessonId, bool $publishedOnly = true): ?array
    {
        foreach ($firestore->listByField('academy_assignments', 'lessonId', $lessonId, 10) as $assignment) {
            if (!$publishedOnly || !empty($assignment['published'])) return $assignment;
        }
        return null;
    }

    public static function save(FirestoreService $firestore, array $data, ?string $id = null): string
    {
        if ($id !== null && !preg_match('/^[A-Za-z0-9_-]{1,150}$/', $id)) throw new InvalidArgumentException('Invalid assignment ID.');
        $data['updatedAt'] = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        if ($id === null) { $data['createdAt'] = $data['updatedAt']; return $firestore->add('academy_assignments', $data); }
        $firestore->update('academy_assignments', $id, $data);
        return $id;
    }
}
