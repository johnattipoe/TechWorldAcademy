<?php
function render_languages_view(array $languages): void
{
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Learn by building</span>
        <h1>Get hands-on<br><span>with technology.</span></h1>
        <p>Build practical foundations in programming, data and modern digital tools with guided projects and supportive instruction.</p>
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
