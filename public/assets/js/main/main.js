(() => {
    'use strict';

    const header = document.querySelector('.site-header');
    if (!header) return;

    const progress = document.createElement('div');
    progress.className = 'reading-progress';
    progress.setAttribute('aria-hidden', 'true');
    const progressFill = document.createElement('span');
    progress.append(progressFill);
    header.append(progress);

    let framePending = false;
    const updateHeader = () => {
        const scrollable = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        const percentage = Math.min(1, Math.max(0, window.scrollY / scrollable));
        header.classList.toggle('is-scrolled', window.scrollY > 8);
        progressFill.style.transform = `scaleX(${percentage})`;
        framePending = false;
    };
    const queueUpdate = () => {
        if (framePending) return;
        framePending = true;
        window.requestAnimationFrame(updateHeader);
    };

    updateHeader();
    window.addEventListener('scroll', queueUpdate, { passive: true });
    window.addEventListener('resize', queueUpdate, { passive: true });
    window.addEventListener('pageshow', queueUpdate);
})();