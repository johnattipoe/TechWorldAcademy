<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$staffPortalUrl = learning_platform_url('/instructor/');
if (preg_match('#^https?://#i', $staffPortalUrl) === 1 && !is_learning_platform_host()) {
    header('Location: ' . $staffPortalUrl, true, 302);
    exit;
}

use App\Middleware\InstructorMiddleware\InstructorMiddleware;
use App\Models\AssignmentSubmission\AssignmentSubmission;
use App\Models\Lesson\Lesson;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;

(new InstructorMiddleware())->requireInstructor();
start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$firestore = new FirestoreService(FirebaseService::firestore());
$instructorId = (string) $_SESSION['learning_user_id'];
$requestedTab = is_string($_GET['tab'] ?? null) ? $_GET['tab'] : 'courses';
$tab = in_array($requestedTab, ['courses', 'submissions', 'students', 'profile', 'activity'], true) ? $requestedTab : 'courses';
$message = '';
$error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
        http_response_code(419);
        $message = 'Your form expired. Refresh and try again.';
        $error = true;
    } elseif (($_POST['action'] ?? '') === 'update-profile') {
        try {
            $newName = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
            if ($newName === '' || mb_strlen($newName) > 100) throw new RuntimeException('Enter your name using no more than 100 characters.');
            if (!preg_match('/^[a-f0-9]{64}$/', $instructorId)) throw new RuntimeException('Your session could not be verified. Please sign in again.');
            $firestore->update('academy_users', $instructorId, ['name' => $newName]);
            $_SESSION['learning_user_name'] = $newName;
            $message = 'Your profile name has been updated.';
            $tab = 'profile';
        } catch (Throwable $exception) {
            error_log('Instructor profile update failed: ' . $exception->getMessage());
            $message = in_array($exception->getMessage(), ['Enter your name using no more than 100 characters.', 'Your session could not be verified. Please sign in again.'], true) ? $exception->getMessage() : 'We could not save your profile just now. Please try again.';
            $error = true;
            $tab = 'profile';
        }
    } elseif (in_array(($_POST['action'] ?? ''), ['create-lesson', 'edit-lesson'], true)) {
        try {
            [$lessonData, $validationErrors] = Lesson::validate($_POST);
            if ($validationErrors) throw new RuntimeException((string) reset($validationErrors));
            $isLessonEdit = ($_POST['action'] ?? '') === 'edit-lesson';
            $lessonEditId = $isLessonEdit ? (is_string($_POST['lesson_id'] ?? null) ? $_POST['lesson_id'] : '') : null;
            if ($isLessonEdit && $lessonEditId === '') throw new RuntimeException('Invalid lesson ID.');
            $course = null;
            foreach ($firestore->list('academy_courses', 100, 'title', 'ASCENDING') as $candidateCourse) {
                if (($candidateCourse['instructorId'] ?? '') === $instructorId && in_array($lessonData['courseId'], [(string) ($candidateCourse['id'] ?? ''), (string) ($candidateCourse['catalogueId'] ?? '')], true)) { $course = $candidateCourse; break; }
            }
            if (!$course) throw new RuntimeException('You can only add lessons to courses assigned to you.');
            if ($isLessonEdit) {
                if (!preg_match('/^[A-Za-z0-9_-]{1,150}$/', $lessonEditId)) throw new RuntimeException('Invalid lesson ID.');
                $existingLesson = $firestore->get('academy_lessons', $lessonEditId);
                if (!$existingLesson || !in_array((string) ($existingLesson['courseId'] ?? ''), [(string) ($course['id'] ?? ''), (string) ($course['catalogueId'] ?? '')], true)) throw new RuntimeException('You can only edit lessons in courses assigned to you.');
            }
            $lessonData['courseId'] = (string) ($course['catalogueId'] ?? $lessonData['courseId']);
            $lessonData['courseTitle'] = (string) ($course['title'] ?? 'Course');
            Lesson::save($firestore, $lessonData, $lessonEditId);
            $message = $lessonEditId === null ? 'Lesson saved. It is now available to approved learners.' : 'Lesson changes saved.';
            $tab = 'courses';
        } catch (Throwable $exception) {
            error_log('Instructor lesson authoring failed: ' . $exception->getMessage());
            $message = in_array($exception->getMessage(), ['Choose a valid course.', 'Add a lesson title up to 150 characters.', 'Add lesson content up to 20,000 characters.', 'Duration must be 1 to 600 minutes.', 'Order must be between 1 and 999.', 'You can only add lessons to courses assigned to you.', 'You can only edit lessons in courses assigned to you.'], true) ? $exception->getMessage() : 'The lesson could not be saved. Check the fields and try again.';
            $error = true;
            $tab = 'courses';
        }
    } elseif (($_POST['action'] ?? '') === 'review-submission') {
        try {
            $submissionId = is_string($_POST['submission_id'] ?? null) ? $_POST['submission_id'] : '';
            $submission = $firestore->get('academy_assignment_submissions', $submissionId);
            if (!$submission) throw new RuntimeException('Submission not found.');
            $submissionCourseId = (string) ($submission['courseId'] ?? '');
            $course = null;
            foreach ($firestore->list('academy_courses', 100, 'title', 'ASCENDING') as $candidateCourse) {
                if (($candidateCourse['instructorId'] ?? '') === $instructorId && in_array($submissionCourseId, [(string) ($candidateCourse['id'] ?? ''), (string) ($candidateCourse['catalogueId'] ?? '')], true)) {
                    $course = $candidateCourse;
                    break;
                }
            }
            if (!$course) throw new RuntimeException('This submission is outside your assigned courses.');
            AssignmentSubmission::review($firestore, $submissionId, $_POST['score'] ?? null, $_POST['feedback'] ?? '');
            $tab = 'submissions';
            $message = 'Learner feedback saved.';
        } catch (Throwable $exception) {
            error_log('Instructor submission review failed: ' . $exception->getMessage());
            $message = 'The review could not be saved. Check the submission and try again.';
            $error = true;
            $tab = 'submissions';
        }
    }
}
$courses = [];
$submissions = [];
$students = [];
$courseLessonRecords = [];
$courseEnrollments = [];
$activityProgress = [];
$loadError = false;
try {
    $courses = array_values(array_filter($firestore->list('academy_courses', 100, 'title', 'ASCENDING'), static fn(array $course): bool => ($course['instructorId'] ?? '') === $instructorId));
    $courseIds = [];
    foreach ($courses as $course) foreach ([(string) ($course['id'] ?? ''), (string) ($course['catalogueId'] ?? '')] as $courseId) if ($courseId !== '') $courseIds[$courseId] = true;
    $submissions = array_values(array_filter($firestore->list('academy_assignment_submissions', 100, 'submittedAt'), static fn(array $submission): bool => isset($courseIds[(string) ($submission['courseId'] ?? '')])));
    if ($tab === 'courses' || $tab === 'activity') {
        $lessonRecordsById = [];
        foreach ($courses as $course) foreach (array_unique(array_filter([(string) ($course['id'] ?? ''), (string) ($course['catalogueId'] ?? '')])) as $lessonCourseId) {
            foreach ($firestore->listByField('academy_lessons', 'courseId', $lessonCourseId, 100) as $lessonRecord) $lessonRecordsById[(string) ($lessonRecord['id'] ?? '')] = $lessonRecord;
        }
        $courseLessonRecords = array_values($lessonRecordsById);
        $courseEnrollments = array_values(array_filter($firestore->list('academy_enrollments', 100, 'requestedAt'), static fn(array $enrollment): bool => isset($courseIds[(string) ($enrollment['courseId'] ?? '')])));
    }
    if ($tab === 'activity') $activityProgress = array_values(array_filter($firestore->list('academy_progress', 100, 'completedAt'), static fn(array $record): bool => isset($courseIds[(string) ($record['courseId'] ?? '')]) && ($record['status'] ?? '') === 'completed'));
    if ($tab === 'students') {
        $enrollments = $firestore->list('academy_enrollments', 100, 'requestedAt');
        $progressRecords = $firestore->list('academy_progress', 100, 'completedAt');
        $lessonRecords = $firestore->list('academy_lessons', 100, 'position', 'ASCENDING');
        $courseTitles = [];
        foreach ($courses as $course) foreach ([(string) ($course['id'] ?? ''), (string) ($course['catalogueId'] ?? '')] as $courseKey) if ($courseKey !== '') $courseTitles[$courseKey] = (string) ($course['title'] ?? 'Course');
        $courseLessonCounts = [];
        foreach ($lessonRecords as $lesson) {
            $lessonCourseId = (string) ($lesson['courseId'] ?? '');
            if (isset($courseTitles[$lessonCourseId]) && !empty($lesson['published'])) $courseLessonCounts[$lessonCourseId] = ($courseLessonCounts[$lessonCourseId] ?? 0) + 1;
        }
        $roster = [];
        foreach ($enrollments as $enrollment) {
            $enrollmentCourse = (string) ($enrollment['courseId'] ?? '');
            $studentId = (string) ($enrollment['studentId'] ?? '');
            if (($enrollment['status'] ?? '') !== 'approved' || !isset($courseTitles[$enrollmentCourse]) || $studentId === '') continue;
            if (!isset($roster[$studentId])) $roster[$studentId] = ['name' => (string) ($enrollment['studentName'] ?? 'Learner'), 'email' => (string) ($enrollment['studentEmail'] ?? ''), 'courses' => [], 'completed' => [], 'lessons' => 0];
            if (!isset($roster[$studentId]['courses'][$enrollmentCourse])) {
                $roster[$studentId]['courses'][$enrollmentCourse] = $courseTitles[$enrollmentCourse];
                $roster[$studentId]['lessons'] += $courseLessonCounts[$enrollmentCourse] ?? 0;
            }
        }
        foreach ($progressRecords as $record) {
            $studentId = (string) ($record['studentId'] ?? '');
            $courseId = (string) ($record['courseId'] ?? '');
            $lessonId = (string) ($record['lessonId'] ?? '');
            if (isset($roster[$studentId]['courses'][$courseId]) && $lessonId !== '' && ($record['status'] ?? '') === 'completed') $roster[$studentId]['completed'][$courseId . ':' . $lessonId] = true;
        }
        foreach ($roster as $studentId => $student) $students[] = ['id' => $studentId, 'name' => $student['name'], 'email' => $student['email'], 'courses' => array_values($student['courses']), 'completed' => count($student['completed']), 'lessons' => $student['lessons']];
        usort($students, static fn(array $a, array $b): int => strcasecmp($a['name'], $b['name']));
    }
} catch (Throwable $exception) {
    error_log('Instructor workspace could not load: ' . $exception->getMessage());
    $loadError = true;
}
require_once dirname(__DIR__, 2) . '/app/Views/staff/layouts/shell.php';
$instructorTitles = ['courses' => 'My Courses', 'submissions' => 'Assessment Reviews', 'students' => 'Students & Progress', 'profile' => 'Profile & Settings', 'activity' => 'Learning Activity'];
$notificationCount = count(array_filter($submissions, static fn(array $submission): bool => ($submission['status'] ?? '') !== 'reviewed'));
staff_page_start($instructorTitles[$tab], 'instructor', $tab, 'Manage your courses, learners and instructor account.', $notificationCount);
?>
<section class="section container instructor-workspace">
<?php if ($message !== ''): ?><div class="admin-alert <?= $error ? 'is-error' : 'is-success' ?>" role="status"><?= e($message) ?></div><?php endif; ?>
<?php if ($loadError): ?><div class="learning-alert is-error" role="status">Instructor data is temporarily unavailable. Please refresh in a moment.</div>
<?php elseif ($tab === 'courses'): ?>
<div class="instructor-summary-grid"><article><span>Assigned courses</span><strong><?= count($courses) ?></strong></article><article><span>Published courses</span><strong><?= count(array_filter($courses, static fn(array $course): bool => !empty($course['published']))) ?></strong></article><article><span>Submissions to review</span><strong><?= count(array_filter($submissions, static fn(array $item): bool => ($item['status'] ?? '') !== 'reviewed')) ?></strong></article><article><span>Reviewed submissions</span><strong><?= count(array_filter($submissions, static fn(array $item): bool => ($item['status'] ?? '') === 'reviewed')) ?></strong></article></div>
<?php if (!$courses): ?><div class="learning-empty"><h2>No courses assigned yet</h2><p>An academy administrator can assign courses to your instructor account. Your lessons and learner submissions will appear here.</p><a class="button button-outline" href="/contact/">Contact academy support</a></div><?php else: ?>
<?php $courseStats = []; foreach ($courses as $course) { $aliases = array_filter([(string) ($course['id'] ?? ''), (string) ($course['catalogueId'] ?? '')]); $courseLessons = array_values(array_filter($courseLessonRecords, static fn(array $row): bool => in_array((string) ($row['courseId'] ?? ''), $aliases, true))); $courseLearners = []; foreach ($courseEnrollments as $row) if (($row['status'] ?? '') === 'approved' && in_array((string) ($row['courseId'] ?? ''), $aliases, true) && !empty($row['studentId'])) $courseLearners[(string) $row['studentId']] = true; $courseSubmissionCount = count(array_filter($submissions, static fn(array $row): bool => in_array((string) ($row['courseId'] ?? ''), $aliases, true))); $courseStats[(string) $course['id']] = ['lessons' => $courseLessons, 'learners' => count($courseLearners), 'submissions' => $courseSubmissionCount]; } ?>
<div class="staff-chart-grid"><section class="staff-chart-card"><h2>Course enrollment comparison</h2><p>Approved learners in the latest 100 enrollment records.</p><div class="staff-horizontal-chart" role="list"><?php $maxLearners = max(1, ...array_column($courseStats, 'learners')); foreach ($courses as $course): $stats = $courseStats[(string) $course['id']]; ?><div class="staff-horizontal-row" role="listitem"><span><?= e((string) ($course['title'] ?? 'Course')) ?></span><div class="staff-horizontal-track"><i style="width:<?= max(2, (int) round(100 * $stats['learners'] / $maxLearners)) ?>%"></i></div><strong><?= (int) $stats['learners'] ?></strong></div><?php endforeach; ?></div></section><section class="staff-table-card"><h2>Course delivery snapshot</h2><p>Lessons, current learners and recent submissions.</p><div class="staff-data-table-wrap"><table class="staff-data-table"><thead><tr><th>Course</th><th>Learners</th><th>Lessons</th><th>Submissions</th><th>Status</th></tr></thead><tbody><?php foreach ($courses as $course): $stats = $courseStats[(string) $course['id']]; ?><tr><td><?= e((string) ($course['title'] ?? 'Course')) ?></td><td><?= (int) $stats['learners'] ?></td><td><?= count($stats['lessons']) ?></td><td><?= (int) $stats['submissions'] ?></td><td><span class="staff-table-status <?= !empty($course['published']) ? '' : 'is-pending' ?>"><?= !empty($course['published']) ? 'Published' : 'Draft' ?></span></td></tr><?php endforeach; ?></tbody></table></div></section></div>
<div class="instructor-course-grid" id="assigned-courses"><?php foreach ($courses as $course): $courseId = !empty($course['catalogueId']) ? (string) $course['catalogueId'] : (string) $course['id']; $courseSubmissions = array_values(array_filter($submissions, static fn(array $item): bool => in_array((string) ($item['courseId'] ?? ''), [(string) ($course['id'] ?? ''), $courseId], true))); $lessons = $courseStats[(string) $course['id']]['lessons']; usort($lessons, static fn(array $a, array $b): int => (int) ($a['position'] ?? 999) <=> (int) ($b['position'] ?? 999)); ?><article class="instructor-course-card"><div class="learning-course-art has-illustration <?= e((string) ($course['color'] ?? 'icon-blue')) ?>"><img src="<?= e(course_illustration_url((string) ($course['title'] ?? ''))) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async"><small><?= !empty($course['published']) ? 'PUBLISHED COURSE' : 'DRAFT COURSE' ?></small></div><div><span class="learning-course-category"><?= e((string) ($course['level'] ?? 'All levels')) ?> &middot; <?= e((string) ($course['duration'] ?? 'Flexible')) ?></span><h2><?= e((string) ($course['title'] ?? 'Course')) ?></h2><p><?= e((string) ($course['description'] ?? '')) ?></p><div class="instructor-course-stats"><span><?= count($lessons) ?> lessons</span><span><?= count($courseSubmissions) ?> learner submissions</span></div><details class="instructor-lesson-list"><summary>Manage lessons (<?= count($lessons) ?>)</summary><?php foreach ($lessons as $lesson): ?><details class="instructor-lesson-item"><summary><?= e((string) ($lesson['title'] ?? 'Lesson')) ?> <span><?= !empty($lesson['published']) ? 'Published' : 'Draft' ?></span></summary><form method="post"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><input type="hidden" name="action" value="edit-lesson"><input type="hidden" name="lesson_id" value="<?= e((string) $lesson['id']) ?>"><input type="hidden" name="course_id" value="<?= e($courseId) ?>"><label>Lesson title<input name="title" maxlength="150" value="<?= e((string) ($lesson['title'] ?? '')) ?>" required></label><label>Lesson content<textarea name="content" rows="6" maxlength="20000" required><?= e((string) ($lesson['content'] ?? '')) ?></textarea></label><label>Duration (minutes)<input type="number" name="duration_minutes" min="1" max="600" value="<?= (int) ($lesson['durationMinutes'] ?? 20) ?>" required></label><label>Lesson order<input type="number" name="position" min="1" max="999" value="<?= (int) ($lesson['position'] ?? 1) ?>" required></label><input type="hidden" name="published" value="0"><label class="staff-publish-toggle"><input type="checkbox" name="published" value="1" <?= !empty($lesson['published']) ? 'checked' : '' ?>> Publish lesson for enrolled learners</label><button class="button button-primary" type="submit">Save lesson</button></form></details><?php endforeach; ?></details><details class="instructor-author-lesson"><summary>Add a lesson</summary><form method="post"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><input type="hidden" name="action" value="create-lesson"><input type="hidden" name="course_id" value="<?= e($courseId) ?>"><label>Lesson title<input name="title" maxlength="150" required></label><label>Lesson content<textarea name="content" rows="6" maxlength="20000" required></textarea></label><label>Duration (minutes)<input type="number" name="duration_minutes" min="1" max="600" value="20" required></label><label>Lesson order<input type="number" name="position" min="1" max="999" value="<?= count($lessons) + 1 ?>" required></label><input type="hidden" name="published" value="1"><button class="button button-primary" type="submit">Save and publish lesson</button></form></details></div></article><?php endforeach; ?></div><?php endif; ?>
<?php elseif ($tab === 'activity'): ?>
<div class="staff-metrics"><article class="staff-metric-card"><span>Completed lessons</span><strong><?= count($activityProgress) ?></strong><small>Completion records for assigned courses</small></article><article class="staff-metric-card"><span>Active learners</span><strong><?= count(array_unique(array_filter(array_map(static fn(array $item): string => (string) ($item['studentId'] ?? ''), $activityProgress)))) ?></strong><small>Learners with a completion record</small></article><article class="staff-metric-card"><span>Approved enrollments</span><strong><?= count(array_filter($courseEnrollments, static fn(array $item): bool => ($item['status'] ?? '') === 'approved')) ?></strong><small>Latest 100 enrollment records</small></article><article class="staff-metric-card"><span>Submissions</span><strong><?= count($submissions) ?></strong><small>Latest 100 records for your courses</small></article></div>
<div class="staff-chart-grid"><section class="staff-table-card"><h2>Recent lesson completions</h2><p>Student progress for courses assigned to you.</p><div class="staff-data-table-wrap"><table class="staff-data-table"><thead><tr><th>Learner</th><th>Course</th><th>Lesson</th><th>Completed</th></tr></thead><tbody><?php if (!$activityProgress): ?><tr><td colspan="4" class="staff-table-empty">No lesson completions recorded yet.</td></tr><?php else: foreach (array_slice($activityProgress, 0, 12) as $item): $completedAt = $item['completedAt'] ?? null; ?><tr><td><?= e((string) ($item['studentName'] ?? 'Learner')) ?></td><td><?= e((string) ($item['courseTitle'] ?? 'Course')) ?></td><td><?= e((string) ($item['lessonTitle'] ?? 'Lesson')) ?></td><td><?= e($completedAt instanceof DateTimeInterface ? $completedAt->format('M j, Y') : '—') ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
<section class="staff-table-card"><h2>Recent assignment work</h2><p>Submissions and review status across your assigned courses.</p><div class="staff-data-table-wrap"><table class="staff-data-table"><thead><tr><th>Learner</th><th>Assignment</th><th>Course</th><th>Status</th></tr></thead><tbody><?php if (!$submissions): ?><tr><td colspan="4" class="staff-table-empty">No assignment submissions yet.</td></tr><?php else: foreach (array_slice($submissions, 0, 12) as $item): $statusValue = strtolower((string) ($item['status'] ?? 'submitted')); ?><tr><td><?= e((string) ($item['studentName'] ?? 'Learner')) ?></td><td><?= e((string) ($item['assignmentTitle'] ?? 'Assignment')) ?></td><td><?= e((string) ($item['courseTitle'] ?? 'Course')) ?></td><td><span class="staff-table-status <?= $statusValue === 'reviewed' ? '' : 'is-pending' ?>"><?= e($statusValue) ?></span></td></tr><?php endforeach; endif; ?></tbody></table></div></section></div>
<?php elseif ($tab === 'students'): ?>
  <?php if (!$students): ?><div class="learning-empty"><h2>No enrolled learners yet</h2><p>Learners with approved access to your assigned courses will appear here.</p></div><?php else: ?><div class="staff-roster-list"><?php foreach ($students as $student): ?><article class="staff-roster-card"><div class="staff-roster-avatar" aria-hidden="true"><?= e(strtoupper(mb_substr($student['name'], 0, 1))) ?></div><div class="staff-roster-person"><h2><?= e($student['name']) ?></h2><a href="mailto:<?= e($student['email']) ?>"><?= e($student['email']) ?></a></div><div class="staff-roster-courses"><strong><?= count($student['courses']) ?> course<?= count($student['courses']) === 1 ? '' : 's' ?></strong><span><?= e(implode(', ', $student['courses'])) ?></span></div><div class="staff-roster-progress"><strong><?= (int) $student['completed'] ?> / <?= (int) $student['lessons'] ?> lessons</strong><span>Completed across assigned courses</span></div></article><?php endforeach; ?></div><?php endif; ?>
<?php elseif ($tab === 'profile'): ?>
  <article class="staff-profile-settings"><span class="learning-home-eyebrow">INSTRUCTOR ACCOUNT</span><h2>Profile and settings</h2><p>Update the name shown to learners and academy administrators. Your sign-in email stays verified and cannot be changed here.</p><form method="post"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><input type="hidden" name="action" value="update-profile"><label for="instructor-profile-name">Display name</label><input id="instructor-profile-name" name="name" maxlength="100" autocomplete="name" value="<?= e((string) ($_SESSION['learning_user_name'] ?? 'Instructor')) ?>" required><label for="instructor-profile-email">Sign-in email</label><input id="instructor-profile-email" type="email" value="<?= e((string) ($_SESSION['learning_user_email'] ?? '')) ?>" readonly><button class="button button-primary" type="submit">Save profile</button></form><div class="staff-profile-security"><h3>Password and sign-in</h3><p>Use the secure password reset flow to update your password.</p><a class="button button-outline" href="/forgot-password/">Reset password</a></div></article>
<?php else: ?>
<?php if (!$submissions): ?><div class="learning-empty"><h2>No submissions yet</h2><p>Submissions from learners enrolled in your assigned courses will appear here.</p><a class="button button-outline" href="/instructor/">View my courses</a></div><?php else: ?><div class="instructor-submission-list"><?php foreach ($submissions as $submission): ?><article class="instructor-submission-card"><div class="instructor-submission-heading"><div><span class="eyebrow"><?= e((string) ($submission['courseTitle'] ?? 'Course')) ?> &middot; <?= e((string) ($submission['lessonTitle'] ?? 'Lesson')) ?></span><h2><?= e((string) ($submission['assignmentTitle'] ?? 'Assignment')) ?></h2><p><?= e((string) ($submission['studentName'] ?? 'Learner')) ?> &middot; <?= e((string) ($submission['status'] ?? 'submitted')) ?></p></div><?php if (isset($submission['submittedAt']) && $submission['submittedAt'] instanceof DateTimeInterface): ?><time><?= e($submission['submittedAt']->format('M j, Y')) ?></time><?php endif; ?></div><div class="learning-lesson-content"><?= nl2br(e((string) ($submission['response'] ?? ''))) ?></div><form class="admin-review-form" method="post" action="/instructor/?tab=submissions"><input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>"><input type="hidden" name="action" value="review-submission"><input type="hidden" name="submission_id" value="<?= e((string) $submission['id']) ?>"><label for="score-<?= e((string) $submission['id']) ?>">Score (0–100)</label><input id="score-<?= e((string) $submission['id']) ?>" type="number" name="score" min="0" max="100" value="<?= e((string) ($submission['score'] ?? 0)) ?>" required><label for="feedback-<?= e((string) $submission['id']) ?>">Feedback</label><textarea id="feedback-<?= e((string) $submission['id']) ?>" name="feedback" maxlength="2000" rows="3"><?= e((string) ($submission['feedback'] ?? '')) ?></textarea><button class="button button-primary" type="submit"><?= ($submission['status'] ?? '') === 'reviewed' ? 'Update review' : 'Save review' ?></button></form></article><?php endforeach; ?></div><?php endif; ?>
<?php endif; ?></section>
<?php staff_page_end(); ?>
