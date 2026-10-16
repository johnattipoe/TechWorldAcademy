<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
$copy = page_copy('services');
page_start($copy['label'], $copy['description']);
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow"><?= e($copy['eyebrow']) ?></span>
        <h1><?= e($copy['title']) ?><?php if ($copy['accent'] !== ''): ?><br><span><?= e($copy['accent']) ?></span><?php endif; ?></h1>
        <p><?= e($copy['description']) ?></p>
        <a class="button button-primary" href="/contact/?course=Team%20training">Discuss team training <span aria-hidden="true">&#8594;</span></a>
    </div>
</section>
<section class="section container two-column">
    <div>
        <span class="eyebrow">A conversation first</span>
        <h2>Training shaped<br>around your goals.</h2>
    </div>
    <div class="prose">
        <p>Teams have different starting points and priorities. We begin by learning what your group needs to do, what they already know and what progress should look like.</p>
        <p>Topics may include web development, data analysis, programming, cybersecurity essentials, cloud foundations or practical AI and automation. We’ll confirm scope, format, schedule and fees with you before any commitment.</p>
        <p>Send us a short description of your team and goals. We’ll follow up to explore whether TechWorld Academy is a fit.</p>
        <a class="button button-primary" href="/contact/?course=Team%20training">Start a conversation <span aria-hidden="true">&#8594;</span></a>
    </div>
</section>
<section class="section learning-paths-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Plan a useful programme</span><h2>Begin with the work<br>your team needs to do.</h2></div><p class="section-note">A short conversation helps shape a relevant proposal. There is no commitment from making an enquiry.</p></div>
        <div class="path-grid">
            <article class="journey-card"><span>01</span><h3>Share the context</h3><p>Tell us about the team, its experience and the tasks or challenges you want to address.</p></article>
            <article class="journey-card"><span>02</span><h3>Choose the focus</h3><p>Discuss topics such as programming, web, data, security, cloud or AI foundations.</p></article>
            <article class="journey-card"><span>03</span><h3>Review the plan</h3><p>Confirm goals, scope, format, schedule and fees together before proceeding.</p></article>
        </div>
    </div>
</section>
<section class="section container two-column"><div><span class="eyebrow">To help us prepare</span><h2>What to include<br>in your enquiry.</h2></div><div class="prose"><ul><li>Approximate group size and learner experience</li><li>Skills or work tasks you want to focus on</li><li>Preferred timing and delivery format, if known</li><li>Any accessibility or equipment needs to discuss</li></ul><p>Not sure yet? Send what you know and we can work through the questions together.</p><a class="button button-primary" href="/contact/?course=Team%20training">Plan a team conversation <span aria-hidden="true">&#8594;</span></a></div></section>
<section class="section learning-paths-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Ways we can help</span><h2>Start with the need<br>your team has today.</h2></div><p class="section-note">We’ll use your goals and constraints to discuss a relevant scope. Topics and delivery are confirmed together.</p></div>
        <div class="path-grid service-offer-grid">
            <article class="path-card path-card-blue"><span class="path-number">01 / FOUNDATIONS</span><h3>Digital and coding basics</h3><p>Introduce web concepts, programming logic and common digital tools through guided, practical exercises.</p><span class="path-card-link">Useful for teams building shared technical confidence</span></article>
            <article class="path-card path-card-orange"><span class="path-number">02 / DATA</span><h3>Data skills for everyday work</h3><p>Explore spreadsheet practice, SQL foundations, data quality and clear ways to communicate findings.</p><span class="path-card-link">Useful for teams working with information</span></article>
            <article class="path-card path-card-green"><span class="path-number">03 / SAFE PRACTICE</span><h3>Security awareness</h3><p>Discuss safer digital habits, account protection and how to recognize common security risks.</p><span class="path-card-link">Useful for building consistent team habits</span></article>
            <article class="path-card path-card-purple"><span class="path-number">04 / MODERN TOOLS</span><h3>Cloud, AI and automation</h3><p>Understand common concepts and evaluate practical opportunities and limits for your workflows.</p><span class="path-card-link">Useful for teams exploring new tools responsibly</span></article>
        </div>
    </div>
</section>
<section class="section container two-column">
    <div><span class="eyebrow">Shape the engagement</span><h2>Agree on a clear scope.</h2></div>
    <div class="prose"><p>Training can be discussed for a single workshop, a focused learning series or a broader programme. Availability, delivery method, group size and fees depend on the requirements and are confirmed in a proposal.</p><ul><li>Learning objectives tied to your team's tasks</li><li>Topics sequenced from core concepts toward applied work</li><li>Exercises that can use fictional or approved sample data</li><li>Practical next steps for continued learning</li></ul><p>We do not need confidential company information to begin the conversation. Please describe needs at a high level in the enquiry form.</p><a class="button button-primary" href="/contact/?course=Team%20training">Request a discussion <span aria-hidden="true">&#8594;</span></a></div>
</section>
<section class="section learning-steps-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Common questions</span><h2>Before you get in touch.</h2></div></div>
        <div class="faq-list service-faq">
            <details><summary>Do you have fixed team-training packages?</summary><p>We start by discussing your goals and constraints. Topics, schedule, delivery, group size and fees are agreed with you before any commitment.</p></details>
            <details><summary>Can you work with different experience levels?</summary><p>Tell us about the group's current experience. We can discuss whether a shared foundation or a more focused topic is a better fit.</p></details>
            <details><summary>What should we send in the first enquiry?</summary><p>A team size estimate, the skills you want to build and any timing or accessibility needs are enough to start. Avoid including confidential data.</p></details>
        </div>
    </div>
</section>
<?php page_end(); ?>
