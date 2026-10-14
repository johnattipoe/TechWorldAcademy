(() => {
    'use strict';

    const language = document.documentElement.lang;
    const messages = {
        en: { open: 'Open navigation', close: 'Close navigation' },
        fr: { open: 'Ouvrir la navigation', close: 'Fermer la navigation' },
        es: { open: 'Abrir navegación', close: 'Cerrar navegación' },
    };
    const text = messages[language] || messages.en;
    const dropdowns = Array.from(document.querySelectorAll('[data-nav-dropdown]'));
    const toggle = document.querySelector('.menu-toggle');
    const menu = document.getElementById('mobile-nav');

    const closeDropdowns = () => dropdowns.forEach((dropdown) => { dropdown.open = false; });
    const closeMenu = (restoreFocus = false) => {
        if (!toggle || !menu || menu.hidden) return;
        menu.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', text.open);
        closeDropdowns();
        if (restoreFocus) toggle.focus();
    };

    document.addEventListener('click', (event) => {
        dropdowns.forEach((dropdown) => {
            if (dropdown.open && !dropdown.contains(event.target)) dropdown.open = false;
        });
        if (menu && !menu.hidden && event.target instanceof Node
            && !menu.contains(event.target) && !toggle?.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        const openDropdown = dropdowns.find((dropdown) => dropdown.open);
        if (openDropdown) {
            event.preventDefault();
            openDropdown.open = false;
            openDropdown.querySelector('summary')?.focus();
            return;
        }
        closeMenu(true);
    });

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const open = toggle.getAttribute('aria-expanded') !== 'true';
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? text.close : text.open);
            menu.hidden = !open;
            if (!open) closeDropdowns();
        });

        menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => closeMenu()));
    }
})();