<?php require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; page_start('Terms of Use'); ?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Using this website</span>
        <h1><?php if (current_locale() === 'fr'): ?>Conditions<br><span>d’utilisation.</span><?php elseif (current_locale() === 'es'): ?>Términos de<br><span>uso.</span><?php else: ?>Terms of<br><span>use.</span><?php endif; ?></h1>
        <p>A few straightforward terms for using TechWorld Academy online.</p>
    </div>
</section>
<section class="section container legal-copy">
    <p><strong>Last updated: 16 October 2026</strong></p>
    <nav class="legal-contents" aria-label="Terms sections"><strong>On this page</strong><a href="#terms-information">Website information</a><a href="#terms-enquiry">Enquiries and enrolment</a><a href="#terms-payments">Payments and trials</a><a href="#terms-use">Acceptable use</a><a href="#terms-learning">Learning materials</a><a href="#terms-availability">Content and availability</a><a href="#terms-external">External services</a><a href="#terms-contact">Contact</a></nav>
    <h2 id="terms-information">Website information</h2>
    <p>This website shares general information about TechWorld Academy and its learning programmes. Course descriptions, levels and durations are indicative and may change. Contact us for current schedules, fees, entry requirements and availability.</p>
    <h2 id="terms-enquiry">Enquiries and enrolment</h2>
    <p>Submitting an enquiry does not create an enrolment, guarantee a place or form a contract. Any confirmed course registration will be subject to the applicable enrolment details and terms supplied by the Academy.</p>
    <p>Event and webinar registrations are subject to confirmation and any joining information supplied by the Academy. Career forms are expressions of interest unless a specific vacancy is clearly posted. Scholarship requests and partnership enquiries do not guarantee funding, acceptance or a partnership. We use information submitted through these forms to administer the request and respond, as described in our <a href="/privacy/">Privacy Policy.</a></p>
    <h2 id="terms-payments">Payments and trials</h2>
    <p>Course listings identify whether access is free, paid or includes a seven-day trial. The price and currency shown at checkout apply to that transaction. Paid access starts after Paystack confirms the payment; entering a mobile money number does not by itself complete payment. Paystack handles payment authorization, and you must review the amount and approve the request through its secure flow. Trial access begins when you enroll and ends at the displayed expiry time. A trial does not renew into a paid plan automatically; after it expires, course access remains locked unless you complete the separately presented payment. Contact us promptly if a payment is charged but access is not updated. Refund requests are reviewed individually under applicable law and any written offer terms shown at purchase; contact the Academy with the transaction reference. Do not send payment PINs or passwords to Academy staff.</p>
    <h2 id="terms-use">Acceptable use</h2>
    <p>Please use this website lawfully and do not attempt to interfere with its operation, access restricted systems, or submit harmful, misleading or unlawful content.</p>
    <h2 id="terms-learning">Learning materials</h2>
    <p>Public guides and project briefs are provided to support learning and practice. Check the terms of any external tools or datasets you use with them. Use fictional or approved sample information in public demonstrations, and do not share confidential or personal information in a project.</p>
    <h2 id="terms-availability">Content and availability</h2>
    <p>Website materials are provided for general information. We work to keep the site available and accurate but cannot promise uninterrupted service or that all information will always be complete and current.</p>
    <h2 id="terms-external">External services</h2>
    <p>Links to third-party sites are provided for convenience. We do not control their content or privacy practices. Our handling of information submitted through this site is described in our <a href="/privacy/">Privacy Policy.</a></p>
    <h2 id="terms-contact">Contact</h2>
    <p>Questions about these terms can be sent to <a href="mailto:hello@techworld.academy">hello@techworld.academy</a>.</p>
</section><?php page_end(); ?>
