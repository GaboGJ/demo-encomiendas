<!-- MODAL DETALLE DE MODELO -->
<div class="modal fade" id="modalDetalleModelo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">

      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">directions_bus</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">Detalle de Modelo</h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl bg-gradient-success border-radius-lg shadow-sm mx-auto d-flex align-items-center justify-content-center text-white">
            <i class="material-symbols-rounded fs-3">directions_bus</i>
          </div>
          <h6 class="text-dark font-weight-bolder mt-2 mb-0" id="detModNombre">-</h6>
          <span class="badge badge-sm bg-gradient-success border-radius-pill mt-1" id="detModEstado">-</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Capacidad</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detModAsientos">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Vehículos con este modelo</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detModVehiculos">-</p>
            </div>
          </div>
        </div>

        <div class="border border-radius-md p-3 mb-3">
          <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
            <i class="material-symbols-rounded text-sm align-middle me-1">layers</i> Distribución por Piso
          </h6>
          <div id="detModPisos" class="text-xxs text-secondary">-</div>
        </div>

        <div class="d-flex justify-content-between flex-wrap gap-1 text-xxs text-secondary">
          <span><strong>Registrado:</strong> <span id="detModCreado">-</span></span>
          <span><strong>Última edición:</strong> <span id="detModActualizado">-</span></span>
        </div>
      </div>

      <div class="modal-footer bg-gray-100 py-2 flex-wrap gap-1">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
        <a href="#" id="btnDetModEditar" class="btn btn-sm btn-outline-dark mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">edit</i> Editar
        </a>
        <a href="#" id="btnDetModConfig" class="btn btn-sm bg-gradient-success mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">event_seat</i> Configurar Asientos
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function verDetalleModelo(idModelo) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  fetch(`${baseUrl}/modelos/detalle?id=${idModelo}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        return;
      }

      const d = res.data;
      const setTxt = (id, v) => { document.getElementById(id).textContent = (v !== null && v !== undefined && String(v).trim() !== '') ? v : '-'; };
      const fecha = f => f ? f.substring(0, 10).split('-').reverse().join('/') : '-';
      const esc = s => { const x = document.createElement('div'); x.textContent = s == null ? '' : String(s); return x.innerHTML; };

      setTxt('detModNombre', d.nombre_modelo);
      setTxt('detModAsientos', parseInt(d.total_asientos_modelo) > 0 ? d.total_asientos_modelo + ' asientos' : 'Sin configurar');
      setTxt('detModVehiculos', d.total_vehiculos);
      setTxt('detModCreado', fecha(d.create_modelo));
      setTxt('detModActualizado', fecha(d.update_modelo));

      const est = document.getElementById('detModEstado');
      const activo = parseInt(d.estado_modelo) === 1;
      est.className = 'badge badge-sm border-radius-pill mt-1 ' + (activo ? 'bg-gradient-success' : 'bg-gradient-secondary');
      est.textContent = activo ? 'Activo' : 'Inactivo';

      const cont = document.getElementById('detModPisos');
      const pisos = d.pisos || [];
      cont.innerHTML = pisos.length
        ? pisos.map(p => `<div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light gap-2">
              <span class="font-weight-bold text-dark">${esc(p.nombre || ('Piso ' + p.numero))}</span>
              <span class="text-end">${p.filas}×${p.columnas} · ${p.asientos} asientos${p.especiales ? ' · ' + p.especiales + ' especial(es)' : ''}</span>
            </div>`).join('')
        : 'Aún no tiene un plano de asientos configurado.';

      document.getElementById('btnDetModEditar').href = `${baseUrl}/modelos/update?id=${d.id_modelo}`;
      document.getElementById('btnDetModConfig').href = `${baseUrl}/modelos/configurar?id=${d.id_modelo}`;

      const modalEl = document.getElementById('modalDetalleModelo');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
    });
}
</script>