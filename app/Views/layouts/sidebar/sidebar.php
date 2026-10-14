<?php
function render_admin_sidebar(string $tab): void
{
$adminLinks = [
    'inquiries' => ['/admin/', 'Enquiries'],
    'courses' => ['/admin/?tab=courses', 'Course catalogue'],
    'subscribers' => ['/admin/?tab=subscribers', 'Newsletter subscribers'],
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
