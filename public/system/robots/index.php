<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
$base = rtrim((string) env_value('APP_URL', 'https://example.com'), '/');
echo "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /api/\nSitemap: " . $base . "/sitemap.xml\n";
