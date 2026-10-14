<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$webRoutes = require dirname(__DIR__) . '/routes/web/web.php';
$apiRoutes = require dirname(__DIR__) . '/routes/api/api.php';
$route = $webRoutes[$path] ?? $webRoutes[rtrim($path, '/') . '/'] ?? null;
$apiKey = ($_SERVER['REQUEST_METHOD'] ?? 'GET') . ' ' . $path;
$route = $route ?? ($apiRoutes[$apiKey] ?? null);

if ($route !== null) {
    $target = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $route);
    $realTarget = realpath($target);
    $root = realpath(dirname(__DIR__));
    if ($realTarget !== false && $root !== false && strpos($realTarget, $root . DIRECTORY_SEPARATOR) === 0 && is_file($realTarget)) {
        require $realTarget;
        return true;
    }
}

$staticPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR));
$publicRoot = realpath(__DIR__);
if ($staticPath !== false && $publicRoot !== false && strpos($staticPath, $publicRoot . DIRECTORY_SEPARATOR) === 0 && is_file($staticPath)) return false;

http_response_code(404);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/Views/errors/404.php';
page_start('Page not found', 'The page you requested could not be found. Browse TechWorld Academy courses or contact our team.');
render_not_found_view();
page_end();
return true;
