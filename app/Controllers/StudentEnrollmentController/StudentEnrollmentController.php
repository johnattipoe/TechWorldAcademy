<?php
declare(strict_types=1);

namespace App\Controllers\StudentEnrollmentController;

use App\Controllers\CourseController\CourseController;
use App\Models\Enrollment\Enrollment;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use Throwable;

final class StudentEnrollmentController
{
    public function request(array $input, array $session, string $ip): array
    {
        if (!isset($input['csrf_token'], $session['csrf_token'])
            || !hash_equals((string) $session['csrf_token'], (string) $input['csrf_token'])) {
            return [419, 'Your form expired. Refresh the page and try again.', false];
        }
        $studentId = $session['learning_user_id'] ?? null;
        if (!is_string($studentId) || $studentId === '' || ($session['learning_user_role'] ?? '') !== 'student') {
            return [403, 'Sign in with a learner account to request a course place.', false];
        }
        try {
            $limit = (new \App\Services\RateLimitService\RateLimitService())->consume($ip . ':' . $studentId, 'course-enrollment', 10, 3600);
        } catch (Throwable $exception) {
            error_log('Enrollment request rate limiter unavailable: ' . $exception->getMessage());
            return [503, 'Course requests are temporarily unavailable. Please try again later.', false];
        }
        if (!$limit['allowed']) return [429, 'Too many course requests. Please try again later.', false, $limit['retry_after']];

        $rawCourseId = $input['course_id'] ?? '';
        $courseId = is_string($rawCourseId) ? trim($rawCourseId) : '';
        if ($courseId === '' || strlen($courseId) > 1500) return [422, 'Choose a valid course from the catalogue.', false];
        $course = null;
        foreach ((new CourseController())->index() as $candidate) {
            if (isset($candidate['id']) && hash_equals((string) $candidate['id'], $courseId)) { $course = $candidate; break; }
        }
        if ($course === null) return [404, 'That course is not currently available.', false];

        try {
            $firestore = new FirestoreService(FirebaseService::firestore());
            $learnerName = (string) ($session['learning_user_name'] ?? '');
            $learnerEmail = (string) ($session['learning_user_email'] ?? '');
            $directAccess = in_array((string) ($course['accessType'] ?? 'free'), ['free', 'free_trial'], true);
            $request = $directAccess
                ? Enrollment::enrollFree($firestore, $studentId, $learnerName, $learnerEmail, $course)
                : Enrollment::request($firestore, $studentId, $learnerName, $learnerEmail, $course);
            if (($request['status'] ?? '') === 'trial_expired') return [403, 'Your 7-day trial has already been used and has ended. Payment is required to continue.', false];
            if ($directAccess && $request['created']) return [201, 'You are enrolled in ' . (string) $course['title'] . '. You can start learning now.', true];
            if ($directAccess && $request['status'] === 'approved') return [200, 'You are already enrolled in this course.', true];
            if (($course['accessType'] ?? '') === 'paid' && $request['created']) return [201, 'Your payment request for ' . (string) $course['title'] . ' was recorded. Payment must be verified before lessons can be opened.', true];
            if ($request['created']) return [201, 'Your request for ' . (string) $course['title'] . ' was sent. A staff member must approve it before course access is granted.', true];
            if ($request['status'] === 'approved') return [200, 'Your request for this course has already been approved.', true];
            return [200, 'You already have a request for this course. It is awaiting staff review.', true];
        } catch (Throwable $exception) {
            error_log('Enrollment request could not be saved: ' . $exception->getMessage());
            return [503, 'We could not save your course request right now. Please try again later.', false];
        }
    }
}
