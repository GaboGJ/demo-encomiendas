<!-- MODAL: ASIGNAR CHOFERES A UN MÓVIL -->
<div class="modal fade" id="modalAsignarChoferes" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-radius-xl">
    <div class="modal-header bg-gradient-success text-white p-3">
      <h5 class="modal-title text-white font-weight-bold fs-6 mb-0 d-flex align-items-center">
        <i class="material-symbols-rounded me-2">group_add</i> Asignar choferes · <span id="asigMovilNombre" class="ms-1">-</span>
      </h5>
      <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body p-2 p-sm-3">
      <p class="text-xxs text-secondary mb-2 px-1">
        Pulse <strong>+ Asignar</strong> en los choferes que podrán manejar esta unidad.
        El <strong>socio titular</strong> del móvil es siempre el chofer titular y queda asignado automáticamente; los demás quedan como relevo.
      </p>

      <div class="table-responsive p-0">
        <table class="table table-borderless align-items-center mb-0 w-100" id="tablaAsignarChoferes">
          <thead>
            <tr>
              <th data-priority="1" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 pe-3 border-top border-bottom border-light">Chofer</th>
              <th data-priority="3" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">C.I. / Licencia</th>
              <th data-priority="1" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 border-top border-bottom border-light">Acción</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <div class="modal-footer bg-gray-100 p-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
      <span class="text-xs font-weight-bold text-secondary" id="asigResumen">0 asignado(s)</span>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary border-radius-md mb-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-sm bg-gradient-success border-radius-md mb-0" id="btnGuardarAsig">Guardar asignación</button>
      </div>
    </div>
  </div></div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const modalEl = document.getElementById('modalAsignarChoferes');
  const sel = new Set();
  let idVehiculo = 0, datos = [], dt = null;

  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
  const esTitular = c => parseInt(c.es_socio_titular) === 1;

  function resumen() {
    document.getElementById('asigResumen').textContent = sel.size + ' asignado(s)';
  }

  function filaChofer(c) {
    const id = String(c.id_chofer), on = sel.has(id), tit = esTitular(c);

    const nombre = '<span class="text-xs font-weight-bold text-dark text-wrap">' + esc(c.nombre_chofer) + '</span>' +
      (tit ? ' <span class="badge badge-sm bg-gradient-info">Titular</span>' : '');

    const doc = '<span class="text-xs text-dark">C.I. ' + esc(c.carnet_persona) + '</span>' +
      '<span class="d-block text-xxs text-secondary">Lic. ' + esc(c.licencia_chofer) + '</span>';

    // El socio titular siempre está asignado y no se puede quitar
    const accion = tit
      ? '<button type="button" class="btn btn-sm mb-0 px-2 py-1 text-xxs bg-gradient-info text-white" disabled>★ Titular</button>'
      : '<button type="button" class="btn btn-sm mb-0 px-2 py-1 text-xxs asig-sel ' +
          (on ? 'bg-gradient-success text-white' : 'btn-outline-success') + '" data-id="' + id + '">' +
          (on ? '✔ Asignado' : '+ Asignar') + '</button>';

    return { chofer: nombre, doc: doc, accion: accion };
  }

  function renderTabla() {
    if (!dt) return;
    dt.clear();
    dt.rows.add(datos.map(filaChofer)).draw(false);
    resumen();
  }

  function iniciarTabla() {
    if (dt || typeof inicializarDataTable !== 'function') return;
    dt = inicializarDataTable('#tablaAsignarChoferes', {
      ordering: false,
      placeholder: 'Buscar chofer, C.I. o licencia...',
      pageLength: 5,
      columns: [
        { data: 'chofer', className: 'ps-4', responsivePriority: 1 },
        { data: 'doc',    responsivePriority: 3 },
        { data: 'accion', className: 'text-end pe-4', orderable: false, responsivePriority: 1 }
      ]
    });
  }

  // Clic en "Asignar" (delegado: funciona con paginación y filas responsive)
  document.getElementById('tablaAsignarChoferes').addEventListener('click', function (e) {
    const b = e.target.closest('.asig-sel');
    if (!b) return;
    const id = b.dataset.id;
    if (sel.has(id)) sel.delete(id); else sel.add(id);
    renderTabla();
  });

  // Con el modal ya visible, DataTables puede medir bien las columnas
  modalEl.addEventListener('shown.bs.modal', function () {
    iniciarTabla();
    renderTabla();
    if (dt) {
      dt.columns.adjust();
      if (dt.responsive) dt.responsive.recalc();
    }
  });

  modalEl.addEventListener('hidden.bs.modal', function () {
    sel.clear(); datos = [];
    if (dt) dt.clear().draw();
  });

  window.abrirAsignarChoferes = function (id) {
    idVehiculo = id;
    sel.clear(); datos = [];

    fetch(`${baseUrl}/moviles/choferesAsignacion?id=${id}&_=${Date.now()}`, { cache: 'no-store' })
      .then(r => r.json())
      .then(res => {
        if (!res.success) { Swal.fire('Error', res.message, 'error'); return; }
        document.getElementById('asigMovilNombre').textContent = res.movil;
        datos = res.choferes || [];

        datos.forEach(c => {
          // Ya asignados en la BD + el socio titular (siempre asignado)
          if (parseInt(c.asignado) || esTitular(c)) sel.add(String(c.id_chofer));
        });

        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
      })
      .catch(() => Swal.fire('Error', 'Error al conectar con el servidor.', 'error'));
  };

  document.getElementById('btnGuardarAsig').addEventListener('click', function () {
    const btn = this; btn.disabled = true;
    const fd = new FormData();
    fd.append('id_vehiculo', idVehiculo);
    fd.append('ids_json', JSON.stringify(Array.from(sel)));

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