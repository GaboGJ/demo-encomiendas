<!-- MODAL: ASIGNAR CHOFERES A UN MÓVIL -->
<div class="modal fade" id="modalAsignarChoferes" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-radius-xl">
    <div class="modal-header bg-gradient-success text-white p-3">
      <h5 class="modal-title text-white font-weight-bold fs-6 mb-0 d-flex align-items-center">
        <i class="material-symbols-rounded me-2">group_add</i> Asignar choferes · <span id="asigMovilNombre" class="ms-1">-</span>
      </h5>
      <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-3">
      <p class="text-xxs text-secondary mb-2">Marque los choferes del móvil y elija un <strong>titular</strong>; los demás quedan como <strong>relevo</strong>. Un socio también aparece aquí porque es chofer.</p>
      <div class="input-group input-group-outline mb-3">
        <input type="text" class="form-control" id="asigBuscar" placeholder="Buscar por nombre, C.I. o licencia...">
      </div>
      <div id="asigLista" class="border border-radius-md" style="max-height:50vh;overflow-y:auto;">
        <div class="text-center text-xs text-secondary py-4">Cargando choferes...</div>
      </div>
      <p class="text-xs font-weight-bold text-secondary mt-2 mb-0" id="asigResumen">0 seleccionado(s)</p>
    </div>
    <div class="modal-footer bg-gray-100 p-3">
      <button type="button" class="btn btn-sm bg-gradient-secondary border-radius-md mb-0" data-bs-dismiss="modal">Cancelar</button>
      <button type="button" class="btn btn-sm bg-gradient-success border-radius-md mb-0" id="btnGuardarAsig">Guardar asignación</button>
    </div>
  </div></div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const lista = document.getElementById('asigLista');
  const sel = new Set();       // ids de chofer marcados
  let titular = null;          // id de chofer titular
  let idVehiculo = 0;
  let datos = [];

  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };

  function resumen() {
    document.getElementById('asigResumen').textContent =
      sel.size + ' seleccionado(s)' + (sel.size ? (titular ? '' : ' · falta elegir titular') : '');
  }

  function render() {
    const q = document.getElementById('asigBuscar').value.trim().toLowerCase();
    const items = datos.filter(c => !q || (c.nombre_chofer + ' ' + c.carnet_persona + ' ' + c.licencia_chofer).toLowerCase().includes(q));

    if (!items.length) { lista.innerHTML = '<div class="text-center text-xs text-secondary py-4">Sin choferes para mostrar.</div>'; return; }

    lista.innerHTML = items.map(c => {
      const id = String(c.id_chofer), on = sel.has(id);
      return `<div class="d-flex align-items-center justify-content-between gap-2 p-2 border-bottom">
          <label class="d-flex align-items-center gap-2 mb-0 flex-grow-1" style="cursor:pointer;">
            <input type="checkbox" class="form-check-input mt-0 asig-chk" data-id="${id}" ${on ? 'checked' : ''}>
            <span>
              <span class="text-xs font-weight-bold text-dark d-block">${esc(c.nombre_chofer)}${parseInt(c.es_socio) ? ' <span class="badge badge-sm bg-gradient-info">Socio</span>' : ''}</span>
              <span class="text-xxs text-secondary">C.I. ${esc(c.carnet_persona)} · Lic. ${esc(c.licencia_chofer)}</span>
            </span>
          </label>
          <label class="d-flex align-items-center gap-1 mb-0 text-xxs font-weight-bold ${on ? 'text-success' : 'text-secondary'}" style="white-space:nowrap;">
            <input type="radio" name="asigTitular" class="form-check-input mt-0 asig-tit" data-id="${id}" ${on ? '' : 'disabled'} ${titular === id ? 'checked' : ''}> Titular
          </label>
        </div>`;
    }).join('');
    resumen();
  }

  lista.addEventListener('change', function (e) {
    const chk = e.target.closest('.asig-chk');
    const tit = e.target.closest('.asig-tit');
    if (chk) {
      const id = chk.dataset.id;
      if (chk.checked) { sel.add(id); if (!titular) titular = id; }
      else { sel.delete(id); if (titular === id) titular = sel.size ? Array.from(sel)[0] : null; }
      render();
    } else if (tit) {
      titular = tit.dataset.id;
      resumen();
    }
  });

  document.getElementById('asigBuscar').addEventListener('input', render);

  window.abrirAsignarChoferes = function (id) {
    idVehiculo = id;
    sel.clear(); titular = null; datos = [];
    document.getElementById('asigBuscar').value = '';
    lista.innerHTML = '<div class="text-center text-xs text-secondary py-4">Cargando choferes...</div>';

    fetch(`${baseUrl}/moviles/choferesAsignacion?id=${id}`)
      .then(r => r.json())
      .then(res => {
        if (!res.success) { Swal.fire('Error', res.message, 'error'); return; }
        document.getElementById('asigMovilNombre').textContent = res.movil;
        datos = res.choferes || [];
        datos.forEach(c => {
          if (parseInt(c.asignado)) { sel.add(String(c.id_chofer)); if (parseInt(c.titular)) titular = String(c.id_chofer); }
        });
        if (!titular && sel.size) titular = Array.from(sel)[0];
        render();
        const m = document.getElementById('modalAsignarChoferes');
        (bootstrap.Modal.getInstance(m) || new bootstrap.Modal(m)).show();
      })
      .catch(() => Swal.fire('Error', 'Error al conectar con el servidor.', 'error'));
  };

  document.getElementById('btnGuardarAsig').addEventListener('click', function () {
    if (sel.size && !titular) {
      Swal.fire({ icon: 'warning', title: 'Falta el titular', text: 'Elija el chofer titular del móvil.' });
      return;
    }
    const btn = this; btn.disabled = true;
    const fd = new FormData();
    fd.append('id_vehiculo', idVehiculo);
    fd.append('ids_json', JSON.stringify(Array.from(sel)));
    fd.append('id_titular', titular || 0);

    fetch(baseUrl + '/moviles/guardarChoferes', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.success) window.location.reload(); // el mensaje sale por Flash
        else { Swal.fire('No se pudo asignar', res.message || 'Error al asignar.', 'error'); btn.disabled = false; }
      })
      .catch(() => { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); btn.disabled = false; });
  });
})();
</script>