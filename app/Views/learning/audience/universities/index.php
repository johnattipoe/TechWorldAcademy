<?php
function render_learning_universities(array $courses): void
{
$copy = page_copy('universities');
    $publicSite = rtrim((string) env_value('PUBLIC_WEBSITE_URL', env_value('APP_URL', 'http://127.0.0.1:8000')), '/');
    $supports = [
        ['number' => '01', 'title' => 'Complement classroom learning', 'description' => 'Use focused online courses to introduce practical technology topics alongside existing programs.'],
        ['number' => '02', 'title' => 'Give students room to practise', 'description' => 'Learners can review lessons, complete activities and build confidence by applying new concepts.'],
        ['number' => '03', 'title' => 'Follow learner progress', 'description' => 'Student accounts collect course activity, assessments and progress in one place.'],
    ];
    $experiences = [
        ['mark' => '01', 'eyebrow' => 'TECHNICAL FOUNDATIONS', 'title' => 'Programming and software', 'description' => 'Explore web development, Python, software engineering and mobile development topics.', 'query' => 'Programming'],
        ['mark' => '02', 'eyebrow' => 'DIGITAL FLUENCY', 'title' => 'Data, AI and automation', 'description' => 'Introduce data analysis, databases, artificial intelligence and practical automation.', 'query' => 'Data'],
        ['mark' => '03', 'eyebrow' => 'CAREER-READY PRACTICE', 'title' => 'Cybersecurity and cloud', 'description' => 'Build awareness of secure digital practice, cloud services, networking and IT support.', 'query' => 'Cybersecurity'],
    ];
    $steps = [
        ['title' => 'Share your learning goals', 'description' => 'Tell us about your institution, student groups and the skills you want learners to practise.'],
        ['title' => 'Review relevant courses', 'description' => 'Explore the current catalogue and discuss how courses may support your learning objectives.'],
        ['title' => 'Plan a starting point', 'description' => 'Agree on a focused first step for your students and how they will access the learner platform.'],
    ];
?>
<section class="learning-university-hero">
    <div class="container learning-business-hero-grid">
        <div class="learning-business-hero-copy">
            <span class="learning-home-eyebrow"><span aria-hidden="true"></span> <?= e($copy['eyebrow']) ?></span>
            <h1><?= e($copy['title']) ?> <span><?= e($copy['accent']) ?></span></h1>
            <p><?= e($copy['description']) ?></p>
            <div class="learning-home-actions"><a class="learning-home-primary" href="#university-learning">Explore learning options <span aria-hidden="true">&#8594;</span></a><a class="learning-home-secondary" href="<?= e($publicSite) ?>/contact/?course=University%20learning" target="_blank" rel="noopener noreferrer">Talk with TechWorld</a></div>
            <ul class="learning-business-points"><li>Practical online course content</li><li>Activities and assessments</li><li>Individual learner progress</li></ul>
        </div>
        <div class="learning-university-hero-visual" aria-hidden="true">
            <div class="learning-university-panel-top"><span>LEARNER WORKSPACE</span><span>COURSE 01 / 04</span></div>
            <div class="learning-university-panel-content"><span class="learning-university-icon">&lt;/&gt;</span><div><small>APPLIED TECHNOLOGY</small><strong>From concept<br>to practice</strong></div></div>
            <div class="learning-university-progress"><div><span>Learning progress</span><strong>Keep building</strong></div><div class="learning-university-progress-bar"><i></i></div></div>
            <div class="learning-university-panel-bottom"><span>LESSON</span><b>Guided learning</b><span aria-hidden="true">&#8594;</span></div>
        </div>
    </div>
</section>

<section class="learning-business-value-strip" aria-label="University learning support"><div class="container"><span>Online course catalogue</span><i aria-hidden="true"></i><span>Guided lessons</span><i aria-hidden="true"></i><span>Practice and assessments</span><i aria-hidden="true"></i><span>Student progress tracking</span></div></section>

<section class="learning-business-benefits section container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">SUPPORTING STUDENT LEARNING</span><h2>Practical skills to complement a degree.</h2><p>Explore ways to pair technical concepts with guided online learning and applied practice.</p></div></div><div class="learning-business-benefit-grid"><?php foreach ($supports as $item): ?><article><span><?= e($item['number']) ?></span><h3><?= e($item['title']) ?></h3><p><?= e($item['description']) ?></p></article><?php endforeach; ?></div></section>

<section class="learning-university-experiences" id="university-learning"><div class="container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">LEARNING EXPERIENCES</span><h2>Explore topics for your students.</h2><p>Use the available TechWorld courses to find a useful place to begin.</p></div><a class="learning-business-all-courses" href="/#catalogue">Browse all courses <span aria-hidden="true">&#8594;</span></a></div><div class="learning-business-track-grid"><?php foreach ($experiences as $item): ?><article><div class="learning-business-track-mark" aria-hidden="true"><?= e($item['mark']) ?></div><span><?= e($item['eyebrow']) ?></span><h3><?= e($item['title']) ?></h3><p><?= e($item['description']) ?></p><a href="/?q=<?= rawurlencode($item['query']) ?>#catalogue">Explore courses <span aria-hidden="true">&#8594;</span></a></article><?php endforeach; ?></div></div></section>

<section class="learning-business-catalogue section container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">CURRENT COURSE OFFERING</span><h2>Start with published courses.</h2><p>Review current course details before deciding what fits your program.</p></div></div><?php if ($courses): ?><div class="learning-home-course-grid"><?php foreach (array_slice($courses, 0, 3) as $course): $courseId = (string) ($course['id'] ?? ''); ?><article class="learning-home-course-card"><div class="learning-course-art has-illustration <?= e((string) ($course['color'] ?? 'icon-blue')) ?>"><img src="<?= e(course_illustration_url((string) ($course['title'] ?? ''))) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async"><small>TECHWORLD COURSE</small></div><div class="learning-home-course-content"><span class="learning-course-category"><?= e((string) ($course['level'] ?? 'All levels')) ?> <i aria-hidden="true">&middot;</i> <?= e((string) ($course['duration'] ?? 'Flexible')) ?></span><?= course_card_badges($course) ?><h3><?= e((string) ($course['title'] ?? 'Technology course')) ?></h3><p><?= e((string) ($course['description'] ?? 'Build practical skills with guided learning.')) ?></p><a class="learning-course-link" href="/learn/course/?id=<?= rawurlencode($courseId) ?>">View course details <span aria-hidden="true">&#8594;</span></a></div></article><?php endforeach; ?></div><?php else: ?><p class="learning-business-empty">The course catalogue is being prepared. <a href="/#catalogue">Visit the learning homepage</a> or contact TechWorld to discuss your program.</p><?php endif; ?></section>

<section class="learning-university-process"><div class="container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">A SIMPLE FIRST STEP</span><h2>Plan learning around your program.</h2></div><p class="section-note">Start with your students’ needs, then explore course fit together.</p></div><ol class="learning-business-steps"><?php foreach ($steps as $index => $step): ?><li><span><?= sprintf('%02d', $index + 1) ?></span><div><h3><?= e($step['title']) ?></h3><p><?= e($step['description']) ?></p></div></li><?php endforeach; ?></ol></div></section>

<section class="learning-university-cta"><div class="container"><div><span class="learning-home-eyebrow">FOR UNIVERSITY TEAMS</span><h2>Let’s discuss what your students are working toward.</h2><p>Contact TechWorld Academy to talk about course fit, learner access and practical technology skills.</p></div><a class="learning-home-primary" href="<?= e($publicSite) ?>/contact/?course=University%20learning" target="_blank" rel="noopener noreferrer">Contact TechWorld <span aria-hidden="true">&#8599;</span></a></div></section>
<?php
}
?>
