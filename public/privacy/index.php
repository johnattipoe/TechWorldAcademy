<?php require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; page_start('Privacy Policy'); ?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Your information</span>
        <h1><?php if (current_locale() === 'fr'): ?>Politique de<br><span>confidentialité.</span><?php elseif (current_locale() === 'es'): ?>Política de<br><span>privacidad.</span><?php else: ?>Privacy<br><span>Policy.</span><?php endif; ?></h1>
        <p>How TechWorld Academy collects and uses information you share with us.</p>
    </div>
</section>
<section class="section container legal-copy">
    <p><strong>Last updated: 14 October 2026</strong></p>
    <h2>Information we collect</h2>
    <p>When you contact us, we collect the name, email address and message you choose to provide. If you subscribe to academy updates, we collect your email address and the date you gave consent. Our website host may also process basic technical information, such as your IP address and browser details, in server logs and to limit repeated form submissions.</p>
    <h2>How we use it</h2>
    <p>We use enquiry information to respond to your message and provide information you request. We use newsletter addresses to send occasional academy updates; you can ask us to unsubscribe you at any time. We do not sell personal information. Contact submissions and subscription records are stored in our Firebase Firestore database, accessible to authorised academy staff.</p>
    <h2>Retention and security</h2>
    <p>We keep enquiries only for as long as needed to handle the request, maintain appropriate business records and meet legal obligations. We keep newsletter details until you unsubscribe or request deletion. We use access controls and reasonable technical safeguards; no internet transmission or storage system can be guaranteed completely secure.</p>
    <h2>Your choices</h2>
    <p>You may ask to access, correct or delete personal information you sent us, or unsubscribe from updates, subject to applicable law. Contact <a href="mailto:hello@techworld.academy">hello@techworld.academy</a> with your request.</p>
    <h2>Children and third parties</h2>
    <p>This website is intended for a general audience and is not designed to collect information from children. Our forms send information to Firebase for storage; its processing is subject to the relevant provider terms and privacy commitments.</p>
    <h2>Changes and contact</h2>
    <p>We may update this policy as our services change. The latest version will appear on this page. Questions can be sent to <a href="mailto:hello@techworld.academy">hello@techworld.academy</a>.</p>
</section><?php page_end(); ?>
