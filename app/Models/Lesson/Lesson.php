<?php
declare(strict_types=1);

namespace App\Models\Lesson;

use App\Services\FirestoreService\FirestoreService;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class Lesson
{
    public static function validate(array $input): array
    {
        $data = [
            'courseId' => trim((string) ($input['course_id'] ?? '')),
            'title' => trim((string) ($input['title'] ?? '')),
            'content' => trim((string) ($input['content'] ?? '')),
            'durationMinutes' => filter_var($input['duration_minutes'] ?? 0, FILTER_VALIDATE_INT),
            'position' => filter_var($input['position'] ?? 0, FILTER_VALIDATE_INT),
            'published' => isset($input['published']) && (string) $input['published'] === '1',
        ];
        $errors = [];
        if ($data['courseId'] === '' || strlen($data['courseId']) > 150) $errors['course_id'] = 'Choose a valid course.';
        if ($data['title'] === '' || mb_strlen($data['title']) > 150) $errors['title'] = 'Add a lesson title up to 150 characters.';
        if ($data['content'] === '' || mb_strlen($data['content']) > 20000) $errors['content'] = 'Add lesson content up to 20,000 characters.';
        if (!is_int($data['durationMinutes']) || $data['durationMinutes'] < 1 || $data['durationMinutes'] > 600) $errors['duration_minutes'] = 'Duration must be 1 to 600 minutes.';
        if (!is_int($data['position']) || $data['position'] < 1 || $data['position'] > 999) $errors['position'] = 'Order must be between 1 and 999.';
        return [$data, $errors];
    }

    public static function forCourse(FirestoreService $firestore, string $courseId, bool $publishedOnly = false): array
    {
        $lessons = $firestore->listByField('academy_lessons', 'courseId', $courseId, 100);
        if ($publishedOnly) $lessons = array_values(array_filter($lessons, static fn(array $lesson): bool => !empty($lesson['published'])));
        usort($lessons, static fn(array $a, array $b): int => ((int) ($a['position'] ?? 999)) <=> ((int) ($b['position'] ?? 999)));
        return $lessons;
    }

    public static function save(FirestoreService $firestore, array $data, ?string $id = null): string
    {
        if ($id !== null && !preg_match('/^[A-Za-z0-9_-]{1,150}$/', $id)) throw new InvalidArgumentException('Invalid lesson ID.');
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $fields = $data + ['updatedAt' => $now];
        if ($id === null) {
            $fields['createdAt'] = $now;
            return $firestore->add('academy_lessons', $fields);
        }
        $firestore->update('academy_lessons', $id, $fields);
        return $id;
    }
}
