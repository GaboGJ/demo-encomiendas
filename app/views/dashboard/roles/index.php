<?php require_once __DIR__ . '/detail.php'; ?>
<?php
  $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

  $total      = count($roles);
  $activos    = count(array_filter($roles, function ($r) { return (int)$r['estado_rol'] === 1; }));
  $inactivos  = $total - $activos;
  $usuarios   = array_sum(array_column($roles, 'total_usuarios'));
  $sinPermisos = count(array_filter($roles, function ($r) { return (int)$r['total_permisos'] === 0 && (int)$r['es_protegido'] !== 1; }));
  $gradientes = ['bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-dark', 'bg-gradient-secondary'];

  $tarjetas = [
    ['Roles Registrados',  $total . ' Roles',                 'shield_person',  'bg-gradient-success', 'shadow-success', 'text-dark'],
    ['Activos',            $activos . ' Activos · ' . $inactivos . ' Inact.', 'verified_user', 'bg-gradient-info', 'shadow-info', 'text-success'],
    ['Usuarios Asignados', $usuarios . ' Usuarios',           'group',          'bg-gradient-dark',    'shadow-dark',    'text-dark'],
    ['Sin Permisos',       $sinPermisos . ' Roles',           'lock',           'bg-gradient-warning', 'shadow-warning', $sinPermisos > 0 ? 'text-danger' : 'text-dark'],
  ];
?>
<div class="container-fluid py-3 flex-grow-1">

  <!-- MÉTRICAS -->
  <div class="row mb-4">
    <?php foreach ($tarjetas as $i => $t): ?>
      <div class="col-xl-3 col-sm-6 <?= $i < 3 ? 'mb-xl-0 mb-4' : '' ?>">
        <div class="card border-0 shadow-sm border-radius-xl">
          <div class="card-body p-3">
            <div class="row">
              <div class="col-8">
                <div class="numbers">
                  <p class="text-xs text-secondary mb-0 font-weight-bold"><?= $t[0] ?></p>
                  <h5 class="font-weight-bolder <?= $t[5] ?> mb-0"><?= $t[1] ?></h5>
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape <?= $t[3] ?> <?= $t[4] ?> text-center border-radius-md">
                  <i class="material-symbols-rounded opacity-10"><?= $t[2] ?></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- TABLA -->
  <div class="row">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">

        <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
          <div>
            <h5 class="font-weight-bolder text-dark mb-0">Listado de Roles del Sistema</h5>
            <p class="text-xs text-secondary mb-0">Administre los perfiles de acceso y asigne sus permisos por módulo</p>
          </div>
          <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <a href="<?= rtrim(URL, '/') ?>/roles/papelera" class="btn btn-outline-secondary mb-0 border-radius-md px-3 d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">delete_sweep</i>
              <span class="font-weight-bold">Papelera<?= $totalPapelera > 0 ? ' (' . (int)$totalPapelera . ')' : '' ?></span>
            </a>
            <a href="<?= rtrim(URL, '/') ?>/roles/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">add_moderator</i>
              <span class="font-weight-bold">Nuevo Rol</span>
            </a>
          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-roles" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th data-priority="1" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Rol / Perfil</th>
                  <th data-priority="4" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Descripción</th>
                  <th data-priority="3" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Usuarios / Permisos</th>
                  <th data-priority="2" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th data-priority="1" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($roles as $r): ?>
                  <?php
                    $activo    = (int)$r['estado_rol'] === 1;
                    $protegido = (int)$r['es_protegido'] === 1;
                    $esMio     = (int)$r['id_rol'] === (int)$idRolActual;
                    $gradient  = $gradientes[$r['id_rol'] % count($gradientes)];
                    $nombreJs  = $h(addslashes($r['nombre_rol']));
                  ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md <?= $gradient ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                          <i class="material-symbols-rounded">shield_person</i>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm font-weight-bold text-dark">
                            <?= $h($r['nombre_rol']) ?>
                            <?= $protegido ? ' <span class="badge badge-sm bg-gradient-dark border-radius-pill ms-1">Sistema</span>' : '' ?>
                            <?= $esMio ? ' <span class="text-xxs text-success">(su rol)</span>' : '' ?>
                          </h6>
                          <span class="text-xxs text-secondary font-weight-bold">Registrado: <?= $r['create_rol'] ? date('d/m/Y', strtotime($r['create_rol'])) : '-' ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-3 align-middle">
                      <span class="text-xs text-secondary"><?= $h($r['descripcion_rol'] ?: 'Sin descripción') ?></span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="text-xs font-weight-bold text-dark"><?= (int)$r['total_usuarios'] ?> usuario(s)</span>
                      <span class="d-block text-xxs font-weight-bold <?= $protegido ? 'text-success' : ((int)$r['total_permisos'] === 0 ? 'text-danger' : 'text-secondary') ?>">
                        <?= $protegido ? 'Acceso total' : (int)$r['total_permisos'] . ' permiso(s)' ?>
                      </span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="badge badge-sm <?= $activo ? 'bg-gradient-success' : 'bg-gradient-secondary' ?> border-radius-pill px-3 py-1 font-weight-bold">
                        <?= $activo ? 'Activo' : 'Inactivo' ?>
                      </span>
                    </td>
                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                      <div class="d-flex align-items-center justify-content-end gap-1 flex-wrap">
                        <button type="button" class="btn btn-link text-info p-2 mb-0" title="Ver Detalle" onclick="verDetalleRol(<?= (int)$r['id_rol'] ?>)">
                          <i class="material-symbols-rounded text-sm">visibility</i>
                        </button>
                        <a href="<?= rtrim(URL, '/') ?>/roles/permisos?id=<?= (int)$r['id_rol'] ?>" class="btn btn-link text-success p-2 mb-0" title="Configurar Permisos">
                          <i class="material-symbols-rounded text-sm">lock_open</i>
                        </a>
                        <a href="<?= rtrim(URL, '/') ?>/roles/update?id=<?= (int)$r['id_rol'] ?>" class="btn btn-link text-dark p-2 mb-0" title="Editar Rol">
                          <i class="material-symbols-rounded text-sm">edit</i>
                        </a>
                        <?php if (!$protegido && !$esMio): ?>
                          <?php if ($activo): ?>
                            <button type="button" class="btn btn-link text-warning p-2 mb-0" title="Desactivar Rol"
                                    onclick="cambiarEstadoRol(<?= (int)$r['id_rol'] ?>, 0, '<?= $nombreJs ?>', <?= (int)$r['total_usuarios'] ?>)">
                              <i class="material-symbols-rounded text-sm">block</i>
                            </button>
                          <?php else: ?>
                            <button type="button" class="btn btn-link text-success p-2 mb-0" title="Activar Rol"
                                    onclick="cambiarEstadoRol(<?= (int)$r['id_rol'] ?>, 1, '<?= $nombreJs ?>', 0)">
                              <i class="material-symbols-rounded text-sm">check_circle</i>
                            </button>
                          <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!$protegido): ?>
                          <button type="button" class="btn btn-link text-danger p-2 mb-0" title="Eliminar Rol"
                                  onclick="eliminarRol(<?= (int)$r['id_rol'] ?>, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">delete</i>
                          </button>
                        <?php endif; ?>
                      </div>
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

  function enviar(url, obj) {
    const fd = new FormData();
    Object.keys(obj).forEach(function (k) { fd.append(k, obj[k]); });
    return fetch(baseUrl + url, { method: 'POST', body: fd }).then(function (r) { return r.json(); });
  }

  function procesar(url, datos, titError) {
    enviar(url, datos)
      .then(function (res) {
        // El mensaje de éxito ya quedó encolado en Flash (servidor) y sale al recargar
        if (res.success) window.location.reload();
        else Swal.fire(titError, res.message || 'Ocurrió un error.', 'error');
      })
      .catch(function () { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); });
  }

  window.eliminarRol = function (id, nombre) {
    Swal.fire({
      title: '¿Eliminar el rol ' + nombre + '?',
      text: 'Pasará a la papelera y dejará de ofrecerse al registrar usuarios. Podrá restaurarlo después. No se puede eliminar si tiene usuarios asignados.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#f5365c'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/roles/eliminar', { id_rol: id }, 'No se pudo eliminar');
    });
  };

  window.cambiarEstadoRol = function (id, estado, nombre, usuarios) {
    Swal.fire({
      title: (estado ? '¿Activar el rol ' : '¿Desactivar el rol ') + nombre + '?',
      text: estado
        ? 'Los usuarios con este rol podrán volver a ingresar al sistema.'
        : 'Los ' + usuarios + ' usuario(s) con este rol no podrán ingresar hasta que se reactive.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: estado ? 'Sí, activar' : 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/roles/cambiarEstado', { id_rol: id, estado: estado }, 'No se pudo cambiar el estado');
    });
  };
})();
</script>