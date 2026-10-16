<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/Services/NewsletterUnsubscribeService/NewsletterUnsubscribeService.php';
require_once dirname(__DIR__, 2) . '/app/Services/FirestoreService/FirestoreValueCodec/index.php';
require_once dirname(__DIR__, 2) . '/app/Validators/ContactValidator/ContactValidator.php';

return [
    'HTML output is escaped' => static function (): void {
        if (e('<script>alert(1)</script>') !== '&lt;script&gt;alert(1)&lt;/script&gt;') throw new RuntimeException('HTML escaping failed.');
    },
    'newsletter unsubscribe token is signed and verified' => static function (): void {
        putenv('NEWSLETTER_UNSUBSCRIBE_SECRET=' . bin2hex(random_bytes(32)));
        $id = hash('sha256', 'subscriber@example.com');
        $token = \App\Services\NewsletterUnsubscribeService\NewsletterUnsubscribeService::token($id);
        if ($token === null || !\App\Services\NewsletterUnsubscribeService\NewsletterUnsubscribeService::verify($id, $token)) throw new RuntimeException('Valid unsubscribe token failed verification.');
        if (\App\Services\NewsletterUnsubscribeService\NewsletterUnsubscribeService::verify($id, str_repeat('0', 64))) throw new RuntimeException('Invalid unsubscribe token was accepted.');
        putenv('NEWSLETTER_UNSUBSCRIBE_SECRET');
    },
    'Firestore REST values preserve timestamps, lists, maps and false values' => static function (): void {
        $input = ['enabled' => false, 'count' => 3, 'tags' => ['php', 'web'], 'empty' => [], 'meta' => ['source' => 'form']];
        $encoded = \App\Services\FirestoreService\FirestoreValueCodec::encodeFields($input);
        $decoded = \App\Services\FirestoreService\FirestoreValueCodec::decodeFields($encoded);
        if ($decoded !== $input) throw new RuntimeException('Firestore value encoding did not round trip.');
        $time = new DateTimeImmutable('2026-10-14T10:30:00+02:00');
        $roundTrip = \App\Services\FirestoreService\FirestoreValueCodec::decodeFields(
            \App\Services\FirestoreService\FirestoreValueCodec::encodeFields(['at' => $time])
        );
        if (!$roundTrip['at'] instanceof DateTimeImmutable || $roundTrip['at']->getTimestamp() !== $time->getTimestamp()) throw new RuntimeException('Firestore timestamp conversion failed.');
    },
    'contact controller rejects an invalid CSRF token before storage access' => static function (): void {
        $controller = new \App\Controllers\ContactController\ContactController();
        [$status] = $controller->submit(['csrf_token' => 'wrong'], ['csrf_token' => 'right'], '127.0.0.1');
        if ($status !== 419) throw new RuntimeException('Invalid CSRF token was not rejected.');
    },
];
