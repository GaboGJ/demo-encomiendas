<!-- MODAL DETALLE DE VENTA DE PASAJES -->
<div class="modal fade" id="modalDetallePasaje" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-radius-xl">

      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">confirmation_number</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">
            Detalle de Venta <span id="detPasCodigo" class="badge bg-gradient-success ms-2"></span>
          </h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">

        <!-- COMPRADOR Y COBRO -->
        <div class="row g-3 mb-4">
          <div class="col-12 col-md-6">
            <div class="border border-radius-md p-3 h-100">
              <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
                <i class="material-symbols-rounded text-sm align-middle me-1">person</i> Comprador (quien paga)
              </h6>
              <p class="text-xs font-weight-bold text-dark mb-1" id="detPasCompradorNombre">-</p>
              <p class="text-xxs text-secondary mb-1"><strong>C.I.:</strong> <span id="detPasCompradorCi">-</span></p>
              <p class="text-xxs text-secondary mb-0"><strong>Celular:</strong> <span id="detPasCompradorCel">-</span></p>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Emitido</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detPasFecha">-</p>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Método de pago</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detPasMetodo">-</p>
            </div>
          </div>
        </div>

        <!-- BOLETOS / ASIENTOS -->
        <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-2">
          <i class="material-symbols-rounded text-sm align-middle me-1">event_seat</i> Boletos de la Venta
          <span class="text-secondary font-weight-normal" id="detPasCantidad"></span>
        </h6>
        <div class="table-responsive border border-radius-md mb-3">
          <table class="table align-items-center mb-0 w-100">
            <thead class="bg-gray-100">
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Asiento</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pasajero</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ruta / Salida</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Vehículo / Chofer</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Precio</th>
                <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">Acción</th>
              </tr>
            </thead>
            <tbody id="tbodyDetallePasaje"></tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end align-items-center p-3 bg-gradient-light border-radius-md">
          <div class="text-end">
            <span class="text-xxs text-secondary d-block">Total de la Venta</span>
            <h5 class="text-success font-weight-bolder mb-0" id="detPasTotal">Bs. 0.00</h5>
          </div>
        </div>

      </div>

      <div class="modal-footer bg-gray-100 py-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-sm bg-gradient-success mb-0" onclick="imprimirBoletoPasaje(pasajeDetalleActual)">
          <i class="material-symbols-rounded text-sm me-1 align-middle">print</i> Imprimir Boleto
        </button>
      </div>

    </div>
  </div>
</div>

<script>
const BASE_URL_PASAJES = '<?= rtrim(URL, "/") ?>';
let pasajeDetalleActual = null;

function escHtmlPasaje(str) {
  const div = document.createElement('div');
  div.textContent = str == null ? '' : String(str);
  return div.innerHTML;
}

function claseBadgeEstadoPasaje(estado) {
  const e = (estado || '').toLowerCase().trim();
  if (e === 'despachado') return 'bg-gradient-success';
  if (e === 'asignado') return 'bg-gradient-info';
  return 'bg-gradient-warning';
}

function verDetallePasaje(idPasaje) {
  fetch(`${BASE_URL_PASAJES}/pasajes/detalle?id=${idPasaje}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        return;
      }

      const d = res.data;
      pasajeDetalleActual = d.id_pasaje;

      document.getElementById('detPasCodigo').textContent = `#${d.codigo_pasaje}`;
      document.getElementById('detPasCompradorNombre').textContent = (d.comprador_nombre || '').trim() || '-';
      document.getElementById('detPasCompradorCi').textContent = d.comprador_ci || '-';
      document.getElementById('detPasCompradorCel').textContent = d.comprador_celular || '-';
      document.getElementById('detPasMetodo').textContent = d.nombre_metodo_pago || '-';
      document.getElementById('detPasFecha').textContent = d.create_pasaje ? d.create_pasaje.substring(0, 16).split('-').join('/') : '-';
      document.getElementById('detPasTotal').textContent = 'Bs. ' + (parseFloat(d.total_pasaje) || 0).toFixed(2);

      const tbody = document.getElementById('tbodyDetallePasaje');
      tbody.innerHTML = '';
      const detalles = d.detalles || [];
      document.getElementById('detPasCantidad').textContent = `(${detalles.length})`;

      if (!detalles.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-xs text-secondary py-3">La venta no tiene boletos activos.</td></tr>';
      }

      detalles.forEach(b => {
        const asiento = b.numero_asiento ? `Nº ${escHtmlPasaje(b.numero_asiento)}` : '<span class="text-warning">Por asignar</span>';
        const esComprador = String(b.pasajero_ci || '') === String(d.comprador_ci || '');
        const pasajero = (b.pasajero_nombre || '').trim() || '-';

        let ruta = '<span class="text-warning">Sin turno</span>';
        let vehiculo = '-';
        if (b.id_turno) {
          ruta = `${escHtmlPasaje(b.origen_ciudad)} ➔ ${escHtmlPasaje(b.destino_ciudad)}`;
          if (b.fecha_salida_turno) {
            const f = b.fecha_salida_turno.split('-').reverse().join('/');
            ruta += `<span class="d-block text-xxs text-secondary">${f}${b.hora_salida_turno ? ' ' + b.hora_salida_turno.substring(0, 5) : ''}</span>`;
          }
          vehiculo = `Móvil ${escHtmlPasaje(b.numero_interno_vehiculo || '-')}<span class="d-block text-xxs text-secondary">${escHtmlPasaje(b.nombre_chofer || '-')}</span>`;
        }

        const despachado = (b.nombre_estado_pasaje || '').toLowerCase().trim() === 'despachado';
        const btnAnular = despachado ? '' :
          `<button type="button" class="btn btn-link text-danger p-0 m-0" title="Anular este boleto"
                   onclick="anularBoleto(${b.id_detalle_pasaje}, '${escHtmlPasaje(b.numero_asiento || 'sin asiento')}')">
             <i class="material-symbols-rounded text-sm">delete</i>
           </button>`;

        tbody.innerHTML += `
          <tr>
            <td class="ps-3 text-xs font-weight-bold text-dark">${asiento}</td>
            <td class="text-xs text-dark">
              ${escHtmlPasaje(pasajero)}
              <span class="d-block text-xxs text-secondary">C.I. ${escHtmlPasaje(b.pasajero_ci || '-')}${esComprador ? ' · Comprador' : ''}</span>
            </td>
            <td class="text-xs text-dark">${ruta}</td>
            <td class="text-xs text-dark">${vehiculo}</td>
            <td class="text-center"><span class="badge badge-sm ${claseBadgeEstadoPasaje(b.nombre_estado_pasaje)} border-radius-pill">${escHtmlPasaje(b.nombre_estado_pasaje)}</span></td>
            <td class="text-end text-xs font-weight-bold text-dark">Bs. ${(parseFloat(b.precio_detalle_pasaje) || 0).toFixed(2)}</td>
            <td class="text-end pe-3">${btnAnular}</td>
          </tr>`;
      });

      const modalEl = document.getElementById('modalDetallePasaje');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
    });
}

function enviarAnulacionPasaje(url, campo, valor) {
  const fd = new FormData();
  fd.append(campo, valor);

  fetch(BASE_URL_PASAJES + url, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        // El mensaje de éxito quedó encolado en el servidor (Flash::set) y se
        // muestra al recargar el listado.
        window.location.reload();
      } else {
        Swal.fire('No se pudo anular', res.message || 'Error al anular.', 'error');
      }
    })
    .catch(() => Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'));
}

function anularBoleto(idDetalle, asiento) {
  Swal.fire({
    title: '¿Anular este boleto?',
    text: `Se anulará solo el boleto del asiento ${asiento}. Los demás asientos de la venta no se tocan y el asiento quedará libre.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, anular boleto',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#f5365c'
  }).then(r => {
    if (r.isConfirmed) enviarAnulacionPasaje('/pasajes/anularDetalle', 'id_detalle_pasaje', idDetalle);
  });
}

function anularVentaPasaje(idPasaje, codigo) {
  Swal.fire({
    title: '¿Anular la venta completa?',
    text: `Se anularán TODOS los boletos de la venta #${codigo} y sus asientos quedarán libres.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, anular venta',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#f5365c'
  }).then(r => {
    if (r.isConfirmed) enviarAnulacionPasaje('/pasajes/anular', 'id_pasaje', idPasaje);
  });
}
</script>