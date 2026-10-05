<!-- MODAL: APERTURA DE CAJA -->
<div class="modal fade" id="modalAbrirCaja" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">
      <div class="modal-header bg-gradient-success text-white p-3">
        <h5 class="modal-title text-white font-weight-bold fs-6 mb-0 d-flex align-items-center">
          <i class="material-symbols-rounded me-2">lock_open</i> Aperturar · <span id="abrirCajaNombre" class="ms-1">-</span>
        </h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-xs text-secondary mb-3">Se registrará un nuevo turno a su nombre. Indique el fondo de cambio con el que inicia.</p>
        <input type="hidden" id="abrirIdCaja">
        <div class="input-group input-group-outline is-filled">
          <label class="form-label">Monto inicial / Fondo (Bs.) *</label>
          <input type="number" class="form-control" id="abrirMonto" min="0" max="99999999.99" step="0.01" value="0.00">
        </div>
      </div>
      <div class="modal-footer bg-gray-100">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-sm bg-gradient-success mb-0" id="btnConfirmarAbrir">Abrir Caja</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: MOVIMIENTOS MANUALES -->
<div class="modal fade" id="modalMovimientos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">
      <div class="modal-header bg-gradient-dark text-white p-3">
        <h5 class="modal-title text-white font-weight-bold fs-6 mb-0 d-flex align-items-center">
          <i class="material-symbols-rounded me-2">swap_vert</i> Movimientos · <span id="movCajaNombre" class="ms-1">-</span>
        </h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3 p-md-4">
        <input type="hidden" id="movIdHistorial">

        <div class="border border-radius-md p-3 mb-3">
          <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
              <label class="form-label text-xs font-weight-bold mb-0">Tipo *</label>
              <div class="input-group input-group-outline is-filled">
                <select class="form-control" id="movTipo">
                  <option value="ingreso">Ingreso</option>
                  <option value="egreso">Egreso</option>
                </select>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="input-group input-group-outline">
                <label class="form-label">Monto (Bs.) *</label>
                <input type="number" class="form-control" id="movMonto" min="0.01" max="99999999.99" step="0.01">
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="input-group input-group-outline">
                <label class="form-label">Concepto *</label>
                <input type="text" class="form-control" id="movConcepto" maxlength="200">
              </div>
            </div>
            <div class="col-12 col-md-2">
              <button type="button" class="btn btn-sm bg-gradient-success w-100 mb-0" id="btnGuardarMov">Registrar</button>
            </div>
          </div>
          <p class="text-xxs text-secondary mb-0 mt-2">Si un egreso es un préstamo, incluya la palabra "préstamo" en el concepto para que se contabilice como tal en los reportes.</p>
        </div>

        <div class="table-responsive p-0">
          <table class="table table-borderless align-items-center mb-0 w-100" id="tablaMovimientos">
            <thead>
              <tr>
                <th data-priority="1" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-3 border-top border-bottom border-light">Fecha / Concepto</th>
                <th data-priority="3" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 border-top border-bottom border-light">Tipo</th>
                <th data-priority="2" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 border-top border-bottom border-light">Monto</th>
                <th data-priority="1" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-3 border-top border-bottom border-light">Acción</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between flex-wrap gap-2 p-3 bg-gray-100 border-radius-md mt-3">
          <span class="text-xs font-weight-bold text-success">Ingresos: <span id="movTotIng">Bs. 0.00</span></span>
          <span class="text-xs font-weight-bold text-danger">Egresos: <span id="movTotEgr">Bs. 0.00</span></span>
        </div>
      </div>
      <div class="modal-footer bg-gray-100">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: ARQUEO Y CIERRE -->
<div class="modal fade" id="modalCerrarCaja" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">
      <div class="modal-header bg-gradient-danger text-white p-3">
        <h5 class="modal-title text-white font-weight-bold fs-6 mb-0 d-flex align-items-center">
          <i class="material-symbols-rounded me-2">calculate</i> Arqueo y Cierre · <span id="cerrarCajaNombre" class="ms-1">-</span>
        </h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="cerrarIdHistorial">

        <div class="border border-radius-md p-3 mb-3" id="cerrarResumen"></div>

        <div class="input-group input-group-outline my-2">
          <label class="form-label">Efectivo contado (Bs.) *</label>
          <input type="number" class="form-control" id="cerrarDeclarado" min="0" max="99999999.99" step="0.01">
        </div>

        <div class="d-flex justify-content-between align-items-center p-3 bg-gray-100 border-radius-md mt-3">
          <span class="text-xs font-weight-bold text-dark">Diferencia:</span>
          <h6 class="mb-0 font-weight-bolder text-secondary" id="cerrarDiferencia">-</h6>
        </div>
        <p class="text-xxs text-secondary mt-2 mb-0">Diferencia = contado − total del sistema. El cierre es definitivo.</p>
      </div>
      <div class="modal-footer bg-gray-100">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-sm bg-gradient-danger mb-0" id="btnConfirmarCerrar">Registrar Cierre</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const $ = id => document.getElementById(id);
  const bs = n => 'Bs. ' + (parseFloat(n) || 0).toFixed(2);
  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
  const modal = id => { const el = $(id); return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); };
  let totalSistema = 0;

  function post(url, obj) {
    const fd = new FormData();
    Object.keys(obj).forEach(k => fd.append(k, obj[k]));
    return fetch(baseUrl + url, { method: 'POST', body: fd }).then(r => r.json());
  }

  /* ---------- Apertura ---------- */
  window.abrirCaja = function (id, nombre) {
    $('abrirIdCaja').value = id;
    $('abrirCajaNombre').textContent = nombre;
    $('abrirMonto').value = '0.00';
    modal('modalAbrirCaja').show();
  };

  $('btnConfirmarAbrir').addEventListener('click', function () {
    const btn = this, monto = $('abrirMonto').value;
    if (monto === '' || parseFloat(monto) < 0) {
      Swal.fire({ icon: 'warning', title: 'Monto inválido', text: 'Indique un monto inicial de 0 o mayor.' });
      return;
    }
    btn.disabled = true;
    post('/cajas/abrir', { id_caja: $('abrirIdCaja').value, monto_inicial: monto })
      .then(res => {
        if (res.success) window.location.reload();
        else { Swal.fire('No se pudo aperturar', res.message, 'error'); btn.disabled = false; }
      })
      .catch(() => { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); btn.disabled = false; });
  });

  /* ---------- Movimientos manuales (DataTable con el helper global) ---------- */
  const modalMovEl = $('modalMovimientos');
  let dtMov = null, movimientos = [];

  const fechaH = f => f ? f.substring(8, 10) + '/' + f.substring(5, 7) + '/' + f.substring(0, 4) + ' ' + f.substring(11, 16) : '';

  function filaMov(m) {
    const es = parseInt(m.tipo) === 1, v = parseFloat(m.monto) || 0;
    return {
      concepto: '<span class="text-xs text-dark font-weight-bold">' + esc(m.concepto) + '</span>' +
                '<span class="d-block text-xxs text-secondary font-weight-normal">' + fechaH(m.fecha) + '</span>',
      tipo:     '<span class="badge badge-sm ' + (es ? 'bg-gradient-success' : 'bg-gradient-danger') + '">' + (es ? 'Ingreso' : 'Egreso') + '</span>',
      monto:    '<span class="text-xs font-weight-bold ' + (es ? 'text-success' : 'text-danger') + '">' + (es ? '' : '- ') + bs(v) + '</span>',
      acciones: '<button type="button" class="btn btn-link text-danger p-0 m-0 mov-del" data-id="' + m.id_movimiento_caja + '" title="Anular">' +
                '<i class="material-symbols-rounded text-sm">delete</i></button>'
    };
  }

  function iniciarTablaMov() {
    if (dtMov || typeof inicializarDataTable !== 'function') return;
    dtMov = inicializarDataTable('#tablaMovimientos', {
      ordering: false,
      placeholder: 'Buscar movimiento...',
      pageLength: 5,
      columns: [
        { data: 'concepto', className: 'ps-3',          responsivePriority: 1 },
        { data: 'tipo',     className: 'text-center',    responsivePriority: 3 },
        { data: 'monto',    className: 'text-end',       responsivePriority: 2 },
        { data: 'acciones', className: 'text-end pe-3',  orderable: false, responsivePriority: 1 }
      ]
    });
  }

  function renderMov() {
    if (!dtMov) return;
    dtMov.clear();
    dtMov.rows.add(movimientos.map(filaMov)).draw(false);
  }

  function cargarMovimientos(abrirModal) {
    const id = $('movIdHistorial').value;
    return fetch(`${baseUrl}/cajas/detalleMovimientos?id=${id}&_=${Date.now()}`, { cache: 'no-store' })
      .then(r => r.json())
      .then(res => {
        if (!res.success) { Swal.fire('Error', res.message, 'error'); return; }
        $('movCajaNombre').textContent = res.caja;
        movimientos = res.movimientos || [];

        let ing = 0, egr = 0;
        movimientos.forEach(m => { const v = parseFloat(m.monto) || 0; if (parseInt(m.tipo) === 1) ing += v; else egr += v; });
        $('movTotIng').textContent = bs(ing);
        $('movTotEgr').textContent = bs(egr);

        renderMov();
        if (abrirModal) modal('modalMovimientos').show();
      })
      .catch(() => Swal.fire('Error', 'Error al conectar con el servidor.', 'error'));
  }

  // Con el modal ya visible DataTables puede medir bien las columnas
  modalMovEl.addEventListener('shown.bs.modal', function () {
    iniciarTablaMov();
    renderMov();
    if (dtMov) {
      dtMov.columns.adjust();
      if (dtMov.responsive) dtMov.responsive.recalc();
    }
  });
  modalMovEl.addEventListener('hidden.bs.modal', function () {
    movimientos = [];
    if (dtMov) dtMov.clear().draw();
  });

  window.abrirMovimientos = function (idHistorial) {
    $('movIdHistorial').value = idHistorial;
    $('movMonto').value = ''; $('movConcepto').value = ''; $('movTipo').value = 'ingreso';
    cargarMovimientos(true);
  };

  $('btnGuardarMov').addEventListener('click', function () {
    const btn = this, monto = $('movMonto').value, concepto = $('movConcepto').value.trim();
    if (!monto || parseFloat(monto) <= 0 || concepto.length < 3) {
      Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Indique un monto mayor a 0 y un concepto (mínimo 3 caracteres).' });
      return;
    }
    btn.disabled = true;
    post('/cajas/guardarMovimiento', { id_historial: $('movIdHistorial').value, tipo: $('movTipo').value, monto: monto, concepto: concepto })
      .then(res => {
        btn.disabled = false;
        if (res.success) { $('movMonto').value = ''; $('movConcepto').value = ''; cargarMovimientos(false); }
        else Swal.fire('No se pudo registrar', res.message, 'error');
      })
      .catch(() => { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); btn.disabled = false; });
  });

  // Delegado sobre la tabla: funciona con paginación y filas responsive
  $('tablaMovimientos').addEventListener('click', function (e) {
    const b = e.target.closest('.mov-del');
    if (!b) return;
    Swal.fire({ title: '¿Anular el movimiento?', text: 'Dejará de contar en el arqueo y los reportes.', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Sí, anular', cancelButtonText: 'Cancelar', confirmButtonColor: '#f5365c' })
      .then(c => {
        if (!c.isConfirmed) return;
        post('/cajas/anularMovimiento', { id_historial: $('movIdHistorial').value, id_movimiento: b.dataset.id })
          .then(res => res.success ? cargarMovimientos(false) : Swal.fire('No se pudo anular', res.message, 'error'))
          .catch(() => Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'));
      });
  });

  /* ---------- Arqueo y cierre ---------- */
  function actualizarDiferencia() {
    const v = $('cerrarDeclarado').value, el = $('cerrarDiferencia');
    if (v === '') { el.textContent = '-'; el.className = 'mb-0 font-weight-bolder text-secondary'; return; }
    const dif = Math.round((parseFloat(v) - totalSistema) * 100) / 100;
    el.textContent = bs(dif) + (dif === 0 ? ' (cuadrada)' : dif < 0 ? ' (faltante)' : ' (sobrante)');
    el.className = 'mb-0 font-weight-bolder ' + (dif === 0 ? 'text-success' : 'text-danger');
  }
  $('cerrarDeclarado').addEventListener('input', actualizarDiferencia);

  window.cerrarCaja = function (idHistorial) {
    fetch(`${baseUrl}/cajas/detalleTurno?id=${idHistorial}&_=${Date.now()}`, { cache: 'no-store' })
      .then(r => r.json())
      .then(res => {
        if (!res.success) { Swal.fire('Error', res.message, 'error'); return; }
        const r = res.resumen;
        totalSistema = r.total;
        $('cerrarIdHistorial').value = idHistorial;
        $('cerrarCajaNombre').textContent = res.caja;
        $('cerrarDeclarado').value = '';

        const fila = (t, v, extra, cls) =>
          `<div class="d-flex justify-content-between py-1 border-bottom border-light gap-2">
             <span class="text-xs text-secondary">${t}${extra ? ' <span class="text-xxs">(' + extra + ')</span>' : ''}</span>
             <span class="text-xs font-weight-bold ${cls || 'text-dark'}">${bs(v)}</span></div>`;
        $('cerrarResumen').innerHTML =
          fila('Fondo inicial', r.inicial) +
          fila('Pasajes vendidos', r.pasajes, r.n_pasajes + ' venta(s)') +
          fila('Encomiendas pagadas en origen', r.encomiendas, r.n_encomiendas + ' guía(s)') +
          fila('Cobros COD en destino', r.entregas) +
          fila('Otros ingresos', r.ingresos_extra) +
          fila('Egresos', -r.egresos, '', 'text-danger') +
          `<div class="d-flex justify-content-between pt-2"><span class="text-xs font-weight-bolder text-dark">TOTAL EN SISTEMA</span>
             <span class="text-sm font-weight-bolder text-success">${bs(r.total)}</span></div>`;

        actualizarDiferencia();
        modal('modalCerrarCaja').show();
      })
      .catch(() => Swal.fire('Error', 'Error al conectar con el servidor.', 'error'));
  };

  $('btnConfirmarCerrar').addEventListener('click', function () {
    const btn = this, declarado = $('cerrarDeclarado').value;
    if (declarado === '' || parseFloat(declarado) < 0) {
      Swal.fire({ icon: 'warning', title: 'Falta el conteo', text: 'Indique el efectivo contado (0 o mayor).' });
      return;
    }
    Swal.fire({
      title: '¿Registrar el cierre definitivo?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, cerrar caja', cancelButtonText: 'Cancelar', confirmButtonColor: '#f5365c'
    }).then(function (c) {
      if (!c.isConfirmed) return;
      btn.disabled = true;
      post('/cajas/cerrar', { id_historial: $('cerrarIdHistorial').value, monto_declarado: declarado })
        .then(res => {
          if (res.success) {
            lanzarImpresionIframe(baseUrl + '/cajas/imprimirCierre?id=' + res.id_historial, function () { window.location.reload(); });
          } else { Swal.fire('No se pudo cerrar', res.message, 'error'); btn.disabled = false; }
        })
        .catch(() => { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); btn.disabled = false; });
    });
  });
})();
</script>