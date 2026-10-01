/* global VuFind */
/*
 * BDTD: janela (lightbox) centrada como no Bootstrap 3 do legado. O Bootstrap 3 reservava à
 * direita da janela a largura da barra de rolagem da página (quando a própria janela não
 * rola); o 5 esconde a barra antes de medir e centra a janela na tela inteira.
 */
(function bdtdModal() {
  let scrollbarWidth = 0;

  function adjust(modal) {
    const overflowing = modal.scrollHeight > document.documentElement.clientHeight;
    modal.style.paddingRight = scrollbarWidth > 0 && !overflowing ? scrollbarWidth + 'px' : '';
  }

  document.addEventListener('show.bs.modal', (event) => {
    scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    adjust(event.target);
  });

  // O conteúdo do lightbox chega depois que a janela abre: reavaliar quando ele é desenhado
  VuFind.listen('lightbox.rendered', (params) => {
    const modal = params && params.container ? params.container[0] || params.container : null;
    if (modal) {
      adjust(modal);
    }
  });
})();
