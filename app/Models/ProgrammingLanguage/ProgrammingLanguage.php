<?php
declare(strict_types=1);

namespace App\Models\ProgrammingLanguage;

final class ProgrammingLanguage
{
    public static function catalogue(): array
    {
        return [
            ['name' => 'JavaScript', 'slug' => 'javascript', 'description' => 'Add interaction and responsive behaviour to web experiences.'],
            ['name' => 'Python', 'slug' => 'python', 'description' => 'Learn a readable language used for scripting, data and automation.'],
            ['name' => 'SQL', 'slug' => 'sql', 'description' => 'Query and organise relational data with confidence.'],
            ['name' => 'HTML & CSS', 'slug' => 'html-css', 'description' => 'Structure and style clear, accessible web pages.'],
        ];
    }
}
