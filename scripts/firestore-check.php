<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

try {
    $database = new \App\Services\FirestoreService\FirestoreService(\App\Services\FirebaseService\FirebaseService::firestore());
    $database->list('academy_courses', 1, 'title', 'ASCENDING');
    echo "Firestore REST access succeeded (one read-only catalogue request)." . PHP_EOL;
    exit(0);
} catch (Throwable $exception) {
    error_log('Firestore connection check failed: ' . $exception->getMessage());
    fwrite(STDERR, "Firestore REST access failed. Check the project ID, private service-account credentials, API enablement, IAM access, and network connectivity." . PHP_EOL);
    exit(1);
}
