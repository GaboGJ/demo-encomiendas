<?php
/**
 * views/dashboard/reportes/index.php
 * Variables: $tipos, $sucursales, $veTodas, $mesActual, $anioActual, $hoy, $inicioMes
 */
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<div class="container-fluid py-3 flex-grow-1">

  <!-- Filtros -->
  <div class="card border-0 shadow-sm border-radius-xl mb-4">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <h5 class="font-weight-bolder text-dark mb-1">Informes económicos</h5>
          <p class="text-xs text-secondary mb-0" id="repDescTipo">&nbsp;</p>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary mb-0 d-inline-flex align-items-center justify-content-center gap-1 flex-fill" id="btnImprimir">
            <i class="material-symbols-rounded text-sm">print</i> Imprimir
          </button>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 d-inline-flex align-items-center justify-content-center gap-1 flex-fill" id="btnExcel">
            <i class="material-symbols-rounded text-sm">download</i> Excel
          </button>
        </div>
      </div>

      <!-- Tipo de reporte: cuadrícula responsive -->
      <label class="form-label text-xs font-weight-bold text-dark mb-2 d-block">Tipo de reporte</label>
      <div class="row g-2 mb-4" id="tiposReporte">
        <?php foreach ($tipos as $k => $t): ?>
          <div class="col-4 col-md-2">
            <button type="button"
                    class="btn btn-sm w-100 mb-0 py-2 px-1 d-flex flex-column flex-xl-row align-items-center justify-content-center gap-1 text-xs <?= $k === 'consolidado' ? 'bg-gradient-success text-white' : 'btn-outline-success' ?>"
                    data-tipo="<?= $h($k) ?>" data-desc="<?= $h($t['desc']) ?>" data-titulo="<?= $h($t['titulo']) ?>">
              <i class="material-symbols-rounded text-lg"><?= $h($t['icono']) ?></i>
              <span class="text-center lh-1"><?= $h($t['titulo']) ?></span>
            </button>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-6 col-xl-3 d-none" data-tipos="diario">
          <label class="form-label text-xs font-weight-bold text-dark mb-1">Día</label>
          <div class="input-group input-group-outline is-filled"><input type="date" class="form-control" id="fDia" value="<?= $h($hoy) ?>"></div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 d-none" data-tipos="semanal mensual">
          <label class="form-label text-xs font-weight-bold text-dark mb-1">Mes</label>
          <div class="input-group input-group-outline is-filled"><input type="month" class="form-control" id="fMes" value="<?= $h($mesActual) ?>"></div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 d-none" data-tipos="semanal">
          <label class="form-label text-xs font-weight-bold text-dark mb-1">Semana</label>
          <div class="input-group input-group-outline is-filled"><select class="form-control" id="fSemana"></select></div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 d-none" data-tipos="anual">
          <label class="form-label text-xs font-weight-bold text-dark mb-1">Gestión (año)</label>
          <div class="input-group input-group-outline is-filled"><input type="number" min="2000" max="2100" step="1" class="form-control" id="fAnio" value="<?= $h($anioActual) ?>"></div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3 d-none" data-tipos="rango">
          <label class="form-label text-xs font-weight-bold text-dark mb-1">Desde</label>
          <div class="input-group input-group-outline is-filled"><input type="date" class="form-control" id="fDesde" value="<?= $h($inicioMes) ?>"></div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3 d-none" data-tipos="rango">
          <label class="form-label text-xs font-weight-bold text-dark mb-1">Hasta</label>
          <div class="input-group input-group-outline is-filled"><input type="date" class="form-control" id="fHasta" value="<?= $h($hoy) ?>"></div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
          <label class="form-label text-xs font-weight-bold text-dark mb-1">Sucursal</label>
          <div class="input-group input-group-outline is-filled">
            <select class="form-control" id="fSucursal" <?= (!$veTodas || count($sucursales) <= 1) ? 'disabled' : '' ?>>
              <?php if ($veTodas && count($sucursales) > 1): ?><option value="0">Todas las sucursales</option><?php endif; ?>
              <?php foreach ($sucursales as $s): ?>
                <option value="<?= (int)$s['id_sucursal'] ?>"><?= $h($s['ciudad_sucursal'] . ' (' . $s['nombre_sucursal'] . ')') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-auto ms-xl-auto">
          <button type="button" class="btn bg-gradient-dark w-100 mb-0 d-inline-flex align-items-center justify-content-center gap-1" id="btnGenerar">
            <i class="material-symbols-rounded text-sm">filter_alt</i> Aplicar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Encabezado del resultado -->
  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 px-1 mb-3">
    <div>
      <h6 class="font-weight-bolder text-dark mb-1" id="repTitulo">Informe</h6>
      <p class="text-xs text-secondary mb-0" id="repSub">&nbsp;</p>
    </div>
    <span class="badge bg-gradient-success px-3 py-2" id="repBadge">General</span>
  </div>

  <!-- Resultado -->
  <div id="repContenedor">
    <div class="text-center text-xs text-secondary py-5">Cargando informe...</div>
  </div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const MES_ACTUAL = '<?= $h($mesActual) ?>';
  const $ = id => document.getElementById(id);
  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
  const num = (n, dec) => (parseFloat(n) || 0).toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
  const bs = n => 'Bs. ' + num(n, 2);
  const pad = n => String(n).padStart(2, '0');
  const DETALLE = ['diario', 'semanal', 'mensual'];

  let tipoActual = 'consolidado';
  let reqId = 0;
  let chart = null;

  /* ---------- Semanas según el mes ---------- */
  function cargarSemanas() {
    const v = $('fMes').value, sel = $('fSemana');
    if (!v) { sel.innerHTML = ''; return; }
    const prev = sel.value;
    const p = v.split('-').map(Number), dim = new Date(p[0], p[1], 0).getDate();
    let html = '';
    for (let s = 1, n = 1; s <= dim; s += 7, n++) {
      html += '<option value="' + n + '">Semana ' + n + ' (' + pad(s) + ' al ' + pad(Math.min(s + 6, dim)) + ')</option>';
    }
    sel.innerHTML = html;
    if (prev && sel.querySelector('option[value="' + prev + '"]')) sel.value = prev;
    else sel.value = (v === MES_ACTUAL) ? Math.ceil(new Date().getDate() / 7) : 1;
  }

  /* ---------- Filtros ---------- */
  function qs() {
    return new URLSearchParams({
      tipo: tipoActual, dia: $('fDia').value, mes: $('fMes').value, semana: $('fSemana').value,
      anio: $('fAnio').value, desde: $('fDesde').value, hasta: $('fHasta').value,
      sucursal: $('fSucursal').value || 0
    }).toString();
  }

  function valido() {
    const t = tipoActual, aviso = (ti, tx) => { Swal.fire({ icon: 'warning', title: ti, text: tx }); return false; };
    if (t === 'diario' && !$('fDia').value) return aviso('Falta el día', 'Seleccione el día del informe.');
    if (t === 'semanal' && (!$('fMes').value || !$('fSemana').value)) return aviso('Falta la semana', 'Seleccione el mes y la semana.');
    if (t === 'mensual' && !$('fMes').value) return aviso('Falta el mes', 'Seleccione el mes del informe.');
    if (t === 'anual') {
      const a = parseInt($('fAnio').value);
      if (!a || a < 2000 || a > 2100) return aviso('Año inválido', 'Indique un año entre 2000 y 2100.');
    }
    if (t === 'rango' && (!$('fDesde').value || !$('fHasta').value || $('fDesde').value > $('fHasta').value)) {
      return aviso('Rango inválido', 'Indique fecha de inicio y fin (inicio no mayor al fin).');
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
    $('repBadge').textContent = btn.dataset.titulo;
  }

  /* ---------- Componentes ---------- */
  function card(inner, extra) {
    return '<div class="card border-0 shadow-sm border-radius-xl ' + (extra || '') + '">' + inner + '</div>';
  }

  /** Resumen del periodo (siempre primero) */
  function resumen(k) {
    const ini = parseFloat(k.inicial) || 0, ing = parseFloat(k.ing) || 0;
    const items = [
      //['Vienen', 'Saldo del periodo anterior', ini, 'text-dark', 'account_balance_wallet', 'bg-gradient-dark'],
      ['Ingresos', 'Pasajes, encomiendas y otros', ing, 'text-success', 'payments', 'bg-gradient-success'],
      //['Disponible', 'Vienen + Ingresos', ini + ing, 'text-dark', 'add_card', 'bg-gradient-info'],
      ['Egresos', 'Gastos del periodo', k.egr, 'text-danger', 'trending_down', 'bg-gradient-danger'],
      //['Préstamos', 'Préstamos entregados', k.pre, 'text-danger', 'handshake', 'bg-gradient-warning'],
      ['Saldo final', 'Disponible − egresos − préstamos', k.saldo, 'text-success', 'savings', 'bg-gradient-success']
    ];
    return '<h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3 px-1">Resumen del periodo</h6>' +
      '<div class="row g-3 mb-4">' + items.map(function (i) {
        return '<div class="col-12 col-sm-6 col-xl-4">' + card(
          '<div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between gap-3">' +
            '<div class="min-width-0">' +
              '<p class="text-xs text-secondary font-weight-bold mb-0">' + i[0] + '</p>' +
              '<h5 class="font-weight-bolder ' + i[3] + ' mb-0 text-break">' + bs(i[2]) + '</h5>' +
              '<p class="text-xxs text-secondary mb-0">' + i[1] + '</p>' +
            '</div>' +
            '<div class="icon icon-md icon-shape ' + i[5] + ' text-center border-radius-md flex-shrink-0 d-flex align-items-center justify-content-center">' +
              '<i class="material-symbols-rounded text-white">' + i[4] + '</i></div>' +
          '</div>', 'h-100') + '</div>';
      }).join('') + '</div>';
  }

  /** Tarjetas por periodo (mes o día) en cuadrícula */
  function periodos(t) {
    const grande = t.filas.length > 12;
    const items = t.filas.map(function (f) {
      const gasto = (parseFloat(f[4]) || 0) + (parseFloat(f[5]) || 0);
      const neg = parseFloat(f[6]) < 0;
      return '<div class="col-12 col-sm-6 col-xl-4">' + card(
        '<div class="card-body p-3">' +
          '<div class="d-flex justify-content-between align-items-center mb-3 gap-2">' +
            '<h6 class="text-sm font-weight-bold text-dark mb-0">' + esc(f[0]) + '</h6>' +
            '<span class="badge badge-sm ' + (neg ? 'bg-gradient-danger' : 'bg-gradient-success') + '">' + num(f[6], 2) + '</span>' +
          '</div>' +
          '<div class="row g-2 text-center">' +
            '<div class="col-4"><span class="d-block text-xxs text-secondary">Ingresos</span><span class="text-xs font-weight-bolder text-success">' + num(f[1], 2) + '</span></div>' +
            '<div class="col-4"><span class="d-block text-xxs text-secondary">Vienen</span><span class="text-xs font-weight-bolder text-dark">' + num(f[2], 2) + '</span></div>' +
            '<div class="col-4"><span class="d-block text-xxs text-secondary">Egresos</span><span class="text-xs font-weight-bolder text-danger">' + num(gasto, 2) + '</span></div>' +
          '</div>' +
        '</div>', 'h-100 border') + '</div>';
    }).join('');

    return '<h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3 px-1">Detalle por periodo</h6>' +
      '<div class="' + (grande ? 'overflow-auto pe-1' : '') + '"' + (grande ? ' style="max-height:560px"' : '') + '>' +
      '<div class="row g-3">' + (items || '<div class="col-12 text-center text-xs text-secondary py-4">Sin registros</div>') + '</div></div>';
  }

  /** Bloque de detalle: filas con nombre y montos etiquetados (se adaptan a cualquier ancho) */
  function bloque(t) {
    const cols = t.cols;
    const val = (c, v) => c[1] === 'monto' ? num(v, 2) : c[1] === 'entero' ? num(v, 0) : esc(v);
    const color = c => {
      const l = c[0].toLowerCase();
      return (l.indexOf('egreso') > -1 || l.indexOf('préstamo') > -1) ? 'text-danger' : (l.indexOf('ingreso') > -1 ? 'text-success' : 'text-dark');
    };
    const montos = (fila, esPie) => cols.slice(1).map(function (c, j) {
      const v = fila[j + 1];
      if (v === null || v === undefined || v === '') return '';
      return '<div class="text-end"><span class="d-block text-xxs text-secondary">' + esc(c[0]) + '</span>' +
             '<span class="text-sm font-weight-bold ' + (esPie ? 'text-dark' : color(c)) + '">' + val(c, v) + '</span></div>';
    }).join('');

    const filas = t.filas.map(function (f) {
      return '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-3 border-bottom border-light">' +
        '<span class="text-sm font-weight-bold text-dark text-wrap">' + esc(f[0]) + '</span>' +
        '<div class="d-flex flex-wrap justify-content-end gap-3 ms-auto">' + montos(f, false) + '</div></div>';
    }).join('');

    const pie = t.pie ? '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-3 bg-gray-100">' +
      '<span class="text-sm font-weight-bolder text-dark">' + esc(t.pie[0]) + '</span>' +
      '<div class="d-flex flex-wrap justify-content-end gap-3 ms-auto">' + montos(t.pie, true) + '</div></div>' : '';

    const cuerpo = t.filas.length > 10 ? '<div class="overflow-auto" style="max-height:400px">' + filas + '</div>' : filas;

    return card(
      '<div class="px-3 pt-3 pb-2"><h6 class="text-xs font-weight-bolder text-uppercase text-success mb-0">' + esc(t.titulo || 'Detalle') + '</h6></div>' +
      (cuerpo || '<div class="text-center text-xs text-secondary py-4">Sin registros</div>') + pie,
      'h-100 overflow-hidden');
  }

  function pintar(r) {
    const cont = $('repContenedor');
    if (chart) { chart.destroy(); chart = null; }
    const detalle = DETALLE.indexOf(r.tipo) !== -1;
    let html = resumen(r.kpi);

    if (detalle) {
      const det = r.tablas.filter(t => t.titulo !== 'RESUMEN DEL PERIODO');
      if (det.length) {
        html += '<h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3 px-1">Detalle</h6>' +
          '<div class="row g-3">' + det.map(t => '<div class="col-12 col-xl-6">' + bloque(t) + '</div>').join('') + '</div>';
      }
    } else {
      const t0 = r.tablas[0];
      if (t0.filas.length > 1) {
        html += card('<div class="card-body p-3 p-md-4">' +
          '<h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3">Ingresos vs egresos</h6>' +
          '<div class="chart"><canvas id="repChart" class="chart-canvas" height="220"></canvas></div></div>', 'mb-4');
      }
      html += periodos(t0);
    }
    cont.innerHTML = html;

    const cv = $('repChart');
    if (cv && typeof Chart !== 'undefined') {
      const f = r.tablas[0].filas;
      chart = new Chart(cv.getContext('2d'), {
        type: 'bar',
        data: {
          labels: f.map(x => x[0]),
          datasets: [
            { label: 'Ingresos', backgroundColor: '#4CAF50', borderRadius: 4, data: f.map(x => x[1]) },
            { label: 'Egresos y préstamos', backgroundColor: '#F44335', borderRadius: 4, data: f.map(x => (+x[4] || 0) + (+x[5] || 0)) },
            { type: 'line', label: 'Saldo final', borderColor: '#344767', borderWidth: 2, pointRadius: 2, tension: 0.3, data: f.map(x => x[6]) }
          ]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
          scales: { x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 60 } }, y: { grid: { color: '#e5e5e5' }, ticks: { font: { size: 10 } } } }
        }
      });
    }
  }

  function generar() {
    if (!valido()) return;
    const mio = ++reqId;
    if (chart) { chart.destroy(); chart = null; }
    $('repContenedor').innerHTML = '<div class="text-center text-xs text-secondary py-5">Generando informe...</div>';

    fetch(baseUrl + '/reportes/datos?' + qs() + '&_=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' })
      .then(r => r.json())
      .then(function (res) {
        if (mio !== reqId) return;
        if (!res.success) {
          $('repContenedor').innerHTML = '<div class="text-center text-xs text-danger py-5">' + esc(res.message) + '</div>';
          Swal.fire('No se pudo generar', res.message, 'error');
          return;
        }
        const r = res.reporte;
        $('repTitulo').textContent = r.titulo;
        $('repSub').textContent = $('fSucursal').options[$('fSucursal').selectedIndex].text + ' · ' + r.periodo;
        pintar(r);
      })
      .catch(function () {
        if (mio !== reqId) return;
        $('repContenedor').innerHTML = '<div class="text-center text-xs text-danger py-5">Ocurrió un error en el servidor.</div>';
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
      });
  }

  /* ---------- Eventos ---------- */
  $('tiposReporte').addEventListener('click', function (e) {
    const b = e.target.closest('[data-tipo]');
    if (!b || b.dataset.tipo === tipoActual) return;
    aplicarTipo(b);
    generar();
  });

  $('fMes').addEventListener('change', cargarSemanas);
  $('btnGenerar').addEventListener('click', generar);
  ['fDia', 'fMes', 'fSemana', 'fAnio', 'fDesde', 'fHasta', 'fSucursal'].forEach(id => $(id).addEventListener('change', generar));
  $('btnImprimir').addEventListener('click', function () { if (valido()) lanzarImpresionIframe(baseUrl + '/reportes/imprimir?' + qs()); });
  $('btnExcel').addEventListener('click', function () { if (valido()) window.location.href = baseUrl + '/reportes/exportar?' + qs(); });

  document.addEventListener('DOMContentLoaded', function () {
    cargarSemanas();
    aplicarTipo(document.querySelector('#tiposReporte [data-tipo="consolidado"]'));
    generar();
  });
})();
</script>