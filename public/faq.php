<?php require_once dirname(__DIR__) . '/app/bootstrap.php'; page_start('Frequently asked questions'); ?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Questions, answered</span>
        <h1>A few things<br><span>you might be wondering.</span></h1>
        <p>Still curious? Our team is happy to help you find the details you need.</p>
    </div>
</section>
<section class="section container faq-list">
    <div class="faq-search"><label for="faq-search">Search these answers</label><input id="faq-search" type="search" placeholder="Try fees, experience or schedule"><p id="faq-count" role="status" aria-live="polite">9 answers</p></div>
    <details><summary>Who are the courses for?<span aria-hidden="true">+</span></summary><p>Our courses welcome beginners and people looking to build on existing skills. Each course lists its recommended level so you can choose a comfortable starting point.</p></details>
    <details><summary>How are courses delivered?<span aria-hidden="true">+</span></summary><p>Course schedules and delivery formats can vary. Enquire about the course you’re interested in and our team will share the current details.</p></details>
    <details><summary>How much time should I set aside?<span aria-hidden="true">+</span></summary><p>Course durations are listed on each course card. Your weekly time commitment depends on the course format and your pace; contact us for a current estimate.</p></details>
    <details><summary>Do I need previous experience?<span aria-hidden="true">+</span></summary><p>Many courses are designed for beginners and state this clearly. For intermediate courses, we can help you check whether your experience is a good fit.</p></details>
    <details><summary>How do I enrol or ask about fees?<span aria-hidden="true">+</span></summary><p>Use the Enquire action on a course or send us a message. We’ll respond with current enrolment, fee and schedule information.</p></details>
    <details><summary>How do you use my contact details?<span aria-hidden="true">+</span></summary><p>We use the details you submit to respond to your enquiry. See our <a href="/privacy/">Privacy Policy</a> for more information.</p></details>
    <details><summary>What equipment or software do I need?<span aria-hidden="true">+</span></summary><p>Requirements depend on the course and delivery format. Ask us about the specific course and we will confirm any computer, internet or software needs before you commit.</p></details>
    <details><summary>Can I arrange training for a team?<span aria-hidden="true">+</span></summary><p>Yes. Tell us about your group, goals and preferred topics on our <a href="/services/">team training page</a>. We can discuss a suitable scope and format.</p></details>
    <details><summary>Do you provide a certificate?<span aria-hidden="true">+</span></summary><p>Recognition or completion documents can vary by programme. Please ask about the course you have in mind so we can confirm what is currently available.</p></details>
    <p class="empty-courses" id="faq-empty" hidden>No answers match that search. Try another topic or <a href="/contact/">ask us directly</a>.</p>
    <div class="faq-cta"><p>Have another question?</p><a class="button button-primary" href="/contact/">Contact our team <span aria-hidden="true">→</span></a></div>
</section><?php page_end(); ?>
