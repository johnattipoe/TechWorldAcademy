<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

use App\Controllers\CourseController\CourseController;
use App\Middleware\StudentMiddleware\StudentMiddleware;
use App\Models\LearningProgress\LearningProgress;
use App\Models\Lesson\Lesson;
use App\Models\Quiz\Quiz;
use App\Models\Assignment\Assignment;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use App\Services\LearningAccess\LearningAccess;

function lesson_content_view(string $content): array
{
    $html = '';
    $sections = [];
    $paragraph = [];
    $list = [];
    $code = [];

    $flushParagraph = static function () use (&$html, &$paragraph): void {
        if ($paragraph === []) return;
        $words = preg_split('/\s+/u', trim(implode(' ', $paragraph))) ?: [];
        $chunk = '';
        foreach ($words as $word) {
            if ($chunk !== '' && strlen($chunk) + strlen($word) + 1 > 300) {
                $html .= '<p>' . e($chunk) . '</p>';
                $chunk = '';
            }
            $chunk .= ($chunk === '' ? '' : ' ') . $word;
        }
        if ($chunk !== '') $html .= '<p>' . e($chunk) . '</p>';
        $paragraph = [];
    };
    $flushList = static function () use (&$html, &$list): void {
        if ($list === []) return;
        $items = [];
        foreach ($list as $item) {
            $words = preg_split('/\s+/u', trim($item)) ?: [];
            $piece = '';
            foreach ($words as $word) {
                if ($piece !== '' && strlen($piece) + strlen($word) + 1 > 280) {
                    $items[] = $piece;
                    $piece = '';
                }
                $piece .= ($piece === '' ? '' : ' ') . $word;
            }
            if ($piece !== '') $items[] = $piece;
        }
        $chunk = [];
        $chunkLength = 0;
        foreach ($items as $item) {
            if ($chunk !== [] && ($chunkLength + strlen($item) > 300 || count($chunk) >= 4)) {
                $html .= '<ul>';
                foreach ($chunk as $chunkItem) $html .= '<li>' . e($chunkItem) . '</li>';
                $html .= '</ul>';
                $chunk = [];
                $chunkLength = 0;
            }
            $chunk[] = $item;
            $chunkLength += strlen($item);
        }
        if ($chunk !== []) {
            $html .= '<ul>';
            foreach ($chunk as $chunkItem) $html .= '<li>' . e($chunkItem) . '</li>';
            $html .= '</ul>';
        }
        $list = [];
    };
    $flushCode = static function () use (&$html, &$code): void {
        if ($code === []) return;
        $chunk = [];
        $chunkLength = 0;
        foreach ($code as $line) {
            if ($chunk !== [] && $chunkLength + strlen($line) > 300) {
                $html .= '<pre><code>' . e(implode("\n", $chunk)) . '</code></pre>';
                $chunk = [];
                $chunkLength = 0;
            }
            $chunk[] = $line;
            $chunkLength += strlen($line);
        }
        if ($chunk !== []) $html .= '<pre><code>' . e(implode("\n", $chunk)) . '</code></pre>';
        $code = [];
    };

    foreach (preg_split('/\R/u', trim($content)) ?: [] as $rawLine) {
        $line = trim($rawLine);
        if ($line === '') {
            $flushParagraph();
            $flushList();
            $flushCode();
            continue;
        }

        if (preg_match('/^(?:#{1,3}\s*)?(Module\s+\d+\s*[:.]?.*|Lesson\s+\d+(?:\.\d+)*\s*[:.]?.*|\d+\.\s+[A-Z].*)$/i', $line, $matches)) {
            $flushParagraph();
            $flushList();
            $flushCode();
            $id = 'lesson-section-' . (count($sections) + 1);
            $title = preg_replace('/^#{1,3}\s*/', '', $matches[1]) ?? $matches[1];
            $sections[] = ['id' => $id, 'title' => $title];
            $html .= '<h2 id="' . e($id) . '">' . e($title) . '</h2>';
            continue;
        }

        if (preg_match('/^(Example|Practice task|Try it|Key takeaway|Reflection)\s*:?\s*$/i', $line)) {
            $flushParagraph();
            $flushList();
            $flushCode();
            $html .= '<h3>' . e(rtrim($line, ':')) . '</h3>';
            continue;
        }

        if (preg_match('/^(?:[-*•]|\d+[.)])\s+(.+)$/u', $line, $matches)) {
            $flushParagraph();
            $flushCode();
            $list[] = $matches[1];
            continue;
        }

        if (preg_match('/^(?:SELECT\b|FROM\b|WHERE\b|ORDER BY\b|GROUP BY\b|import\b|data\s*=|df\.|print\s*\(|=[A-Z]+\()/i', $line)) {
            $flushParagraph();
            $flushList();
            $code[] = $line;
            continue;
        }

        $flushCode();
        $flushList();
        $paragraph[] = $line;
    }

    $flushParagraph();
    $flushList();
    $flushCode();

    $blocks = [];
    preg_match_all('/<(h2|h3|p|ul|pre)\b[^>]*>.*?<\/\1>/si', $html, $blocks);
    $slides = [];
    $pageHtml = '';
    $pageSectionId = '';
    $pageTitle = 'Lesson introduction';
    $pageBodyLength = 0;
    $flushPage = static function () use (&$slides, &$pageHtml, &$pageSectionId, &$pageTitle, &$pageBodyLength): void {
        if ($pageHtml === '') return;
        $slides[] = [
            'sectionId' => $pageSectionId,
            'title' => $pageTitle,
            'html' => $pageHtml,
        ];
        $pageHtml = '';
        $pageBodyLength = 0;
    };

    foreach ($blocks[0] ?? [] as $block) {
        if (preg_match('/^<h2 id="([^"]+)">(.+)<\/h2>$/s', $block, $heading)) {
            $flushPage();
            $pageSectionId = $heading[1];
            $pageTitle = html_entity_decode(strip_tags($heading[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $pageHtml = $block;
            continue;
        }

        if ($pageBodyLength > 0 && $pageBodyLength + strlen($block) > 320) {
            $flushPage();
            $pageHtml = '<h3>Continued</h3>';
        }
        $pageHtml .= $block;
        $pageBodyLength += strlen($block);
    }
    $flushPage();
    if ($slides === []) {
        $slides[] = [
            'sectionId' => '',
            'title' => 'Lesson introduction',
            'html' => '<p>This lesson does not have any reading content yet.</p>',
        ];
    }

    foreach ($sections as &$section) {
        $section['pageIndex'] = 0;
        foreach ($slides as $index => $slide) {
            if ($slide['sectionId'] === $section['id']) {
                $section['pageIndex'] = $index;
                break;
            }
        }
    }
    unset($section);

    return ['html' => $html, 'sections' => $sections, 'slides' => $slides];
}

(new StudentMiddleware())->requireStudent();
start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$courseId = is_string($_REQUEST['course'] ?? null) ? $_REQUEST['course'] : '';
$lessonId = is_string($_REQUEST['lesson'] ?? null) ? $_REQUEST['lesson'] : '';
$course = $lesson = $quiz = $assignment = null;
$savedNote = '';
$notesUnavailable = false;
foreach ((new CourseController())->index() as $candidate) {
    if (($candidate['id'] ?? '') === $courseId) {
        $course = $candidate;
        break;
    }
}

$error = '';
$courseLessons = [];
$completedLessons = [];
try {
    if (!$course || !preg_match('/^[A-Za-z0-9_-]{1,150}$/', $lessonId)) {
        throw new RuntimeException('Lesson not found.');
    }

    $firestore = new FirestoreService(FirebaseService::firestore());
    $studentId = (string) $_SESSION['learning_user_id'];
    LearningAccess::assertApproved($firestore, $studentId, $courseId);
    $lesson = $firestore->get('academy_lessons', $lessonId);
    if (!$lesson || ($lesson['courseId'] ?? '') !== $courseId || empty($lesson['published'])) {
        throw new RuntimeException('Lesson not found.');
    }

    $noteId = hash('sha256', $studentId . ':' . $courseId . ':' . $lessonId);
    try {
        $noteRecord = $firestore->get('academy_lesson_notes', $noteId);
        if ($noteRecord && ($noteRecord['studentId'] ?? '') === $studentId
            && ($noteRecord['courseId'] ?? '') === $courseId
            && ($noteRecord['lessonId'] ?? '') === $lessonId) {
            $savedNote = (string) ($noteRecord['note'] ?? '');
        }
    } catch (Throwable $exception) {
        error_log('Learner lesson note could not be loaded: ' . $exception->getMessage());
        $notesUnavailable = true;
    }

    $courseLessons = Lesson::forCourse($firestore, $courseId, true);
    $progress = LearningProgress::forStudent($firestore, $studentId);
    foreach ($progress as $record) {
        if (($record['courseId'] ?? '') === $courseId && ($record['status'] ?? '') === 'completed') {
            $completedLessons[(string) ($record['lessonId'] ?? '')] = true;
        }
    }

    $quiz = Quiz::forLesson($firestore, $lessonId);
    $assignment = Assignment::forLesson($firestore, $lessonId);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
            throw new RuntimeException('Form expired. Refresh and try again.');
        }
        LearningProgress::complete($firestore, $studentId, $courseId, $lessonId, (string) $course['title'], (string) $lesson['title']);
        $_SESSION['learning_flash'] = ['success' => true, 'message' => 'Lesson marked complete.'];
        header('Location: /student/lesson/?course=' . rawurlencode($courseId) . '&lesson=' . rawurlencode($lessonId));
        exit;
    }
} catch (Throwable $exception) {
    http_response_code(403);
    $error = $exception->getMessage() === 'This course is not available to your account yet.'
        ? $exception->getMessage()
        : ($exception->getMessage() === 'Form expired. Refresh and try again.' ? $exception->getMessage() : 'This lesson is unavailable.');
}

$flash = $_SESSION['learning_flash'] ?? null;
unset($_SESSION['learning_flash']);
$sectionData = $lesson ? lesson_content_view((string) ($lesson['content'] ?? '')) : ['html' => '', 'sections' => [], 'slides' => []];
$lessonCount = count($courseLessons);
$completedCount = count($completedLessons);
$courseProgress = $lessonCount > 0 ? (int) round($completedCount * 100 / $lessonCount) : 0;
$currentIndex = null;
foreach ($courseLessons as $index => $courseLesson) {
    if (($courseLesson['id'] ?? '') === $lessonId) {
        $currentIndex = $index;
        break;
    }
}
$previousLesson = $currentIndex !== null && $currentIndex > 0 ? $courseLessons[$currentIndex - 1] : null;
$nextLesson = $currentIndex !== null && isset($courseLessons[$currentIndex + 1]) ? $courseLessons[$currentIndex + 1] : null;
$lessonCompleted = isset($completedLessons[$lessonId]);
$resumeKey = hash('sha256', (string) ($_SESSION['learning_user_id'] ?? '') . ':' . $courseId . ':' . $lessonId);

page_start($lesson ? (string) $lesson['title'] : 'Lesson', 'TechWorld Academy course lesson.');
?>
<?php if ($lesson): ?>
<div class="lesson-reading-progress" data-reading-progress role="progressbar" aria-label="Lesson reading progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
    <span data-reading-progress-bar></span>
</div>
<?php endif; ?>
<section class="lesson-workspace container" data-lesson-workspace<?= $lesson ? ' data-resume-key="' . e($resumeKey) . '"' : '' ?>>
    <a class="lesson-back-link" href="/student/learn/?course=<?= rawurlencode($courseId) ?>">
        <span aria-hidden="true">&larr;</span> Back to <?= e((string) ($course['title'] ?? 'course')) ?>
    </a>

    <?php if ($error !== ''): ?>
        <div class="learning-alert is-error" role="status"><?= e($error) ?></div>
    <?php elseif ($lesson): ?>
        <header class="lesson-hero">
            <div class="lesson-hero-copy">
                <span class="lesson-kicker"><span aria-hidden="true">✦</span> COURSE LESSON</span>
                <h1><?= e((string) $lesson['title']) ?></h1>
                <p><?= e((string) ($course['title'] ?? 'Your course')) ?> <span aria-hidden="true">·</span> Lesson <?= $currentIndex !== null ? $currentIndex + 1 : 1 ?><?= $lessonCount > 0 ? ' of ' . $lessonCount : '' ?></p>
            </div>
            <div class="lesson-progress-card">
                <div class="lesson-progress-card-heading"><span>Course progress</span><strong><?= $courseProgress ?>%</strong></div>
                <progress class="lesson-progress-native" aria-label="Course completion" value="<?= $courseProgress ?>" max="100"><?= $courseProgress ?>%</progress>
                <span><?= $completedCount ?> of <?= $lessonCount ?> lessons complete</span>
            </div>
            <div class="lesson-meta">
                <span><span aria-hidden="true">◷</span> <?= max(1, (int) ($lesson['durationMinutes'] ?? 1)) ?> min</span>
                <span><span aria-hidden="true">▤</span> Reading lesson</span>
                <?php if ($lessonCompleted): ?><span class="lesson-complete-badge"><span aria-hidden="true">✓</span> Completed</span><?php endif; ?>
            </div>
        </header>

        <section class="lesson-resume-prompt" data-resume-prompt aria-labelledby="lesson-resume-title" hidden>
            <span class="lesson-resume-mark" aria-hidden="true">↗</span>
            <div><strong id="lesson-resume-title">Welcome back to this lesson</strong><p data-resume-description>You have a saved reading position.</p></div>
            <button class="lesson-resume-continue" type="button" data-resume-continue>Resume reading</button>
            <button class="lesson-resume-dismiss" type="button" data-resume-dismiss aria-label="Dismiss resume prompt">Start at the beginning</button>
        </section>

        <?php if (is_array($flash) && !empty($flash['message'])): ?>
            <div class="learning-alert lesson-flash" role="status"><?= e((string) $flash['message']) ?></div>
        <?php endif; ?>

        <div class="lesson-reading-layout">
            <section id="lesson-notes" class="lesson-tools-card lesson-notes" data-lesson-notes data-endpoint="/student/lesson/features/notes/" data-course="<?= e($courseId) ?>" data-lesson="<?= e($lessonId) ?>" data-csrf-token="<?= e((string) $_SESSION['csrf_token']) ?>">
                <div class="lesson-tools-heading">
                    <span class="lesson-tools-icon lesson-notes-icon" aria-hidden="true">✎</span>
                    <div><strong>My lesson notes</strong><span>Private to your account</span></div>
                </div>
                <?php if ($notesUnavailable): ?><p class="lesson-notes-error" role="status">Saved notes are temporarily unavailable. Your lesson is still ready to read.</p><?php endif; ?>
                <label class="visually-hidden" for="lesson-notes-text">Your private notes for this lesson</label>
                <textarea id="lesson-notes-text" data-lesson-notes-input maxlength="5000" rows="7" placeholder="Capture an idea, question, or useful reminder…"><?= e($savedNote) ?></textarea>
                <div class="lesson-notes-footer">
                    <span data-notes-status role="status" aria-live="polite"><?= $notesUnavailable ? 'Could not load saved notes.' : 'Notes save automatically.' ?></span>
                    <button type="button" data-notes-save>Save note</button>
                </div>
            </section>

            <div class="lesson-main-column">
                <article class="learning-lesson lesson-article" data-lesson-article>
                    <button class="lesson-focus-exit" type="button" data-focus-mode-exit hidden>Exit focus mode</button>
                    <div class="lesson-viewer-stage">
                        <div class="lesson-viewer-document">
                            <div class="lesson-article-topline">
                                <span>YOUR LESSON</span>
                                <span class="lesson-player-compact-title"><?= e((string) $lesson['title']) ?></span>
                                <span data-slide-status aria-live="polite">Page 1 of <?= max(1, count($sectionData['slides'])) ?></span>
                            </div>
                            <div class="learning-lesson-content lesson-content" data-lesson-content>
                                <?php foreach ($sectionData['slides'] as $slideIndex => $slide): ?>
                                    <section class="lesson-slide" data-lesson-slide data-slide-index="<?= $slideIndex ?>" data-section-id="<?= e($slide['sectionId']) ?>"<?= $slideIndex === 0 ? '' : ' hidden' ?>>
                                        <?= $slide['html'] ?>
                                    </section>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <nav class="lesson-slide-navigation" aria-label="Lesson page navigation">
                        <button type="button" data-slide-previous disabled><span aria-hidden="true">&larr;</span> Back</button>
                        <span data-slide-count aria-live="polite">Page 1 of <?= count($sectionData['slides']) ?></span>
                        <button type="button" data-slide-next>Next <span aria-hidden="true">&rarr;</span></button>
                    </nav>
                    <div class="lesson-viewer-details">
                        <div>
                            <span class="lesson-viewer-type">READING</span>
                            <h2><?= e((string) $lesson['title']) ?></h2>
                        </div>
                        <a href="#lesson-notes-text" class="lesson-save-note-link"><span aria-hidden="true">✎</span> Save note</a>
                        <div class="lesson-mobile-tools">
                            <button type="button" data-player-panel="notes" aria-expanded="false">My notes</button>
                            <button type="button" data-player-panel="outline" aria-expanded="false">Sections</button>
                        </div>
                    </div>
                    <div class="lesson-completion-panel">
                        <?php if ($lessonCompleted): ?>
                            <span class="lesson-completed-message"><span aria-hidden="true">✓</span> You’ve completed this lesson</span>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="course" value="<?= e($courseId) ?>">
                                <input type="hidden" name="lesson" value="<?= e($lessonId) ?>">
                                <button class="button button-primary" type="submit">Mark lesson complete <span aria-hidden="true">&rarr;</span></button>
                            </form>
                        <?php endif; ?>
                        <span class="lesson-completion-hint">Your progress is saved to your learning profile.</span>
                        <nav class="lesson-course-links" aria-label="Previous and next course lesson">
                            <?php if ($previousLesson): ?>
                                <a href="/student/lesson/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode((string) $previousLesson['id']) ?>"><span aria-hidden="true">&larr;</span> Previous lesson</a>
                            <?php endif; ?>
                            <?php if ($nextLesson): ?>
                                <a href="/student/lesson/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode((string) $nextLesson['id']) ?>">Next lesson <span aria-hidden="true">&rarr;</span></a>
                            <?php elseif ($quiz): ?>
                                <a href="/student/quiz/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode($lessonId) ?>">Take lesson quiz <span aria-hidden="true">&rarr;</span></a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </article>
            </div>

            <aside class="lesson-tools" aria-label="Lesson tools">
                <section class="lesson-tools-card">
                    <div class="lesson-tools-heading">
                        <span class="lesson-tools-icon" aria-hidden="true">☷</span>
                        <div><strong>In this lesson</strong><span><?= count($sectionData['sections']) ?> sections</span></div>
                    </div>
                    <?php if ($sectionData['sections']): ?>
                        <nav class="lesson-outline" aria-label="Lesson sections">
                            <?php foreach ($sectionData['sections'] as $index => $section): ?>
                                <a href="#<?= e($section['id']) ?>" data-slide-target="<?= (int) $section['pageIndex'] ?>"><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e($section['title']) ?></a>
                            <?php endforeach; ?>
                        </nav>
                    <?php else: ?>
                        <p class="lesson-outline-empty">This lesson has no section headings.</p>
                    <?php endif; ?>
                </section>

                <section class="lesson-tools-card lesson-preferences">
                    <strong>Reading preferences</strong>
                    <div class="lesson-preference-row">
                        <span>Text size</span>
                        <div class="lesson-text-controls" aria-label="Text size controls">
                            <button type="button" data-text-size="decrease" aria-label="Decrease text size">A−</button>
                            <button type="button" data-text-size="increase" aria-label="Increase text size">A+</button>
                        </div>
                    </div>
                    <button class="lesson-tool-button" type="button" data-focus-mode aria-pressed="false">Enter focus mode <span aria-hidden="true">↗</span></button>
                    <button class="lesson-tool-button" type="button" data-copy-lesson>Copy lesson link <span aria-hidden="true">↗</span></button>
                    <span class="lesson-tool-status" data-lesson-status role="status" aria-live="polite"></span>
                </section>

            </aside>
        </div>

        <nav class="lesson-pagination" aria-label="Course lesson navigation">
            <div>
                <?php if ($previousLesson): ?>
                    <a href="/student/lesson/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode((string) $previousLesson['id']) ?>">
                        <span aria-hidden="true">&larr;</span><span><small>PREVIOUS LESSON</small><strong><?= e((string) $previousLesson['title']) ?></strong></span>
                    </a>
                <?php else: ?>
                    <a href="/student/learn/?course=<?= rawurlencode($courseId) ?>"><span aria-hidden="true">&larr;</span><span><small>COURSE OVERVIEW</small><strong>All lessons</strong></span></a>
                <?php endif; ?>
            </div>
            <div class="lesson-pagination-next">
                <?php if ($nextLesson): ?>
                    <a href="/student/lesson/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode((string) $nextLesson['id']) ?>">
                        <span><small>NEXT LESSON</small><strong><?= e((string) $nextLesson['title']) ?></strong></span><span aria-hidden="true">&rarr;</span>
                    </a>
                <?php elseif ($quiz): ?>
                    <a href="/student/quiz/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode($lessonId) ?>">
                        <span><small>UP NEXT</small><strong>Take lesson quiz</strong></span><span aria-hidden="true">&rarr;</span>
                    </a>
                <?php endif; ?>
            </div>
        </nav>

        <?php if ($quiz || $assignment): ?>
            <section class="lesson-extra-actions" aria-label="Lesson activities">
                <span>Keep practising</span>
                <?php if ($quiz): ?><a class="button button-outline" href="/student/quiz/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode($lessonId) ?>">Take lesson quiz <span aria-hidden="true">&rarr;</span></a><?php endif; ?>
                <?php if ($assignment): ?><a class="button button-outline" href="/student/assignment/?course=<?= rawurlencode($courseId) ?>&amp;lesson=<?= rawurlencode($lessonId) ?>">Open assignment <span aria-hidden="true">&rarr;</span></a><?php endif; ?>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</section>
<script src="/assets/js/lesson-experience.js" defer></script>
<script src="/assets/js/resume-reading.js" defer></script>
<script src="/assets/js/lesson-notes.js" defer></script>
<?php page_end(); ?>
