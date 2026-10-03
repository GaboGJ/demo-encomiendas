<?php
/**
 * Formulario compartido por new.php y update.php.
 * Variables: $modo ('nuevo' | 'editar') y $mod (datos; vacío en "nuevo").
 */
$h         = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$esEdicion = ($modo ?? 'nuevo') === 'editar';
$nombre    = $h($mod['nombre_modelo'] ?? '');
?>
<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-8 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0"><?= $esEdicion ? 'Editar Modelo' : 'Registrar Nuevo Modelo' ?></h5>
          <p class="text-xs text-secondary mb-0">
            <?= $esEdicion
                ? 'Modifique el nombre del modelo. El plano de asientos se edita desde "Configurar Asientos".'
                : 'Registre el modelo; al guardar pasará directamente al editor para armar su plano de asientos.' ?>
          </p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4">
          <form id="formModelo" onsubmit="return false;" autocomplete="off">
            <?php if ($esEdicion): ?>
              <input type="hidden" name="id_modelo" value="<?= (int)$mod['id_modelo'] ?>">
            <?php endif; ?>

            <div class="p-3 border border-radius-md bg-white">
              <div class="d-flex align-items-center mb-3">
                <span class="material-symbols-rounded text-success me-2">directions_bus</span>
                <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Identificación</h6>
              </div>

              <div class="input-group input-group-outline my-2<?= $nombre !== '' ? ' is-filled' : '' ?>">
                <label class="form-label">Nombre del Modelo *</label>
                <input type="text" class="form-control" name="nombre_modelo" id="nombre_modelo" maxlength="50" value="<?= $nombre ?>" placeholder="Ej. Toyota Noa" required>
              </div>
              <p class="text-xxs text-secondary mb-0 mt-1">El nombre no puede repetirse, ni siquiera con modelos de la papelera.</p>

              <?php if ($esEdicion): ?>
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap p-2 bg-gray-100 border-radius-md mt-3">
                  <span class="text-xs text-secondary font-weight-bold">
                    Capacidad actual:
                    <span class="text-dark"><?= (int)$mod['total_asientos_modelo'] > 0 ? (int)$mod['total_asientos_modelo'] . ' asientos' : 'sin configurar' ?></span>
                  </span>
                  <a href="<?= rtrim(URL, '/') ?>/modelos/configurar?id=<?= (int)$mod['id_modelo'] ?>" class="btn btn-sm btn-outline-success mb-0 d-inline-flex align-items-center gap-1">
                    <i class="material-symbols-rounded text-sm">event_seat</i> Configurar Asientos
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/modelos" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarModelo">
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> <?= $esEdicion ? 'Guardar Cambios' : 'Guardar y Configurar Asientos' ?>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl  = '<?= rtrim(URL, "/") ?>';
  const endpoint = '<?= $esEdicion ? "/modelos/actualizar" : "/modelos/guardar" ?>';
  const esEdicion = <?= $esEdicion ? 'true' : 'false' ?>;
  const form = document.getElementById('formModelo');
  const btn  = document.getElementById('btnGuardarModelo');

  btn.addEventListener('click', function () {
    if (!form.reportValidity()) return;

    btn.disabled = true;
    fetch(baseUrl + endpoint, { method: 'POST', body: new FormData(form) })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en la siguiente pantalla (Flash)
          window.location.href = baseUrl + (esEdicion ? '/modelos' : '/modelos/configurar?id=' + res.id);
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar el modelo.', 'error');
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