<?php
function render_home_view(array $home): void
{
$settings = site_settings();
?>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow"><span class="pulse"></span> Learn skills that move you forward</span>
            <h1><?= e((string) $settings['homeHeroTitle']) ?> <span><?= e((string) $settings['homeHeroAccent']) ?></span></h1>
            <p><?= e((string) $settings['homeHeroDescription']) ?></p>
            <div class="hero-actions"><a class="button button-primary" href="/courses/">Explore our courses <span aria-hidden="true">&#8594;</span></a><a class="text-link" href="/about/">Get to know us <span aria-hidden="true">&#8599;</span></a></div>
            <div class="hero-proof"><div class="proof-avatars"><span>TW</span><span>IT</span><span>AI</span></div><p><strong>Learn by doing.</strong><br>Skills for real-world work.</p></div>
        </div>
        <div class="hero-art" aria-label="Abstract illustration of connected technology blocks"><div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div><div class="art-core"><span>01</span><b>BUILD<br>WHAT'S<br>NEXT</b><small>TECHWORLD ACADEMY</small></div><div class="art-node node-one">&lt;/&gt;</div><div class="art-node node-two">&#10033;</div><div class="art-node node-three">&#8599;</div><div class="art-caption">Curiosity &rarr; capability</div></div>
    </div>
    <div class="container hero-bottom"><?php foreach ($home['promises'] as $promise): ?><span><?= e((string) $promise) ?></span><?php endforeach; ?></div>
</section>
<section class="section container">
    <div class="section-heading"><div><span class="eyebrow">What you can learn</span><h2>Skills for a world<br>that keeps changing.</h2></div><a class="text-link" href="/courses/">See all courses <span aria-hidden="true">&#8594;</span></a></div>
    <div class="course-grid"><?php foreach ($home['featuredCourses'] as $course): ?>
        <article class="course-card"><img class="course-card-art" src="<?= e(course_illustration_url((string) $course['title'])) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async"><div class="course-icon <?= e((string) $course['color']) ?>"><?= tech_icon(course_icon_key((string) $course['title']), 'course-icon-svg') ?></div><span class="tag">Technology course</span><h3><?= e((string) $course['title']) ?></h3><p><?= e((string) $course['description']) ?></p><div class="course-meta"><span><?= e((string) $course['level']) ?></span><span><?= e((string) $course['duration']) ?></span></div><a href="/contact/?course=<?= rawurlencode((string) $course['title']) ?>" class="card-link">Enquire about this course <span aria-hidden="true">&#8599;</span></a></article>
    <?php endforeach; ?></div>
</section>
<section class="section container site-learning-gallery">
    <div class="section-heading"><div><span class="eyebrow">Learning at TechWorld</span><h2>Learn with people<br>and projects in mind.</h2></div><a class="text-link" href="<?= e(learning_platform_url('/')) ?>" target="_blank" rel="noopener noreferrer">Visit the learning platform <span aria-hidden="true">&#8599;</span></a></div>
    <div class="site-learning-gallery-grid">
        <figure><img src="/assets/images/student-learning.png" alt="Learner practising technology skills on a laptop" loading="lazy" decoding="async"><figcaption><strong>Learn at your pace</strong><span>Build a steady learning routine.</span></figcaption></figure>
        <figure><img src="/assets/images/online-course-interface.png" alt="Online lesson interface with an instructor and coding practice" loading="lazy" decoding="async"><figcaption><strong>Follow guided lessons</strong><span>Connect clear explanations with practice.</span></figcaption></figure>
        <figure><img src="/assets/images/instructor-dashboard.png" alt="Instructor preparing a course at a laptop" loading="lazy" decoding="async"><figcaption><strong>Learn with support</strong><span>Get guidance as you work through a course.</span></figcaption></figure>
        <figure><img src="/assets/images/community-collaboration.png" alt="Learners collaborating around a laptop" loading="lazy" decoding="async"><figcaption><strong>Grow together</strong><span>Share ideas and build with others.</span></figcaption></figure>
    </div>
</section>
<section class="section learning-paths-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Choose a direction</span><h2>Start with what<br>you are curious about.</h2></div><p class="section-note">Explore a topic to see the courses that connect to it.</p></div>
        <div class="path-grid">
            <a class="path-card path-card-blue" href="/courses/?search=web"><span class="path-number">01 / BUILD</span><h3>Websites and apps</h3><p>Explore web development and the tools behind interactive products.</p><span class="path-card-link">See related courses <span aria-hidden="true">&#8594;</span></span></a>
            <a class="path-card path-card-orange" href="/courses/?search=data"><span class="path-number">02 / UNDERSTAND</span><h3>Data and decisions</h3><p>Build foundations in analytics, visualisation and working with information.</p><span class="path-card-link">See related courses <span aria-hidden="true">&#8594;</span></span></a>
            <a class="path-card path-card-green" href="/courses/?search=python"><span class="path-number">03 / AUTOMATE</span><h3>Code and AI</h3><p>Discover programming, useful automation and responsible AI workflows.</p><span class="path-card-link">See related courses <span aria-hidden="true">&#8594;</span></span></a>
            <a class="path-card path-card-purple" href="/courses/?search=security"><span class="path-number">04 / PROTECT</span><h3>Security and cloud</h3><p>Learn the concepts behind safer systems and modern cloud platforms.</p><span class="path-card-link">See related courses <span aria-hidden="true">&#8594;</span></span></a>
        </div>
    </div>
</section>
<section class="section learning-steps-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">A clear next step</span><h2>From curiosity<br>to confident practice.</h2></div><p class="section-note">Start with a conversation. We will share current details before you decide.</p></div>
        <ol class="learning-steps"><li><span>01</span><div><h3>Explore a topic</h3><p>Browse by subject, level or the kind of work you want to try.</p></div></li><li><span>02</span><div><h3>Ask what you need</h3><p>Enquire about fit, schedule, fees and current availability.</p></div></li><li><span>03</span><div><h3>Learn by doing</h3><p>Build understanding through guided practice and projects.</p></div></li></ol>
    </div>
</section>
<section class="band"><div class="container band-grid"><div><span class="eyebrow">A better way to learn</span><h2>Real skills. Real practice.<br>Your next chapter.</h2></div><p>Our courses pair clear instruction with hands-on projects, so you can apply what you learn as you go. Whether you are starting fresh or levelling up, there is a place for you here.</p><a class="button button-light" href="/about/">Why TechWorld <span aria-hidden="true">&#8594;</span></a></div></section>
<section class="section container">
    <div class="section-heading"><div><span class="eyebrow">Learning that connects</span><h2>Build more than<br>a list of lessons.</h2></div><p class="section-note">A good learning experience gives you ways to explore, practice and make your progress visible.</p></div>
    <div class="home-learning-grid">
        <article><span aria-hidden="true">01</span><h3>Start with a question</h3><p>Choose a subject that connects to your interests, your current work or a problem you want to solve.</p><a class="card-link" href="/resources/guides/">Plan your learning <span aria-hidden="true">&#8594;</span></a></article>
        <article><span aria-hidden="true">02</span><h3>Practice in small steps</h3><p>Use exercises and project briefs to turn ideas into skills you can repeat and explain.</p><a class="card-link" href="/resources/projects/">Try a project brief <span aria-hidden="true">&#8594;</span></a></article>
        <article><span aria-hidden="true">03</span><h3>Keep asking questions</h3><p>Check course details, compare options and get in touch when you need help choosing a starting point.</p><a class="card-link" href="/faq/">Read common questions <span aria-hidden="true">&#8594;</span></a></article>
    </div>
</section>
<section class="cta-section container"><div><span class="eyebrow">Your next step starts here</span><h2>Not sure where to begin?</h2><p>Tell us what you are aiming for. We will help you find a course that fits.</p></div><a class="button button-primary" href="/contact/">Talk to our team <span aria-hidden="true">&#8594;</span></a></section>
<?php
}
