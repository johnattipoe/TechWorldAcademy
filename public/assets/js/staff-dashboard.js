(() => {
  const loader = document.querySelector('[data-staff-loader]');
  const hideLoader = () => loader?.classList.add('is-hidden');
  const showLoader = (label = 'Opening your workspace…') => {
    if (!loader) return;
    const labelNode = loader.querySelector('[data-staff-loader-label]');
    if (labelNode) labelNode.textContent = label;
    loader.classList.remove('is-hidden');
  };
  requestAnimationFrame(hideLoader);
  window.addEventListener('pageshow', hideLoader);
  document.addEventListener('click', (event) => {
    const clickTarget = event.target instanceof Element ? event.target : null;
    const link = clickTarget?.closest('a[href]');
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    if (link.target && link.target !== '_self' || link.hasAttribute('download') || link.getAttribute('href')?.startsWith('#')) return;
    const destination = new URL(link.href, window.location.href);
    if (destination.origin !== window.location.origin || destination.href === window.location.href) return;
    showLoader('Loading page…');
  });

  const form = document.querySelector('[data-staff-search-form]');
  const input = form?.querySelector('input[type="search"]');
  if (!form || !input) return;
  const status = form.querySelector('[data-staff-search-status]');

  const searchable = () => document.querySelectorAll(
    '.staff-main .admin-control-grid > a, .staff-main .admin-inquiry, .staff-main .instructor-course-card, .staff-main .instructor-submission-card, .staff-main .staff-roster-card, .staff-main .staff-data-table tbody tr'
  );
  document.querySelectorAll('.staff-table-card').forEach((card) => {
    const table = card.querySelector('table');
    const heading = card.querySelector('h2');
    if (!table || !heading) return;
    const exportButton = document.createElement('button');
    exportButton.type = 'button';
    exportButton.className = 'staff-table-export';
    exportButton.textContent = 'Export CSV';
    exportButton.setAttribute('aria-label', `Export ${heading.textContent.trim()} rows as CSV`);
    exportButton.addEventListener('click', () => {
      const rows = [...table.querySelectorAll('tr')]
        .filter((row) => !row.hidden && !row.querySelector('.staff-table-empty'))
        .map((row) => [...row.querySelectorAll('th, td')].map((cell) => `"${cell.innerText.trim().replace(/"/g, '""')}"`).join(','));
      if (!rows.length) return;
      const fileName = `${heading.textContent.trim().toLocaleLowerCase().replace(/[^a-z0-9]+/g, '-') || 'staff-report'}.csv`;
      const file = new Blob([`\uFEFF${rows.join('\r\n')}`], { type: 'text/csv;charset=utf-8' });
      const url = URL.createObjectURL(file);
      const download = document.createElement('a');
      download.href = url;
      download.download = fileName;
      download.hidden = true;
      document.body.append(download);
      download.click();
      download.remove();
      window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
    card.append(exportButton);
  });

  const applyFilter = () => {
    const query = input.value.trim().toLocaleLowerCase();
    let visible = 0;
    searchable().forEach((item) => {
      item.hidden = query !== '' && !item.textContent.toLocaleLowerCase().includes(query);
      if (!item.hidden) visible++;
    });
    if (status) {
      status.textContent = query && visible === 0 ? 'No matching items on this page.' : '';
      status.hidden = !status.textContent;
    }
  };

  input.addEventListener('input', applyFilter);
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    applyFilter();
    input.focus();
  });
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      input.value = '';
      applyFilter();
      input.blur();
    }
  });
  document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLocaleLowerCase() === 'k') {
      event.preventDefault();
      input.focus();
      input.select();
    }
  });
})();
