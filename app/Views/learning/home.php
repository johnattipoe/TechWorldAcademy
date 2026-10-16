<?php
function render_learning_home(array $courses, string $query = '', string $level = 'all', string $access = 'all', string $program = 'all', string $collection = 'all'): void
{
    $settings = site_settings();
    $query = trim($query);
    if (!in_array($level, ['all', 'beginner', 'intermediate', 'advanced'], true)) $level = 'all';
    if (!in_array($access, ['all', 'free', 'free_trial'], true)) $access = 'all';
    if (!in_array($program, ['all', 'course', 'certificate', 'degree_pathway'], true)) $program = 'all';
    if (!in_array($collection, ['all', 'trending', 'new', 'popular'], true)) $collection = 'all';
    $visibleCourses = array_values(array_filter($courses, static function (array $course) use ($query, $level, $access, $program, $collection): bool {
        $text = (string) ($course['title'] ?? '') . ' ' . (string) ($course['description'] ?? '') . ' ' . (string) ($course['level'] ?? '');
        $matchesQuery = $query === '' || stripos($text, $query) !== false;
        $courseLevel = strtolower((string) ($course['level'] ?? ''));
        $matchesLevel = $level === 'all' || $courseLevel === $level || ($level === 'beginner' && $courseLevel === 'all levels');
        $matchesAccess = $access === 'all' || ($course['accessType'] ?? 'free') === $access;
        $matchesProgram = $program === 'all' || ($course['programType'] ?? 'course') === $program;
        $matchesCollection = $collection === 'all' || !empty($course[$collection === 'new' ? 'isNew' : $collection]);
        return $matchesQuery && $matchesLevel && $matchesAccess && $matchesProgram && $matchesCollection;
    }));
    $topics = [
        ['label' => 'Artificial intelligence', 'query' => 'AI & Automation', 'icon' => 'ai'],
        ['label' => 'Web development', 'query' => 'Web', 'icon' => 'web-development'],
        ['label' => 'Data analytics', 'query' => 'Data Analytics', 'icon' => 'analytics'],
        ['label' => 'Cybersecurity', 'query' => 'Cybersecurity', 'icon' => 'cybersecurity'],
        ['label' => 'Cloud computing', 'query' => 'Cloud', 'icon' => 'cloud'],
        ['label' => 'Python', 'query' => 'Python', 'icon' => 'python'],
        ['label' => 'UI/UX design', 'query' => 'UI/UX', 'icon' => 'design'],
        ['label' => 'Software engineering', 'query' => 'Software Engineering', 'icon' => 'code-cpp'],
        ['label' => 'IT support', 'query' => 'IT Support', 'icon' => 'support'],
        ['label' => 'Networking', 'query' => 'Networking', 'icon' => 'globe'],
        ['label' => 'Databases and SQL', 'query' => 'Database', 'icon' => 'database'],
        ['label' => 'Mobile app development', 'query' => 'Mobile App', 'icon' => 'mobile'],
        ['label' => 'Digital marketing', 'query' => 'Digital Marketing', 'icon' => 'business'],
        ['label' => 'Product management', 'query' => 'Product Management', 'icon' => 'checklist'],
    ];
?>
<section class="learning-home-hero">
    <div class="container learning-home-hero-inner">
        <div class="learning-home-copy">
            <span class="learning-home-eyebrow"><span aria-hidden="true"></span> TECHWORLD LEARNING</span>
            <h1><?= e((string) $settings['learningHeroTitle']) ?><br><span><?= e((string) $settings['learningHeroAccent']) ?></span></h1>
            <p><?= e((string) $settings['learningHeroDescription']) ?></p>
            <div class="learning-home-actions"><a class="learning-home-primary" href="#catalogue">Explore courses <span aria-hidden="true">&#8594;</span></a><a class="learning-home-secondary" href="<?= !empty($_SESSION['learning_user_id']) ? '/student/' : '/register/' ?>"><?= !empty($_SESSION['learning_user_id']) ? 'Go to my learning' : 'Create a free account' ?></a></div>
        </div>
        <div class="learning-home-art" aria-hidden="true">
            <div class="learning-orbit learning-orbit-one"></div><div class="learning-orbit learning-orbit-two"></div>
            <div class="learning-art-center"><span>LEARN</span><strong>Make your<br>next move</strong><small>SKILLS · PRACTICE · PROGRESS</small></div>
            <div class="learning-art-chip chip-code">&lt;/&gt;</div><div class="learning-art-chip chip-data">01</div><div class="learning-art-chip chip-ai">✳</div>
        </div>
    </div>
</section>
<section class="learning-trust-strip" aria-label="Learning approach"><div class="container"><span>Practical, focused learning</span><i aria-hidden="true"></i><span>Learn at your own pace</span><i aria-hidden="true"></i><span>Track your progress</span><i aria-hidden="true"></i><span>Build real projects</span></div></section>
<section class="learning-home-section container" id="catalogue">
    <div class="learning-section-heading"><div><span class="learning-home-eyebrow">FIND YOUR NEXT SKILL</span><h2><?= $query !== '' ? 'Results for “' . e($query) . '”' : ($level !== 'all' ? 'Courses for ' . e(ucfirst($level)) . ' learners' : 'Explore courses') ?></h2><p>Choose a topic and start with a clear, guided learning path.</p></div><span class="learning-course-count"><?= count($visibleCourses) ?> <?= count($visibleCourses) === 1 ? 'course' : 'courses' ?></span></div>
    <h3 class="learning-topics-title">Explore by topic</h3><div class="learning-topic-list" aria-label="Popular topics">
        <?php foreach ($topics as $topic): ?><a href="/?q=<?= rawurlencode($topic['query']) ?>#catalogue"><?= tech_icon($topic['icon'], 'learning-topic-icon') ?><span><?= e($topic['label']) ?></span><span aria-hidden="true">&#8599;</span></a><?php endforeach; ?>
    </div>
    <nav class="learning-discovery-links" aria-label="Featured course collections"><a href="/?access=free#catalogue">Free courses</a><a href="/?access=free_trial#catalogue">Free trials</a><a href="/?program=degree_pathway#catalogue">Degree pathways</a><a href="/?collection=trending#catalogue">Trending now</a><a href="/?collection=new#catalogue">New courses</a></nav>
    <form class="learning-catalogue-filter" method="get" action="/#catalogue">
        <label for="learning-level-filter">Filter by level</label>
        <select id="learning-level-filter" name="level"><option value="all"<?= $level === 'all' ? ' selected' : '' ?>>All levels</option><option value="beginner"<?= $level === 'beginner' ? ' selected' : '' ?>>Beginner</option><option value="intermediate"<?= $level === 'intermediate' ? ' selected' : '' ?>>Intermediate</option><option value="advanced"<?= $level === 'advanced' ? ' selected' : '' ?>>Advanced</option></select>
        <label for="learning-access-filter">Access</label><select id="learning-access-filter" name="access"><option value="all"<?= $access === 'all' ? ' selected' : '' ?>>All access</option><option value="free"<?= $access === 'free' ? ' selected' : '' ?>>Free</option><option value="free_trial"<?= $access === 'free_trial' ? ' selected' : '' ?>>Free trial</option></select>
        <label for="learning-program-filter">Format</label><select id="learning-program-filter" name="program"><option value="all"<?= $program === 'all' ? ' selected' : '' ?>>All formats</option><option value="course"<?= $program === 'course' ? ' selected' : '' ?>>Courses</option><option value="certificate"<?= $program === 'certificate' ? ' selected' : '' ?>>Certificates</option><option value="degree_pathway"<?= $program === 'degree_pathway' ? ' selected' : '' ?>>Degree pathways</option></select>
        <label for="learning-collection-filter">Collection</label><select id="learning-collection-filter" name="collection"><option value="all"<?= $collection === 'all' ? ' selected' : '' ?>>All courses</option><option value="trending"<?= $collection === 'trending' ? ' selected' : '' ?>>Trending now</option><option value="popular"<?= $collection === 'popular' ? ' selected' : '' ?>>Popular</option><option value="new"<?= $collection === 'new' ? ' selected' : '' ?>>New</option></select>
        <?php if ($query !== ''): ?><input type="hidden" name="q" value="<?= e($query) ?>"><?php endif; ?>
        <button type="submit">Apply filter</button>
        <?php if ($level !== 'all' || $access !== 'all' || $program !== 'all' || $collection !== 'all' || $query !== ''): ?><a class="learning-filter-clear" href="/#catalogue">Clear</a><?php endif; ?>
    </form>
    <p class="learning-degree-note">Degree pathway labels describe a learning sequence and do not mean TechWorld Academy awards an accredited academic degree. Access, fees and trial terms are confirmed by the Academy before enrollment.</p>
    <?php if ($visibleCourses): ?>
    <div class="learning-home-course-grid">
        <?php foreach ($visibleCourses as $index => $course): ?>
        <?php $courseTitle = (string) ($course['title'] ?? 'Technology course'); $courseId = (string) ($course['id'] ?? ''); ?>
        <article class="learning-home-course-card">
            <div class="learning-course-art has-illustration <?= e((string) ($course['color'] ?? 'icon-blue')) ?>"><img src="<?= e(course_illustration_url($courseTitle)) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async"><small>TECHWORLD COURSE</small></div>
            <div class="learning-home-course-content"><span class="learning-course-category"><?= e((string) ($course['level'] ?? 'All levels')) ?> <i aria-hidden="true">·</i> <?= e((string) ($course['duration'] ?? 'Flexible')) ?></span><?= course_card_badges($course) ?><h3><?= e($courseTitle) ?></h3><p><?= e((string) ($course['description'] ?? 'Build practical skills with a guided learning path.')) ?></p>
                <a class="learning-course-link" href="/learn/course/?id=<?= rawurlencode($courseId) ?>">View course details <span aria-hidden="true">&#8594;</span></a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?><div class="learning-no-results"><h3><?= $query !== '' ? 'No courses found for “' . e($query) . '”' : 'No courses match this level yet.' ?></h3><p>Try changing the search or level filter.</p><a href="/#catalogue">Clear filters and view all courses</a></div><?php endif; ?>
</section>
<section class="learning-goal-section"><div class="container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">LEARN FOR YOUR NEXT STEP</span><h2>Choose a direction to explore.</h2><p>Start with a goal. You can build on your skills one course at a time.</p></div></div><div class="learning-goal-grid">
    <a href="/?q=Web#catalogue"><span>01 / CREATE</span><strong>Build websites and digital products</strong><small>Explore web development and programming.</small><b aria-hidden="true">&#8594;</b></a>
    <a href="/?q=Data#catalogue"><span>02 / DISCOVER</span><strong>Make better use of data</strong><small>Explore analytics, SQL and data visualisation.</small><b aria-hidden="true">&#8594;</b></a>
    <a href="/?q=Cybersecurity#catalogue"><span>03 / PROTECT</span><strong>Develop safer digital habits</strong><small>Build cybersecurity awareness and practical skills.</small><b aria-hidden="true">&#8594;</b></a>
    <a href="/?q=AI#catalogue"><span>04 / AUTOMATE</span><strong>Explore AI and automation</strong><small>Learn to evaluate tools and build useful workflows.</small><b aria-hidden="true">&#8594;</b></a>
</div></div></section>
<section class="learning-benefits-section"><div class="container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">HOW TECHWORLD LEARNING WORKS</span><h2>Learn, practise, see your progress.</h2></div></div><div class="learning-benefit-grid"><article><span class="learning-benefit-symbol"><?= tech_icon('book-open', 'learning-benefit-icon') ?></span><h3>Learn in clear steps</h3><p>Published lessons introduce a topic in a structured sequence.</p></article><article><span class="learning-benefit-symbol"><?= tech_icon('lesson', 'learning-benefit-icon') ?></span><h3>Put ideas into practice</h3><p>Courses can include quizzes and hands-on assignments to reinforce learning.</p></article><article><span class="learning-benefit-symbol"><?= tech_icon('progress', 'learning-benefit-icon') ?></span><h3>Keep track of progress</h3><p>Your learner area brings course requests, completed lessons and assessments together.</p></article></div></div></section>
<section class="learning-story-gallery"><div class="container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">YOUR LEARNING EXPERIENCE</span><h2>From the first lesson<br>to learning together.</h2><p>Explore guided learning, instructor support and collaborative practice.</p></div></div><div class="learning-story-grid">
    <figure><img src="/assets/images/student-learning.png" alt="Learner studying technology on a laptop" loading="lazy" decoding="async"><figcaption><strong>Study at your pace</strong><span>Build a regular learning habit.</span></figcaption></figure>
    <figure><img src="/assets/images/online-course-interface.png" alt="Online course screen with instruction and coding practice" loading="lazy" decoding="async"><figcaption><strong>Learn by doing</strong><span>Use lessons and guided exercises.</span></figcaption></figure>
    <figure><img src="/assets/images/instructor-dashboard.png" alt="Instructor working on course content" loading="lazy" decoding="async"><figcaption><strong>Get instructor support</strong><span>Follow structured lessons and feedback.</span></figcaption></figure>
    <figure><img src="/assets/images/community-collaboration.png" alt="Learners collaborating on a technology project" loading="lazy" decoding="async"><figcaption><strong>Build with others</strong><span>Share ideas as you grow your skills.</span></figcaption></figure>
</div></div></section>
<section class="learning-teams-banner"><div class="container"><div><span class="learning-home-eyebrow">FOR TEAMS AND ORGANISATIONS</span><h2>Build practical technology skills together.</h2><p>Ask TechWorld about tailored team training and learning support.</p></div><a href="<?= e(rtrim((string) env_value('PUBLIC_WEBSITE_URL', 'http://127.0.0.1:8000'), '/')) ?>/services/" target="_blank" rel="noopener noreferrer">Explore team training <span aria-hidden="true">&#8599;</span></a></div></section>
<section class="learning-home-faq container"><div class="learning-faq-intro"><span class="learning-home-eyebrow">A FEW QUICK ANSWERS</span><h2>Questions before you begin?</h2><p>See how course access and learner accounts work.</p><a href="<?= e(rtrim((string) env_value('PUBLIC_WEBSITE_URL', 'http://127.0.0.1:8000'), '/')) ?>/faq/">Visit the Academy FAQ <span aria-hidden="true">&#8594;</span></a></div><div class="learning-faq-list">
    <details><summary>How do I start a course?<span aria-hidden="true">+</span></summary><p>Create a learner account, choose a course and request a place. The Academy team reviews requests and updates your course status.</p></details>
    <details><summary>Can I browse before creating an account?<span aria-hidden="true">+</span></summary><p>Yes. You can search the catalogue and read course details before you sign in or register.</p></details>
    <details><summary>What happens after my request is approved?<span aria-hidden="true">+</span></summary><p>Published lessons and activities become available in your learner area. You can track your progress there as you work through them.</p></details>
    <details><summary>Can I earn a course certificate?<span aria-hidden="true">+</span></summary><p>Certificates are available for eligible courses after the required published lessons and assessments are completed. Your dashboard shows certificate progress.</p></details>
</div></section>
<section class="learning-home-pathways"><div class="container learning-pathways-inner"><div><span class="learning-home-eyebrow">MAKE LEARNING YOURS</span><h2>A small step today<br>can open a new path.</h2><p>Create an account to save your progress, request a place in a course and keep your work together.</p></div><a class="learning-home-primary" href="<?= !empty($_SESSION['learning_user_id']) ? '/student/' : '/register/' ?>"><?= !empty($_SESSION['learning_user_id']) ? 'Go to my learning' : 'Join for free' ?> <span aria-hidden="true">&#8594;</span></a></div></section>
<?php
}
?>
