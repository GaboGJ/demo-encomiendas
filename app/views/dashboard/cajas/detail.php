<!-- MODAL DETALLE DE CAJA -->
<div class="modal fade" id="modalDetalleCaja" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">

      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">point_of_sale</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">Detalle de Caja</h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl bg-gradient-success border-radius-lg shadow-sm mx-auto d-flex align-items-center justify-content-center text-white">
            <i class="material-symbols-rounded fs-3">point_of_sale</i>
          </div>
          <h6 class="text-dark font-weight-bolder mt-2 mb-0" id="detCajNombre">-</h6>
          <span class="badge badge-sm bg-gradient-success border-radius-pill mt-1" id="detCajEstado">-</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Sucursal</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detCajSucursal">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Turno actual</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detCajTurno">-</p>
            </div>
          </div>
        </div>

        <div class="border border-radius-md p-3 mb-3">
          <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
            <i class="material-symbols-rounded text-sm align-middle me-1">history</i> Últimos Turnos
          </h6>
          <div class="table-responsive">
            <table class="table align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Cajero / Fechas</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Inicial</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sistema</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contado</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dif.</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-2"></th>
                </tr>
              </thead>
              <tbody id="detCajHistorial"></tbody>
            </table>
          </div>
        </div>

        <div class="d-flex justify-content-between flex-wrap gap-1 text-xxs text-secondary">
          <span><strong>Registrada:</strong> <span id="detCajCreado">-</span></span>
          <span><strong>Última edición:</strong> <span id="detCajActualizado">-</span></span>
        </div>
      </div>

      <div class="modal-footer bg-gray-100 py-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
        <a href="#" id="btnDetCajEditar" class="btn btn-sm bg-gradient-success mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">edit</i> Editar
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function verDetalleCaja(idCaja) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  fetch(`${baseUrl}/cajas/detalle?id=${idCaja}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        return;
      }

      const d = res.data;
      const setTxt = (id, v) => { document.getElementById(id).textContent = (v !== null && v !== undefined && String(v).trim() !== '') ? v : '-'; };
      const fecha  = f => f ? f.substring(0, 10).split('-').reverse().join('/') : '-';
      const fechaH = f => f ? f.substring(8, 10) + '/' + f.substring(5, 7) + '/' + f.substring(0, 4) + ' ' + f.substring(11, 16) : '';
      const bs  = n => 'Bs. ' + (parseFloat(n) || 0).toFixed(2);
      const esc = s => { const x = document.createElement('div'); x.textContent = s == null ? '' : String(s); return x.innerHTML; };

      setTxt('detCajNombre', d.nombre_caja);
      setTxt('detCajSucursal', d.ciudad_sucursal + ' (' + d.nombre_sucursal + ')');
      setTxt('detCajCreado', fecha(d.create_caja));
      setTxt('detCajActualizado', fecha(d.update_caja));

      const abierta = !!d.id_historial_abierto;
      setTxt('detCajTurno', abierta ? (d.cajero_abierto || 'Cajero') + ' · desde ' + fechaH(d.fecha_apertura_abierto) : 'Sin turno abierto');

      const est = document.getElementById('detCajEstado');
      const activo = parseInt(d.estado_caja) === 1;
      est.className = 'badge badge-sm border-radius-pill mt-1 ' + (abierta ? 'bg-gradient-success' : (activo ? 'bg-gradient-info' : 'bg-gradient-secondary'));
      est.textContent = abierta ? 'Abierta' : (activo ? 'Cerrada' : 'Inactiva');

      const tb = document.getElementById('detCajHistorial');
      const hist = d.historial || [];
      tb.innerHTML = hist.length ? hist.map(h => {
        const open = parseInt(h.abierta) === 1;
        const dif = h.diferencia_historial_caja;
        const difCls = dif === null ? 'text-secondary' : (parseFloat(dif) === 0 ? 'text-success' : 'text-danger');
        return `<tr>
          <td class="ps-2 text-xs text-dark font-weight-bold">${esc(h.cajero || '-')}
            <span class="d-block text-xxs text-secondary font-weight-normal">${fechaH(h.fecha_apertura)}${h.fecha_cierre ? ' → ' + fechaH(h.fecha_cierre) : ' (abierta)'}</span></td>
          <td class="text-end text-xs">${bs(h.monto_inicial)}</td>
          <td class="text-end text-xs">${open ? '-' : bs(h.monto_final_sistema)}</td>
          <td class="text-end text-xs">${open ? '-' : bs(h.monto_final_declarado)}</td>
          <td class="text-end text-xs font-weight-bold ${difCls}">${dif === null ? '-' : bs(dif)}</td>
          <td class="text-end pe-2"><button type="button" class="btn btn-link text-success p-0 m-0" title="Imprimir reporte"
              onclick="lanzarImpresionIframe('${baseUrl}/cajas/imprimirCierre?id=${h.id_historial_caja}')">
              <i class="material-symbols-rounded text-sm">print</i></button></td>
        </tr>`;
      }).join('') : '<tr><td colspan="6" class="text-center text-xs text-secondary py-3">Esta caja aún no tiene turnos registrados.</td></tr>';

      document.getElementById('btnDetCajEditar').href = `${baseUrl}/cajas/update?id=${d.id_caja}`;

      const modalEl = document.getElementById('modalDetalleCaja');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
    });
}
</script>