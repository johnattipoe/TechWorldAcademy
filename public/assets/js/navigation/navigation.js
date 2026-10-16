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
    const authDialog = document.querySelector('[data-learning-auth]');

    if (authDialog instanceof HTMLDialogElement) {
        const title = authDialog.querySelector('[data-auth-title]');
        const description = authDialog.querySelector('[data-auth-description]');
        const tabs = Array.from(authDialog.querySelectorAll('[data-auth-tab]'));
        const forms = Array.from(authDialog.querySelectorAll('[data-auth-form]'));
        const socialStatus = authDialog.querySelector('[data-social-status]');
        let previousFocus = null;
        let continueTo = '/student/';

        const setAuthMode = (mode) => {
            const isRegister = mode === 'register';
            title.textContent = isRegister ? 'Create your free account' : 'Welcome back';
            description.textContent = isRegister ? 'Create an account to explore courses and track your progress.' : 'Sign in to continue your learning journey.';
            tabs.forEach((tab) => tab.setAttribute('aria-selected', String(tab.dataset.authTab === mode)));
            forms.forEach((form) => {
                const active = form.dataset.authForm === mode;
                form.hidden = !active;
                form.querySelectorAll('input').forEach((input) => { input.disabled = !active; });
            });
            authDialog.querySelectorAll('input[name="continue"]').forEach((input) => { input.value = continueTo; });
        };

        const openAuth = (mode, source) => {
            previousFocus = source instanceof HTMLElement ? source : document.activeElement;
            setAuthMode(mode);
            if (!authDialog.open) authDialog.showModal();
            authDialog.querySelector(`[data-auth-form="${mode}"] input:not([type="hidden"])`)?.focus();
        };

        document.addEventListener('click', (event) => {
            const trigger = event.target instanceof Element ? event.target.closest('[data-auth-open]') : null;
            if (trigger) {
                event.preventDefault();
                openAuth(trigger.dataset.authOpen === 'register' ? 'register' : 'login', trigger);
                return;
            }
            const authLink = event.target instanceof Element ? event.target.closest('a[href^="/login/"], a[href^="/register/"]') : null;
            if (authLink && authDialog) {
                event.preventDefault();
                const mode = authLink.pathname === '/register/' ? 'register' : 'login';
                const target = new URL(authLink.href);
                continueTo = target.searchParams.get('continue') || '/student/';
                openAuth(mode, authLink);
            }
        });

        tabs.forEach((tab) => tab.addEventListener('click', () => setAuthMode(tab.dataset.authTab)));
        authDialog.querySelector('[data-auth-close]')?.addEventListener('click', () => authDialog.close());
        authDialog.querySelectorAll('[data-social-provider]').forEach((button) => button.addEventListener('click', () => {
            const provider = button.dataset.socialProvider;
            const names = { google: 'Google', facebook: 'Facebook', apple: 'Apple' };
            socialStatus.textContent = `${names[provider] || 'Social'} sign-in needs to be enabled in the TechWorld Firebase project before it can be used. Please continue with email for now.`;
        }));
        authDialog.addEventListener('close', () => {
            if (previousFocus instanceof HTMLElement && previousFocus.isConnected) previousFocus.focus();
        });
        authDialog.addEventListener('click', (event) => {
            if (event.target === authDialog) authDialog.close();
        });
        setAuthMode('login');
    }

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
