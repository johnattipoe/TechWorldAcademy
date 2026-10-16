<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/Views/learning/audience/universities/index.php';

$universityCourses = (new \App\Controllers\CourseController\CourseController())->index();
page_start('Technology learning for universities', 'Explore practical technology courses that can complement university teaching and help students build applied skills.');
render_learning_universities($universityCourses);
page_end();
