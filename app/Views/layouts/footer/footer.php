<?php
function render_site_footer(): void
{
$icon = static function (string $name): string {
    $paths = [
        'learn' => '<path d="M3 5.5A2.5 2.5 0 0 1 5.5 3H12v17H5.5A2.5 2.5 0 0 0 3 22.5z"/><path d="M21 5.5A2.5 2.5 0 0 0 18.5 3H12v17h6.5a2.5 2.5 0 0 1 2.5 2.5z"/>',
        'resources' => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5v16M8 7h8M8 11h8"/>',
        'topics' => '<path d="m12 3 2.4 5.2 5.6.7-4.1 3.9 1 5.6-4.9-2.8-4.9 2.8 1-5.6L4 8.9l5.6-.7z"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9a2.5 2.5 0 1 1 4.4 1.6c-1.2 1-2 1.4-2 3M12 17h.01"/>',
        'contact' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'newsletter' => '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6M8 3v4M16 3v4"/>',
    ];
    return '<svg class="footer-heading-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
};
?>
<button class="back-to-top" type="button" aria-label="Back to top" title="Back to top">&#8593;</button>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand footer-brand" href="/" aria-label="TechWorld Academy home">
                <span class="brand-mark" aria-hidden="true">T</span><span>TechWorld <b>Academy</b></span>
            </a>
            <p>Practical technology learning, guided practice and support for your next step.</p>
            <button class="footer-finder-open" type="button" data-open-course-finder><?= e(locale_text('Find a course for me', 'Trouver un cours', 'Encontrar un curso')) ?> <span aria-hidden="true">&#8594;</span></button>
        </div>
        <div><strong class="footer-heading"> <?= $icon('learn') ?><?= e(locale_text('Learn', 'Apprendre', 'Aprender')) ?></strong><a href="/courses/"><?= e(locale_text('All courses', 'Tous les cours', 'Todos los cursos')) ?></a><a href="/training/"><?= e(locale_text('Technology training', 'Formation technologique', 'Formación tecnológica')) ?></a><a href="/services/"><?= e(locale_text('Team training', 'Formation en équipe', 'Formación para equipos')) ?></a><a href="/about/"><?= e(locale_text('How we teach', 'Notre méthode', 'Cómo enseñamos')) ?></a></div>
        <div><strong class="footer-heading"> <?= $icon('resources') ?><?= e(locale_text('Resources', 'Ressources', 'Recursos')) ?></strong><a href="/resources/"><?= e(locale_text('Resource library', 'Bibliothèque de ressources', 'Biblioteca de recursos')) ?></a><a href="/resources/guides/"><?= e(locale_text('Learning guides', 'Guides d’apprentissage', 'Guías de aprendizaje')) ?></a><a href="/resources/glossary/"><?= e(locale_text('Tech glossary', 'Glossaire technique', 'Glosario tecnológico')) ?></a><a href="/resources/projects/"><?= e(locale_text('Practice projects', 'Projets pratiques', 'Proyectos prácticos')) ?></a></div>
        <div><strong class="footer-heading"> <?= $icon('topics') ?><?= e(locale_text('Popular topics', 'Sujets populaires', 'Temas populares')) ?></strong><a href="/courses/?search=web"><?= e(locale_text('Web development', 'Développement web', 'Desarrollo web')) ?></a><a href="/courses/?search=python">Python</a><a href="/courses/?search=data"><?= e(locale_text('Data analytics', 'Analyse de données', 'Análisis de datos')) ?></a><a href="/courses/?search=ai"><?= e(locale_text('AI and automation', 'IA et automatización', 'IA y automatización')) ?></a><a href="/courses/?search=security"><?= e(locale_text('Cybersecurity', 'Cybersécurité', 'Ciberseguridad')) ?></a><a href="/courses/?search=cloud"><?= e(locale_text('Cloud computing', 'Informatique en nuage', 'Computación en la nube')) ?></a></div>
        <div><strong class="footer-heading"> <?= $icon('help') ?><?= e(locale_text('Help and information', 'Aide et informations', 'Ayuda e información')) ?></strong><a href="/faq/"><?= e(locale_text('Frequently asked questions', 'Questions fréquentes', 'Preguntas frecuentes')) ?></a><a href="/contact/"><?= e(locale_text('Contact the team', 'Contacter l’équipe', 'Contactar con el equipo')) ?></a><a href="/privacy/"><?= e(locale_text('Privacy policy', 'Politique de confidentialité', 'Política de privacidad')) ?></a><a href="/terms/"><?= e(locale_text('Terms of use', 'Conditions d’utilisation', 'Términos de uso')) ?></a><a href="/sitemap.xml"><?= e(locale_text('Site map', 'Plan du site', 'Mapa del sitio')) ?></a></div>
        <div><strong class="footer-heading"> <?= $icon('contact') ?><?= e(locale_text('Get in touch', 'Nous contacter', 'Póngase en contacto')) ?></strong><a href="mailto:<?= e(env_value('CONTACT_EMAIL', 'hello@techworld.academy')) ?>"><?= e(env_value('CONTACT_EMAIL', 'hello@techworld.academy')) ?></a><p class="footer-contact-note"><?= e(locale_text('Ask about course fit, schedules, fees or team training.', 'Posez vos questions sur les cours, horaires, tarifs ou formations d’équipe.', 'Consulte sobre cursos, horarios, tarifas o formación para equipos.')) ?></p></div>
    </div>
    <div class="container newsletter">
        <div><strong class="footer-heading"> <?= $icon('newsletter') ?>Occasional notes from TechWorld</strong><p>Course updates and practical learning ideas. Unsubscribe at any time.</p></div>
        <form id="newsletter-form" action="/api/newsletter/newsletter.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>">
            <div class="form-trap" aria-hidden="true"><label for="newsletter-website">Leave this field empty</label><input id="newsletter-website" name="website" tabindex="-1" autocomplete="off"></div>
            <label class="visually-hidden" for="newsletter-email">Email address</label>
            <input id="newsletter-email" name="email" type="email" autocomplete="email" maxlength="254" required placeholder="Your email address">
            <label class="consent-label"><input type="checkbox" name="consent" value="1" required> I agree to receive occasional academy emails. <a href="/privacy/">Privacy policy</a></label>
            <div class="newsletter-response" id="newsletter-response" role="status" aria-live="polite"></div>
            <button type="submit">Sign me up <span aria-hidden="true">&#8594;</span></button>
        </form>
    </div>
    <div class="container copyright">&#169; <?= date('Y') ?> TechWorld Academy. All rights reserved.</div>
</footer>
<script src="/assets/js/navigation/navigation.js" defer></script>
<script src="/assets/js/main/main.js" defer></script>
    <script src="/assets/js/form-validation/form-validation.js" defer></script>
<script src="/assets/js/compiled/main.js" defer></script>
</body>
</html>
<?php
}
