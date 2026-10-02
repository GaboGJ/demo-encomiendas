<!-- MODAL DETALLE DE CHOFER -->
<div class="modal fade" id="modalDetalleChofer" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">

      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">badge</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">Detalle de Chofer</h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl bg-gradient-success border-radius-lg shadow-sm mx-auto d-flex align-items-center justify-content-center text-white font-weight-bold fs-4" id="detChoIniciales">--</div>
          <h6 class="text-dark font-weight-bolder mt-2 mb-0" id="detChoNombre">-</h6>
          <span class="badge badge-sm bg-gradient-success border-radius-pill mt-1" id="detChoEstado">-</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Licencia</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detChoLicencia">-</p>
              <span class="text-xxs text-secondary" id="detChoCategoria">-</span>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Vencimiento</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detChoVence">-</p>
              <span class="badge badge-sm border-radius-pill" id="detChoVigencia">-</span>
            </div>
          </div>
        </div>

        <div class="border border-radius-md p-3 mb-3">
          <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
            <i class="material-symbols-rounded text-sm align-middle me-1">person</i> Datos Personales
          </h6>
          <p class="text-xxs text-secondary mb-1"><strong>C.I.:</strong> <span id="detChoCi">-</span></p>
          <p class="text-xxs text-secondary mb-1"><strong>Celular:</strong> <span id="detChoCel">-</span></p>
          <p class="text-xxs text-secondary mb-1"><strong>Dirección:</strong> <span id="detChoDir">-</span></p>
          <p class="text-xxs text-secondary mb-0"><strong>Vehículos asignados:</strong> <span id="detChoVehiculos">-</span></p>
        </div>

        <div class="d-flex justify-content-between text-xxs text-secondary">
          <span><strong>Registrado:</strong> <span id="detChoCreado">-</span></span>
          <span><strong>Última edición:</strong> <span id="detChoActualizado">-</span></span>
        </div>
      </div>

      <div class="modal-footer bg-gray-100 py-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
        <a href="#" id="btnDetChoEditar" class="btn btn-sm bg-gradient-success mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">edit</i> Editar
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function verDetalleChofer(idChofer) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  fetch(`${baseUrl}/choferes/detalle?id=${idChofer}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        return;
      }

      const d = res.data;
      const setTxt = (id, v) => { document.getElementById(id).textContent = (v !== null && v !== undefined && String(v).trim() !== '') ? v : '-'; };
      const fecha = f => f ? f.substring(0, 10).split('-').reverse().join('/') : '-';

      const ini = ((d.nombre_persona || '').charAt(0) + (d.apellido_paterno_persona || '').charAt(0)).toUpperCase();
      setTxt('detChoIniciales', ini);
      setTxt('detChoNombre', d.nombre_completo);
      setTxt('detChoLicencia', d.licencia_chofer);
      setTxt('detChoCategoria', d.categoria_licencia_chofer ? 'Categoría ' + d.categoria_licencia_chofer : 'Sin categoría');
      setTxt('detChoVence', fecha(d.vencimiento_licencia_chofer));
      setTxt('detChoCi', d.carnet_persona);
      setTxt('detChoCel', d.telefono_persona);
      setTxt('detChoDir', d.direccion_persona);
      setTxt('detChoVehiculos', d.total_vehiculos);
      setTxt('detChoCreado', fecha(d.create_chofer));
      setTxt('detChoActualizado', fecha(d.update_chofer));

      const est = document.getElementById('detChoEstado');
      const activo = parseInt(d.estado_chofer) === 1;
      est.className = 'badge badge-sm border-radius-pill mt-1 ' + (activo ? 'bg-gradient-success' : 'bg-gradient-secondary');
      est.textContent = activo ? 'Activo' : 'Inactivo';

      // Vigencia de la licencia
      const vig = document.getElementById('detChoVigencia');
      let txt = 'Sin fecha', cls = 'bg-gradient-secondary';
      if (d.dias_vencimiento !== null) {
        const dias = parseInt(d.dias_vencimiento);
        if (dias < 0)        { txt = 'Vencida';    cls = 'bg-gradient-danger'; }
        else if (dias <= 30) { txt = 'Por vencer'; cls = 'bg-gradient-warning'; }
        else                 { txt = 'Vigente';    cls = 'bg-gradient-success'; }
      }
      vig.className = 'badge badge-sm border-radius-pill ' + cls;
      vig.textContent = txt;

      document.getElementById('btnDetChoEditar').href = `${baseUrl}/choferes/update?id=${d.id_chofer}`;

      const modalEl = document.getElementById('modalDetalleChofer');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
    });
}
</script>