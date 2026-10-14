<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

function current_locale(): string
{
    $supported = ['en', 'fr', 'es'];
    $requested = $_GET['lang'] ?? null;
    if (is_string($requested) && in_array($requested, $supported, true)) {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('site_locale', $requested, [
            'expires' => time() + 60 * 60 * 24 * 180,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE['site_locale'] = $requested;
        $query = $_GET;
        unset($query['lang']);
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $location = $path . ($query ? '?' . http_build_query($query) : '');
        header('Location: ' . $location, true, 303);
        exit;
    }

    $locale = $_COOKIE['site_locale'] ?? 'en';
    return is_string($locale) && in_array($locale, $supported, true) ? $locale : 'en';
}

function locale_text(string $english, string $french, string $spanish): string
{
    return match (current_locale()) {
        'fr' => $french,
        'es' => $spanish,
        default => $english,
    };
}

function localized_phrase(string $english): string
{
    $locale = current_locale();
    if ($locale === 'en') return $english;
    static $catalogues = null;
    if ($catalogues === null) $catalogues = require __DIR__ . '/Localization/catalog.php';
    return (string) ($catalogues[$locale][$english] ?? $english);
}

function localize_page_html(string $html): string
{
    $locale = current_locale();
    if ($locale === 'en') return $html;

    static $catalogues = null;
    if ($catalogues === null) $catalogues = require __DIR__ . '/Localization/catalog.php';
    $translations = [];
    foreach (($catalogues[$locale] ?? []) as $source => $target) {
        $translations[$source] = htmlspecialchars($target, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedSource = htmlspecialchars($source, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $translations[$escapedSource] = htmlspecialchars($target, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    uksort($translations, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));
    return strtr($html, $translations);
}

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

function request_client_ip(): string
{
    if (env_value('RENDER') === 'true') {
        $forwarded = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
        $candidate = trim(explode(',', $forwarded, 2)[0]);
        if (filter_var($candidate, FILTER_VALIDATE_IP) !== false) return $candidate;
    }
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    return filter_var($remote, FILTER_VALIDATE_IP) !== false ? $remote : 'unknown';
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        $appUrl = (string) env_value('APP_URL', env_value('RENDER_EXTERNAL_URL', ''));
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || parse_url($appUrl, PHP_URL_SCHEME) === 'https';
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        $sessionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions';
        if (!is_dir($sessionPath)) @mkdir($sessionPath, 0700, true);
        if (is_dir($sessionPath) && is_writable($sessionPath)) session_save_path($sessionPath);
        session_set_cookie_params(['httponly' => true, 'secure' => $https, 'samesite' => 'Lax']);
        session_start();
    }
}

function page_start(string $title, string $description = ''): void
{
    current_locale();
    start_app_session();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    ob_start('localize_page_html');
    $description = $description ?: 'Practical technology training for the skills you need to build what comes next.';
    $base = rtrim((string) env_value('APP_URL', env_value('RENDER_EXTERNAL_URL', '')), '/');
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = rtrim($path, '/') . '/';
    require_once __DIR__ . '/Views/layouts/header/header.php';
    render_site_header($title, $description, $path);
}

function page_end(): void
{
    require_once __DIR__ . '/Views/layouts/footer/footer.php';
    render_site_footer();
    if (ob_get_level() > 0) ob_end_flush();
}

function firestore_database()
{
    return \App\Services\FirebaseService\FirebaseService::firestore();
}

function load_local_env(): void
{
    $file = dirname(__DIR__) . '/.env';
    if (!is_file($file)) return;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        if (getenv(trim($key)) === false) putenv(trim($key) . '=' . trim(trim($value), "\"'"));
    }
}

load_local_env();

function set_security_headers(): void
{
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    $appUrl = (string) env_value('APP_URL', env_value('RENDER_EXTERNAL_URL', ''));
    if (env_value('APP_ENV') === 'production' && parse_url($appUrl, PHP_URL_SCHEME) === 'https') {
        header('Strict-Transport-Security: max-age=31536000');
    }
    $imageSources = "'self' data: https://storage.googleapis.com https://firebasestorage.googleapis.com";
    $r2PublicUrl = trim((string) env_value('CLOUDFLARE_R2_PUBLIC_URL', ''));
    $r2UrlParts = $r2PublicUrl !== '' ? parse_url($r2PublicUrl) : false;
    if (is_array($r2UrlParts) && ($r2UrlParts['scheme'] ?? '') === 'https'
        && !empty($r2UrlParts['host']) && !isset($r2UrlParts['user'], $r2UrlParts['pass'])) {
        $imageSources .= ' https://' . $r2UrlParts['host']
            . (isset($r2UrlParts['port']) ? ':' . (int) $r2UrlParts['port'] : '');
    }
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; script-src 'self'; style-src 'self'; img-src " . $imageSources . "; connect-src 'self'");
}

set_security_headers();
