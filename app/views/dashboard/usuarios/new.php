<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-10 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0">Registrar Nuevo Usuario</h5>
          <p class="text-xs text-secondary mb-0">Ingrese los datos de la persona, su rol y sucursal. El C.I. será su credencial de acceso.</p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4">
          <form id="formUsuario" onsubmit="return false;" autocomplete="off">
            <div class="row g-3 g-md-4">

              <!-- DATOS PERSONALES -->
              <div class="col-12 col-lg-6">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">person</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Datos Personales</h6>
                  </div>
                  <div class="row g-2">
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Nº Carnet (C.I.) *</label>
                        <input type="text" class="form-control" name="ci" id="ci" onblur="buscarPersonaPorCi()" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Celular *</label>
                        <input type="text" class="form-control" name="celular" id="celular" required>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Nombres *</label>
                        <input type="text" class="form-control" name="nombres" id="nombres" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Apellido Paterno *</label>
                        <input type="text" class="form-control" name="paterno" id="paterno" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" name="materno" id="materno">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Dirección / Ref.</label>
                        <input type="text" class="form-control" name="direccion" id="direccion">
                      </div>
                    </div>
                  </div>
                  <p class="text-xxs text-secondary mb-0 mt-1" id="lblPersonaExistente"></p>
                </div>
              </div>

              <!-- ACCESO -->
              <div class="col-12 col-lg-6">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">admin_panel_settings</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Acceso y Asignación</h6>
                  </div>
                  <div class="row g-2">
                    <div class="col-12">
                      <label class="form-label text-xs font-weight-bold mb-0">Rol Asignado *</label>
                      <div class="input-group input-group-outline is-filled my-1">
                        <select class="form-control" name="id_rol" required>
                          <option value="" selected disabled>Seleccione un rol...</option>
                          <?php foreach ($roles as $r): ?>
                            <option value="<?= (int)$r['id_rol'] ?>"><?= htmlspecialchars($r['nombre_rol']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-12">
                      <label class="form-label text-xs font-weight-bold mb-0 mt-2">Sucursal / Terminal *</label>
                      <div class="input-group input-group-outline is-filled my-1">
                        <select class="form-control" name="id_sucursal" required>
                          <option value="" selected disabled>Seleccione una sucursal...</option>
                          <?php foreach ($sucursales as $s): ?>
                            <option value="<?= (int)$s['id_sucursal'] ?>"><?= htmlspecialchars($s['ciudad_sucursal'] . ' - ' . $s['nombre_sucursal']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" name="password" minlength="6" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Confirmar Contraseña *</label>
                        <input type="password" class="form-control" name="password_confirm" minlength="6" required>
                      </div>
                    </div>
                  </div>
                  <p class="text-xxs text-secondary mb-0">Mínimo 6 caracteres. Se almacena cifrada.</p>
                </div>
              </div>

            </div>
          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/usuarios" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarUsuario">
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> Guardar Usuario
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const $ = id => document.getElementById(id);

  // Marca el input como "lleno" para que la etiqueta flotante de Material Dashboard suba
  function llenar(id, valor) {
    const el = $(id);
    if (!el) return;
    el.value = valor || '';
    const grupo = el.closest('.input-group');
    if (grupo) grupo.classList.toggle('is-filled', !!el.value);
  }

  window.buscarPersonaPorCi = function () {
    const ci = $('ci').value.trim();
    const lbl = $('lblPersonaExistente');
    lbl.textContent = '';
    if (!ci) return;

    fetch(`${baseUrl}/usuarios/buscarPersona?ci=${encodeURIComponent(ci)}`)
      .then(r => r.json())
      .then(res => {
        if (!res.success) return;
        const p = res.persona;
        llenar('nombres', p.nombre_persona);
        llenar('paterno', p.apellido_paterno_persona);
        llenar('materno', p.apellido_materno_persona);
        llenar('celular', p.telefono_persona);

        if (res.tiene_usuario) {
          lbl.className = 'text-xxs text-danger font-weight-bold mb-0 mt-1';
          lbl.textContent = 'Esta persona ya tiene un usuario registrado en el sistema.';
        } else {
          lbl.className = 'text-xxs text-success font-weight-bold mb-0 mt-1';
          lbl.textContent = 'Persona ya registrada: se completaron sus datos automáticamente.';
        }
      })
      .catch(() => {});
  };

  $('btnGuardarUsuario').addEventListener('click', function () {
    const form = $('formUsuario');
    const btn = this;

    if (!form.reportValidity()) return;

    const fd = new FormData(form);
    if (fd.get('password') !== fd.get('password_confirm')) {
      Swal.fire({ icon: 'warning', title: 'Contraseñas distintas', text: 'Las contraseñas no coinciden.' });
      return;
    }

    btn.disabled = true;
    fetch(baseUrl + '/usuarios/guardar', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en el listado (Flash)
          window.location.href = baseUrl + '/usuarios';
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar el usuario.', 'error');
          btn.disabled = false;
        }
      })
      .catch(() => {
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
        btn.disabled = false;
      });
  });
})();
</script>