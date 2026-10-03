<?php
/**
 * Formulario compartido por new.php y update.php.
 * Variables: $modo ('nuevo' | 'editar'), $rl (datos; vacío en "nuevo"), $rolesCopia (solo en "nuevo").
 */
$h         = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$esEdicion = ($modo ?? 'nuevo') === 'editar';
$protegido = $esEdicion && (int)($rl['es_protegido'] ?? 0) === 1;
$nombre    = $h($rl['nombre_rol'] ?? '');
$desc      = $h($rl['descripcion_rol'] ?? '');
?>
<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-8 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0"><?= $esEdicion ? 'Editar Rol' : 'Registrar Nuevo Rol' ?></h5>
          <p class="text-xs text-secondary mb-0">
            <?= $esEdicion
                ? 'Modifique el nombre y la descripción. Los permisos se editan desde "Configurar Permisos".'
                : 'Registre el rol; al guardar pasará directamente a la matriz de permisos.' ?>
          </p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4">
          <form id="formRol" onsubmit="return false;" autocomplete="off">
            <?php if ($esEdicion): ?>
              <input type="hidden" name="id_rol" value="<?= (int)$rl['id_rol'] ?>">
            <?php endif; ?>

            <div class="p-3 border border-radius-md bg-white">
              <div class="d-flex align-items-center mb-3">
                <span class="material-symbols-rounded text-success me-2">shield_person</span>
                <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Identificación</h6>
              </div>

              <div class="input-group input-group-outline my-2<?= $nombre !== '' ? ' is-filled' : '' ?>">
                <label class="form-label">Nombre del Rol *</label>
                <input type="text" class="form-control <?= $protegido ? 'bg-gray-100' : '' ?>" name="nombre_rol" maxlength="50"
                       value="<?= $nombre ?>" placeholder="Ej. Supervisor de Agencia" <?= $protegido ? 'readonly' : 'required' ?>>
              </div>
              <?php if ($protegido): ?>
                <p class="text-xxs text-warning font-weight-bold mb-2">Es el rol del sistema (administrador): su nombre no se puede cambiar.</p>
              <?php endif; ?>

              <div class="input-group input-group-outline my-2<?= $desc !== '' ? ' is-filled' : '' ?>">
                <label class="form-label">Descripción de funciones</label>
                <textarea class="form-control" name="descripcion_rol" rows="3" maxlength="200"><?= $desc ?></textarea>
              </div>

              <?php if (!$esEdicion): ?>
                <label class="form-label text-xs font-weight-bold mb-0 mt-2">Copiar permisos de (opcional)</label>
                <div class="input-group input-group-outline is-filled my-1">
                  <select class="form-control" name="copiar_de">
                    <option value="0">Sin copiar (configurar desde cero)</option>
                    <?php foreach ($rolesCopia as $c): ?>
                      <?php if ((int)$c['es_protegido'] === 1) continue; ?>
                      <option value="<?= (int)$c['id_rol'] ?>"><?= $h($c['nombre_rol']) ?> (<?= (int)$c['total_permisos'] ?> permisos)</option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>

              <p class="text-xxs text-secondary mb-0 mt-2">El nombre no puede repetirse, ni siquiera con roles de la papelera.</p>
            </div>
          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/roles" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarRol">
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> <?= $esEdicion ? 'Guardar Cambios' : 'Guardar y Configurar Permisos' ?>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl   = '<?= rtrim(URL, "/") ?>';
  const endpoint  = '<?= $esEdicion ? "/roles/actualizar" : "/roles/guardar" ?>';
  const esEdicion = <?= $esEdicion ? 'true' : 'false' ?>;
  const form = document.getElementById('formRol');
  const btn  = document.getElementById('btnGuardarRol');

  btn.addEventListener('click', function () {
    if (!form.reportValidity()) return;

    btn.disabled = true;
    fetch(baseUrl + endpoint, { method: 'POST', body: new FormData(form) })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en la siguiente pantalla (Flash)
          window.location.href = baseUrl + (esEdicion ? '/roles' : '/roles/permisos?id=' + res.id);
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar el rol.', 'error');
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