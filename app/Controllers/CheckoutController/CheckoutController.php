<?php
declare(strict_types=1);

namespace App\Controllers\CheckoutController;

use App\Controllers\CourseController\CourseController;
use App\Models\Enrollment\Enrollment;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\PaystackService\PaystackService;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final class CheckoutController
{
    public function start(array $input, array $session): array
    {
        if (!isset($input['csrf_token'], $session['csrf_token']) || !hash_equals((string) $session['csrf_token'], (string) $input['csrf_token'])) {
            return [419, 'Your form expired. Refresh and try again.', null];
        }
        $studentId = (string) ($session['learning_user_id'] ?? '');
        if ($studentId === '' || ($session['learning_user_role'] ?? '') !== 'student') return [403, 'Sign in with a learner account to purchase a course.', null];
        try {
            $limit = (new \App\Services\RateLimitService\RateLimitService())->consume((string) ($session['learning_user_id'] ?? ''), 'course-checkout', 8, 3600);
        } catch (Throwable $exception) {
            error_log('Checkout rate limiter unavailable: ' . $exception->getMessage());
            return [503, 'Checkout is temporarily unavailable. Please try again later.', null];
        }
        if (!$limit['allowed']) return [429, 'Too many checkout attempts. Please try again later.', null];
        $rawId = $input['course_id'] ?? '';
        $courseId = is_string($rawId) ? trim($rawId) : '';
        $course = null;
        foreach ((new CourseController())->index() as $candidate) {
            if (isset($candidate['id']) && hash_equals((string) $candidate['id'], $courseId)) { $course = $candidate; break; }
        }
        if (!$course || !in_array(($course['accessType'] ?? ''), ['paid', 'free_trial'], true)) return [404, 'That paid course could not be found.', null];
        $amount = (float) ($course['price'] ?? 0);
        $currency = strtoupper((string) ($course['currency'] ?? 'GHS'));
        if ($amount <= 0 || !preg_match('/^[A-Z]{3}$/', $currency)) return [422, 'The Academy has not set a valid price for this course.', null];
        $amountMinor = (int) round($amount * 100);
        $reference = 'twa-' . bin2hex(random_bytes(18));
        try {
            $firestore = new FirestoreService(FirebaseService::firestore());
            $paystack = new PaystackService();
            $email = (string) ($session['learning_user_email'] ?? '');
            $name = (string) ($session['learning_user_name'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return [422, 'Add a valid email to your learner profile before checkout.', null];
            $firestore->create('academy_payments', $reference, [
                'reference' => $reference, 'studentId' => $studentId, 'studentEmail' => $email, 'studentName' => $name,
                'courseId' => $courseId, 'courseTitle' => (string) $course['title'], 'amountMinor' => $amountMinor,
                'currency' => $currency, 'status' => 'initializing', 'createdAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
            ]);
            $callbackUrl = \learning_platform_url('/checkout/callback/');
            $checkout = $paystack->initialize($email, $amountMinor, $currency, $reference, $callbackUrl, ['studentId' => $studentId, 'courseId' => $courseId]);
            $url = (string) ($checkout['authorization_url'] ?? '');
            $parts = parse_url($url);
            if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') !== 'checkout.paystack.com') throw new RuntimeException('Paystack returned an invalid checkout URL.');
            $firestore->update('academy_payments', $reference, ['status' => 'pending', 'checkoutUrl' => $url]);
            return [200, '', $url];
        } catch (Throwable $exception) {
            error_log('Course checkout could not start: ' . $exception->getMessage());
            return [503, 'Checkout is temporarily unavailable. Please try again later.', null];
        }
    }

    public function complete(string $reference): array
    {
        if (!preg_match('/^[A-Za-z0-9.=\-]{8,100}$/', $reference)) return [400, 'The payment reference is invalid.', false];
        try {
            $firestore = new FirestoreService(FirebaseService::firestore());
            $payment = $firestore->get('academy_payments', $reference);
            if (!$payment) return [404, 'Payment was not found.', false];
            if (($payment['status'] ?? '') === 'paid') {
                Enrollment::markPaid($firestore, $payment);
                return [200, 'Payment confirmed. Your course is ready.', true];
            }
            $verified = (new PaystackService())->verify($reference);
            if (($verified['status'] ?? '') !== 'success'
                || (string) ($verified['reference'] ?? '') !== $reference
                || (int) ($verified['amount'] ?? 0) !== (int) ($payment['amountMinor'] ?? -1)
                || strtoupper((string) ($verified['currency'] ?? '')) !== strtoupper((string) ($payment['currency'] ?? ''))
                || strcasecmp((string) ($verified['customer']['email'] ?? ''), (string) ($payment['studentEmail'] ?? '')) !== 0
                || (string) ($verified['channel'] ?? '') !== 'mobile_money') {
                $firestore->update('academy_payments', $reference, ['status' => 'verification_failed']);
                return [402, 'Payment could not be verified. If you were charged, contact the Academy with your payment reference.', false];
            }
            $firestore->update('academy_payments', $reference, [
                'status' => 'paid', 'paidAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
                'providerTransactionId' => (string) ($verified['id'] ?? ''), 'channel' => (string) ($verified['channel'] ?? ''),
            ]);
            Enrollment::markPaid($firestore, $payment);
            return [200, 'Payment confirmed. Your course is ready.', true];
        } catch (Throwable $exception) {
            error_log('Paystack payment could not be verified: ' . $exception->getMessage());
            return [503, 'Payment verification is temporarily unavailable. Your payment will be checked again when you return.', false];
        }
    }
}
