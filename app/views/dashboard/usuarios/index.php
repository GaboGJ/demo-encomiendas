<?php require_once __DIR__ . '/detail.php'; ?>
<?php
  // Métricas calculadas con datos reales
  $totalUsuarios   = count($usuarios);
  $totalActivos    = count(array_filter($usuarios, function ($u) { return (int)$u['estado_usuario'] === 1; }));
  $totalInactivos  = $totalUsuarios - $totalActivos;
  $rolesEnUso      = count(array_unique(array_column($usuarios, 'id_rol')));
  $sucursalesEnUso = count(array_unique(array_column($usuarios, 'id_sucursal')));

  $gradientes = ['bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-dark', 'bg-gradient-secondary'];
  $iniciales = function ($u) {
      return mb_strtoupper(mb_substr($u['nombre_persona'], 0, 1, 'UTF-8') . mb_substr($u['apellido_paterno_persona'], 0, 1, 'UTF-8'), 'UTF-8');
  };
?>
<div class="container-fluid py-3 flex-grow-1">

  <!-- MÉTRICAS -->
  <div class="row mb-4">
    <?php
      $tarjetas = [
        ['Usuarios Registrados', $totalUsuarios . ' Usuarios',  'group',                  'bg-gradient-success', 'shadow-success', 'text-dark'],
        ['Cuentas Activas',      $totalActivos . ' Activas',    'verified_user',          'bg-gradient-info',    'shadow-info',    'text-success'],
        ['Cuentas Inactivas',    $totalInactivos . ' Inactivas','block',                  'bg-gradient-warning', 'shadow-warning', 'text-dark'],
        ['Roles en Uso',         $rolesEnUso . ' Roles · ' . $sucursalesEnUso . ' Suc.', 'admin_panel_settings', 'bg-gradient-dark', 'shadow-dark', 'text-dark'],
      ];
    ?>
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
            <h5 class="font-weight-bolder text-dark mb-0">Listado de Usuarios Registrados en el Sistema</h5>
            <p class="text-xs text-secondary mb-0">Personal autorizado, rol asignado, sucursal y estado de cuenta</p>
          </div>
          <a href="<?= rtrim(URL, '/') ?>/usuarios/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto">
            <i class="material-symbols-rounded text-sm">person_add</i>
            <span class="font-weight-bold">Nuevo Usuario</span>
          </a>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-usuarios" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Usuario / Personal</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Rol Asignado</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Sucursal</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $u): ?>
                  <?php
                    $activo   = (int)$u['estado_usuario'] === 1;
                    $gradient = $gradientes[$u['id_rol'] % count($gradientes)];
                    $esYo     = (int)$u['id_usuario'] === (int)$idUsuarioActual;
                    $nombre   = trim($u['nombre_completo']);
                  ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md <?= $gradient ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white font-weight-bold">
                          <?= htmlspecialchars($iniciales($u)) ?>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm font-weight-bold text-dark"><?= htmlspecialchars($nombre) ?><?= $esYo ? ' <span class="text-xxs text-success">(usted)</span>' : '' ?></h6>
                          <span class="text-xxs text-secondary font-weight-bold">C.I. <?= htmlspecialchars($u['carnet_persona']) ?> · Cel: <?= htmlspecialchars($u['telefono_persona']) ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-3 align-middle">
                      <span class="badge badge-sm <?= $gradient ?> border-radius-pill px-3 py-1 font-weight-bold"><?= htmlspecialchars($u['nombre_rol']) ?></span>
                    </td>
                    <td class="py-3 px-3 align-middle">
                      <span class="text-xs font-weight-bold text-dark"><?= htmlspecialchars($u['nombre_sucursal']) ?></span>
                      <span class="d-block text-xxs text-secondary font-weight-bold"><?= htmlspecialchars($u['ciudad_sucursal']) ?></span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="badge badge-sm <?= $activo ? 'bg-gradient-success' : 'bg-gradient-secondary' ?> border-radius-pill px-3 py-1 font-weight-bold">
                        <?= $activo ? 'Activo' : 'Inactivo' ?>
                      </span>
                    </td>
                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                      <div class="d-flex align-items-center justify-content-end gap-1">
                        <button type="button" class="btn btn-link text-info p-2 mb-0" title="Ver Detalle" onclick="verDetalleUsuario(<?= (int)$u['id_usuario'] ?>)">
                          <i class="material-symbols-rounded text-sm">visibility</i>
                        </button>
                        <a href="<?= rtrim(URL, '/') ?>/usuarios/update?id=<?= (int)$u['id_usuario'] ?>" class="btn btn-link text-dark p-2 mb-0" title="Editar Usuario">
                          <i class="material-symbols-rounded text-sm">edit</i>
                        </a>
                        <?php if (!$esYo): ?>
                          <?php if ($activo): ?>
                            <button type="button" class="btn btn-link text-warning p-2 mb-0" title="Desactivar Cuenta"
                                    onclick="cambiarEstadoUsuario(<?= (int)$u['id_usuario'] ?>, 0, '<?= htmlspecialchars(addslashes($nombre), ENT_QUOTES) ?>')">
                              <i class="material-symbols-rounded text-sm">block</i>
                            </button>
                          <?php else: ?>
                            <button type="button" class="btn btn-link text-success p-2 mb-0" title="Activar Cuenta"
                                    onclick="cambiarEstadoUsuario(<?= (int)$u['id_usuario'] ?>, 1, '<?= htmlspecialchars(addslashes($nombre), ENT_QUOTES) ?>')">
                              <i class="material-symbols-rounded text-sm">check_circle</i>
                            </button>
                          <?php endif; ?>
                          <button type="button" class="btn btn-link text-danger p-2 mb-0" title="Eliminar Usuario"
                                  onclick="eliminarUsuario(<?= (int)$u['id_usuario'] ?>, '<?= htmlspecialchars(addslashes($nombre), ENT_QUOTES) ?>')">
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

  window.eliminarUsuario = function (id, nombre) {
    Swal.fire({
      title: '¿Eliminar a ' + nombre + '?',
      text: 'El usuario dejará de aparecer en el sistema y no podrá ingresar. Sus registros históricos se conservan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#f5365c'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/usuarios/eliminar', { id_usuario: id }, 'No se pudo eliminar');
    });
  };

  window.cambiarEstadoUsuario = function (id, estado, nombre) {
    Swal.fire({
      title: (estado ? '¿Activar' : '¿Desactivar') + ' la cuenta de ' + nombre + '?',
      text: estado ? 'El usuario podrá volver a ingresar al sistema.' : 'El usuario no podrá ingresar hasta que se reactive.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: estado ? 'Sí, activar' : 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/usuarios/cambiarEstado', { id_usuario: id, estado: estado }, 'No se pudo cambiar el estado');
    });
  };
})();
</script>