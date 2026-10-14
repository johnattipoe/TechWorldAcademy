<?php
declare(strict_types=1);

namespace App\Controllers\ContactController;

use App\Models\ContactMessage\ContactMessage;
use App\Services\EmailService\EmailService;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\RateLimitService\RateLimitService;
use Throwable;

final class ContactController
{
    public function submit(array $input, array $session, string $ip): array
    {
        if (!isset($input['csrf_token'], $session['csrf_token']) || !hash_equals((string) $session['csrf_token'], (string) $input['csrf_token'])) {
            return [419, ['ok' => false, 'message' => 'Your form expired. Refresh the page and try again.']];
        }
        if (!empty($input['website'])) return [200, ['ok' => true, 'message' => 'Thanks for getting in touch. We’ll reply as soon as we can.']];

        try {
            $rate = (new RateLimitService())->consume($ip, 'contact', (int) env_value('CONTACT_RATE_LIMIT', '5'), (int) env_value('CONTACT_RATE_WINDOW', '3600'));
        } catch (Throwable $error) {
            error_log('Contact rate limiter unavailable: ' . $error->getMessage());
            return [503, ['ok' => false, 'message' => 'The form is temporarily unavailable. Please email us directly.']];
        }
        if (!$rate['allowed']) return [429, ['ok' => false, 'message' => 'You’ve sent several messages recently. Please wait a little and try again.'], $rate['retry_after']];

        [$data, $errors] = \validate_contact($input);
        if ($errors) return [422, ['ok' => false, 'message' => 'Please check the highlighted fields.', 'errors' => $errors]];

        try {
            ContactMessage::create(new FirestoreService(FirebaseService::firestore()), $data);
            $notification = "A new TechWorld Academy inquiry was received.\n\nName: {$data['name']}\nEmail: {$data['email']}\n\nMessage:\n{$data['message']}";
            try {
                $mailSent = (new EmailService())->send((string) env_value('CONTACT_EMAIL', 'hello@techworld.academy'), 'New academy contact inquiry', $notification, $data['email']);
                if (!$mailSent && env_value('MAIL_FROM')) error_log('Contact inquiry saved but email notification was not sent.');
            } catch (Throwable $mailError) {
                error_log('Contact inquiry saved but email notification failed: ' . $mailError->getMessage());
            }
            return [200, ['ok' => true, 'message' => 'Thanks for getting in touch. We’ll reply as soon as we can.']];
        } catch (Throwable $error) {
            error_log('Contact inquiry could not be saved: ' . $error->getMessage());
            return [503, ['ok' => false, 'message' => 'We couldn’t send your message just now. Please try again or email hello@techworld.academy.']];
        }
    }
}
