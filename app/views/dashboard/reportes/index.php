<?php
/**
 * views/dashboard/reportes/index.php
 * Variables: $tipos, $sucursales, $veTodas, $mesActual, $anioActual, $hoy, $inicioMes
 */
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$tarjetas = [
  ['kIni', 'Saldo Inicial / Vienen',  'account_balance_wallet', 'bg-gradient-dark',    'shadow-dark',    'text-dark'],
  ['kIng', 'Ingresos Totales',        'payments',               'bg-gradient-success', 'shadow-success', 'text-success'],
  ['kEgr', 'Egresos y Préstamos',     'trending_down',          'bg-gradient-info',    'shadow-info',    'text-danger'],
  ['kSal', 'Saldo Final',             'savings',                'bg-gradient-success', 'shadow-success', 'text-success'],
];
?>
<style>
  .rep-tabla thead th { text-transform: uppercase; font-size: .65rem; font-weight: 700; color: #8392ab; white-space: nowrap;
                        padding: .75rem .6rem; border-top: 1px solid #f0f2f5; border-bottom: 1px solid #f0f2f5; }
  .rep-tabla tbody td { padding: .6rem; vertical-align: middle; }
  .rep-tabla tfoot td { padding: .7rem .6rem; font-weight: 800; background: #e8f5e9; font-size: .8rem; }
  .rep-saldo { font-size: .8rem; font-weight: 800; color: #344767; }
  .rep-tipos { display: flex; flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; gap: 0; }
  .rep-tipos .btn { white-space: nowrap; flex-shrink: 0; margin-bottom: 0; border-radius: 0; }
  .rep-tipos .btn:first-child { border-radius: .5rem 0 0 .5rem; }
  .rep-tipos .btn:last-child  { border-radius: 0 .5rem .5rem 0; }
  .rep-tipos .btn + .btn { margin-left: -1px; }
</style>

<div class="container-fluid py-3 flex-grow-1">

  <!-- TARJETAS DE MÉTRICAS (se actualizan al generar) -->
  <div class="row mb-4">
    <?php foreach ($tarjetas as $i => $t): ?>
      <div class="col-xl-3 col-sm-6 <?= $i < 3 ? 'mb-xl-0 mb-4' : '' ?>">
        <div class="card border-0 shadow-sm border-radius-xl"><div class="card-body p-3"><div class="row">
          <div class="col-8"><div class="numbers">
            <p class="text-xs text-secondary mb-0 font-weight-bold"><?= $t[1] ?></p>
            <h5 class="font-weight-bolder <?= $t[5] ?> mb-0" id="<?= $t[0] ?>">-</h5>
          </div></div>
          <div class="col-4 text-end"><div class="icon icon-shape <?= $t[3] ?> <?= $t[4] ?> text-center border-radius-md">
            <i class="material-symbols-rounded opacity-10"><?= $t[2] ?></i>
          </div></div>
        </div></div></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- FILTROS -->
  <div class="row mb-4"><div class="col-12">
    <div class="card border-0 shadow-sm border-radius-xl p-3 p-md-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div>
          <h5 class="font-weight-bolder text-dark mb-1">Generador de Informes Económicos</h5>
          <p class="text-xs text-secondary mb-0" id="repDescTipo">Control financiero por periodo.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary mb-0 d-inline-flex align-items-center gap-1" id="btnImprimir">
            <i class="material-symbols-rounded text-sm">print</i> Imprimir
          </button>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 d-inline-flex align-items-center gap-1" id="btnExcel">
            <i class="material-symbols-rounded text-sm">download</i> Exportar Excel
          </button>
        </div>
      </div>

      <!-- TIPO DE REPORTE: botones agrupados -->
      <label class="form-label text-xs font-weight-bold text-dark mb-1">Tipo de Reporte</label>
      <div class="rep-tipos mb-3" id="tiposReporte" role="group">
        <?php foreach ($tipos as $k => $t): ?>
          <button type="button"
                  class="btn btn-sm d-inline-flex align-items-center gap-1 <?= $k === 'consolidado' ? 'bg-gradient-success text-white' : 'btn-outline-success' ?>"
                  data-tipo="<?= $h($k) ?>" data-desc="<?= $h($t['desc']) ?>" data-titulo="<?= $h($t['titulo']) ?>">
            <i class="material-symbols-rounded text-sm"><?= $h($t['icono']) ?></i> <?= $h($t['titulo']) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="row g-3 align-items-end">
        <div class="col-6 col-md-3 d-none" data-tipos="diario semanal mensual">
          <label class="form-label text-xs font-weight-bold text-dark mb-0">Mes</label>
          <div class="input-group input-group-outline is-filled"><input type="month" class="form-control" id="fMes" value="<?= $h($mesActual) ?>"></div>
        </div>
        <div class="col-6 col-md-3 d-none" data-tipos="anual">
          <label class="form-label text-xs font-weight-bold text-dark mb-0">Gestión (año)</label>
          <div class="input-group input-group-outline is-filled"><input type="number" min="2000" max="2100" step="1" class="form-control" id="fAnio" value="<?= $h($anioActual) ?>"></div>
        </div>
        <div class="col-6 col-md-3 d-none" data-tipos="rango">
          <label class="form-label text-xs font-weight-bold text-dark mb-0">Fecha inicio</label>
          <div class="input-group input-group-outline is-filled"><input type="date" class="form-control" id="fDesde" value="<?= $h($inicioMes) ?>"></div>
        </div>
        <div class="col-6 col-md-3 d-none" data-tipos="rango">
          <label class="form-label text-xs font-weight-bold text-dark mb-0">Fecha fin</label>
          <div class="input-group input-group-outline is-filled"><input type="date" class="form-control" id="fHasta" value="<?= $h($hoy) ?>"></div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <label class="form-label text-xs font-weight-bold text-dark mb-0">Sucursal</label>
          <div class="input-group input-group-outline is-filled">
            <select class="form-control" id="fSucursal" <?= (!$veTodas || count($sucursales) <= 1) ? 'disabled' : '' ?>>
              <?php if ($veTodas && count($sucursales) > 1): ?><option value="0">Todas las sucursales</option><?php endif; ?>
              <?php foreach ($sucursales as $s): ?>
                <option value="<?= (int)$s['id_sucursal'] ?>"><?= $h($s['ciudad_sucursal'] . ' (' . $s['nombre_sucursal'] . ')') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
          <button type="button" class="btn btn-sm bg-gradient-dark w-100 mb-0 d-inline-flex align-items-center justify-content-center gap-1" id="btnGenerar">
            <i class="material-symbols-rounded text-sm">filter_alt</i> Aplicar
          </button>
        </div>
      </div>
    </div>
  </div></div>

  <!-- RESULTADO -->
  <div class="row"><div class="col-12">
    <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
      <div class="card-header bg-white p-3 p-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
        <div>
          <h6 class="font-weight-bolder text-dark mb-0" id="repTitulo">Informe</h6>
          <p class="text-xxs text-secondary mb-1" id="repSub">&nbsp;</p>
          <span class="rep-saldo" id="repSaldo"></span>
        </div>
        <span class="badge bg-gradient-success text-xxs px-2 py-1" id="repBadge">Filtro: Consolidado</span>
      </div>
      <hr class="horizontal dark my-0 opacity-2">
      <div class="card-body px-0 pt-3 pb-2" id="repContenedor">
        <div class="text-center text-xs text-secondary py-5">Cargando informe...</div>
      </div>
    </div>
  </div></div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const $ = id => document.getElementById(id);
  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
  const num = (n, dec) => (parseFloat(n) || 0).toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
  const bs = n => 'Bs. ' + num(n, 2);

  let tipoActual = 'consolidado';
  let reqId = 0;      // solo la respuesta de la última petición se pinta
  let tablaSeq = 0;   // ids únicos de tabla en cada generación

  function celda(tipoCol) {
    return function (d, type) {
      if (type !== 'display') return d == null ? '' : d;
      if (d === null || d === '') return '';
      const t = tipoCol === 'monto' ? num(d, 2) : tipoCol === 'entero' ? num(d, 0) : esc(d);
      return '<span class="text-xs font-weight-bold text-dark">' + t + '</span>';
    };
  }

  function qs() {
    return new URLSearchParams({
      tipo: tipoActual, mes: $('fMes').value, anio: $('fAnio').value,
      desde: $('fDesde').value, hasta: $('fHasta').value,
      sucursal: $('fSucursal').value || 0
    }).toString();
  }

  function valido() {
    const t = tipoActual;
    if (['diario', 'semanal', 'mensual'].indexOf(t) !== -1 && !$('fMes').value) {
      Swal.fire({ icon: 'warning', title: 'Falta el mes', text: 'Seleccione el mes del informe.' }); return false;
    }
    if (t === 'anual') {
      const a = parseInt($('fAnio').value);
      if (!a || a < 2000 || a > 2100) { Swal.fire({ icon: 'warning', title: 'Año inválido', text: 'Indique un año entre 2000 y 2100.' }); return false; }
    }
    if (t === 'rango' && (!$('fDesde').value || !$('fHasta').value || $('fDesde').value > $('fHasta').value)) {
      Swal.fire({ icon: 'warning', title: 'Rango inválido', text: 'Indique fecha de inicio y fin (inicio no mayor al fin).' }); return false;
    }
    return true;
  }

  function aplicarTipo(btn) {
    tipoActual = btn.dataset.tipo;
    document.querySelectorAll('#tiposReporte [data-tipo]').forEach(function (x) {
      const on = x === btn;
      x.classList.toggle('bg-gradient-success', on);
      x.classList.toggle('text-white', on);
      x.classList.toggle('btn-outline-success', !on);
    });
    document.querySelectorAll('[data-tipos]').forEach(g => g.classList.toggle('d-none', g.dataset.tipos.split(' ').indexOf(tipoActual) === -1));
    $('repDescTipo').textContent = btn.dataset.desc || '';
    $('repBadge').textContent = 'Filtro: ' + btn.dataset.titulo;
  }

  /** Destruye todas las DataTables del contenedor antes de volver a pintar. */
  function limpiarTablas() {
    const cont = $('repContenedor');
    if (window.jQuery && $.fn.DataTable) {
      window.jQuery(cont).find('table').each(function () {
        if ($.fn.DataTable.isDataTable(this)) window.jQuery(this).DataTable().destroy();
      });
    }
    cont.innerHTML = '';
  }

  function pintarTablas(r) {
    //limpiarTablas();
    const cont = $('repContenedor');

    r.tablas.forEach(function (t) {
      const id = 'tablaRep' + (++tablaSeq);
      const wrap = document.createElement('div');
      wrap.className = 'px-0 mb-4';
      const pie = t.pie ? '<tfoot><tr>' + t.cols.map(function (c, i) {
        const v = t.pie[i];
        const txt = v === null || v === undefined ? '' : (c[1] === 'monto' ? num(v, 2) : c[1] === 'entero' ? num(v, 0) : esc(v));
        return '<td class="' + (i === 0 ? 'ps-4' : (c[1] === 'texto' ? '' : 'text-end')) + '">' + txt + '</td>';
      }).join('') + '</tr></tfoot>' : '';
      wrap.innerHTML = (t.titulo ? '<h6 class="text-xs font-weight-bolder text-uppercase text-success px-4 mb-2">' + esc(t.titulo) + '</h6>' : '') +
        '<div class="table-responsive p-0"><table id="' + id + '" class="table table-borderless align-items-center mb-0 w-100 rep-tabla">' + pie + '</table></div>';
      cont.appendChild(wrap);

      const n = t.cols.length;
      const grande = t.filas.length > 10;
      const base = {
        ordering: false,
        data: t.filas.map(f => f.slice()),
        language: { emptyTable: 'Sin registros' },
        columns: t.cols.map(function (c, i) {
          return { title: c[0], render: celda(c[1]), data: i,
                   className: (i === 0 ? 'ps-4 ' : '') + (c[1] === 'texto' ? '' : 'text-end'),
                   responsivePriority: i === 0 ? 1 : (i === n - 1 ? 2 : 3 + i) };
        })
      };
      // Tablas largas: DataTable completo del script general. Cortas: solo la tabla.
      inicializarDataTable('#' + id, Object.assign(base, grande
        ? { placeholder: 'Buscar en el reporte...', pageLength: 10 }
        : { paging: false, searching: false, info: false, dom: 't' }));
    });
  }

  function generar() {
    if (!valido()) return;
    const mio = ++reqId;
    //limpiarTablas();
    $('repContenedor').innerHTML = '<div class="text-center text-xs text-secondary py-5">Generando informe...</div>';

    fetch(baseUrl + '/reportes/datos?' + qs() + '&_=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' })
      .then(r => r.json())
      .then(function (res) {
        if (mio !== reqId) return; // llegó una respuesta más nueva
        if (!res.success) {
          $('repContenedor').innerHTML = '<div class="text-center text-xs text-danger py-5">' + esc(res.message) + '</div>';
          Swal.fire('No se pudo generar', res.message, 'error');
          return;
        }
        const r = res.reporte;
        $('repTitulo').textContent = r.titulo;
        $('repSub').textContent = $('fSucursal').options[$('fSucursal').selectedIndex].text + ' · ' + r.periodo;
        $('repSaldo').textContent = r.saldo_label + ': ' + bs(r.saldo);
        $('kIni').textContent = bs(r.kpi.inicial);
        $('kIng').textContent = bs(r.kpi.ing);
        $('kEgr').textContent = bs((parseFloat(r.kpi.egr) || 0) + (parseFloat(r.kpi.pre) || 0));
        $('kSal').textContent = bs(r.kpi.saldo);
        pintarTablas(r);
      })
      .catch(function () {
        if (mio !== reqId) return;
        $('repContenedor').innerHTML = '<div class="text-center text-xs text-danger py-5">Ocurrió un error en el servidor.</div>';
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
      });
  }

  $('tiposReporte').addEventListener('click', function (e) {
    const b = e.target.closest('[data-tipo]');
    if (!b || b.dataset.tipo === tipoActual) return;
    aplicarTipo(b);
    generar();
  });

  $('btnGenerar').addEventListener('click', generar);
  ['fMes', 'fAnio', 'fDesde', 'fHasta', 'fSucursal'].forEach(id => $(id).addEventListener('change', generar));
  $('btnImprimir').addEventListener('click', function () { if (valido()) lanzarImpresionIframe(baseUrl + '/reportes/imprimir?' + qs()); });
  $('btnExcel').addEventListener('click', function () { if (valido()) window.location.href = baseUrl + '/reportes/exportar?' + qs(); });

  document.addEventListener('DOMContentLoaded', function () {
    aplicarTipo(document.querySelector('#tiposReporte [data-tipo="consolidado"]'));
    generar();
  });
})();
</script>