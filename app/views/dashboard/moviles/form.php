<?php
/**
 * Formulario compartido por new.php y update.php.
 * Variables: $modo ('nuevo' | 'editar'), $mov (datos; vacío en "nuevo"), $socios, $modelos.
 */
$h         = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$esEdicion = ($modo ?? 'nuevo') === 'editar';
$val       = function ($campo) use ($mov, $h) { return $h($mov[$campo] ?? ''); };
$lleno     = function ($campo) use ($mov) { return !empty($mov[$campo]) ? ' is-filled' : ''; };
?>
<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-10 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0"><?= $esEdicion ? 'Editar Unidad' : 'Registrar Nueva Unidad' ?></h5>
          <p class="text-xs text-secondary mb-0">
            <?= $esEdicion
                ? 'Modifique los datos del vehículo. El estado se cambia desde el listado.'
                : 'Ingrese los datos del vehículo, su modelo y el socio titular. Los choferes se asignan después.' ?>
          </p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4">
          <form id="formMovil" onsubmit="return false;" autocomplete="off">
            <?php if ($esEdicion): ?>
              <input type="hidden" name="id_vehiculo" value="<?= (int)$mov['id_vehiculo'] ?>">
            <?php endif; ?>

            <div class="row g-3 g-md-4">

              <!-- IDENTIFICACIÓN -->
              <div class="col-12 col-lg-6">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">directions_bus</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Identificación</h6>
                  </div>
                  <div class="row g-2">
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2<?= $lleno('numero_interno_vehiculo') ?>">
                        <label class="form-label">Número de Unidad / Interno *</label>
                        <input type="text" class="form-control" name="numero_interno" maxlength="50" value="<?= $val('numero_interno_vehiculo') ?>" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group input-group-outline my-2<?= $lleno('placa_vehiculo') ?>">
                        <label class="form-label">Placa</label>
                        <input type="text" class="form-control text-uppercase" name="placa" id="placa" maxlength="15" value="<?= $val('placa_vehiculo') ?>">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline my-2<?= $lleno('color_vehiculo') ?>">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" name="color" maxlength="50" value="<?= $val('color_vehiculo') ?>">
                      </div>
                    </div>
                  </div>
                  <p class="text-xxs text-secondary mb-0 mt-1">El número de unidad no puede repetirse en su sindicato y la placa no puede repetirse en el sistema.</p>
                </div>
              </div>

              <!-- MODELO Y TITULAR -->
              <div class="col-12 col-lg-6">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">car_rental</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Modelo y Socio Titular</h6>
                  </div>
                  <div class="row g-2">
                    <div class="col-12">
                      <label class="form-label text-xs font-weight-bold mb-0">Modelo de Vehículo *</label>
                      <div class="input-group input-group-outline is-filled my-1">
                        <select class="form-control" name="id_modelo" required>
                          <option value="" disabled <?= $esEdicion ? '' : 'selected' ?>>Seleccione un modelo...</option>
                          <?php foreach ($modelos as $m): ?>
                            <option value="<?= (int)$m['id_modelo'] ?>" <?= $esEdicion && (int)$m['id_modelo'] === (int)$mov['id_modelo'] ? 'selected' : '' ?>>
                              <?= $h($m['nombre_modelo'] . ' (' . (int)$m['total_asientos_modelo'] . ' asientos)') ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-12">
                      <label class="form-label text-xs font-weight-bold mb-0 mt-2">Socio Titular *</label>
                      <div class="input-group input-group-outline is-filled my-1">
                        <select class="form-control" name="id_socio" required>
                          <option value="" disabled <?= $esEdicion ? '' : 'selected' ?>>Seleccione un socio...</option>
                          <?php foreach ($socios as $s): ?>
                            <option value="<?= (int)$s['id_socio'] ?>" <?= $esEdicion && (int)$s['id_socio'] === (int)$mov['id_socio'] ? 'selected' : '' ?>>
                              <?= $h($s['nombre_socio'] . ($s['codigo_socio'] ? ' (' . $s['codigo_socio'] . ')' : '')) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <?php if (empty($socios)): ?>
                    <p class="text-xxs text-warning font-weight-bold mb-0 mt-2">Su sindicato aún no tiene socios registrados; registre uno antes de crear móviles.</p>
                  <?php endif; ?>
                </div>
              </div>

            </div>
          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/moviles" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarMovil">
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> <?= $esEdicion ? 'Guardar Cambios' : 'Guardar Unidad' ?>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl  = '<?= rtrim(URL, "/") ?>';
  const endpoint = '<?= $esEdicion ? "/moviles/actualizar" : "/moviles/guardar" ?>';
  const form = document.getElementById('formMovil');
  const btn  = document.getElementById('btnGuardarMovil');

  // La placa siempre en mayúsculas y sin espacios
  document.getElementById('placa').addEventListener('input', function () {
    this.value = this.value.toUpperCase().replace(/\s+/g, '');
  });

  btn.addEventListener('click', function () {
    if (!form.reportValidity()) return;

    btn.disabled = true;
    fetch(baseUrl + endpoint, { method: 'POST', body: new FormData(form) })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en el listado (Flash)
          window.location.href = baseUrl + '/moviles';
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar el móvil.', 'error');
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