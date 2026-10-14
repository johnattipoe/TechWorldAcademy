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
    <title><?= e($title) ?> &#183; TechWorld Academy</title>
    <link rel="sitemap" type="application/xml" href="/sitemap.xml">
    <link rel="stylesheet" href="/assets/css/compiled/tailwind.css">
    <link rel="stylesheet" href="/assets/css/style/style.css">
    <link rel="stylesheet" href="/assets/css/components/nav-loading.css">
    <link rel="stylesheet" href="/assets/css/components/site-expansion.css">
    <link rel="stylesheet" href="/assets/css/components/language-switcher.css">
    <link rel="stylesheet" href="/assets/css/responsive/responsive.css">
</head>
<body>
<div class="page-loader" id="page-loader" role="status" aria-live="polite" hidden>
    <div class="page-loader-card"><span class="page-loader-mark" aria-hidden="true">T</span><strong>TechWorld Academy</strong><span class="page-loader-spinner" aria-hidden="true"></span><p><?= e(locale_text('Loading your next step...', 'Chargement de votre prochaine étape…', 'Cargando tu próximo paso…')) ?></p></div>
</div>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="/" aria-label="TechWorld Academy home"<?= $path === '/' ? ' aria-current="page"' : '' ?>>
            <span class="brand-mark" aria-hidden="true">T</span><span>TechWorld <b>Academy</b></span>
        </a>
        <?php require dirname(__DIR__) . '/navbar/navbar.php'; ?>
    </div>
</header>
<?php if ($path !== '/'): ?>
    <?php
    $crumbLabels = [
        '/courses/' => 'Courses', '/training/' => 'Training', '/services/' => 'Team training',
        '/about/' => 'About', '/faq/' => 'FAQ', '/contact/' => 'Contact',
        '/resources/' => 'Resources', '/resources/guides/' => 'Learning guides',
        '/resources/glossary/' => 'Tech glossary', '/resources/projects/' => 'Practice projects',
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
