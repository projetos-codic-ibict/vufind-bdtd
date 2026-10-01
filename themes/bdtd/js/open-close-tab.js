/* BDTD: a aba de metadados do registro começa fechada, abre no clique e fecha no clique seguinte */
(function openCloseTab() {
  let wasOpen = false;

  function openPane(tab) {
    const li = tab.parentNode;
    const top = tab.closest('.record-tabs');
    if (!li || !top) {
      return null;
    }
    const pane = top.querySelector('.tab-pane.' + li.dataset.tab + '-tab.active');
    return tab.classList.contains('active') && pane ? pane : null;
  }

  // O record.js abre a aba já no foco (antes do clique), então o estado é lido no pointerdown
  document.addEventListener('pointerdown', (event) => {
    const tab = event.target.closest('.record-tabs .nav-tabs a');
    wasOpen = tab !== null && openPane(tab) !== null;
  }, true);

  document.addEventListener('click', (event) => {
    // Só cliques do usuário: o record.js também clica na aba ao aplicar o #hash da URL
    const tab = event.isTrusted ? event.target.closest('.record-tabs .nav-tabs a') : null;
    const pane = tab ? openPane(tab) : null;
    if (!pane || (event.detail > 0 && !wasOpen)) {
      return;
    }
    event.preventDefault();
    event.stopImmediatePropagation();
    pane.classList.remove('active');
    tab.classList.remove('active');
    tab.setAttribute('aria-selected', 'false');
    if (window.location.hash) {
      window.history.replaceState({}, '', window.location.pathname + window.location.search);
    }
  }, true);
})();
