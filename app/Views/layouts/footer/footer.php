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
<?php if (!in_array($_COOKIE['site_cookie_consent'] ?? '', ['accepted', 'rejected'], true)): ?>
<section class="cookie-consent-banner" aria-labelledby="cookie-consent-title" data-cookie-consent>
    <div class="cookie-consent-copy">
        <h2 id="cookie-consent-title"><?= e(locale_text('Cookie choices', 'Choix des cookies', 'Opciones de cookies')) ?></h2>
        <p><?= e(locale_text('We use essential cookies for secure sign-in, your language choice and this preference. Accept to confirm, or Cancel to decline optional cookies. Essential cookies remain active so the site can work. Read our', 'Nous utilisons des cookies essentiels pour la connexion sécurisée, votre langue et ce choix. Acceptez ou annulez pour refuser les cookies facultatifs. Les cookies essentiels restent actifs. Consultez notre', 'Usamos cookies esenciales para iniciar sesión de forma segura, recordar el idioma y guardar esta opción. Acepta o cancela para rechazar cookies opcionales. Las cookies esenciales siguen activas. Lee nuestra')) ?> <a href="/privacy/"><?= e(locale_text('Privacy Policy', 'Politique de confidentialité', 'Política de privacidad')) ?></a>.</p>
    </div>
    <form method="post" action="/cookie-consent/" class="cookie-consent-actions">
        <input type="hidden" name="csrf_token" value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>">
        <input type="hidden" name="return_to" value="<?= e((string) ($_SERVER['REQUEST_URI'] ?? '/')) ?>">
        <button type="submit" name="choice" value="rejected" class="cookie-consent-cancel"><?= e(locale_text('Cancel', 'Annuler', 'Cancelar')) ?></button>
        <button type="submit" name="choice" value="accepted" class="cookie-consent-accept"><?= e(locale_text('Accept', 'Accepter', 'Aceptar')) ?></button>
    </form>
</section>
<?php endif; ?>
<aside class="learning-platform-promo" data-learning-promo role="dialog" aria-modal="false" aria-labelledby="learning-promo-title">
    <button class="learning-promo-close" type="button" data-learning-promo-close aria-label="<?= e(locale_text('Close learning platform offer', 'Fermer cette offre', 'Cerrar esta invitación')) ?>">&#215;</button>
    <span class="learning-promo-kicker"><?= e(locale_text('Your learning space', 'Votre espace de formation', 'Tu espacio de aprendizaje')) ?></span>
    <h2 id="learning-promo-title"><?= e(locale_text('Ready to start learning?', 'Prêt à commencer ?', '¿Listo para empezar?')) ?></h2>
    <p><?= e(locale_text('Explore courses, track your progress and keep your learning in one place.', 'Explorez les cours et suivez votre progression au même endroit.', 'Explora cursos y sigue tu progreso en un solo lugar.')) ?></p>
    <a href="<?= e(learning_platform_url('/')) ?>" target="_blank" rel="noopener noreferrer"><?= e(locale_text('Open learning platform', 'Ouvrir la plateforme', 'Abrir plataforma de aprendizaje')) ?> <span aria-hidden="true">&#8599;</span></a>
</aside>
<button class="back-to-top" type="button" aria-label="Back to top" title="Back to top">&#8593;</button>
<div class="chat-assistant" data-chat-assistant>
    <section class="chat-panel" id="chat-panel" aria-labelledby="chat-title" hidden>
        <div class="chat-panel-heading">
            <div><span class="chat-status-mark" aria-hidden="true"></span><div><h2 id="chat-title"><?= e(locale_text('TechWorld help', 'Aide TechWorld', 'Ayuda de TechWorld')) ?></h2><p><?= e(locale_text('Course and learning information', 'Informations sur les cours et l’apprentissage', 'Información sobre cursos y aprendizaje')) ?></p></div></div>
            <button class="chat-close" type="button" aria-label="<?= e(locale_text('Close chat', 'Fermer le chat', 'Cerrar chat')) ?>" data-chat-close>&#215;</button>
        </div>
        <div class="chat-messages" id="chat-messages" role="log" aria-live="polite" aria-relevant="additions text">
            <p class="chat-message chat-message-assistant"><?= e(locale_text('Hi! I can help you find courses, learning resources or the right way to contact the Academy.', 'Bonjour ! Je peux vous aider à trouver des cours, des ressources ou le bon moyen de contacter l’Académie.', '¡Hola! Puedo ayudarte a encontrar cursos, recursos o la forma de contactar con la Academia.')) ?></p>
        </div>
        <div class="chat-quick-questions" aria-label="<?= e(locale_text('Suggested questions', 'Questions suggérées', 'Preguntas sugeridas')) ?>">
            <button type="button" data-chat-prompt="courses"><?= e(locale_text('Browse courses', 'Voir les cours', 'Ver cursos')) ?></button>
            <button type="button" data-chat-prompt="team training"><?= e(locale_text('Team training', 'Formation en équipe', 'Formación para equipos')) ?></button>
            <button type="button" data-chat-prompt="resources"><?= e(locale_text('Learning resources', 'Ressources pédagogiques', 'Recursos de aprendizaje')) ?></button>
        </div>
        <form class="chat-form" id="chat-form">
            <label class="visually-hidden" for="chat-input"><?= e(locale_text('Ask TechWorld a question', 'Posez une question à TechWorld', 'Pregunta a TechWorld')) ?></label>
            <input id="chat-input" name="message" type="text" maxlength="300" autocomplete="off" placeholder="<?= e(locale_text('Type a question…', 'Écrivez une question…', 'Escribe una pregunta…')) ?>" required>
            <button type="submit" aria-label="<?= e(locale_text('Send question', 'Envoyer la question', 'Enviar pregunta')) ?>"><span aria-hidden="true">&#8593;</span></button>
        </form>
        <p class="chat-privacy-note"><?= e(locale_text('Automated guidance only. Your message stays in this browser and is not sent to a server.', 'Réponses automatisées. Votre message reste dans ce navigateur et n’est pas envoyé à un serveur.', 'Orientación automática. Tu mensaje permanece en este navegador y no se envía a un servidor.')) ?></p>
    </section>
    <button class="chat-launcher" type="button" id="chat-toggle" aria-expanded="false" aria-controls="chat-panel" aria-label="<?= e(locale_text('Open TechWorld chat', 'Ouvrir le chat TechWorld', 'Abrir chat de TechWorld')) ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H5l1.8-3.2A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8 11h.01M12 11h.01M16 11h.01"/></svg><span class="visually-hidden"><?= e(locale_text('Chat', 'Chat', 'Chat')) ?></span>
    </button>
</div>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand footer-brand" href="/" aria-label="<?= e((string) site_setting('siteName', 'TechWorld Academy')) ?> home">
                <img class="brand-mark" src="/assets/icons/techworld-mark.svg" alt="" aria-hidden="true"><span><?= e((string) site_setting('siteName', 'TechWorld Academy')) ?></span>
            </a>
            <p>Practical technology learning, guided practice and support for your next step.</p>
            <button class="footer-finder-open" type="button" data-open-course-finder><?= e(locale_text('Find a course for me', 'Trouver un cours', 'Encontrar un curso')) ?> <span aria-hidden="true">&#8594;</span></button>
        </div>
        <div><strong class="footer-heading"> <?= $icon('learn') ?><?= e(locale_text('Learn', 'Apprendre', 'Aprender')) ?></strong><a href="/courses/"><?= e(locale_text('All courses', 'Tous les cours', 'Todos los cursos')) ?></a><a href="/training/"><?= e(locale_text('Technology training', 'Formation technologique', 'Formación tecnológica')) ?></a><a href="/services/"><?= e(locale_text('Team training', 'Formation en équipe', 'Formación para equipos')) ?></a><a href="/about/"><?= e(locale_text('How we teach', 'Notre méthode', 'Cómo enseñamos')) ?></a></div>
        <div><strong class="footer-heading"> <?= $icon('resources') ?><?= e(locale_text('Resources', 'Ressources', 'Recursos')) ?></strong><a href="/resources/"><?= e(locale_text('Resource library', 'Bibliothèque de ressources', 'Biblioteca de recursos')) ?></a><a href="/resources/guides/"><?= e(locale_text('Learning guides', 'Guides d’apprentissage', 'Guías de aprendizaje')) ?></a><a href="/resources/glossary/"><?= e(locale_text('Tech glossary', 'Glossaire technique', 'Glosario tecnológico')) ?></a><a href="/resources/projects/"><?= e(locale_text('Practice projects', 'Projets pratiques', 'Proyectos prácticos')) ?></a></div>
        <div><strong class="footer-heading"> <?= $icon('topics') ?><?= e(locale_text('Popular topics', 'Sujets populaires', 'Temas populares')) ?></strong><a href="/courses/?search=web"><?= e(locale_text('Web development', 'Développement web', 'Desarrollo web')) ?></a><a href="/courses/?search=python">Python</a><a href="/courses/?search=data"><?= e(locale_text('Data analytics', 'Analyse de données', 'Análisis de datos')) ?></a><a href="/courses/?search=ai"><?= e(locale_text('AI and automation', 'IA et automatización', 'IA y automatización')) ?></a><a href="/courses/?search=security"><?= e(locale_text('Cybersecurity', 'Cybersécurité', 'Ciberseguridad')) ?></a><a href="/courses/?search=cloud"><?= e(locale_text('Cloud computing', 'Informatique en nuage', 'Computación en la nube')) ?></a></div>
        <div><strong class="footer-heading"> <?= $icon('help') ?><?= e(locale_text('Help and information', 'Aide et informations', 'Ayuda e información')) ?></strong><a href="/faq/"><?= e(locale_text('Frequently asked questions', 'Questions fréquentes', 'Preguntas frecuentes')) ?></a><a href="/contact/"><?= e(locale_text('Contact the team', 'Contacter l’équipe', 'Contactar con el equipo')) ?></a><a href="/privacy/"><?= e(locale_text('Privacy policy', 'Politique de confidentialité', 'Política de privacidad')) ?></a><a href="/terms/"><?= e(locale_text('Terms of use', 'Conditions d’utilisation', 'Términos de uso')) ?></a><a href="/sitemap.xml"><?= e(locale_text('Site map', 'Plan du site', 'Mapa del sitio')) ?></a></div>
        <div><strong class="footer-heading"> <?= $icon('contact') ?><?= e(locale_text('Get in touch', 'Nous contacter', 'Póngase en contacto')) ?></strong><a href="mailto:<?= e((string) site_setting('contactEmail', env_value('CONTACT_EMAIL', 'hello@techworld.academy'))) ?>"><?= e((string) site_setting('contactEmail', env_value('CONTACT_EMAIL', 'hello@techworld.academy'))) ?></a><?php if ((string) site_setting('contactPhone', '') !== ''): ?><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', (string) site_setting('contactPhone', ''))) ?>"><?= e((string) site_setting('contactPhone', '')) ?></a><?php endif; ?><p class="footer-contact-note"><?= e(locale_text('Ask about course fit, schedules, fees or team training.', 'Posez vos questions sur les cours, horaires, tarifs ou formations d’équipe.', 'Consulte sobre cursos, horarios, tarifas o formación para equipos.')) ?></p></div>
        <div><strong class="footer-heading">Explore the academy</strong><a href="/events/">Events &amp; workshops</a><a href="/webinars/">Webinars</a><a href="/blog/">Ideas and articles</a><a href="/careers/">Careers</a><a href="/partners/">Partnerships</a><a href="/scholarships/">Scholarships</a><a href="/success-stories/">Learner stories</a><a href="/pricing/">Pricing</a><a href="/help/">Help center</a><a href="/accessibility/">Accessibility</a><a href="/status/">Service status</a></div>
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
    <div class="container copyright">&#169; <?= date('Y') ?> <?= e((string) site_setting('siteName', 'TechWorld Academy')) ?>. All rights reserved.</div>
</footer>
<script src="/assets/js/navigation/navigation.js" defer></script>
<script src="/assets/js/main/main.js" defer></script>
    <script src="/assets/js/form-validation/form-validation.js" defer></script>
<script src="/assets/js/compiled/main.js" defer></script>
</body>
</html>
<?php
}
