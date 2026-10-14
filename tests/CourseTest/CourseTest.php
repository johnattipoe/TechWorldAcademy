<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

return [
    'valid course data passes validation' => static function (): void {
        [, $errors] = \App\Models\Course\Course::validate([
            'title' => 'Accessible Web Design', 'description' => 'Build accessible interfaces with semantic HTML and practical exercises.',
            'level' => 'Beginner', 'duration' => '6 weeks', 'icon' => 'WEB', 'color' => 'icon-blue', 'published' => '1',
        ]);
        if ($errors !== []) throw new RuntimeException('Expected valid course data to pass.');
    },
    'invalid course level and description are rejected' => static function (): void {
        [, $errors] = \App\Models\Course\Course::validate(['title' => 'Course', 'description' => 'tiny', 'level' => 'Expert', 'duration' => '4 weeks']);
        if (!isset($errors['description'], $errors['level'])) throw new RuntimeException('Expected invalid course fields to be rejected.');
    },
];
