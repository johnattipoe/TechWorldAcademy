<?php
function render_site_header(string $title, string $description, string $path): void
{
?>
<!doctype html>
<html lang="<?= e(current_locale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="theme-color" content="#f7f8f4">
    <link rel="icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
    <title><?= e($title) ?> &#183; <?= e((string) site_setting('siteName', 'TechWorld Academy')) ?></title>
    <link rel="sitemap" type="application/xml" href="/sitemap.xml">
    <link rel="stylesheet" href="/assets/css/compiled/tailwind.css">
    <link rel="stylesheet" href="/assets/css/style/style.css">
    <link rel="stylesheet" href="/assets/css/components/nav-loading.css">
    <link rel="stylesheet" href="/assets/css/components/site-expansion.css">
    <link rel="stylesheet" href="/assets/css/components/admin-control.css">
    <link rel="stylesheet" href="/assets/css/components/public-features.css">
    <link rel="stylesheet" href="/assets/css/components/language-switcher.css">
    <link rel="stylesheet" href="/assets/css/responsive/responsive.css">
</head>
<body>
<div class="page-loader" id="page-loader" role="status" aria-live="polite" hidden>
    <div class="page-loader-card"><img class="page-loader-mark" src="/assets/icons/techworld-mark.svg" alt="" aria-hidden="true"><strong>TechWorld Academy</strong><span class="page-loader-spinner" aria-hidden="true"></span><p><?= e(locale_text('Loading your next step...', 'Chargement de votre prochaine étape…', 'Cargando tu próximo paso…')) ?></p></div>
</div>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="/" aria-label="<?= e((string) site_setting('siteName', 'TechWorld Academy')) ?> home"<?= $path === '/' ? ' aria-current="page"' : '' ?>>
            <img class="brand-mark" src="/assets/icons/techworld-mark.svg" alt="" aria-hidden="true"><span><?= e((string) site_setting('siteName', 'TechWorld Academy')) ?></span>
        </a>
        <?php require dirname(__DIR__) . '/navbar/navbar.php'; ?>
    </div>
</header>
<?php if ((bool) site_setting('announcementEnabled', false) && (string) site_setting('announcementText', '') !== ''): ?><aside class="site-announcement" role="status"><div class="container"><span aria-hidden="true">&#9733;</span><?= e((string) site_setting('announcementText', '')) ?></div></aside><?php endif; ?>
<?php if ($path !== '/'): ?>
    <?php
    $crumbLabels = [
        '/courses/' => 'Courses', '/training/' => 'Training', '/services/' => 'Team training',
        '/about/' => 'About', '/faq/' => 'FAQ', '/contact/' => 'Contact',
        '/resources/' => 'Resources', '/resources/guides/' => 'Learning guides',
        '/resources/glossary/' => 'Tech glossary', '/resources/projects/' => 'Practice projects',
        '/events/' => 'Events & workshops', '/events/details/' => 'Event registration',
        '/webinars/' => 'Webinars', '/webinars/details/' => 'Webinar registration',
        '/blog/' => 'Academy blog', '/blog/articles/' => 'Article', '/careers/' => 'Careers',
        '/careers/roles/' => 'Career opportunity', '/careers/applications/' => 'Expression of interest',
        '/partners/' => 'Partners', '/partners/apply/' => 'Partnership enquiry',
        '/help/' => 'Help center', '/help/topics/' => 'Help topic', '/community/' => 'Community',
        '/community/guidelines/' => 'Community guidelines', '/success-stories/' => 'Learner stories',
        '/success-stories/stories/' => 'Learner story', '/pricing/' => 'Pricing', '/pricing/courses/' => 'Course pricing',
        '/scholarships/' => 'Scholarships', '/scholarships/apply/' => 'Scholarship application',
        '/locations/' => 'Locations', '/locations/online/' => 'Online learning',
        '/accessibility/' => 'Accessibility', '/accessibility/feedback/' => 'Accessibility feedback',
        '/status/' => 'Service status', '/status/incident-history/' => 'Incident history',
        '/privacy/' => 'Privacy policy', '/terms/' => 'Terms of use', '/admin/' => 'Staff dashboard',
    ];
    $crumb = $crumbLabels[$path] ?? $title;
    ?>
    <nav class="breadcrumbs container" aria-label="Breadcrumb">
        <a href="/">Home</a><span aria-hidden="true">/</span><span aria-current="page"><?= e($crumb) ?></span>
    </nav>
<?php endif; ?>
<main id="main">
<?php
}
