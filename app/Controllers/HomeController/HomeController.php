<?php
declare(strict_types=1);

namespace App\Controllers\HomeController;

use App\Controllers\CourseController\CourseController;

final class HomeController
{
    public function index(): array
    {
        return [
            'featuredCourses' => (new CourseController())->featured(),
            'promises' => [
                'Learn at your pace',
                'Practical, guided projects',
                'Support from real people',
            ],
        ];
    }
}
