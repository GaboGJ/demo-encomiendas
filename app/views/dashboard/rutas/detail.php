<!-- MODAL DETALLE DE RUTA -->
<div class="modal fade" id="modalDetalleRuta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">

      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">alt_route</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">Detalle de Ruta</h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl bg-gradient-success border-radius-lg shadow-sm mx-auto d-flex align-items-center justify-content-center text-white">
            <i class="material-symbols-rounded fs-3">route</i>
          </div>
          <h6 class="text-dark font-weight-bolder mt-2 mb-0" id="detRutNombre">-</h6>
          <span class="badge badge-sm bg-gradient-success border-radius-pill mt-1" id="detRutEstado">-</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-12">
            <div class="p-3 bg-gray-100 border-radius-md">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Sindicato</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detRutSindicato">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Origen</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detRutOrigen">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Destino</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detRutDestino">-</p>
            </div>
          </div>
          <div class="col-12">
            <div class="p-3 bg-gray-100 border-radius-md">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Precio del pasaje</span>
              <h5 class="text-success font-weight-bolder mb-0" id="detRutPasaje">Bs. 0.00</h5>
            </div>
          </div>
        </div>

        <div class="border border-radius-md p-3 mb-3">
          <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
            <i class="material-symbols-rounded text-sm align-middle me-1">inventory_2</i> Tarifas de Encomienda
          </h6>
          <div id="detRutTarifas" class="text-xxs text-secondary">-</div>
        </div>

        <div class="d-flex justify-content-between flex-wrap gap-1 text-xxs text-secondary">
          <span><strong>Registrada:</strong> <span id="detRutCreado">-</span></span>
          <span><strong>Última edición:</strong> <span id="detRutActualizado">-</span></span>
        </div>
      </div>

      <div class="modal-footer bg-gray-100 py-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
        <a href="#" id="btnDetRutEditar" class="btn btn-sm bg-gradient-success mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">edit</i> Editar
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function verDetalleRuta(idRuta) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  fetch(`${baseUrl}/rutas/detalle?id=${idRuta}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        return;
      }

      const d = res.data;
      // Las utilidades se definen ANTES de usarlas
      const setTxt = (id, v) => { document.getElementById(id).textContent = (v !== null && v !== undefined && String(v).trim() !== '') ? v : '-'; };
      const fecha = f => f ? f.substring(0, 10).split('-').reverse().join('/') : '-';
      const esc = s => { const x = document.createElement('div'); x.textContent = s == null ? '' : String(s); return x.innerHTML; };
      const bs = n => 'Bs. ' + (parseFloat(n) || 0).toFixed(2);

      setTxt('detRutSindicato', d.nombre_sindicato);
      setTxt('detRutNombre', d.ciudad_origen + ' ➔ ' + d.ciudad_destino);
      setTxt('detRutOrigen', d.ciudad_origen + ' (' + d.nombre_origen + ')');
      setTxt('detRutDestino', d.ciudad_destino + ' (' + d.nombre_destino + ')');
      setTxt('detRutPasaje', bs(d.base_precio_pasaje));
      setTxt('detRutCreado', fecha(d.create_precio_pasaje));
      setTxt('detRutActualizado', fecha(d.update_precio_pasaje));

      const est = document.getElementById('detRutEstado');
      const activo = parseInt(d.estado_ruta) === 1;
      est.className = 'badge badge-sm border-radius-pill mt-1 ' + (activo ? 'bg-gradient-success' : 'bg-gradient-secondary');
      est.textContent = activo ? 'Activa' : 'Inactiva';

      const cont = document.getElementById('detRutTarifas');
      const tar = d.tarifas || [];
      cont.innerHTML = tar.length
        ? tar.map(t => {
            const rango = (t.peso_minimo !== null && t.peso_minimo !== '' ? parseFloat(t.peso_minimo) + ' – ' : 'hasta ') + parseFloat(t.peso_maximo) + ' kg';
            return `<div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light gap-2">
                <span class="font-weight-bold text-dark">${esc(t.nombre_encomienda_contenido)}<span class="d-block text-secondary font-weight-normal">${rango}</span></span>
                <span class="font-weight-bold text-dark text-end">${bs(t.precio_tarifa_encomienda)}</span>
              </div>`;
          }).join('')
        : 'Esta ruta aún no tiene tarifas de encomienda.';

      document.getElementById('btnDetRutEditar').href = `${baseUrl}/rutas/update?id=${d.id_precio_pasaje}`;

      const modalEl = document.getElementById('modalDetalleRuta');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
    });
}
</script>