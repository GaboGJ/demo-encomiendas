/**
 * Inicializa las pestañas animadas responsive en contenedores `.custom-nav-wrapper`.
 */
function initCustomNavPills() {
  var wrappers = document.querySelectorAll('.custom-nav-wrapper');

  wrappers.forEach(function (wrapper) {
    var navPills = wrapper.querySelector('.custom-nav-pills');
    if (!navPills) return;

    var movingTab = wrapper.querySelector('.moving-tab');
    if (!movingTab) {
      movingTab = document.createElement('div');
      movingTab.className = 'moving-tab';
      wrapper.appendChild(movingTab);
    }

    // Calcula coordenadas 2D (soporta filas y columnas)
    function updateTabPosition(activeLink) {
      if (!activeLink) return;

      var navItem = activeLink.closest('.nav-item');
      if (!navItem) return;

      var leftOffset = navItem.offsetLeft + 4;
      var topOffset = navItem.offsetTop + 4;
      var tabWidth = navItem.offsetWidth;
      var tabHeight = navItem.offsetHeight;

      movingTab.style.transform = 'translate3d(' + (leftOffset - 4) + 'px, ' + (topOffset - 4) + 'px, 0px)';
      movingTab.style.width = tabWidth + 'px';
      movingTab.style.height = tabHeight + 'px';
    }

    function setInitialPosition() {
      var currentActive = navPills.querySelector('.nav-link.active') || navPills.querySelector('.nav-link');
      if (currentActive) {
        updateTabPosition(currentActive);
      }
    }

    setInitialPosition();

    var tabLinks = navPills.querySelectorAll('.nav-link');
    tabLinks.forEach(function (tab) {
      // Compatibilidad con eventos Bootstrap (data-bs-toggle="pill")
      tab.addEventListener('shown.bs.tab', function (e) {
        updateTabPosition(e.target);
      });

      // Compatibilidad con clics directos u onclicks personalizados
      tab.addEventListener('click', function (e) {
        tabLinks.forEach(function (l) { l.classList.remove('active'); });
        e.currentTarget.classList.add('active');
        updateTabPosition(e.currentTarget);
      });
    });

    if (window.ResizeObserver) {
      var resizeObserver = new ResizeObserver(function () {
        var active = navPills.querySelector('.nav-link.active');
        if (active) updateTabPosition(active);
      });
      resizeObserver.observe(wrapper);
    } else {
      window.addEventListener('resize', function () {
        var active = navPills.querySelector('.nav-link.active');
        if (active) updateTabPosition(active);
      });
    }
  });
}

document.addEventListener('DOMContentLoaded', function () {
  initCustomNavPills();
});

window.addEventListener('load', function () {
  setTimeout(initCustomNavPills, 100);
});