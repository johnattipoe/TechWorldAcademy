<?php
function render_learning_business(array $courses): void
{
$copy = page_copy('business');
    $publicSite = rtrim((string) env_value('PUBLIC_WEBSITE_URL', env_value('APP_URL', 'http://127.0.0.1:8000')), '/');
    $tracks = [
        ['eyebrow' => 'MAKE BETTER DECISIONS', 'title' => 'Data and analytics', 'description' => 'Build practical capability in spreadsheets, databases, data analysis and clear reporting.', 'query' => 'Data', 'mark' => '01'],
        ['eyebrow' => 'WORK SMARTER', 'title' => 'AI and automation', 'description' => 'Help teams understand AI tools, assess their uses and create useful, responsible workflows.', 'query' => 'AI', 'mark' => '02'],
        ['eyebrow' => 'BUILD WITH CONFIDENCE', 'title' => 'Software and cloud', 'description' => 'Develop foundations in programming, web technologies, cloud platforms and modern IT.', 'query' => 'Cloud', 'mark' => '03'],
        ['eyebrow' => 'PROTECT YOUR WORK', 'title' => 'Cybersecurity and IT', 'description' => 'Strengthen everyday digital awareness and grow practical skills in security and support.', 'query' => 'Cybersecurity', 'mark' => '04'],
    ];
    $benefits = [
        ['title' => 'A practical starting point', 'description' => 'Choose from technology courses with clear outcomes, guided lessons and opportunities to practise.'],
        ['title' => 'Flexible for busy teams', 'description' => 'Learners can work through online materials at a pace that fits around their schedules.'],
        ['title' => 'Progress you can follow', 'description' => 'Learner accounts bring course access, lesson activity and assessment progress together.'],
    ];
    $steps = [
        ['title' => 'Tell us what your team needs', 'description' => 'Share your goals, team size and the skills you want to build.'],
        ['title' => 'Choose a learning direction', 'description' => 'We can discuss relevant courses, learning paths and a practical starting point.'],
        ['title' => 'Get learners started', 'description' => 'Learners use individual accounts to access assigned learning and track their progress.'],
    ];
?>
<section class="learning-business-hero">
    <div class="container learning-business-hero-grid">
        <div class="learning-business-hero-copy">
            <span class="learning-home-eyebrow"><span aria-hidden="true"></span> <?= e($copy['eyebrow']) ?></span>
            <h1><?= e($copy['title']) ?> <span><?= e($copy['accent']) ?></span></h1>
            <p><?= e($copy['description']) ?></p>
            <div class="learning-home-actions"><a class="learning-home-primary" href="#business-tracks">Explore learning tracks <span aria-hidden="true">&#8594;</span></a><a class="learning-home-secondary" href="<?= e($publicSite) ?>/contact/?course=Team%20training" target="_blank" rel="noopener noreferrer">Talk to our team</a></div>
            <ul class="learning-business-points"><li>Technology skills for different roles</li><li>Flexible, self-paced course access</li><li>Course and learner progress in one place</li></ul>
        </div>
        <div class="learning-business-hero-visual" aria-hidden="true">
            <div class="learning-business-visual-top"><span>TEAM SKILLS</span><span>LEARNING PATH</span></div>
            <div class="learning-business-visual-title"><small>GROW TOGETHER</small><strong>Skills in action</strong></div>
            <div class="learning-business-visual-bars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
            <div class="learning-business-visual-bottom"><span>Learn</span><b aria-hidden="true">&#8594;</b><span>Practise</span><b aria-hidden="true">&#8594;</b><span>Progress</span></div>
            <span class="learning-business-orbit orbit-a"></span><span class="learning-business-orbit orbit-b"></span>
        </div>
    </div>
</section>

<section class="learning-business-value-strip" aria-label="Business learning benefits"><div class="container"><span>Role-relevant technology learning</span><i aria-hidden="true"></i><span>Guided lessons and practice</span><i aria-hidden="true"></i><span>Learner progress tracking</span><i aria-hidden="true"></i><span>Support from the Academy</span></div></section>

<section class="learning-business-benefits section container">
    <div class="learning-section-heading"><div><span class="learning-home-eyebrow">LEARNING THAT FITS YOUR TEAM</span><h2>Build capability with a clear plan.</h2><p>Start with the work your team does and the skills that will help them do it well.</p></div></div>
    <div class="learning-business-benefit-grid"><?php foreach ($benefits as $index => $benefit): ?><article><span><?= sprintf('%02d', $index + 1) ?></span><h3><?= e($benefit['title']) ?></h3><p><?= e($benefit['description']) ?></p></article><?php endforeach; ?></div>
</section>

<section class="learning-business-tracks" id="business-tracks"><div class="container">
    <div class="learning-section-heading"><div><span class="learning-home-eyebrow">SKILLS TRACKS</span><h2>Choose a focus for your team.</h2><p>Explore practical topics in the current TechWorld course catalogue.</p></div><a class="learning-business-all-courses" href="/#catalogue">Browse all courses <span aria-hidden="true">&#8594;</span></a></div>
    <div class="learning-business-track-grid"><?php foreach ($tracks as $track): ?><article><div class="learning-business-track-mark" aria-hidden="true"><?= e($track['mark']) ?></div><span><?= e($track['eyebrow']) ?></span><h3><?= e($track['title']) ?></h3><p><?= e($track['description']) ?></p><a href="/?q=<?= rawurlencode($track['query']) ?>#catalogue">Explore this track <span aria-hidden="true">&#8594;</span></a></article><?php endforeach; ?></div>
</div></section>

<section class="learning-business-catalogue section container">
    <div class="learning-section-heading"><div><span class="learning-home-eyebrow">CURRENT COURSE OFFERING</span><h2>Start with courses already available.</h2><p>Browse published courses and find a good first step for your team.</p></div></div>
    <?php if ($courses): ?><div class="learning-home-course-grid"><?php foreach (array_slice($courses, 0, 3) as $course): $courseId = (string) ($course['id'] ?? ''); ?><article class="learning-home-course-card"><div class="learning-course-art has-illustration <?= e((string) ($course['color'] ?? 'icon-blue')) ?>"><img src="<?= e(course_illustration_url((string) ($course['title'] ?? ''))) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async"><small>TECHWORLD COURSE</small></div><div class="learning-home-course-content"><span class="learning-course-category"><?= e((string) ($course['level'] ?? 'All levels')) ?> <i aria-hidden="true">&middot;</i> <?= e((string) ($course['duration'] ?? 'Flexible')) ?></span><?= course_card_badges($course) ?><h3><?= e((string) ($course['title'] ?? 'Technology course')) ?></h3><p><?= e((string) ($course['description'] ?? 'Build practical skills with guided learning.')) ?></p><a class="learning-course-link" href="/learn/course/?id=<?= rawurlencode($courseId) ?>">View course details <span aria-hidden="true">&#8594;</span></a></div></article><?php endforeach; ?></div><?php else: ?><p class="learning-business-empty">The course catalogue is being prepared. <a href="/#catalogue">Visit the learning homepage</a> or contact us to discuss team learning.</p><?php endif; ?>
</section>

<section class="learning-business-process"><div class="container"><div class="learning-section-heading"><div><span class="learning-home-eyebrow">HOW TO GET STARTED</span><h2>From team goals to a first course.</h2></div><p class="section-note">A simple conversation helps us point you toward a useful learning plan.</p></div><ol class="learning-business-steps"><?php foreach ($steps as $index => $step): ?><li><span><?= sprintf('%02d', $index + 1) ?></span><div><h3><?= e($step['title']) ?></h3><p><?= e($step['description']) ?></p></div></li><?php endforeach; ?></ol></div></section>

<section class="learning-business-cta"><div class="container"><div><span class="learning-home-eyebrow">LET'S TALK ABOUT YOUR GOALS</span><h2>What would your team like to learn?</h2><p>Tell us about your organization and the skills you want to build. The TechWorld team can help you explore the next step.</p></div><a class="learning-home-primary" href="<?= e($publicSite) ?>/contact/?course=Team%20training" target="_blank" rel="noopener noreferrer">Contact TechWorld <span aria-hidden="true">&#8599;</span></a></div></section>
<?php
}
?>
