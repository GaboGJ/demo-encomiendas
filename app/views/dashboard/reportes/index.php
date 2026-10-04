<?php
/**
 * views/dashboard/reportes/index.php
 * Variables: $tipos, $sucursales, $destinos, $veTodas, $mesActual, $anioActual, $hoy, $inicioMes
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
          <div class="col-4 col-md">
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
        <div class="col-12 col-md-6 col-xl-3 d-none" data-tipos="semanal mensual destino">
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

        <!-- Filtro por destino -->
        <div class="col-12 col-md-6 col-xl-3 d-none" data-tipos="destino">
          <label class="form-label text-xs font-weight-bold text-dark mb-1">Destino</label>
          <div class="input-group input-group-outline is-filled">
            <select class="form-control" id="fDestino">
              <option value="">Seleccione el destino...</option>
              <?php foreach ($destinos as $d): ?>
                <option value="<?= $h($d) ?>"><?= $h($d) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
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

      <!-- Sindicatos con ruta al destino elegido -->
      <div class="d-none mt-4" data-tipos="destino" id="panelSindicatos">
        <label class="form-label text-xs font-weight-bold text-dark mb-1 d-block">Sindicatos con ruta a este destino y monto por orden (Bs.)</label>
        <div class="row g-2" id="listaSindicatos">
          <div class="col-12 text-xs text-secondary">Seleccione un destino para ver los sindicatos.</div>
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
  <div class="card border-0 shadow-sm border-radius-xl">
    <div class="card-body p-3 p-md-4" id="repContenedor">
      <div class="text-center text-xs text-secondary py-5">Cargando informe...</div>
    </div>
  </div>
</div>

<style>
  .rep-saldo { font-size:.8rem; font-weight:700; margin:0 0 .5rem; text-transform:uppercase; }
  .rep-t { font-size:.72rem; font-weight:700; text-transform:uppercase; margin:.25rem 0 .25rem; }
  .rep-tabla { width:100%; border-collapse:collapse; font-size:.78rem; background:#fff; }
  .rep-tabla th, .rep-tabla td { border:1px solid #cfd4da; padding:4px 8px; white-space:nowrap; }
  .rep-tabla th { background:#f5f6f8; font-size:.68rem; text-transform:uppercase; color:#67748e; }
  .rep-tabla tfoot td { font-weight:700; background:#e8f5e9; }
  .sind-precio { max-width:110px; }
</style>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const MES_ACTUAL = '<?= $h($mesActual) ?>';
  const $ = id => document.getElementById(id);
  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
  const num = (n, dec) => (parseFloat(n) || 0).toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
  const pad = n => String(n).padStart(2, '0');

  let tipoActual = 'consolidado';
  let reqId = 0;

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

  /* ---------- Sindicatos del destino ---------- */
  function sindSeleccionados() {
    return Array.from(document.querySelectorAll('#listaSindicatos .chk-sind:checked'));
  }

  function destinoListo() {
    return !!$('fDestino').value && sindSeleccionados().length > 0;
  }

  function cargarSindicatos() {
    const d = $('fDestino').value, lista = $('listaSindicatos');
    if (!d) {
      lista.innerHTML = '<div class="col-12 text-xs text-secondary">Seleccione un destino para ver los sindicatos.</div>';
      return Promise.resolve();
    }
    lista.innerHTML = '<div class="col-12 text-xs text-secondary">Cargando sindicatos...</div>';

    return fetch(baseUrl + '/reportes/obtenerSindicatosDestino?destino=' + encodeURIComponent(d) + '&_=' + Date.now(),
                 { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' })
      .then(r => r.json())
      .then(function (res) {
        if (!res.success) { lista.innerHTML = '<div class="col-12 text-xs text-danger">' + esc(res.message) + '</div>'; return; }
        if (!res.sindicatos.length) {
          lista.innerHTML = '<div class="col-12 text-xs text-warning font-weight-bold">Ningún sindicato tiene ruta hacia este destino.</div>';
          return;
        }
        lista.innerHTML = res.sindicatos.map(function (s) {
          return '<div class="col-12 col-md-6 col-xl-4"><div class="p-2 border border-radius-md d-flex align-items-center gap-2">' +
            '<div class="form-check mb-0"><input class="form-check-input chk-sind" type="checkbox" checked id="sind_' + s.id_sindicato + '" value="' + s.id_sindicato + '"></div>' +
            '<label class="text-xs font-weight-bold text-dark mb-0 flex-grow-1" for="sind_' + s.id_sindicato + '">' + esc(s.nombre_sindicato) + '</label>' +
            '<input type="number" min="0" step="0.01" value="0" title="Monto por orden (Bs.)" class="form-control border px-2 py-1 border-radius-md text-xs sind-precio" data-id="' + s.id_sindicato + '">' +
            '</div></div>';
        }).join('');
      })
      .catch(function () {
        lista.innerHTML = '<div class="col-12 text-xs text-danger">No se pudieron cargar los sindicatos.</div>';
      });
  }

  /* ---------- Filtros ---------- */
  function qs() {
    const sel = sindSeleccionados();
    const precios = sel.map(function (c) {
      const inp = document.querySelector('#listaSindicatos .sind-precio[data-id="' + c.value + '"]');
      return c.value + ':' + ((inp && inp.value !== '') ? inp.value : '0');
    });
    return new URLSearchParams({
      tipo: tipoActual, dia: $('fDia').value, mes: $('fMes').value, semana: $('fSemana').value,
      anio: $('fAnio').value, desde: $('fDesde').value, hasta: $('fHasta').value,
      sucursal: $('fSucursal').value || 0,
      destino: $('fDestino').value,
      sind: sel.map(c => c.value).join(','),
      precios: precios.join(',')
    }).toString();
  }

  function valido() {
    const t = tipoActual, aviso = (ti, tx) => { Swal.fire({ icon: 'warning', title: ti, text: tx }); return false; };
    if (t === 'diario' && !$('fDia').value) return aviso('Falta el día', 'Seleccione el día del informe.');
    if (t === 'semanal' && (!$('fMes').value || !$('fSemana').value)) return aviso('Falta la semana', 'Seleccione el mes y la semana.');
    if (t === 'mensual' && !$('fMes').value) return aviso('Falta el mes', 'Seleccione el mes del informe.');
    if (t === 'destino') {
      if (!$('fMes').value) return aviso('Falta el mes', 'Seleccione el mes del informe.');
      if (!$('fDestino').value) return aviso('Falta el destino', 'Seleccione el destino del informe.');
      if (!sindSeleccionados().length) return aviso('Faltan sindicatos', 'Seleccione al menos un sindicato.');
    }
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
  function tabla(t) {
    const c = t.cols;
    const fmt = (col, v) => (v === null || v === undefined || v === '') ? ''
      : col[1] === 'monto' ? num(v, 2) : col[1] === 'entero' ? num(v, 0) : esc(v);
    const row = f => '<tr>' + c.map((x, i) =>
      '<td class="' + (i ? 'text-end' : '') + '">' + fmt(x, f[i]) + '</td>').join('') + '</tr>';
    return (t.titulo ? '<div class="rep-t">' + esc(t.titulo) + '</div>' : '') +
      '<div class="table-responsive"><table class="rep-tabla"><thead><tr>' +
      c.map((x, i) => '<th class="' + (i ? 'text-end' : '') + '">' + esc(x[0]) + '</th>').join('') +
      '</tr></thead><tbody>' +
      (t.filas.length ? t.filas.map(row).join('') : '<tr><td colspan="' + c.length + '" class="text-center">Sin registros</td></tr>') +
      '</tbody>' + (t.pie ? '<tfoot>' + row(t.pie) + '</tfoot>' : '') + '</table></div>';
  }

  // Las tablas pequeñas ("mitad") se muestran de a dos por fila en pantallas grandes
  function pintar(r) {
    $('repContenedor').innerHTML =
      (r.saldo_label ? '<p class="rep-saldo">' + esc(r.saldo_label) + ': ' + num(r.saldo, 2) + '</p>' : '') +
      '<div class="row g-3">' +
      r.tablas.map(t => '<div class="col-12' + (t.mitad ? ' col-lg-6' : '') + '">' + tabla(t) + '</div>').join('') +
      '</div>';
  }

  function generar() {
    if (!valido()) return;
    const mio = ++reqId;
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

  // Genera automáticamente; en "Por destino" espera a que haya destino y sindicatos elegidos
  function auto() {
    if (tipoActual === 'destino' && (!$('fMes').value || !destinoListo())) {
      reqId++;
      $('repTitulo').textContent = 'Informe por destino';
      $('repSub').innerHTML = '&nbsp;';
      $('repContenedor').innerHTML = '<div class="text-center text-xs text-secondary py-5">Seleccione el mes, el destino y los sindicatos para generar el informe.</div>';
      return;
    }
    generar();
  }

  /* ---------- Eventos ---------- */
  $('tiposReporte').addEventListener('click', function (e) {
    const b = e.target.closest('[data-tipo]');
    if (!b || b.dataset.tipo === tipoActual) return;
    aplicarTipo(b);
    auto();
  });

  $('fMes').addEventListener('change', cargarSemanas);
  $('btnGenerar').addEventListener('click', generar);
  ['fDia', 'fMes', 'fSemana', 'fAnio', 'fDesde', 'fHasta', 'fSucursal'].forEach(id => $(id).addEventListener('change', auto));

  $('fDestino').addEventListener('change', function () { cargarSindicatos().then(auto); });
  $('listaSindicatos').addEventListener('change', auto); // casillas y montos por orden

  $('btnImprimir').addEventListener('click', function () { if (valido()) lanzarImpresionIframe(baseUrl + '/reportes/imprimir?' + qs()); });
  $('btnExcel').addEventListener('click', function () { if (valido()) window.location.href = baseUrl + '/reportes/exportar?' + qs(); });

  document.addEventListener('DOMContentLoaded', function () {
    cargarSemanas();
    aplicarTipo(document.querySelector('#tiposReporte [data-tipo="consolidado"]'));
    generar();
  });
})();
</script>