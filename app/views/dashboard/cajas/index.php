<?php require_once __DIR__ . '/detail.php'; ?>
<?php require_once __DIR__ . '/modales.php'; ?>
<?php
  $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

  $total     = count($cajas);
  $abiertas  = count(array_filter($cajas, function ($c) { return !empty($c['id_historial_abierto']); }));
  $inactivas = count(array_filter($cajas, function ($c) { return (int)$c['estado_caja'] !== 1; }));
  $libres    = $total - $abiertas - $inactivas;
  $gradientes = ['bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-dark', 'bg-gradient-secondary'];

  $tarjetas = [
    ['Cajas Registradas', $total . ' Cajas',        'point_of_sale', 'bg-gradient-success', 'shadow-success', 'text-dark'],
    ['Cajas Abiertas',    $abiertas . ' Abiertas',  'lock_open',     'bg-gradient-info',    'shadow-info',    'text-success'],
    ['Disponibles',       $libres . ' Cerradas',    'lock',          'bg-gradient-dark',    'shadow-dark',    'text-dark'],
    ['Inactivas',         $inactivas . ' Inactivas','block',         'bg-gradient-warning', 'shadow-warning', 'text-dark'],
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
            <h5 class="font-weight-bolder text-dark mb-0">Cajas y Turnos de Caja</h5>
            <p class="text-xs text-secondary mb-0">Cajas de su sucursal: aperture turnos, registre movimientos y realice el arqueo y cierre</p>
          </div>
          <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <a href="<?= rtrim(URL, '/') ?>/cajas/papelera" class="btn btn-outline-secondary mb-0 border-radius-md px-3 d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">delete_sweep</i>
              <span class="font-weight-bold">Papelera<?= $totalPapelera > 0 ? ' (' . (int)$totalPapelera . ')' : '' ?></span>
            </a>
            <a href="<?= rtrim(URL, '/') ?>/cajas/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">add</i>
              <span class="font-weight-bold">Nueva Caja</span>
            </a>
          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-cajas" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th data-priority="1" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Caja / Sucursal</th>
                  <th data-priority="4" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Turno Actual</th>
                  <th data-priority="5" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Turnos</th>
                  <th data-priority="2" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th data-priority="1" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cajas as $c): ?>
                  <?php
                    $activo   = (int)$c['estado_caja'] === 1;
                    $abierta  = !empty($c['id_historial_abierto']);
                    $gradient = $abierta ? 'bg-gradient-success' : ($activo ? $gradientes[$c['id_caja'] % count($gradientes)] : 'bg-gradient-secondary');
                    $puedeAbrir  = $activo && !$abierta && (int)$c['id_sucursal'] === (int)$idSucursalActual;
                    $puedeCerrar = $abierta && ((int)$c['id_usuario_abierto'] === (int)$idUsuarioActual || $esPrincipal);
                    $nombreJs = $h(addslashes($c['nombre_caja']));
                  ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md <?= $gradient ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                          <i class="material-symbols-rounded">point_of_sale</i>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm font-weight-bold text-dark"><?= $h($c['nombre_caja']) ?></h6>
                          <span class="text-xxs text-secondary font-weight-bold"><?= $h($c['ciudad_sucursal']) ?> · <?= $h($c['nombre_sucursal']) ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-3 align-middle">
                      <?php if ($abierta): ?>
                        <span class="text-xs font-weight-bold text-dark"><?= $h($c['cajero_abierto'] ?: 'Cajero') ?></span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">
                          Desde <?= date('d/m/Y H:i', strtotime($c['fecha_apertura_abierto'])) ?> · Inicial Bs. <?= number_format($c['monto_inicial_abierto'], 2) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-xs text-secondary">Sin turno abierto</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="text-xs font-weight-bold text-dark"><?= (int)$c['total_turnos'] ?> turno(s)</span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <?php if ($abierta): ?>
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Abierta</span>
                      <?php elseif ($activo): ?>
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Cerrada</span>
                      <?php else: ?>
                        <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">Inactiva</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                      <div class="d-flex align-items-center justify-content-end gap-1 flex-wrap">
                        <?php if ($puedeAbrir): ?>
                          <button type="button" class="btn btn-sm btn-outline-success mb-0 px-2 py-1 d-inline-flex align-items-center gap-1" title="Aperturar caja"
                                  onclick="abrirCaja(<?= (int)$c['id_caja'] ?>, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">lock_open</i> Abrir
                          </button>
                        <?php endif; ?>
                        <?php if ($puedeCerrar): ?>
                          <button type="button" class="btn btn-sm btn-outline-dark mb-0 px-2 py-1 d-inline-flex align-items-center gap-1" title="Ingresos y egresos manuales"
                                  onclick="abrirMovimientos(<?= (int)$c['id_historial_abierto'] ?>)">
                            <i class="material-symbols-rounded text-sm">swap_vert</i> Movimientos
                          </button>
                          <button type="button" class="btn btn-sm bg-gradient-danger text-white mb-0 px-2 py-1 d-inline-flex align-items-center gap-1" title="Arqueo y cierre"
                                  onclick="cerrarCaja(<?= (int)$c['id_historial_abierto'] ?>)">
                            <i class="material-symbols-rounded text-sm">calculate</i> Arqueo
                          </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-link text-info p-2 mb-0" title="Ver Detalle" onclick="verDetalleCaja(<?= (int)$c['id_caja'] ?>)">
                          <i class="material-symbols-rounded text-sm">visibility</i>
                        </button>
                        <a href="<?= rtrim(URL, '/') ?>/cajas/update?id=<?= (int)$c['id_caja'] ?>" class="btn btn-link text-dark p-2 mb-0" title="Editar Caja">
                          <i class="material-symbols-rounded text-sm">edit</i>
                        </a>
                        <?php if (!$abierta): ?>
                          <?php if ($activo): ?>
                            <button type="button" class="btn btn-link text-warning p-2 mb-0" title="Desactivar Caja"
                                    onclick="cambiarEstadoCaja(<?= (int)$c['id_caja'] ?>, 0, '<?= $nombreJs ?>')">
                              <i class="material-symbols-rounded text-sm">block</i>
                            </button>
                          <?php else: ?>
                            <button type="button" class="btn btn-link text-success p-2 mb-0" title="Activar Caja"
                                    onclick="cambiarEstadoCaja(<?= (int)$c['id_caja'] ?>, 1, '<?= $nombreJs ?>')">
                              <i class="material-symbols-rounded text-sm">check_circle</i>
                            </button>
                          <?php endif; ?>
                          <button type="button" class="btn btn-link text-danger p-2 mb-0" title="Eliminar Caja"
                                  onclick="eliminarCaja(<?= (int)$c['id_caja'] ?>, '<?= $nombreJs ?>')">
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
        if (res.success) window.location.reload();
        else Swal.fire(titError, res.message || 'Ocurrió un error.', 'error');
      })
      .catch(function () { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); });
  }

  window.eliminarCaja = function (id, nombre) {
    Swal.fire({
      title: '¿Eliminar la caja ' + nombre + '?',
      text: 'Pasará a la papelera y su historial de turnos se conserva. Podrá restaurarla después. No se puede eliminar con un turno abierto.',
      icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', confirmButtonColor: '#f5365c'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/cajas/eliminar', { id_caja: id }, 'No se pudo eliminar');
    });
  };

  window.cambiarEstadoCaja = function (id, estado, nombre) {
    Swal.fire({
      title: (estado ? '¿Activar la caja ' : '¿Desactivar la caja ') + nombre + '?',
      text: estado ? 'La caja podrá volver a aperturarse.' : 'La caja no podrá aperturarse hasta que se reactive.',
      icon: 'question', showCancelButton: true, confirmButtonText: estado ? 'Sí, activar' : 'Sí, desactivar', cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/cajas/cambiarEstado', { id_caja: id, estado: estado }, 'No se pudo cambiar el estado');
    });
  };
})();
</script>