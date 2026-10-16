<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/Views/learning/audience/government/index.php';

$governmentCourses = (new \App\Controllers\CourseController\CourseController())->index();
page_start('Technology learning for government', 'Explore practical digital skills courses for public sector teams, workforce programs and community learners.');
render_learning_government($governmentCourses);
page_end();
