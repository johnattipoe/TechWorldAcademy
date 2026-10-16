(() => {
    'use strict';

    const header = document.querySelector('.site-header');
    if (!header) return;

    document.querySelectorAll('[data-learning-promo-close]').forEach((button) => {
        button.addEventListener('click', () => {
            const promo = button.closest('[data-learning-promo]');
            if (promo) promo.hidden = true;
        });
    });

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

    const glossarySearch = document.querySelector('#glossary-search-input');
    const glossaryTerms = Array.from(document.querySelectorAll('.resource-glossary > div'));
    const glossaryCount = document.querySelector('#glossary-result-count');
    const glossaryEmpty = document.querySelector('#glossary-empty');
    if (glossarySearch && glossaryTerms.length) {
        const updateGlossary = () => {
            const query = glossarySearch.value.trim().toLocaleLowerCase();
            let visibleCount = 0;
            glossaryTerms.forEach((term) => {
                const matches = term.textContent.toLocaleLowerCase().includes(query);
                term.hidden = !matches;
                if (matches) visibleCount += 1;
            });
            if (glossaryCount) {
                glossaryCount.textContent = query
                    ? `${visibleCount} ${visibleCount === 1 ? 'term' : 'terms'} found`
                    : `Showing all ${visibleCount} terms`;
            }
            if (glossaryEmpty) glossaryEmpty.hidden = visibleCount !== 0;
        };
        glossarySearch.addEventListener('input', updateGlossary);
        updateGlossary();
    }

    document.querySelectorAll('[data-print-certificate]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });

})();
