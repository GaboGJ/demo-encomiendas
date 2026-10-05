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
                    <label class="form-label">Origen (su sucursal)</label>
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
                    <label class="form-label text-xs font-weight-bold mb-0">Sucursal de Destino *</label>
                    <div class="input-group input-group-outline is-filled my-1">
                      <select class="form-control" name="id_destino" required>
                        <option value="" disabled selected>Seleccione el destino...</option>
                        <?php foreach ($destinos as $s): ?>
                          <option value="<?= (int)$s['id_sucursal'] ?>"><?= $h($s['ciudad_sucursal'] . ' (' . $s['nombre_sucursal'] . ')') ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <label class="form-label text-xs font-weight-bold mb-0 mt-2">Sindicato de la Ruta *</label>
                    <div class="input-group input-group-outline is-filled my-1">
                      <select class="form-control" name="id_sindicato" required>
                        <?php if (count($sindicatos) !== 1): ?><option value="" disabled selected>Seleccione el sindicato...</option><?php endif; ?>
                        <?php foreach ($sindicatos as $s): ?>
                          <option value="<?= (int)$s['id_sindicato'] ?>" <?= (int)$s['id_sindicato'] === (int)($_SESSION['id_sindicato'] ?? 0) ? 'selected' : '' ?>><?= $sinTxt($s) ?></option>
                        <?php endforeach; ?>
                      </select>
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
                  <p class="text-xxs text-secondary mb-2">Una tarifa por tipo de contenido. El peso mínimo es opcional; el máximo es obligatorio. Dejar la ruta sin tarifas es opcional.</p>

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
  // Estado de las tarifas: vive aquí (no en el DOM) para que la paginación de DataTables no pierda datos
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
    const otros = tarifas.map((x, j) => j !== i ? String(x.id_contenido) : null);
    const opts = CONTENIDOS.map(c =>
      '<option value="' + c.id + '"' + (String(c.id) === String(t.id_contenido) ? ' selected' : '') +
      (otros.indexOf(String(c.id)) !== -1 ? ' disabled' : '') + '>' + esc(c.nombre) + '</option>').join('');
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
    document.getElementById('btnAddTarifa').disabled = !CONTENIDOS.length || tarifas.length >= CONTENIDOS.length;
  }

  // Delegados sobre la tabla: sirven en cualquier página
  tabla.addEventListener('input', function (e) {
    const el = e.target;
    if (el.tagName === 'INPUT' && el.dataset.i !== undefined) tarifas[el.dataset.i][el.dataset.f] = el.value;
  });
  tabla.addEventListener('change', function (e) {
    const el = e.target;
    if (el.tagName === 'SELECT' && el.dataset.i !== undefined) { tarifas[el.dataset.i].id_contenido = el.value; render(); }
  });
  tabla.addEventListener('click', function (e) {
    const b = e.target.closest('.t-del');
    if (!b) return;
    tarifas.splice(parseInt(b.dataset.i), 1);
    render();
  });

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

  btn.addEventListener('click', function () {
    if (!form.reportValidity()) return;

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