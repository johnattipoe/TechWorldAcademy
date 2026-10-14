<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$base = rtrim((string) env_value('APP_URL', 'https://example.com'), '/');
$paths = ['/', '/courses/', '/about/', '/training/', '/services/', '/resources/', '/resources/guides/', '/resources/glossary/', '/resources/projects/', '/faq/', '/contact/', '/privacy/', '/terms/'];
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($paths as $path) echo '<url><loc>' . htmlspecialchars($base . $path, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
echo '</urlset>';
