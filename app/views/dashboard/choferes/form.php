<?php
/**
 * Formulario compartido por new.php y update.php.
 * Variables: $modo ('nuevo' | 'editar') y $cho (array con los datos; vacío en "nuevo").
 */
$h         = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$esEdicion = ($modo ?? 'nuevo') === 'editar';
$val       = function ($campo) use ($cho, $h) { return $h($cho[$campo] ?? ''); };
$lleno     = function ($campo) use ($cho) { return !empty($cho[$campo]) ? ' is-filled' : ''; };
$categorias = ['A' => 'A - Motocicletas', 'B' => 'B - Automóviles', 'C' => 'C - Camionetas / Minibuses', 'P' => 'P - Profesional (buses)', 'M' => 'M - Motocicletas', 'T' => 'T - Tractores'];
$catActual = $cho['categoria_licencia_chofer'] ?? '';
?>
<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-10 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0"><?= $esEdicion ? 'Editar Chofer' : 'Registrar Nuevo Chofer' ?></h5>
          <p class="text-xs text-secondary mb-0">
            <?= $esEdicion
                ? 'Modifique los datos personales o de la licencia. El C.I. no se puede cambiar.'
                : 'Ingrese los datos de la persona y su licencia de conducir. Si el C.I. ya está registrado se completan los datos automáticamente.' ?>
          </p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4">
          <form id="formChofer" onsubmit="return false;" autocomplete="off">
            <?php if ($esEdicion): ?>
              <input type="hidden" name="id_chofer" value="<?= (int)$cho['id_chofer'] ?>">
            <?php endif; ?>

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
                      <div class="input-group input-group-outline my-2<?= $lleno('carnet_persona') ?>">
                        <label class="form-label">Nº Carnet (C.I.) *</label>
                        <?php if ($esEdicion): ?>
                          <input type="text" class="form-control bg-gray-100" value="<?= $val('carnet_persona') ?>" readonly>
                        <?php else: ?>
                          <input type="text" class="form-control" name="ci" id="ci" maxlength="20" onblur="buscarPersonaPorCi()" required>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2<?= $lleno('telefono_persona') ?>">
                        <label class="form-label">Celular *</label>
                        <input type="text" class="form-control" name="celular" id="celular" maxlength="50" value="<?= $val('telefono_persona') ?>" required>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline my-2<?= $lleno('nombre_persona') ?>">
                        <label class="form-label">Nombres *</label>
                        <input type="text" class="form-control" name="nombres" id="nombres" maxlength="50" value="<?= $val('nombre_persona') ?>" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2<?= $lleno('apellido_paterno_persona') ?>">
                        <label class="form-label">Apellido Paterno *</label>
                        <input type="text" class="form-control" name="paterno" id="paterno" maxlength="50" value="<?= $val('apellido_paterno_persona') ?>" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2<?= $lleno('apellido_materno_persona') ?>">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" name="materno" id="materno" maxlength="50" value="<?= $val('apellido_materno_persona') ?>">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline my-2<?= $lleno('direccion_persona') ?>">
                        <label class="form-label">Dirección / Ref.</label>
                        <input type="text" class="form-control" name="direccion" id="direccion" maxlength="200" value="<?= $val('direccion_persona') ?>">
                      </div>
                    </div>
                  </div>
                  <p class="text-xxs mb-0 mt-1" id="lblPersonaExistente"></p>
                </div>
              </div>

              <!-- LICENCIA -->
              <div class="col-12 col-lg-6">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">badge</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Licencia de Conducir</h6>
                  </div>
                  <div class="row g-2">
                    <div class="col-12">
                      <div class="input-group input-group-outline my-2<?= $lleno('licencia_chofer') ?>">
                        <label class="form-label">Nº de Licencia *</label>
                        <input type="text" class="form-control text-uppercase" name="licencia" id="licencia" maxlength="50"
                               pattern="[0-9A-Za-z\-\/\.]{4,50}" title="Letras, números, guion, punto y barra (4 a 50 caracteres, sin espacios)"
                               value="<?= $val('licencia_chofer') ?>" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <label class="form-label text-xs font-weight-bold mb-0 mt-2">Categoría</label>
                      <div class="input-group input-group-outline is-filled my-1">
                        <select class="form-control" name="categoria">
                          <option value="">Sin categoría</option>
                          <?php foreach ($categorias as $k => $txt): ?>
                            <option value="<?= $k ?>" <?= $catActual === $k ? 'selected' : '' ?>><?= $h($txt) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <label class="form-label text-xs font-weight-bold mb-0 mt-2">Vencimiento</label>
                      <div class="input-group input-group-outline is-filled my-1">
                        <input type="date" class="form-control" name="vencimiento" value="<?= $val('vencimiento_licencia_chofer') ?>">
                      </div>
                    </div>
                  </div>
                  <p class="text-xxs text-secondary mb-0 mt-2">El número de licencia y la persona no pueden repetirse, ni siquiera con choferes de la papelera.</p>
                </div>
              </div>

            </div>
          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/choferes" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarChofer">
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> <?= $esEdicion ? 'Guardar Cambios' : 'Guardar Chofer' ?>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl  = '<?= rtrim(URL, "/") ?>';
  const endpoint = '<?= $esEdicion ? "/choferes/actualizar" : "/choferes/guardar" ?>';
  const form = document.getElementById('formChofer');
  const btn  = document.getElementById('btnGuardarChofer');
  const $ = id => document.getElementById(id);

  $('licencia').addEventListener('input', function () { this.value = this.value.toUpperCase(); });

  // Marca el input como "lleno" para que la etiqueta flotante de Material Dashboard suba
  function llenar(id, valor) {
    const el = $(id);
    if (!el) return;
    el.value = valor || '';
    const grupo = el.closest('.input-group');
    if (grupo) grupo.classList.toggle('is-filled', !!el.value);
  }

  <?php if (!$esEdicion): ?>
  window.buscarPersonaPorCi = function () {
    const ci = $('ci').value.trim();
    const lbl = $('lblPersonaExistente');
    lbl.textContent = '';
    if (!ci) return;

    fetch(`${baseUrl}/choferes/buscarPersona?ci=${encodeURIComponent(ci)}`)
      .then(r => r.json())
      .then(res => {
        if (!res.success) return;
        const p = res.persona;
        llenar('nombres', p.nombre_persona);
        llenar('paterno', p.apellido_paterno_persona);
        llenar('materno', p.apellido_materno_persona);
        llenar('celular', p.telefono_persona);

        if (res.tiene_chofer) {
          lbl.className = 'text-xxs text-danger font-weight-bold mb-0 mt-1';
          lbl.textContent = res.eliminado
            ? 'Esta persona ya fue registrada como chofer y está en la papelera: restáurela desde allí.'
            : 'Esta persona ya está registrada como chofer.';
        } else {
          lbl.className = 'text-xxs text-success font-weight-bold mb-0 mt-1';
          lbl.textContent = 'Persona ya registrada: se completaron sus datos automáticamente.';
        }
      })
      .catch(() => {});
  };
  <?php endif; ?>

  btn.addEventListener('click', function () {
    if (!form.reportValidity()) return;

    btn.disabled = true;
    fetch(baseUrl + endpoint, { method: 'POST', body: new FormData(form) })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en el listado (Flash)
          window.location.href = baseUrl + '/choferes';
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar el chofer.', 'error');
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