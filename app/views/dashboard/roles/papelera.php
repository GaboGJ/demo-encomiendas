<?php
  $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
  $fecha = function ($f) { return $f ? date('d/m/Y H:i', strtotime($f)) : '-'; };
?>
<div class="container-fluid py-3 flex-grow-1">
  <div class="row">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">

        <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
          <div>
            <h5 class="font-weight-bolder text-dark mb-0">Papelera de Roles</h5>
            <p class="text-xs text-secondary mb-0">Roles eliminados. Al restaurarlos vuelven al listado como activos con sus permisos; su nombre sigue reservado mientras estén aquí.</p>
          </div>
          <a href="<?= rtrim(URL, '/') ?>/roles" class="btn btn-outline-secondary mb-0 border-radius-md px-3 d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto">
            <i class="material-symbols-rounded text-sm">arrow_back</i>
            <span class="font-weight-bold">Volver al Listado</span>
          </a>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-roles-papelera" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th data-priority="1" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Rol</th>
                  <th data-priority="3" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Descripción</th>
                  <th data-priority="2" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Eliminado el</th>
                  <th data-priority="1" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($eliminados as $r): ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <h6 class="mb-0 text-sm font-weight-bold text-dark"><?= $h($r['nombre_rol']) ?></h6>
                      <span class="text-xxs text-secondary font-weight-bold"><?= (int)$r['total_permisos'] ?> permiso(s)</span>
                    </td>
                    <td class="py-3 px-3 align-middle"><span class="text-xs text-secondary"><?= $h($r['descripcion_rol'] ?: 'Sin descripción') ?></span></td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="text-xs font-weight-bold text-dark"><?= $h($fecha($r['delete_rol'])) ?></span>
                    </td>
                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                      <button type="button" class="btn btn-sm btn-outline-success mb-0 d-inline-flex align-items-center gap-1"
                              onclick="restaurarRol(<?= (int)$r['id_rol'] ?>, '<?= $h(addslashes($r['nombre_rol'])) ?>')">
                        <i class="material-symbols-rounded text-sm">restore_from_trash</i> Restaurar
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  
  window.restaurarRol = function (id, nombre) {
    Swal.fire({
      title: '¿Restaurar el rol ' + nombre + '?',
      text: 'Volverá al listado de roles como activo.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, restaurar',
      cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (!r.isConfirmed) return;

      const fd = new FormData();
      fd.append('id_rol', id);
      fetch(baseUrl + '/roles/restaurar', { method: 'POST', body: fd })
        .then(function (res) { return res.json(); })
        .then(function (res) {
          // El mensaje de éxito ya quedó encolado en Flash (servidor) y sale al recargar
          if (res.success) window.location.reload();
          else Swal.fire('No se pudo restaurar', res.message || 'Ocurrió un error.', 'error');
        })
        .catch(function () { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); });
    });
  };
})();
</script>