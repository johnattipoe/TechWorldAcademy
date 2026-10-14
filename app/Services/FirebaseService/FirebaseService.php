<?php
declare(strict_types=1);

namespace App\Services\FirebaseService;

use RuntimeException;

final class FirebaseService
{
    public static function firestore(): array
    {
        $config = require dirname(__DIR__, 3) . '/config/firebase/firebase.php';
        $projectId = (string) ($config['project_id'] ?? '');
        $credentials = (string) ($config['credentials'] ?? '');
        if ($projectId === '' || $credentials === '') {
            throw new RuntimeException('Firebase is not configured. Set FIREBASE_PROJECT_ID and FIREBASE_CREDENTIALS.');
        }

        $credentialPath = $credentials;
        if (!preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $credentialPath)) {
            $credentialPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $credentialPath;
        }
        $realPath = realpath($credentialPath);
        $publicPath = realpath(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public');
        if ($realPath === false || !is_file($realPath) || ($publicPath && stripos($realPath, $publicPath . DIRECTORY_SEPARATOR) === 0)) {
            throw new RuntimeException('Firebase credentials must be a readable JSON file outside public/.');
        }
        $serviceAccount = json_decode((string) file_get_contents($realPath), true);
        if (!is_array($serviceAccount) || ($serviceAccount['project_id'] ?? '') !== $projectId
            || empty($serviceAccount['client_email']) || empty($serviceAccount['private_key'])) {
            throw new RuntimeException('Firebase service-account JSON is invalid or does not match FIREBASE_PROJECT_ID.');
        }

        return ['project_id' => $projectId, 'credentials' => $serviceAccount];
    }
}
