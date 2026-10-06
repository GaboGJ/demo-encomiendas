<?php
/**
 * Formulario compartido por new.php y update.php.
 * Variables: $modo, $ru, $origen, $sindicatos, $destinos, $contenidos, $tarifas.
 */
$h         = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$esEdicion = ($modo ?? 'nuevo') === 'editar';
$precio    = $esEdicion ? number_format((float)$ru['base_precio_pasaje'], 2, '.', '') : '';
$sinTxt    = function ($s) use ($h) { return $h($s['nombre_sindicato'] . ($s['sigla_sindicato'] ? ' (' . $s['sigla_sindicato'] . ')' : '')); };
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
                ? 'Modifique el precio del pasaje y las tarifas de encomienda. Origen, destino y sindicato no se pueden cambiar.'
                : 'El origen es su sucursal. Elija el destino, el sindicato de la ruta, el precio del pasaje y las tarifas de encomienda.' ?>
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

                  <?php
                    $origenTxt = $esEdicion ? $ru['ciudad_origen'] . ' (' . $ru['nombre_origen'] . ')'
                               : ($origen ? $origen['ciudad_sucursal'] . ' (' . $origen['nombre_sucursal'] . ')' : 'Sin sucursal asignada');
                  ?>
                  <div class="input-group input-group-outline is-filled my-2">
                    <label class="form-label"><?= $esEdicion ? 'Origen' : 'Origen (su sucursal)' ?></label>
                    <input type="text" class="form-control bg-gray-100" value="<?= $h($origenTxt) ?>" readonly>
                  </div>

                  <?php if ($esEdicion): ?>
                    <div class="input-group input-group-outline is-filled my-2">
                      <label class="form-label">Destino</label>
                      <input type="text" class="form-control bg-gray-100" value="<?= $h($ru['ciudad_destino'] . ' (' . $ru['nombre_destino'] . ')') ?>" readonly>
                    </div>
                    <div class="input-group input-group-outline is-filled my-2">
                      <label class="form-label">Sindicato</label>
                      <input type="text" class="form-control bg-gray-100" value="<?= $h($ru['nombre_sindicato']) ?>" readonly>
                    </div>
                  <?php else: ?>
                           
                                       <label class="form-label text-xs font-weight-bold mb-0 mt-2">Sindicato de la Ruta *</label>
                    <div class="position-relative my-1">
                      <div class="input-group input-group-outline is-filled">
                        <input type="text" class="form-control" id="acSindicato" placeholder="Escriba para buscar el sindicato..." autocomplete="off">
                      </div>
                      <input type="hidden" name="id_sindicato" id="hidSindicato">
                      <div id="listaSindicato" class="autocompletar-lista list-group position-absolute w-100 shadow-sm border-radius-md mt-1"></div>
                    </div>
                                 <label class="form-label text-xs font-weight-bold mb-0">Sucursal de Destino *</label>
                    <div class="position-relative my-1">
                      <div class="input-group input-group-outline is-filled">
                        <input type="text" class="form-control" id="acDestino" placeholder="Escriba para buscar el destino..." autocomplete="off">
                        <button type="button" class="btn btn-sm bg-gradient-success mb-0 px-2" id="btnNuevoDestino" title="Crear nuevo destino"><i class="material-symbols-rounded text-sm">add</i></button>
                      </div>
                      <input type="hidden" name="id_destino" id="hidDestino">
                      <div id="listaDestino" class="autocompletar-lista list-group position-absolute w-100 shadow-sm border-radius-md mt-1"></div>
                    </div>
                  <?php endif; ?>

                  <div class="input-group input-group-outline is-filled my-3">
                    <label class="form-label">Precio del Pasaje (Bs.) *</label>
                    <input type="number" class="form-control" name="precio_pasaje" min="0.01" max="99999999.99" step="0.01" value="<?= $h($precio) ?>" required>
                  </div>

                  <?php if (!$esEdicion && !$origen): ?>
                    <p class="text-xxs text-warning font-weight-bold mb-0">Su sucursal no está activa o no pertenece a su sindicato.</p>
                  <?php endif; ?>
                  <p class="text-xxs text-secondary mb-0">Solo puede existir una ruta por origen, destino y sindicato (incluye la papelera). Cada sindicato puede tener precios distintos al mismo destino.</p>
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
                  <p class="text-xxs text-secondary mb-2">Puede agregar varias tarifas por tipo de contenido usando rangos de peso distintos (sin solaparse). El peso mínimo es opcional; el máximo es obligatorio.</p>

                  <?php if (empty($contenidos)): ?>
                    <p class="text-xxs text-warning font-weight-bold">No hay tipos de contenido registrados en <strong>encomiendas_contenidos</strong>.</p>
                  <?php endif; ?>

                  <div class="table-responsive p-0">
                    <table class="table table-borderless align-items-center mb-0 w-100" id="tablaTarifas">
                      <thead>
                        <tr>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipo de contenido *</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Peso mín. (kg)</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Peso máx. (kg) *</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Precio (Bs.) *</th>
                          <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7"></th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>
                  </div>
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
  const CONTENIDOS = <?= json_encode(array_map(function ($c) {
      return ['id' => (int)$c['id_encomienda_contenido'], 'nombre' => $c['nombre_encomienda_contenido']];
  }, $contenidos), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  // El estado vive aquí (no en el DOM) para que la paginación de DataTables no pierda datos
  let tarifas = <?= json_encode(array_map(function ($t) {
      return [
          'id_contenido' => (int)$t['id_encomienda_contenido'],
          'peso_min'     => $t['peso_minimo'] !== null ? (float)$t['peso_minimo'] : '',
          'peso_max'     => (float)$t['peso_maximo'],
          'precio'       => (float)$t['precio_tarifa_encomienda'],
      ];
  }, $tarifas), JSON_HEX_TAG) ?>;

  const form = document.getElementById('formRuta');
  const btn  = document.getElementById('btnGuardarRuta');
  const tabla = document.getElementById('tablaTarifas');
  const esc = s => { const x = document.createElement('div'); x.textContent = s == null ? '' : String(s); return x.innerHTML; };
  let dt = null;

  const cls = 'form-control border px-2 py-1 border-radius-md text-xs bg-white t-in';
  const inp = (i, f, v, min) => '<input type="number" min="' + min + '" step="0.01" class="' + cls + '" style="min-width:90px" data-i="' + i + '" data-f="' + f + '" value="' + esc(v) + '">';

  function fila(t, i) {
    const opts = CONTENIDOS.map(c =>
      '<option value="' + c.id + '"' + (String(c.id) === String(t.id_contenido) ? ' selected' : '') + '>' + esc(c.nombre) + '</option>').join('');
    return {
      tipo:     '<select class="' + cls + '" style="min-width:150px" data-i="' + i + '" data-f="id_contenido"><option value="">Seleccione...</option>' + opts + '</select>',
      pmin:     inp(i, 'peso_min', t.peso_min, 0),
      pmax:     inp(i, 'peso_max', t.peso_max, 0.01),
      precio:   inp(i, 'precio', t.precio, 0.01),
      acciones: '<button type="button" class="btn btn-link text-danger p-1 mb-0 t-del" data-i="' + i + '" title="Quitar tarifa"><i class="material-symbols-rounded text-sm">delete</i></button>'
    };
  }

  function render() {
    if (!dt) return;
    dt.clear();
    dt.rows.add(tarifas.map(fila)).draw(false);
  }

  // Delegados sobre la tabla: sirven en cualquier página
  tabla.addEventListener('input', function (e) {
    const el = e.target;
    if (el.tagName === 'INPUT' && el.dataset.i !== undefined) tarifas[el.dataset.i][el.dataset.f] = el.value;
  });
  tabla.addEventListener('change', function (e) {
    const el = e.target;
    if (el.tagName === 'SELECT' && el.dataset.i !== undefined) tarifas[el.dataset.i].id_contenido = el.value;
  });
  tabla.addEventListener('click', function (e) {
    const b = e.target.closest('.t-del');
    if (!b) return;
    tarifas.splice(parseInt(b.dataset.i), 1);
    render();
  });

  // Sin límite: se pueden agregar tantas tarifas como se necesiten
  document.getElementById('btnAddTarifa').addEventListener('click', function () {
    tarifas.push({ id_contenido: '', peso_min: '', peso_max: '', precio: '' });
    render();
    dt.page('last').draw(false);
  });

  // inicializarDataTable() vive en layouts/script.php (se carga después de esta vista)
  document.addEventListener('DOMContentLoaded', function () {
    dt = inicializarDataTable('#tablaTarifas', {
      ordering: false,
      placeholder: 'Buscar tarifa...',
      pageLength: 5,
      responsive: false,
      columns: [
        { data: 'tipo' }, { data: 'pmin' }, { data: 'pmax' }, { data: 'precio' },
        { data: 'acciones', className: 'text-end', orderable: false }
      ]
    });
    render();
  });

  // Autocompletar (solo en modo nuevo). crearAutocompletar vive en script.php, que carga después de esta vista.
  document.addEventListener('DOMContentLoaded', function () {
    const inpD = document.getElementById('acDestino');
    if (!inpD) return;
    const norm = s => String(s).toLowerCase();
    const DESTINOS = <?= json_encode(array_map(function ($s) {
        $l = $s['ciudad_sucursal'] . ' (' . $s['nombre_sucursal'] . ')';
        return ['id' => (int)$s['id_sucursal'], 'label' => $l, 'buscar' => mb_strtolower($l, 'UTF-8')];
    }, $destinos ?? []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    const SINDICATOS = <?= json_encode(array_map(function ($s) {
        $l = $s['nombre_sindicato'] . ($s['sigla_sindicato'] ? ' (' . $s['sigla_sindicato'] . ')' : '');
        return ['id' => (int)$s['id_sindicato'], 'label' => $l, 'buscar' => mb_strtolower($l, 'UTF-8')];
    }, $sindicatos ?? []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

    crearAutocompletar({ inputId: 'acDestino', listaId: 'listaDestino', hiddenId: 'hidDestino', data: DESTINOS });
    crearAutocompletar({ inputId: 'acSindicato', listaId: 'listaSindicato', hiddenId: 'hidSindicato', data: SINDICATOS });

    // Sindicato por defecto: el de la sesión (si está en la lista)
    const miSind = <?= (int)($_SESSION['id_sindicato'] ?? 0) ?>;
    const ini = SINDICATOS.find(s => s.id === miSind) || (SINDICATOS.length === 1 ? SINDICATOS[0] : null);
    if (ini) { document.getElementById('acSindicato').value = ini.label; document.getElementById('hidSindicato').value = ini.id; }

    document.getElementById('btnNuevoDestino').addEventListener('click', function () {
      const idSind = document.getElementById('hidSindicato').value;
      if (!idSind) { Swal.fire({ icon: 'warning', title: 'Falta el sindicato', text: 'Seleccione primero el sindicato de la ruta.' }); return; }

      Swal.fire({
        title: 'Nuevo destino', input: 'text', inputLabel: 'Nombre del destino (ciudad)',
        inputAttributes: { maxlength: 50 }, showCancelButton: true,
        confirmButtonText: 'Crear', cancelButtonText: 'Cancelar',
        inputValidator: v => (v || '').trim().length < 2 ? 'Ingrese el nombre del destino.' : null
      }).then(function (r) {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('id_sindicato', idSind);
        fd.append('nombre', r.value.trim());
        fetch(baseUrl + '/rutas/crearDestino', { method: 'POST', body: fd })
          .then(x => x.json())
          .then(res => {
            if (!res.success) { Swal.fire('No se pudo crear', res.message, 'error'); return; }
            if (!DESTINOS.some(d => d.id === res.id)) DESTINOS.push({ id: res.id, label: res.label, buscar: norm(res.label) });
            inpD.value = res.label;
            document.getElementById('hidDestino').value = res.id;
          })
          .catch(() => Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'));
      });
    });
  });

  btn.addEventListener('click', function () {
    if (!form.reportValidity()) return;

    const eD = form.elements.id_destino, eS = form.elements.id_sindicato;
    if (eD && !eD.value) { Swal.fire({ icon: 'warning', title: 'Falta el destino', text: 'Seleccione un destino de la lista o cree uno nuevo.' }); return; }
    if (eS && !eS.value) { Swal.fire({ icon: 'warning', title: 'Falta el sindicato', text: 'Seleccione un sindicato de la lista.' }); return; }

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