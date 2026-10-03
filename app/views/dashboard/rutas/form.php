<?php
/**
 * Formulario compartido por new.php y update.php.
 * Variables: $modo ('nuevo'|'editar'), $ru (datos de la ruta; vacío en "nuevo"),
 *            $origenes, $destinos, $contenidos, $tarifas.
 */
$h         = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$esEdicion = ($modo ?? 'nuevo') === 'editar';
$precio    = $esEdicion ? number_format((float)$ru['base_precio_pasaje'], 2, '.', '') : '';
?>
<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-10 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0">
            <?= $esEdicion ? 'Editar Ruta: ' . $h($ru['ciudad_origen'] . ' ➔ ' . $ru['ciudad_destino']) : 'Registrar Nueva Ruta' ?>
          </h5>
          <p class="text-xs text-secondary mb-0">
            <?= $esEdicion
                ? 'Modifique el precio del pasaje y las tarifas de encomienda. El origen y el destino no se pueden cambiar.'
                : 'Defina el trayecto entre dos sucursales, el precio del pasaje y las tarifas de encomienda por tipo de contenido.' ?>
          </p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4">
          <form id="formRuta" onsubmit="return false;" autocomplete="off">
            <?php if ($esEdicion): ?>
              <input type="hidden" name="id_ruta" value="<?= (int)$ru['id_precio_pasaje'] ?>">
            <?php endif; ?>

            <div class="row g-3 g-md-4">

              <!-- TRAYECTO Y PASAJE -->
              <div class="col-12 col-lg-5">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">route</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Trayecto y Pasaje</h6>
                  </div>

                  <?php if ($esEdicion): ?>
                    <div class="input-group input-group-outline is-filled my-2">
                      <label class="form-label">Origen</label>
                      <input type="text" class="form-control bg-gray-100" value="<?= $h($ru['ciudad_origen'] . ' (' . $ru['nombre_origen'] . ')') ?>" readonly>
                    </div>
                    <div class="input-group input-group-outline is-filled my-2">
                      <label class="form-label">Destino</label>
                      <input type="text" class="form-control bg-gray-100" value="<?= $h($ru['ciudad_destino'] . ' (' . $ru['nombre_destino'] . ')') ?>" readonly>
                    </div>
                  <?php else: ?>
                    <label class="form-label text-xs font-weight-bold mb-0">Sucursal de Origen *</label>
                    <div class="input-group input-group-outline is-filled my-1">
                      <select class="form-control" name="id_origen" id="selOrigen" required>
                        <option value="" disabled selected>Seleccione el origen...</option>
                        <?php foreach ($origenes as $s): ?>
                          <option value="<?= (int)$s['id_sucursal'] ?>"><?= $h($s['ciudad_sucursal'] . ' (' . $s['nombre_sucursal'] . ')') ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <label class="form-label text-xs font-weight-bold mb-0 mt-2">Sucursal de Destino *</label>
                    <div class="input-group input-group-outline is-filled my-1">
                      <select class="form-control" name="id_destino" id="selDestino" required>
                        <option value="" disabled selected>Seleccione el destino...</option>
                      </select>
                    </div>
                  <?php endif; ?>

                  <div class="input-group input-group-outline is-filled my-3">
                    <label class="form-label">Precio del Pasaje (Bs.) *</label>
                    <input type="number" class="form-control" name="precio_pasaje" id="precio_pasaje" min="0.01" max="99999999.99" step="0.01" value="<?= $h($precio) ?>" required>
                  </div>

                  <?php if (!$esEdicion && empty($origenes)): ?>
                    <p class="text-xxs text-warning font-weight-bold mb-0">Su sindicato no tiene sucursales activas para usar como origen.</p>
                  <?php endif; ?>
                  <p class="text-xxs text-secondary mb-0">Solo puede existir una ruta por par de sucursales, ni siquiera con rutas de la papelera.</p>
                </div>
              </div>

              <!-- TARIFAS DE ENCOMIENDA -->
              <div class="col-12 col-lg-7">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2">
                    <div class="d-flex align-items-center">
                      <span class="material-symbols-rounded text-success me-2">inventory_2</span>
                      <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Tarifas de Encomienda</h6>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success mb-0 d-inline-flex align-items-center gap-1" id="btnAddTarifa" <?= empty($contenidos) ? 'disabled' : '' ?>>
                      <i class="material-symbols-rounded text-sm">add</i> Agregar tarifa
                    </button>
                  </div>
                  <p class="text-xxs text-secondary mb-3">Una tarifa por tipo de contenido. El peso mínimo es opcional; el máximo es obligatorio. Es opcional dejar la ruta sin tarifas.</p>

                  <?php if (empty($contenidos)): ?>
                    <p class="text-xxs text-warning font-weight-bold">No hay tipos de contenido registrados en <strong>encomiendas_contenidos</strong>; regístrelos para poder crear tarifas.</p>
                  <?php endif; ?>

                  <div id="listaTarifas"></div>
                  <p class="text-xs text-secondary text-center py-3 mb-0" id="lblSinTarifas">Sin tarifas de encomienda. Use "Agregar tarifa".</p>
                </div>
              </div>

            </div>
          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/rutas" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarRuta">
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> <?= $esEdicion ? 'Guardar Cambios' : 'Guardar Ruta' ?>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl    = '<?= rtrim(URL, "/") ?>';
  const endpoint   = '<?= $esEdicion ? "/rutas/actualizar" : "/rutas/guardar" ?>';
  const esEdicion  = <?= $esEdicion ? 'true' : 'false' ?>;
  const CONTENIDOS = <?= json_encode(array_map(function ($c) {
      return ['id' => (int)$c['id_encomienda_contenido'], 'nombre' => $c['nombre_encomienda_contenido']];
  }, $contenidos), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  const DESTINOS   = <?= json_encode(array_map(function ($s) {
      return ['id' => (int)$s['id_sucursal'], 'label' => $s['ciudad_sucursal'] . ' (' . $s['nombre_sucursal'] . ')'];
  }, $destinos), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  const TARIFAS    = <?= json_encode(array_map(function ($t) {
      return [
          'id_contenido' => (int)$t['id_encomienda_contenido'],
          'peso_min'     => $t['peso_minimo'] !== null ? (float)$t['peso_minimo'] : '',
          'peso_max'     => (float)$t['peso_maximo'],
          'precio'       => (float)$t['precio_tarifa_encomienda'],
      ];
  }, $tarifas), JSON_HEX_TAG) ?>;

  const form  = document.getElementById('formRuta');
  const btn   = document.getElementById('btnGuardarRuta');
  const lista = document.getElementById('listaTarifas');
  const vacio = document.getElementById('lblSinTarifas');
  const esc = s => { const x = document.createElement('div'); x.textContent = s == null ? '' : String(s); return x.innerHTML; };

  /* ---------- Destino depende del origen (no puede ser el mismo) ---------- */
  if (!esEdicion) {
    const selO = document.getElementById('selOrigen'), selD = document.getElementById('selDestino');
    selO.addEventListener('change', function () {
      const actual = selD.value;
      selD.innerHTML = '<option value="" disabled selected>Seleccione el destino...</option>' +
        DESTINOS.filter(d => String(d.id) !== selO.value)
                .map(d => `<option value="${d.id}">${esc(d.label)}</option>`).join('');
      if (actual && selD.querySelector(`option[value="${actual}"]`)) selD.value = actual;
    });
  }

  /* ---------- Filas de tarifa ---------- */
  function usados() {
    return Array.from(lista.querySelectorAll('.t-contenido')).map(s => s.value).filter(Boolean);
  }

  function refrescarOpciones() {
    const ids = usados();
    lista.querySelectorAll('.t-contenido').forEach(function (sel) {
      Array.from(sel.options).forEach(function (o) {
        o.disabled = o.value !== '' && o.value !== sel.value && ids.indexOf(o.value) !== -1;
      });
    });
    vacio.classList.toggle('d-none', lista.children.length > 0);
    document.getElementById('btnAddTarifa').disabled = CONTENIDOS.length === 0 || ids.length >= CONTENIDOS.length && lista.querySelectorAll('.t-contenido').length >= CONTENIDOS.length;
  }

  function agregarFila(t) {
    t = t || { id_contenido: '', peso_min: '', peso_max: '', precio: '' };
    const row = document.createElement('div');
    row.className = 'p-2 border border-radius-md bg-gray-100 mb-2 fila-tarifa';
    row.innerHTML =
      '<div class="row g-2 align-items-end">' +
        '<div class="col-12 col-md-4"><label class="form-label text-xxs font-weight-bold mb-0">Tipo de contenido *</label>' +
          '<select class="form-control border px-2 py-1 border-radius-md text-xs bg-white t-contenido">' +
            '<option value="">Seleccione...</option>' +
            CONTENIDOS.map(c => `<option value="${c.id}">${esc(c.nombre)}</option>`).join('') +
          '</select></div>' +
        '<div class="col-4 col-md-2"><label class="form-label text-xxs font-weight-bold mb-0">Peso mín. (kg)</label>' +
          '<input type="number" min="0" step="0.01" class="form-control border px-2 py-1 border-radius-md text-xs bg-white t-pmin"></div>' +
        '<div class="col-4 col-md-2"><label class="form-label text-xxs font-weight-bold mb-0">Peso máx. (kg) *</label>' +
          '<input type="number" min="0.01" step="0.01" class="form-control border px-2 py-1 border-radius-md text-xs bg-white t-pmax"></div>' +
        '<div class="col-4 col-md-3"><label class="form-label text-xxs font-weight-bold mb-0">Precio (Bs.) *</label>' +
          '<input type="number" min="0.01" step="0.01" class="form-control border px-2 py-1 border-radius-md text-xs bg-white t-precio"></div>' +
        '<div class="col-12 col-md-1 text-end"><button type="button" class="btn btn-link text-danger p-1 mb-0 t-del" title="Quitar tarifa">' +
          '<i class="material-symbols-rounded text-sm">delete</i></button></div>' +
      '</div>';
    row.querySelector('.t-contenido').value = t.id_contenido;
    row.querySelector('.t-pmin').value = t.peso_min;
    row.querySelector('.t-pmax').value = t.peso_max;
    row.querySelector('.t-precio').value = t.precio;
    lista.appendChild(row);
    refrescarOpciones();
  }

  lista.addEventListener('change', function (e) { if (e.target.classList.contains('t-contenido')) refrescarOpciones(); });
  lista.addEventListener('click', function (e) {
    const b = e.target.closest('.t-del');
    if (!b) return;
    b.closest('.fila-tarifa').remove();
    refrescarOpciones();
  });
  document.getElementById('btnAddTarifa').addEventListener('click', function () { agregarFila(); });

  TARIFAS.forEach(agregarFila);
  refrescarOpciones();

  /* ---------- Guardar ---------- */
  btn.addEventListener('click', function () {
    if (!form.reportValidity()) return;

    const tarifas = Array.from(lista.querySelectorAll('.fila-tarifa')).map(function (r) {
      return {
        id_contenido: r.querySelector('.t-contenido').value,
        peso_min:     r.querySelector('.t-pmin').value,
        peso_max:     r.querySelector('.t-pmax').value,
        precio:       r.querySelector('.t-precio').value
      };
    });

    const incompleta = tarifas.findIndex(t => !t.id_contenido || !t.peso_max || !t.precio);
    if (incompleta !== -1) {
      Swal.fire({ icon: 'warning', title: 'Tarifa incompleta', text: 'La tarifa #' + (incompleta + 1) + ' necesita tipo de contenido, peso máximo y precio.' });
      return;
    }

    const fd = new FormData(form);
    fd.append('tarifas_json', JSON.stringify(tarifas));

    btn.disabled = true;
    fetch(baseUrl + endpoint, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en el listado (Flash)
          window.location.href = baseUrl + '/rutas';
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar la ruta.', 'error');
          btn.disabled = false;
        }
      })
      .catch(() => {
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
        btn.disabled = false;
      });
  });
})();
</script>