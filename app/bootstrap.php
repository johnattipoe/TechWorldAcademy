<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

function app_uses_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    if (env_value('RENDER') === 'true' && strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') return true;
    $appUrl = (string) env_value('APP_URL', env_value('RENDER_EXTERNAL_URL', ''));
    return parse_url($appUrl, PHP_URL_SCHEME) === 'https';
}

function current_locale(): string
{
    $supported = ['en', 'fr', 'es'];
    $requested = $_GET['lang'] ?? null;
    if (is_string($requested) && in_array($requested, $supported, true)) {
        $secure = app_uses_https();
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

function tech_icon(string $symbol, string $class = 'tech-icon'): string
{
    $symbols = ['graduation', 'web-development', 'mobile', 'analytics', 'cybersecurity', 'design', 'database', 'cloud', 'ai', 'business', 'video-learning', 'certificate', 'support', 'python', 'html', 'javascript', 'java', 'php', 'code-cpp', 'globe', 'checklist', 'book-open', 'lesson', 'quiz', 'progress', 'instructor', 'community', 'accessibility', 'calendar'];
    if (!in_array($symbol, $symbols, true)) $symbol = 'web-development';
    $safeClass = preg_replace('/[^A-Za-z0-9 _-]/', '', $class) ?: 'tech-icon';
    return '<svg class="' . e($safeClass) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="/assets/icons/techworld-icons.svg#' . e($symbol) . '"></use></svg>';
}

function course_icon_key(string $title): string
{
    $title = strtolower($title);
    if (preg_match('/\bai\b/', $title) === 1) return 'ai';
    foreach (['cyber' => 'cybersecurity', 'security' => 'cybersecurity', 'database' => 'database', 'sql' => 'database', 'data' => 'analytics', 'analytics' => 'analytics', 'cloud' => 'cloud', 'javascript' => 'javascript', 'java' => 'java', 'python' => 'python', 'php' => 'php', 'mobile' => 'mobile', 'design' => 'design', 'artificial intelligence' => 'ai', 'machine learning' => 'ai', 'automation' => 'ai', 'software' => 'code-cpp', 'programming' => 'code-cpp', 'business' => 'business', 'product' => 'checklist', 'marketing' => 'analytics', 'network' => 'globe', 'support' => 'support'] as $needle => $icon) {
        if (str_contains($title, $needle)) return $icon;
    }
    return 'web-development';
}

function course_illustration_url(string $title): string
{
    $artwork = [
        'web-development' => 'web-development', 'analytics' => 'data-analytics', 'database' => 'data-analytics',
        'cybersecurity' => 'cybersecurity', 'cloud' => 'cloud-ai', 'ai' => 'cloud-ai',
        'design' => 'mobile-design', 'mobile' => 'mobile-design', 'business' => 'data-analytics',
        'support' => 'cybersecurity', 'globe' => 'cloud-ai', 'checklist' => 'data-analytics', 'python' => 'programming',
        'javascript' => 'programming', 'java' => 'programming', 'php' => 'programming', 'code-cpp' => 'programming',
    ];
    $symbol = course_icon_key($title);
    return '/assets/images/course-art/' . ($artwork[$symbol] ?? 'programming') . '.svg';
}

function course_card_badges(array $course): string
{
    $access = (string) ($course['accessType'] ?? 'free');
    $program = (string) ($course['programType'] ?? 'course');
    $badges = [];
    $accessLabels = ['free' => ['Free', 'badge-access-free'], 'free_trial' => ['7-day trial', 'badge-access-free_trial'], 'paid' => ['Paid', 'badge-access-paid']];
    $programLabels = ['certificate' => ['Certificate', ''], 'degree_pathway' => ['Degree pathway', '']];
    if (isset($accessLabels[$access])) {
        $badges[] = $accessLabels[$access];
        if ($access === 'paid' && (float) ($course['price'] ?? 0) > 0) {
            $currency = strtoupper((string) ($course['currency'] ?? 'GHS'));
            $amount = number_format((float) $course['price'], 2, '.', ',');
            $badges[] = [$currency . ' ' . $amount, 'badge-access-paid'];
        } elseif ($access === 'paid') {
            $badges[] = ['Price pending', 'badge-access-paid'];
        } elseif ($access === 'free_trial' && (float) ($course['price'] ?? 0) > 0) {
            $currency = strtoupper((string) ($course['currency'] ?? 'GHS'));
            $amount = number_format((float) $course['price'], 2, '.', ',');
            $badges[] = [$currency . ' ' . $amount . ' after trial', 'badge-access-paid'];
        }
    }
    if (isset($programLabels[$program])) $badges[] = $programLabels[$program];
    if (!empty($course['trending'])) $badges[] = ['Trending now', 'badge-trending'];
    if (!empty($course['isNew'])) $badges[] = ['New', 'badge-new'];
    if (!empty($course['popular'])) $badges[] = ['Popular', 'badge-popular'];
    if (!$badges) return '';

    $html = '<div class="learning-course-badges" aria-label="Course features">';
    foreach ($badges as [$label, $modifier]) {
        $class = trim('course-badge ' . $modifier);
        $html .= '<span class="' . e($class) . '">' . e($label) . '</span>';
    }
    return $html . '</div>';
}

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(is_learning_platform_host() ? 'TWALEARNSESSID' : 'TWASITESESSID');
        $https = app_uses_https();
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        $sessionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions';
        if (!is_dir($sessionPath)) @mkdir($sessionPath, 0700, true);
        if (is_dir($sessionPath) && is_writable($sessionPath)) session_save_path($sessionPath);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
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
    if (is_learning_portal_page($path)) {
        require_once __DIR__ . '/Views/learning/layouts/header/index.php';
        render_learning_header($title, $description, $path);
        return;
    }
    require_once __DIR__ . '/Views/layouts/header/header.php';
    render_site_header($title, $description, $path);
}

function page_end(): void
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (is_learning_portal_page($path)) {
        require_once __DIR__ . '/Views/learning/layouts/footer/index.php';
        render_learning_footer();
        if (ob_get_level() > 0) ob_end_flush();
        return;
    }
    require_once __DIR__ . '/Views/layouts/footer/footer.php';
    render_site_footer();
    if (ob_get_level() > 0) ob_end_flush();
}

function learning_platform_url(string $path = '/'): string
{
    $configured = trim((string) env_value('LEARNING_PLATFORM_URL', ''));
    if ($configured === '' && env_value('APP_ENV', 'local') !== 'production') $configured = 'http://127.0.0.1:8001';
    if ($configured === '') return $path;
    $parts = parse_url($configured);
    if (!is_array($parts) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host']) || isset($parts['user'], $parts['pass'])) return $path;
    return rtrim($configured, '/') . '/' . ltrim($path, '/');
}

function public_website_url(string $path = '/'): string
{
    $configured = trim((string) env_value('PUBLIC_WEBSITE_URL', ''));
    if ($configured === '') $configured = trim((string) env_value('APP_URL', env_value('RENDER_EXTERNAL_URL', '')));
    if (env_value('APP_ENV', 'local') !== 'production') {
        $parts = parse_url($configured);
        if (is_array($parts) && in_array($parts['host'] ?? '', ['127.0.0.1', 'localhost'], true) && (int) ($parts['port'] ?? 0) === 8001) {
            $configured = ($parts['scheme'] ?? 'http') . '://' . $parts['host'] . ':8000';
        }
        if ($configured === '') $configured = 'http://127.0.0.1:8000';
    }
    $parts = parse_url($configured);
    if (!is_array($parts) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) return $path;
    return rtrim($configured, '/') . '/' . ltrim($path, '/');
}

function is_learning_platform_host(): bool
{
    $configured = trim((string) env_value('LEARNING_PLATFORM_URL', ''));
    if ($configured === '' && env_value('APP_ENV', 'local') !== 'production') $configured = 'http://127.0.0.1:8001';
    $platform = parse_url($configured);
    if (!is_array($platform) || empty($platform['host'])) return false;
    $current = parse_url('http://' . (string) ($_SERVER['HTTP_HOST'] ?? ''));
    if (!is_array($current) || strtolower((string) ($current['host'] ?? '')) !== strtolower((string) $platform['host'])) return false;
    return !isset($platform['port']) || (int) ($current['port'] ?? 80) === (int) $platform['port'];
}

function is_learning_portal_page(?string $path = null): bool
{
    if (is_learning_platform_host()) return true;
    $path = $path ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    return preg_match('#^/(?:login|register|logout|forgot-password|reset-password|student|instructor|business|universities|government)(?:/|$)#', $path) === 1;
}

function firestore_database()
{
    return \App\Services\FirebaseService\FirebaseService::firestore();
}

function site_settings(): array
{
    static $settings = null;
    if (is_array($settings)) return $settings;
    $settings = \App\Models\SiteSettings\SiteSettings::defaults();
    if (!env_value('FIREBASE_PROJECT_ID') || !env_value('FIREBASE_CREDENTIALS')) return $settings;
    try {
        $settings = \App\Models\SiteSettings\SiteSettings::get(new \App\Services\FirestoreService\FirestoreService(firestore_database()));
    } catch (\Throwable $exception) {
        error_log('Site settings could not be loaded: ' . $exception->getMessage());
    }
    return $settings;
}

function site_setting(string $key, mixed $default = null): mixed
{
    return site_settings()[$key] ?? $default;
}

function page_copy(string $key): array
{
    static $copies = [];
    if (isset($copies[$key])) return $copies[$key];
    $pages = \App\Models\PageContent\PageContent::pages();
    if (!isset($pages[$key])) return [];
    $copies[$key] = $pages[$key];
    if (!env_value('FIREBASE_PROJECT_ID') || !env_value('FIREBASE_CREDENTIALS')) return $copies[$key];
    try {
        $copies[$key] = \App\Models\PageContent\PageContent::get(new \App\Services\FirestoreService\FirestoreService(firestore_database()), $key);
    } catch (\Throwable $exception) {
        error_log('Page content could not be loaded: ' . $exception->getMessage());
    }
    return $copies[$key];
}

function load_local_env(): void
{
    $file = dirname(__DIR__) . '/.env';
    if (!is_file($file)) return;
    $preserveProcessEnvironment = getenv('APP_ENV') === 'production';
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $refreshLocalAdminCredential = !$preserveProcessEnvironment && in_array($key, ['ADMIN_EMAIL', 'ADMIN_PASSWORD_HASH'], true);
        if (getenv($key) === false || $refreshLocalAdminCredential) putenv($key . '=' . trim(trim($value), "\"'"));
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
