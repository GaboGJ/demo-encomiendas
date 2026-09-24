<!-- Contenido Exclusivo de Autenticación (Login / Registro deslizante) -->
<main class="main-content mt-6 mt-md-8 d-flex align-items-center py-3">
    <div class="container my-auto">
      <div class="contenedor_todo">
        
        <div class="caja_trasera bg-gradient-success shadow-success">
          <div class="caja_trasera-login">
            <h3 class="text-white font-weight-bolder mb-2">¿Ya estás registrado?</h3>
            <p class="text-xs text-white opacity-9 mb-4">Ingresa con tus credenciales para consultar tus encomiendas o gestionar tu sindicato</p>
            <button id="btn_modo-login" class="btn btn-outline-white border-radius-lg px-4 text-capitalize mb-0">Iniciar Sesión</button>
          </div>

          <div class="caja_trasera-registro">
            <h3 class="text-white font-weight-bolder mb-2">¿Eres nuevo?</h3>
            <p class="text-xs text-white opacity-9 mb-4">Regístrate como Cliente para rastrear tus envíos o registra un Sindicato de Transporte</p>
            <button id="btn_modo-registro" class="btn btn-outline-white border-radius-lg px-4 text-capitalize mb-0">Registrarse</button>
          </div>
        </div>

        <div class="contenedor_login-deslizable">
          
          <!-- LOGIN -->
          <div class="card-auth-container formulario_login">
            <div class="d-flex align-items-center mb-2">
              <div class="icon icon-shape icon-sm bg-gradient-success shadow-success text-center border-radius-md me-2 d-flex align-items-center justify-content-center">
                <span class="material-symbols-rounded text-white text-sm">lock</span>
              </div>
              <h4 class="font-weight-bolder text-dark mb-0">Iniciar Sesión</h4>
            </div>
            <p class="text-xs text-secondary mb-4">Acceso unificado para Clientes, Administradores y Choferes</p>

            <form id="formLoginGeneral">
              <div class="mb-3">
                <label class="label-custom">Correo Electrónico o Usuario</label>
                <input type="text" class="input-custom" placeholder="ejemplo@correo.com">
              </div>

              <div class="mb-3">
                <label class="label-custom">Contraseña</label>
                <input type="password" class="input-custom" placeholder="••••••••">
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div class="form-check form-switch ps-0">
                  <input class="form-check-input ms-auto" type="checkbox" id="rememberMe" checked>
                  <label class="form-check-label text-xs text-secondary mb-0 ms-2" for="rememberMe">Recordarme</label>
                </div>
                <a href="javascript:;" class="text-xs text-success font-weight-bold">¿Olvidaste tu contraseña?</a>
              </div>

<button type="button" onclick="window.location.href='<?= URL; ?>/dashboard'" class="btn bg-gradient-success border-radius-lg w-100 font-weight-bold text-capitalize shadow-success py-2 mb-0">
    Entrar al Sistema <span class="material-symbols-rounded text-sm ms-1 align-middle">arrow_forward</span>
</button>
            </form>
          </div>

          <!-- REGISTRO -->
          <div class="card-auth-container formulario_registro" style="display: none;">
            <h4 class="font-weight-bolder text-dark mb-2">Crear Cuenta</h4>

            <div class="custom-nav-wrapper mb-3">
              <ul class="nav nav-pills" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="tab-cliente" data-bs-toggle="pill" data-bs-target="#pills-cliente" type="button" role="tab" aria-selected="true">
                    <span class="material-symbols-rounded text-sm me-1">person</span> Cliente (Rastreo)
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="tab-sindicato" data-bs-toggle="pill" data-bs-target="#pills-sindicato" type="button" role="tab" aria-selected="false">
                    <span class="material-symbols-rounded text-sm me-1">directions_bus</span> Sindicato
                  </button>
                </li>
              </ul>
            </div>

            <div class="tab-content" id="pills-tabContent">
              
              <!-- CLIENTE -->
              <div class="tab-pane fade show active" id="pills-cliente" role="tabpanel">
                <p class="text-xs text-secondary mb-3">Regístrate para ver el estado en tiempo real de tus encomiendas</p>
                <form id="formRegistroCliente">
                  <div class="row g-2">
                    <div class="col-12 col-sm-6">
                      <label class="label-custom">Nombres *</label>
                      <input type="text" class="input-custom" placeholder="Ej. Juan Carlos" required>
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="label-custom">Apellidos *</label>
                      <input type="text" class="input-custom" placeholder="Ej. Pérez" required>
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="label-custom">Teléfono / WhatsApp *</label>
                      <input type="tel" class="input-custom" placeholder="Ej. 78512345" required>
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="label-custom">N° C.I. / Documento *</label>
                      <input type="text" class="input-custom" placeholder="Ej. 7845123 Beni" required>
                    </div>

                    <div class="col-12">
                      <label class="label-custom">Correo Electrónico *</label>
                      <input type="email" class="input-custom" placeholder="cliente@correo.com" required>
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="label-custom">Contraseña *</label>
                      <input type="password" class="input-custom" placeholder="Mínimo 6 caracteres" required>
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="label-custom">Confirmar Contraseña *</label>
                      <input type="password" class="input-custom" placeholder="Repite la contraseña" required>
                    </div>

                    <div class="col-12 mt-2">
                      <div class="form-check p-0 ms-1">
                        <input class="form-check-input" type="checkbox" id="checkClienteTerminos" required>
                        <label class="form-check-label text-xs text-secondary ms-1" for="checkClienteTerminos">
                          Acepto Términos de Servicio y Rastreo
                        </label>
                      </div>
                    </div>

                    <div class="col-12 mt-2">
                      <button type="submit" class="btn bg-gradient-success border-radius-lg w-100 font-weight-bold text-capitalize shadow-success py-2 text-white">
                        Registrarme como Cliente
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              <!-- SINDICATO EN PASOS -->
              <div class="tab-pane fade" id="pills-sindicato" role="tabpanel">
                <div class="border-bottom pb-2 mb-3">
                  <div class="d-flex justify-content-between align-items-center text-center">
                    <div class="step-indicator flex-fill" id="indicator-sindicato-1">
                      <button type="button" class="btn btn-icon-only btn-rounded bg-gradient-success text-white mb-0 btn-sm shadow-none" onclick="goToStepSindicato(1)">
                        <span class="material-symbols-rounded text-xs text-white align-middle">domain</span>
                      </button>
                      <span class="d-block text-xxs font-weight-bold text-dark">1. Institución</span>
                    </div>

                    <div class="border-top border-2 flex-fill opacity-3"></div>

                    <div class="step-indicator flex-fill" id="indicator-sindicato-2">
                      <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-0 btn-sm shadow-none" onclick="goToStepSindicato(2)">
                        <span class="material-symbols-rounded text-xs align-middle">badge</span>
                      </button>
                      <span class="d-block text-xxs font-weight-bold text-secondary">2. Legal</span>
                    </div>

                    <div class="border-top border-2 flex-fill opacity-3"></div>

                    <div class="step-indicator flex-fill" id="indicator-sindicato-3">
                      <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-0 btn-sm shadow-none" id="btn-sindicato-3-nav" disabled>
                        <span class="material-symbols-rounded text-xs align-middle">admin_panel_settings</span>
                      </button>
                      <span class="d-block text-xxs font-weight-bold text-secondary">3. Acceso</span>
                    </div>
                  </div>
                </div>

                <form id="formRegistroSindicato">
                  <div class="step-content-sindicato" id="step-sindicato-1">
                    <div class="row g-2">
                      <div class="col-12">
                        <label class="label-custom">Nombre del Sindicato / Empresa *</label>
                        <input type="text" class="input-custom" placeholder="Ej. Sindicato Misto 18 de Noviembre" required>
                      </div>

                      <div class="col-12 col-sm-6">
                        <label class="label-custom">NIT / Registro Legal *</label>
                        <input type="text" class="input-custom" placeholder="Ej. 1028374019" required>
                      </div>

                      <div class="col-12 col-sm-6">
                        <label class="label-custom">Teléfono Central *</label>
                        <input type="tel" class="input-custom" placeholder="Ej. 3-4620000" required>
                      </div>

                      <div class="col-12">
                        <label class="label-custom">Ciudad Sede Principal *</label>
                        <input type="text" class="input-custom" placeholder="Ej. Trinidad, Beni" required>
                      </div>
                    </div>
                  </div>

                  <div class="step-content-sindicato d-none" id="step-sindicato-2">
                    <div class="row g-2">
                      <div class="col-12">
                        <label class="label-custom">Nombre Representante Legal *</label>
                        <input type="text" class="input-custom" placeholder="Ej. Roberto Suárez" required>
                      </div>

                      <div class="col-12 col-sm-6">
                        <label class="label-custom">C.I. Representante *</label>
                        <input type="text" class="input-custom" placeholder="Ej. 4587123 Beni" required>
                      </div>

                      <div class="col-12 col-sm-6">
                        <label class="label-custom">Celular de Contacto *</label>
                        <input type="tel" class="input-custom" placeholder="Ej. 78512345" required>
                      </div>

                      <div class="col-12">
                        <label class="label-custom">Cargo en el Sindicato *</label>
                        <input type="text" class="input-custom" placeholder="Ej. Secretario General / Presidente" required>
                      </div>
                    </div>
                  </div>

                  <div class="step-content-sindicato d-none" id="step-sindicato-3">
                    <div class="row g-2">
                      <div class="col-12">
                        <label class="label-custom">Correo del Administrador *</label>
                        <input type="email" class="input-custom" placeholder="admin@sindicato.com" required>
                      </div>

                      <div class="col-12 col-sm-6">
                        <label class="label-custom">Contraseña *</label>
                        <input type="password" class="input-custom" id="pass_sind1" placeholder="Mínimo 6 caracteres" minlength="6" required>
                      </div>

                      <div class="col-12 col-sm-6">
                        <label class="label-custom">Confirmar Contraseña *</label>
                        <input type="password" class="input-custom" id="pass_sind2" placeholder="Repita contraseña" minlength="6" required>
                      </div>

                      <div class="col-12 mt-2">
                        <div class="form-check p-0 ms-1">
                          <input class="form-check-input" type="checkbox" id="checkSindicatoTerminos" required>
                          <label class="form-check-label text-xs text-secondary ms-1" for="checkSindicatoTerminos">
                            Acepto Términos de Administración y Gestión
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center step-footer-sindicato" id="footer-sindicato-1">
                      <div></div>
                      <button type="button" class="btn btn-xs bg-gradient-success mb-0 border-radius-md px-3 font-weight-bold text-capitalize" onclick="goToStepSindicato(2)">
                        Siguiente <i class="fas fa-arrow-right text-xxs ms-1"></i>
                      </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center step-footer-sindicato d-none" id="footer-sindicato-2">
                      <button type="button" class="btn btn-xs bg-gradient-secondary mb-0 border-radius-md px-3 font-weight-bold text-capitalize" onclick="goToStepSindicato(1)">
                        <i class="fas fa-arrow-left text-xxs me-1"></i> Anterior
                      </button>
                      <button type="button" class="btn btn-xs bg-gradient-success mb-0 border-radius-md px-3 font-weight-bold text-capitalize" onclick="goToStepSindicato(3)">
                        Siguiente <i class="fas fa-arrow-right text-xxs ms-1"></i>
                      </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center step-footer-sindicato d-none" id="footer-sindicato-3">
                      <button type="button" class="btn btn-xs bg-gradient-secondary mb-0 border-radius-md px-3 font-weight-bold text-capitalize" onclick="goToStepSindicato(2)">
                        <i class="fas fa-arrow-left text-xxs me-1"></i> Anterior
                      </button>
                      <button type="submit" class="btn btn-xs bg-gradient-success mb-0 border-radius-md px-3 font-weight-bold text-capitalize">
                        Registrar Sindicato <i class="fas fa-paper-plane ms-1 text-xxs"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </main>

<!-- Estilos específicos de la animación de auth y diseño -->
<style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden;
    }
    .contenedor_todo {
      width: 100%;
      max-width: 1050px;
      margin: 20px auto;
      position: relative;
    }
    .caja_trasera {
      width: 100%;
      height: 540px;
      padding: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 1rem !important;
      position: relative;
    }
    .caja_trasera-login, 
    .caja_trasera-registro {
      width: 50%;
      padding: 20px;
      text-align: center;
      color: white;
      transition: all 500ms ease;
      z-index: 1;
    }
    .contenedor_login-deslizable {
      display: flex;
      align-items: center;
      width: 50%;
      height: calc(100% + 40px);
      position: absolute;
      top: -20px; 
      left: 0;
      z-index: 2;
      transition: left 500ms cubic-bezier(0.175, 0.885, 0.320, 1.275);
    }
    .card-auth-container {
      width: 100%;
      height: 100%;
      padding: 24px;
      background: #ffffff;
      border-radius: 1rem !important;
      display: flex;
      flex-direction: column;
      justify-content: center;
      box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(76, 175, 80, 0.4) !important;
      overflow-y: hidden !important;
    }
    .custom-nav-wrapper {
      position: relative;
      background-color: #f8f9fa;
      padding: 4px;
      border-radius: 0.5rem;
      width: 100%;
      box-sizing: border-box;
    }
    .custom-nav-wrapper .nav-pills {
      position: relative;
      display: flex;
      margin-bottom: 0;
      padding-left: 0;
      list-style: none;
      width: 100%;
    }
    .custom-nav-wrapper .nav-pills .nav-item {
      flex: 1 1 0%;
      text-align: center;
      z-index: 2;
      min-width: 0;
    }
    .custom-nav-wrapper .nav-pills .nav-link {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 0.5rem 1rem;
      border: 0;
      background: transparent;
      color: #67748e;
      font-weight: 600;
      font-size: 0.875rem;
      border-radius: 0.375rem;
      transition: color 0.3s ease;
      cursor: pointer;
      white-space: nowrap;
    }
    .custom-nav-wrapper .nav-pills .nav-link.active {
      color: #2e7d32 !important;
      background-color: transparent !important;
    }
    .custom-nav-wrapper .moving-tab {
      position: absolute;
      top: 0;
      left: 0;
      background-color: #ffffff;
      border-radius: 0.375rem;
      box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.12);
      z-index: 1;
      transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1), width 0.35s cubic-bezier(0.25, 1, 0.5, 1), height 0.35s cubic-bezier(0.25, 1, 0.5, 1);
      pointer-events: none;
    }
    .input-custom {
      border: 1px solid #d2d6da;
      border-radius: 0.375rem;
      padding: 0.35rem 0.65rem;
      font-size: 0.8125rem;
      width: 100%;
      outline: none;
      transition: all 0.2s ease;
    }
    .input-custom:focus {
      border-color: #4caf50;
      box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.25);
    }
    .label-custom {
      font-size: 0.75rem;
      font-weight: 700;
      color: #344767;
      margin-bottom: 2px;
      display: block;
    }
    @media (max-width: 850px) {
      .contenedor_todo {
        margin: 10px auto;
        padding: 0 10px;
      }
      .caja_trasera {
        height: auto;
        padding: 15px;
        flex-direction: column;
        border-radius: 1rem !important;
      }
      .caja_trasera-login, 
      .caja_trasera-registro {
        width: 100%;
        padding: 10px;
      }
      .contenedor_login-deslizable {
        width: 100%;
        height: auto;
        position: relative;
        top: 0;
        left: 0 !important;
      }
      .card-auth-container {
        padding: 20px 15px;
        border-radius: 1rem !important;
        overflow-y: auto !important;
      }
    }
</style>

<!-- Scripts de comportamiento específicos para la vista Auth -->
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