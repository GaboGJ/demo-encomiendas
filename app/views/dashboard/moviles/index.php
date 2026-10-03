<?php require_once __DIR__ . '/detail.php'; ?>
<?php
  $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

  // Métricas calculadas con datos reales
  $totalMoviles   = count($moviles);
  $totalActivos   = count(array_filter($moviles, function ($m) { return (int)$m['estado_vehiculo'] === 1; }));
  $totalInactivos = $totalMoviles - $totalActivos;
  $sinChofer      = count(array_filter($moviles, function ($m) { return (int)$m['total_choferes'] === 0; }));

  $gradientes = ['bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-dark', 'bg-gradient-secondary'];
?>
<div class="container-fluid py-3 flex-grow-1">

  <!-- MÉTRICAS -->
  <div class="row mb-4">
    <?php
      $tarjetas = [
        ['Unidades Registradas', $totalMoviles . ' Vehículos',   'directions_bus', 'bg-gradient-success', 'shadow-success', 'text-dark'],
        ['Activas',              $totalActivos . ' Unidades',    'check_circle',   'bg-gradient-info',    'shadow-info',    'text-success'],
        ['Inactivas',            $totalInactivos . ' Unidades',  'block',          'bg-gradient-warning', 'shadow-warning', 'text-dark'],
        ['Sin Chofer Asignado',  $sinChofer . ' Unidades',       'person_off',     'bg-gradient-dark',    'shadow-dark',    'text-dark'],
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

  <!-- TABLA PRINCIPAL -->
  <div class="row">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">

        <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
          <div>
            <h5 class="font-weight-bolder text-dark mb-0">Parque Automotor y Unidades Asignadas</h5>
            <p class="text-xs text-secondary mb-0">Listado de móviles del sindicato, su socio titular, modelo y estado</p>
          </div>
          <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <a href="<?= rtrim(URL, '/') ?>/moviles/papelera" class="btn btn-outline-secondary mb-0 border-radius-md px-3 d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">delete_sweep</i>
              <span class="font-weight-bold">Papelera<?= $totalPapelera > 0 ? ' (' . (int)$totalPapelera . ')' : '' ?></span>
            </a>
            <a href="<?= rtrim(URL, '/') ?>/moviles/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">add</i>
              <span class="font-weight-bold">Registrar Vehículo</span>
            </a>
          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-flotas" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Unidad / Placa</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Socio Titular</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Modelo / Capacidad</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($moviles as $m): ?>
                  <?php
                    $activo   = (int)$m['estado_vehiculo'] === 1;
                    $gradient = $gradientes[$m['id_modelo'] % count($gradientes)];
                    $nombreJs = $h(addslashes('Unidad ' . $m['numero_interno_vehiculo']));
                  ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md <?= $gradient ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                          <i class="material-symbols-rounded">directions_bus</i>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm font-weight-bold text-dark">Unidad <?= $h($m['numero_interno_vehiculo']) ?></h6>
                          <span class="text-xxs text-secondary font-weight-bold">Placa: <?= $h($m['placa_vehiculo'] ?: 'S/P') ?><?= $m['color_vehiculo'] ? ' · ' . $h($m['color_vehiculo']) : '' ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-3 align-middle">
                      <span class="text-xs font-weight-bold text-dark"><?= $h($m['socio_nombre']) ?></span>
                      <span class="d-block text-xxs text-secondary font-weight-bold"><?= $m['codigo_socio'] ? 'Cód. ' . $h($m['codigo_socio']) : 'Socio titular' ?> · <?= (int)$m['total_choferes'] ?> chofer(es)</span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="text-sm font-weight-bolder text-dark"><?= (int)$m['total_asientos_modelo'] ?> Asientos</span>
                      <span class="d-block text-xxs text-secondary font-weight-bold"><?= $h($m['nombre_modelo']) ?></span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="badge badge-sm <?= $activo ? 'bg-gradient-success' : 'bg-gradient-secondary' ?> border-radius-pill px-3 py-1 font-weight-bold">
                        <?= $activo ? 'Activo' : 'Inactivo' ?>
                      </span>
                    </td>
                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                      <div class="d-flex align-items-center justify-content-end gap-1">
                        <button type="button" class="btn btn-link text-info p-2 mb-0" title="Ver Detalle" onclick="verDetalleMovil(<?= (int)$m['id_vehiculo'] ?>)">
                          <i class="material-symbols-rounded text-sm">visibility</i>
                        </button>
                        <a href="<?= rtrim(URL, '/') ?>/moviles/update?id=<?= (int)$m['id_vehiculo'] ?>" class="btn btn-link text-dark p-2 mb-0" title="Editar Unidad">
                          <i class="material-symbols-rounded text-sm">edit</i>
                        </a>
                        <?php if ($activo): ?>
                          <button type="button" class="btn btn-link text-warning p-2 mb-0" title="Desactivar Unidad"
                                  onclick="cambiarEstadoMovil(<?= (int)$m['id_vehiculo'] ?>, 0, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">block</i>
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-link text-success p-2 mb-0" title="Activar Unidad"
                                  onclick="cambiarEstadoMovil(<?= (int)$m['id_vehiculo'] ?>, 1, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">check_circle</i>
                          </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-link text-danger p-2 mb-0" title="Eliminar Unidad"
                                onclick="eliminarMovil(<?= (int)$m['id_vehiculo'] ?>, '<?= $nombreJs ?>')">
                          <i class="material-symbols-rounded text-sm">delete</i>
                        </button>
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

  window.eliminarMovil = function (id, nombre) {
    Swal.fire({
      title: '¿Eliminar la ' + nombre + '?',
      text: 'El móvil pasará a la papelera y dejará de ofrecerse para nuevos turnos. Podrá restaurarlo después. No se puede eliminar si tiene turnos abiertos.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#f5365c'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/moviles/eliminar', { id_vehiculo: id }, 'No se pudo eliminar');
    });
  };

  window.cambiarEstadoMovil = function (id, estado, nombre) {
    Swal.fire({
      title: (estado ? '¿Activar la ' : '¿Desactivar la ') + nombre + '?',
      text: estado ? 'El móvil podrá volver a asignarse a turnos.' : 'El móvil no se ofrecerá para nuevos turnos hasta que se reactive.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: estado ? 'Sí, activar' : 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/moviles/cambiarEstado', { id_vehiculo: id, estado: estado }, 'No se pudo cambiar el estado');
    });
  };
})();
</script>