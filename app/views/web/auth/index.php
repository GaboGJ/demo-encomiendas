<!-- Contenido Exclusivo de Autenticación (Login / Registro de Sindicato deslizante) -->
<!-- Los estilos base (.contenedor_todo, .caja_trasera, .input-custom...) ya viven en layouts/header.php -->
<style>
  /* Con más campos el registro necesita poder desplazarse en vez de recortarse */
  .card-auth-container { overflow-y: auto !important; }
  .caja_trasera { min-height: 540px; height: auto; }
</style>
<!-- SweetAlert para los mensajes de login/registro (el header web no lo incluye) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="main-content mt-6 mt-md-8 d-flex align-items-center py-3">
    <div class="container my-auto">
      <div class="contenedor_todo">

        <div class="caja_trasera bg-gradient-success shadow-success">
          <div class="caja_trasera-login">
            <h3 class="text-white font-weight-bolder mb-2">¿Ya estás registrado?</h3>
            <p class="text-xs text-white opacity-9 mb-4">Ingresa con tus credenciales para gestionar tu sindicato</p>
            <button id="btn_modo-login" class="btn btn-outline-white border-radius-lg px-4 text-capitalize mb-0">Iniciar Sesión</button>
          </div>

          <div class="caja_trasera-registro">
            <h3 class="text-white font-weight-bolder mb-2">¿Eres nuevo?</h3>
            <p class="text-xs text-white opacity-9 mb-4">Registra tu Sindicato de Transporte y empieza a administrar tus pasajes y encomiendas</p>
            <button id="btn_modo-registro" class="btn btn-outline-white border-radius-lg px-4 text-capitalize mb-0">Registrar Sindicato</button>
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
            <p class="text-xs text-secondary mb-4">Ingresa con tu C.I. y tu contraseña</p>

            <form id="formLoginGeneral" autocomplete="off">
              <div class="mb-3">
                <label class="label-custom">C.I. / Documento</label>
                <input type="text" class="input-custom" name="ci" id="login_ci" maxlength="20" placeholder="Ej. 7845123" required>
              </div>

              <div class="mb-4">
                <label class="label-custom">Contraseña</label>
                <input type="password" class="input-custom" name="password" id="login_password" placeholder="••••••••" required>
              </div>

              <button type="submit" id="btnLogin" class="btn bg-gradient-success border-radius-lg w-100 font-weight-bold text-capitalize shadow-success py-2 mb-0 text-white">
                Entrar al Sistema <span class="material-symbols-rounded text-sm ms-1 align-middle">arrow_forward</span>
              </button>
            </form>
          </div>

          <!-- REGISTRO DE SINDICATO -->
          <div class="card-auth-container formulario_registro" style="display: none;">
            <h4 class="font-weight-bolder text-dark mb-1">Registrar Sindicato</h4>
            <p class="text-xs text-secondary mb-3">El representante legal será el administrador del sistema.</p>

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
                  <span class="d-block text-xxs font-weight-bold text-secondary">2. Representante</span>
                </div>

                <div class="border-top border-2 flex-fill opacity-3"></div>

                <div class="step-indicator flex-fill" id="indicator-sindicato-3">
                  <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-0 btn-sm shadow-none" onclick="goToStepSindicato(3)">
                    <span class="material-symbols-rounded text-xs align-middle">admin_panel_settings</span>
                  </button>
                  <span class="d-block text-xxs font-weight-bold text-secondary">3. Acceso</span>
                </div>
              </div>
            </div>

            <!-- novalidate: los pasos ocultos con "required" bloqueaban el submit; se valida por paso en JS -->
            <form id="formRegistroSindicato" novalidate autocomplete="off">
              <div class="step-content-sindicato" id="step-sindicato-1">
                <div class="row g-2">
                  <div class="col-12">
                    <label class="label-custom">Nombre del Sindicato / Empresa *</label>
                    <input type="text" class="input-custom" name="nombre_sindicato" maxlength="50" placeholder="Ej. Sindicato Mixto 18 de Noviembre" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">NIT / Registro Legal *</label>
                    <input type="text" class="input-custom" name="nit" maxlength="50" placeholder="Ej. 1028374019" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Teléfono Central *</label>
                    <input type="tel" class="input-custom" name="telefono_sindicato" maxlength="50" placeholder="Ej. 34620000" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Ciudad Sede Principal *</label>
                    <input type="text" class="input-custom" name="ciudad" maxlength="50" placeholder="Ej. Trinidad" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Dirección de la Sede *</label>
                    <input type="text" class="input-custom" name="direccion" maxlength="200" placeholder="Ej. Av. 6 de Agosto #123" required>
                  </div>
                </div>
              </div>

              <div class="step-content-sindicato d-none" id="step-sindicato-2">
                <div class="row g-2">
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Nombres del Representante *</label>
                    <input type="text" class="input-custom" name="rep_nombres" maxlength="50" placeholder="Ej. Roberto" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Apellido Paterno *</label>
                    <input type="text" class="input-custom" name="rep_paterno" maxlength="50" placeholder="Ej. Suárez" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Apellido Materno</label>
                    <input type="text" class="input-custom" name="rep_materno" maxlength="50" placeholder="Opcional">
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">C.I. Representante *</label>
                    <input type="text" class="input-custom" name="rep_ci" maxlength="20" placeholder="Ej. 4587123" required>
                  </div>
                  <div class="col-12">
                    <label class="label-custom">Celular de Contacto *</label>
                    <input type="tel" class="input-custom" name="rep_celular" maxlength="50" placeholder="Ej. 78512345" required>
                  </div>
                </div>
              </div>

              <div class="step-content-sindicato d-none" id="step-sindicato-3">
                <p class="text-xs text-secondary mb-2">El administrador ingresará con el <strong>C.I. del representante</strong> y la contraseña que defina aquí.</p>
                <div class="row g-2">
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Contraseña *</label>
                    <input type="password" class="input-custom" name="password" placeholder="Mínimo 6 caracteres" minlength="6" maxlength="72" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Confirmar Contraseña *</label>
                    <input type="password" class="input-custom" name="password_confirm" placeholder="Repita contraseña" minlength="6" maxlength="72" required>
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
                  <button type="submit" id="btnRegSindicato" class="btn btn-xs bg-gradient-success mb-0 border-radius-md px-3 font-weight-bold text-capitalize">
                    Registrar Sindicato <i class="fas fa-paper-plane ms-1 text-xxs"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </main>

<!-- Scripts de comportamiento de Auth (JS puro: no depende de jQuery) -->
<script>
    var BASE_URL = <?= json_encode(rtrim(URL, '/')) ?>;

    /* ---------- Deslizamiento login / registro ---------- */
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
    }

    /* ---------- Wizard del sindicato ---------- */
    function mostrarPasoSindicato(n) {
      document.querySelectorAll('.step-content-sindicato').forEach(function (el) { el.classList.add('d-none'); });
      document.getElementById('step-sindicato-' + n).classList.remove('d-none');

      document.querySelectorAll('.step-footer-sindicato').forEach(function (el) { el.classList.add('d-none'); });
      document.getElementById('footer-sindicato-' + n).classList.remove('d-none');

      for (var i = 1; i <= 3; i++) {
        var ind  = document.getElementById('indicator-sindicato-' + i);
        var btn  = ind.querySelector('button');
        var icon = btn.querySelector('span');
        var lbl  = ind.querySelector('span.d-block');
        var on   = i <= n;

        btn.classList.toggle('bg-gradient-success', on);
        btn.classList.toggle('text-white', on);
        btn.classList.toggle('bg-gray-200', !on);
        btn.classList.toggle('text-secondary', !on);
        icon.classList.toggle('text-white', on);
        lbl.classList.toggle('text-dark', on);
        lbl.classList.toggle('text-secondary', !on);
      }
    }

    // Valida los campos de UN paso; si falla, muestra ese paso y el aviso nativo del navegador
    function validarPasoSindicato(paso) {
      var inputs = document.querySelectorAll('#step-sindicato-' + paso + ' input');
      for (var i = 0; i < inputs.length; i++) {
        if (!inputs[i].checkValidity()) {
          mostrarPasoSindicato(paso);
          inputs[i].reportValidity();
          return false;
        }
      }
      return true;
    }

    // Para avanzar exige que los pasos anteriores estén completos
    function goToStepSindicato(n) {
      for (var p = 1; p < n; p++) {
        if (!validarPasoSindicato(p)) return;
      }
      mostrarPasoSindicato(n);
    }

    /* ---------- Envío por fetch ---------- */
    function enviarFormulario(url, form, boton) {
      boton.disabled = true;
      return fetch(BASE_URL + url, { method: 'POST', body: new FormData(form) })
        .then(function (r) { return r.json(); })
        .then(function (res) { boton.disabled = false; return res; })
        .catch(function () {
          boton.disabled = false;
          return { success: false, message: 'Ocurrió un error al comunicarse con el servidor.' };
        });
    }

    // LOGIN
    document.getElementById('formLoginGeneral').addEventListener('submit', function (e) {
      e.preventDefault();
      enviarFormulario('/auth/login', this, document.getElementById('btnLogin')).then(function (res) {
        if (res.success) {
          window.location.href = res.redirect;
        } else {
          Swal.fire({ icon: 'error', title: 'No se pudo ingresar', text: res.message });
        }
      });
    });

    // REGISTRO SINDICATO
    document.getElementById('formRegistroSindicato').addEventListener('submit', function (e) {
      e.preventDefault();
      var form = this;

      for (var p = 1; p <= 3; p++) {
        if (!validarPasoSindicato(p)) return;
      }
      if (form.password.value !== form.password_confirm.value) {
        Swal.fire({ icon: 'warning', title: 'Contraseñas distintas', text: 'Las contraseñas no coinciden.' });
        return;
      }

      enviarFormulario('/auth/registrarSindicato', form, document.getElementById('btnRegSindicato')).then(function (res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'Sindicato registrado', text: res.message }).then(function () {
            form.reset();
            mostrarPasoSindicato(1);
            document.getElementById('login_ci').value = res.ci || '';
            verLogin();
            document.getElementById('login_password').focus();
          });
        } else {
          Swal.fire({ icon: 'error', title: 'No se pudo registrar', text: res.message });
        }
      });
    });
</script>