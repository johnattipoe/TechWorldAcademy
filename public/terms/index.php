<?php require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; page_start('Terms of Use'); ?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Using this website</span>
        <h1><?php if (current_locale() === 'fr'): ?>Conditions<br><span>d’utilisation.</span><?php elseif (current_locale() === 'es'): ?>Términos de<br><span>uso.</span><?php else: ?>Terms of<br><span>use.</span><?php endif; ?></h1>
        <p>A few straightforward terms for using TechWorld Academy online.</p>
    </div>
</section>
<section class="section container legal-copy">
    <p><strong>Last updated: 14 October 2026</strong></p>
    <h2>Website information</h2>
    <p>This website shares general information about TechWorld Academy and its learning programmes. Course descriptions, levels and durations are indicative and may change. Contact us for current schedules, fees, entry requirements and availability.</p>
    <h2>Enquiries and enrolment</h2>
    <p>Submitting an enquiry does not create an enrolment, guarantee a place or form a contract. Any confirmed course registration will be subject to the applicable enrolment details and terms supplied by the Academy.</p>
    <h2>Acceptable use</h2>
    <p>Please use this website lawfully and do not attempt to interfere with its operation, access restricted systems, or submit harmful, misleading or unlawful content.</p>
    <h2>Content and availability</h2>
    <p>Website materials are provided for general information. We work to keep the site available and accurate but cannot promise uninterrupted service or that all information will always be complete and current.</p>
    <h2>External services</h2>
    <p>Links to third-party sites are provided for convenience. We do not control their content or privacy practices. Our handling of information submitted through this site is described in our <a href="/privacy/">Privacy Policy.</a></p>
    <h2>Contact</h2>
    <p>Questions about these terms can be sent to <a href="mailto:hello@techworld.academy">hello@techworld.academy</a>.</p>
</section><?php page_end(); ?>
