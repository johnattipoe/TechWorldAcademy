<?php
declare(strict_types=1);

namespace App\Services\RateLimitService;

use RuntimeException;

final class RateLimitService
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory ?: dirname(__DIR__, 3) . '/storage/framework/rate-limits';
    }

    /** Consume one request slot. State is protected from concurrent requests with an exclusive file lock. */
    public function consume(string $key, string $bucket, int $limit, int $windowSeconds): array
    {
        if (!preg_match('/^[a-z0-9_-]{1,40}$/i', $bucket)) throw new RuntimeException('Invalid rate limit bucket.');
        $limit = max(1, min(100, $limit));
        $windowSeconds = max(60, min(86400, $windowSeconds));
        if (!is_dir($this->directory) && !@mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new RuntimeException('Rate limit storage is unavailable.');
        }
        $file = rtrim($this->directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $bucket . '-' . hash('sha256', $key) . '.json';
        $handle = @fopen($file, 'c+');
        if (!$handle || !flock($handle, LOCK_EX)) throw new RuntimeException('Rate limit storage is unavailable.');
        try {
            rewind($handle);
            $state = json_decode((string) stream_get_contents($handle), true);
            if (!is_array($state) || !isset($state['start'], $state['count'])) $state = ['start' => time(), 'count' => 0];
            $elapsed = time() - (int) $state['start'];
            if ($elapsed >= $windowSeconds) $state = ['start' => time(), 'count' => 0];
            if ((int) $state['count'] >= $limit) return ['allowed' => false, 'retry_after' => max(1, $windowSeconds - (time() - (int) $state['start']))];
            $state['count']++;
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($state, JSON_THROW_ON_ERROR));
            fflush($handle);
            return ['allowed' => true, 'retry_after' => 0];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

}
