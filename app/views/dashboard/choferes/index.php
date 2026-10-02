<?php require_once __DIR__ . '/detail.php'; ?>
<?php
  $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

  // Estado de la licencia según los días que faltan para su vencimiento
  $licencia = function ($dias) {
      if ($dias === null) return ['Sin fecha', 'bg-gradient-secondary', 'text-secondary'];
      $dias = (int)$dias;
      if ($dias < 0)   return ['Vencida', 'bg-gradient-danger', 'text-danger'];
      if ($dias <= 30) return ['Por vencer', 'bg-gradient-warning', 'text-warning'];
      return ['Vigente', 'bg-gradient-success', 'text-success'];
  };

  // Métricas con datos reales
  $totalChoferes  = count($choferes);
  $totalActivos   = count(array_filter($choferes, function ($c) { return (int)$c['estado_chofer'] === 1; }));
  $totalInactivos = $totalChoferes - $totalActivos;
  $vencidas       = count(array_filter($choferes, function ($c) { return $c['dias_vencimiento'] !== null && (int)$c['dias_vencimiento'] < 0; }));
  $porVencer      = count(array_filter($choferes, function ($c) { return $c['dias_vencimiento'] !== null && (int)$c['dias_vencimiento'] >= 0 && (int)$c['dias_vencimiento'] <= 30; }));

  $gradientes = ['bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-dark', 'bg-gradient-secondary'];
  $iniciales = function ($c) {
      return mb_strtoupper(mb_substr($c['nombre_persona'], 0, 1, 'UTF-8') . mb_substr($c['apellido_paterno_persona'], 0, 1, 'UTF-8'), 'UTF-8');
  };
?>
<div class="container-fluid py-3 flex-grow-1">

  <!-- MÉTRICAS -->
  <div class="row mb-4">
    <?php
      $tarjetas = [
        ['Choferes Registrados', $totalChoferes . ' Choferes',   'badge',         'bg-gradient-success', 'shadow-success', 'text-dark'],
        ['Cuentas Activas',      $totalActivos . ' Activos',     'verified_user', 'bg-gradient-info',    'shadow-info',    'text-success'],
        ['Inactivos',            $totalInactivos . ' Inactivos', 'block',         'bg-gradient-dark',    'shadow-dark',    'text-dark'],
        ['Licencias en Alerta',  $vencidas . ' Vencidas · ' . $porVencer . ' Por vencer', 'warning', 'bg-gradient-warning', 'shadow-warning', ($vencidas + $porVencer) > 0 ? 'text-danger' : 'text-dark'],
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
            <h5 class="font-weight-bolder text-dark mb-0">Listado General de Choferes</h5>
            <p class="text-xs text-secondary mb-0">Directorio de conductores, licencia, vigencia y vehículos asignados</p>
          </div>
          <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <a href="<?= rtrim(URL, '/') ?>/choferes/papelera" class="btn btn-outline-secondary mb-0 border-radius-md px-3 d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">delete_sweep</i>
              <span class="font-weight-bold">Papelera<?= $totalPapelera > 0 ? ' (' . (int)$totalPapelera . ')' : '' ?></span>
            </a>
            <a href="<?= rtrim(URL, '/') ?>/choferes/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">person_add</i>
              <span class="font-weight-bold">Nuevo Chofer</span>
            </a>
          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-choferes" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Chofer / C.I.</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Licencia / Categoría</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Vigencia</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Vehículos</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($choferes as $c): ?>
                  <?php
                    $activo   = (int)$c['estado_chofer'] === 1;
                    $gradient = $gradientes[$c['id_chofer'] % count($gradientes)];
                    $lic      = $licencia($c['dias_vencimiento']);
                    $nombre   = $c['nombre_completo'];
                    $nombreJs = $h(addslashes($nombre));
                  ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md <?= $gradient ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white font-weight-bold">
                          <?= $h($iniciales($c)) ?>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm font-weight-bold text-dark"><?= $h($nombre) ?></h6>
                          <span class="text-xxs text-secondary font-weight-bold">C.I. <?= $h($c['carnet_persona']) ?> · Cel: <?= $h($c['telefono_persona']) ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-3 align-middle">
                      <span class="text-xs font-weight-bold text-dark"><?= $h($c['licencia_chofer']) ?></span>
                      <span class="d-block text-xxs text-secondary font-weight-bold"><?= $c['categoria_licencia_chofer'] ? 'Categoría ' . $h($c['categoria_licencia_chofer']) : 'Sin categoría' ?></span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="badge badge-sm <?= $lic[1] ?> border-radius-pill px-3 py-1 font-weight-bold"><?= $lic[0] ?></span>
                      <span class="d-block text-xxs <?= $lic[2] ?> font-weight-bold mt-1">
                        <?= $c['vencimiento_licencia_chofer'] ? date('d/m/Y', strtotime($c['vencimiento_licencia_chofer'])) : '-' ?>
                      </span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="text-xs font-weight-bold text-dark"><?= (int)$c['total_vehiculos'] ?> asignado(s)</span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="badge badge-sm <?= $activo ? 'bg-gradient-success' : 'bg-gradient-secondary' ?> border-radius-pill px-3 py-1 font-weight-bold">
                        <?= $activo ? 'Activo' : 'Inactivo' ?>
                      </span>
                    </td>
                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                      <div class="d-flex align-items-center justify-content-end gap-1">
                        <button type="button" class="btn btn-link text-info p-2 mb-0" title="Ver Detalle" onclick="verDetalleChofer(<?= (int)$c['id_chofer'] ?>)">
                          <i class="material-symbols-rounded text-sm">visibility</i>
                        </button>
                        <a href="<?= rtrim(URL, '/') ?>/choferes/update?id=<?= (int)$c['id_chofer'] ?>" class="btn btn-link text-dark p-2 mb-0" title="Editar Chofer">
                          <i class="material-symbols-rounded text-sm">edit</i>
                        </a>
                        <?php if ($activo): ?>
                          <button type="button" class="btn btn-link text-warning p-2 mb-0" title="Desactivar Chofer"
                                  onclick="cambiarEstadoChofer(<?= (int)$c['id_chofer'] ?>, 0, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">block</i>
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-link text-success p-2 mb-0" title="Activar Chofer"
                                  onclick="cambiarEstadoChofer(<?= (int)$c['id_chofer'] ?>, 1, '<?= $nombreJs ?>')">
                            <i class="material-symbols-rounded text-sm">check_circle</i>
                          </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-link text-danger p-2 mb-0" title="Eliminar Chofer"
                                onclick="eliminarChofer(<?= (int)$c['id_chofer'] ?>, '<?= $nombreJs ?>')">
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

  window.eliminarChofer = function (id, nombre) {
    Swal.fire({
      title: '¿Eliminar a ' + nombre + '?',
      text: 'El chofer pasará a la papelera y dejará de aparecer en el sistema. Podrá restaurarlo después. Solo se puede eliminar si no tiene vehículos asignados.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#f5365c'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/choferes/eliminar', { id_chofer: id }, 'No se pudo eliminar');
    });
  };

  window.cambiarEstadoChofer = function (id, estado, nombre) {
    Swal.fire({
      title: (estado ? '¿Activar a ' : '¿Desactivar a ') + nombre + '?',
      text: estado ? 'El chofer volverá a estar disponible.' : 'El chofer quedará inactivo hasta que se reactive.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: estado ? 'Sí, activar' : 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/choferes/cambiarEstado', { id_chofer: id, estado: estado }, 'No se pudo cambiar el estado');
    });
  };
})();
</script>