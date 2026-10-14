<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/Views/courses/index.php';

$courses = (new \App\Controllers\CourseController\CourseController())->index();
page_start('Courses', 'Explore practical technology courses in web development, data, AI, cybersecurity, cloud and programming.');
render_courses_view($courses);
page_end();
