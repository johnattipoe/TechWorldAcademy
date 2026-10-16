<?php
declare(strict_types=1);

namespace App\Models\Quiz;

use App\Services\FirestoreService\FirestoreService;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class Quiz
{
    public static function validate(array $input): array
    {
        $raw = trim((string) ($input['questions_json'] ?? ''));
        $questions = json_decode($raw, true);
        if (is_array($questions)) $questions = array_values($questions);
        $data = [
            'lessonId' => trim((string) ($input['lesson_id'] ?? '')),
            'title' => trim((string) ($input['title'] ?? '')),
            'questions' => is_array($questions) ? $questions : [],
            'published' => isset($input['published']) && (string) $input['published'] === '1',
        ];
        $errors = [];
        if ($data['lessonId'] === '' || strlen($data['lessonId']) > 150) $errors['lesson_id'] = 'Choose a valid lesson.';
        if ($data['title'] === '' || mb_strlen($data['title']) > 150) $errors['title'] = 'Add a quiz title up to 150 characters.';
        if (!is_array($questions)) $errors['questions'] = 'Questions must be valid JSON.';
        elseif (count($questions) < 1 || count($questions) > 10) $errors['questions'] = 'A quiz must have 1 to 10 questions.';
        else foreach ($questions as $index => $question) {
            if (!is_array($question) || trim((string) ($question['prompt'] ?? '')) === '' || mb_strlen((string) ($question['prompt'] ?? '')) > 500) { $errors['questions'] = 'Each question needs a prompt up to 500 characters.'; break; }
            if (!is_array($question['options'] ?? null) || count($question['options']) !== 4) { $errors['questions'] = 'Each question must have exactly four options.'; break; }
            foreach ($question['options'] as $option) if (!is_string($option) || trim($option) === '' || mb_strlen($option) > 300) { $errors['questions'] = 'Each answer option must contain 1 to 300 characters.'; break 2; }
            if (!in_array($question['correct'] ?? '', ['A', 'B', 'C', 'D'], true)) { $errors['questions'] = 'Choose a correct answer (A, B, C or D) for each question.'; break; }
            $questions[$index] = ['prompt' => trim($question['prompt']), 'options' => array_values(array_map('trim', $question['options'])), 'correct' => $question['correct']];
        }
        $data['questions'] = $questions;
        return [$data, $errors];
    }

    public static function forLesson(FirestoreService $firestore, string $lessonId, bool $publishedOnly = true): ?array
    {
        $quizzes = $firestore->listByField('academy_quizzes', 'lessonId', $lessonId, 10);
        foreach ($quizzes as $quiz) if (!$publishedOnly || !empty($quiz['published'])) return $quiz;
        return null;
    }

    public static function save(FirestoreService $firestore, array $data, ?string $id = null): string
    {
        if ($id !== null && !preg_match('/^[A-Za-z0-9_-]{1,150}$/', $id)) throw new InvalidArgumentException('Invalid quiz ID.');
        $data['updatedAt'] = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        if ($id === null) {
            $data['createdAt'] = $data['updatedAt'];
            return $firestore->add('academy_quizzes', $data);
        }
        $firestore->update('academy_quizzes', $id, $data);
        return $id;
    }
}
