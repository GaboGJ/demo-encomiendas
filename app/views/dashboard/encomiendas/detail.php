<!-- MODAL DETALLE DE ENCOMIENDA -->
<div class="modal fade" id="modalDetalleEncomienda" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-radius-xl">
      
      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">inventory</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">
            Detalle de Encomienda <span id="detGuia" class="badge bg-gradient-success ms-2"></span>
          </h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        
        <!-- RUTA Y ESTADOS -->
        <div class="row g-3 mb-4">
          <div class="col-12 col-md-4">
            <div class="p-3 bg-gray-100 border-radius-md">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Origen</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detOrigen">-</p>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 bg-gray-100 border-radius-md">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Destino</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detDestino">-</p>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 bg-gray-100 border-radius-md">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Modalidad / Pago</span>
              <span id="detEstadoPago" class="badge text-xxs mt-1"></span>
            </div>
          </div>
        </div>

        <!-- REMITENTE Y DESTINATARIO -->
        <div class="row g-3 mb-4">
          <div class="col-12 col-md-6">
            <div class="border border-radius-md p-3 h-100">
              <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
                <i class="material-symbols-rounded text-sm align-middle me-1">person</i> Remitente
              </h6>
              <p class="text-xs font-weight-bold text-dark mb-1" id="detRemitenteNombre">-</p>
              <p class="text-xxs text-secondary mb-1"><strong>CI/NIT:</strong> <span id="detRemitenteCi">-</span></p>
              <p class="text-xxs text-secondary mb-1"><strong>Celular:</strong> <span id="detRemitenteCel">-</span></p>
              <p class="text-xxs text-secondary mb-0"><strong>Dirección:</strong> <span id="detRemitenteDir">-</span></p>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="border border-radius-md p-3 h-100">
              <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
                <i class="material-symbols-rounded text-sm align-middle me-1">person_pin</i> Destinatario
              </h6>
              <p class="text-xs font-weight-bold text-dark mb-1" id="detDestinatarioNombre">-</p>
              <p class="text-xxs text-secondary mb-1"><strong>CI:</strong> <span id="detDestinatarioCi">-</span></p>
              <p class="text-xxs text-secondary mb-1"><strong>Celular:</strong> <span id="detDestinatarioCel">-</span></p>
              <p class="text-xxs text-secondary mb-0"><strong>Dirección:</strong> <span id="detDestinatarioDir">-</span></p>
            </div>
          </div>
        </div>

        <!-- INFORMACIÓN GENERAL -->
        <div class="p-3 bg-gray-100 border-radius-md mb-4">
          <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Declaración de Contenido</span>
          <p class="text-xs font-weight-bold text-dark mb-0" id="detDeclaracion">-</p>
        </div>

        <!-- TABLA DE BULTOS -->
        <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-2">
          <i class="material-symbols-rounded text-sm align-middle me-1">inventory_2</i> Desglose de Bultos
        </h6>
        <div class="table-responsive border border-radius-md mb-3">
          <table class="table align-items-center mb-0 w-100">
            <thead class="bg-gray-100">
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Código Bulto</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Descripción</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Categoría</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Peso</th>
                <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">Subtotal</th>
              </tr>
            </thead>
            <tbody id="tbodyDetalleBultos"></tbody>
          </table>
        </div>

        <!-- RESUMEN FINANCIERO -->
        <div class="d-flex justify-content-between align-items-center p-3 bg-gradient-light border-radius-md">
          <span class="text-xs font-weight-bold text-dark">Método de Cobro: <strong id="detMetodoPago" class="text-uppercase">-</strong></span>
          <div class="text-end">
            <span class="text-xxs text-secondary d-block">Monto Total Carga</span>
            <h5 class="text-success font-weight-bolder mb-0" id="detMontoTotal">Bs. 0.00</h5>
          </div>
        </div>

      </div>

      <div class="modal-footer bg-gray-100 py-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<!-- LÓGICA JAVASCRIPT PARA POBLAR EL MODAL -->
<script>
function verDetalleEncomienda(idEncomienda) {
  const baseUrl = '<?php echo rtrim(URL, "/"); ?>';

  fetch(`${baseUrl}/encomiendas/detalle?id=${idEncomienda}`)
    .then(response => response.json())
    .then(res => {
      if (!res.success) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        } else {
          alert(res.message);
        }
        return;
      }

      const data = res.data;

      // Encabezado y Ruta
      document.getElementById('detGuia').textContent = `#${data.guia_encomienda}`;
      document.getElementById('detOrigen').textContent = `${data.sucursal_origen_ciudad} (${data.sucursal_origen_nombre})`;
      document.getElementById('detDestino').textContent = `${data.sucursal_destino_ciudad} (${data.sucursal_destino_nombre})`;

      // Estado de Pago
      const badgePago = document.getElementById('detEstadoPago');
      if (parseInt(data.estado_pago_encomienda) === 1) {
        badgePago.className = 'badge bg-gradient-success text-xxs';
        badgePago.textContent = 'Pagado en Origen';
      } else {
        badgePago.className = 'badge bg-gradient-warning text-xxs';
        badgePago.textContent = 'Por Cobrar en Destino (COD)';
      }

      // Remitente
      document.getElementById('detRemitenteNombre').textContent = data.remitente_nombre || '-';
      document.getElementById('detRemitenteCi').textContent = data.remitente_ci || '-';
      document.getElementById('detRemitenteCel').textContent = data.remitente_celular || '-';
      document.getElementById('detRemitenteDir').textContent = data.remitente_direccion || '-';

      // Destinatario
      document.getElementById('detDestinatarioNombre').textContent = data.destinatario_nombre || '-';
      document.getElementById('detDestinatarioCi').textContent = data.destinatario_ci || '-';
      document.getElementById('detDestinatarioCel').textContent = data.destinatario_celular || '-';
      document.getElementById('detDestinatarioDir').textContent = data.destinatario_direccion || '-';

      // Declaración y Métodos
      document.getElementById('detDeclaracion').textContent = data.declaracion_encomienda || 'Sin declaración registrada';
      document.getElementById('detMetodoPago').textContent = data.nombre_metodo_pago || 'Efectivo';
      document.getElementById('detMontoTotal').textContent = `Bs. ${parseFloat(data.monto_encomienda).toFixed(2)}`;

      // Bultos
      const tbody = document.getElementById('tbodyDetalleBultos');
      tbody.innerHTML = '';

      if (data.bultos && data.bultos.length > 0) {
        data.bultos.forEach(b => {
          const pesoTxt = b.peso_detalle_encomienda ? `${parseFloat(b.peso_detalle_encomienda).toFixed(1)} Kg` : '-';
          const row = `
            <tr>
              <td class="ps-3 text-xs font-weight-bold text-dark">${b.codigo_detalle_encomienda}</td>
              <td class="text-xs text-secondary">${b.descripcion_detalle_encomienda}</td>
              <td class="text-xs text-secondary">${b.nombre_encomienda_contenido || 'General'}</td>
              <td class="text-center text-xs text-secondary">${pesoTxt}</td>
              <td class="text-end text-xs font-weight-bold text-dark pe-3">Bs. ${parseFloat(b.subtotal_detalle_encomienda).toFixed(2)}</td>
            </tr>
          `;
          tbody.innerHTML += row;
        });
      } else {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-xs text-secondary py-3">No hay detalles de bultos asociados.</td></tr>`;
      }

      // Abrir el Modal con Bootstrap
      const modalEl = document.getElementById('modalDetalleEncomienda');
      const modalObj = new bootstrap.Modal(modalEl);
      modalObj.show();
    })
    .catch(err => {
      console.error(err);
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
      }
    });
}
</script>