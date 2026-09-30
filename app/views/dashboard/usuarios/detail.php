<!-- MODAL DETALLE DE USUARIO -->
<div class="modal fade" id="modalDetalleUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">

      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">badge</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">Detalle de Usuario</h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl bg-gradient-success border-radius-lg shadow-sm mx-auto d-flex align-items-center justify-content-center text-white font-weight-bold fs-4" id="detUsrIniciales">--</div>
          <h6 class="text-dark font-weight-bolder mt-2 mb-0" id="detUsrNombre">-</h6>
          <span class="badge badge-sm bg-gradient-success border-radius-pill mt-1" id="detUsrEstado">-</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Rol</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detUsrRol">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Sucursal</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detUsrSucursal">-</p>
            </div>
          </div>
        </div>

        <div class="border border-radius-md p-3 mb-3">
          <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
            <i class="material-symbols-rounded text-sm align-middle me-1">person</i> Datos Personales
          </h6>
          <p class="text-xxs text-secondary mb-1"><strong>C.I.:</strong> <span id="detUsrCi">-</span></p>
          <p class="text-xxs text-secondary mb-1"><strong>Celular:</strong> <span id="detUsrCel">-</span></p>
          <p class="text-xxs text-secondary mb-0"><strong>Dirección:</strong> <span id="detUsrDir">-</span></p>
        </div>

        <div class="d-flex justify-content-between text-xxs text-secondary">
          <span><strong>Registrado:</strong> <span id="detUsrCreado">-</span></span>
          <span><strong>Última edición:</strong> <span id="detUsrActualizado">-</span></span>
        </div>
      </div>

      <div class="modal-footer bg-gray-100 py-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
        <a href="#" id="btnDetUsrEditar" class="btn btn-sm bg-gradient-success mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">edit</i> Editar
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function verDetalleUsuario(idUsuario) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  fetch(`${baseUrl}/usuarios/detalle?id=${idUsuario}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        return;
      }

      const d = res.data;
      const setTxt = (id, v) => { document.getElementById(id).textContent = (v && String(v).trim()) ? v : '-'; };
      const fecha = f => f ? f.substring(0, 16).split('-').reverse().join('/').replace(/^(\d{2}\/\d{2}\/)(\d{4})/, '$1$2') : '-';

      const ini = ((d.nombre_persona || '').charAt(0) + (d.apellido_paterno_persona || '').charAt(0)).toUpperCase();
      setTxt('detUsrIniciales', ini);
      setTxt('detUsrNombre', (d.nombre_completo || '').trim());
      setTxt('detUsrRol', d.nombre_rol);
      setTxt('detUsrSucursal', `${d.nombre_sucursal} (${d.ciudad_sucursal})`);
      setTxt('detUsrCi', d.carnet_persona);
      setTxt('detUsrCel', d.telefono_persona);
      setTxt('detUsrDir', d.direccion_persona);
      setTxt('detUsrCreado', d.create_usuario ? d.create_usuario.substring(0, 10).split('-').reverse().join('/') : '-');
      setTxt('detUsrActualizado', d.update_usuario ? d.update_usuario.substring(0, 10).split('-').reverse().join('/') : '-');

      const est = document.getElementById('detUsrEstado');
      const activo = parseInt(d.estado_usuario) === 1;
      est.className = 'badge badge-sm border-radius-pill mt-1 ' + (activo ? 'bg-gradient-success' : 'bg-gradient-secondary');
      est.textContent = activo ? 'Activo' : 'Inactivo';

      document.getElementById('btnDetUsrEditar').href = `${baseUrl}/usuarios/update?id=${d.id_usuario}`;

      const modalEl = document.getElementById('modalDetalleUsuario');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
    });
}
</script>