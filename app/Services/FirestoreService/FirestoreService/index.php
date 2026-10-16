<?php
declare(strict_types=1);

namespace App\Services\FirestoreService;

use InvalidArgumentException;
use RuntimeException;

final class FirestoreService
{
    private string $projectId;
    private array $credentials;
    private static array $tokens = [];

    public function __construct(array $configuration)
    {
        if (empty($configuration['project_id']) || !is_array($configuration['credentials'] ?? null)) {
            throw new InvalidArgumentException('A valid Firebase project configuration is required.');
        }
        if (!function_exists('curl_init') || !function_exists('openssl_sign')) {
            throw new RuntimeException('The cURL and OpenSSL extensions are required for Firestore access.');
        }
        $this->projectId = (string) $configuration['project_id'];
        $this->credentials = $configuration['credentials'];
    }

    public function add(string $collection, array $data): string
    {
        $result = $this->request('POST', $this->collectionPath($collection), ['fields' => FirestoreValueCodec::encodeFields($data)]);
        $name = (string) ($result['name'] ?? '');
        $id = basename($name);
        if ($id === '' || $id === '.') throw new RuntimeException('Firestore did not return a document ID.');
        return $id;
    }

    public function create(string $collection, string $id, array $data): void
    {
        if ($data === []) throw new InvalidArgumentException('Document data cannot be empty.');
        $this->request('POST', $this->collectionPath($collection) . '?documentId=' . rawurlencode($this->documentId($id)), ['fields' => FirestoreValueCodec::encodeFields($data)]);
    }

    public function get(string $collection, string $id): ?array
    {
        $path = $this->documentPath($collection, $id);
        try {
            $snapshot = $this->request('GET', $path);
        } catch (FirestoreNotFoundException $exception) {
            return null;
        }
        $documentId = basename((string) ($snapshot['name'] ?? $id));
        return ['id' => $documentId] + FirestoreValueCodec::decodeFields((array) ($snapshot['fields'] ?? []));
    }

    public function update(string $collection, string $id, array $data): void
    {
        if ($data === []) throw new InvalidArgumentException('At least one field is required to update a document.');
        $fields = FirestoreValueCodec::encodeFields($data);
        $query = [];
        foreach (array_keys($fields) as $field) $query[] = 'updateMask.fieldPaths=' . rawurlencode($field);
        $this->request('PATCH', $this->documentPath($collection, $id) . '?' . implode('&', $query), ['fields' => $fields]);
    }

    public function delete(string $collection, string $id): void
    {
        $this->request('DELETE', $this->documentPath($collection, $id));
    }

    public function list(string $collection, int $limit = 25, ?string $orderBy = 'createdAt', string $direction = 'DESCENDING'): array
    {
        if (!in_array($direction, ['ASCENDING', 'DESCENDING'], true)) throw new InvalidArgumentException('Invalid sort direction.');
        if ($orderBy !== null && !preg_match('/^[A-Za-z][A-Za-z0-9_.]{0,99}$/', $orderBy)) throw new InvalidArgumentException('Invalid sort field.');
        $limit = max(1, min(100, $limit));
        $query = ['pageSize' => $limit];
        if ($orderBy !== null) $query['orderBy'] = $orderBy . ($direction === 'ASCENDING' ? ' asc' : ' desc');
        $result = $this->request('GET', $this->collectionPath($collection) . '?' . http_build_query($query));
        $documents = [];
        foreach (($result['documents'] ?? []) as $snapshot) {
            $id = basename((string) ($snapshot['name'] ?? ''));
            if ($id !== '') $documents[] = ['id' => $id] + FirestoreValueCodec::decodeFields((array) ($snapshot['fields'] ?? []));
        }
        return $documents;
    }

    /** List documents whose selected field exactly matches a string value. */
    public function listByField(string $collection, string $field, string $value, int $limit = 100): array
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_.]{0,99}$/', $field)) throw new InvalidArgumentException('Invalid filter field.');
        $limit = max(1, min(100, $limit));
        $result = $this->request('POST', $this->apiRoot() . ':runQuery', [
            'structuredQuery' => [
                'from' => [['collectionId' => $this->collectionName($collection)]],
                'where' => ['fieldFilter' => [
                    'field' => ['fieldPath' => $field],
                    'op' => 'EQUAL',
                    'value' => ['stringValue' => $value],
                ]],
                'limit' => $limit,
            ],
        ]);
        $documents = [];
        foreach ($result as $row) {
            if (!is_array($row) || !isset($row['document'])) continue;
            $snapshot = $row['document'];
            $id = basename((string) ($snapshot['name'] ?? ''));
            if ($id !== '') $documents[] = ['id' => $id] + FirestoreValueCodec::decodeFields((array) ($snapshot['fields'] ?? []));
        }
        return $documents;
    }

    private function collectionPath(string $collection): string
    {
        return $this->apiRoot() . '/' . rawurlencode($this->collectionName($collection));
    }

    private function collectionName(string $collection): string
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,99}$/', $collection)) throw new InvalidArgumentException('Invalid Firestore collection name.');
        return $collection;
    }

    private function documentPath(string $collection, string $id): string
    {
        return $this->collectionPath($collection) . '/' . rawurlencode($this->documentId($id));
    }

    private function documentId(string $id): string
    {
        if ($id === '' || strlen($id) > 1500 || strpos($id, '/') !== false) throw new InvalidArgumentException('Invalid Firestore document ID.');
        return $id;
    }

    private function apiRoot(): string
    {
        return 'https://firestore.googleapis.com/v1/projects/' . rawurlencode($this->projectId) . '/databases/(default)/documents';
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        $handle = curl_init($path);
        if ($handle === false) throw new RuntimeException('Could not initialize the Firestore HTTP client.');
        $headers = ['Authorization: Bearer ' . $this->accessToken(), 'Accept: application/json'];
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $headers,
        ];
        if ($body !== null) {
            $encoded = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            $options[CURLOPT_POSTFIELDS] = $encoded;
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json; charset=utf-8';
        }
        curl_setopt_array($handle, $options);
        $response = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($handle);
        curl_close($handle);
        if ($response === false) throw new RuntimeException('Firestore request failed: ' . $curlError);
        if ($status >= 200 && $status < 300) {
            if ($response === '') return [];
            $decoded = json_decode($response, true);
            if (!is_array($decoded)) throw new RuntimeException('Firestore returned an invalid response.');
            return $decoded;
        }
        $payload = json_decode($response, true);
        $message = (string) ($payload['error']['message'] ?? 'Firestore request was rejected.');
        if ($status === 404) throw new FirestoreNotFoundException($message);
        if ($status === 409) throw new FirestoreConflictException($message);
        throw new RuntimeException('Firestore request failed with HTTP ' . $status . ': ' . $message);
    }

    private function accessToken(): string
    {
        $email = (string) ($this->credentials['client_email'] ?? '');
        $privateKey = (string) ($this->credentials['private_key'] ?? '');
        if ($email === '' || $privateKey === '') throw new RuntimeException('Firestore service-account credentials are incomplete.');
        $cacheKey = hash('sha256', $email . $this->projectId);
        if (isset(self::$tokens[$cacheKey]) && self::$tokens[$cacheKey]['expires'] > time() + 60) return self::$tokens[$cacheKey]['token'];

        $issuedAt = time();
        $encode = static fn (string $part): string => rtrim(strtr(base64_encode($part), '+/', '-_'), '=');
        $jwtHeader = ['alg' => 'RS256', 'typ' => 'JWT'];
        if (!empty($this->credentials['private_key_id'])) $jwtHeader['kid'] = (string) $this->credentials['private_key_id'];
        $header = $encode(json_encode($jwtHeader, JSON_THROW_ON_ERROR));
        $claims = $encode(json_encode([
            'iss' => $email,
            'scope' => 'https://www.googleapis.com/auth/datastore',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ], JSON_THROW_ON_ERROR));
        $unsigned = $header . '.' . $claims;
        $key = openssl_pkey_get_private($privateKey);
        if ($key === false || !openssl_sign($unsigned, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Could not sign a Firestore service-account assertion.');
        }
        $assertion = $unsigned . '.' . $encode($signature);
        $tokenBody = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
        ]);
        $handle = curl_init('https://oauth2.googleapis.com/token');
        if ($handle === false) throw new RuntimeException('Could not initialize Google OAuth.');
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $tokenBody,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($handle);
        curl_close($handle);
        if ($response === false) throw new RuntimeException('Google OAuth request failed: ' . $curlError);
        $decoded = json_decode($response, true);
        if ($status < 200 || $status >= 300 || empty($decoded['access_token'])) {
            throw new RuntimeException('Google OAuth rejected the service-account assertion.');
        }
        $expires = $issuedAt + max(60, (int) ($decoded['expires_in'] ?? 3600));
        self::$tokens[$cacheKey] = ['token' => (string) $decoded['access_token'], 'expires' => $expires];
        return self::$tokens[$cacheKey]['token'];
    }
}
