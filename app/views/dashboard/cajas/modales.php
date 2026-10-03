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
        if (res.success) window.location.reload();   // el mensaje sale por Flash
        else { Swal.fire('No se pudo aperturar', res.message, 'error'); btn.disabled = false; }
      })
      .catch(() => { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); btn.disabled = false; });
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
            // Flash se mostrará al recargar; se imprime el reporte del turno cerrado
            lanzarImpresionIframe(baseUrl + '/cajas/imprimirCierre?id=' + res.id_historial, function () { window.location.reload(); });
          } else { Swal.fire('No se pudo cerrar', res.message, 'error'); btn.disabled = false; }
        })
        .catch(() => { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); btn.disabled = false; });
    });
  });
})();
</script>