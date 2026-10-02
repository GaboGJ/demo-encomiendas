<!-- MODAL DETALLE DE SINDICATO -->
<div class="modal fade" id="modalDetalleSindicato" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">

      <div class="modal-header bg-gradient-dark text-white p-3">
        <div class="d-flex align-items-center">
          <span class="material-symbols-rounded me-2">domain</span>
          <h6 class="modal-title text-white font-weight-bold mb-0">Detalle de Sindicato</h6>
        </div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl bg-gradient-success border-radius-lg shadow-sm mx-auto d-flex align-items-center justify-content-center text-white font-weight-bold fs-4" id="detSinIniciales">--</div>
          <h6 class="text-dark font-weight-bolder mt-2 mb-0" id="detSinNombre">-</h6>
          <span class="badge badge-sm bg-gradient-success border-radius-pill mt-1" id="detSinEstado">-</span>
          <span class="badge badge-sm bg-gradient-dark border-radius-pill mt-1 d-none" id="detSinPrincipal">Principal</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Sucursales vigentes</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detSinSucursales">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-gray-100 border-radius-md h-100">
              <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Socios vigentes</span>
              <p class="text-xs font-weight-bold text-dark mb-0" id="detSinSocios">-</p>
            </div>
          </div>
        </div>

        <div class="border border-radius-md p-3 mb-3">
          <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2">
            <i class="material-symbols-rounded text-sm align-middle me-1">apartment</i> Datos de la Institución
          </h6>
          <p class="text-xxs text-secondary mb-1"><strong>Sigla:</strong> <span id="detSinSigla">-</span></p>
          <p class="text-xxs text-secondary mb-1"><strong>NIT / Registro legal:</strong> <span id="detSinNit">-</span></p>
          <p class="text-xxs text-secondary mb-1"><strong>Teléfono:</strong> <span id="detSinTel">-</span></p>
          <p class="text-xxs text-secondary mb-0"><strong>Dirección:</strong> <span id="detSinDir">-</span></p>
        </div>

        <div class="d-flex justify-content-between text-xxs text-secondary">
          <span><strong>Registrado:</strong> <span id="detSinCreado">-</span></span>
          <span><strong>Última edición:</strong> <span id="detSinActualizado">-</span></span>
        </div>
      </div>

      <div class="modal-footer bg-gray-100 py-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
        <a href="#" id="btnDetSinEditar" class="btn btn-sm bg-gradient-success mb-0">
          <i class="material-symbols-rounded text-sm me-1 align-middle">edit</i> Editar
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function verDetalleSindicato(idSindicato) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';

  fetch(`${baseUrl}/sindicatos/detalle?id=${idSindicato}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        return;
      }

      const d = res.data;
      const setTxt = (id, v) => { document.getElementById(id).textContent = (v !== null && v !== undefined && String(v).trim() !== '') ? v : '-'; };
      const fecha = f => f ? f.substring(0, 10).split('-').reverse().join('/') : '-';

      const base = (d.sigla_sindicato || d.nombre_sindicato || '').trim();
      setTxt('detSinIniciales', base.substring(0, 2).toUpperCase());
      setTxt('detSinNombre', d.nombre_sindicato);
      setTxt('detSinSigla', d.sigla_sindicato);
      setTxt('detSinNit', d.personeria_sindicato);
      setTxt('detSinTel', d.telefono_sindicato);
      setTxt('detSinDir', d.direccion_sindicato);
      setTxt('detSinSucursales', d.total_sucursales);
      setTxt('detSinSocios', d.total_socios);
      setTxt('detSinCreado', fecha(d.create_sindicato));
      setTxt('detSinActualizado', fecha(d.update_sindicato));

      const est = document.getElementById('detSinEstado');
      const activo = parseInt(d.estado_sindicato) === 1;
      est.className = 'badge badge-sm border-radius-pill mt-1 ' + (activo ? 'bg-gradient-success' : 'bg-gradient-secondary');
      est.textContent = activo ? 'Activo' : 'Inactivo';

      document.getElementById('detSinPrincipal').classList.toggle('d-none', parseInt(d.es_principal_sindicato) !== 1);
      document.getElementById('btnDetSinEditar').href = `${baseUrl}/sindicatos/update?id=${d.id_sindicato}`;

      const modalEl = document.getElementById('modalDetalleSindicato');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' });
    });
}
</script>