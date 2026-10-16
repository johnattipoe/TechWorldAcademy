<?php
function render_learning_header(string $title, string $description, string $path): void
{
    $searchQuery = $_GET['q'] ?? '';
    if (!is_string($searchQuery)) $searchQuery = '';
    $isInstructor = ($_SESSION['learning_user_role'] ?? '') === 'instructor';
    $learningHomeUrl = $isInstructor ? '/instructor/' : (!empty($_SESSION['learning_user_id']) ? '/student/' : '/learning/');
    $courseBrowsePath = !empty($_SESSION['learning_user_id']) && !$isInstructor ? '/student/' : '/learning/';
    $accountName = (string) ($_SESSION['learning_user_name'] ?? 'Learner');
    $accountEmail = (string) ($_SESSION['learning_user_email'] ?? '');
    $accountInitial = function_exists('mb_substr') ? mb_strtoupper(mb_substr(trim($accountName), 0, 1)) : strtoupper(substr(trim($accountName), 0, 1));
    $localeQuery = $_GET;
    unset($localeQuery['lang']);
    $localePath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $localeNames = ['en' => 'English', 'fr' => 'Français', 'es' => 'Español'];
?>
<!doctype html>
<html lang="<?= e(current_locale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="referrer" content="no-referrer">
    <meta name="theme-color" content="#f5f7fb">
    <link rel="icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
    <title><?= e($title) ?> | TechWorld Learning</title>
    <link rel="stylesheet" href="/assets/css/compiled/tailwind.css">
    <link rel="stylesheet" href="/assets/css/style/style.css">
    <link rel="stylesheet" href="/assets/css/components/site-expansion.css">
    <link rel="stylesheet" href="/assets/css/components/admin-control.css">
    <link rel="stylesheet" href="/assets/css/responsive/responsive.css">
    <link rel="stylesheet" href="/assets/css/components/nav-loading.css">
    <link rel="stylesheet" href="/assets/css/learning-platform.css">
    <?php if ($path === '/student/lesson/'): ?><link rel="stylesheet" href="/assets/css/lesson-experience.css"><?php endif; ?>
</head>
<body class="learning-platform-body<?= $path === '/student/lesson/' ? ' lesson-player-body' : '' ?>">
<div class="page-loader learning-page-loader" id="page-loader" role="status" aria-live="polite" aria-label="Loading TechWorld Learning" hidden>
    <div class="learning-loader-card">
        <span class="learning-loader-orbit learning-loader-orbit-one" aria-hidden="true"></span>
        <span class="learning-loader-orbit learning-loader-orbit-two" aria-hidden="true"></span>
        <img class="learning-loader-mark" src="/assets/icons/techworld-mark.svg" alt="" aria-hidden="true">
        <strong>TechWorld <span>Learning</span></strong>
        <span class="learning-loader-progress" aria-hidden="true"><i></i></span>
        <p>Preparing your next step</p>
    </div>
</div>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header learning-site-header">
        <?php if (empty($_SESSION['learning_user_id'])): ?><div class="learning-audience-bar"><nav class="container" aria-label="Learning for"><a href="/learning/#catalogue">For individuals</a><a href="/business/">For businesses</a><a href="/universities/">For universities</a><a href="/government/">For government</a></nav></div><?php endif; ?>
    <div class="container learning-platform-nav">
        <a class="learning-platform-brand" href="<?= e($learningHomeUrl) ?>" aria-label="<?= e((string) site_setting('siteName', 'TechWorld Academy')) ?> Learning home"><img class="brand-mark" src="/assets/icons/techworld-mark.svg" alt="" aria-hidden="true"><span><?= e((string) site_setting('siteName', 'TechWorld Academy')) ?> <strong>Learning</strong></span></a>
        <nav class="learning-discovery-nav" aria-label="Explore learning">
            <details data-nav-dropdown class="learning-nav-dropdown">
                <summary>Explore <span aria-hidden="true">&#9662;</span></summary>
                <div class="learning-nav-dropdown-menu">
                    <a href="<?= $isInstructor ? '/learning/#catalogue' : (!empty($_SESSION['learning_user_id']) ? '/student/#student-recommend-title' : '/learning/#catalogue') ?>">All courses</a>
                    <a href="<?= e($courseBrowsePath) ?>?q=Web#<?= !empty($_SESSION['learning_user_id']) && !$isInstructor ? 'student-recommend-title' : 'catalogue' ?>">Web development</a>
                    <a href="<?= e($courseBrowsePath) ?>?q=Data#<?= !empty($_SESSION['learning_user_id']) && !$isInstructor ? 'student-recommend-title' : 'catalogue' ?>">Data and analytics</a>
                    <a href="<?= e($courseBrowsePath) ?>?q=AI#<?= !empty($_SESSION['learning_user_id']) && !$isInstructor ? 'student-recommend-title' : 'catalogue' ?>">AI and automation</a>
                    <a href="<?= e($courseBrowsePath) ?>?q=Design#<?= !empty($_SESSION['learning_user_id']) && !$isInstructor ? 'student-recommend-title' : 'catalogue' ?>">Design</a>
                    <a href="<?= e($courseBrowsePath) ?>?q=Business#<?= !empty($_SESSION['learning_user_id']) && !$isInstructor ? 'student-recommend-title' : 'catalogue' ?>">Business</a>
                </div>
            </details>
            <?php if (empty($_SESSION['learning_user_id'])): ?><a class="learning-degree-link" href="/learning/?q=degree#catalogue">Degrees</a><?php endif; ?>
        </nav>
        <form class="learning-search" method="get" action="<?= !empty($_SESSION['learning_user_id']) && !$isInstructor ? '/student/' : '/learning/#catalogue' ?>" role="search">
            <label class="visually-hidden" for="learning-search-input">Search courses</label>
            <input id="learning-search-input" type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="What do you want to learn?">
            <button type="submit" aria-label="Search courses"><span aria-hidden="true">&#9906;</span></button>
        </form>
        <?php if (!empty($_SESSION['learning_user_id'])): ?>
        <?php if ($isInstructor): ?>
        <nav aria-label="Instructor navigation"><a href="/instructor/"<?= in_array($path, ['/instructor/', '/instructor/courses/'], true) ? ' aria-current="page"' : '' ?>>My courses</a><a href="/instructor/?tab=submissions"<?= in_array($path, ['/instructor/submissions/'], true) || (($_GET['tab'] ?? '') === 'submissions') ? ' aria-current="page"' : '' ?>>Learner submissions</a></nav>
        <?php else: ?>
        <nav aria-label="Learning platform navigation">
            <a href="/student/"<?= $path === '/student/' ? ' aria-current="page"' : '' ?>>My learning</a>
            <a href="/student/my-courses/"<?= $path === '/student/my-courses/' ? ' aria-current="page"' : '' ?>>My courses</a>
            <details data-nav-dropdown class="learning-nav-dropdown">
                <summary<?= in_array($path, ['/student/progress/', '/student/assessments/', '/student/assignments/', '/student/certificates/'], true) ? ' aria-current="page"' : '' ?>>Learning tools <span aria-hidden="true">&#9662;</span></summary>
                <div class="learning-nav-dropdown-menu">
                    <a href="/student/progress/"<?= $path === '/student/progress/' ? ' aria-current="page"' : '' ?>>Progress</a>
                    <a href="/student/assessments/"<?= $path === '/student/assessments/' ? ' aria-current="page"' : '' ?>>Assessments</a>
                    <a href="/student/assignments/"<?= $path === '/student/assignments/' ? ' aria-current="page"' : '' ?>>Assignments</a>
                    <a href="/student/certificates/"<?= $path === '/student/certificates/' ? ' aria-current="page"' : '' ?>>Certificates</a>
                </div>
            </details>
        </nav>
        <?php endif; ?>
        <div class="learning-account-actions">
        <details class="learning-action-menu learning-notification-menu" data-nav-dropdown>
            <summary aria-label="Notifications"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></summary>
            <div class="learning-action-dropdown learning-notification-dropdown"><strong><?= $isInstructor ? 'Learner updates' : 'Notifications' ?></strong><p><?= $isInstructor ? 'Review new learner submissions and provide feedback on your assigned courses.' : 'See your course request statuses and completed learning milestones.' ?></p><a href="<?= $isInstructor ? '/instructor/?tab=submissions' : '/student/notifications/' ?>"><?= $isInstructor ? 'Review submissions' : 'View all notifications' ?> <span aria-hidden="true">&#8594;</span></a></div>
        </details>
        <details class="learning-action-menu learning-language-menu" data-nav-dropdown>
            <summary aria-label="Choose language"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg><span><?= e(strtoupper(current_locale())) ?></span></summary>
            <div class="learning-action-dropdown learning-language-dropdown"><strong>Choose language</strong><?php foreach ($localeNames as $localeCode => $localeName): $nextQuery = $localeQuery; $nextQuery['lang'] = $localeCode; ?><a href="<?= e($localePath . '?' . http_build_query($nextQuery)) ?>" lang="<?= e($localeCode) ?>"<?= current_locale() === $localeCode ? ' aria-current="true"' : '' ?>><span><?= e($localeName) ?></span><?php if (current_locale() === $localeCode): ?><span aria-hidden="true">&#10003;</span><?php endif; ?></a><?php endforeach; ?></div>
        </details>
        <details class="learning-profile-menu" data-nav-dropdown>
            <summary aria-label="Account menu for <?= e($accountName) ?>"><span class="learning-profile-avatar" aria-hidden="true"><?= e($accountInitial !== '' ? $accountInitial : 'L') ?></span><span class="learning-profile-chevron" aria-hidden="true">&#9662;</span></summary>
            <div class="learning-profile-dropdown">
                <div class="learning-profile-identity"><strong><?= e($accountName) ?></strong><?php if ($accountEmail !== ''): ?><span><?= e($accountEmail) ?></span><?php endif; ?></div>
                <?php if ($isInstructor): ?><a href="/instructor/">Instructor workspace</a><a href="/instructor/?tab=submissions">Learner submissions</a>
                <?php else: ?>
                <a href="/student/">My learning</a>
                <a href="/student/my-courses/">My courses</a>
                <a href="/student/settings/"<?= $path === '/student/settings/' ? ' aria-current="page"' : '' ?>>Settings</a>
                <a href="/student/#student-updates">Updates</a>
                <?php endif; ?>
                <a href="<?= e(rtrim((string) env_value('PUBLIC_WEBSITE_URL', env_value('APP_URL', 'http://127.0.0.1:8000')), '/')) ?>/faq/" target="_blank" rel="noopener noreferrer">Help center <span aria-hidden="true">&#8599;</span></a>
                <form method="post" action="/logout/"><input type="hidden" name="csrf_token" value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>"><button type="submit">Sign out</button></form>
            </div>
        </details>
        </div>
        <?php else: ?>
        <nav aria-label="Learning platform navigation" class="learning-account-nav"><button type="button" data-auth-open="login">Sign in</button><button class="learning-join-button" type="button" data-auth-open="register">Join for free</button></nav>
        <?php endif; ?>
    </div>
</header>
<?php if ((bool) site_setting('announcementEnabled', false) && (string) site_setting('announcementText', '') !== ''): ?><aside class="site-announcement learning-announcement" role="status"><div class="container"><span aria-hidden="true">&#9733;</span><?= e((string) site_setting('announcementText', '')) ?></div></aside><?php endif; ?>
<main id="main" class="learning-platform-main">
<?php
}
?>
