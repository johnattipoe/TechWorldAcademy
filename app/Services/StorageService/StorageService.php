<?php
declare(strict_types=1);

namespace App\Services\StorageService;

use InvalidArgumentException;
use RuntimeException;

/** S3-compatible object storage client for Cloudflare R2. */
final class StorageService
{
    private string $accountId;
    private string $bucket;
    private string $accessKeyId;
    private string $secretAccessKey;
    private string $endpoint;
    private ?string $publicBaseUrl;

    public function __construct(array $configuration)
    {
        $this->accountId = trim((string) ($configuration['account_id'] ?? ''));
        $this->bucket = trim((string) ($configuration['bucket'] ?? ''));
        $this->accessKeyId = trim((string) ($configuration['access_key_id'] ?? ''));
        $this->secretAccessKey = (string) ($configuration['secret_access_key'] ?? '');
        $publicUrl = trim((string) ($configuration['public_url'] ?? ''));
        $this->publicBaseUrl = $publicUrl !== '' ? rtrim($publicUrl, '/') : null;

        if (!preg_match('/^[A-Za-z0-9_-]{5,64}$/', $this->accountId)
            || !preg_match('/^[a-z0-9][a-z0-9.-]{1,61}[a-z0-9]$/', $this->bucket)
            || $this->accessKeyId === '' || $this->secretAccessKey === '') {
            throw new InvalidArgumentException('Cloudflare R2 account, bucket, access key, and secret key must be configured.');
        }
        if (!function_exists('curl_init') || !function_exists('hash_hmac')) {
            throw new RuntimeException('The cURL and hash extensions are required for Cloudflare R2.');
        }
        if ($this->publicBaseUrl !== null) {
            $parts = parse_url($this->publicBaseUrl);
            if (!filter_var($this->publicBaseUrl, FILTER_VALIDATE_URL)
                || ($parts['scheme'] ?? '') !== 'https'
                || empty($parts['host'])
                || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
                throw new InvalidArgumentException('CLOUDFLARE_R2_PUBLIC_URL must be an HTTPS origin or path without credentials, query, or fragment.');
            }
        }

        $this->endpoint = 'https://' . $this->accountId . '.r2.cloudflarestorage.com';
    }

    public static function fromEnvironment(): self
    {
        $configuration = require dirname(__DIR__, 3) . '/config/services/services.php';
        return new self($configuration['cloudflare_r2'] ?? []);
    }

    /** Upload bytes to a private R2 object. Configure a public domain only for public assets. */
    public function put(string $key, string $contents, string $contentType = 'application/octet-stream'): array
    {
        $this->validateKey($key);
        $contentType = trim($contentType);
        if ($contentType === '' || preg_match('/[\r\n]/', $contentType)) {
            throw new InvalidArgumentException('A valid object content type is required.');
        }

        $response = $this->request('PUT', $key, $contents, $contentType);
        return [
            'key' => $key,
            'etag' => trim($response['headers']['etag'] ?? '', '"'),
            'url' => $this->publicUrl($key),
        ];
    }

    /** Retrieve an object body for server-side processing. */
    public function get(string $key): string
    {
        $this->validateKey($key);
        return $this->request('GET', $key, '', 'application/octet-stream')['body'];
    }

    /** Permanently delete an object from the configured bucket. */
    public function delete(string $key): void
    {
        $this->validateKey($key);
        $this->request('DELETE', $key, '', 'application/octet-stream');
    }

    /** Return a browser URL only when a public R2 domain has been configured. */
    public function publicUrl(string $key): ?string
    {
        $this->validateKey($key);
        if ($this->publicBaseUrl === null) return null;
        return $this->publicBaseUrl . '/' . $this->encodePath($key);
    }

    private function request(string $method, string $key, string $body, string $contentType): array
    {
        $host = $this->accountId . '.r2.cloudflarestorage.com';
        $uri = '/' . rawurlencode($this->bucket) . '/' . $this->encodePath($key);
        $url = $this->endpoint . $uri;
        $timestamp = time();
        $date = gmdate('Ymd\THis\Z', $timestamp);
        $dateStamp = gmdate('Ymd', $timestamp);
        $payloadHash = hash('sha256', $body);
        $canonicalHeaders = 'content-type:' . trim($contentType) . "\n"
            . 'host:' . $host . "\n"
            . 'x-amz-content-sha256:' . $payloadHash . "\n"
            . 'x-amz-date:' . $date . "\n";
        $signedHeaders = 'content-type;host;x-amz-content-sha256;x-amz-date';
        $canonicalRequest = $method . "\n" . $uri . "\n\n" . $canonicalHeaders . "\n"
            . $signedHeaders . "\n" . $payloadHash;
        $scope = $dateStamp . '/auto/s3/aws4_request';
        $stringToSign = "AWS4-HMAC-SHA256\n" . $date . "\n" . $scope . "\n" . hash('sha256', $canonicalRequest);
        $dateKey = hash_hmac('sha256', $dateStamp, 'AWS4' . $this->secretAccessKey, true);
        $regionKey = hash_hmac('sha256', 'auto', $dateKey, true);
        $serviceKey = hash_hmac('sha256', 's3', $regionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $serviceKey, true);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        $authorization = 'AWS4-HMAC-SHA256 Credential=' . $this->accessKeyId . '/' . $scope
            . ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature;

        $headers = [
            'Authorization: ' . $authorization,
            'Content-Type: ' . $contentType,
            'X-Amz-Content-Sha256: ' . $payloadHash,
            'X-Amz-Date: ' . $date,
            'Expect:',
        ];
        $responseHeaders = [];
        $handle = curl_init($url);
        if ($handle === false) throw new RuntimeException('Could not initialize the Cloudflare R2 request.');

        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                if (strpos($line, ':') !== false) {
                    [$name, $value] = explode(':', $line, 2);
                    $responseHeaders[strtolower(trim($name))] = trim($value);
                }
                return $length;
            },
        ]);
        if ($method === 'PUT') curl_setopt($handle, CURLOPT_POSTFIELDS, $body);

        $responseBody = curl_exec($handle);
        $curlError = curl_errno($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);
        if ($responseBody === false || $curlError !== 0) {
            throw new RuntimeException('The Cloudflare R2 request could not be completed.');
        }
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('Cloudflare R2 returned HTTP ' . $status . '.');
        }

        return ['body' => (string) $responseBody, 'headers' => $responseHeaders, 'status' => $status];
    }

    private function validateKey(string $key): void
    {
        if ($key === '' || strlen($key) > 1024 || $key[0] === '/' || strpos($key, "\0") !== false
            || preg_match('/[\x00-\x1F\x7F\\\\]/', $key)) {
            throw new InvalidArgumentException('The R2 object key is invalid.');
        }
        foreach (explode('/', $key) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidArgumentException('The R2 object key contains an invalid path segment.');
            }
        }
    }

    private function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
