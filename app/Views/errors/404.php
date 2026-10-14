<?php
function render_not_found_view(): void
{
?>
<section class="page-hero error-hero"><div class="container"><span class="eyebrow">Error 404</span><h1>We cannot find<br><span>that page.</span></h1><p>The address may be out of date, or the page may have moved. Try one of these useful links instead.</p><div class="hero-actions"><a class="button button-primary" href="/">Go to the homepage <span aria-hidden="true">&#8594;</span></a><a class="text-link" href="/courses/">Explore courses</a></div></div></section>
<section class="section container"><div class="section-heading"><div><span class="eyebrow">Keep exploring</span><h2>Find what you need.</h2></div></div><div class="course-grid"><article class="course-card"><span class="tag">Learn</span><h3>Browse courses</h3><p>Explore the catalogue by topic and experience level.</p><a class="card-link" href="/courses/">See all courses <span aria-hidden="true">&#8599;</span></a></article><article class="course-card"><span class="tag">Get support</span><h3>Ask a question</h3><p>Tell us what you are looking for and our team can point you in the right direction.</p><a class="card-link" href="/contact/">Contact TechWorld <span aria-hidden="true">&#8599;</span></a></article><article class="course-card"><span class="tag">Find an answer</span><h3>Visit the FAQ</h3><p>Check common questions about courses, schedules and enrolment.</p><a class="card-link" href="/faq/">Read the FAQs <span aria-hidden="true">&#8599;</span></a></article></div></section>
<?php
}
