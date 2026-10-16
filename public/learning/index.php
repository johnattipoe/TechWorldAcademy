<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

start_app_session();

if (!empty($_SESSION['learning_user_id'])) {
    $dashboard = match ((string) ($_SESSION['learning_user_role'] ?? 'student')) {
        'admin' => dirname(__DIR__) . '/admin/index.php',
        'instructor' => dirname(__DIR__) . '/instructor/index.php',
        default => dirname(__DIR__) . '/student/dashboard/index.php',
    };
    require $dashboard;
    exit;
}

require_once dirname(__DIR__, 2) . '/app/Views/learning/home.php';
$courses = (new \App\Controllers\CourseController\CourseController())->index();
$query = $_GET['q'] ?? '';
$query = is_string($query) ? substr(trim($query), 0, 120) : '';
$level = $_GET['level'] ?? 'all';
$level = is_string($level) ? $level : 'all';
$access = is_string($_GET['access'] ?? null) ? $_GET['access'] : 'all';
$program = is_string($_GET['program'] ?? null) ? $_GET['program'] : 'all';
$collection = is_string($_GET['collection'] ?? null) ? $_GET['collection'] : 'all';

page_start(
    'Explore online courses',
    'Explore practical technology courses, find a learning path and start building skills with TechWorld Learning.'
);
render_learning_home($courses, $query, $level, $access, $program, $collection);
page_end();
