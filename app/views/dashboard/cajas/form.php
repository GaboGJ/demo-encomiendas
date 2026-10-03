<?php
/**
 * Formulario compartido por new.php y update.php.
 * Variables: $modo ('nuevo' | 'editar'), $caj (datos; vacío en "nuevo"), $sucursales.
 */
$h         = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$esEdicion = ($modo ?? 'nuevo') === 'editar';
$nombre    = $h($caj['nombre_caja'] ?? '');
?>
<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-8 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0"><?= $esEdicion ? 'Editar Caja' : 'Registrar Nueva Caja' ?></h5>
          <p class="text-xs text-secondary mb-0">
            <?= $esEdicion
                ? 'Modifique el nombre de la caja. La sucursal no se puede cambiar.'
                : 'Defina la caja (ventanilla) de una sucursal. Luego podrá aperturar turnos desde el listado.' ?>
          </p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4">
          <form id="formCaja" onsubmit="return false;" autocomplete="off">
            <?php if ($esEdicion): ?>
              <input type="hidden" name="id_caja" value="<?= (int)$caj['id_caja'] ?>">
            <?php endif; ?>

            <div class="p-3 border border-radius-md bg-white">
              <div class="d-flex align-items-center mb-3">
                <span class="material-symbols-rounded text-success me-2">point_of_sale</span>
                <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Identificación</h6>
              </div>

              <?php if ($esEdicion): ?>
                <div class="input-group input-group-outline is-filled my-2">
                  <label class="form-label">Sucursal</label>
                  <input type="text" class="form-control bg-gray-100" value="<?= $h($caj['ciudad_sucursal'] . ' (' . $caj['nombre_sucursal'] . ')') ?>" readonly>
                </div>
              <?php else: ?>
                <label class="form-label text-xs font-weight-bold mb-0">Sucursal *</label>
                <div class="input-group input-group-outline is-filled my-1">
                  <select class="form-control" name="id_sucursal" required>
                    <option value="" disabled selected>Seleccione una sucursal...</option>
                    <?php foreach ($sucursales as $s): ?>
                      <option value="<?= (int)$s['id_sucursal'] ?>"><?= $h($s['ciudad_sucursal'] . ' (' . $s['nombre_sucursal'] . ')') ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php if (empty($sucursales)): ?>
                  <p class="text-xxs text-warning font-weight-bold mb-0">Su sindicato no tiene sucursales activas.</p>
                <?php endif; ?>
              <?php endif; ?>

              <div class="input-group input-group-outline my-3<?= $nombre !== '' ? ' is-filled' : '' ?>">
                <label class="form-label">Nombre de la Caja *</label>
                <input type="text" class="form-control" name="nombre_caja" maxlength="50" value="<?= $nombre ?>" placeholder="Ej. Ventanilla 1" required>
              </div>
              <p class="text-xxs text-secondary mb-0">El nombre no puede repetirse dentro de la sucursal, ni siquiera con cajas de la papelera.</p>
            </div>
          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/cajas" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarCaja">
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> <?= $esEdicion ? 'Guardar Cambios' : 'Guardar Caja' ?>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl  = '<?= rtrim(URL, "/") ?>';
  const endpoint = '<?= $esEdicion ? "/cajas/actualizar" : "/cajas/guardar" ?>';
  const form = document.getElementById('formCaja');
  const btn  = document.getElementById('btnGuardarCaja');

  btn.addEventListener('click', function () {
    if (!form.reportValidity()) return;

    btn.disabled = true;
    fetch(baseUrl + endpoint, { method: 'POST', body: new FormData(form) })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en el listado (Flash)
          window.location.href = baseUrl + '/cajas';
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar la caja.', 'error');
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