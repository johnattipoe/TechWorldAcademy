<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

return [
    'rate limiter enforces its window and does not store raw keys in filenames' => static function (): void {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'techworld-rate-test-' . bin2hex(random_bytes(6));
        $limiter = new \App\Services\RateLimitService\RateLimitService($directory);
        try {
            $first = $limiter->consume('203.0.113.42', 'contact-test', 2, 60);
            $second = $limiter->consume('203.0.113.42', 'contact-test', 2, 60);
            $third = $limiter->consume('203.0.113.42', 'contact-test', 2, 60);
            if (!$first['allowed'] || !$second['allowed'] || $third['allowed'] || $third['retry_after'] < 1) throw new RuntimeException('Rate-limit window behaved unexpectedly.');
            $files = glob($directory . DIRECTORY_SEPARATOR . '*') ?: [];
            if (count($files) !== 1 || strpos(basename($files[0]), '203.0.113.42') !== false) throw new RuntimeException('Rate-limit key was not safely hashed.');
        } finally {
            foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) @unlink($file);
            @rmdir($directory);
        }
    },
];
