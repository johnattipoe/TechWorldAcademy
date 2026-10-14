<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$errors = [];
$warnings = [];
$check = static function (bool $ok, string $message) use (&$errors): void {
    echo ($ok ? '[OK]   ' : '[FAIL] ') . $message . PHP_EOL;
    if (!$ok) $errors[] = $message;
};
$warn = static function (bool $ok, string $message) use (&$warnings): void {
    echo ($ok ? '[OK]   ' : '[WARN] ') . $message . PHP_EOL;
    if (!$ok) $warnings[] = $message;
};

$check(PHP_VERSION_ID >= 80000, 'PHP 8.0 or newer is installed.');
foreach (['mbstring', 'curl', 'openssl', 'fileinfo'] as $extension) {
    $check(extension_loaded($extension), 'PHP extension ' . $extension . ' is available.');
}
$check(is_file(dirname(__DIR__) . '/vendor/autoload.php'), 'Composer dependencies are installed.');

$appUrl = (string) env_value('APP_URL', env_value('RENDER_EXTERNAL_URL', ''));
$isHttps = filter_var($appUrl, FILTER_VALIDATE_URL) && parse_url($appUrl, PHP_URL_SCHEME) === 'https';
$production = env_value('APP_ENV', 'local') === 'production';
$warn(!$production || PHP_VERSION_ID >= 80300, 'Production PHP runtime is PHP 8.3 or newer.');
$warn(!$production || $isHttps, 'Production APP_URL uses HTTPS.');
$check(!$production || (string) env_value('FIREBASE_PROJECT_ID', '') !== '', 'Production Firebase project ID is configured.');

$credentialValue = (string) env_value('FIREBASE_CREDENTIALS', '');
$credentialPath = $credentialValue;
if ($credentialPath !== '' && !preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $credentialPath)) {
    $credentialPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $credentialPath;
}
$realCredential = $credentialPath !== '' ? realpath($credentialPath) : false;
$publicRoot = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public');
$outsidePublic = $realCredential !== false && is_file($realCredential)
    && !($publicRoot && stripos($realCredential, $publicRoot . DIRECTORY_SEPARATOR) === 0);
$credentialJson = $outsidePublic ? json_decode((string) file_get_contents($realCredential), true) : null;
$firebaseReady = $outsidePublic && is_array($credentialJson) && !empty($credentialJson['client_email'])
    && !empty($credentialJson['private_key']) && (string) ($credentialJson['project_id'] ?? '') === (string) env_value('FIREBASE_PROJECT_ID', '');
$warn(!$production || $firebaseReady, 'Production Firebase service-account file is valid, matches the project, and is outside public/.');

$storageRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework';
foreach (['sessions', 'rate-limits'] as $directory) {
    $path = $storageRoot . DIRECTORY_SEPARATOR . $directory;
    if (!is_dir($path)) @mkdir($path, 0700, true);
    $warn(is_dir($path) && is_writable($path), 'storage/framework/' . $directory . ' is writable by PHP.');
}

$adminHash = (string) env_value('ADMIN_PASSWORD_HASH', '');
$warn($adminHash === '' || password_get_info($adminHash)['algo'] !== null, 'ADMIN_PASSWORD_HASH is a password_hash() result when admin access is enabled.');
$unsubscribeSecret = (string) env_value('NEWSLETTER_UNSUBSCRIBE_SECRET', '');
$warn(!$production || strlen($unsubscribeSecret) >= 32, 'Production newsletter unsubscribe secret has at least 32 characters.');
$mailFrom = (string) env_value('MAIL_FROM', '');
$warn(!$production || filter_var($mailFrom, FILTER_VALIDATE_EMAIL) !== false, 'Production MAIL_FROM is a valid email address.');

echo PHP_EOL . (count($errors) ? count($errors) . ' required check(s) failed.' : 'Required checks passed.') . PHP_EOL;
if ($warnings) echo count($warnings) . ' optional or production configuration warning(s) need attention.' . PHP_EOL;
exit($errors || ($production && $warnings) ? 1 : 0);
