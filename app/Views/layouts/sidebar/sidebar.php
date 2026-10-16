<?php
function render_admin_sidebar(string $tab): void
{
$adminLinks = [
    'overview' => ['/admin/', 'Control center'],
    'inquiries' => ['/admin/?tab=inquiries', 'Course enquiries'],
    'users' => ['/admin/?tab=users', 'Accounts & instructors'],
    'instructors' => ['/admin/?tab=instructors', 'Instructor oversight'],
    'courses' => ['/admin/?tab=courses', 'Course catalogue'],
    'pages' => ['/admin/?tab=pages', 'Page introductions'],
    'enrollments' => ['/admin/?tab=enrollments', 'Course requests'],
    'lessons' => ['/admin/?tab=lessons', 'Course lessons'],
    'quizzes' => ['/admin/?tab=quizzes', 'Quizzes'],
    'assignments' => ['/admin/?tab=assignments', 'Assignments'],
    'submissions' => ['/admin/?tab=submissions', 'Submissions'],
    'activity' => ['/admin/?tab=activity', 'Learner activity'],
    'certificates' => ['/admin/?tab=certificates', 'Certificates'],
    'subscribers' => ['/admin/?tab=subscribers', 'Newsletter subscribers'],
    'settings' => ['/admin/?tab=settings', 'Website settings'],
];
?>
<div class="admin-sidebar-title"><span class="eyebrow">Workspace</span><strong>Manage academy</strong></div>
<nav aria-label="Admin sections">
    <?php foreach ($adminLinks as $key => [$href, $label]): ?>
        <a href="<?= e($href) ?>"<?= $tab === $key ? ' aria-current="page"' : '' ?>>
            <span class="admin-sidebar-dot" aria-hidden="true"></span><?= e($label) ?>
        </a>
    <?php endforeach; ?>
</nav>
<a class="admin-sidebar-public" href="/">View public website <span aria-hidden="true">&#8599;</span></a>
<?php
}
