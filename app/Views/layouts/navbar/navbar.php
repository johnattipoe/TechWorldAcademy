<?php
$navItems = [
    '/' => locale_text('Home', 'Accueil', 'Inicio'),
    '/courses/' => locale_text('Courses', 'Cours', 'Cursos'),
];
$learnItems = [
    '/training/' => locale_text('Technology training', 'Formation technologique', 'Formación tecnológica'),
];
$serviceItems = [
    '/services/' => locale_text('Team training', 'Formation en équipe', 'Formación para equipos'),
    '/contact/?course=Team%20training' => locale_text('Request a tailored programme', 'Demander un programme adapté', 'Solicitar un programa a medida'),
];
$resourceItems = [
    '/resources/' => locale_text('Resource library', 'Bibliothèque de ressources', 'Biblioteca de recursos'),
    '/resources/guides/' => locale_text('Learning guides', 'Guides d’apprentissage', 'Guías de aprendizaje'),
    '/resources/glossary/' => locale_text('Tech glossary', 'Glossaire technique', 'Glosario tecnológico'),
    '/resources/projects/' => locale_text('Practice projects', 'Projets pratiques', 'Proyectos prácticos'),
];
$communityItems = [
    '/events/' => 'Events & workshops', '/webinars/' => 'Webinars', '/community/' => 'Community',
    '/success-stories/' => 'Learner stories', '/scholarships/' => 'Scholarships',
    '/careers/' => 'Careers', '/partners/' => 'Partner with us', '/help/' => 'Help center',
    '/pricing/' => 'Pricing', '/locations/' => 'Locations', '/accessibility/' => 'Accessibility', '/status/' => 'Service status',
];
$learningPortalHref = learning_platform_url('/');
$learningPortalOrigin = rtrim(learning_platform_url('/'), '/');
$academyItems = [
    '/about/' => locale_text('About TechWorld', 'À propos de TechWorld', 'Acerca de TechWorld'),
    '/faq/' => locale_text('Frequently asked questions', 'Questions fréquentes', 'Preguntas frecuentes'),
    '/contact/' => locale_text('Contact the academy', 'Contacter l’académie', 'Contactar con la academia'),
    '/privacy/' => locale_text('Privacy policy', 'Politique de confidentialité', 'Política de privacidad'),
    '/terms/' => locale_text('Terms of use', 'Conditions d’utilisation', 'Términos de uso'),
];
$academyItems[$learningPortalHref] = locale_text('Online learning platform', 'Plateforme d’apprentissage', 'Plataforma de aprendizaje');
if (!empty($_SESSION['learning_user_id'])) {
    $academyItems[learning_platform_url('/student/my-courses/')] = locale_text('My courses', 'Mes cours', 'Mis cursos');
    $academyItems[learning_platform_url('/student/progress/')] = locale_text('My progress', 'Ma progression', 'Mi progreso');
    $academyItems[learning_platform_url('/student/assessments/')] = locale_text('My assessments', 'Mes évaluations', 'Mis evaluaciones');
    $academyItems[learning_platform_url('/student/assignments/')] = locale_text('My assignments', 'Mes devoirs', 'Mis tareas');
    $academyItems[learning_platform_url('/student/certificates/')] = locale_text('My certificates', 'Mes certificats', 'Mis certificados');
}
$learnIsCurrent = array_key_exists($path, $learnItems);
$servicesIsCurrent = $path === '/services/';
$resourceIsCurrent = array_key_exists($path, $resourceItems) || str_starts_with($path, '/resources/') || str_starts_with($path, '/blog/');
$communityIsCurrent = array_key_exists($path, $communityItems) || str_starts_with($path, '/events/') || str_starts_with($path, '/webinars/') || str_starts_with($path, '/careers/') || str_starts_with($path, '/partners/') || str_starts_with($path, '/scholarships/') || str_starts_with($path, '/community/') || str_starts_with($path, '/success-stories/') || str_starts_with($path, '/help/') || str_starts_with($path, '/pricing/') || str_starts_with($path, '/locations/') || str_starts_with($path, '/accessibility/') || str_starts_with($path, '/status/');
$academyIsCurrent = array_key_exists($path, $academyItems) || $path === '/register/';
$languageQuery = $_GET;
unset($languageQuery['lang']);
$languageAction = $path . ($languageQuery ? '?' . http_build_query($languageQuery) : '');
?>
<nav class="main-nav" aria-label="<?= e(locale_text('Main navigation', 'Navigation principale', 'Navegación principal')) ?>">
    <?php foreach ($navItems as $href => $label): ?>
        <a href="<?= e($href) ?>"<?= $path === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
    <details class="nav-dropdown" data-nav-dropdown>
        <summary<?= $learnIsCurrent ? ' aria-current="page"' : '' ?>><?= e(locale_text('Learn', 'Apprendre', 'Aprender')) ?> <span aria-hidden="true">&#8964;</span></summary>
        <div class="nav-dropdown-menu">
            <span class="nav-dropdown-label"><?= e(locale_text('Learning options', 'Options de formation', 'Opciones de aprendizaje')) ?></span>
            <?php foreach ($learnItems as $href => $label): ?>
                <a href="<?= e($href) ?>"<?= $path === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
            <button class="dropdown-finder-trigger" type="button" data-open-course-finder><?= e(locale_text('Find a course for me', 'Trouver un cours', 'Encontrar un curso')) ?> <span aria-hidden="true">&#8594;</span></button>
        </div>
    </details>
    <details class="nav-dropdown" data-nav-dropdown>
        <summary<?= $servicesIsCurrent ? ' aria-current="page"' : '' ?>><?= e(locale_text('Services', 'Services', 'Servicios')) ?> <span aria-hidden="true">&#8964;</span></summary>
        <div class="nav-dropdown-menu">
            <span class="nav-dropdown-label"><?= e(locale_text('Training for people and teams', 'Formation pour les personnes et les équipes', 'Formación para personas y equipos')) ?></span>
            <?php foreach ($serviceItems as $href => $label): ?>
                <a href="<?= e($href) ?>"<?= $path === '/services/' && $href === '/services/' ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
    </details>
    <details class="nav-dropdown" data-nav-dropdown>
        <summary<?= $resourceIsCurrent ? ' aria-current="page"' : '' ?>><?= e(locale_text('Resources', 'Ressources', 'Recursos')) ?> <span aria-hidden="true">&#8964;</span></summary>
        <div class="nav-dropdown-menu">
            <span class="nav-dropdown-label"><?= e(locale_text('Tools for your learning', 'Outils pour apprendre', 'Herramientas de aprendizaje')) ?></span>
            <?php foreach ($resourceItems as $href => $label): ?>
                <a href="<?= e($href) ?>"<?= $path === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
    </details>
    <details class="nav-dropdown" data-nav-dropdown>
        <summary<?= $communityIsCurrent ? ' aria-current="page"' : '' ?>>Explore <span aria-hidden="true">&#8964;</span></summary>
        <div class="nav-dropdown-menu"><span class="nav-dropdown-label">Events, community &amp; opportunities</span><?php foreach ($communityItems as $href => $label): ?><a href="<?= e($href) ?>"<?= $path === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a><?php endforeach; ?></div>
    </details>
    <details class="nav-dropdown" data-nav-dropdown>
        <summary<?= $academyIsCurrent ? ' aria-current="page"' : '' ?>><?= e(locale_text('Academy', 'Académie', 'Academia')) ?> <span aria-hidden="true">&#8964;</span></summary>
        <div class="nav-dropdown-menu">
            <span class="nav-dropdown-label"><?= e(locale_text('About and support', 'À propos et assistance', 'Información y ayuda')) ?></span>
            <?php foreach ($academyItems as $href => $label): ?>
                <?php $portalLink = $learningPortalOrigin !== '' && str_starts_with($href, $learningPortalOrigin); ?><a href="<?= e($href) ?>"<?= $portalLink ? ' target="_blank" rel="noopener noreferrer"' : '' ?><?= $path === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
    </details>
    <button class="nav-finder-open" type="button" data-open-course-finder><?= e(locale_text('Find a course', 'Trouver un cours', 'Buscar un curso')) ?> <span aria-hidden="true">&#8594;</span></button>
    <a class="nav-cta nav-learning-cta" href="<?= e($learningPortalHref) ?>" target="_blank" rel="noopener noreferrer"><?= e(locale_text('Open learning platform', 'Ouvrir la plateforme', 'Abrir plataforma de aprendizaje')) ?> <span aria-hidden="true">&#8599;</span></a>
    <a class="nav-cta" href="/contact/"<?= $path === '/contact/' ? ' aria-current="page"' : '' ?>><?= e(locale_text('Contact', 'Contact', 'Contacto')) ?> <span aria-hidden="true">&#8599;</span></a>
    <form class="language-switcher" action="<?= e($languageAction) ?>" method="get" data-language-switcher>
        <select id="site-language" name="lang" aria-label="<?= e(locale_text('Choose language', 'Choisir la langue', 'Elegir idioma')) ?>">
            <option value="en"<?= current_locale() === 'en' ? ' selected' : '' ?>>EN</option>
            <option value="fr"<?= current_locale() === 'fr' ? ' selected' : '' ?>>FR</option>
            <option value="es"<?= current_locale() === 'es' ? ' selected' : '' ?>>ES</option>
        </select>
    </form>
</nav>
<button class="search-toggle" type="button" aria-label="<?= e(locale_text('Search the site', 'Rechercher sur le site', 'Buscar en el sitio')) ?>" aria-keyshortcuts="Control+K Meta+K"><span aria-hidden="true">&#9906;</span></button>
<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-nav" aria-label="<?= e(locale_text('Open navigation', 'Ouvrir la navigation', 'Abrir navegación')) ?>"><span></span><span></span></button>
</div>
<nav id="mobile-nav" class="mobile-nav" aria-label="<?= e(locale_text('Mobile navigation', 'Navigation mobile', 'Navegación móvil')) ?>" hidden>
    <form class="mobile-language-switcher" action="<?= e($languageAction) ?>" method="get" data-language-switcher>
        <select id="mobile-site-language" name="lang" aria-label="<?= e(locale_text('Choose language', 'Choisir la langue', 'Elegir idioma')) ?>">
            <option value="en"<?= current_locale() === 'en' ? ' selected' : '' ?>>EN</option>
            <option value="fr"<?= current_locale() === 'fr' ? ' selected' : '' ?>>FR</option>
            <option value="es"<?= current_locale() === 'es' ? ' selected' : '' ?>>ES</option>
        </select>
    </form>
    <?php foreach ($navItems as $href => $label): ?>
        <a href="<?= e($href) ?>"<?= $path === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
    <?php foreach ([
        'Learn' => [$learnIsCurrent, $learnItems],
        'Services' => [$servicesIsCurrent, $serviceItems],
        'Resources' => [$resourceIsCurrent, $resourceItems],
        'Explore' => [$communityIsCurrent, $communityItems],
        'Academy' => [$academyIsCurrent, $academyItems],
    ] as $section => [$isCurrent, $items]): ?>
        <details class="mobile-nav-dropdown" data-nav-dropdown>
            <summary<?= $isCurrent ? ' aria-current="page"' : '' ?>><?= e($section === 'Explore' ? 'Explore' : locale_text($section, $section === 'Learn' ? 'Apprendre' : ($section === 'Services' ? 'Services' : ($section === 'Resources' ? 'Ressources' : 'Académie')), $section === 'Learn' ? 'Aprender' : ($section === 'Services' ? 'Servicios' : ($section === 'Resources' ? 'Recursos' : 'Academia')))) ?> <span aria-hidden="true">&#8964;</span></summary>
            <div class="mobile-nav-submenu">
                <?php foreach ($items as $href => $label): ?>
                    <?php $portalLink = $section === 'Academy' && $learningPortalOrigin !== '' && str_starts_with($href, $learningPortalOrigin); ?><a href="<?= e($href) ?>"<?= $portalLink ? ' target="_blank" rel="noopener noreferrer"' : '' ?><?= $path === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
                <?php endforeach; ?>
                <?php if ($section === 'Learn'): ?><button class="mobile-submenu-finder" type="button" data-open-course-finder><?= e(locale_text('Find a course for me', 'Trouver un cours', 'Encontrar un curso')) ?></button><?php endif; ?>
            </div>
        </details>
    <?php endforeach; ?>
    <button class="mobile-finder-trigger" type="button" data-open-course-finder><?= e(locale_text('Find a course', 'Trouver un cours', 'Buscar un curso')) ?></button>
    <a href="<?= e($learningPortalHref) ?>" target="_blank" rel="noopener noreferrer"><?= e(locale_text('Open learning platform', 'Ouvrir la plateforme', 'Abrir plataforma de aprendizaje')) ?></a>
    <a href="/contact/"<?= $path === '/contact/' ? ' aria-current="page"' : '' ?>><?= e(locale_text('Contact', 'Contact', 'Contacto')) ?></a>
    <button class="mobile-search-trigger" type="button"><?= e(locale_text('Search the site', 'Rechercher sur le site', 'Buscar en el sitio')) ?></button>
</nav>
<dialog class="site-search" id="site-search" aria-labelledby="site-search-title">
    <div class="site-search-panel">
        <div class="site-search-heading"><div><span class="eyebrow"><?= e(locale_text('Find your way', 'Trouvez votre chemin', 'Encuentre su camino')) ?></span><h2 id="site-search-title"><?= e(locale_text('Search TechWorld', 'Rechercher sur TechWorld', 'Buscar en TechWorld')) ?></h2></div><button class="site-search-close" type="button" aria-label="<?= e(locale_text('Close search', 'Fermer la recherche', 'Cerrar búsqueda')) ?>">&#215;</button></div>
        <label for="site-search-input"><?= e(locale_text('Search pages and topics', 'Rechercher des pages et sujets', 'Buscar páginas y temas')) ?></label>
        <input id="site-search-input" type="search" autocomplete="off" placeholder="<?= e(locale_text('Try courses, Python or contact', 'Essayez cours, Python ou contact', 'Pruebe cursos, Python o contacto')) ?>">
        <p class="search-hint"><?= e(locale_text('Type a topic, then choose a result. Press Escape to close.', 'Saisissez un sujet, puis choisissez un résultat. Appuyez sur Échap pour fermer.', 'Escribe un tema y elige un resultado. Pulsa Escape para cerrar.')) ?></p>
        <ul class="site-search-results" id="site-search-results" aria-live="polite"></ul>
    </div>
</dialog>
<dialog class="site-search course-finder" id="course-finder" aria-labelledby="course-finder-title">
    <div class="site-search-panel">
        <div class="site-search-heading"><div><span class="eyebrow"><?= e(locale_text('A good place to begin', 'Un bon point de départ', 'Un buen lugar para comenzar')) ?></span><h2 id="course-finder-title"><?= e(locale_text('Find a course for you', 'Trouver un cours pour vous', 'Encuentra un curso para ti')) ?></h2></div><button class="finder-close" type="button" aria-label="<?= e(locale_text('Close course finder', 'Fermer le conseiller de cours', 'Cerrar el buscador de cursos')) ?>">&#215;</button></div>
        <p class="finder-intro"><?= e(locale_text('Choose the kind of work you want to explore. We’ll suggest a starting point from the current course catalogue.', 'Choisissez le domaine à explorer. Nous vous proposerons un point de départ dans notre catalogue actuel.', 'Elige el área que quieres explorar. Te sugeriremos un punto de partida del catálogo actual.')) ?></p>
        <form id="course-finder-form">
            <label for="course-interest"><?= e(locale_text('What would you like to learn?', 'Que souhaitez-vous apprendre ?', '¿Qué te gustaría aprender?')) ?></label>
            <select id="course-interest" name="interest" required>
                <option value=""><?= e(locale_text('Choose an interest', 'Choisissez un domaine', 'Elige un área')) ?></option>
                <option value="web"><?= e(locale_text('Build websites and digital products', 'Créer des sites et produits numériques', 'Crear sitios web y productos digitales')) ?></option>
                <option value="data"><?= e(locale_text('Make sense of data', 'Comprendre les données', 'Entender los datos')) ?></option>
                <option value="ai"><?= e(locale_text('Explore AI and automation', 'Découvrir l’IA et l’automatisation', 'Explorar IA y automatización')) ?></option>
                <option value="security"><?= e(locale_text('Protect systems and information', 'Protéger les systèmes et les informations', 'Proteger sistemas e información')) ?></option>
                <option value="cloud"><?= e(locale_text('Understand cloud platforms', 'Comprendre les plateformes cloud', 'Entender plataformas en la nube')) ?></option>
                <option value="code"><?= e(locale_text('Learn to write programs', 'Apprendre à programmer', 'Aprender a programar')) ?></option>
            </select>
            <label for="course-experience"><?= e(locale_text('Where are you starting?', 'Quel est votre niveau de départ ?', '¿Cuál es tu punto de partida?')) ?></label>
            <select id="course-experience" name="experience" required>
                <option value=""><?= e(locale_text('Choose your experience', 'Choisissez votre expérience', 'Elige tu experiencia')) ?></option>
                <option value="new"><?= e(locale_text('I’m new to this', 'Je débute', 'Estoy empezando')) ?></option>
                <option value="some"><?= e(locale_text('I have some experience', 'J’ai déjà de l’expérience', 'Tengo algo de experiencia')) ?></option>
                <option value="exploring"><?= e(locale_text('I’m still exploring', 'Je découvre encore', 'Todavía estoy explorando')) ?></option>
            </select>
            <button class="button button-primary finder-submit" type="submit"><?= e(locale_text('Show my suggestion', 'Voir ma suggestion', 'Ver mi sugerencia')) ?> <span aria-hidden="true">&#8594;</span></button>
        </form>
        <div class="finder-result" id="course-finder-result" role="status" aria-live="polite" hidden></div>
    </div>
</dialog>
