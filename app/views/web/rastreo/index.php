<main class="main-content mt-8 py-4">
  <div class="container"><div class="row justify-content-center"><div class="col-12 col-md-8 col-lg-6">
    <div class="card border-0 shadow-sm border-radius-xl p-4">
      <h4 class="font-weight-bolder text-dark mb-1">Rastreo de Encomiendas</h4>
      <p class="text-xs text-secondary mb-3">Ingrese el número de guía que figura en su comprobante.</p>
      <div class="input-group input-group-outline mb-3">
        <label class="form-label">Nº de guía (Ej. ENC-000123)</label>
        <input type="text" class="form-control" id="rGuia" maxlength="50" value="<?= htmlspecialchars($_GET['guia'] ?? '', ENT_QUOTES) ?>">
      </div>
      <button type="button" class="btn bg-gradient-success w-100 mb-3" id="rBuscar">Buscar</button>
      <div id="rResultado"></div>
    </div>
  </div></div></div>
</main>
<script>
function verLogin(){ location.href = '<?= URL ?>/auth'; }
function verRegistro(){ location.href = '<?= URL ?>/auth'; }
(function () {
  const $ = id => document.getElementById(id);
  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
  const fh = f => f ? f.substring(8,10)+'/'+f.substring(5,7)+'/'+f.substring(0,4)+' '+f.substring(11,16) : '';

  function paso(ok, t, sub) {
    return '<div class="d-flex align-items-start gap-2 mb-3"><span class="material-symbols-rounded ' + (ok ? 'text-success' : 'text-secondary') + '">' +
      (ok ? 'check_circle' : 'radio_button_unchecked') + '</span><div><div class="text-sm font-weight-bold ' + (ok ? 'text-dark' : 'text-secondary') + '">' +
      t + '</div>' + (sub ? '<div class="text-xxs text-secondary">' + esc(sub) + '</div>' : '') + '</div></div>';
  }

  function buscar() {
    const g = $('rGuia').value.trim(), out = $('rResultado');
    if (!g) return;
    out.innerHTML = '<p class="text-xs text-secondary">Buscando...</p>';
    fetch('<?= URL ?>/rastreo/buscar?guia=' + encodeURIComponent(g), {cache:'no-store'}).then(r => r.json()).then(res => {
      if (!res.success) { out.innerHTML = '<p class="text-xs text-danger font-weight-bold">' + esc(res.message) + '</p>'; return; }
      const d = res.data, entregado = !!d.fecha_entrega;
      out.innerHTML =
        '<div class="p-3 bg-gray-100 border-radius-md mb-3"><div class="d-flex justify-content-between"><span class="text-sm font-weight-bold">#' + esc(d.guia_encomienda) +
        '</span><span class="badge bg-gradient-success">' + esc(d.estado) + '</span></div>' +
        '<div class="text-xs text-secondary mt-1">' + esc(d.origen) + ' ➔ ' + esc(d.destino) + ' · ' + (+d.bultos) + ' bulto(s)</div></div>' +
        paso(true, 'Registrada en origen', fh(d.create_encomienda)) +
        paso(!!d.id_turno, 'Asignada a una unidad', '') +
        paso(d.despachado, 'En camino', d.despachado ? fh(d.fecha_salida_turno + ' ' + d.hora_salida_turno) : '') +
        paso(entregado, 'Entregada al destinatario', fh(d.fecha_entrega));
    }).catch(() => { out.innerHTML = '<p class="text-xs text-danger">Error al consultar. Intente nuevamente.</p>'; });
  }
  $('rBuscar').addEventListener('click', buscar);
  $('rGuia').addEventListener('keydown', e => { if (e.key === 'Enter') buscar(); });
  if ($('rGuia').value) buscar();
})();
</script>