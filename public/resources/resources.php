<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$resourcePath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/resources/', PHP_URL_PATH) ?: '/resources/', '/') . '/';
$resourcePages = [
    '/resources/' => ['Resources', 'Practical guides and tools to support your technology learning.'],
    '/resources/guides/' => ['Learning guides', 'A practical framework for learning technical skills through small, repeatable steps.'],
    '/resources/glossary/' => ['Tech glossary', 'Plain-language definitions for common programming and digital technology terms.'],
    '/resources/projects/' => ['Practice projects', 'Starter project briefs to help you turn technology concepts into practical work.'],
];
if (!isset($resourcePages[$resourcePath])) {
    http_response_code(404);
    $resourcePath = '/resources/';
}
[$resourceTitle, $resourceDescription] = $resourcePages[$resourcePath];
page_start($resourceTitle, $resourceDescription);
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">TechWorld learning resources</span>
        <h1><?= $resourcePath === '/resources/' ? 'Useful tools for<br><span>your next step.</span>' : e($resourceTitle) ?></h1>
        <p><?= e($resourceDescription) ?></p>
        <?php if ($resourcePath !== '/resources/'): ?><a class="text-link" href="/resources/">&#8592; All resources</a><?php endif; ?>
    </div>
</section>

<?php if ($resourcePath === '/resources/'): ?>
<section class="section container">
    <div class="section-heading"><div><span class="eyebrow">Explore and practice</span><h2>Start with a helpful resource.</h2></div><p class="section-note">Use these materials at your own pace. They are designed to complement, not replace, course guidance.</p></div>
    <div class="path-grid resource-grid">
        <a class="path-card path-card-blue" href="/resources/guides/"><span class="path-number">01 / LEARN</span><h3>Learning guides</h3><p>Build a study routine, break big topics into manageable steps and keep track of what you can do.</p><span class="path-card-link">Read the guides <span aria-hidden="true">&#8594;</span></span></a>
        <a class="path-card path-card-orange" href="/resources/glossary/"><span class="path-number">02 / UNDERSTAND</span><h3>Tech glossary</h3><p>Get clear, beginner-friendly explanations of common programming and technology terms.</p><span class="path-card-link">Browse the glossary <span aria-hidden="true">&#8594;</span></span></a>
        <a class="path-card path-card-green" href="/resources/projects/"><span class="path-number">03 / BUILD</span><h3>Practice projects</h3><p>Try short project briefs in web development, data and programming to apply new concepts.</p><span class="path-card-link">Explore project ideas <span aria-hidden="true">&#8594;</span></span></a>
    </div>
</section>
<section class="cta-section container"><div><span class="eyebrow">Ready to go deeper?</span><h2>Connect practice with a course.</h2><p>Browse the catalogue or ask us what fits your goals and starting point.</p></div><a class="button button-primary" href="/courses/">Explore courses <span aria-hidden="true">&#8594;</span></a></section>

<?php elseif ($resourcePath === '/resources/guides/'): ?>
<section class="section container">
    <div class="section-heading"><div><span class="eyebrow">A repeatable routine</span><h2>Learn in short, focused cycles.</h2></div><p class="section-note">Consistency and active practice help turn unfamiliar ideas into skills you can use.</p></div>
    <ol class="learning-steps resource-steps">
        <li><span>01</span><div><h3>Set one clear goal</h3><p>Choose a small outcome, such as creating a page with a heading and a link, or explaining what a variable does.</p></div></li>
        <li><span>02</span><div><h3>Study a small concept</h3><p>Read or watch one focused lesson. Write down new terms and what you think they mean.</p></div></li>
        <li><span>03</span><div><h3>Practice without copying</h3><p>Close the example and recreate the idea from memory. When you get stuck, look up one specific question.</p></div></li>
        <li><span>04</span><div><h3>Review and explain</h3><p>Note what worked, what confused you and one next step. Explain the idea in your own words.</p></div></li>
    </ol>
    <div class="prose resource-note"><h2>A simple weekly plan</h2><p>Pick two or three study sessions that fit your schedule. Spend each session reviewing a concept, practicing it and recording your progress. Leave time to revisit challenging ideas instead of rushing to cover more topics.</p><p>Use keyboard shortcuts, breaks and accessible display settings that work for you. If a course exercise feels unclear, ask your instructor or contact the Academy for guidance.</p></div>
</section>

<?php elseif ($resourcePath === '/resources/glossary/'): ?>
<section class="section container">
    <div class="section-heading"><div><span class="eyebrow">Technology, in plain language</span><h2>Words you will meet as you learn.</h2></div><p class="section-note">These are short introductions. Meanings can vary a little with context.</p></div>
    <dl class="resource-glossary">
        <div><dt>Algorithm</dt><dd>A set of steps for solving a problem or completing a task.</dd></div>
        <div><dt>API</dt><dd>A defined way for software systems to request information or actions from one another.</dd></div>
        <div><dt>Backend</dt><dd>The parts of an application that run on a server, such as data handling and business rules.</dd></div>
        <div><dt>Cloud computing</dt><dd>Using computing resources, such as storage or servers, over a network rather than managing all the hardware yourself.</dd></div>
        <div><dt>Database</dt><dd>An organised collection of information that software can store, find and update.</dd></div>
        <div><dt>Frontend</dt><dd>The part of a website or application that people see and interact with.</dd></div>
        <div><dt>Git</dt><dd>A version control tool that records changes to files and helps people work on projects together.</dd></div>
        <div><dt>HTML</dt><dd>A markup language used to describe the structure and meaning of web content.</dd></div>
        <div><dt>Python</dt><dd>A general-purpose programming language often used for automation, data work and application development.</dd></div>
        <div><dt>Responsive design</dt><dd>An approach to building interfaces that adapt to different screen sizes and input methods.</dd></div>
        <div><dt>Variable</dt><dd>A named place in a program for a value that can be used or updated.</dd></div>
        <div><dt>Version control</dt><dd>A way to track edits over time, compare versions and restore earlier work.</dd></div>
    </dl>
</section>

<?php else: ?>
<section class="section container">
    <div class="section-heading"><div><span class="eyebrow">Learn by making</span><h2>Three projects to get started.</h2></div><p class="section-note">Choose a project that matches your current skills. Start small and add one improvement at a time.</p></div>
    <div class="resource-project-grid">
        <article class="course-card"><span class="tag">Web · beginner</span><h3>A personal profile page</h3><p>Create a simple page introducing a fictional learner, with clear sections and useful links.</p><h4>Try this</h4><ul><li>Structure the content with meaningful HTML</li><li>Add readable spacing, colour and type with CSS</li><li>Check the page at narrow and wide screen sizes</li></ul><p><strong>Stretch:</strong> Add a navigation area and a contact form layout.</p></article>
        <article class="course-card"><span class="tag">Data · beginner</span><h3>A small data story</h3><p>Choose a small public or made-up dataset and explain one useful pattern you notice.</p><h4>Try this</h4><ul><li>Write down a question before exploring the data</li><li>Check column names, missing values and units</li><li>Summarise one finding with a clear chart or table</li></ul><p><strong>Stretch:</strong> Compare two groups and explain the limits of the comparison.</p></article>
        <article class="course-card"><span class="tag">Programming · beginner</span><h3>A command-line quiz</h3><p>Build a short quiz that asks questions, checks answers and reports a final score.</p><h4>Try this</h4><ul><li>Store questions and answers in a simple structure</li><li>Use a loop to ask each question</li><li>Keep track of correct answers and show feedback</li></ul><p><strong>Stretch:</strong> Let the learner retry missed questions.</p></article>
    </div>
    <p class="resource-safety-note">Use sample or synthetic data for practice. Do not include real people’s personal or confidential information in public projects.</p>
</section>
<?php endif; ?>
<?php page_end(); ?>
