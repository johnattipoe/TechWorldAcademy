<?php
function render_languages_view(array $languages): void
{
$copy = page_copy('training');
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow"><?= e($copy['eyebrow']) ?></span>
        <h1><?= e($copy['title']) ?><?php if ($copy['accent'] !== ''): ?><br><span><?= e($copy['accent']) ?></span><?php endif; ?></h1>
        <p><?= e($copy['description']) ?></p>
    </div>
</section>
<section class="section container">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Programming foundations</span>
            <h2>Start with the building blocks.</h2>
        </div>
        <a class="text-link" href="/courses/">Browse full courses <span aria-hidden="true">&#8594;</span></a>
    </div>
    <div class="course-grid language-grid"><?php foreach ($languages as $language): ?>
        <article class="course-card">
            <div class="course-icon icon-blue">&lt;/&gt;</div>
            <span class="tag">Programming skill</span>
            <h3><?= e((string) $language['name']) ?></h3>
            <p><?= e((string) $language['description']) ?></p>
            <a class="card-link" href="/contact/?course=<?= rawurlencode((string) $language['name']) ?>">Ask about learning <?= e((string) $language['name']) ?><span aria-hidden="true">&#8599;</span></a>
        </article>
    <?php endforeach; ?></div>
</section>
<section class="section learning-steps-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Make learning manageable</span><h2>Build a strong foundation.</h2></div><p class="section-note">Start with core ideas, then use them in small projects as your confidence grows.</p></div>
        <ol class="learning-steps"><li><span>01</span><div><h3>Understand the essentials</h3><p>Get familiar with the concepts, vocabulary and tools used in your chosen topic.</p></div></li><li><span>02</span><div><h3>Practice with examples</h3><p>Work through exercises that turn new ideas into repeatable skills.</p></div></li><li><span>03</span><div><h3>Try a project</h3><p>Connect the pieces in a practical project and identify what to learn next.</p></div></li></ol>
    </div>
</section>
<section class="section container">
    <div class="section-heading"><div><span class="eyebrow">Pick a starting point</span><h2>Follow a practical learning route.</h2></div><p class="section-note">You do not need to know every tool before you begin. Focus on one foundation and build outward.</p></div>
    <div class="path-grid training-route-grid">
        <article class="path-card path-card-blue"><span class="path-number">WEB FOUNDATIONS</span><h3>From first page to interaction</h3><p>Learn HTML structure and CSS layout, then use JavaScript to make pages respond to people.</p><span class="path-card-link">Good next step: build a multi-page site</span></article>
        <article class="path-card path-card-orange"><span class="path-number">PROGRAMMING</span><h3>From logic to useful scripts</h3><p>Practice variables, conditions, loops and functions before combining them in a small Python application.</p><span class="path-card-link">Good next step: automate a routine task</span></article>
        <article class="path-card path-card-green"><span class="path-number">DATA</span><h3>From questions to evidence</h3><p>Use SQL to find and organise information, then summarize patterns with a chart or clear explanation.</p><span class="path-card-link">Good next step: create a small data story</span></article>
    </div>
</section>
<section class="section learning-paths-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">A supportive process</span><h2>Know what you are working toward.</h2></div><p class="section-note">A short enquiry helps us understand your experience and recommend a sensible next step.</p></div>
        <ol class="learning-steps">
            <li><span>01</span><div><h3>Tell us your goal</h3><p>Share what you want to make, understand or improve, even if the goal is still taking shape.</p></div></li>
            <li><span>02</span><div><h3>Check the starting level</h3><p>We can discuss the background knowledge and tools that may be useful before you start.</p></div></li>
            <li><span>03</span><div><h3>Practice and review</h3><p>Use short exercises and project work to identify what you understand and what to revisit.</p></div></li>
        </ol>
    </div>
</section>
<section class="cta-section container">
    <div>
        <span class="eyebrow">A team or group to train?</span>
        <h2>Learning can be shared.</h2>
        <p>Tell us about your group and the skills you want to build.</p>
    </div>
    <a class="button button-primary" href="/services/">Team training <span aria-hidden="true">&#8594;</span></a>
</section>
<?php
}
