<?php
declare(strict_types=1);

namespace App\Models\ContactMessage;

use App\Services\FirestoreService\FirestoreService;
use DateTimeImmutable;
use DateTimeZone;

final class ContactMessage
{
    public static function create(FirestoreService $firestore, array $data): string
    {
        return $firestore->add('contact_inquiries', [
            'name' => $data['name'],
            'email' => mb_strtolower($data['email']),
            'message' => $data['message'],
            'status' => 'new',
            'createdAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
        ]);
    }
}
