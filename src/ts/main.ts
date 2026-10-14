function queryElement<T extends Element>(selector: string): T | null {
  return document.querySelector<T>(selector);
}
type ApiResponse = {
  ok?: boolean;
  message?: string;
  errors?: Record<string, string>;
};

function uiText(english: string, french: string, spanish: string): string {
  switch (document.documentElement.lang) {
    case 'fr': return french;
    case 'es': return spanish;
    default: return english;
  }
}

function localizedApiMessage(message: string): string {
  const messages: Record<string, [string, string]> = {
    'Your form expired. Refresh the page and try again.': ['Votre formulaire a expiré. Actualisez la page puis réessayez.', 'El formulario ha caducado. Actualiza la página e inténtalo de nuevo.'],
    'Thanks for getting in touch. We’ll reply as soon as we can.': ['Merci pour votre message. Nous vous répondrons dès que possible.', 'Gracias por escribirnos. Te responderemos lo antes posible.'],
    'The form is temporarily unavailable. Please email us directly.': ['Le formulaire est temporairement indisponible. Contactez-nous directement par e-mail.', 'El formulario no está disponible temporalmente. Escríbenos directamente.'],
    'You’ve sent several messages recently. Please wait a little and try again.': ['Vous avez envoyé plusieurs messages récemment. Veuillez patienter avant de réessayer.', 'Has enviado varios mensajes recientemente. Espera un poco antes de intentarlo de nuevo.'],
    'Please check the highlighted fields.': ['Veuillez vérifier les champs indiqués.', 'Revisa los campos señalados.'],
    'We couldn’t send your message just now. Please try again or email hello@techworld.academy.': ['Nous ne pouvons pas envoyer votre message pour le moment. Réessayez ou écrivez à hello@techworld.academy.', 'No podemos enviar tu mensaje ahora. Inténtalo de nuevo o escribe a hello@techworld.academy.'],
    'Your message has been sent.': ['Votre message a été envoyé.', 'Tu mensaje se ha enviado.'],
    'We could not send your message. Please try again.': ['Nous ne pouvons pas envoyer votre message. Veuillez réessayer.', 'No podemos enviar tu mensaje. Inténtalo de nuevo.'],
    'We could not reach the server. Please try again later.': ['Impossible de joindre le serveur. Veuillez réessayer plus tard.', 'No se pudo conectar con el servidor. Inténtalo más tarde.'],
    'Thanks. If the address is eligible, it has been added.': ['Merci. Si l’adresse est éligible, elle a été ajoutée.', 'Gracias. Si la dirección cumple los requisitos, se ha añadido.'],
    'We could not complete your subscription.': ['Nous n’avons pas pu finaliser votre inscription.', 'No pudimos completar tu suscripción.'],
    'Enter your name (up to 100 characters).': ['Saisissez votre nom (100 caractères maximum).', 'Introduce tu nombre (hasta 100 caracteres).'],
    'Enter a valid email address.': ['Saisissez une adresse e-mail valide.', 'Introduce una dirección de correo válida.'],
    'Write a message between 10 and 5,000 characters.': ['Rédigez un message de 10 à 5 000 caractères.', 'Escribe un mensaje de entre 10 y 5.000 caracteres.'],
    'Your message contains unsupported characters.': ['Votre message contient des caractères non pris en charge.', 'Tu mensaje contiene caracteres no admitidos.'],
    'Subscription is temporarily unavailable. Please try again later.': ['L’abonnement est temporairement indisponible. Réessayez plus tard.', 'La suscripción no está disponible temporalmente. Inténtalo más tarde.'],
    'Please wait before trying to subscribe again.': ['Veuillez patienter avant de réessayer de vous abonner.', 'Espera antes de volver a intentar suscribirte.'],
    'Please confirm that you want to receive academy updates.': ['Veuillez confirmer que vous souhaitez recevoir les actualités de l’académie.', 'Confirma que quieres recibir novedades de la academia.'],
  };
  const translated = messages[message];
  return translated ? uiText(message, translated[0], translated[1]) : message;
}

function getElement<T extends HTMLElement>(id: string): T | null {
  return document.getElementById(id) as T | null;
}

function initPageLoading(): void {
  const loader = getElement<HTMLElement>('page-loader');
  if (!loader) return;

  const show = (): void => {
    loader.hidden = false;
    document.body.classList.add('page-is-loading');
  };
  const hide = (): void => {
    document.body.classList.remove('page-is-loading');
    loader.hidden = true;
  };

  if (document.readyState !== 'complete') show();
  document.addEventListener('DOMContentLoaded', hide, { once: true });
  window.addEventListener('pageshow', hide);
  document.addEventListener('click', (event) => {
    if (event.defaultPrevented || !(event.target instanceof Element)) return;
    const anchor = event.target.closest<HTMLAnchorElement>('a[href]');
    if (!anchor || anchor.hasAttribute('download') || anchor.target === '_blank') return;
    if (event instanceof MouseEvent && (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey)) return;
    const destination = new URL(anchor.href, window.location.href);
    if (destination.origin !== window.location.origin) return;
    if (destination.pathname === window.location.pathname && destination.search === window.location.search) return;
    show();
  });
}

type SearchEntry = { title: string; href: string; keywords: string };
const searchEntries: SearchEntry[] = [
  { title: uiText('Home', 'Accueil', 'Inicio'), href: '/', keywords: 'home start academy technology learn accueil inicio' },
  { title: uiText('Courses', 'Cours', 'Cursos'), href: '/courses/', keywords: 'courses catalogue course level duration web development data AI cybersecurity cloud python programming cours cursos' },
  { title: uiText('Technology training', 'Formation technologique', 'Formación tecnológica'), href: '/training/', keywords: 'training programming skills languages hands-on projects formation formación' },
  { title: uiText('Team training', 'Formation en équipe', 'Formación para equipos'), href: '/services/', keywords: 'services teams organisations group corporate workshop équipe equipo' },
  { title: uiText('Resources', 'Ressources', 'Recursos'), href: '/resources/', keywords: 'learning resources guides glossary projects practice study ressources recursos' },
  { title: uiText('Learning guides', 'Guides d’apprentissage', 'Guías de aprendizaje'), href: '/resources/guides/', keywords: 'study practice routine goal learning guide apprentissage guías' },
  { title: uiText('Tech glossary', 'Glossaire technique', 'Glosario tecnológico'), href: '/resources/glossary/', keywords: 'terms definitions programming technology glossary glossaire glosario' },
  { title: uiText('Practice projects', 'Projets pratiques', 'Proyectos prácticos'), href: '/resources/projects/', keywords: 'project briefs build practice web data programming projets proyectos' },
  { title: uiText('About TechWorld', 'À propos de TechWorld', 'Acerca de TechWorld'), href: '/about/', keywords: 'about mission values practical supportive community à propos acerca' },
  { title: uiText('Frequently asked questions', 'Questions fréquentes', 'Preguntas frecuentes'), href: '/faq/', keywords: 'faq questions answers fees enrolment experience delivery schedule fréquentes preguntas' },
  { title: uiText('Contact', 'Contact', 'Contacto'), href: '/contact/', keywords: 'contact email message enquiry ask help contacto' },
  { title: uiText('Privacy policy', 'Politique de confidentialité', 'Política de privacidad'), href: '/privacy/', keywords: 'privacy personal information data collection newsletter consent confidentialité privacidad' },
  { title: uiText('Terms of use', 'Conditions d’utilisation', 'Términos de uso'), href: '/terms/', keywords: 'terms conditions website use condiciones términos uso' },
];

function initSiteSearch(): void {
  const dialog = getElement<HTMLDialogElement>('site-search');
  const input = getElement<HTMLInputElement>('site-search-input');
  const results = getElement<HTMLUListElement>('site-search-results');
  const openButtons = document.querySelectorAll<HTMLButtonElement>('.search-toggle, .mobile-search-trigger');
  const close = document.querySelector<HTMLButtonElement>('.site-search-close');
  if (!dialog || !input || !results || !openButtons.length || !close) return;

  const render = (): void => {
    const query = input.value.trim().toLowerCase();
    const matches = query
      ? searchEntries.filter((entry) => `${entry.title} ${entry.keywords}`.toLowerCase().includes(query))
      : searchEntries.slice(0, 5);
    results.replaceChildren();
    if (matches.length === 0) {
      const item = document.createElement('li');
      item.className = 'site-search-empty';
      item.textContent = uiText('No matching pages. Try a broader topic or contact our team.', 'Aucune page correspondante. Essayez un autre sujet ou contactez notre équipe.', 'No hay páginas coincidentes. Prueba otro tema o contacta con el equipo.');
      results.append(item);
      return;
    }
    matches.forEach((entry) => {
      const item = document.createElement('li');
      const link = document.createElement('a');
      link.href = entry.href;
      link.textContent = entry.title;
      item.append(link);
      results.append(item);
    });
  };

  const open = (): void => {
    if (!dialog.open) dialog.showModal();
    render();
    window.setTimeout(() => input.focus(), 0);
  };
  openButtons.forEach((button) => button.addEventListener('click', () => {
    if (button.classList.contains('mobile-search-trigger')) {
      const menu = getElement<HTMLElement>('mobile-nav');
      const toggle = document.querySelector<HTMLButtonElement>('.menu-toggle');
      if (menu) menu.hidden = true;
      toggle?.setAttribute('aria-expanded', 'false');
      toggle?.setAttribute('aria-label', uiText('Open navigation', 'Ouvrir la navigation', 'Abrir navegación'));
    }
    open();
  }));
  close.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });
  dialog.addEventListener('close', () => {
    document.querySelector<HTMLButtonElement>('.search-toggle')?.focus();
  });
  input.addEventListener('input', render);
  document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      event.preventDefault();
      open();
    }
  });
}

type CourseSuggestion = { title: string; level: string; duration: string; summary: string };

function initCourseFinder(): void {
  const dialog = getElement<HTMLDialogElement>('course-finder');
  const form = getElement<HTMLFormElement>('course-finder-form');
  const result = getElement<HTMLElement>('course-finder-result');
  const close = document.querySelector<HTMLButtonElement>('.finder-close');
  const triggers = document.querySelectorAll<HTMLButtonElement>('[data-open-course-finder]');
  if (!dialog || !form || !result || !close || !triggers.length) return;

  const suggestions: Record<string, CourseSuggestion> = {
    web: { title: uiText('Web Development', 'Développement web', 'Desarrollo web'), level: uiText('Beginner', 'Débutant', 'Principiante'), duration: uiText('8 weeks', '8 semaines', '8 semanas'), summary: uiText('Create modern, accessible websites with HTML, CSS, JavaScript and practical projects.', 'Créez des sites modernes et accessibles avec HTML, CSS, JavaScript et des projets pratiques.', 'Crea sitios modernos y accesibles con HTML, CSS, JavaScript y proyectos prácticos.') },
    data: { title: uiText('Data Analytics', 'Analyse de données', 'Análisis de datos'), level: uiText('Beginner', 'Débutant', 'Principiante'), duration: uiText('10 weeks', '10 semaines', '10 semanas'), summary: uiText('Work with spreadsheets, SQL and data visualisation to answer useful questions.', 'Utilisez des feuilles de calcul, SQL et la visualisation pour répondre à des questions concrètes.', 'Usa hojas de cálculo, SQL y visualización de datos para responder preguntas útiles.') },
    ai: { title: uiText('AI & Automation', 'IA et automatisation', 'IA y automatización'), level: uiText('All levels', 'Tous niveaux', 'Todos los niveles'), duration: uiText('6 weeks', '6 semaines', '6 semanas'), summary: uiText('Understand generative AI and build thoughtful workflows for real tasks.', 'Comprenez l’IA générative et créez des processus adaptés à des tâches réelles.', 'Comprende la IA generativa y crea flujos de trabajo útiles para tareas reales.') },
    security: { title: uiText('Cybersecurity Essentials', 'Les bases de la cybersécurité', 'Fundamentos de ciberseguridad'), level: uiText('Beginner', 'Débutant', 'Principiante'), duration: uiText('6 weeks', '6 semaines', '6 semanas'), summary: uiText('Learn core security concepts, safe practices and how to spot common threats.', 'Apprenez les principes de sécurité, les bonnes pratiques et à repérer les menaces courantes.', 'Aprende conceptos de seguridad, prácticas seguras y a detectar amenazas comunes.') },
    cloud: { title: uiText('Cloud Computing', 'Informatique en nuage', 'Computación en la nube'), level: uiText('Intermediate', 'Intermédiaire', 'Intermedio'), duration: uiText('8 weeks', '8 semaines', '8 semanas'), summary: uiText('Explore cloud foundations, deployment concepts and essential cloud services.', 'Découvrez les bases du cloud, du déploiement et des services essentiels.', 'Explora fundamentos de la nube, despliegues y servicios esenciales.') },
    code: { title: uiText('Python Programming', 'Programmation Python', 'Programación con Python'), level: uiText('Beginner', 'Débutant', 'Principiante'), duration: uiText('8 weeks', '8 semaines', '8 semanas'), summary: uiText('Build a strong programming foundation and automate useful everyday work.', 'Acquérez de solides bases en programmation et automatisez des tâches quotidiennes.', 'Construye una base sólida de programación y automatiza tareas cotidianas.') },
  };

  const menu = getElement<HTMLElement>('mobile-nav');
  const menuToggle = document.querySelector<HTMLButtonElement>('.menu-toggle');
  triggers.forEach((trigger) => trigger.addEventListener('click', () => {
    if (menu) menu.hidden = true;
    menuToggle?.setAttribute('aria-expanded', 'false');
    menuToggle?.setAttribute('aria-label', uiText('Open navigation', 'Ouvrir la navigation', 'Abrir navegación'));
    result.hidden = true;
    if (!dialog.open) dialog.showModal();
    window.setTimeout(() => getElement<HTMLSelectElement>('course-interest')?.focus(), 0);
  }));
  close.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const values = new FormData(form);
    const suggestion = suggestions[String(values.get('interest') || '')];
    const experience = String(values.get('experience') || 'exploring');
    if (!suggestion) return;

    result.replaceChildren();
    const eyebrow = document.createElement('span');
    eyebrow.className = 'eyebrow';
    eyebrow.textContent = uiText('Your suggested starting point', 'Votre point de départ suggéré', 'Tu punto de partida sugerido');
    const heading = document.createElement('h3');
    heading.textContent = suggestion.title;
    const detail = document.createElement('p');
    detail.textContent = `${suggestion.summary} ${uiText('Level', 'Niveau', 'Nivel')} ${suggestion.level} · ${suggestion.duration}.`;
    const guidance = document.createElement('p');
    guidance.className = 'finder-guidance';
    guidance.textContent = experience === 'new' && String(values.get('interest')) === 'cloud'
      ? uiText('This course is marked intermediate. Contact our team to check whether it fits your experience.', 'Ce cours est de niveau intermédiaire. Contactez notre équipe pour vérifier s’il correspond à votre expérience.', 'Este curso es de nivel intermedio. Contacta con el equipo para comprobar si se adapta a tu experiencia.')
      : experience === 'some'
        ? uiText('This suggestion starts with the listed course. Ask us about options that build on your experience.', 'Cette suggestion part du cours indiqué. Demandez-nous quelles options peuvent compléter votre expérience.', 'Esta sugerencia parte del curso indicado. Pregúntanos por opciones que amplíen tu experiencia.')
        : uiText('Course availability and schedules can change. Contact us for current details.', 'Les disponibilités et horaires peuvent changer. Contactez-nous pour obtenir les informations actuelles.', 'La disponibilidad y los horarios pueden cambiar. Contáctanos para conocer los detalles actuales.');
    const actions = document.createElement('div');
    actions.className = 'finder-actions';
    const enquire = document.createElement('a');
    enquire.className = 'button button-primary';
    enquire.href = `/contact/?course=${encodeURIComponent(suggestion.title)}`;
    enquire.textContent = uiText('Enquire about this course', 'Se renseigner sur ce cours', 'Consultar sobre este curso');
    const allCourses = document.createElement('a');
    allCourses.href = '/courses/';
    allCourses.textContent = uiText('Browse all courses', 'Voir tous les cours', 'Ver todos los cursos');
    actions.append(enquire, allCourses);
    result.append(eyebrow, heading, detail, guidance, actions);
    result.hidden = false;
  });
}

function initBackToTop(): void {
  const button = document.querySelector<HTMLButtonElement>('.back-to-top');
  if (!button) return;
  const update = (): void => { button.hidden = window.scrollY < 450; };
  update();
  window.addEventListener('scroll', update, { passive: true });
  button.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

function initConfirmForms(): void {
  document.querySelectorAll<HTMLFormElement>('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm || 'Continue with this action?')) event.preventDefault();
    });
  });
}

function initContactForm(): void {
  const form = getElement<HTMLFormElement>('contact-form');
  if (!form) return;
  const response = getElement<HTMLElement>('form-response');
  const submit = form.querySelector<HTMLButtonElement>('[type="submit"]');
  if (!response || !submit) return;

  const fields = ['name', 'email', 'message'];
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    fields.forEach((name) => {
      const field = form.elements.namedItem(name) as HTMLInputElement | HTMLTextAreaElement | null;
      const error = getElement<HTMLElement>(`${name}-error`);
      if (error) error.textContent = '';
      field?.removeAttribute('aria-invalid');
    });
    response.textContent = '';
    if (!form.reportValidity()) return;

    submit.disabled = true;
      submit.textContent = uiText('Sending...', 'Envoi en cours…', 'Enviando…');
    try {
      const result = await fetch(form.action, {
        method: 'POST', body: new FormData(form),
        headers: { Accept: 'application/json' }, credentials: 'same-origin',
      });
      const payload = await result.json() as ApiResponse;
      if (!result.ok) {
        Object.entries(payload.errors || {}).forEach(([name, message]) => {
          const field = form.elements.namedItem(name) as HTMLInputElement | HTMLTextAreaElement | null;
          const error = getElement<HTMLElement>(`${name}-error`);
          if (field && error) { field.setAttribute('aria-invalid', 'true'); error.textContent = localizedApiMessage(message); }
        });
        response.textContent = localizedApiMessage(payload.message || 'We could not send your message. Please try again.');
        response.className = 'form-response is-error';
        form.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
      } else {
        response.textContent = localizedApiMessage(payload.message || 'Your message has been sent.');
        response.className = 'form-response is-success';
        form.reset();
      }
    } catch {
      response.textContent = localizedApiMessage('We could not reach the server. Please try again later.');
      response.className = 'form-response is-error';
    } finally {
      submit.disabled = false;
      submit.innerHTML = uiText('Send your message', 'Envoyer votre message', 'Enviar mensaje') + ' <span aria-hidden="true">&rarr;</span>';
    }
  });
}

function initNewsletterForm(): void {
  const form = getElement<HTMLFormElement>('newsletter-form');
  if (!form) return;
  const response = getElement<HTMLElement>('newsletter-response');
  const submit = form.querySelector<HTMLButtonElement>('[type="submit"]');
  if (!response || !submit) return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    response.textContent = '';
    if (!form.reportValidity()) return;
    submit.disabled = true;
    try {
      const result = await fetch(form.action, {
        method: 'POST', body: new FormData(form),
        headers: { Accept: 'application/json' }, credentials: 'same-origin',
      });
      const payload = await result.json() as ApiResponse;
      response.textContent = localizedApiMessage(payload.message || 'We could not complete your subscription.');
      response.className = result.ok ? 'newsletter-response is-success' : 'newsletter-response is-error';
      if (result.ok) form.reset();
    } catch {
      response.textContent = localizedApiMessage('We could not reach the server. Please try again later.');
      response.className = 'newsletter-response is-error';
    } finally {
      submit.disabled = false;
    }
  });
}

function initCourseFilters(): void {
  const list = getElement<HTMLElement>('course-list');
  const search = getElement<HTMLInputElement>('course-search');
  const level = getElement<HTMLSelectElement>('course-level');
  const count = getElement<HTMLElement>('course-count');
  const empty = getElement<HTMLElement>('empty-courses');
  if (!list || !search || !level || !count || !empty) return;

  const cards = Array.from(list.querySelectorAll<HTMLElement>('.course-card'));
  const queryParams = new URLSearchParams(window.location.search);
  search.value = queryParams.get('search') || '';
  const requestedLevel = queryParams.get('level') || 'all';
  if (Array.from(level.options).some((option) => option.value === requestedLevel)) level.value = requestedLevel;
  const filter = (): void => {
    const query = search.value.trim().toLowerCase();
    let visible = 0;
    cards.forEach((card) => {
      const matches = ((card.dataset.search || '').includes(query) || (card.textContent || '').toLowerCase().includes(query))
        && (level.value === 'all' || card.dataset.level === level.value);
      card.hidden = !matches;
      if (matches) visible += 1;
    });
    count.textContent = `${visible} ${visible === 1 ? uiText('course', 'cours', 'curso') : uiText('courses', 'cours', 'cursos')}`;
    empty.hidden = visible !== 0;
  };
  search.addEventListener('input', filter);
  level.addEventListener('change', filter);
  filter();
}

function initCourseCompare(): void {
  const list = getElement<HTMLElement>('course-list');
  const bar = getElement<HTMLElement>('course-compare-bar');
  const count = getElement<HTMLElement>('course-compare-count');
  const open = getElement<HTMLButtonElement>('course-compare-open');
  const clear = getElement<HTMLButtonElement>('course-compare-clear');
  const dialog = getElement<HTMLDialogElement>('course-compare-dialog');
  const close = document.querySelector<HTMLButtonElement>('.course-compare-close');
  const body = getElement<HTMLTableSectionElement>('course-compare-body');
  if (!list || !bar || !count || !open || !clear || !dialog || !close || !body) return;

  const choices = Array.from(list.querySelectorAll<HTMLInputElement>('[data-course-compare]'));
  const selectedCards = (): HTMLElement[] => choices
    .filter((choice) => choice.checked)
    .map((choice) => choice.closest<HTMLElement>('.course-card'))
    .filter((card): card is HTMLElement => card !== null);

  const update = (): void => {
    const selected = selectedCards();
    bar.hidden = selected.length === 0;
    count.textContent = `${selected.length} ${selected.length === 1 ? uiText('course', 'cours', 'curso') : uiText('courses', 'cours', 'cursos')} ${uiText('selected', 'sélectionné(s)', 'seleccionados')}`;
    open.disabled = selected.length < 2;
    open.textContent = selected.length < 2 ? uiText('Select one more to compare', 'Sélectionnez-en un autre à comparer', 'Selecciona otro para comparar') : `${uiText('Compare selected', 'Comparer la sélection', 'Comparar seleccionados')} (${selected.length})`;
    choices.forEach((choice) => {
      choice.disabled = selected.length >= 3 && !choice.checked;
    });
  };

  choices.forEach((choice) => choice.addEventListener('change', update));
  clear.addEventListener('click', () => {
    choices.forEach((choice) => { choice.checked = false; choice.disabled = false; });
    update();
  });
  open.addEventListener('click', () => {
    const selected = selectedCards();
    if (selected.length < 2) return;
    body.replaceChildren();
    selected.forEach((card) => {
      const row = document.createElement('tr');
      const title = document.createElement('th');
      title.scope = 'row';
      title.textContent = card.dataset.courseTitle || uiText('Course', 'Cours', 'Curso');
      const levelCell = document.createElement('td');
      levelCell.textContent = card.dataset.courseLevel || uiText('Ask us', 'Nous consulter', 'Consultar');
      const durationCell = document.createElement('td');
      durationCell.textContent = card.dataset.courseDuration || uiText('Ask us', 'Nous consulter', 'Consultar');
      const description = document.createElement('td');
      description.textContent = card.dataset.courseDescription || '';
      row.append(title, levelCell, durationCell, description);
      body.append(row);
    });
    dialog.showModal();
  });
  close.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });
  dialog.addEventListener('close', () => open.focus());
  update();
}

function initFaqFilter(): void {
  const search = getElement<HTMLInputElement>('faq-search');
  const count = getElement<HTMLElement>('faq-count');
  const empty = getElement<HTMLElement>('faq-empty');
  const list = document.querySelector<HTMLElement>('.faq-list');
  if (!search || !count || !empty || !list) return;
  const questions = Array.from(list.querySelectorAll<HTMLElement>('details'));
  const filter = (): void => {
    const query = search.value.trim().toLowerCase();
    let visible = 0;
    questions.forEach((question) => {
      const match = question.textContent?.toLowerCase().includes(query) ?? false;
      question.hidden = !match;
      if (match) visible += 1;
    });
    count.textContent = `${visible} ${visible === 1 ? uiText('answer', 'réponse', 'respuesta') : uiText('answers', 'réponses', 'respuestas')}`;
    empty.hidden = visible !== 0;
  };
  search.addEventListener('input', filter);
}

function initLanguageSwitchers(): void {
  document.querySelectorAll<HTMLFormElement>('[data-language-switcher]').forEach((form) => {
    const select = form.querySelector<HTMLSelectElement>('select[name="lang"]');
    if (!select) return;
    select.addEventListener('change', () => form.requestSubmit());
  });
}

initPageLoading();
document.addEventListener('DOMContentLoaded', () => {
  initLanguageSwitchers();
  initSiteSearch();
  initCourseFinder();
  initBackToTop();
  initConfirmForms();
  initContactForm();
  initNewsletterForm();
  initCourseFilters();
  initCourseCompare();
  initFaqFilter();
});
