<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Middleware\AdminMiddleware\AdminMiddleware;
use App\Models\Course\Course;
use App\Models\NewsletterSubscriber\NewsletterSubscriber;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\RateLimitService\RateLimitService;

$middleware = new AdminMiddleware();
start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$passwordHash = env_value('ADMIN_PASSWORD_HASH');
$message = '';
$error = false;
$view = isset($_GET['view']) && $_GET['view'] === 'login' ? 'login' : 'dashboard';
$tab = isset($_GET['tab']) && in_array($_GET['tab'], ['subscribers', 'courses'], true) ? $_GET['tab'] : 'inquiries';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
        http_response_code(419);
        $message = 'Your form expired. Refresh and try again.';
        $error = true;
    } elseif (($_POST['action'] ?? '') === 'login') {
        $view = 'login';
        try {
            $limit = (new RateLimitService())->consume((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 'admin-login', 10, 3600);
        } catch (Throwable $exception) {
            $limit = ['allowed' => false, 'retry_after' => 3600];
        }
        if (!$limit['allowed']) {
            http_response_code(429);
            header('Retry-After: ' . (int) $limit['retry_after']);
            $message = 'Too many attempts. Wait a while before trying again.';
            $error = true;
        } elseif (!$passwordHash || !password_verify((string) ($_POST['password'] ?? ''), $passwordHash)) {
            $message = 'Sign-in failed. Check your password and try again.';
            $error = true;
        } else {
            session_regenerate_id(true);
            $_SESSION['admin_authenticated'] = true;
            $_SESSION['admin_authenticated_at'] = time();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: /admin/');
            exit;
        }
    } elseif (($_POST['action'] ?? '') === 'logout') {
        $middleware->logout();
        header('Location: /admin/?view=login');
        exit;
    } elseif ($middleware->authenticated()) {
        try {
            $firestore = new FirestoreService(FirebaseService::firestore());
            $action = (string) ($_POST['action'] ?? '');
            if ($action === 'close' && isset($_POST['inquiry_id'])) {
                $firestore->update('contact_inquiries', (string) $_POST['inquiry_id'], ['status' => 'closed', 'updatedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC'))]);
                $message = 'Enquiry marked as closed.';
            } elseif ($action === 'delete' && isset($_POST['inquiry_id'])) {
                $firestore->delete('contact_inquiries', (string) $_POST['inquiry_id']);
                $message = 'Enquiry deleted.';
            } elseif ($action === 'unsubscribe' && isset($_POST['subscriber_email'])) {
                NewsletterSubscriber::unsubscribe($firestore, (string) $_POST['subscriber_email']);
                $tab = 'subscribers';
                $message = 'Subscriber has been unsubscribed.';
            } elseif ($action === 'save-course') {
                [$course, $validationErrors] = Course::validate($_POST);
                if ($validationErrors) {
                    $tab = 'courses';
                    $message = reset($validationErrors);
                    $error = true;
                } elseif (!empty($_POST['course_id'])) {
                    $courseId = (string) $_POST['course_id'];
                    if (!$firestore->get('academy_courses', $courseId)) throw new RuntimeException('Course record was not found.');
                    $firestore->update('academy_courses', $courseId, $course);
                    $tab = 'courses';
                    $message = 'Course changes saved.';
                } else {
                    $firestore->add('academy_courses', $course);
                    $tab = 'courses';
                    $message = 'Course added to the catalogue.';
                }
            } elseif ($action === 'unpublish-course' && isset($_POST['course_id'])) {
                $firestore->update('academy_courses', (string) $_POST['course_id'], ['published' => false]);
                $tab = 'courses';
                $message = 'Course removed from the public catalogue.';
            }
        } catch (Throwable $exception) {
            error_log('Admin action failed: ' . $exception->getMessage());
            $message = 'That action could not be completed.';
            $error = true;
        }
    }
}

if (!$middleware->authenticated()) $view = 'login';
page_start('Academy admin');
?>
<section class="section container admin-page">
  <?php if ($view === 'login'): ?>
    <div class="admin-login"><span class="eyebrow">Staff access</span><h1>Academy admin</h1><p>Sign in to review course enquiries and newsletter subscriptions.</p>
      <?php if (!$passwordHash): ?><div class="admin-alert is-error">Admin access is not configured. Set ADMIN_PASSWORD_HASH in the private environment.</div><?php endif; ?>
      <?php if ($message !== ''): ?><div class="admin-alert <?= $error ? 'is-error' : 'is-success' ?>" role="status"><?= e($message) ?></div><?php endif; ?>
      <form method="post" action="/admin/?view=login"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="action" value="login"><label for="admin-password">Password</label><input id="admin-password" name="password" type="password" autocomplete="current-password" required><button class="button button-primary" type="submit" <?= !$passwordHash ? 'disabled' : '' ?>>Sign in</button></form>
    </div>
  <?php else: ?>
    <div class="admin-heading"><div><span class="eyebrow">Staff workspace</span><h1><?= $tab === 'subscribers' ? 'Newsletter subscribers' : ($tab === 'courses' ? 'Course catalogue' : 'Course enquiries') ?></h1><p><?= $tab === 'subscribers' ? 'Manage subscription consent and opt-out requests.' : ($tab === 'courses' ? 'Create courses and choose which are visible on the public site.' : 'Review messages sent through the public contact form.') ?></p></div><form method="post" action="/admin/"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="action" value="logout"><button class="button button-outline" type="submit">Sign out</button></form></div>
    <div class="admin-layout"><aside class="admin-sidebar"><?php require_once dirname(__DIR__, 2) . '/app/Views/layouts/sidebar/sidebar.php'; render_admin_sidebar($tab); ?></aside><div class="admin-content">
    <?php if ($message !== ''): ?><div class="admin-alert <?= $error ? 'is-error' : 'is-success' ?>" role="status"><?= e($message) ?></div><?php endif; ?>
    <?php if ($tab === 'courses'): ?>
      <?php try { $items = (new FirestoreService(FirebaseService::firestore()))->list('academy_courses', 100, 'title', 'ASCENDING'); } catch (Throwable $exception) { error_log('Admin could not load courses: ' . $exception->getMessage()); $items = []; ?><div class="admin-alert is-error">Courses are temporarily unavailable. Check Firebase setup and server logs.</div><?php } ?>
      <?php $editingCourse = null; if (isset($_GET['edit'])) foreach ($items as $candidate) if (($candidate['id'] ?? '') === (string) $_GET['edit']) $editingCourse = $candidate; ?>
      <form class="admin-course-form" method="post" action="/admin/?tab=courses<?= $editingCourse ? '&amp;edit=' . rawurlencode((string) $editingCourse['id']) : '' ?>"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="action" value="save-course"><?php if ($editingCourse): ?><input type="hidden" name="course_id" value="<?= e((string) $editingCourse['id']) ?>"><?php endif; ?><h2><?= $editingCourse ? 'Edit course' : 'Add a course' ?></h2><div class="admin-course-fields"><div><label for="course-title">Title</label><input id="course-title" name="title" maxlength="100" value="<?= e((string) ($editingCourse['title'] ?? '')) ?>" required></div><div><label for="course-duration">Duration</label><input id="course-duration" name="duration" maxlength="60" placeholder="8 weeks" value="<?= e((string) ($editingCourse['duration'] ?? '')) ?>" required></div><div><label for="course-level">Level</label><select id="course-level" name="level" required><?php foreach (['Beginner', 'Intermediate', 'Advanced', 'All levels'] as $level): ?><option value="<?= e($level) ?>" <?= ($editingCourse['level'] ?? '') === $level ? 'selected' : '' ?>><?= e($level) ?></option><?php endforeach; ?></select></div><div><label for="course-icon">Icon</label><input id="course-icon" name="icon" maxlength="12" value="<?= e((string) ($editingCourse['icon'] ?? '</>')) ?>" required></div><div><label for="course-color">Accent</label><select id="course-color" name="color"><?php foreach (['icon-blue' => 'Blue', 'icon-orange' => 'Orange', 'icon-green' => 'Green', 'icon-purple' => 'Purple'] as $color => $label): ?><option value="<?= e($color) ?>" <?= ($editingCourse['color'] ?? 'icon-blue') === $color ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div><div class="admin-course-description"><label for="course-description">Description</label><textarea id="course-description" name="description" rows="3" minlength="20" maxlength="1000" required><?= e((string) ($editingCourse['description'] ?? '')) ?></textarea></div></div><input type="hidden" name="published" value="0"><label class="admin-publish"><input type="checkbox" name="published" value="1" <?= !empty($editingCourse['published']) ? 'checked' : '' ?>> Publish on the public course page</label><button class="button button-primary" type="submit"><?= $editingCourse ? 'Save course changes' : 'Save course' ?></button><?php if ($editingCourse): ?> <a class="text-link" href="/admin/?tab=courses">Cancel</a><?php endif; ?></form>
      <?php if (!$items): ?><div class="admin-empty">No custom courses have been added. The public site is using its starter catalogue.</div><?php else: ?><div class="admin-inquiries"><?php foreach ($items as $item): ?><article class="admin-inquiry subscriber-row"><div><strong><?= e((string) ($item['title'] ?? 'Untitled course')) ?></strong><span class="admin-status <?= !empty($item['published']) ? '' : 'is-closed' ?>"><?= !empty($item['published']) ? 'published' : 'draft' ?></span></div><div class="course-admin-actions"><a class="button button-outline" href="/admin/?tab=courses&amp;edit=<?= rawurlencode((string) $item['id']) ?>">Edit</a><?php if (!empty($item['published'])): ?><form method="post" action="/admin/?tab=courses"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="course_id" value="<?= e((string) $item['id']) ?>"><input type="hidden" name="action" value="unpublish-course"><button class="button button-outline" type="submit">Unpublish</button></form><?php endif; ?></div></article><?php endforeach; ?></div><?php endif; ?>
    <?php elseif ($tab === 'subscribers'): ?>
      <?php try { $items = (new FirestoreService(FirebaseService::firestore()))->list('newsletter_subscribers', 100, 'consentedAt'); } catch (Throwable $exception) { error_log('Admin could not load subscribers: ' . $exception->getMessage()); $items = []; ?><div class="admin-alert is-error">Subscribers are temporarily unavailable. Check Firebase setup and server logs.</div><?php } ?>
      <?php if (!$items): ?><div class="admin-empty">No subscribers to show yet.</div><?php else: ?><div class="admin-inquiries"><?php foreach ($items as $item): ?><article class="admin-inquiry subscriber-row"><div><a href="mailto:<?= e((string) ($item['email'] ?? '')) ?>"><?= e((string) ($item['email'] ?? '')) ?></a><span class="admin-status <?= ($item['status'] ?? 'active') === 'unsubscribed' ? 'is-closed' : '' ?>"><?= e((string) ($item['status'] ?? 'active')) ?></span></div><?php if (($item['status'] ?? 'active') === 'active'): ?><form method="post" action="/admin/?tab=subscribers"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="subscriber_email" value="<?= e((string) $item['email']) ?>"><input type="hidden" name="action" value="unsubscribe"><button class="button button-outline" type="submit">Unsubscribe</button></form><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
    <?php else: ?>
      <?php try { $items = (new FirestoreService(FirebaseService::firestore()))->list('contact_inquiries', 100); } catch (Throwable $exception) { error_log('Admin could not load enquiries: ' . $exception->getMessage()); $items = []; ?><div class="admin-alert is-error">Enquiries are temporarily unavailable. Check Firebase setup and server logs.</div><?php } ?>
      <?php if (!$items): ?><div class="admin-empty">No enquiries to show yet.</div><?php else: ?><div class="admin-inquiries">
        <?php foreach ($items as $item): ?><article class="admin-inquiry"><div class="admin-inquiry-top"><div><h2><?= e((string) ($item['name'] ?? 'Unnamed')) ?></h2><a href="mailto:<?= e((string) ($item['email'] ?? '')) ?>"><?= e((string) ($item['email'] ?? '')) ?></a></div><span class="admin-status <?= ($item['status'] ?? 'new') === 'closed' ? 'is-closed' : '' ?>"><?= e((string) ($item['status'] ?? 'new')) ?></span></div><p><?= nl2br(e((string) ($item['message'] ?? ''))) ?></p><div class="admin-actions"><form method="post" action="/admin/"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="inquiry_id" value="<?= e((string) $item['id']) ?>"><input type="hidden" name="action" value="close"><button class="button button-outline" type="submit">Mark closed</button></form><form method="post" action="/admin/" data-confirm="Permanently delete this enquiry?"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="inquiry_id" value="<?= e((string) $item['id']) ?>"><input type="hidden" name="action" value="delete"><button class="button button-danger" type="submit">Delete</button></form></div></article><?php endforeach; ?>
      </div><?php endif; ?>
    <?php endif; ?>
    </div></div>
  <?php endif; ?>
</section>
<?php page_end(); ?>
