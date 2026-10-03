<?php
/**
 * views/dashboard/modelos/configurar.php
 * Editor del plano de asientos de un modelo.
 * Variables (Modelos_controller::configurar): $modelo, $tipos [{id,nombre,es_asiento}], $pisos (con 'elementos'), $bloqueado
 */
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<style>
  .cfg-grid-wrap { overflow: auto; max-width: 100%; padding: 12px; background: #f8f9fa; border-radius: .75rem; border: 1px dashed #d2d6da; }
  .cfg-grid { --cell: clamp(36px, 7.5vw, 54px); display: grid; gap: 4px; width: max-content; margin: 0 auto; user-select: none; -webkit-user-select: none; }
  .cfg-cell { width: var(--cell); height: var(--cell); border-radius: 8px; border: 1px solid #e0e3e7; background: #fff; display: flex; flex-direction: column;
              align-items: center; justify-content: center; cursor: pointer; font-size: .7rem; font-weight: 700; line-height: 1; padding: 0; color: #344767; transition: transform .08s ease; }
  .cfg-cell:hover { transform: scale(1.06); border-color: #4caf50; }
  .cfg-cell .material-symbols-rounded { font-size: calc(var(--cell) * .38); }
  .cfg-cell .lbl { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: calc(var(--cell) * .24); padding: 0 2px; }
  .cfg-cell.seat .lbl { font-size: calc(var(--cell) * .34); }
  .cfg-cell.k0 { background: #e8f5e9; border-color: #66bb6a; color: #2e7d32; }
  .cfg-cell.k1 { background: #344767; border-color: #344767; color: #fff; }
  .cfg-cell.k2 { background: #e3f2fd; border-color: #42a5f5; color: #1565c0; }
  .cfg-cell.k3 { background: #fff3e0; border-color: #ffa726; color: #e65100; }
  .cfg-cell.k4 { background: #f3e5f5; border-color: #ab47bc; color: #6a1b9a; }
  .cfg-cell.k5 { background: #eceff1; border-color: #90a4ae; color: #455a64; }
  .cfg-tool { display: flex; align-items: center; gap: .5rem; width: 100%; text-align: left; padding: .45rem .6rem; border-radius: .5rem; border: 1px solid #e0e3e7; background: #fff; font-size: .75rem; font-weight: 700; color: #344767; }
  .cfg-tool.active { border-color: #4caf50; box-shadow: 0 0 0 2px rgba(76,175,80,.25); background: #e8f5e9; }
  .cfg-tool .sw { width: 22px; height: 22px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid; }
  .cfg-tool .sw .material-symbols-rounded { font-size: 14px; }
  .cfg-tools { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .4rem; }
  @media (min-width: 992px) { .cfg-tools { grid-template-columns: 1fr; } }
  .cfg-tab { border: 0; background: transparent; padding: .4rem .9rem; border-radius: .5rem; font-size: .75rem; font-weight: 700; color: #67748e; white-space: nowrap; }
  .cfg-tab.active { background: #e8f5e9; color: #2e7d32; }
  .cfg-fixed { opacity: .55; pointer-events: none; }
</style>

<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-11 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <div class="card-header bg-white p-3 p-md-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
              <h5 class="font-weight-bolder text-dark mb-0">Configurar Asientos · <?= $h($modelo['nombre_modelo']) ?></h5>
              <p class="text-xs text-secondary mb-0">Elija un elemento a la izquierda y toque las celdas para colocarlo. Las celdas vacías son pasillo.</p>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
              <span class="badge bg-gradient-success px-3 py-2"><span id="lblTotalAsientos">0</span> asientos</span>
              <span class="badge bg-gradient-dark px-3 py-2"><span id="lblTotalEspeciales">0</span> especiales</span>
            </div>
          </div>

          <?php if ($bloqueado): ?>
            <div class="alert alert-warning text-white text-xs mt-3 mb-0 py-2 d-flex align-items-center gap-2">
              <i class="material-symbols-rounded text-sm">lock</i>
              Hay pasajes vendidos en turnos abiertos con este modelo: el plano está en modo solo lectura hasta que esos turnos se despachen o cancelen.
            </div>
          <?php endif; ?>
          <?php if (empty($tipos)): ?>
            <div class="alert alert-danger text-white text-xs mt-3 mb-0 py-2">
              No hay tipos de elemento registrados en la tabla <strong>tipos_elementos</strong> (Asiento, Chofer, Baño, Escalera, etc.). Regístrelos para poder armar el plano.
            </div>
          <?php endif; ?>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-3 p-md-4 <?= $bloqueado ? 'cfg-fixed' : '' ?>">
          <div class="row g-3 g-lg-4">

            <!-- HERRAMIENTAS -->
            <div class="col-12 col-lg-3">
              <div class="p-3 border border-radius-md bg-white">
                <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-2">
                  <i class="material-symbols-rounded text-sm align-middle me-1 text-success">palette</i> Elementos
                </h6>
                <div class="cfg-tools" id="listaTools"></div>

                <hr class="horizontal dark my-3">
                <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-2">Acciones</h6>
                <div class="cfg-tools">
                  <button type="button" class="cfg-tool" data-tool="edit"><span class="material-symbols-rounded text-sm">edit</span> Editar etiqueta</button>
                  <button type="button" class="cfg-tool" data-tool="erase"><span class="material-symbols-rounded text-sm">ink_eraser</span> Borrar celda</button>
                  <button type="button" class="cfg-tool" id="btnRenumerar"><span class="material-symbols-rounded text-sm">pin</span> Renumerar asientos</button>
                  <button type="button" class="cfg-tool" id="btnLimpiarPiso"><span class="material-symbols-rounded text-sm">delete_sweep</span> Vaciar piso</button>
                </div>
                <p class="text-xxs text-secondary mt-2 mb-0">Con el mouse puede arrastrar para pintar varias celdas.</p>
              </div>
            </div>

            <!-- EDITOR -->
            <div class="col-12 col-lg-9">
              <div class="p-3 border border-radius-md bg-white">

                <!-- Pisos -->
                <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                  <div class="d-flex gap-1 overflow-auto" id="tabsPisos"></div>
                  <button type="button" class="btn btn-sm btn-outline-success mb-0 px-2 py-1 d-inline-flex align-items-center gap-1" id="btnAddPiso">
                    <i class="material-symbols-rounded text-sm">add</i> Piso
                  </button>
                </div>

                <!-- Dimensiones -->
                <div class="row g-2 align-items-end mb-3">
                  <div class="col-12 col-md-4">
                    <div class="input-group input-group-outline is-filled">
                      <label class="form-label">Nombre del piso</label>
                      <input type="text" class="form-control" id="pisoNombre" maxlength="50" placeholder="Ej. Planta baja">
                    </div>
                  </div>
                  <div class="col-6 col-md-2">
                    <div class="input-group input-group-outline is-filled">
                      <label class="form-label">Filas</label>
                      <input type="number" class="form-control" id="pisoFilas" min="1" max="10">
                    </div>
                  </div>
                  <div class="col-6 col-md-2">
                    <div class="input-group input-group-outline is-filled">
                      <label class="form-label">Columnas</label>
                      <input type="number" class="form-control" id="pisoCols" min="1" max="20">
                    </div>
                  </div>
                  <div class="col-12 col-md-4 text-md-end">
                    <button type="button" class="btn btn-sm btn-outline-danger mb-0 d-inline-flex align-items-center gap-1" id="btnQuitarPiso">
                      <i class="material-symbols-rounded text-sm">delete</i> Quitar este piso
                    </button>
                  </div>
                </div>

                <!-- Grilla -->
                <div class="cfg-grid-wrap">
                  <div class="cfg-grid" id="grilla"></div>
                </div>
                <p class="text-xxs text-secondary mt-2 mb-0" id="lblResumenPiso"></p>
              </div>
            </div>

          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/modelos" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarPlano" <?= $bloqueado || empty($tipos) ? 'disabled' : '' ?>>
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> Guardar Plano
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl   = '<?= rtrim(URL, "/") ?>';
  const ID_MODELO = <?= (int)$modelo['id_modelo'] ?>;
  const TIPOS     = <?= json_encode($tipos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  const PISOS_DB  = <?= json_encode($pisos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  const MAX_PISOS = 3, MAX_FILAS = 10, MAX_COLS = 20;

  const $ = id => document.getElementById(id);
  const tipoPorId = {};
  TIPOS.forEach(t => tipoPorId[t.id] = t);

  let pisos = [];     // [{numero, nombre, filas, cols, bf, bc, cells:{"f:c":{t,d}}}]
  let activo = 0;     // índice del piso visible
  let tool = null;    // {kind:'tipo', id} | {kind:'edit'} | {kind:'erase'}

  /* ---------- Estilo por tipo (según su nombre en la BD) ---------- */
  function estiloTipo(t) {
    const n = (t.nombre || '').toLowerCase();
    if (t.es_asiento)              return { icon: 'event_seat', k: 0 };
    if (n.includes('chofer'))      return { icon: 'person',     k: 1 };
    if (n.includes('baño') || n.includes('bano')) return { icon: 'wc', k: 2 };
    if (n.includes('escalera'))    return { icon: 'stairs',     k: 3 };
    if (n.includes('tele') || n === 'tv') return { icon: 'tv', k: 4 };
    if (n.includes('puerta'))      return { icon: 'door_front', k: 5 };
    return { icon: 'category', k: 5 };
  }
  const swColors = ['#e8f5e9|#66bb6a|#2e7d32', '#344767|#344767|#fff', '#e3f2fd|#42a5f5|#1565c0', '#fff3e0|#ffa726|#e65100', '#f3e5f5|#ab47bc|#6a1b9a', '#eceff1|#90a4ae|#455a64'];

  /* ---------- Carga inicial desde la BD ---------- */
  function nuevoPiso(numero) {
    return { numero: numero, nombre: '', filas: 4, cols: 5, bf: 0, bc: 0, cells: {} };
  }

  function cargar() {
    pisos = PISOS_DB.map(function (p) {
      const els = (p.elementos || []).map(e => ({ f: +e.fila_elemento, c: +e.columna_elemento, t: +e.id_tipo_elemento, d: e.dato_elemento }));
      // Las coordenadas guardadas pueden empezar en 0 o en 1; se conserva esa base al guardar
      const minF = els.length ? Math.min.apply(null, els.map(e => e.f)) : 0;
      const minC = els.length ? Math.min.apply(null, els.map(e => e.c)) : 0;
      const bf = minF >= 1 ? 1 : 0, bc = minC >= 1 ? 1 : 0;
      let filas = +p.filas_piso || 1, cols = +p.columnas_piso || 1;
      const cells = {};
      els.forEach(function (e) {
        const f = e.f - bf, c = e.c - bc;
        filas = Math.max(filas, f + 1);
        cols  = Math.max(cols, c + 1);
        cells[f + ':' + c] = { t: e.t, d: e.d };
      });
      return { numero: +p.numero_piso, nombre: p.nombre_piso || '', filas: filas, cols: cols, bf: bf, bc: bc, cells: cells };
    });
    if (!pisos.length) pisos = [nuevoPiso(1)];
  }

  /* ---------- Utilidades ---------- */
  function esAsiento(cell) { return cell && tipoPorId[cell.t] && tipoPorId[cell.t].es_asiento; }

  function etiquetasAsiento(excluirKey, excluirPiso) {
    const set = new Set();
    pisos.forEach(function (p, i) {
      Object.keys(p.cells).forEach(function (k) {
        if (i === excluirPiso && k === excluirKey) return;
        if (esAsiento(p.cells[k])) set.add(String(p.cells[k].d).toLowerCase());
      });
    });
    return set;
  }

  function siguienteNumeroLibre() {
    const usados = etiquetasAsiento(null, -1);
    let n = 1;
    while (usados.has(String(n))) n++;
    return String(n);
  }

  function totales() {
    let a = 0, s = 0;
    pisos.forEach(p => Object.keys(p.cells).forEach(k => esAsiento(p.cells[k]) ? a++ : s++));
    $('lblTotalAsientos').textContent = a;
    $('lblTotalEspeciales').textContent = s;
  }

  /* ---------- Render ---------- */
  function renderTools() {
    const cont = $('listaTools');
    cont.innerHTML = '';
    TIPOS.forEach(function (t) {
      const st = estiloTipo(t), col = swColors[st.k].split('|');
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'cfg-tool';
      b.dataset.tipo = t.id;
      const sw = document.createElement('span');
      sw.className = 'sw';
      sw.style.cssText = 'background:' + col[0] + ';border-color:' + col[1] + ';color:' + col[2];
      sw.innerHTML = '<span class="material-symbols-rounded">' + st.icon + '</span>';
      const tx = document.createElement('span');
      tx.textContent = t.nombre;
      b.appendChild(sw); b.appendChild(tx);
      cont.appendChild(b);
    });
    marcarTool();
  }

  function marcarTool() {
    document.querySelectorAll('.cfg-tool[data-tipo], .cfg-tool[data-tool]').forEach(function (b) {
      let on = false;
      if (tool) {
        if (tool.kind === 'tipo') on = b.dataset.tipo && +b.dataset.tipo === tool.id;
        else on = b.dataset.tool === tool.kind;
      }
      b.classList.toggle('active', !!on);
    });
  }

  function renderTabs() {
    const cont = $('tabsPisos');
    cont.innerHTML = '';
    pisos.forEach(function (p, i) {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'cfg-tab' + (i === activo ? ' active' : '');
      b.textContent = p.nombre ? p.nombre : 'Piso ' + p.numero;
      b.addEventListener('click', function () { activo = i; render(); });
      cont.appendChild(b);
    });
    $('btnAddPiso').disabled = pisos.length >= MAX_PISOS;
    $('btnQuitarPiso').disabled = pisos.length <= 1;
  }

  function renderGrilla() {
    const p = pisos[activo];
    const g = $('grilla');
    g.innerHTML = '';
    g.style.gridTemplateColumns = 'repeat(' + p.cols + ', var(--cell))';

    for (let f = 0; f < p.filas; f++) {
      for (let c = 0; c < p.cols; c++) {
        const key = f + ':' + c, cell = p.cells[key];
        const d = document.createElement('button');
        d.type = 'button';
        d.dataset.k = key;
        d.className = 'cfg-cell';
        if (cell && tipoPorId[cell.t]) {
          const t = tipoPorId[cell.t], st = estiloTipo(t);
          d.classList.add('k' + st.k);
          if (t.es_asiento) {
            d.classList.add('seat');
            d.innerHTML = '<span class="material-symbols-rounded">' + st.icon + '</span><span class="lbl"></span>';
          } else {
            d.innerHTML = '<span class="material-symbols-rounded">' + st.icon + '</span><span class="lbl"></span>';
          }
          d.querySelector('.lbl').textContent = cell.d;
          d.title = t.nombre + ' · ' + cell.d;
        } else {
          d.title = 'Fila ' + (f + 1) + ', columna ' + (c + 1) + ' (pasillo)';
        }
        g.appendChild(d);
      }
    }

    let a = 0, s = 0;
    Object.keys(p.cells).forEach(k => esAsiento(p.cells[k]) ? a++ : s++);
    $('lblResumenPiso').textContent = p.filas + ' filas × ' + p.cols + ' columnas · ' + a + ' asiento(s) · ' + s + ' elemento(s) especial(es) en este piso';
  }

  function renderControles() {
    const p = pisos[activo];
    $('pisoNombre').value = p.nombre;
    $('pisoFilas').value = p.filas;
    $('pisoCols').value = p.cols;
  }

  function render() {
    renderTabs();
    renderControles();
    renderGrilla();
    totales();
  }

  /* ---------- Acciones sobre celdas ---------- */
  function aplicar(key) {
    const p = pisos[activo];
    if (!tool) {
      Swal.fire({ icon: 'info', title: 'Elija una herramienta', text: 'Seleccione un elemento de la lista para colocarlo en la grilla.', timer: 1800, showConfirmButton: false });
      return false;
    }

    if (tool.kind === 'erase') {
      if (!p.cells[key]) return false;
      delete p.cells[key];
      return true;
    }

    if (tool.kind === 'edit') {
      const cell = p.cells[key];
      if (!cell) return false;
      editarEtiqueta(key, cell);
      return false;
    }

    // Colocar un tipo
    const t = tipoPorId[tool.id];
    if (!t) return false;
    const actual = p.cells[key];
    if (actual && actual.t === t.id) return false;

    if (t.es_asiento) {
      // Conserva el número si la celda ya era un asiento; si no, toma el siguiente libre
      p.cells[key] = { t: t.id, d: (actual && esAsiento(actual)) ? actual.d : siguienteNumeroLibre() };
    } else {
      p.cells[key] = { t: t.id, d: t.nombre.substring(0, 50) };
    }
    return true;
  }

  function editarEtiqueta(key, cell) {
    const asiento = esAsiento(cell);
    Swal.fire({
      title: asiento ? 'Número de asiento' : 'Etiqueta del elemento',
      input: 'text',
      inputValue: cell.d,
      inputAttributes: { maxlength: 50 },
      showCancelButton: true,
      confirmButtonText: 'Guardar',
      cancelButtonText: 'Cancelar',
      inputValidator: function (v) {
        v = (v || '').trim();
        if (!v) return 'La etiqueta no puede estar vacía.';
        if (asiento && etiquetasAsiento(key, activo).has(v.toLowerCase())) return 'Ese número de asiento ya existe.';
        return null;
      }
    }).then(function (r) {
      if (!r.isConfirmed) return;
      cell.d = r.value.trim();
      render();
    });
  }

  /* ---------- Eventos de la grilla (clic y arrastre con mouse) ---------- */
  let pintando = false, ultimaKey = null;

  function celdaDe(el) { return el && el.closest ? el.closest('.cfg-cell') : null; }

  $('grilla').addEventListener('pointerdown', function (e) {
    const c = celdaDe(e.target);
    if (!c) return;
    if (e.pointerType === 'mouse') { pintando = true; ultimaKey = c.dataset.k; }
    if (aplicar(c.dataset.k)) { renderGrilla(); totales(); }
  });

  $('grilla').addEventListener('pointermove', function (e) {
    if (!pintando || e.pointerType !== 'mouse' || !tool || tool.kind === 'edit') return;
    const c = celdaDe(document.elementFromPoint(e.clientX, e.clientY));
    if (!c || c.dataset.k === ultimaKey) return;
    ultimaKey = c.dataset.k;
    if (aplicar(c.dataset.k)) { renderGrilla(); totales(); }
  });

  document.addEventListener('pointerup', function () { pintando = false; ultimaKey = null; });

  /* ---------- Herramientas ---------- */
  document.querySelector('.col-lg-3').addEventListener('click', function (e) {
    const b = e.target.closest('.cfg-tool');
    if (!b) return;
    if (b.dataset.tipo) tool = { kind: 'tipo', id: +b.dataset.tipo };
    else if (b.dataset.tool) tool = { kind: b.dataset.tool };
    else return;
    marcarTool();
  });

  $('btnRenumerar').addEventListener('click', function () {
    let n = 1;
    pisos.forEach(function (p) {
      for (let f = 0; f < p.filas; f++) {
        for (let c = 0; c < p.cols; c++) {
          const cell = p.cells[f + ':' + c];
          if (esAsiento(cell)) cell.d = String(n++);
        }
      }
    });
    render();
    Swal.fire({ icon: 'success', title: 'Asientos renumerados', text: 'Del 1 al ' + (n - 1) + ', por filas y de piso en piso.', timer: 1800, showConfirmButton: false });
  });

  $('btnLimpiarPiso').addEventListener('click', function () {
    const p = pisos[activo];
    if (!Object.keys(p.cells).length) return;
    Swal.fire({ title: '¿Vaciar este piso?', text: 'Se quitarán todos sus elementos.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, vaciar', cancelButtonText: 'Cancelar', confirmButtonColor: '#f5365c' })
      .then(function (r) { if (r.isConfirmed) { p.cells = {}; render(); } });
  });

  /* ---------- Pisos y dimensiones ---------- */
  $('btnAddPiso').addEventListener('click', function () {
    if (pisos.length >= MAX_PISOS) return;
    let n = 1;
    while (pisos.some(p => p.numero === n)) n++;
    pisos.push(nuevoPiso(n));
    pisos.sort((a, b) => a.numero - b.numero);
    activo = pisos.findIndex(p => p.numero === n);
    render();
  });

  $('btnQuitarPiso').addEventListener('click', function () {
    if (pisos.length <= 1) return;
    const p = pisos[activo];
    Swal.fire({ title: '¿Quitar el piso ' + p.numero + '?', text: 'Se perderán sus elementos al guardar el plano.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, quitar', cancelButtonText: 'Cancelar', confirmButtonColor: '#f5365c' })
      .then(function (r) {
        if (!r.isConfirmed) return;
        pisos.splice(activo, 1);
        activo = Math.max(0, activo - 1);
        render();
      });
  });

  $('pisoNombre').addEventListener('input', function () {
    pisos[activo].nombre = this.value;
    renderTabs();
  });

  function cambiarDimension(campo, valor, max) {
    const p = pisos[activo];
    valor = Math.max(1, Math.min(max, parseInt(valor) || 1));
    const nuevoF = campo === 'filas' ? valor : p.filas;
    const nuevoC = campo === 'cols'  ? valor : p.cols;

    const perdidas = Object.keys(p.cells).filter(function (k) {
      const x = k.split(':');
      return +x[0] >= nuevoF || +x[1] >= nuevoC;
    });

    function aplicarCambio() {
      perdidas.forEach(k => delete p.cells[k]);
      p.filas = nuevoF; p.cols = nuevoC;
      render();
    }

    if (!perdidas.length) { aplicarCambio(); return; }
    Swal.fire({ title: 'Se perderán ' + perdidas.length + ' elemento(s)', text: 'Al reducir la grilla se quitan los elementos que quedan fuera.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Reducir', cancelButtonText: 'Cancelar' })
      .then(function (r) { if (r.isConfirmed) aplicarCambio(); else renderControles(); });
  }

  $('pisoFilas').addEventListener('change', function () { cambiarDimension('filas', this.value, MAX_FILAS); });
  $('pisoCols').addEventListener('change',  function () { cambiarDimension('cols',  this.value, MAX_COLS); });

  /* ---------- Guardar ---------- */
  $('btnGuardarPlano').addEventListener('click', function () {
    const btn = this;

    if (!document.querySelector('#listaTools .cfg-tool')) {
      Swal.fire('Sin elementos', 'No hay tipos de elemento registrados.', 'warning');
      return;
    }

    let asientos = 0;
    pisos.forEach(p => Object.keys(p.cells).forEach(k => { if (esAsiento(p.cells[k])) asientos++; }));
    if (asientos < 1) {
      Swal.fire('Sin asientos', 'El modelo debe tener al menos un asiento.', 'warning');
      return;
    }

    const config = pisos.map(function (p) {
      return {
        numero: p.numero,
        nombre_piso: p.nombre.trim(),
        filas: p.filas,
        columnas: p.cols,
        base_fila: p.bf,
        base_columna: p.bc,
        elementos: Object.keys(p.cells).map(function (k) {
          const x = k.split(':');
          return { fila: +x[0], columna: +x[1], id_tipo: p.cells[k].t, dato: String(p.cells[k].d).trim() };
        })
      };
    });

    const fd = new FormData();
    fd.append('id_modelo', ID_MODELO);
    fd.append('config_json', JSON.stringify(config));

    btn.disabled = true;
    fetch(baseUrl + '/modelos/guardarConfiguracion', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en el listado (Flash)
          window.location.href = baseUrl + '/modelos';
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar el plano.', 'error');
          btn.disabled = false;
        }
      })
      .catch(() => {
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
        btn.disabled = false;
      });
  });

  /* ---------- Inicio ---------- */
  cargar();
  renderTools();
  // Herramienta por defecto: el primer tipo "asiento"
  const primerAsiento = TIPOS.find(t => t.es_asiento) || TIPOS[0];
  if (primerAsiento) tool = { kind: 'tipo', id: primerAsiento.id };
  marcarTool();
  render();
})();
</script>