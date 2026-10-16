<?php
declare(strict_types=1);

namespace App\Services\PublicFeatureService;

use App\Services\EmailService\EmailService;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\RateLimitService\RateLimitService;
use Throwable;

final class PublicFeatureService
{
    private const CONFIG = [
        'events' => ['academy_event_registrations', 'event registration'],
        'webinars' => ['academy_webinar_registrations', 'webinar registration'],
        'careers' => ['academy_job_applications', 'job application'],
        'scholarships' => ['academy_scholarship_applications', 'scholarship application'],
        'partners' => ['academy_partner_inquiries', 'partnership inquiry'],
        'accessibility' => ['academy_accessibility_reports', 'accessibility report'],
    ];
    private const REFERENCES = [
        'events' => ['web-build-day', 'portfolio-clinic', 'community-showcase'],
        'webinars' => ['starting-in-tech', 'practical-ai', 'career-portfolio'],
        'careers' => ['learning-coach', 'web-development-instructor', 'community-coordinator'],
        'scholarships' => ['access-scholarship', 'women-in-tech-award', 'career-transition-grant'],
        'partners' => ['partnership'],
        'accessibility' => ['accessibility-report'],
    ];

    public function submit(string $feature, array $input, array $session, string $ip): array
    {
        if (!isset(self::CONFIG[$feature])) return [404, 'This form is unavailable.'];
        if (!isset($input['csrf_token'], $session['csrf_token']) || !hash_equals((string) $session['csrf_token'], (string) $input['csrf_token'])) return [419, 'Your form expired. Refresh the page and try again.'];
        if (!empty($input['website'])) return [200, 'Thank you. Your submission has been received.'];
        if (($input['consent'] ?? '') !== '1') return [422, 'Please agree to the Privacy Policy before submitting.'];

        try {
            $limit = (new RateLimitService())->consume($ip, 'public-feature', 6, 3600);
            if (!$limit['allowed']) return [429, 'Too many submissions were sent recently. Please try again later.'];
            $name = trim(is_string($input['name'] ?? null) ? $input['name'] : '');
            $email = mb_strtolower(trim(is_string($input['email'] ?? null) ? $input['email'] : ''));
            $details = trim(is_string($input['details'] ?? null) ? $input['details'] : '');
            $reference = trim(is_string($input['reference'] ?? null) ? $input['reference'] : '');
            if ($name === '' || mb_strlen($name) > 100) return [422, 'Enter your name using no more than 100 characters.'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) return [422, 'Enter a valid email address.'];
            $detailsRequired = !in_array($feature, ['events', 'webinars'], true);
            if (($detailsRequired && ($details === '' || mb_strlen($details) < 10)) || mb_strlen($details) > 5000) return [422, 'Please provide details between 10 and 5,000 characters.'];
            if (!in_array($reference, self::REFERENCES[$feature], true)) return [422, 'Choose a valid event, role or program.'];
            if ($feature === 'careers' && ($input['profile_url'] ?? '') !== '') {
                $profileUrl = trim((string) $input['profile_url']);
                if (strlen($profileUrl) > 500 || filter_var($profileUrl, FILTER_VALIDATE_URL) === false || parse_url($profileUrl, PHP_URL_SCHEME) !== 'https') return [422, 'Use a valid HTTPS link for your portfolio or professional profile.'];
            } else $profileUrl = '';

            [$collection, $label] = self::CONFIG[$feature];
            $record = [
                'name' => $name,
                'email' => $email,
                'details' => $details,
                'reference' => $reference,
                'feature' => $feature,
                'status' => 'new',
                'privacyConsent' => true,
                'submittedAt' => new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            ];
            if ($profileUrl !== '') $record['profileUrl'] = $profileUrl;
            $id = (new FirestoreService(FirebaseService::firestore()))->add($collection, $record);
            $notification = "A new TechWorld Academy {$label} was received.\n\nName: {$name}\nEmail: {$email}\nReference: {$reference}\nRecord: {$id}\n\nDetails:\n{$details}";
            $recipient = (string) site_setting('contactEmail', env_value('CONTACT_EMAIL', 'hello@techworld.academy'));
            if (!(new EmailService())->send($recipient, 'New academy ' . $label, $notification, $email)) error_log('Public feature form saved but email notification could not be sent.');
            return [200, 'Thank you. Your submission has been received. The Academy team will follow up by email.'];
        } catch (Throwable $exception) {
            error_log('Public feature submission failed (' . $feature . '): ' . $exception->getMessage());
            return [503, 'We could not save your submission right now. Please try again later or contact the Academy team.'];
        }
    }
}
