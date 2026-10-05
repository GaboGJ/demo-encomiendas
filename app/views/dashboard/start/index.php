<?php
$k   = $dash['kpi'];
$bs  = function ($n) { return 'Bs. ' . number_format((float)$n, 2); };
$h   = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$ago = function ($f) {
    $s = time() - strtotime($f);
    if ($s < 3600)  return 'Hace ' . max(1, intval($s / 60)) . ' min';
    if ($s < 86400) return 'Hace ' . intval($s / 3600) . ' h';
    return date('d/m/Y H:i', strtotime($f));
};
$dif = $k['flete_ayer'] > 0 ? round(($k['flete'] - $k['flete_ayer']) * 100 / $k['flete_ayer']) : null;
$tarjetas = [
  ['Fletes del Día',       $bs($k['flete']),               'attach_money',   $dif === null ? 'Sin datos de ayer' : ($dif >= 0 ? '+' : '') . $dif . '% respecto a ayer', $dif !== null && $dif < 0 ? 'text-danger' : 'text-success'],
  ['Guías Emitidas Hoy',   (int)$k['guias'],               'local_post_office', (int)$k['transito'] . ' guías en tránsito', 'text-success'],
  ['Turnos Despachados',   (int)$k['despachos'],           'directions_bus', 'despachados hoy', 'text-success'],
  ['Cobros en Destino',    $bs($k['cod']),                 'pending_actions', (int)$k['cod_n'] . ' paquetes por cobrar', 'text-warning'],
];
?>
<div class="container-fluid py-4">
  <div class="row mb-4"><div class="col-12">
    <h3 class="mb-0 h4 font-weight-bolder">Resumen de Operaciones</h3>
    <p class="mb-0 text-sm text-muted">Control diario de encomiendas, ingresos por flete y despachos de flota.</p>
  </div></div>

  <div class="row">
    <?php foreach ($tarjetas as $t): ?>
    <div class="col-xl-3 col-sm-6 mb-4"><div class="card">
      <div class="card-header p-2 ps-3"><div class="d-flex justify-content-between">
        <div><p class="text-sm mb-0 text-capitalize"><?= $t[0] ?></p><h4 class="mb-0"><?= $h($t[1]) ?></h4></div>
        <div class="icon icon-md icon-shape bg-gradient-success shadow-success text-center border-radius-lg"><i class="material-symbols-rounded opacity-10"><?= $t[2] ?></i></div>
      </div></div>
      <hr class="dark horizontal my-0">
      <div class="card-footer p-2 ps-3"><p class="mb-0 text-sm <?= $t[4] ?> font-weight-bolder"><?= $h($t[3]) ?></p></div>
    </div></div>
    <?php endforeach; ?>
  </div>

  <div class="row mb-4">
    <div class="col-lg-8 col-md-6 mb-md-0 mb-4"><div class="card h-100">
      <div class="card-header pb-0"><h6>Manifiestos Recientes de Salida</h6>
        <p class="text-sm mb-0"><span class="font-weight-bold"><?= (int)$k['despachos'] ?> salidas</span> completadas hoy</p></div>
      <div class="card-body px-0 pb-2"><div class="table-responsive">
        <table class="table align-items-center mb-0">
          <thead><tr>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Chofer / Unidad</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Ruta</th>
            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Carga</th>
            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Salida</th>
          </tr></thead>
          <tbody>
          <?php foreach ($dash['manifiestos'] as $m): ?>
            <tr>
              <td><div class="d-flex px-2 py-1">
                <div class="avatar avatar-sm me-3 bg-gradient-success border-radius-md d-flex align-items-center justify-content-center"><span class="material-symbols-rounded text-white text-sm">directions_bus</span></div>
                <div class="d-flex flex-column justify-content-center"><h6 class="mb-0 text-sm">Unidad <?= $h($m['unidad'] ?: 'S/N') ?> - <?= $h(trim($m['chofer'])) ?></h6></div>
              </div></td>
              <td><span class="text-xs font-weight-bold"><?= $h($m['origen']) ?> ➔ <?= $h($m['destino']) ?></span></td>
              <td class="align-middle text-center"><span class="text-xs font-weight-bold"><?= $bs($m['carga']) ?></span></td>
              <td class="align-middle text-center"><span class="badge badge-sm bg-gradient-success"><?= $h(date('d/m H:i', strtotime($m['fecha_salida_turno'] . ' ' . $m['hora_salida_turno']))) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$dash['manifiestos']): ?><tr><td colspan="4" class="text-center text-xs text-secondary py-3">Aún no hay despachos.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div></div>
    </div></div>

    <div class="col-lg-4 col-md-6 mb-4"><div class="card h-100">
      <div class="card-header pb-0"><h6>Últimos Movimientos de Carga</h6></div>
      <div class="card-body p-3"><div class="timeline timeline-one-side">
        <?php foreach ($dash['movs'] as $mv): ?>
        <div class="timeline-block mb-3">
          <span class="timeline-step"><i class="material-symbols-rounded text-<?= $mv[2] ?> text-gradient"><?= $mv[1] ?></i></span>
          <div class="timeline-content">
            <h6 class="text-dark text-sm font-weight-bold mb-0"><?= $h($mv[0]) ?></h6>
            <p class="text-secondary font-weight-bold text-xs mt-1 mb-0"><?= $h($ago($mv[3])) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (!$dash['movs']): ?><p class="text-xs text-secondary mb-0">Sin movimientos recientes.</p><?php endif; ?>
      </div></div>
    </div></div>
  </div>

  <div class="row mb-4">
    <?php foreach ([['dash-bars','Flujo Semanal de Paquetes','Guías registradas por día'],
                    ['dash-line','Ingresos Mensuales de Flete','Últimos 6 meses (Bs.)'],
                    ['dash-tasks','Entregas Efectivas','% entregado por semana en destino']] as $g): ?>
    <div class="col-lg-4 col-md-6 mb-4"><div class="card h-100"><div class="card-body">
      <h6 class="mb-0"><?= $g[1] ?></h6><p class="text-sm"><?= $g[2] ?></p>
      <div class="pe-2"><div class="chart"><canvas id="<?= $g[0] ?>" class="chart-canvas" height="170"></canvas></div></div>
    </div></div></div>
    <?php endforeach; ?>
  </div>

  <div class="row mb-4"><div class="col-12"><div class="card">
    <div class="card-header pb-0"><h6>Personal Operativo y Roles</h6></div>
    <div class="card-body p-3"><div class="table-responsive">
      <table class="table align-items-center mb-0">
        <thead><tr>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Usuario</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Rol Asignado</th>
          <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
        </tr></thead>
        <tbody>
        <?php foreach ($dash['usuarios'] as $u): ?>
          <tr>
            <td><span class="text-xs font-weight-bold"><?= $h(trim($u['nombre'])) ?></span></td>
            <td><span class="badge badge-sm bg-gradient-info"><?= $h($u['nombre_rol']) ?></span></td>
            <td class="align-middle text-center"><span class="text-xs <?= $u['activo'] ? 'text-success' : 'text-secondary' ?> font-weight-bold"><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  </div></div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const D = <?= json_encode(['s' => $dash['semana'], 'm' => $dash['meses'], 'e' => $dash['efect']], JSON_UNESCAPED_UNICODE) ?>;
  const opts = { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}},
    scales:{ y:{grid:{color:'#e5e5e5'}, ticks:{color:'#737373'}, beginAtZero:true}, x:{grid:{display:false}, ticks:{color:'#737373'}} } };
  const mk = (id, type, o) => { const el = document.getElementById(id); if (el) new Chart(el.getContext('2d'), { type:type, options:opts, data:{ labels:o.labels,
    datasets:[{ data:o.data, backgroundColor:'#4CAF50', borderColor:'#4CAF50', borderWidth: type === 'bar' ? 0 : 2, borderRadius:4, pointRadius:3, tension:0 }] } }); };
  mk('dash-bars', 'bar', D.s);
  mk('dash-line', 'line', D.m);
  mk('dash-tasks', 'line', D.e);
});
</script>