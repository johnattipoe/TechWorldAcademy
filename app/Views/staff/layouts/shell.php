<?php
declare(strict_types=1);

function staff_page_start(string $title, string $role, string $active, string $description = '', int $notificationCount = 0): void
{
    current_locale();
    start_app_session();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    ob_start('localize_page_html');

    $isAdmin = $role === 'admin';
    $canManage = ($isAdmin && ($_SESSION['learning_user_role'] ?? '') === 'admin')
        || (!$isAdmin && ($_SESSION['learning_user_role'] ?? '') === 'instructor');
    $name = (string) ($_SESSION['learning_user_name'] ?? ($isAdmin ? 'Administrator' : 'Instructor'));
    $email = (string) ($_SESSION['learning_user_email'] ?? '');
    $navGroups = $isAdmin
        ? [
            ['Workspace', [
                ['overview', 'Dashboard', '/admin/', 'D'], ['users', 'Users', '/admin/?tab=users', 'U'], ['instructors', 'Instructors', '/admin/?tab=instructors', 'I'],
            ]],
            ['Learning management', [
                ['courses', 'Courses', '/admin/?tab=courses', 'C'], ['lessons', 'Lessons', '/admin/?tab=lessons', 'L'],
                ['quizzes', 'Quizzes', '/admin/?tab=quizzes', 'Q'], ['assignments', 'Assignments', '/admin/?tab=assignments', 'A'],
                ['enrollments', 'Enrollments', '/admin/?tab=enrollments', 'E'], ['payments', 'Payments', '/admin/?tab=payments', '$'],
                ['submissions', 'Submissions', '/admin/?tab=submissions', 'S'], ['certificates', 'Certificates', '/admin/?tab=certificates', 'C'],
            ]],
            ['Website', [
                ['inquiries', 'Website messages', '/admin/?tab=inquiries', 'M'], ['public-submissions', 'Public submissions', '/admin/?tab=public-submissions', 'F'], ['subscribers', 'Subscribers', '/admin/?tab=subscribers', 'N'],
                ['pages', 'Page content', '/admin/?tab=pages', 'P'],
            ]],
            ['Insights & settings', [
                ['activity', 'Reports', '/admin/?tab=activity', 'R'], ['settings', 'Settings', '/admin/?tab=settings', '*'],
            ]],
        ]
        : [
            ['Teaching', [
                ['courses', 'My Courses', '/instructor/', 'C'], ['create', 'Create lesson', '/instructor/#assigned-courses', '+'],
                ['activity', 'Learning activity', '/instructor/?tab=activity', 'A'],
            ]],
            ['Learner support', [
                ['students', 'Students & progress', '/instructor/?tab=students', 'S'], ['submissions', 'Assessment reviews', '/instructor/?tab=submissions', 'R'],
            ]],
            ['Account', [
                ['profile', 'Profile & settings', '/instructor/?tab=profile', 'P'], ['help', 'Help center', '/contact/', '?'],
            ]],
        ];
    $titleText = e($title . ' | TechWorld Academy');
    $descriptionText = e($description !== '' ? $description : 'Manage your TechWorld Academy workspace.');
    ?>
    <!doctype html>
    <html lang="<?= e(current_locale()) ?>">
    <head>
      <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="<?= $descriptionText ?>"><meta name="theme-color" content="#082b56">
      <title><?= $titleText ?></title>
      <link rel="icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
      <link rel="stylesheet" href="/assets/css/style/style.css">
      <link rel="stylesheet" href="/assets/css/learning-platform.css">
      <link rel="stylesheet" href="/assets/css/components/admin-control.css">
      <link rel="stylesheet" href="/assets/css/staff-dashboard.css">
    </head>
    <body class="staff-body <?= $isAdmin ? 'staff-admin' : 'staff-instructor' ?>">
      <a class="staff-skip-link" href="#staff-main">Skip to dashboard content</a>
      <div class="staff-shell">
        <aside class="staff-sidebar" aria-label="Staff navigation">
          <a class="staff-brand" href="<?= $isAdmin ? '/admin/' : '/instructor/' ?>"><span class="staff-brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32" focusable="false"><path d="M2 11.5 16 4l14 7.5L16 19 2 11.5Zm5 4.2v7.1c5.4 4.1 12.6 4.1 18 0v-7.1l-9 4.8-9-4.8Zm21-1.5v8.2h2v-9.2l-2 1Z"/></svg></span><span><strong>TechWorld Academy</strong><small>Learn &middot; Build &middot; Grow</small></span></a>
          <p class="staff-role-label"><?= $isAdmin ? 'ADMIN WORKSPACE' : 'INSTRUCTOR WORKSPACE' ?></p>
          <nav class="staff-nav" aria-label="Staff sections"<?= $canManage ? '' : ' hidden' ?>>
            <?php foreach ($navGroups as [$groupLabel, $groupLinks]): $groupActive = false; foreach ($groupLinks as [$groupKey]) if ($groupKey === $active || ($groupKey === 'courses' && $active === 'create')) $groupActive = true; ?>
              <details class="staff-nav-group"<?= $groupActive ? ' open' : '' ?>><summary><?= e($groupLabel) ?><span aria-hidden="true">v</span></summary><div class="staff-nav-links">
                <?php foreach ($groupLinks as [$key, $label, $href, $icon]): $selected = $key === $active; ?>
                  <a href="<?= e($key === 'help' ? public_website_url('/contact/') : $href) ?>" class="<?= $selected ? 'is-active' : '' ?>"<?= $selected ? ' aria-current="page"' : '' ?><?= $key === 'help' ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><span class="staff-nav-icon" aria-hidden="true"><?= e($icon) ?></span><?= e($label) ?></a>
                <?php endforeach; ?>
              </div></details>
            <?php endforeach; ?>
          </nav>
          <details class="staff-account"><summary><span class="staff-avatar" aria-hidden="true"><?= e(strtoupper(mb_substr($name, 0, 1))) ?></span><span class="staff-account-copy"><strong><?= e($name) ?></strong><small><?= $isAdmin ? 'System Administrator' : 'Instructor' ?></small></span><span class="staff-account-arrow" aria-hidden="true">›</span></summary><div class="staff-account-popover"><strong><?= e($isAdmin ? 'TechWorld Academy Administrator' : 'TechWorld Academy Instructor') ?></strong><span><?= e($email) ?></span><small><?= $isAdmin ? 'System Administrator' : 'Instructor' ?></small><a href="<?= $isAdmin ? '/admin/?tab=settings' : '/instructor/?tab=profile' ?>">Account settings</a><form method="post" action="/logout/"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><button type="submit">Sign out</button></form></div></details>
        </aside>
        <div class="staff-workspace">
          <header class="staff-topbar">
            <form class="staff-search" role="search" data-staff-search-form><label class="sr-only" for="staff-search-input">Search dashboard items</label><span class="staff-search-icon" aria-hidden="true"></span><input id="staff-search-input" type="search" placeholder="Search dashboard items..." autocomplete="off" aria-describedby="staff-search-hint"><kbd id="staff-search-hint" class="staff-search-shortcut">Ctrl K</kbd><button type="submit" aria-label="Apply search">Search</button><span class="staff-search-status" data-staff-search-status role="status" aria-live="polite" hidden></span></form>
            <div class="staff-top-actions">
              <a class="staff-notification" href="<?= $isAdmin ? '/admin/?tab=inquiries' : '/instructor/?tab=submissions' ?>" aria-label="Open <?= $isAdmin ? 'website messages' : 'assessment reviews' ?>, <?= (int) $notificationCount ?> requiring attention"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg><span aria-hidden="true"><?= $notificationCount > 99 ? '99+' : (int) $notificationCount ?></span></a>
              <details class="staff-profile-menu"><summary><span class="staff-avatar" aria-hidden="true"><?= e(strtoupper(mb_substr($name, 0, 1))) ?></span><span><strong><?= e($name) ?></strong><small><?= e($email) ?></small></span><span aria-hidden="true">v</span></summary><div class="staff-profile-popover"><strong><?= e($name) ?></strong><span><?= e($email) ?></span><a href="<?= $isAdmin ? '/admin/?tab=settings' : '/instructor/?tab=profile' ?>">Account settings</a><form method="post" action="/logout/"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><button type="submit">Sign out</button></form></div></details>
            </div>
          </header>
          <main id="staff-main" class="staff-main" tabindex="-1">
            <div class="staff-page-heading"><div><p><?= $title === 'Staff sign in' ? 'STAFF ACCESS' : 'WELCOME BACK, ' . e($name) ?></p><h1><?= e($title) ?></h1><span><?= $descriptionText ?></span></div><time><?= e((new DateTimeImmutable('now'))->format('l, F j, Y')) ?></time></div>
    <?php
}

function staff_page_end(): void
{
    ?>
          </main>
        </div>
      </div>
      <div class="staff-page-loader is-hidden" data-staff-loader aria-hidden="true"><span class="staff-loader-mark" aria-hidden="true">T</span><strong>TechWorld Academy</strong><span class="staff-loader-spinner" aria-hidden="true"></span><span data-staff-loader-label>Opening your workspace…</span></div>
      <script src="/assets/js/staff-dashboard.js" defer></script>
    </body>
    </html>
    <?php
    if (ob_get_level() > 0) ob_end_flush();
}
