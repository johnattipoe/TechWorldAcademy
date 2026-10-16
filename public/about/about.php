<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
$copy = page_copy('about');
page_start($copy['label'], $copy['description']);
?>
<section class="page-hero"><div class="container"><span class="eyebrow"><?= e($copy['eyebrow']) ?></span><h1><?= e($copy['title']) ?><?php if ($copy['accent'] !== ''): ?><br><span><?= e($copy['accent']) ?></span><?php endif; ?></h1><p><?= e($copy['description']) ?></p><a class="button button-primary" href="/courses/">Explore courses <span aria-hidden="true">&#8594;</span></a></div></section>
<section class="section container two-column"><div><span class="eyebrow">Why we are here</span><h2>Confidence comes<br>from doing.</h2></div><div class="prose"><p>The digital world is changing quickly. We believe access to useful technology skills should feel possible, whatever your starting point.</p><p>TechWorld Academy brings together practical lessons, hands-on projects and thoughtful guidance. Our goal is to help learners build skills they can use, explain and keep developing.</p><p>We are building a community where curiosity is welcome and progress matters more than perfection.</p></div></section>
<section class="values-band"><div class="container value-grid"><article><span>01</span><h3>Practical first</h3><p>Learn through projects connected to real tasks and real decisions.</p></article><article><span>02</span><h3>People centered</h3><p>Clear teaching and supportive guidance at every stage.</p></article><article><span>03</span><h3>Always curious</h3><p>Stay open, keep learning and make room for new ideas.</p></article></div></section>
<section class="section container"><div class="section-heading"><div><span class="eyebrow">A clear learning journey</span><h2>Small steps make progress visible.</h2></div></div><div class="value-grid"><article class="journey-card"><span>01</span><h3>Choose a direction</h3><p>Explore topics and tell us what you want to achieve.</p></article><article class="journey-card"><span>02</span><h3>Practice with purpose</h3><p>Build understanding through guided exercises and projects.</p></article><article class="journey-card"><span>03</span><h3>Keep growing</h3><p>Use what you learn as a foundation for your next challenge.</p></article></div></section>
<section class="section learning-paths-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">How we approach learning</span><h2>Make the process<br>clear and useful.</h2></div><p class="section-note">Technology can feel complex. A structured path and a chance to practice make the next step easier to see.</p></div>
        <div class="value-grid about-principles">
            <article><span>01 / CLARITY</span><h3>Explain the why</h3><p>Connect new terms and tools to the problem they help solve, then make space to ask questions.</p></article>
            <article><span>02 / PRACTICE</span><h3>Learn by making</h3><p>Use focused exercises and projects to apply ideas, discover mistakes and improve your work.</p></article>
            <article><span>03 / REFLECTION</span><h3>Notice your progress</h3><p>Review what you can do now, what still needs practice and where you might go next.</p></article>
        </div>
    </div>
</section>
<section class="section container two-column">
    <div><span class="eyebrow">An open starting point</span><h2>Different goals.<br>Room to grow.</h2></div>
    <div class="prose"><p>Learners arrive with different levels of experience, time and access to tools. When you enquire, tell us what you already know and what you hope to do; we can help you check course fit and requirements.</p><p>We aim to make our website and learning information clear and usable. If you need an accommodation or want to discuss access needs, <a href="/contact/">contact the Academy</a> before choosing a programme.</p><p>Our course catalogue and <a href="/resources/">learning resources</a> are a starting point. Confirm current schedules, formats, fees and prerequisites with us.</p></div>
</section>
<section class="cta-section container"><div><span class="eyebrow">Let us get started</span><h2>Ready to explore?</h2><p>Browse our courses or tell us what you would like to learn.</p></div><a class="button button-primary" href="/contact/">Talk with us <span aria-hidden="true">&#8594;</span></a></section>
<?php page_end(); ?>
