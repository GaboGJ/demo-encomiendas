<?php require_once __DIR__ . '/detail.php'; ?>
<?php
  $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

  $total     = count($rutas);
  $activas   = count(array_filter($rutas, function ($r) { return (int)$r['estado_ruta'] === 1; }));
  $inactivas = $total - $activas;
  $promedio  = $total ? array_sum(array_column($rutas, 'base_precio_pasaje')) / $total : 0;
  $sinTarifa = count(array_filter($rutas, function ($r) { return (int)$r['total_tarifas'] === 0; }));
  $gradientes = ['bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-dark', 'bg-gradient-secondary'];

  $tarjetas = [
    ['Rutas Registradas',      $total . ' Rutas',                'alt_route',    'bg-gradient-success', 'shadow-success', 'text-dark'],
    ['Activas',                $activas . ' Act. · ' . $inactivas . ' Inact.', 'verified_user', 'bg-gradient-info', 'shadow-info', 'text-success'],
    ['Tarifa Promedio Pasaje', 'Bs. ' . number_format($promedio, 2), 'payments', 'bg-gradient-dark',    'shadow-dark',    'text-dark'],
    ['Sin Tarifa de Encomienda', $sinTarifa . ' Rutas',          'inventory_2',  'bg-gradient-warning', 'shadow-warning', $sinTarifa > 0 ? 'text-danger' : 'text-dark'],
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
                  <h5 class="font-weight-bolder <?= $t[5] ?> mb-0"><?= $h($t[1]) ?></h5>
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
            <h5 class="font-weight-bolder text-dark mb-0">Tabla Oficial de Rutas y Tarifarios Vigentes</h5>
            <p class="text-xs text-secondary mb-0">Trayectos entre sucursales por sindicato, precio del pasaje y tarifas de encomienda por tipo de contenido</p>
          </div>
          <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <a href="<?= rtrim(URL, '/') ?>/rutas/papelera" class="btn btn-outline-secondary mb-0 border-radius-md px-3 d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">delete_sweep</i>
              <span class="font-weight-bold">Papelera<?= $totalPapelera > 0 ? ' (' . (int)$totalPapelera . ')' : '' ?></span>
            </a>
            <a href="<?= rtrim(URL, '/') ?>/rutas/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">add_road</i>
              <span class="font-weight-bold">Nueva Ruta</span>
            </a>
          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-rutas" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th data-priority="1" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Origen / Destino</th>
                  <th data-priority="3" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Sindicato</th>
                  <th data-priority="4" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Tarifa Pasaje (Bs.)</th>
                  <th data-priority="5" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Tarifas Encomienda (Bs.)</th>
                  <th data-priority="2" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th data-priority="1" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rutas as $r): ?>
                  <?php
                    $activo   = (int)$r['estado_ruta'] === 1;
                    $gradient = $gradientes[$r['id_precio_pasaje'] % count($gradientes)];
                    $nTar     = (int)$r['total_tarifas'];
                    $nombreJs = $h(addslashes($r['ciudad_origen'] . ' ➔ ' . $r['ciudad_destino'] . ' (' . $r['nombre_sindicato'] . ')'));
                  ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md <?= $gradient ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                          <i class="material-symbols-rounded">route</i>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm font-weight-bold text-dark"><?= $h($r['ciudad_origen']) ?> ➔ <?= $h($r['ciudad_destino']) ?></h6>
                          <span class="text-xxs text-secondary font-weight-bold"><?= $h($r['nombre_origen']) ?> · <?= $h($r['nombre_destino']) ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-3 align-middle">
                      <span class="text-xs font-weight-bold text-dark"><?= $h($r['nombre_sindicato']) ?></span>
                      <?php if ($r['sigla_sindicato']): ?><span class="d-block text-xxs text-secondary font-weight-bold"><?= $h($r['sigla_sindicato']) ?></span><?php endif; ?>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="text-sm font-weight-bolder text-dark">Bs. <?= number_format($r['base_precio_pasaje'], 2) ?></span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <?php if ($nTar > 0): ?>
                        <span class="text-xs font-weight-bold text-dark">
                          <?= $r['tarifa_min'] == $r['tarifa_max']
                              ? 'Bs. ' . number_format($r['tarifa_min'], 2)
                              : 'Bs. ' . number_format($r['tarifa_min'], 2) . ' – ' . number_format($r['tarifa_max'], 2) ?>
                        </span>
                        <span class="d-block text-xxs text-secondary font-weight-bold"><?= $nTar ?> tipo(s) de contenido</span>
                      <?php else: ?>
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">Sin tarifas</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="badge badge-sm <?= $activo ? 'bg-gradient-success' : 'bg-gradient-secondary' ?> border-radius-pill px-3 py-1 font-weight-bold">
                        <?= $activo ? 'Activa' : 'Inactiva' ?>
                      </span>
                    </td>
                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                      <div class="d-flex align-items-center justify-content-end gap-1 flex-wrap">
                        <button type="button" class="btn btn-link text-info p-2 mb-0" title="Ver Detalle" onclick="verDetalleRuta(<?= (int)$r['id_precio_pasaje'] ?>)">
                          <i class="material-symbols-rounded text-sm">visibility</i>
                        </button>
                        <a href="<?= rtrim(URL, '/') ?>/rutas/update?id=<?= (int)$r['id_precio_pasaje'] ?>" class="btn btn-link text-dark p-2 mb-0" title="Editar Ruta y Tarifas">
                          <i class="material-symbols-rounded text-sm">edit</i>
                        </a>
                        <?php if ($activo): ?>
                          <button type="button" class="btn btn-link text-warning p-2 mb-0" title="Desactivar Ruta"
                                  onclick="cambiarEstadoRuta(<?= (int)$r['id_precio_pasaje'] ?>, 0, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">block</i>
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-link text-success p-2 mb-0" title="Activar Ruta"
                                  onclick="cambiarEstadoRuta(<?= (int)$r['id_precio_pasaje'] ?>, 1, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">check_circle</i>
                          </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-link text-danger p-2 mb-0" title="Eliminar Ruta"
                                onclick="eliminarRuta(<?= (int)$r['id_precio_pasaje'] ?>, '<?= $nombreJs ?>')">
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
        if (res.success) window.location.reload();
        else Swal.fire(titError, res.message || 'Ocurrió un error.', 'error');
      })
      .catch(function () { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); });
  }

  window.eliminarRuta = function (id, nombre) {
    Swal.fire({
      title: '¿Eliminar la ruta ' + nombre + '?',
      text: 'Pasará a la papelera junto con sus tarifas y dejará de ofrecerse en turnos y encomiendas. Podrá restaurarla después. No se puede eliminar si hay turnos abiertos en la ruta.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#f5365c'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/rutas/eliminar', { id_ruta: id }, 'No se pudo eliminar');
    });
  };

  window.cambiarEstadoRuta = function (id, estado, nombre) {
    Swal.fire({
      title: (estado ? '¿Activar la ruta ' : '¿Desactivar la ruta ') + nombre + '?',
      text: estado
        ? 'La ruta y sus tarifas volverán a ofrecerse en turnos y encomiendas.'
        : 'La ruta y sus tarifas no se ofrecerán para nuevos turnos ni encomiendas hasta que se reactive.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: estado ? 'Sí, activar' : 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/rutas/cambiarEstado', { id_ruta: id, estado: estado }, 'No se pudo cambiar el estado');
    });
  };
})();
</script>