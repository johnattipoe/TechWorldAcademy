<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
start_app_session();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$course = isset($_GET['course']) ? trim((string) $_GET['course']) : '';
$copy = page_copy('contact');
page_start('Contact TechWorld Academy', $copy['description']);
?>
<section class="page-hero contact-hero">
    <div class="container">
        <span class="eyebrow"><?= e($copy['eyebrow']) ?></span>
        <h1><?= e($copy['title']) ?><?php if ($copy['accent'] !== ''): ?><br><span><?= e($copy['accent']) ?></span><?php endif; ?></h1>
        <p><?= e($copy['description']) ?></p>
        <div class="contact-hero-links">
            <a class="button button-primary" href="#contact-form">Send an enquiry <span aria-hidden="true">&darr;</span></a>
            <a class="text-link" href="/courses/">Explore courses <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>
</section>

<section class="section container contact-layout">
    <div class="contact-aside">
        <span class="eyebrow">Get in touch</span>
        <h2>A real person<br>will get back to you.</h2>
        <p>Use the form for course questions, learning guidance, team training enquiries or general messages.</p>
        <div class="contact-detail"><span class="contact-detail-icon" aria-hidden="true">@</span><div><strong>Email</strong><a href="mailto:<?= e((string) site_setting('contactEmail', env_value('CONTACT_EMAIL', 'hello@techworld.academy'))) ?>"><?= e((string) site_setting('contactEmail', env_value('CONTACT_EMAIL', 'hello@techworld.academy'))) ?></a></div></div>
        <?php if ((string) site_setting('contactPhone', '') !== ''): ?><div class="contact-detail"><span class="contact-detail-icon" aria-hidden="true">&#9742;</span><div><strong>Phone</strong><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', (string) site_setting('contactPhone', ''))) ?>"><?= e((string) site_setting('contactPhone', '')) ?></a></div></div><?php endif; ?>
        <div class="contact-detail"><span class="contact-detail-icon" aria-hidden="true">↗</span><div><strong>Explore first</strong><a href="/courses/">Browse our courses</a></div></div>
        <div class="contact-detail"><span class="contact-detail-icon" aria-hidden="true">?</span><div><strong>Quick answers</strong><a href="/faq/">Visit frequently asked questions</a></div></div>
        <p class="privacy-note">We use your details to respond to your enquiry. Please avoid sending passwords, payment details or confidential information. <a href="/privacy/">Read our Privacy Policy.</a></p>
    </div>

    <form class="contact-form" id="contact-form" action="/api/contact/contact.php" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <div class="form-trap" aria-hidden="true"><label for="website">Leave this field empty</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
        <div class="form-intro"><h2>Send us a message</h2><p>Fields marked <span aria-hidden="true">*</span> are required.</p></div>
        <div class="form-field"><label for="name">Your name <span aria-hidden="true">*</span></label><input id="name" name="name" type="text" autocomplete="name" maxlength="100" required aria-describedby="name-error"><span class="field-error" id="name-error"></span></div>
        <div class="form-field"><label for="email">Email address <span aria-hidden="true">*</span></label><input id="email" name="email" type="email" autocomplete="email" maxlength="254" required aria-describedby="email-error"><span class="field-error" id="email-error"></span></div>
        <div class="form-field"><label for="message">How can we help? <span aria-hidden="true">*</span></label><textarea id="message" name="message" rows="6" minlength="10" maxlength="5000" required aria-describedby="message-hint message-error" placeholder="Tell us a little about what you're looking for..."><?= e($course !== '' ? 'I would like to know more about ' . $course . '.' : '') ?></textarea><span class="form-field-hint" id="message-hint">A course name, your experience level and what you want to achieve can help us give a more useful answer.</span><span class="field-error" id="message-error"></span></div>
        <div class="form-response" id="form-response" role="status" aria-live="polite" tabindex="-1"></div>
        <button class="button button-primary submit-button" type="submit">Send your message <span aria-hidden="true">&rarr;</span></button>
        <p class="form-small">Your information is handled in line with our <a href="/privacy/">Privacy Policy.</a></p>
    </form>
</section>

<section class="section contact-next-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">After you send your message</span><h2>Here's what to expect.</h2></div><p class="section-note">A simple path from your question to a helpful conversation.</p></div>
        <ol class="learning-steps contact-next-steps">
            <li><span>01</span><div><h3>Your enquiry is received</h3><p>We securely record your message so the team can review and respond.</p></div></li>
            <li><span>02</span><div><h3>We look at your goals</h3><p>Include the topic, your experience and what you want to learn for more relevant guidance.</p></div></li>
            <li><span>03</span><div><h3>We reply by email</h3><p>We’ll use the email address you provide to follow up about your question.</p></div></li>
        </ol>
    </div>
</section>

<section class="section learning-steps-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">What to include</span><h2>A little context helps us help you.</h2></div><p class="section-note">Keep your message focused on the question or learning goal you want to discuss.</p></div>
        <div class="home-learning-grid contact-tips-grid">
            <article><span aria-hidden="true">01</span><h3>Your topic</h3><p>Mention a course, skill or type of team training you would like to know about. <a href="/services/">See team training</a>.</p></article>
            <article><span aria-hidden="true">02</span><h3>Your starting point</h3><p>Share relevant experience and what you hope to be able to do next. Beginners are welcome to say so.</p></article>
            <article><span aria-hidden="true">03</span><h3>Your questions</h3><p>Ask about course fit, learning format, fees, schedules or any requirements you’ve seen.</p></article>
        </div>
        <p class="resource-safety-note">Please do not include passwords, payment details or confidential personal or company information in your message. <a href="/faq/">Browse frequently asked questions</a> if you are still comparing options.</p>
    </div>
</section>
<?php page_end(); ?>
