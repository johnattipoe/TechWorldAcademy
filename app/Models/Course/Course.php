<?php
declare(strict_types=1);

namespace App\Models\Course;

final class Course
{
    public static function catalogue(): array
    {
        return [
            ['title' => 'Web Development', 'description' => 'Create modern, accessible websites with HTML, CSS, JavaScript and practical projects.', 'level' => 'Beginner', 'duration' => '8 weeks', 'icon' => '{ }', 'color' => 'icon-blue'],
            ['title' => 'Data Analytics', 'description' => 'Work with spreadsheets, SQL and data visualisation to answer useful questions.', 'level' => 'Beginner', 'duration' => '10 weeks', 'icon' => '◈', 'color' => 'icon-orange'],
            ['title' => 'AI & Automation', 'description' => 'Understand generative AI and build thoughtful workflows for real tasks.', 'level' => 'All levels', 'duration' => '6 weeks', 'icon' => '✳', 'color' => 'icon-green'],
            ['title' => 'Cybersecurity Essentials', 'description' => 'Learn core security concepts, safe practices and how to spot common threats.', 'level' => 'Beginner', 'duration' => '6 weeks', 'icon' => '⌁', 'color' => 'icon-purple'],
            ['title' => 'Cloud Computing', 'description' => 'Explore cloud foundations, deployment concepts and essential cloud services.', 'level' => 'Intermediate', 'duration' => '8 weeks', 'icon' => '☁', 'color' => 'icon-blue'],
            ['title' => 'Python Programming', 'description' => 'Build a strong programming foundation and automate useful everyday work.', 'level' => 'Beginner', 'duration' => '8 weeks', 'icon' => '⌘', 'color' => 'icon-orange'],
        ];
    }

    public static function validate(array $input): array
    {
        $data = [
            'title' => trim((string) ($input['title'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'level' => trim((string) ($input['level'] ?? '')),
            'duration' => trim((string) ($input['duration'] ?? '')),
            'icon' => trim((string) ($input['icon'] ?? '</>')),
            'color' => trim((string) ($input['color'] ?? 'icon-blue')),
            'published' => isset($input['published']) && (string) $input['published'] === '1',
        ];
        $errors = [];
        if ($data['title'] === '' || mb_strlen($data['title']) > 100) $errors['title'] = 'Add a title up to 100 characters.';
        if (mb_strlen($data['description']) < 20 || mb_strlen($data['description']) > 1000) $errors['description'] = 'Write a description between 20 and 1,000 characters.';
        if (!in_array($data['level'], ['Beginner', 'Intermediate', 'Advanced', 'All levels'], true)) $errors['level'] = 'Choose a valid course level.';
        if ($data['duration'] === '' || mb_strlen($data['duration']) > 60) $errors['duration'] = 'Add a duration up to 60 characters.';
        if ($data['icon'] === '' || mb_strlen($data['icon']) > 12) $errors['icon'] = 'Add a short course icon or symbol.';
        if (!in_array($data['color'], ['icon-blue', 'icon-orange', 'icon-green', 'icon-purple'], true)) $errors['color'] = 'Choose a valid course accent.';
        return [$data, $errors];
    }
}
