  <!-- Scripts -->
  <script src="<?= URL ?>/public/assets/js/core/popper.min.js"></script>
  <script src="<?= URL ?>/public/assets/js/core/bootstrap.min.js"></script>
  <script src="<?= URL ?>/public/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="<?= URL ?>/public/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="<?= URL ?>/public/assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <script>
    function initCustomNavPills() {
      var wrappers = document.querySelectorAll('.custom-nav-wrapper');
      wrappers.forEach(function (wrapper) {
        var navPills = wrapper.querySelector('.nav-pills');
        if (!navPills) return;

        var movingTab = wrapper.querySelector('.moving-tab');
        if (!movingTab) {
          movingTab = document.createElement('div');
          movingTab.className = 'moving-tab';
          wrapper.appendChild(movingTab);
        }

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

        var currentActive = navPills.querySelector('.nav-link.active') || navPills.querySelector('.nav-link');
        if (currentActive) updateTabPosition(currentActive);

        var tabLinks = navPills.querySelectorAll('.nav-link');
        tabLinks.forEach(function (tab) {
          tab.addEventListener('shown.bs.tab', function (e) {
            updateTabPosition(e.target);
          });
          tab.addEventListener('click', function (e) {
            updateTabPosition(e.currentTarget);
          });
        });
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      initCustomNavPills();
    });

    document.getElementById("btn_modo-login").addEventListener("click", verLogin);
    document.getElementById("btn_modo-registro").addEventListener("click", verRegistro);
    window.addEventListener("resize", AnchoPagina);

    var contenedor_deslizable = document.querySelector(".contenedor_login-deslizable");
    var formulario_login = document.querySelector(".formulario_login");
    var formulario_registro = document.querySelector(".formulario_registro");
    var caja_trasera_login = document.querySelector(".caja_trasera-login");
    var caja_trasera_registro = document.querySelector(".caja_trasera-registro");

    function AnchoPagina() {
      if (window.innerWidth > 850) {
        caja_trasera_login.style.display = "block";
        caja_trasera_registro.style.display = "block";
      } else {
        caja_trasera_registro.style.display = "block";
        caja_trasera_registro.style.opacity = "1";
        caja_trasera_login.style.display = "none";
        formulario_login.style.display = "flex";
        formulario_registro.style.display = "none";
        contenedor_deslizable.style.left = "0px";
      }
    }
    AnchoPagina();

    function verLogin() {
      if (window.innerWidth > 850) {
        formulario_registro.style.display = "none";
        contenedor_deslizable.style.left = "0px";
        formulario_login.style.display = "flex";
        caja_trasera_registro.style.opacity = "1";
        caja_trasera_login.style.opacity = "0";
      } else {
        formulario_registro.style.display = "none";
        contenedor_deslizable.style.left = "0px";
        formulario_login.style.display = "flex";
        caja_trasera_registro.style.display = "block";
        caja_trasera_login.style.display = "none";
      }
    }

    function verRegistro() {
      if (window.innerWidth > 850) {
        formulario_registro.style.display = "flex";
        contenedor_deslizable.style.left = "50%";
        formulario_login.style.display = "none";
        caja_trasera_registro.style.opacity = "0";
        caja_trasera_login.style.opacity = "1";
      } else {
        formulario_registro.style.display = "flex";
        contenedor_deslizable.style.left = "0px";
        formulario_login.style.display = "none";
        caja_trasera_registro.style.display = "none";
        caja_trasera_login.style.display = "block";    
        caja_trasera_login.style.opacity = "1";    
      }
      setTimeout(initCustomNavPills, 100);
    }

    function goToStepSindicato(stepNumber) {
      $('.step-content-sindicato').addClass('d-none');
      $('#step-sindicato-' + stepNumber).removeClass('d-none');

      $('.step-footer-sindicato').addClass('d-none');
      $('#footer-sindicato-' + stepNumber).removeClass('d-none');

      $('.step-indicator button')
        .removeClass('bg-gradient-success text-white')
        .addClass('bg-gray-200 text-secondary');
      
      $('.step-indicator button span')
        .removeClass('text-white')
        .addClass('text-secondary');
      
      $('.step-indicator span.d-block')
        .removeClass('text-dark font-weight-bold')
        .addClass('text-secondary');

      for (let i = 1; i <= stepNumber; i++) {
        let indicatorBtn = $('#indicator-sindicato-' + i + ' button');
        let indicatorIcon = $('#indicator-sindicato-' + i + ' button span');
        let indicatorSpan = $('#indicator-sindicato-' + i + ' span.d-block');

        indicatorBtn.removeClass('bg-gray-200 text-secondary').addClass('bg-gradient-success text-white');
        indicatorIcon.removeClass('text-secondary').addClass('text-white');
        indicatorSpan.removeClass('text-secondary').addClass('text-dark font-weight-bold');
      }

      if (stepNumber === 3) {
        $('#btn-sindicato-3-nav').removeAttr('disabled');
      }
    }

    $('#formRegistroSindicato').on('submit', function(e) {
      e.preventDefault();
      const p1 = $('#pass_sind1').val();
      const p2 = $('#pass_sind2').val();

      if (p1 !== p2) {
        alert('Las contraseñas no coinciden. Revisa nuevamente.');
        return;
      }
      alert('¡Registro de Sindicato completado con éxito!');
    });
  </script>

  </body>
</html>