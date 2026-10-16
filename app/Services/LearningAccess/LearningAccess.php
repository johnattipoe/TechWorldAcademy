<?php
declare(strict_types=1);

namespace App\Services\LearningAccess;

use App\Models\Enrollment\Enrollment;
use App\Services\FirestoreService\FirestoreService;
use RuntimeException;

final class LearningAccess
{
    public static function assertApproved(FirestoreService $firestore, string $studentId, string $courseId): array
    {
        $record = $firestore->get('academy_enrollments', hash('sha256', $studentId . ':' . $courseId));
        return self::assertEnrollmentAccess($record);
    }

    /** Apply the same server-side access rules to every protected learning feature. */
    public static function assertEnrollmentAccess(?array $record): array
    {
        if (!$record || ($record['status'] ?? '') !== 'approved') throw new RuntimeException('This course is not available to your account yet.');
        if (($record['accessType'] ?? '') === 'paid' && ($record['paymentStatus'] ?? '') !== 'paid') {
            throw new RuntimeException('Payment is required before you can start this course.');
        }
        $trialEndsAt = $record['trialEndsAt'] ?? null;
        if (($record['accessType'] ?? '') === 'free_trial') {
            if (!$trialEndsAt instanceof \DateTimeInterface) throw new RuntimeException('The trial access window could not be verified. Contact the Academy.');
            if ($trialEndsAt <= new \DateTimeImmutable('now', new \DateTimeZone('UTC'))) {
                throw new RuntimeException('Your 7-day course trial has ended. Complete payment to continue learning.');
            }
        }
        return $record;
    }
}
