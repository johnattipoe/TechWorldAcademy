<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

return [
    'approved free enrollment grants learning access' => static function (): void {
        $record = \App\Services\LearningAccess\LearningAccess::assertEnrollmentAccess(['status' => 'approved', 'accessType' => 'free']);
        if (($record['status'] ?? '') !== 'approved') throw new RuntimeException('Approved free enrollment was rejected.');
    },
    'unpaid enrollment is denied' => static function (): void {
        try { \App\Services\LearningAccess\LearningAccess::assertEnrollmentAccess(['status' => 'approved', 'accessType' => 'paid', 'paymentStatus' => 'pending']); }
        catch (RuntimeException $exception) { if (!str_contains($exception->getMessage(), 'Payment')) throw $exception; return; }
        throw new RuntimeException('Unpaid enrollment was granted access.');
    },
    'active trial is allowed and expired trial is denied' => static function (): void {
        \App\Services\LearningAccess\LearningAccess::assertEnrollmentAccess(['status' => 'approved', 'accessType' => 'free_trial', 'trialEndsAt' => new DateTimeImmutable('+1 day')]);
        try { \App\Services\LearningAccess\LearningAccess::assertEnrollmentAccess(['status' => 'approved', 'accessType' => 'free_trial', 'trialEndsAt' => new DateTimeImmutable('-1 second')]); }
        catch (RuntimeException $exception) { if (!str_contains($exception->getMessage(), 'trial has ended')) throw $exception; return; }
        throw new RuntimeException('Expired trial was granted access.');
    },
    'trial without an expiry is denied' => static function (): void {
        try { \App\Services\LearningAccess\LearningAccess::assertEnrollmentAccess(['status' => 'approved', 'accessType' => 'free_trial']); }
        catch (RuntimeException $exception) { if (!str_contains($exception->getMessage(), 'could not be verified')) throw $exception; return; }
        throw new RuntimeException('Unverifiable trial was granted access.');
    },
];
