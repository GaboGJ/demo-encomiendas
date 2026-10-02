<?php
/**
 * Formulario compartido por new.php y update.php.
 * Variables: $modo ('nuevo' | 'editar') y $sin (array con los datos; vacío en "nuevo").
 */
$h         = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$esEdicion = ($modo ?? 'nuevo') === 'editar';
$val       = function ($campo) use ($sin, $h) { return $h($sin[$campo] ?? ''); };
$lleno     = function ($campo) use ($sin) { return !empty($sin[$campo]) ? ' is-filled' : ''; };
$principal = $esEdicion && (int)($sin['es_principal_sindicato'] ?? 0) === 1;
?>
<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-10 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0">
            <?= $esEdicion ? 'Editar Sindicato' : 'Registrar Nuevo Sindicato' ?>
            <?php if ($principal): ?><span class="badge badge-sm bg-gradient-dark border-radius-pill ms-2">Principal</span><?php endif; ?>
          </h5>
          <p class="text-xs text-secondary mb-0">
            <?= $esEdicion
                ? 'Modifique los datos de la institución. El estado se cambia desde el listado.'
                : 'Ingrese los datos legales y de contacto del sindicato asociado. Sus sucursales y socios se registran después.' ?>
          </p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4">
          <form id="formSindicato" onsubmit="return false;" autocomplete="off">
            <?php if ($esEdicion): ?>
              <input type="hidden" name="id_sindicato" value="<?= (int)$sin['id_sindicato'] ?>">
            <?php endif; ?>

            <div class="row g-3 g-md-4">

              <!-- IDENTIFICACIÓN -->
              <div class="col-12 col-lg-6">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">domain</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Identificación</h6>
                  </div>
                  <div class="row g-2">
                    <div class="col-12">
                      <div class="input-group input-group-outline my-2<?= $lleno('nombre_sindicato') ?>">
                        <label class="form-label">Nombre del Sindicato *</label>
                        <input type="text" class="form-control" name="nombre_sindicato" maxlength="50" value="<?= $val('nombre_sindicato') ?>" required>
                      </div>
                    </div>
                    <div class="col-12 col-sm-5">
                      <div class="input-group input-group-outline my-2<?= $lleno('sigla_sindicato') ?>">
                        <label class="form-label">Sigla</label>
                        <input type="text" class="form-control text-uppercase" name="sigla" id="sigla" maxlength="20" value="<?= $val('sigla_sindicato') ?>">
                      </div>
                    </div>
                    <div class="col-12 col-sm-7">
                      <div class="input-group input-group-outline my-2<?= $lleno('personeria_sindicato') ?>">
                        <label class="form-label">NIT / Registro Legal *</label>
                        <input type="text" class="form-control" name="nit" maxlength="50" pattern="[0-9A-Za-z\-\/\.]{5,50}" title="Letras, números, guion, punto y barra (5 a 50 caracteres, sin espacios)" value="<?= $val('personeria_sindicato') ?>" required>
                      </div>
                    </div>
                  </div>
                  <p class="text-xxs text-secondary mb-0 mt-1">El nombre y el NIT no pueden repetirse, ni siquiera con sindicatos de la papelera.</p>
                </div>
              </div>

              <!-- CONTACTO -->
              <div class="col-12 col-lg-6">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">call</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Contacto y Ubicación</h6>
                  </div>
                  <div class="row g-2">
                    <div class="col-12">
                      <div class="input-group input-group-outline my-2<?= $lleno('telefono_sindicato') ?>">
                        <label class="form-label">Teléfono Central *</label>
                        <input type="tel" class="form-control" name="telefono_sindicato" maxlength="50" value="<?= $val('telefono_sindicato') ?>" required>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline my-2<?= $lleno('direccion_sindicato') ?>">
                        <label class="form-label">Dirección *</label>
                        <input type="text" class="form-control" name="direccion" maxlength="200" value="<?= $val('direccion_sindicato') ?>" required>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/sindicatos" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarSindicato">
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> <?= $esEdicion ? 'Guardar Cambios' : 'Guardar Sindicato' ?>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const endpoint = '<?= $esEdicion ? "/sindicatos/actualizar" : "/sindicatos/guardar" ?>';
  const form = document.getElementById('formSindicato');
  const btn  = document.getElementById('btnGuardarSindicato');

  // La sigla siempre en mayúsculas
  document.getElementById('sigla').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
  });

  btn.addEventListener('click', function () {
    if (!form.reportValidity()) return;

    btn.disabled = true;
    fetch(baseUrl + endpoint, { method: 'POST', body: new FormData(form) })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en el listado (Flash)
          window.location.href = baseUrl + '/sindicatos';
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar el sindicato.', 'error');
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