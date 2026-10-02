<?php require_once __DIR__ . '/detail.php'; ?>
<?php
  $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

  // Métricas calculadas con datos reales
  $totalSindicatos = count($sindicatos);
  $totalActivos    = count(array_filter($sindicatos, function ($s) { return (int)$s['estado_sindicato'] === 1; }));
  $totalInactivos  = $totalSindicatos - $totalActivos;
  $totalSocios     = array_sum(array_column($sindicatos, 'total_socios'));
  $totalSucursales = array_sum(array_column($sindicatos, 'total_sucursales'));

  $gradientes = ['bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-dark', 'bg-gradient-secondary'];
  $iniciales = function ($s) {
      $base = trim((string)($s['sigla_sindicato'] ?: $s['nombre_sindicato']));
      return mb_strtoupper(mb_substr($base, 0, 2, 'UTF-8'), 'UTF-8');
  };
?>
<div class="container-fluid py-3 flex-grow-1">

  <!-- MÉTRICAS -->
  <div class="row mb-4">
    <?php
      $tarjetas = [
        ['Sindicatos Registrados', $totalSindicatos . ' Sindicatos', 'domain',        'bg-gradient-success', 'shadow-success', 'text-dark'],
        ['Activos',                $totalActivos . ' Activos',       'verified_user', 'bg-gradient-info',    'shadow-info',    'text-success'],
        ['Inactivos',              $totalInactivos . ' Inactivos',   'block',         'bg-gradient-warning', 'shadow-warning', 'text-dark'],
        ['Socios y Sucursales',    $totalSocios . ' Socios · ' . $totalSucursales . ' Suc.', 'groups', 'bg-gradient-dark', 'shadow-dark', 'text-dark'],
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

  <!-- TABLA PRINCIPAL DE SINDICATOS -->
  <div class="row">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">

        <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
          <div>
            <h5 class="font-weight-bolder text-dark mb-0">Directorio de Sindicatos Asociados</h5>
            <p class="text-xs text-secondary mb-0">Organizaciones afiliadas, su NIT, contacto y la cantidad de sucursales y socios vigentes</p>
          </div>
          <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <a href="<?= rtrim(URL, '/') ?>/sindicatos/papelera" class="btn btn-outline-secondary mb-0 border-radius-md px-3 d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">delete_sweep</i>
              <span class="font-weight-bold">Papelera<?= $totalPapelera > 0 ? ' (' . (int)$totalPapelera . ')' : '' ?></span>
            </a>
            <a href="<?= rtrim(URL, '/') ?>/sindicatos/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
              <i class="material-symbols-rounded text-sm">add_business</i>
              <span class="font-weight-bold">Nuevo Sindicato</span>
            </a>
          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-sindicatos" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Sindicato</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">NIT y Contacto</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Sucursales / Socios</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sindicatos as $s): ?>
                  <?php
                    $activo    = (int)$s['estado_sindicato'] === 1;
                    $principal = (int)$s['es_principal_sindicato'] === 1;
                    $gradient  = $gradientes[$s['id_sindicato'] % count($gradientes)];
                    $nombre    = $s['nombre_sindicato'];
                    $nombreJs  = $h(addslashes($nombre));
                  ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md <?= $gradient ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white font-weight-bold">
                          <?= $h($iniciales($s)) ?>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm font-weight-bold text-dark"><?= $h($nombre) ?><?= $principal ? ' <span class="badge badge-sm bg-gradient-dark border-radius-pill ms-1">Principal</span>' : '' ?></h6>
                          <span class="text-xxs text-secondary font-weight-bold"><?= $s['sigla_sindicato'] ? 'Sigla: ' . $h($s['sigla_sindicato']) : 'Sin sigla' ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-3 align-middle">
                      <span class="text-xs font-weight-bold text-dark">NIT <?= $h($s['personeria_sindicato'] ?: '-') ?></span>
                      <span class="d-block text-xxs text-secondary font-weight-bold">Tel: <?= $h($s['telefono_sindicato'] ?: '-') ?></span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="text-xs font-weight-bold text-dark"><?= (int)$s['total_sucursales'] ?> Suc. · <?= (int)$s['total_socios'] ?> Socios</span>
                    </td>
                    <td class="align-middle text-center py-3 px-3">
                      <span class="badge badge-sm <?= $activo ? 'bg-gradient-success' : 'bg-gradient-secondary' ?> border-radius-pill px-3 py-1 font-weight-bold">
                        <?= $activo ? 'Activo' : 'Inactivo' ?>
                      </span>
                    </td>
                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                      <div class="d-flex align-items-center justify-content-end gap-1">
                        <button type="button" class="btn btn-link text-info p-2 mb-0" title="Ver Detalle" onclick="verDetalleSindicato(<?= (int)$s['id_sindicato'] ?>)">
                          <i class="material-symbols-rounded text-sm">visibility</i>
                        </button>
                        <a href="<?= rtrim(URL, '/') ?>/sindicatos/update?id=<?= (int)$s['id_sindicato'] ?>" class="btn btn-link text-dark p-2 mb-0" title="Editar Sindicato">
                          <i class="material-symbols-rounded text-sm">edit</i>
                        </a>
                        <?php if (!$principal): ?>
                          <?php if ($activo): ?>
                            <button type="button" class="btn btn-link text-warning p-2 mb-0" title="Desactivar Sindicato"
                                    onclick="cambiarEstadoSindicato(<?= (int)$s['id_sindicato'] ?>, 0, '<?= $nombreJs ?>')">
                              <i class="material-symbols-rounded text-sm">block</i>
                            </button>
                          <?php else: ?>
                            <button type="button" class="btn btn-link text-success p-2 mb-0" title="Activar Sindicato"
                                    onclick="cambiarEstadoSindicato(<?= (int)$s['id_sindicato'] ?>, 1, '<?= $nombreJs ?>')">
                              <i class="material-symbols-rounded text-sm">check_circle</i>
                            </button>
                          <?php endif; ?>
                          <button type="button" class="btn btn-link text-danger p-2 mb-0" title="Eliminar Sindicato"
                                  onclick="eliminarSindicato(<?= (int)$s['id_sindicato'] ?>, '<?= $nombreJs ?>')">
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

  window.eliminarSindicato = function (id, nombre) {
    Swal.fire({
      title: '¿Eliminar a ' + nombre + '?',
      text: 'El sindicato pasará a la papelera y dejará de aparecer en el sistema. Podrá restaurarlo después. Solo se puede eliminar si no tiene sucursales ni socios vigentes.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#f5365c'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/sindicatos/eliminar', { id_sindicato: id }, 'No se pudo eliminar');
    });
  };

  window.cambiarEstadoSindicato = function (id, estado, nombre) {
    Swal.fire({
      title: (estado ? '¿Activar a ' : '¿Desactivar a ') + nombre + '?',
      text: estado ? 'El sindicato y sus usuarios podrán volver a ingresar al sistema.' : 'Los usuarios de este sindicato no podrán ingresar hasta que se reactive.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: estado ? 'Sí, activar' : 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (r.isConfirmed) procesar('/sindicatos/cambiarEstado', { id_sindicato: id, estado: estado }, 'No se pudo cambiar el estado');
    });
  };
})();
</script>