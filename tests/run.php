<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$testFiles = [
    __DIR__ . '/ContactTest/ContactTest.php',
    __DIR__ . '/CourseTest/CourseTest.php',
    __DIR__ . '/SecurityTest/SecurityTest.php',
    __DIR__ . '/RateLimitTest/RateLimitTest.php',
];
$passed = 0;
$failed = 0;
foreach ($testFiles as $file) {
    foreach (require $file as $name => $test) {
        try {
            $test();
            echo "[PASS] {$name}" . PHP_EOL;
            $passed++;
        } catch (Throwable $exception) {
            echo "[FAIL] {$name}: {$exception->getMessage()}" . PHP_EOL;
            $failed++;
        }
    }
}
echo PHP_EOL . "{$passed} passed, {$failed} failed." . PHP_EOL;
exit($failed === 0 ? 0 : 1);
