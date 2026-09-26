<div class="container-fluid py-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-lg-11 mx-auto px-2 px-md-3">

      <div class="card border-0 shadow-sm border-radius-xl">

        <!-- HEADER STEPPER -->
        <div class="card-header bg-white p-3">
          <div class="row align-items-center g-3">
            <div class="col-12 text-center text-md-start">
              <h5 class="font-weight-bolder text-dark mb-0">Venta de Pasaje</h5>
              <p class="text-xs text-secondary mb-0">Seleccione el chofer/vehículo en turno (o deje la venta en espera de asignación), registre al comprador y emita el boleto</p>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center text-center px-1 px-md-4 mt-4 py-2" id="contenedor-pasos">
            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-1">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gradient-success text-white mb-1 shadow-none" onclick="irAlPasoDirecto(1)">
                <i class="material-symbols-rounded text-sm">departure_board</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-dark text-truncate">1. Turno y Comprador</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3" id="line-step-2"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-2">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPasoDirecto(2)">
                <i class="material-symbols-rounded text-sm">event_seat</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-secondary text-truncate">2. Asientos / Cupo</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3" id="line-step-3"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-3">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPasoDirecto(3)">
                <i class="material-symbols-rounded text-sm">print</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-secondary text-truncate">3. Pago y Emisión</span>
            </div>
          </div>

          <hr class="horizontal dark my-0 opacity-2">
        </div>

        <!-- CUERPO DEL WIZARD -->
        <div class="card-body p-3 p-md-4">
          <form id="formVentaPasaje" onsubmit="return false;">

            <!-- PASO 1: TURNO/CHOFER Y COMPRADOR -->
            <div class="wizard-step" id="step-1">
              <div class="row g-4">

                <!-- Columna Izquierda: Turno en curso -->
                <div class="col-12 col-lg-6">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <div class="d-flex align-items-center mb-3">
                      <span class="material-symbols-rounded text-success me-2">directions_bus</span>
                      <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Chofer / Vehículo en Turno</h6>
                    </div>

                    <div class="input-group input-group-outline my-2 is-filled">
                      <label class="form-label">Turno Disponible</label>
                      <select class="form-control" id="selectTurno" onchange="onCambioTurno()">
                        <option value="">-- Sin turno asignado (venta en espera) --</option>
                        <?php if (!empty($turnos_disponibles)): ?>
                          <?php foreach ($turnos_disponibles as $t): ?>
                            <?php
                              $capacidad = intval($t['total_asientos_modelo'] ?? 0);
                              $ocupados  = intval($t['asientos_ocupados'] ?? 0);
                              $textoOpt  = $t['ciudad_destino'] . ' — Móvil ' . $t['numero_interno_vehiculo']
                                         . ' (' . $t['nombre_modelo'] . ') — ' . $t['nombre_chofer']
                                         . ' — Bs. ' . number_format($t['precio_pasaje_turno'], 2)
                                         . ' — ' . $ocupados . '/' . $capacidad . ' ocupados';
                            ?>
                            <option value="<?= $t['id_turno'] ?>"
                                    data-precio="<?= $t['precio_pasaje_turno'] ?>"
                                    data-destino="<?= htmlspecialchars($t['ciudad_destino']) ?>"
                                    data-vehiculo="Móvil <?= htmlspecialchars($t['numero_interno_vehiculo']) ?> (<?= htmlspecialchars($t['nombre_modelo']) ?>)"
                                    data-chofer="<?= htmlspecialchars($t['nombre_chofer']) ?>"
                                    data-capacidad="<?= $capacidad ?>"
                                    data-ocupados="<?= $ocupados ?>"
                                    <?= $ocupados >= $capacidad && $capacidad > 0 ? 'disabled' : '' ?>>
                              <?= htmlspecialchars($textoOpt) ?><?= ($ocupados >= $capacidad && $capacidad > 0) ? ' (Cupo Lleno)' : '' ?>
                            </option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                    </div>

                    <?php if (empty($turnos_disponibles)): ?>
                      <p class="text-xxs text-warning font-weight-bold mt-2 mb-0">
                        <i class="material-symbols-rounded text-xs align-middle">info</i>
                        No hay ningún turno activo en esta sucursal. Puede continuar y la venta quedará en espera de asignación.
                      </p>
                    <?php endif; ?>

                    <!-- Info del turno seleccionado -->
                    <div id="infoTurnoSeleccionado" class="d-none mt-3 p-2 bg-gray-100 border-radius-md">
                      <div class="d-flex justify-content-between">
                        <span class="text-xxs text-secondary font-weight-bold">Destino:</span>
                        <span class="text-xxs font-weight-bold text-dark" id="lblInfoDestino">-</span>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-xxs text-secondary font-weight-bold">Vehículo:</span>
                        <span class="text-xxs font-weight-bold text-dark" id="lblInfoVehiculo">-</span>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-xxs text-secondary font-weight-bold">Chofer:</span>
                        <span class="text-xxs font-weight-bold text-dark" id="lblInfoChofer">-</span>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-xxs text-secondary font-weight-bold">Precio por asiento:</span>
                        <span class="text-xxs font-weight-bold text-success" id="lblInfoPrecio">Bs. 0.00</span>
                      </div>
                    </div>

                    <!-- Panel "en espera" (visible cuando NO hay turno seleccionado) -->
                    <div id="panelEnEspera" class="mt-3 p-3 border border-radius-md bg-light">
                      <p class="text-xxs text-secondary mb-2">
                        <i class="material-symbols-rounded text-xs align-middle text-warning">schedule</i>
                        Sin turno, el pasaje se registra <strong>en espera</strong>: quedará pendiente hasta que se le asigne un chofer y vehículo desde Despachos.
                      </p>
                      <div class="row g-2">
                        <div class="col-6">
                          <div class="input-group input-group-outline my-2">
                            <label class="form-label">Cantidad de Pasajes *</label>
                            <input type="number" min="1" step="1" class="form-control" id="inputCantidadPasajes" value="1" oninput="recalcularTotalEnEspera()">
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="input-group input-group-outline my-2">
                            <label class="form-label">Precio Unitario (Bs.) *</label>
                            <input type="number" min="0" step="0.5" class="form-control" id="inputPrecioManual" value="0.00" oninput="recalcularTotalEnEspera()">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Columna Derecha: Datos del Comprador -->
                <div class="col-12 col-lg-6">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <div class="d-flex align-items-center mb-3">
                      <span class="material-symbols-rounded text-success me-2">person</span>
                      <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Datos del Comprador / Pasajero</h6>
                    </div>

                    <div class="row g-2">
                      <div class="col-12">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Nº Carnet / C.I. *</label>
                          <input type="text" class="form-control" id="comprador_ci" onblur="buscarComprador()" required>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Nombres *</label>
                          <input type="text" class="form-control" id="comprador_nombres" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Apellido Paterno *</label>
                          <input type="text" class="form-control" id="comprador_paterno" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Apellido Materno</label>
                          <input type="text" class="form-control" id="comprador_materno">
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Celular de Contacto *</label>
                          <input type="text" class="form-control" id="comprador_celular" required>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Dirección / Ref.</label>
                          <input type="text" class="form-control" id="comprador_direccion">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- PASO 2: ASIENTOS DEL TURNO O RESUMEN EN ESPERA -->
            <div class="wizard-step d-none" id="step-2">

              <!-- Panel de asientos: visible si hay turno seleccionado -->
              <div id="panelAsientos" class="d-none">
                <div class="d-flex align-items-center mb-2">
                  <span class="material-symbols-rounded text-success me-2">event_seat</span>
                  <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Estado y Selección de Asientos del Vehículo</h6>
                </div>
                <p class="text-xxs text-secondary mb-3">Los asientos "Ocupados" ya fueron vendidos en este turno y no se pueden seleccionar.</p>

                <div class="p-3 border border-radius-md bg-white">
                  <div class="table-responsive p-0" style="max-height: 420px; overflow-y: auto;">
                    <table class="table align-items-center mb-0 w-100">
                      <thead>
                        <tr>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Sel.</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nº Asiento</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pasajero</th>
                        </tr>
                      </thead>
                      <tbody id="contenedorAsientos">
                        <tr><td colspan="4" class="text-center text-xs text-secondary py-3">Cargando asientos...</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 bg-gray-100 border-radius-lg mt-3">
                  <span class="text-xs font-weight-bold text-dark" id="lblCantAsientos">0 asiento(s)</span>
                  <h5 class="text-success mb-0 font-weight-bolder" id="lblTotalPagarAsientos">Bs. 0.00</h5>
                </div>
              </div>

              <!-- Panel resumen: visible si la venta quedará en espera -->
              <div id="panelResumenEspera" class="d-none">
                <div class="d-flex align-items-center mb-2">
                  <span class="material-symbols-rounded text-warning me-2">schedule</span>
                  <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Venta en Espera de Asignación</h6>
                </div>
                <div class="p-4 border border-radius-md bg-white text-center">
                  <p class="text-xs text-secondary mb-3">No se seleccionó un turno. Este pasaje quedará registrado como <strong>Pendiente</strong> y podrá asignarse a un chofer/vehículo más adelante desde Despachos.</p>
                  <h4 class="font-weight-bolder text-dark mb-1" id="lblCantidadEsperaResumen">1 pasaje(s)</h4>
                  <h5 class="text-success font-weight-bolder mb-0" id="lblTotalEsperaResumen">Bs. 0.00</h5>
                </div>
              </div>
            </div>

            <!-- PASO 3: PAGO Y EMISIÓN -->
            <div class="wizard-step d-none" id="step-3">
              <div class="row g-3">

                <!-- Columna Izquierda: Método de pago -->
                <div class="col-12 col-lg-5">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3">Método de Pago</h6>

                    <div class="custom-nav-wrapper">
                      <ul class="custom-nav-pills d-flex flex-wrap gap-1" role="tablist" id="pillsMetodosPago">
                        <?php if (!empty($metodos_pago)): ?>
                          <?php foreach ($metodos_pago as $index => $metodo): ?>
                            <li class="nav-item flex-fill text-center">
                              <a class="nav-link text-xs py-2 px-2 <?= $index === 0 ? 'active' : '' ?>"
                                 id="tab-metodo-<?= $metodo['id_metodo_pago'] ?>"
                                 data-bs-toggle="tab"
                                 href="#metodo-<?= $metodo['id_metodo_pago'] ?>"
                                 role="tab"
                                 onclick="actualizarMetodoPago('<?= $metodo['id_metodo_pago'] ?>')">
                                <?= htmlspecialchars($metodo['nombre_metodo_pago']) ?>
                              </a>
                            </li>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <li class="nav-item flex-fill text-center">
                            <a class="nav-link text-xs py-2 px-2 active" onclick="actualizarMetodoPago('1')">Efectivo</a>
                          </li>
                        <?php endif; ?>
                      </ul>
                      <div class="moving-tab"></div>
                    </div>
                    <input type="hidden" id="selectMetodoPago" value="<?= !empty($metodos_pago) ? $metodos_pago[0]['id_metodo_pago'] : 1 ?>">

                    <div class="p-3 bg-gray-100 border-radius-lg mt-4">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-xs text-secondary">Estado de la Venta:</span>
                        <span class="badge bg-gradient-success text-xxs" id="badgeEstadoVenta">Asignado</span>
                      </div>
                      <div class="d-flex justify-content-between align-items-center">
                        <span class="text-xs font-weight-bold text-dark">Total a Cobrar:</span>
                        <h4 class="text-success mb-0 font-weight-bolder" id="lblTotalFinal">Bs. 0.00</h4>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Columna Derecha: Previsualización del Boleto -->
                <div class="col-12 col-lg-7">
                  <div class="card border border-radius-lg shadow-none bg-white p-3 position-relative overflow-hidden h-100">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
                      <div class="d-flex align-items-center">
                        <span class="material-symbols-rounded text-success fs-2 me-2">confirmation_number</span>
                        <div>
                          <h5 class="font-weight-bolder text-dark mb-0">TransExpress</h5>
                          <p class="text-xxs text-secondary mb-0">Previsualización del Boleto</p>
                        </div>
                      </div>
                      <span class="badge bg-gradient-success text-sm px-3 py-2">-- PENDIENTE DE EMISIÓN --</span>
                    </div>

                    <div class="p-2 bg-gray-100 border-radius-md mb-2">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Ruta / Turno</span>
                      <p class="text-xs font-weight-bold text-dark mb-0" id="resRuta">Sin turno asignado (en espera)</p>
                    </div>

                    <div class="p-2 border border-radius-md mb-2">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Comprador</span>
                      <p class="text-xs font-weight-bold text-dark mb-0" id="resComprador">-</p>
                      <p class="text-xxs text-secondary mb-0">CI: <span id="resCi">-</span> | Cel: <span id="resCelular">-</span></p>
                    </div>

                    <div class="p-2 border border-radius-md">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Detalle de Cobro</span>
                      <p class="text-xs font-weight-bold text-dark mb-0" id="resAsientos">1 pasaje en espera</p>
                      <p class="text-xxs text-success font-weight-bold mb-0">Total: <span id="resTotal">Bs. 0.00</span></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <!-- FOOTER DE NAVEGACIÓN -->
        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, "/") ?>/pasajes" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold w-100 w-sm-auto" id="btnCancel">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>

          <button class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold d-none w-100 w-sm-auto" id="btnPrev" onclick="cambiarPaso(-1)">
            <i class="material-symbols-rounded me-1 text-sm align-middle">arrow_back</i> Anterior
          </button>

          <div class="ms-auto d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <button class="btn btn-sm bg-gradient-success mb-0 border-radius-md px-4 font-weight-bold w-100 w-sm-auto" id="btnNext" onclick="cambiarPaso(1)">
              Siguiente <i class="material-symbols-rounded ms-1 text-sm align-middle">arrow_forward</i>
            </button>

            <button class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold d-none w-100 w-sm-auto" id="btnSave">
              <i class="material-symbols-rounded me-1 text-sm align-middle">print</i> Emitir e Imprimir Boleto(s)
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- SCRIPT PESTAÑAS ANIMADAS (mismo patrón que encomiendas/new.php) -->
<script>
function initCustomNavPills() {
  var wrappers = document.querySelectorAll('.custom-nav-wrapper');
  wrappers.forEach(function (wrapper) {
    var navPills = wrapper.querySelector('.custom-nav-pills');
    if (!navPills) return;

    var movingTab = wrapper.querySelector('.moving-tab');
    if (!movingTab) {
      movingTab = document.createElement('div');
      movingTab.className = 'moving-tab';
      wrapper.appendChild(movingTab);
    }

    function updateTabPosition(activeLink) {
      if (!activeLink) return;
      var navItem = activeLink.closest('.nav-item');
      if (!navItem) return;

      var leftOffset = navItem.offsetLeft + 4;
      var topOffset = navItem.offsetTop + 4;
      var tabWidth = navItem.offsetWidth;
      var tabHeight = navItem.offsetHeight;

      movingTab.style.transform = 'translate3d(' + (leftOffset - 4) + 'px, ' + (topOffset - 4) + 'px, 0px)';
      movingTab.style.width = tabWidth + 'px';
      movingTab.style.height = tabHeight + 'px';
    }

    var currentActive = navPills.querySelector('.nav-link.active') || navPills.querySelector('.nav-link');
    if (currentActive) updateTabPosition(currentActive);

    var tabLinks = navPills.querySelectorAll('.nav-link');
    tabLinks.forEach(function (tab) {
      tab.addEventListener('click', function (e) {
        tabLinks.forEach(function (l) { l.classList.remove('active'); });
        e.currentTarget.classList.add('active');
        updateTabPosition(e.currentTarget);
      });
    });
  });
}
</script>

<!-- SCRIPT DE LÓGICA DEL WIZARD -->
<script>
let pasoActual = 1;
let turnoSeleccionado = null; // { id, precio, id_modelo, destino, vehiculo, chofer, capacidad, ocupados }
const baseUrl = '<?php echo rtrim(URL, "/"); ?>';

function redireccionarAlListado() {
  window.location.href = baseUrl + '/pasajes';
}

// --- Búsqueda de comprador por CI (reutiliza el endpoint ya usado en Encomiendas) ---
function buscarComprador() {
  const ci = document.getElementById('comprador_ci').value.trim();
  if (!ci) return;

  fetch(`${baseUrl}/personas/buscarPorCiJson?ci=${encodeURIComponent(ci)}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.persona) {
        document.getElementById('comprador_nombres').value = data.persona.nombres_persona || data.persona.nombre_persona || '';
        document.getElementById('comprador_paterno').value = data.persona.paterno_persona || data.persona.apellido_paterno_persona || '';
        document.getElementById('comprador_materno').value = data.persona.materno_persona || data.persona.apellido_materno_persona || '';
        document.getElementById('comprador_celular').value = data.persona.celular_persona || data.persona.telefono_persona || '';
      }
    })
    .catch(() => {});
}

// --- Selección de turno ---
function onCambioTurno() {
  const sel = document.getElementById('selectTurno');
  const infoTurno = document.getElementById('infoTurnoSeleccionado');
  const panelEnEspera = document.getElementById('panelEnEspera');

  if (!sel.value) {
    turnoSeleccionado = null;
    infoTurno.classList.add('d-none');
    panelEnEspera.classList.remove('d-none');
    return;
  }

  const opt = sel.options[sel.selectedIndex];
  turnoSeleccionado = {
    id: sel.value,
    precio: parseFloat(opt.getAttribute('data-precio')) || 0,
    destino: opt.getAttribute('data-destino'),
    vehiculo: opt.getAttribute('data-vehiculo'),
    chofer: opt.getAttribute('data-chofer'),
    capacidad: parseInt(opt.getAttribute('data-capacidad')) || 0,
    ocupados: parseInt(opt.getAttribute('data-ocupados')) || 0
  };

  document.getElementById('lblInfoDestino').textContent = turnoSeleccionado.destino;
  document.getElementById('lblInfoVehiculo').textContent = turnoSeleccionado.vehiculo;
  document.getElementById('lblInfoChofer').textContent = turnoSeleccionado.chofer;
  document.getElementById('lblInfoPrecio').textContent = 'Bs. ' + turnoSeleccionado.precio.toFixed(2);

  infoTurno.classList.remove('d-none');
  panelEnEspera.classList.add('d-none');
}

function recalcularTotalEnEspera() {
  const cant = Math.max(1, parseInt(document.getElementById('inputCantidadPasajes').value) || 1);
  const precio = parseFloat(document.getElementById('inputPrecioManual').value) || 0;
  document.getElementById('lblCantidadEsperaResumen').textContent = cant + ' pasaje(s)';
  document.getElementById('lblTotalEsperaResumen').textContent = 'Bs. ' + (cant * precio).toFixed(2);
}

// --- Carga del mapa de asientos (paso 2, con turno seleccionado) ---
function cargarAsientosTurno() {
  const cont = document.getElementById('contenedorAsientos');
  cont.innerHTML = '<tr><td colspan="4" class="text-center text-xs text-secondary py-3">Cargando asientos...</td></tr>';

  fetch(`${baseUrl}/pasajes/obtenerConfiguracionTurno?id_turno=${turnoSeleccionado.id}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        cont.innerHTML = `<tr><td colspan="4" class="text-center text-xs text-danger py-3">${res.message}</td></tr>`;
        return;
      }
      turnoSeleccionado.precio = parseFloat(res.turno.precio_pasaje_turno) || turnoSeleccionado.precio;
      document.getElementById('lblInfoPrecio').textContent = 'Bs. ' + turnoSeleccionado.precio.toFixed(2);
      renderizarTablaAsientos(res.asientos || []);
    })
    .catch(() => {
      cont.innerHTML = '<tr><td colspan="4" class="text-center text-xs text-danger py-3">Error al cargar los asientos.</td></tr>';
    });
}

function renderizarTablaAsientos(asientos) {
  const cont = document.getElementById('contenedorAsientos');

  if (!asientos.length) {
    cont.innerHTML = '<tr><td colspan="4" class="text-center text-xs text-secondary py-3">Este modelo de vehículo no tiene asientos configurados.</td></tr>';
    recalcularTotalAsientos();
    return;
  }

  let html = '';
  asientos.forEach(a => {
    const ocupado = !!a.id_detalle_pasaje;
    html += `<tr>
      <td class="align-middle ps-2">
        <div class="form-check mb-0">
          <input class="form-check-input asiento-check" type="checkbox" value="${a.id_elemento}" ${ocupado ? 'disabled' : ''} onchange="recalcularTotalAsientos()">
        </div>
      </td>
      <td class="align-middle"><span class="text-xs font-weight-bold text-dark">Asiento ${a.dato_elemento}</span></td>
      <td class="align-middle">${ocupado ? '<span class="badge bg-gradient-secondary text-xxs">Ocupado</span>' : '<span class="badge bg-gradient-success text-xxs">Disponible</span>'}</td>
      <td class="align-middle"><span class="text-xs text-secondary">${a.pasajero_nombre ? a.pasajero_nombre : '-'}</span></td>
    </tr>`;
  });

  cont.innerHTML = html;
  recalcularTotalAsientos();
}

function recalcularTotalAsientos() {
  const n = document.querySelectorAll('.asiento-check:checked').length;
  const precio = turnoSeleccionado ? turnoSeleccionado.precio : 0;
  document.getElementById('lblCantAsientos').textContent = n + ' asiento(s)';
  document.getElementById('lblTotalPagarAsientos').textContent = 'Bs. ' + (n * precio).toFixed(2);
}

function obtenerAsientosSeleccionados() {
  return Array.from(document.querySelectorAll('.asiento-check:checked')).map(chk => chk.value);
}

function calcularTotalVenta() {
  if (turnoSeleccionado) {
    return obtenerAsientosSeleccionados().length * turnoSeleccionado.precio;
  }
  const cant = Math.max(1, parseInt(document.getElementById('inputCantidadPasajes').value) || 1);
  const precio = parseFloat(document.getElementById('inputPrecioManual').value) || 0;
  return cant * precio;
}

// --- Navegación del wizard ---
function cambiarPaso(direccion) {
  const nuevoPaso = pasoActual + direccion;
  if (nuevoPaso < 1 || nuevoPaso > 3) return;

  if (pasoActual === 1 && direccion === 1) {
    const ci = document.getElementById('comprador_ci').value.trim();
    const nombres = document.getElementById('comprador_nombres').value.trim();
    const paterno = document.getElementById('comprador_paterno').value.trim();

    if (!ci || !nombres || !paterno) {
      Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Complete el C.I., nombres y apellido paterno del comprador.' });
      return;
    }

    if (!turnoSeleccionado) {
      const precio = parseFloat(document.getElementById('inputPrecioManual').value) || 0;
      if (precio <= 0) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Indique el precio unitario para la venta en espera.' });
        return;
      }
    }
  }

  if (pasoActual === 2 && direccion === 1) {
    if (turnoSeleccionado && obtenerAsientosSeleccionados().length === 0) {
      Swal.fire({ icon: 'warning', title: 'Sin Asientos', text: 'Debe seleccionar al menos un asiento disponible.' });
      return;
    }
  }

  irAlPasoDirecto(nuevoPaso);
}

function irAlPasoDirecto(paso) {
  document.getElementById(`step-${pasoActual}`).classList.add('d-none');
  pasoActual = paso;
  document.getElementById(`step-${pasoActual}`).classList.remove('d-none');

  const btnAnt = document.querySelector(`#indicator-step-${pasoActual} button`);
  document.querySelectorAll('.step-indicator button').forEach(b => {
    b.classList.remove('bg-gradient-success', 'text-white');
    b.classList.add('bg-gray-200', 'text-secondary');
  });
  for (let i = 1; i <= pasoActual; i++) {
    const btn = document.querySelector(`#indicator-step-${i} button`);
    if (btn) {
      btn.classList.remove('bg-gray-200', 'text-secondary');
      btn.classList.add('bg-gradient-success', 'text-white');
    }
  }

  document.getElementById('btnCancel').classList.toggle('d-none', pasoActual !== 1);
  document.getElementById('btnPrev').classList.toggle('d-none', pasoActual === 1);
  document.getElementById('btnNext').classList.toggle('d-none', pasoActual === 3);
  document.getElementById('btnSave').classList.toggle('d-none', pasoActual !== 3);

  if (pasoActual === 2) {
    const panelAsientos = document.getElementById('panelAsientos');
    const panelResumenEspera = document.getElementById('panelResumenEspera');
    if (turnoSeleccionado) {
      panelAsientos.classList.remove('d-none');
      panelResumenEspera.classList.add('d-none');
      cargarAsientosTurno();
    } else {
      panelAsientos.classList.add('d-none');
      panelResumenEspera.classList.remove('d-none');
      recalcularTotalEnEspera();
    }
  }

  if (pasoActual === 3) {
    initCustomNavPills();
    previsualizarBoleto();
  }
}

function previsualizarBoleto() {
  const nombres = document.getElementById('comprador_nombres').value.trim();
  const paterno = document.getElementById('comprador_paterno').value.trim();
  const ci = document.getElementById('comprador_ci').value.trim();
  const celular = document.getElementById('comprador_celular').value.trim();

  document.getElementById('resComprador').textContent = `${nombres} ${paterno}`.trim();
  document.getElementById('resCi').textContent = ci || '-';
  document.getElementById('resCelular').textContent = celular || '-';

  const total = calcularTotalVenta();
  document.getElementById('lblTotalFinal').textContent = 'Bs. ' + total.toFixed(2);
  document.getElementById('resTotal').textContent = 'Bs. ' + total.toFixed(2);

  if (turnoSeleccionado) {
    const nAsientos = obtenerAsientosSeleccionados().length;
    document.getElementById('resRuta').textContent = `Trinidad ➔ ${turnoSeleccionado.destino} — ${turnoSeleccionado.vehiculo} — ${turnoSeleccionado.chofer}`;
    document.getElementById('resAsientos').textContent = `${nAsientos} asiento(s) reservado(s)`;
    document.getElementById('badgeEstadoVenta').textContent = 'Asignado';
    document.getElementById('badgeEstadoVenta').className = 'badge bg-gradient-success text-xxs';
  } else {
    const cant = Math.max(1, parseInt(document.getElementById('inputCantidadPasajes').value) || 1);
    document.getElementById('resRuta').textContent = 'Sin turno asignado (en espera de chofer y vehículo)';
    document.getElementById('resAsientos').textContent = `${cant} pasaje(s) en espera`;
    document.getElementById('badgeEstadoVenta').textContent = 'Pendiente';
    document.getElementById('badgeEstadoVenta').className = 'badge bg-gradient-warning text-xxs';
  }
}

function actualizarMetodoPago(idMetodo) {
  document.getElementById('selectMetodoPago').value = idMetodo;
}

document.addEventListener('DOMContentLoaded', function() {
  const btnSave = document.getElementById('btnSave');
  if (btnSave) {
    btnSave.addEventListener('click', function() {
      btnSave.disabled = true;

      const formData = new FormData();
      formData.append('comprador_ci', document.getElementById('comprador_ci').value.trim());
      formData.append('comprador_nombres', document.getElementById('comprador_nombres').value.trim());
      formData.append('comprador_paterno', document.getElementById('comprador_paterno').value.trim());
      formData.append('comprador_materno', document.getElementById('comprador_materno').value.trim());
      formData.append('comprador_celular', document.getElementById('comprador_celular').value.trim());
      formData.append('comprador_direccion', document.getElementById('comprador_direccion').value.trim());
      formData.append('metodo_cobro', document.getElementById('selectMetodoPago').value);

      if (turnoSeleccionado) {
        formData.append('id_turno', turnoSeleccionado.id);
        formData.append('asientos_json', JSON.stringify(obtenerAsientosSeleccionados()));
      } else {
        formData.append('id_turno', '');
        formData.append('cantidad_pasajes', document.getElementById('inputCantidadPasajes').value);
        formData.append('precio_manual', document.getElementById('inputPrecioManual').value);
      }

      fetch(baseUrl + '/pasajes/guardar', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(res => {
          if (res.success && res.id_pasaje) {
            // El mensaje de éxito ya quedó encolado en el servidor (Flash::set)
            // y se mostrará al llegar al listado, tras imprimir el boleto.
            imprimirBoletoPasaje(res.id_pasaje, redireccionarAlListado);
          } else {
            Swal.fire('Error al guardar', res.message || 'Error al guardar la venta de pasaje', 'error');
            btnSave.disabled = false;
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
          btnSave.disabled = false;
        });
    });
  }

  recalcularTotalEnEspera();
});
</script>