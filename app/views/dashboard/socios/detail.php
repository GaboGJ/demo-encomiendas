<!-- MODAL DETALLE DE SOCIO -->
<div class="modal fade" id="modalDetalleSocio" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-radius-xl">
    <div class="modal-header bg-gradient-dark text-white p-3">
      <div class="d-flex align-items-center">
        <span class="material-symbols-rounded me-2">handshake</span>
        <h6 class="modal-title text-white font-weight-bold mb-0">Detalle de Socio</h6>
      </div>
      <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-4">
      <div class="text-center mb-3">
        <div class="avatar avatar-xl bg-gradient-success border-radius-lg shadow-sm mx-auto d-flex align-items-center justify-content-center text-white font-weight-bold fs-4" id="detSocIniciales">--</div>
        <h6 class="text-dark font-weight-bolder mt-2 mb-0" id="detSocNombre">-</h6>
        <span class="badge badge-sm bg-gradient-success border-radius-pill mt-1" id="detSocEstado">-</span>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-6"><div class="p-3 bg-gray-100 border-radius-md h-100">
          <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Código de socio</span>
          <p class="text-xs font-weight-bold text-dark mb-0" id="detSocCodigo">-</p>
        </div></div>
        <div class="col-6"><div class="p-3 bg-gray-100 border-radius-md h-100">
          <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block">Afiliación</span>
          <p class="text-xs font-weight-bold text-dark mb-0" id="detSocAfiliacion">-</p>
        </div></div>
      </div>
      <div class="border border-radius-md p-3 mb-3">
        <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2"><i class="material-symbols-rounded text-sm align-middle me-1">person</i> Datos Personales</h6>
        <p class="text-xxs text-secondary mb-1"><strong>C.I.:</strong> <span id="detSocCi">-</span></p>
        <p class="text-xxs text-secondary mb-1"><strong>Celular:</strong> <span id="detSocCel">-</span></p>
        <p class="text-xxs text-secondary mb-0"><strong>Dirección:</strong> <span id="detSocDir">-</span></p>
      </div>
      <div class="border border-radius-md p-3 mb-3">
        <h6 class="text-xs font-weight-bolder text-uppercase text-success mb-2"><i class="material-symbols-rounded text-sm align-middle me-1">badge</i> Chofer / Licencia</h6>
        <p class="text-xxs text-secondary mb-1"><strong>Licencia:</strong> <span id="detSocLicencia">-</span> <span id="detSocCategoria"></span></p>
        <p class="text-xxs text-secondary mb-1"><strong>Vencimiento:</strong> <span id="detSocVence">-</span> <span class="badge badge-sm border-radius-pill" id="detSocVigencia"></span></p>
        <p class="text-xxs text-secondary mb-0"><strong>Vehículos titulares:</strong> <span id="detSocVehiculos">-</span></p>
      </div>
      <div class="d-flex justify-content-between text-xxs text-secondary">
        <span><strong>Registrado:</strong> <span id="detSocCreado">-</span></span>
        <span><strong>Última edición:</strong> <span id="detSocActualizado">-</span></span>
      </div>
    </div>
    <div class="modal-footer bg-gray-100 py-2">
      <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
      <a href="#" id="btnDetSocEditar" class="btn btn-sm bg-gradient-success mb-0"><i class="material-symbols-rounded text-sm me-1 align-middle">edit</i> Editar</a>
    </div>
  </div></div>
</div>

<script>
function verDetalleSocio(idSocio) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  fetch(`${baseUrl}/socios/detalle?id=${idSocio}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) { Swal.fire({ icon: 'error', title: 'Error', text: res.message }); return; }
      const d = res.data;
      const setTxt = (id, v) => { document.getElementById(id).textContent = (v !== null && v !== undefined && String(v).trim() !== '') ? v : '-'; };
      const fecha = f => f ? f.substring(0, 10).split('-').reverse().join('/') : '-';

      setTxt('detSocIniciales', ((d.nombre_persona || '').charAt(0) + (d.apellido_paterno_persona || '').charAt(0)).toUpperCase());
      setTxt('detSocNombre', d.nombre_completo);
      setTxt('detSocCodigo', d.codigo_socio);
      setTxt('detSocAfiliacion', fecha(d.fecha_afiliacion_socio));
      setTxt('detSocCi', d.carnet_persona);
      setTxt('detSocCel', d.telefono_persona);
      setTxt('detSocDir', d.direccion_persona);
      setTxt('detSocLicencia', d.licencia_chofer || 'Sin licencia');
      document.getElementById('detSocCategoria').textContent = d.categoria_licencia_chofer ? '(Cat. ' + d.categoria_licencia_chofer + ')' : '';
      setTxt('detSocVence', fecha(d.vencimiento_licencia_chofer));
      setTxt('detSocVehiculos', d.total_vehiculos);
      setTxt('detSocCreado', fecha(d.create_socio));
      setTxt('detSocActualizado', fecha(d.update_socio));

      const est = document.getElementById('detSocEstado');
      const activo = parseInt(d.estado_socio) === 1;
      est.className = 'badge badge-sm border-radius-pill mt-1 ' + (activo ? 'bg-gradient-success' : 'bg-gradient-secondary');
      est.textContent = activo ? 'Activo' : 'Inactivo';

      const vig = document.getElementById('detSocVigencia');
      let txt = '', cls = 'd-none';
      if (d.dias_vencimiento !== null && d.dias_vencimiento !== undefined) {
        const dias = parseInt(d.dias_vencimiento);
        if (dias < 0)        { txt = 'Vencida';    cls = 'bg-gradient-danger'; }
        else if (dias <= 30) { txt = 'Por vencer'; cls = 'bg-gradient-warning'; }
        else                 { txt = 'Vigente';    cls = 'bg-gradient-success'; }
      }
      vig.className = 'badge badge-sm border-radius-pill ' + cls;
      vig.textContent = txt;

      document.getElementById('btnDetSocEditar').href = `${baseUrl}/socios/update?id=${d.id_socio}`;
      const modalEl = document.getElementById('modalDetalleSocio');
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al conectar con el servidor.' }));
}
</script>