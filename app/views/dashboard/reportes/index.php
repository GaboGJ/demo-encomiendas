<?php
/**
 * views/dashboard/reportes/index.php
 * Variables: $tipos, $sucursales, $veTodas, $mesActual, $mesInicio
 */
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$tarjetas = [
  ['kIng', 'Ingresos del Periodo', 'payments',            'bg-gradient-success', 'shadow-success', 'text-success'],
  ['kEgr', 'Egresos',              'trending_down',       'bg-gradient-info',    'shadow-info',    'text-danger'],
  ['kPre', 'Préstamos',            'request_quote',       'bg-gradient-warning', 'shadow-warning', 'text-dark'],
  ['kSal', 'Saldo Final',          'savings',             'bg-gradient-dark',    'shadow-dark',    'text-dark'],
];
?>
<style>
  .rep-tipos { display: flex; gap: .5rem; overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .rep-tipos .btn { white-space: nowrap; flex-shrink: 0; }
  .rep-tabla thead th { text-transform: uppercase; font-size: .65rem; font-weight: 700; color: #8392ab; white-space: nowrap;
                        padding: .75rem .6rem; border-top: 1px solid #f0f2f5; border-bottom: 1px solid #f0f2f5; }
  .rep-tabla tbody td { padding: .6rem; vertical-align: middle; }
  .rep-tabla tfoot td { padding: .7rem .6rem; font-weight: 800; background: #e8f5e9; font-size: .8rem; }
  .rep-saldo { font-size: .8rem; font-weight: 800; color: #344767; }
</style>

<div class="container-fluid py-3 flex-grow-1">

  <!-- TARJETAS -->
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
          <h5 class="font-weight-bolder text-dark mb-1">Informes Económicos</h5>
          <p class="text-xs text-secondary mb-0">Genere el resumen mensual o el informe de un mes. Puede imprimirlo o exportarlo a Excel.</p>
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

      <div class="rep-tipos mb-3" id="tiposReporte">
        <?php $primero = true; foreach ($tipos as $k => $t): ?>
          <button type="button" class="btn btn-sm mb-0 d-inline-flex align-items-center gap-1 <?= $primero ? 'bg-gradient-success text-white' : 'btn-outline-success' ?>"
                  data-tipo="<?= $h($k) ?>" title="<?= $h($t['desc']) ?>">
            <i class="material-symbols-rounded text-sm"><?= $h($t['icono']) ?></i> <?= $h($t['titulo']) ?>
          </button>
        <?php $primero = false; endforeach; ?>
      </div>

      <div class="row g-3 align-items-end">
        <div class="col-6 col-lg-2" data-grupo="resumen">
          <label class="form-label text-xs font-weight-bold text-dark mb-0">Desde (mes)</label>
          <div class="input-group input-group-outline is-filled"><input type="month" class="form-control" id="fDesde" value="<?= $h($mesInicio) ?>"></div>
        </div>
        <div class="col-6 col-lg-2" data-grupo="resumen">
          <label class="form-label text-xs font-weight-bold text-dark mb-0">Hasta (mes)</label>
          <div class="input-group input-group-outline is-filled"><input type="month" class="form-control" id="fHasta" value="<?= $h($mesActual) ?>"></div>
        </div>
        <div class="col-12 col-lg-2 d-none" data-grupo="detalle">
          <label class="form-label text-xs font-weight-bold text-dark mb-0">Mes del informe</label>
          <div class="input-group input-group-outline is-filled"><input type="month" class="form-control" id="fMes" value="<?= $h($mesActual) ?>"></div>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label text-xs font-weight-bold text-dark mb-0" id="lblSaldo">Saldo inicial (Bs.)</label>
          <div class="input-group input-group-outline is-filled"><input type="number" step="0.01" class="form-control" id="fSaldo" value="0.00"></div>
        </div>
        <div class="col-6 col-lg-3">
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
        <div class="col-12 col-lg-3">
          <button type="button" class="btn btn-sm bg-gradient-dark w-100 mb-0 d-inline-flex align-items-center justify-content-center gap-1" id="btnGenerar">
            <i class="material-symbols-rounded text-sm">filter_alt</i> Generar
          </button>
        </div>
        <div class="col-12 col-md-6">
          <div class="input-group input-group-outline"><label class="form-label">Encargado de Finanzas (para la firma)</label>
            <input type="text" class="form-control" id="fFinanzas" maxlength="60"></div>
        </div>
        <div class="col-12 col-md-6">
          <div class="input-group input-group-outline"><label class="form-label">Encargado de parada (para la firma)</label>
            <input type="text" class="form-control" id="fParada" maxlength="60"></div>
        </div>
      </div>
    </div>
  </div></div>

  <!-- RESULTADO -->
  <div class="row"><div class="col-12">
    <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
      <div class="card-header bg-white p-3 p-md-4">
        <h6 class="font-weight-bolder text-dark mb-0" id="repTitulo">Informe</h6>
        <p class="text-xxs text-secondary mb-1" id="repSub">&nbsp;</p>
        <span class="rep-saldo" id="repSaldo"></span>
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

  let tipo = 'resumen', generando = false;

  function celda(tipoCol) {
    return function (d, type) {
      if (type !== 'display') return d == null ? '' : d;
      if (d === null || d === '') return '';
      const t = tipoCol === 'monto' ? num(d, 2) : tipoCol === 'entero' ? num(d, 0) : esc(d);
      return '<span class="text-xs font-weight-bold text-dark">' + t + '</span>';
    };
  }

  function qs() {
    const p = new URLSearchParams({
      tipo: tipo, desde: $('fDesde').value, hasta: $('fHasta').value, mes: $('fMes').value,
      saldo: $('fSaldo').value, sucursal: $('fSucursal').value || 0,
      finanzas: $('fFinanzas').value, parada: $('fParada').value
    });
    return p.toString();
  }

  function valido() {
    if (tipo === 'detalle') {
      if (!$('fMes').value) { Swal.fire({ icon: 'warning', title: 'Falta el mes', text: 'Seleccione el mes del informe.' }); return false; }
    } else if (!$('fDesde').value || !$('fHasta').value || $('fDesde').value > $('fHasta').value) {
      Swal.fire({ icon: 'warning', title: 'Periodo inválido', text: 'Indique mes de inicio y de fin (inicio no mayor al fin).' }); return false;
    }
    return true;
  }

  function pintarTablas(r) {
    const cont = $('repContenedor');
    cont.innerHTML = '';
    r.tablas.forEach(function (t, k) {
      const wrap = document.createElement('div');
      wrap.className = 'px-0 mb-4';
      const id = 'tablaRep' + k;
      const pie = t.pie ? '<tfoot><tr>' + t.cols.map(function (c, i) {
        const v = t.pie[i];
        const txt = v === null || v === undefined ? '' : (c[1] === 'monto' ? num(v, 2) : c[1] === 'entero' ? num(v, 0) : esc(v));
        return '<td class="' + (i === 0 ? 'ps-4' : (c[1] === 'texto' ? '' : 'text-end')) + '">' + txt + '</td>';
      }).join('') + '</tr></tfoot>' : '';
      wrap.innerHTML = (t.titulo ? '<h6 class="text-xs font-weight-bolder text-uppercase text-success px-4 mb-2">' + esc(t.titulo) + '</h6>' : '') +
        '<div class="table-responsive p-0"><table id="' + id + '" class="table table-borderless align-items-center mb-0 w-100 rep-tabla">' + pie + '</table></div>';
      cont.appendChild(wrap);

      const n = t.cols.length;
      inicializarDataTable('#' + id, {
        ordering: false, paging: false, searching: false, info: false, dom: 't',
        data: t.filas.map(f => f.slice()),
        language: { emptyTable: 'Sin registros' },
        columns: t.cols.map(function (c, i) {
          return { title: c[0], render: celda(c[1]), data: i,
                   className: (i === 0 ? 'ps-4 ' : '') + (c[1] === 'texto' ? '' : 'text-end'),
                   responsivePriority: i === 0 ? 1 : (i === n - 1 ? 2 : 3 + i) };
        })
      });
    });
  }

  function setCargando(v) { generando = v; ['btnGenerar', 'btnImprimir', 'btnExcel'].forEach(id => $(id).disabled = v); }

  function generar() {
    if (generando || !valido()) return;
    setCargando(true);
    $('repContenedor').innerHTML = '<div class="text-center text-xs text-secondary py-5">Generando informe...</div>';

    fetch(baseUrl + '/reportes/datos?' + qs() + '&_=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' })
      .then(r => r.json())
      .then(function (res) {
        if (!res.success) {
          $('repContenedor').innerHTML = '<div class="text-center text-xs text-danger py-5">' + esc(res.message) + '</div>';
          Swal.fire('No se pudo generar', res.message, 'error');
          return;
        }
        const r = res.reporte;
        $('repTitulo').textContent = r.titulo;
        $('repSub').textContent = $('fSucursal').options[$('fSucursal').selectedIndex].text;
        $('repSaldo').textContent = r.saldo_label + ': ' + bs(r.saldo);
        $('kIng').textContent = bs(r.kpi.ing);
        $('kEgr').textContent = bs(r.kpi.egr);
        $('kPre').textContent = bs(r.kpi.pre);
        $('kSal').textContent = bs(r.kpi.saldo);
        pintarTablas(r);
      })
      .catch(function () {
        $('repContenedor').innerHTML = '<div class="text-center text-xs text-danger py-5">Ocurrió un error en el servidor.</div>';
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
      })
      .then(() => setCargando(false));
  }

  $('tiposReporte').addEventListener('click', function (e) {
    const b = e.target.closest('[data-tipo]');
    if (!b) return;
    tipo = b.dataset.tipo;
    this.querySelectorAll('[data-tipo]').forEach(function (x) {
      const on = x === b;
      x.classList.toggle('bg-gradient-success', on); x.classList.toggle('text-white', on); x.classList.toggle('btn-outline-success', !on);
    });
    document.querySelectorAll('[data-grupo]').forEach(g => g.classList.toggle('d-none', g.dataset.grupo !== tipo));
    $('lblSaldo').textContent = tipo === 'detalle' ? 'Saldo inicial al 1.º de enero (Bs.)' : 'Saldo inicial al primer mes (Bs.)';
    generar();
  });

  $('btnGenerar').addEventListener('click', generar);
  ['fDesde', 'fHasta', 'fMes', 'fSucursal'].forEach(id => $(id).addEventListener('change', generar));
  $('btnImprimir').addEventListener('click', function () { if (valido()) lanzarImpresionIframe(baseUrl + '/reportes/imprimir?' + qs()); });
  $('btnExcel').addEventListener('click', function () { if (valido()) window.location.href = baseUrl + '/reportes/exportar?' + qs(); });

  document.addEventListener('DOMContentLoaded', generar);
})();
</script>