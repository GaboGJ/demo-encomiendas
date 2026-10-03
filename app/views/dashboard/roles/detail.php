<!-- MODAL DETALLE DE ROL -->
<div class="modal fade" id="modalDetalleRol" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">

      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">shield_person</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">Detalle de Rol</h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl bg-gradient-success border-radius-lg shadow-sm mx-auto d-flex align-items-center justify-content-center text-white">
            <i class="material-symbols-rounded fs-3">shield_person</i>
          </div>
          <h6 class="text-dark font-weight-bolder mt-2 mb-0" id="detRolNombre">-</h6>
          <span class="badge badge-sm bg-gradient-success border-radius-pill mt-1" id="detRolEstado">-</span>
          <span class="badge badge-sm bg-gradient-dark border-radius-pill mt-1 d-none" id="detRolSistema">Sistema</span>
          <p class="text-xxs text-secondary mt-2 mb-0" id="detRolDescripcion">-</p>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Usuarios asignados</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detRolUsuarios">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Permisos</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detRolPermisos">-</p>
            </div>
          </div>
        </div>

        <div class="border border-radius-md p-3 mb-3">
          <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
            <i class="material-symbols-rounded text-sm align-middle me-1">lock_open</i> Permisos por Módulo
          </h6>
          <div id="detRolMatriz" class="text-xxs text-secondary">-</div>
        </div>

        <div class="d-flex justify-content-between flex-wrap gap-1 text-xxs text-secondary">
          <span><strong>Registrado:</strong> <span id="detRolCreado">-</span></span>
          <span><strong>Última edición:</strong> <span id="detRolActualizado">-</span></span>
        </div>
      </div>

      <div class="modal-footer bg-gray-100 py-2 flex-wrap gap-1">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
        <a href="#" id="btnDetRolEditar" class="btn btn-sm btn-outline-dark mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">edit</i> Editar
        </a>
        <a href="#" id="btnDetRolPermisos" class="btn btn-sm bg-gradient-success mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">lock_open</i> Permisos
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function verDetalleRol(idRol) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  fetch(`${baseUrl}/roles/detalle?id=${idRol}`)
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
      const protegido = parseInt(d.es_protegido) === 1;

      setTxt('detRolNombre', d.nombre_rol);
      setTxt('detRolDescripcion', d.descripcion_rol || 'Sin descripción');
      setTxt('detRolUsuarios', d.total_usuarios);
      setTxt('detRolPermisos', protegido ? 'Acceso total' : d.total_permisos + ' permiso(s)');
      setTxt('detRolCreado', fecha(d.create_rol));
      setTxt('detRolActualizado', fecha(d.update_rol));

      const est = document.getElementById('detRolEstado');
      const activo = parseInt(d.estado_rol) === 1;
      est.className = 'badge badge-sm border-radius-pill mt-1 ' + (activo ? 'bg-gradient-success' : 'bg-gradient-secondary');
      est.textContent = activo ? 'Activo' : 'Inactivo';
      document.getElementById('detRolSistema').classList.toggle('d-none', !protegido);

      const cont = document.getElementById('detRolMatriz');
      const perm = d.permisos || [];
      cont.innerHTML = protegido
        ? 'Este rol del sistema tiene acceso total a todos los módulos.'
        : (perm.length
            ? perm.map(p => `<div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light gap-2">
                  <span class="font-weight-bold text-dark">${esc(p.nombre_modulo)}</span>
                  <span class="text-end">${esc(p.acciones)}</span>
                </div>`).join('')
            : 'Este rol aún no tiene permisos asignados.');

      document.getElementById('btnDetRolEditar').href = `${baseUrl}/roles/update?id=${d.id_rol}`;
      document.getElementById('btnDetRolPermisos').href = `${baseUrl}/roles/permisos?id=${d.id_rol}`;

      const modalEl = document.getElementById('modalDetalleRol');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
    });
}
</script>