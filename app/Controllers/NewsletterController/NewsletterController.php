<?php
declare(strict_types=1);

namespace App\Controllers\NewsletterController;

use App\Models\NewsletterSubscriber\NewsletterSubscriber;
use App\Services\EmailService\EmailService;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\NewsletterUnsubscribeService\NewsletterUnsubscribeService;
use App\Services\RateLimitService\RateLimitService;
use Throwable;

final class NewsletterController
{
    public function subscribe(array $input, array $session, string $ip): array
    {
        if (!isset($input['csrf_token'], $session['csrf_token']) || !hash_equals((string) $session['csrf_token'], (string) $input['csrf_token'])) {
            return [419, ['ok' => false, 'message' => 'Your form expired. Refresh the page and try again.']];
        }
        if (!empty($input['website'])) return [200, ['ok' => true, 'message' => 'Thanks. If the address is eligible, it has been added.']];

        try {
            $rate = (new RateLimitService())->consume($ip, 'newsletter', 5, 3600);
        } catch (Throwable $error) {
            error_log('Newsletter rate limiter unavailable: ' . $error->getMessage());
            return [503, ['ok' => false, 'message' => 'Subscription is temporarily unavailable. Please try again later.']];
        }
        if (!$rate['allowed']) return [429, ['ok' => false, 'message' => 'Please wait before trying to subscribe again.'], $rate['retry_after']];

        $email = trim((string) ($input['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            return [422, ['ok' => false, 'message' => 'Enter a valid email address.', 'errors' => ['email' => 'Enter a valid email address.']]];
        }
        if (($input['consent'] ?? '') !== '1') return [422, ['ok' => false, 'message' => 'Please confirm that you want to receive academy updates.']];

        try {
            $firestore = new FirestoreService(FirebaseService::firestore());
            $added = NewsletterSubscriber::subscribe($firestore, $email);
            if ($added) {
                $subscriberId = hash('sha256', mb_strtolower($email));
                $unsubscribeUrl = NewsletterUnsubscribeService::url($subscriberId);
                if ($unsubscribeUrl !== null) {
                    $welcome = "You are subscribed to occasional TechWorld Academy updates.\n\nTo unsubscribe at any time, confirm here:\n{$unsubscribeUrl}\n\nIf you did not sign up, you can ignore this message.";
                    try {
                        if (!(new EmailService())->send($email, 'TechWorld Academy updates', $welcome)) {
                            error_log('Newsletter subscription saved but welcome email was not sent.');
                        }
                    } catch (Throwable $mailError) {
                        error_log('Newsletter subscription saved but welcome email failed: ' . $mailError->getMessage());
                    }
                } else {
                    error_log('Newsletter unsubscribe link unavailable; set a strong NEWSLETTER_UNSUBSCRIBE_SECRET and APP_URL.');
                }
            }
            return [200, ['ok' => true, 'message' => 'Thanks. If the address is eligible, it has been added.']];
        } catch (Throwable $error) {
            error_log('Newsletter subscription could not be saved: ' . $error->getMessage());
            return [503, ['ok' => false, 'message' => 'Subscription is temporarily unavailable. Please try again later.']];
        }
    }
}
