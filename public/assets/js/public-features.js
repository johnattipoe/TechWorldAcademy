(() => {
  const search = document.querySelector('[data-help-search]');
  const results = document.querySelector('[data-help-result]');
  const cards = [...document.querySelectorAll('[data-help-item]')];
  if (!search || !cards.length) return;

  const filterCards = () => {
    const query = search.value.trim().toLocaleLowerCase();
    let visible = 0;
    cards.forEach((card) => {
      const matches = query === '' || card.textContent.toLocaleLowerCase().includes(query);
      card.hidden = !matches;
      if (matches) visible++;
    });
    if (results) results.textContent = query ? `${visible} help topic${visible === 1 ? '' : 's'} found.` : '';
  };
  search.addEventListener('input', filterCards);
  search.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      search.value = '';
      filterCards();
      search.blur();
    }
  });
})();
