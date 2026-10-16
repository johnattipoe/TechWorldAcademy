<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/Views/learning/audience/business/index.php';

$businessCourses = (new \App\Controllers\CourseController\CourseController())->index();
page_start('Technology learning for businesses', 'Help teams build practical technology skills with flexible learning paths from TechWorld Academy.');
render_learning_business($businessCourses);
page_end();
