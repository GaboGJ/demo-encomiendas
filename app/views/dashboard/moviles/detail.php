<!-- MODAL DETALLE DE MÓVIL -->
<div class="modal fade" id="modalDetalleMovil" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">

      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">directions_bus</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">Detalle de Móvil</h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl bg-gradient-success border-radius-lg shadow-sm mx-auto d-flex align-items-center justify-content-center text-white">
            <i class="material-symbols-rounded fs-3">directions_bus</i>
          </div>
          <h6 class="text-dark font-weight-bolder mt-2 mb-0" id="detMovNombre">-</h6>
          <span class="badge badge-sm bg-gradient-success border-radius-pill mt-1" id="detMovEstado">-</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Placa</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detMovPlaca">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Color</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detMovColor">-</p>
            </div>
          </div>
        </div>

        <div class="border border-radius-md p-3 mb-3">
          <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
            <i class="material-symbols-rounded text-sm align-middle me-1">info</i> Datos del Vehículo
          </h6>
          <p class="text-xxs text-secondary mb-1"><strong>Modelo:</strong> <span id="detMovModelo">-</span></p>
          <p class="text-xxs text-secondary mb-1"><strong>Capacidad:</strong> <span id="detMovCapacidad">-</span></p>
          <p class="text-xxs text-secondary mb-0"><strong>Socio titular:</strong> <span id="detMovSocio">-</span></p>
        </div>

        <div class="border border-radius-md p-3 mb-3">
          <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
            <i class="material-symbols-rounded text-sm align-middle me-1">badge</i> Choferes Asignados
          </h6>
          <div id="detMovChoferes" class="text-xxs text-secondary">-</div>
        </div>

        <div class="d-flex justify-content-between text-xxs text-secondary">
          <span><strong>Registrado:</strong> <span id="detMovCreado">-</span></span>
          <span><strong>Última edición:</strong> <span id="detMovActualizado">-</span></span>
        </div>
      </div>

      <div class="modal-footer bg-gray-100 py-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
        <a href="#" id="btnDetMovEditar" class="btn btn-sm bg-gradient-success mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">edit</i> Editar
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function verDetalleMovil(idVehiculo) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  fetch(`${baseUrl}/moviles/detalle?id=${idVehiculo}`)
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

      setTxt('detMovNombre', 'Unidad ' + d.numero_interno_vehiculo);
      setTxt('detMovPlaca', d.placa_vehiculo || 'Sin placa');
      setTxt('detMovColor', d.color_vehiculo);
      setTxt('detMovModelo', d.nombre_modelo);
      setTxt('detMovCapacidad', d.total_asientos_modelo + ' asientos');
      setTxt('detMovSocio', d.socio_nombre + (d.codigo_socio ? ' (Cód. ' + d.codigo_socio + ')' : ''));
      setTxt('detMovCreado', fecha(d.create_vehiculo));
      setTxt('detMovActualizado', fecha(d.update_vehiculo));

      const est = document.getElementById('detMovEstado');
      const activo = parseInt(d.estado_vehiculo) === 1;
      est.className = 'badge badge-sm border-radius-pill mt-1 ' + (activo ? 'bg-gradient-success' : 'bg-gradient-secondary');
      est.textContent = activo ? 'Activo' : 'Inactivo';

      const cont = document.getElementById('detMovChoferes');
      const chofs = d.choferes || [];
      cont.innerHTML = chofs.length
        ? chofs.map(c => `<div class="d-flex justify-content-between py-1 border-bottom border-light">
              <span class="font-weight-bold text-dark">${esc(c.nombre_chofer)}${parseInt(c.titular) === 1 ? ' <span class="badge badge-sm bg-gradient-success">Titular</span>' : ''}</span>
              <span>Lic. ${esc(c.licencia_chofer)} · ${esc(c.telefono_persona)}</span>
            </div>`).join('')
        : 'Sin choferes asignados.';

      document.getElementById('btnDetMovEditar').href = `${baseUrl}/moviles/update?id=${d.id_vehiculo}`;

      const modalEl = document.getElementById('modalDetalleMovil');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
    });
}
</script>