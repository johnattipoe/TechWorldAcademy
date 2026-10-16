<?php
function render_learning_government(array $courses): void
{
$copy = page_copy('government');
    $publicSite = rtrim((string) env_value('PUBLIC_WEBSITE_URL', env_value('APP_URL', 'http://127.0.0.1:8000')), '/');
    $audiences = [
        ['number' => '01', 'title' => 'Public sector teams', 'description' => 'Help staff build confidence with everyday digital tools, data and emerging technologies.'],
        ['number' => '02', 'title' => 'Workforce programs', 'description' => 'Offer guided technology learning as part of skills development and career transition support.'],
        ['number' => '03', 'title' => 'Community learners', 'description' => 'Create an accessible starting point for people developing foundational digital and technical skills.'],
    ];
    $tracks = [
        ['mark' => '01', 'eyebrow' => 'MAKE INFORMED DECISIONS', 'title' => 'Data and digital services', 'description' => 'Explore data analysis, databases, reporting and digital service fundamentals.', 'query' => 'Data'],
        ['mark' => '02', 'eyebrow' => 'BUILD DIGITAL RESILIENCE', 'title' => 'Cybersecurity and IT', 'description' => 'Develop security awareness and practical foundations in IT support and networking.', 'query' => 'Cybersecurity'],
        ['mark' => '03', 'eyebrow' => 'RESPONSIBLE INNOVATION', 'title' => 'AI and automation', 'description' => 'Build AI literacy, understand responsible use and explore helpful automation workflows.', 'query' => 'AI'],
        ['mark' => '04', 'eyebrow' => 'MODERNIZE SKILLS', 'title' => 'Software and cloud', 'description' => 'Introduce programming, cloud computing and software development concepts.', 'query' => 'Cloud'],
    ];
    $steps = [
        ['title' => 'Describe your goals', 'description' => 'Tell us which learner groups you serve and what technology skills they need.'],
        ['title' => 'Explore the catalogue', 'description' => 'Review existing courses and identify topics that align with your program.'],
        ['title' => 'Discuss a starting point', 'description' => 'Contact TechWorld to talk about learner access and an appropriate first learning step.'],
    ];
?>
<section class="learning-government-hero">
    <div class="container learning-business-hero-grid">
        <div class="learning-business-hero-copy">
            <span class="learning-home-eyebrow"><span aria-hidden="true"></span> <?= e($copy['eyebrow']) ?></span>
            <h1><?= e($copy['title']) ?> <span><?= e($copy['accent']) ?></span></h1>
            <p><?= e($copy['description']) ?></p>
            <div class="learning-home-actions"><a class="learning-home-primary" href="#government-tracks">Explore learning tracks <span aria-hidden="true">&#8594;</span></a><a class="learning-home-secondary" href="<?= e($publicSite) ?>/contact/?course=Government%20and%20public%20sector%20learning" target="_blank" rel="noopener noreferrer">Talk with TechWorld</a></div>
            <ul class="learning-business-points"><li>Digital and technology fundamentals</li><li>Guided courses with practice</li><li>Learning options for different groups</li></ul>
        </div>
        <div class="learning-government-visual" aria-hidden="true">
            <div class="learning-government-top"><span>PUBLIC SERVICE SKILLS</span><span>LEARNING PATH</span></div>
            <div class="learning-government-emblem"><span>&#9670;</span></div>
            <div class="learning-government-copy"><small>LEARN WITH PURPOSE</small><strong>Skills for a<br>digital future</strong></div>
            <div class="learning-government-bottom"><span>Digital confidence</span><i></i><span>Practical skills</span></div>
        </div>
    </div>
</section>

<section class="learning-business-value-strip" aria-label="Public sector learning benefits"><div class="container"><span>Flexible online courses</span><i aria-hidden="true"></i><span>Guided lessons and activities</span><i aria-hidden="true"></i><span>Technology skills pathways</span><i aria-hidden="true"></i><span>Learner progress tools</span></div></section>

<section class="learning-business-benefits section container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">LEARNING FOR PUBLIC NEEDS</span><h2>Support different learners and goals.</h2><p>Explore how practical technology learning can fit into public sector and workforce development programs.</p></div></div><div class="learning-business-benefit-grid"><?php foreach ($audiences as $item): ?><article><span><?= e($item['number']) ?></span><h3><?= e($item['title']) ?></h3><p><?= e($item['description']) ?></p></article><?php endforeach; ?></div></section>

<section class="learning-government-tracks" id="government-tracks"><div class="container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">SKILLS TRACKS</span><h2>Build useful digital foundations.</h2><p>Explore topics already represented in the TechWorld course catalogue.</p></div><a class="learning-business-all-courses" href="/#catalogue">Browse all courses <span aria-hidden="true">&#8594;</span></a></div><div class="learning-business-track-grid"><?php foreach ($tracks as $track): ?><article><div class="learning-business-track-mark" aria-hidden="true"><?= e($track['mark']) ?></div><span><?= e($track['eyebrow']) ?></span><h3><?= e($track['title']) ?></h3><p><?= e($track['description']) ?></p><a href="/?q=<?= rawurlencode($track['query']) ?>#catalogue">Explore courses <span aria-hidden="true">&#8594;</span></a></article><?php endforeach; ?></div></div></section>

<section class="learning-business-catalogue section container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">CURRENT COURSE OFFERING</span><h2>Review available courses.</h2><p>Browse published course details and consider what fits your learners’ needs.</p></div></div><?php if ($courses): ?><div class="learning-home-course-grid"><?php foreach (array_slice($courses, 0, 3) as $course): $courseId = (string) ($course['id'] ?? ''); ?><article class="learning-home-course-card"><div class="learning-course-art has-illustration <?= e((string) ($course['color'] ?? 'icon-blue')) ?>"><img src="<?= e(course_illustration_url((string) ($course['title'] ?? ''))) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async"><small>TECHWORLD COURSE</small></div><div class="learning-home-course-content"><span class="learning-course-category"><?= e((string) ($course['level'] ?? 'All levels')) ?> <i aria-hidden="true">&middot;</i> <?= e((string) ($course['duration'] ?? 'Flexible')) ?></span><?= course_card_badges($course) ?><h3><?= e((string) ($course['title'] ?? 'Technology course')) ?></h3><p><?= e((string) ($course['description'] ?? 'Build practical skills with guided learning.')) ?></p><a class="learning-course-link" href="/learn/course/?id=<?= rawurlencode($courseId) ?>">View course details <span aria-hidden="true">&#8594;</span></a></div></article><?php endforeach; ?></div><?php else: ?><p class="learning-business-empty">The course catalogue is being prepared. <a href="/#catalogue">Visit the learning homepage</a> or contact TechWorld to discuss your program.</p><?php endif; ?></section>

<section class="learning-government-process"><div class="container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">GETTING STARTED</span><h2>Begin with the people you serve.</h2></div><p class="section-note">A focused conversation can help identify a relevant first step.</p></div><ol class="learning-business-steps"><?php foreach ($steps as $index => $step): ?><li><span><?= sprintf('%02d', $index + 1) ?></span><div><h3><?= e($step['title']) ?></h3><p><?= e($step['description']) ?></p></div></li><?php endforeach; ?></ol></div></section>

<section class="learning-government-cta"><div class="container"><div><span class="learning-home-eyebrow">FOR PUBLIC SECTOR ORGANIZATIONS</span><h2>Let’s discuss your learning objectives.</h2><p>Tell TechWorld about your program, your learners and the skills you want them to develop.</p></div><a class="learning-home-primary" href="<?= e($publicSite) ?>/contact/?course=Government%20and%20public%20sector%20learning" target="_blank" rel="noopener noreferrer">Contact TechWorld <span aria-hidden="true">&#8599;</span></a></div></section>
<?php
}
?>
