/* =========================================================
   utils-ui.js
   Utilidades compartidas por varias vistas del dashboard.
   Expone: window.UIUtils y window.initNavPillsAnimados
   ========================================================= */
(function (w, d) {
  'use strict';

  var UIUtils = {
    escaparHtml: function (str) {
      var div = d.createElement('div');
      div.textContent = str == null ? '' : String(str);
      return div.innerHTML;
    },

    valorInput: function (id) {
      var el = d.getElementById(id);
      return el ? el.value.trim() : '';
    },

    // Asigna valor a un input de Material Dashboard y sube la etiqueta flotante
    setValorInput: function (id, valor) {
      var el = d.getElementById(id);
      if (!el) return;
      el.value = valor || '';
      var grupo = el.closest('.input-group');
      if (grupo) grupo.classList.toggle('is-filled', el.value !== '');
    }
  };

  /**
   * Pestañas animadas (.custom-nav-wrapper > .custom-nav-pills + .moving-tab).
   * Se puede llamar varias veces (por ejemplo al mostrar un paso oculto):
   * los listeners se registran una sola vez y solo se reposiciona la pestaña.
   * También reposiciona al cambiar el tamaño de la pantalla (responsivo).
   */
  function initNavPillsAnimados() {
    d.querySelectorAll('.custom-nav-wrapper').forEach(function (wrapper) {
      var navPills = wrapper.querySelector('.custom-nav-pills');
      if (!navPills) return;

      var movingTab = wrapper.querySelector('.moving-tab');
      if (!movingTab) {
        movingTab = d.createElement('div');
        movingTab.className = 'moving-tab';
        wrapper.appendChild(movingTab);
      }

      function posicionar() {
        var activo = navPills.querySelector('.nav-link.active') || navPills.querySelector('.nav-link');
        if (!activo) return;
        var item = activo.closest('.nav-item');
        if (!item || !item.offsetWidth) return; // oculto: se reposiciona al mostrarse
        movingTab.style.transform = 'translate3d(' + item.offsetLeft + 'px, ' + item.offsetTop + 'px, 0px)';
        movingTab.style.width = item.offsetWidth + 'px';
        movingTab.style.height = item.offsetHeight + 'px';
      }

      if (!wrapper.dataset.navInit) {
        wrapper.dataset.navInit = '1';

        navPills.querySelectorAll('.nav-link').forEach(function (tab) {
          tab.addEventListener('click', function (e) {
            navPills.querySelectorAll('.nav-link').forEach(function (l) { l.classList.remove('active'); });
            e.currentTarget.classList.add('active');
            posicionar();
          });
        });

        if (w.ResizeObserver) {
          new ResizeObserver(posicionar).observe(wrapper);
        } else {
          w.addEventListener('resize', posicionar);
        }
      }

      posicionar();
    });
  }

  w.UIUtils = UIUtils;
  w.initNavPillsAnimados = initNavPillsAnimados;
})(window, document);