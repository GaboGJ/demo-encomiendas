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
      <p class="text-xxs text-secondary mb-2">Pulse <strong>+ Asignar</strong> en los choferes del móvil y marque con <strong>★ Titular</strong> a uno; los demás quedan como relevo. El socio titular de la unidad aparece primero.</p>
      <div class="input-group input-group-outline mb-3">
        <input type="text" class="form-control" id="asigBuscar" placeholder="Buscar por nombre, C.I. o licencia...">
      </div>
      <div id="asigLista" class="border border-radius-md" style="max-height:50vh;overflow-y:auto;">
        <div class="text-center text-xs text-secondary py-4">Cargando choferes...</div>
      </div>
      <p class="text-xs font-weight-bold text-secondary mt-2 mb-0" id="asigResumen">0 asignado(s)</p>
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
  const modalEl = document.getElementById('modalAsignarChoferes');
  const sel = new Set();
  let titular = null, idVehiculo = 0, datos = [];

  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };

  function resumen() {
    document.getElementById('asigResumen').textContent =
      sel.size + ' asignado(s)' + (sel.size && !titular ? ' · falta elegir titular' : '');
  }

  function render() {
    const q = document.getElementById('asigBuscar').value.trim().toLowerCase();
    const items = datos.filter(c => !q || (c.nombre_chofer + ' ' + c.carnet_persona + ' ' + c.licencia_chofer).toLowerCase().includes(q));

    if (!items.length) {
      lista.innerHTML = '<div class="text-center text-xs text-secondary py-4">Sin choferes para mostrar.</div>';
      resumen(); return;
    }

    lista.innerHTML = items.map(c => {
      const id = String(c.id_chofer), on = sel.has(id), esTit = titular === id;
      return `<div class="d-flex align-items-center justify-content-between gap-2 p-2 border-bottom ${on ? 'bg-gray-100' : ''}">
          <div class="flex-grow-1" style="min-width:0;">
            <span class="text-xs font-weight-bold text-dark d-block text-wrap">${esc(c.nombre_chofer)}${parseInt(c.es_socio_titular) ? ' <span class="badge badge-sm bg-gradient-info">Socio titular</span>' : ''}</span>
            <span class="text-xxs text-secondary">C.I. ${esc(c.carnet_persona)} · Lic. ${esc(c.licencia_chofer)}</span>
          </div>
          <div class="d-flex gap-1 flex-shrink-0">
            <button type="button" class="btn btn-sm mb-0 px-2 py-1 text-xxs asig-sel ${on ? 'bg-gradient-success text-white' : 'btn-outline-success'}" data-id="${id}">
              ${on ? '✔ Asignado' : '+ Asignar'}
            </button>
            <button type="button" class="btn btn-sm mb-0 px-2 py-1 text-xxs asig-tit ${esTit ? 'bg-gradient-warning text-white' : 'btn-outline-secondary'}" data-id="${id}" ${on ? '' : 'disabled'}>
              ★ Titular
            </button>
          </div>
        </div>`;
    }).join('');
    resumen();
  }

  lista.addEventListener('click', function (e) {
    const b1 = e.target.closest('.asig-sel');
    const b2 = e.target.closest('.asig-tit');
    if (b1) {
      const id = b1.dataset.id;
      if (sel.has(id)) {
        sel.delete(id);
        if (titular === id) titular = sel.size ? Array.from(sel)[0] : null;
      } else {
        sel.add(id);
        if (!titular) titular = id;
      }
      render();
    } else if (b2 && !b2.disabled) {
      titular = b2.dataset.id;
      render();
    }
  });

  document.getElementById('asigBuscar').addEventListener('input', render);

  // Al cerrar se limpia todo: la próxima apertura vuelve a leer del servidor
  modalEl.addEventListener('hidden.bs.modal', function () {
    sel.clear(); titular = null; datos = [];
    lista.innerHTML = '';
  });

  window.abrirAsignarChoferes = function (id) {
    idVehiculo = id;
    sel.clear(); titular = null; datos = [];
    document.getElementById('asigBuscar').value = '';
    lista.innerHTML = '<div class="text-center text-xs text-secondary py-4">Cargando choferes...</div>';

    fetch(`${baseUrl}/moviles/choferesAsignacion?id=${id}&_=${Date.now()}`, { cache: 'no-store' })
      .then(r => r.json())
      .then(res => {
        if (!res.success) { Swal.fire('Error', res.message, 'error'); return; }
        document.getElementById('asigMovilNombre').textContent = res.movil;
        datos = res.choferes || [];

        // Lo que ya está guardado en la BD
        datos.forEach(c => {
          if (parseInt(c.asignado)) {
            sel.add(String(c.id_chofer));
            if (parseInt(c.titular)) titular = String(c.id_chofer);
          }
        });

        // Unidad sin choferes: se preselecciona el socio titular de la unidad
        if (!sel.size) {
          const st = datos.find(c => parseInt(c.es_socio_titular));
          if (st) { sel.add(String(st.id_chofer)); titular = String(st.id_chofer); }
        }
        if (!titular && sel.size) titular = Array.from(sel)[0];

        render();
        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
      })
      .catch(() => Swal.fire('Error', 'Error al conectar con el servidor.', 'error'));
  };

  document.getElementById('btnGuardarAsig').addEventListener('click', function () {
    if (sel.size && !titular) {
      Swal.fire({ icon: 'warning', title: 'Falta el titular', text: 'Marque con ★ al chofer titular del móvil.' });
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
        if (res.success) window.location.reload();
        else { Swal.fire('No se pudo asignar', res.message || 'Error al asignar.', 'error'); btn.disabled = false; }
      })
      .catch(() => { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); btn.disabled = false; });
  });
})();
</script>