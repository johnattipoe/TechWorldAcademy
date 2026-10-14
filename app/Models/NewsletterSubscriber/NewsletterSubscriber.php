<?php
declare(strict_types=1);

namespace App\Models\NewsletterSubscriber;

use App\Services\FirestoreService\FirestoreService;
use App\Services\FirestoreService\FirestoreConflictException;
use DateTimeImmutable;
use DateTimeZone;

final class NewsletterSubscriber
{
    /** Returns false when this email is already subscribed. */
    public static function subscribe(FirestoreService $firestore, string $email): bool
    {
        $email = mb_strtolower(trim($email));
        $id = hash('sha256', $email);
        try {
            $firestore->create('newsletter_subscribers', $id, [
                'email' => $email,
                'consentedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
                'status' => 'active',
            ]);
            return true;
        } catch (FirestoreConflictException $exception) {
            $existing = $firestore->get('newsletter_subscribers', $id);
            if (!$existing || ($existing['status'] ?? 'active') === 'active') return false;
            $firestore->update('newsletter_subscribers', $id, [
                'consentedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
                'status' => 'active',
            ]);
            return true;
        }
    }

    public static function unsubscribe(FirestoreService $firestore, string $email): void
    {
        $id = hash('sha256', mb_strtolower(trim($email)));
        self::unsubscribeById($firestore, $id);
    }

    public static function unsubscribeById(FirestoreService $firestore, string $id): void
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $id)) throw new \InvalidArgumentException('Invalid subscriber reference.');
        $firestore->update('newsletter_subscribers', $id, [
            'status' => 'unsubscribed',
            'unsubscribedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
        ]);
    }
}
