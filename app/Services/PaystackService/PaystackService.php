<?php
declare(strict_types=1);

namespace App\Services\PaystackService;

use RuntimeException;

final class PaystackService
{
    private string $secretKey;

    public function __construct()
    {
        $this->secretKey = trim((string) \env_value('PAYSTACK_SECRET_KEY', ''));
        if ($this->secretKey === '') throw new RuntimeException('Paystack is not configured. Add PAYSTACK_SECRET_KEY to the server environment.');
    }

    public function initialize(string $email, int $amountMinor, string $currency, string $reference, string $callbackUrl, array $metadata): array
    {
        return $this->request('POST', '/transaction/initialize', [
            'email' => $email,
            'amount' => (string) $amountMinor,
            'currency' => $currency,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'channels' => ['mobile_money'],
            'metadata' => $metadata,
        ]);
    }

    public function verify(string $reference): array
    {
        return $this->request('GET', '/transaction/verify/' . rawurlencode($reference));
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        $handle = curl_init('https://api.paystack.co' . $path);
        if ($handle === false) throw new RuntimeException('Could not initialize the Paystack client.');
        $headers = ['Authorization: Bearer ' . $this->secretKey, 'Accept: application/json'];
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $headers,
        ];
        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        }
        curl_setopt_array($handle, $options);
        $response = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);
        if ($response === false) throw new RuntimeException('Paystack request failed: ' . $error);
        $payload = json_decode($response, true);
        if ($status < 200 || $status >= 300 || !is_array($payload) || empty($payload['status']) || !is_array($payload['data'] ?? null)) {
            throw new RuntimeException('Paystack could not process this payment request.');
        }
        return $payload['data'];
    }
}
