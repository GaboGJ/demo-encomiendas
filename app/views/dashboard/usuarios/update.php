<?php $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }; ?>
<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-10 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0">Editar Usuario</h5>
          <p class="text-xs text-secondary mb-0">Modifique los datos personales, el rol o la sucursal. Deje la contraseña en blanco para conservar la actual.</p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4">
          <form id="formUsuario" onsubmit="return false;" autocomplete="off">
            <input type="hidden" name="id_usuario" value="<?= (int)$usuario['id_usuario'] ?>">

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
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Nº Carnet (C.I.)</label>
                        <input type="text" class="form-control bg-gray-100" value="<?= $h($usuario['carnet_persona']) ?>" readonly>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Celular *</label>
                        <input type="text" class="form-control" name="celular" value="<?= $h($usuario['telefono_persona']) ?>" required>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Nombres *</label>
                        <input type="text" class="form-control" name="nombres" value="<?= $h($usuario['nombre_persona']) ?>" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Apellido Paterno *</label>
                        <input type="text" class="form-control" name="paterno" value="<?= $h($usuario['apellido_paterno_persona']) ?>" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" name="materno" value="<?= $h($usuario['apellido_materno_persona']) ?>">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Dirección / Ref.</label>
                        <input type="text" class="form-control" name="direccion" value="<?= $h($usuario['direccion_persona']) ?>">
                      </div>
                    </div>
                  </div>
                  <p class="text-xxs text-secondary mb-0 mt-1">El C.I. no se puede modificar porque identifica a la persona en todo el sistema.</p>
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
                          <?php foreach ($roles as $r): ?>
                            <option value="<?= (int)$r['id_rol'] ?>" <?= (int)$r['id_rol'] === (int)$usuario['id_rol'] ? 'selected' : '' ?>><?= $h($r['nombre_rol']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-12">
                      <label class="form-label text-xs font-weight-bold mb-0 mt-2">Sucursal / Terminal *</label>
                      <div class="input-group input-group-outline is-filled my-1">
                        <select class="form-control" name="id_sucursal" required>
                          <?php foreach ($sucursales as $s): ?>
                            <option value="<?= (int)$s['id_sucursal'] ?>" <?= (int)$s['id_sucursal'] === (int)$usuario['id_sucursal'] ? 'selected' : '' ?>><?= $h($s['ciudad_sucursal'] . ' - ' . $s['nombre_sucursal']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password" minlength="6">
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2">
                        <label class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" name="password_confirm" minlength="6">
                      </div>
                    </div>
                  </div>
                  <p class="text-xxs text-secondary mb-0">Solo complete estos campos si desea cambiar la contraseña.</p>
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
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnActualizarUsuario">
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> Guardar Cambios
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  document.getElementById('btnActualizarUsuario').addEventListener('click', function () {
    const form = document.getElementById('formUsuario');
    const btn = this;

    if (!form.reportValidity()) return;

    const fd = new FormData(form);
    if (fd.get('password') !== fd.get('password_confirm')) {
      Swal.fire({ icon: 'warning', title: 'Contraseñas distintas', text: 'Las contraseñas no coinciden.' });
      return;
    }

    btn.disabled = true;
    fetch(baseUrl + '/usuarios/actualizar', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          window.location.href = baseUrl + '/usuarios';
        } else {
          Swal.fire('No se pudo actualizar', res.message || 'Error al actualizar el usuario.', 'error');
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