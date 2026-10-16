<?php
declare(strict_types=1);

namespace App\Models\Enrollment;

use App\Services\FirestoreService\FirestoreConflictException;
use App\Services\FirestoreService\FirestoreService;
use DateTimeImmutable;
use DateTimeZone;

final class Enrollment
{
    public static function markPaid(FirestoreService $firestore, array $payment): void
    {
        $studentId = (string) ($payment['studentId'] ?? '');
        $courseId = (string) ($payment['courseId'] ?? '');
        if ($studentId === '' || $courseId === '') throw new \InvalidArgumentException('Payment is missing its learner or course reference.');
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $id = hash('sha256', $studentId . ':' . $courseId);
        $fields = [
            'studentId' => $studentId, 'studentName' => (string) ($payment['studentName'] ?? ''),
            'studentEmail' => mb_strtolower((string) ($payment['studentEmail'] ?? '')),
            'courseId' => $courseId, 'courseTitle' => (string) ($payment['courseTitle'] ?? 'Course'),
            'accessType' => 'paid', 'coursePrice' => ((int) ($payment['amountMinor'] ?? 0)) / 100,
            'currency' => (string) ($payment['currency'] ?? 'GHS'), 'status' => 'approved',
            'paymentStatus' => 'paid', 'paymentReference' => (string) ($payment['reference'] ?? ''),
            'requestedAt' => $now, 'approvedAt' => $now,
        ];
        try {
            $firestore->create('academy_enrollments', $id, $fields);
        } catch (FirestoreConflictException $exception) {
            $firestore->update('academy_enrollments', $id, $fields);
        }
    }

    public static function enrollFree(FirestoreService $firestore, string $studentId, string $studentName, string $studentEmail, array $course): array
    {
        $courseId = (string) ($course['id'] ?? '');
        if ($studentId === '' || $courseId === '') throw new \InvalidArgumentException('A valid learner and course are required.');
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $id = hash('sha256', $studentId . ':' . $courseId);
        $trialEndsAt = (string) ($course['accessType'] ?? 'free') === 'free_trial' ? $now->modify('+7 days') : null;
        try {
            $firestore->create('academy_enrollments', $id, [
                'studentId' => $studentId,
                'studentName' => $studentName,
                'studentEmail' => mb_strtolower($studentEmail),
                'courseId' => $courseId,
                'courseTitle' => (string) ($course['title'] ?? 'Course'),
                'status' => 'approved',
                'requestedAt' => $now,
                'approvedAt' => $now,
                'accessType' => (string) ($course['accessType'] ?? 'free'),
                'trialStartedAt' => $trialEndsAt ? $now : null,
                'trialEndsAt' => $trialEndsAt,
            ]);
            return ['created' => true, 'status' => 'approved'];
        } catch (FirestoreConflictException $exception) {
            $existing = $firestore->get('academy_enrollments', $id);
            $status = (string) ($existing['status'] ?? 'requested');
            if ($trialEndsAt && isset($existing['trialStartedAt'])) {
                $endsAt = $existing['trialEndsAt'] ?? null;
                if ($endsAt instanceof \DateTimeInterface && $endsAt <= $now) return ['created' => false, 'status' => 'trial_expired'];
                if ($status !== 'approved') {
                    $firestore->update('academy_enrollments', $id, ['status' => 'approved', 'approvedAt' => $now]);
                    return ['created' => true, 'status' => 'approved'];
                }
                return ['created' => false, 'status' => 'approved'];
            }
            if ($status !== 'approved') {
                $firestore->update('academy_enrollments', $id, [
                    'status' => 'approved',
                    'approvedAt' => $now,
                    'courseTitle' => (string) ($course['title'] ?? 'Course'),
                    'accessType' => (string) ($course['accessType'] ?? 'free'),
                    'trialStartedAt' => $trialEndsAt ? $now : null,
                    'trialEndsAt' => $trialEndsAt,
                ]);
                return ['created' => true, 'status' => 'approved'];
            }
            return ['created' => false, 'status' => 'approved'];
        }
    }

    public static function request(FirestoreService $firestore, string $studentId, string $studentName, string $studentEmail, array $course): array
    {
        $courseId = (string) ($course['id'] ?? '');
        if ($studentId === '' || $courseId === '') throw new \InvalidArgumentException('A valid learner and course are required.');
        $id = hash('sha256', $studentId . ':' . $courseId);
        try {
            $firestore->create('academy_enrollments', $id, [
                'studentId' => $studentId,
                'studentName' => $studentName,
                'studentEmail' => mb_strtolower($studentEmail),
                'courseId' => $courseId,
                'courseTitle' => (string) ($course['title'] ?? 'Course'),
                'accessType' => (string) ($course['accessType'] ?? 'free'),
                'coursePrice' => (float) ($course['price'] ?? 0),
                'currency' => (string) ($course['currency'] ?? 'GHS'),
                'status' => 'requested',
                'requestedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
            ]);
            return ['created' => true, 'status' => 'requested'];
        } catch (FirestoreConflictException $exception) {
            $existing = $firestore->get('academy_enrollments', $id);
            $status = (string) ($existing['status'] ?? 'requested');
            if ($status === 'rejected') {
                $firestore->update('academy_enrollments', $id, [
                    'status' => 'requested',
                    'requestedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
                    'reviewedAt' => null,
                    'accessType' => (string) ($course['accessType'] ?? 'free'),
                    'coursePrice' => (float) ($course['price'] ?? 0),
                    'currency' => (string) ($course['currency'] ?? 'GHS'),
                ]);
                return ['created' => true, 'status' => 'requested'];
            }
            return ['created' => false, 'status' => $status];
        }
    }

    public static function forStudent(FirestoreService $firestore, string $studentId): array
    {
        if ($studentId === '') throw new \InvalidArgumentException('A learner ID is required.');
        $records = $firestore->listByField('academy_enrollments', 'studentId', $studentId, 100);
        $byCourse = [];
        foreach ($records as $record) {
            $courseId = (string) ($record['courseId'] ?? '');
            if ($courseId !== '') $byCourse[$courseId] = $record;
        }
        return $byCourse;
    }
}
