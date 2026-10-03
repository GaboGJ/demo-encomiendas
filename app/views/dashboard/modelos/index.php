   <?php require_once __DIR__ . '/detail.php'; ?>
<?php
  $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

  $total          = count($modelos);
  $activos        = count(array_filter($modelos, function ($m) { return (int)$m['estado_modelo'] === 1; }));
  $inactivos      = $total - $activos;
  $sinConfigurar  = count(array_filter($modelos, function ($m) { return (int)$m['total_asientos_modelo'] === 0; }));
  $gradientes     = ['bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-dark', 'bg-gradient-secondary'];

  $tarjetas = [
    ['Modelos Registrados', $total . ' Modelos',          'car_rental',    'bg-gradient-success', 'shadow-success', 'text-dark'],
    ['Activos',             $activos . ' Activos',        'verified_user', 'bg-gradient-info',    'shadow-info',    'text-success'],
    ['Inactivos',           $inactivos . ' Inactivos',    'block',         'bg-gradient-dark',    'shadow-dark',    'text-dark'],
    ['Sin Plano de Asientos', $sinConfigurar . ' Modelos', 'event_seat',   'bg-gradient-warning', 'shadow-warning', $sinConfigurar > 0 ? 'text-danger' : 'text-dark'],
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
            <h5 class="font-weight-bolder text-dark mb-0">Modelos de Vehículos y Distribución de Asientos</h5>
            <p class="text-xs text-secondary mb-0">Catálogo de modelos, su capacidad y el plano de asientos que usa la venta de pasajes</p>
          </div>
          <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <a href="<?= rtrim(URL, '/') ?>/modelos/papelera" class="btn btn-outline-secondary mb-0 border-radius-md px-3 d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">delete_sweep</i>
              <span class="font-weight-bold">Papelera<?= $totalPapelera > 0 ? ' (' . (int)$totalPapelera . ')' : '' ?></span>
            </a>
            <a href="<?= rtrim(URL, '/') ?>/modelos/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">add</i>
              <span class="font-weight-bold">Nuevo Modelo</span>
            </a>
          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-modelos" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th data-priority="1" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Modelo</th>
                  <th data-priority="3" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Capacidad / Pisos</th>
                  <th data-priority="4" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Vehículos</th>
                  <th data-priority="2" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th data-priority="1" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($modelos as $m): ?>
                  <?php
                    $activo   = (int)$m['estado_modelo'] === 1;
                    $asientos = (int)$m['total_asientos_modelo'];
                    $gradient = $gradientes[$m['id_modelo'] % count($gradientes)];
                    $nombreJs = $h(addslashes($m['nombre_modelo']));
                  ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md <?= $gradient ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                          <i class="material-symbols-rounded">directions_bus</i>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm font-weight-bold text-dark"><?= $h($m['nombre_modelo']) ?></h6>
                          <span class="text-xxs text-secondary font-weight-bold">Registrado: <?= $m['create_modelo'] ? date('d/m/Y', strtotime($m['create_modelo'])) : '-' ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <?php if ($asientos > 0): ?>
                        <span class="text-sm font-weight-bolder text-dark"><?= $asientos ?> Asientos</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold"><?= (int)$m['total_pisos'] ?> piso(s)</span>
                      <?php else: ?>
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">Sin configurar</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="text-xs font-weight-bold text-dark"><?= (int)$m['total_vehiculos'] ?> unidad(es)</span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="badge badge-sm <?= $activo ? 'bg-gradient-success' : 'bg-gradient-secondary' ?> border-radius-pill px-3 py-1 font-weight-bold">
                        <?= $activo ? 'Activo' : 'Inactivo' ?>
                      </span>
                    </td>
                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                      <div class="d-flex align-items-center justify-content-end gap-1 flex-wrap">
                        <button type="button" class="btn btn-link text-info p-2 mb-0" title="Ver Detalle" onclick="verDetalleModelo(<?= (int)$m['id_modelo'] ?>)">
                          <i class="material-symbols-rounded text-sm">visibility</i>
                        </button>
                        <a href="<?= rtrim(URL, '/') ?>/modelos/configurar?id=<?= (int)$m['id_modelo'] ?>" class="btn btn-link text-success p-2 mb-0" title="Configurar Asientos">
                          <i class="material-symbols-rounded text-sm">event_seat</i>
                        </a>
                        <a href="<?= rtrim(URL, '/') ?>/modelos/update?id=<?= (int)$m['id_modelo'] ?>" class="btn btn-link text-dark p-2 mb-0" title="Editar Modelo">
                          <i class="material-symbols-rounded text-sm">edit</i>
                        </a>
                        <?php if ($activo): ?>
                          <button type="button" class="btn btn-link text-warning p-2 mb-0" title="Desactivar Modelo"
                                  onclick="cambiarEstadoModelo(<?= (int)$m['id_modelo'] ?>, 0, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">block</i>
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-link text-success p-2 mb-0" title="Activar Modelo"
                                  onclick="cambiarEstadoModelo(<?= (int)$m['id_modelo'] ?>, 1, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">check_circle</i>
                          </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-link text-danger p-2 mb-0" title="Eliminar Modelo"
                                onclick="eliminarModelo(<?= (int)$m['id_modelo'] ?>, '<?= $nombreJs ?>')">
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

  // inicializarDataTable() vive en layouts/script.php (se carga después de esta vista)
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof inicializarDataTable === 'function') {
      inicializarDataTable('#datatable-modelos', { ordering: false, placeholder: 'Buscar modelo...' });
    }
  });

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

  window.eliminarModelo = function (id, nombre) {
    Swal.fire({
      title: '¿Eliminar el modelo ' + nombre + '?',
      text: 'Pasará a la papelera y dejará de ofrecerse al registrar móviles. Podrá restaurarlo después. No se puede eliminar si hay vehículos con este modelo.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#f5365c'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/modelos/eliminar', { id_modelo: id }, 'No se pudo eliminar');
    });
  };

  window.cambiarEstadoModelo = function (id, estado, nombre) {
    Swal.fire({
      title: (estado ? '¿Activar ' : '¿Desactivar ') + nombre + '?',
      text: estado ? 'El modelo volverá a ofrecerse al registrar móviles.' : 'El modelo no se ofrecerá para nuevos móviles hasta que se reactive. Los móviles existentes no se afectan.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: estado ? 'Sí, activar' : 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/modelos/cambiarEstado', { id_modelo: id, estado: estado }, 'No se pudo cambiar el estado');
    });
  };
})();
</script>