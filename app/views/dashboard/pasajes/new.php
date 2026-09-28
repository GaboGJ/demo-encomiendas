<div class="container-fluid py-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-lg-11 mx-auto px-2 px-md-3">

      <div class="card border-0 shadow-sm border-radius-xl">

        <!-- HEADER STEPPER -->
        <div class="card-header bg-white p-3">
          <div class="row align-items-center g-3">
            <div class="col-12 text-center text-md-start">
              <h5 class="font-weight-bolder text-dark mb-0">Venta de Pasaje</h5>
              <p class="text-xs text-secondary mb-0">Seleccione el turno (chofer/vehículo), registre al comprador, elija los asientos y a nombre de quién viaja cada uno</p>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center text-center px-1 px-md-4 mt-4 py-2" id="contenedor-pasos">
            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-1">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gradient-success text-white mb-1 shadow-none" onclick="irAlPaso(1)">
                <i class="material-symbols-rounded text-sm">departure_board</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-dark text-truncate">1. Turno y Comprador</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3" id="line-step-2"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-2">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPaso(2)">
                <i class="material-symbols-rounded text-sm">event_seat</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-secondary text-truncate">2. Asientos y Pasajeros</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3" id="line-step-3"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-3">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPaso(3)">
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

                    <div class="mb-2 position-relative">
                      <label class="form-label text-xs font-weight-bold text-dark text-uppercase">Turno Disponible *</label>
                      <input type="text" id="buscadorTurno" class="form-control border px-3 py-2 border-radius-md text-sm" placeholder="Escriba destino, chofer, placa o Nº de unidad..." autocomplete="off"<?= empty($turnos_disponibles) ? ' disabled' : '' ?>>
                      <input type="hidden" id="selectTurno" value="">
                      <div id="listaTurnos" class="list-group position-absolute w-100 shadow-sm border-radius-md mt-1" style="z-index: 1055; max-height: 280px; overflow-y: auto; display: none;"></div>
                    </div>

                    <?php if (empty($turnos_disponibles)): ?>
                      <p class="text-xxs text-warning font-weight-bold mt-2 mb-0">
                        <i class="material-symbols-rounded text-xs align-middle">info</i>
                        No hay ningún turno activo en esta sucursal. Registre uno en
                        <a href="<?= rtrim(URL, '/') ?>/despachos/new" class="text-success">Despachos</a>
                        antes de vender pasajes.
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
                  </div>
                </div>
<!-- Columna Derecha: Datos del Comprador -->
<div class="col-12 col-lg-6">
  <div class="p-3 border border-radius-md bg-white h-100">
    <div class="d-flex align-items-center mb-3">
      <span class="material-symbols-rounded text-success me-2">person</span>
      <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Datos del Comprador (quien paga)</h6>
    </div>

    <div class="row g-2">
      <!-- Fila 1: Carnet y Celular -->
      <div class="col-12 col-md-6">
        <div class="input-group input-group-outline my-1">
          <label class="form-label">Nº Carnet / C.I. *</label>
          <input type="text" class="form-control form-control" id="comprador_ci" onblur="buscarComprador()" required>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="input-group input-group-outline my-1">
          <label class="form-label">Celular de Contacto *</label>
          <input type="text" class="form-control form-control" id="comprador_celular" required>
        </div>
      </div>

      <!-- Fila 2: Nombres, Ap. Paterno y Ap. Materno -->
      <div class="col-12 col-md-4">
        <div class="input-group input-group-outline my-1">
          <label class="form-label">Nombres *</label>
          <input type="text" class="form-control form-control" id="comprador_nombres" required>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="input-group input-group-outline my-1">
          <label class="form-label">Apellido Paterno *</label>
          <input type="text" class="form-control form-control" id="comprador_paterno" required>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="input-group input-group-outline my-1">
          <label class="form-label">Apellido Materno</label>
          <input type="text" class="form-control form-control" id="comprador_materno">
        </div>
      </div>

      <!-- Fila 3: Dirección -->
      <div class="col-12">
        <div class="input-group input-group-outline my-1">
          <label class="form-label">Dirección / Ref.</label>
          <input type="text" class="form-control form-control" id="comprador_direccion">
        </div>
      </div>
    </div>
  </div>
</div>
                
              </div>
            </div>

<!-- PASO 2: PLANO DE ASIENTOS Y PASAJERO POR ASIENTO (LADO A LADO) -->
<div class="wizard-step d-none" id="step-2">
  <div id="panelAsientos">
    <div class="row g-3">

      <!-- COLUMNA IZQUIERDA: PLANO DEL VEHÍCULO Y SELECCIÓN DE ASIENTOS -->
      <div class="col-12 col-lg-6">
        <div class="p-3 border border-radius-md bg-white h-100 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2">
              <div class="d-flex align-items-center">
                <span class="material-symbols-rounded text-success me-2">event_seat</span>
                <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Plano del Vehículo y Selección</h6>
              </div>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="d-flex align-items-center gap-1 text-xxs text-secondary font-weight-bold"><span class="leyenda-punto leyenda-disponible"></span> Disp.</span>
                <span class="d-flex align-items-center gap-1 text-xxs text-secondary font-weight-bold"><span class="leyenda-punto leyenda-seleccionado"></span> Selec.</span>
                <span class="d-flex align-items-center gap-1 text-xxs text-secondary font-weight-bold"><span class="leyenda-punto leyenda-ocupado"></span> Ocup.</span>
              </div>
            </div>
            <p class="text-xxs text-secondary mb-2">Haga clic sobre un asiento disponible para seleccionarlo.</p>

            <!-- PLANO CONTENEDOR -->
            <div id="contenedorPlanoAsientos" class="vehicle-blueprint-horizontal mx-auto position-relative p-3 bg-white overflow-auto" style="max-width: 100%;">
              <div class="text-center text-xs text-secondary py-4" id="mensajePlanoAsientos">Cargando plano del vehículo...</div>
            </div>

            <!-- PAGINADOR DE PISO -->
            <div class="d-none align-items-center justify-content-center gap-3 mt-3" id="wrapperPisosAsientos">
              <button type="button" class="btn btn-icon-only btn-rounded btn-outline-success btn-sm mb-0" id="btnPisoAnterior" onclick="cambiarPisoPaginador(-1)">
                <i class="material-symbols-rounded text-sm">chevron_left</i>
              </button>
              <span class="text-xs font-weight-bold text-dark" id="lblPisoActual">Piso 1 de 1</span>
              <button type="button" class="btn btn-icon-only btn-rounded btn-outline-success btn-sm mb-0" id="btnPisoSiguiente" onclick="cambiarPisoPaginador(1)">
                <i class="material-symbols-rounded text-sm">chevron_right</i>
              </button>
            </div>
          </div>

          <!-- RESUMEN DE ASIENTOS Y TOTAL -->
          <div class="d-flex justify-content-between align-items-center p-3 bg-gray-100 border-radius-lg mt-3">
            <span class="text-xs font-weight-bold text-dark" id="lblCantAsientos">0 asiento(s)</span>
            <h5 class="text-success mb-0 font-weight-bolder" id="lblTotalPagarAsientos">Bs. 0.00</h5>
          </div>
        </div>
      </div>

      <!-- COLUMNA DERECHA: TABLA DE PASAJERO POR ASIENTO -->
      <div class="col-12 col-lg-6">
        <div class="p-3 border border-radius-md bg-white h-100">
          <div class="d-flex align-items-center mb-1">
            <span class="material-symbols-rounded text-success me-2">groups</span>
            <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Pasajero por Asiento</h6>
          </div>
          <p class="text-xxs text-secondary mb-3">Por defecto cada asiento queda a nombre del comprador. Use el lápiz para asignarlo a otra persona.</p>

          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0 w-100" id="tablaPasajerosAsientos">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Asiento</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pasajero</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tipo</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

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
                        <span class="badge bg-gradient-success text-xxs">Asignado</span>
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
                      <p class="text-xs font-weight-bold text-dark mb-0" id="resRuta">-</p>
                    </div>

                    <div class="p-2 border border-radius-md mb-2">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Comprador</span>
                      <p class="text-xs font-weight-bold text-dark mb-0" id="resComprador">-</p>
                      <p class="text-xxs text-secondary mb-0">CI: <span id="resCi">-</span> | Cel: <span id="resCelular">-</span></p>
                    </div>

                    <div class="p-2 border border-radius-md">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Asientos y Pasajeros</span>
                      <div class="text-xs font-weight-bold text-dark mb-1" id="resAsientos">-</div>
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

<!-- MODAL: PASAJERO DE UN ASIENTO (cuando NO es el comprador) -->
<div class="modal fade" id="modalPasajeroAsiento" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">
      <div class="modal-header bg-gradient-success text-white">
        <h5 class="modal-title text-white font-weight-bold">
          <i class="material-symbols-rounded me-1 align-middle">person_pin</i> Pasajero del Asiento <span id="lblAsientoModal"></span>
        </h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-xxs text-secondary mb-2">Indique quién viajará en este asiento. Si el C.I. ya está registrado se completan los datos automáticamente.</p>
        <input type="hidden" id="pas_id_elemento">
        <div class="row g-2">
          <div class="col-12">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Nº Carnet / C.I. *</label>
              <input type="text" class="form-control" id="pas_ci" onblur="buscarPasajeroModal()">
            </div>
          </div>
          <div class="col-12">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Nombres *</label>
              <input type="text" class="form-control" id="pas_nombres">
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Apellido Paterno *</label>
              <input type="text" class="form-control" id="pas_paterno">
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Apellido Materno</label>
              <input type="text" class="form-control" id="pas_materno">
            </div>
          </div>
          <div class="col-12">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Celular</label>
              <input type="text" class="form-control" id="pas_celular">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-gray-100">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-sm bg-gradient-success mb-0" onclick="guardarPasajeroAsiento()">Guardar Pasajero</button>
      </div>
    </div>
  </div>
</div>

<!-- ESTILOS DEL PLANO VISUAL DE ASIENTOS (mismo lenguaje visual que "Configuración de Asientos") -->
<style>
  .vehicle-blueprint-horizontal {
    border: 4px solid #344767 !important;
    border-radius: 20px 40px 40px 20px !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
    min-height: 140px;
  }
  .grid-asientos-piso {
    display: grid;
    gap: 8px;
    justify-content: center;
    margin: 12px auto;
  }
  .celda-elemento {
    width: 54px;
    height: 54px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .celda-asiento {
    background: #fff;
    border: 2px solid #2dce89;
    color: #2dce89;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    flex-direction: column;
    user-select: none;
    transition: all 0.15s ease;
  }
  .celda-asiento:hover {
    background: rgba(45, 206, 137, 0.08);
    transform: translateY(-1px);
  }
  .celda-asiento.seleccionado {
    background: #2dce89;
    border-color: #2dce89;
    color: #fff;
  }
  .celda-asiento.ocupado {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #6c757d;
    cursor: not-allowed;
  }
  .celda-asiento.ocupado:hover {
    transform: none;
  }
  .celda-especial {
    background: #f8f9fa;
    border: 1px dashed #adb5bd;
    color: #6c757d;
    font-size: 0.6rem;
    text-align: center;
    line-height: 1.1;
    padding: 2px;
  }
  .celda-pasillo {
    background: transparent;
    border: none;
  }
  .leyenda-punto {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 4px;
  }
  .leyenda-disponible { background: #fff; border: 2px solid #2dce89; }
  .leyenda-seleccionado { background: #2dce89; }
  .leyenda-ocupado { background: #e9ecef; border: 2px solid #adb5bd; }
</style>

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
let turnoSeleccionado = null; // { id, precio, destino, vehiculo, chofer, capacidad, ocupados }
const baseUrl = '<?php echo rtrim(URL, "/"); ?>';

// --- Estado del plano visual de asientos ---
let pisosPlano = [];
let elementosPlano = [];
let pisoActivoPlano = null;
let dtPasajeros = null;

// Asientos elegidos: key (id_elemento como string) -> datos del pasajero de ESE asiento.
//   usar_comprador = true  -> el asiento va a nombre del comprador (se resuelve al enviar,
//                             así si el comprador se edita después, el asiento lo sigue).
//   usar_comprador = false -> pasajero distinto: ci / nombres / paterno / materno / celular.
let asientosSeleccionados = new Map();

function redireccionarAlListado() {
  window.location.href = baseUrl + '/pasajes';
}

// --- Utilidades ---
function valorInput(id) {
  const el = document.getElementById(id);
  return el ? el.value.trim() : '';
}

// Asigna un valor a un input de Material Dashboard y sube la etiqueta flotante
// (clase is-filled) para que no se superponga con el texto.
function setValorInput(id, valor) {
  const el = document.getElementById(id);
  if (!el) return;
  el.value = valor || '';
  const grupo = el.closest('.input-group');
  if (grupo) grupo.classList.toggle('is-filled', el.value !== '');
}

function escaparHtmlAsientos(str) {
  const div = document.createElement('div');
  div.textContent = str == null ? '' : String(str);
  return div.innerHTML;
}

function datosComprador() {
  return {
    ci: valorInput('comprador_ci'),
    nombres: valorInput('comprador_nombres'),
    paterno: valorInput('comprador_paterno'),
    materno: valorInput('comprador_materno'),
    celular: valorInput('comprador_celular')
  };
}

function nombreCompleto(p) {
  return [p.nombres, p.paterno, p.materno].filter(Boolean).join(' ');
}

// Busca una persona por C.I. (mismo endpoint usado en Encomiendas) y llama a cb(persona | null)
function buscarPersonaPorCi(ci, cb) {
  if (!ci) return;
  fetch(`${baseUrl}/personas/buscarPorCiJson?ci=${encodeURIComponent(ci)}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.persona) {
        const p = data.persona;
        cb({
          nombres: p.nombres_persona || p.nombre_persona || '',
          paterno: p.paterno_persona || p.apellido_paterno_persona || '',
          materno: p.materno_persona || p.apellido_materno_persona || '',
          celular: p.celular_persona || p.telefono_persona || ''
        });
      }
    })
    .catch(() => {});
}

function buscarComprador() {
  buscarPersonaPorCi(valorInput('comprador_ci'), function (p) {
    setValorInput('comprador_nombres', p.nombres);
    setValorInput('comprador_paterno', p.paterno);
    setValorInput('comprador_materno', p.materno);
    setValorInput('comprador_celular', p.celular);
    renderizarListaPasajeros();
  });
}

function buscarPasajeroModal() {
  buscarPersonaPorCi(valorInput('pas_ci'), function (p) {
    setValorInput('pas_nombres', p.nombres);
    setValorInput('pas_paterno', p.paterno);
    setValorInput('pas_materno', p.materno);
    setValorInput('pas_celular', p.celular);
  });
}

// --- Selección de turno (buscador tipo autocompletar, igual que en despachos/new.php) ---

// Datos de TODOS los turnos disponibles, precargados desde PHP. Antes esta
// información solo vivía dentro de los atributos data-* del <select>: si el
// navegador no disparaba bien el evento "change" (o el <select> se quedaba
// sin resolver por el estilo de Material Dashboard), turnoSeleccionado nunca
// se llenaba y por eso el plano de asientos del Paso 2 no tenía de dónde
// cargar datos. Ahora el buscador arma turnoSeleccionado directamente desde
// este arreglo, sin depender de leer atributos de un elemento del DOM.
const turnosDisponiblesData = <?= json_encode(array_map(function ($t) {
    $capacidad = intval($t['total_asientos_modelo'] ?? 0);
    $ocupados  = intval($t['asientos_ocupados'] ?? 0);
    $lleno     = ($ocupados >= $capacidad && $capacidad > 0);
    $label     = $t['ciudad_destino'] . ' — Móvil ' . $t['numero_interno_vehiculo']
               . ' (' . $t['nombre_modelo'] . ') — ' . $t['nombre_chofer']
               . ' — Bs. ' . number_format($t['precio_pasaje_turno'], 2)
               . ' — ' . $ocupados . '/' . $capacidad . ' ocupados'
               . ($lleno ? ' (Cupo Lleno)' : '');
    return [
        'id'        => $t['id_turno'],
        'label'     => $label,
        'precio'    => $t['precio_pasaje_turno'],
        'destino'   => $t['ciudad_destino'],
        'vehiculo'  => 'Móvil ' . $t['numero_interno_vehiculo'] . ' (' . $t['nombre_modelo'] . ')',
        'chofer'    => $t['nombre_chofer'],
        'capacidad' => $capacidad,
        'ocupados'  => $ocupados,
        'lleno'     => $lleno,
        'buscar'    => mb_strtolower(
            $t['ciudad_destino'] . ' ' . $t['numero_interno_vehiculo'] . ' ' . $t['nombre_modelo'] . ' ' . $t['nombre_chofer'],
            'UTF-8'
        )
    ];
}, $turnos_disponibles ?? []), JSON_UNESCAPED_UNICODE) ?>;

let resultadosTurnoActuales = [];
let indiceActivoTurno = -1;

function renderListaTurnos(items) {
  const listaContainer = document.getElementById('listaTurnos');
  resultadosTurnoActuales = items;
  indiceActivoTurno = -1;

  if (!items.length) {
    listaContainer.innerHTML = '<div class="list-group-item text-xs text-secondary">Sin resultados...</div>';
    listaContainer.style.display = 'block';
    return;
  }

  listaContainer.innerHTML = items.map(function (item, i) {
    const claseLleno = item.lleno ? ' text-secondary bg-gray-100' : '';
    return `<button type="button" class="list-group-item list-group-item-action text-xs py-2${claseLleno}" data-index="${i}"${item.lleno ? ' disabled' : ''}>${escaparHtmlAsientos(item.label)}</button>`;
  }).join('');
  listaContainer.style.display = 'block';
}

function marcarActivoTurno() {
  const listaContainer = document.getElementById('listaTurnos');
  const botones = listaContainer.querySelectorAll('[data-index]');
  botones.forEach(function (b, i) { b.classList.toggle('active', i === indiceActivoTurno); });
}

function seleccionarTurnoBuscado(item) {
  if (!item || item.lleno) return;

  document.getElementById('selectTurno').value = item.id;
  document.getElementById('buscadorTurno').value = item.label;
  document.getElementById('buscadorTurno').classList.remove('is-invalid');
  document.getElementById('listaTurnos').style.display = 'none';

  // Cambiar de turno invalida cualquier selección de asientos previa.
  asientosSeleccionados.clear();
  pisosPlano = [];
  elementosPlano = [];
  pisoActivoPlano = null;
  renderizarListaPasajeros();

  turnoSeleccionado = {
    id: item.id,
    precio: parseFloat(item.precio) || 0,
    destino: item.destino,
    vehiculo: item.vehiculo,
    chofer: item.chofer,
    capacidad: item.capacidad,
    ocupados: item.ocupados
  };

  document.getElementById('lblInfoDestino').textContent = turnoSeleccionado.destino;
  document.getElementById('lblInfoVehiculo').textContent = turnoSeleccionado.vehiculo;
  document.getElementById('lblInfoChofer').textContent = turnoSeleccionado.chofer;
  document.getElementById('lblInfoPrecio').textContent = 'Bs. ' + turnoSeleccionado.precio.toFixed(2);
  document.getElementById('infoTurnoSeleccionado').classList.remove('d-none');
}

(function inicializarBuscadorTurno() {
  const inputBuscador = document.getElementById('buscadorTurno');
  const listaContainer = document.getElementById('listaTurnos');
  if (!inputBuscador || !listaContainer) return;

  inputBuscador.addEventListener('input', function () {
    document.getElementById('selectTurno').value = '';
    turnoSeleccionado = null;
    document.getElementById('infoTurnoSeleccionado').classList.add('d-none');

    const q = this.value.trim().toLowerCase();
    if (!q) {
      listaContainer.style.display = 'none';
      return;
    }

    const filtrados = turnosDisponiblesData.filter(function (t) {
      return t.buscar.includes(q);
    }).slice(0, 8);

    renderListaTurnos(filtrados);
  });

  inputBuscador.addEventListener('focus', function () {
    if (this.value.trim() && listaContainer.innerHTML) {
      listaContainer.style.display = 'block';
    }
  });

  inputBuscador.addEventListener('keydown', function (e) {
    if (listaContainer.style.display === 'none' || !resultadosTurnoActuales.length) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      indiceActivoTurno = Math.min(indiceActivoTurno + 1, resultadosTurnoActuales.length - 1);
      marcarActivoTurno();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      indiceActivoTurno = Math.max(indiceActivoTurno - 1, 0);
      marcarActivoTurno();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const elegido = indiceActivoTurno >= 0 ? resultadosTurnoActuales[indiceActivoTurno] : resultadosTurnoActuales[0];
      if (elegido) seleccionarTurnoBuscado(elegido);
    } else if (e.key === 'Escape') {
      listaContainer.style.display = 'none';
    }
  });

  listaContainer.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-index]');
    if (!btn || btn.disabled) return;
    const item = resultadosTurnoActuales[parseInt(btn.getAttribute('data-index'), 10)];
    if (item) seleccionarTurnoBuscado(item);
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('#buscadorTurno') && !e.target.closest('#listaTurnos')) {
      listaContainer.style.display = 'none';
    }
  });
})();

// --- Carga del plano visual del vehículo (paso 2) ---
function cargarAsientosTurno() {
  const contenedor = document.getElementById('contenedorPlanoAsientos');
  contenedor.innerHTML = '<div class="text-center text-xs text-secondary py-4" id="mensajePlanoAsientos">Cargando plano del vehículo...</div>';

  fetch(`${baseUrl}/pasajes/obtenerConfiguracionTurno?id_turno=${turnoSeleccionado.id}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        contenedor.innerHTML = `<div class="text-center text-xs text-danger py-4">${escaparHtmlAsientos(res.message)}</div>`;
        return;
      }
      turnoSeleccionado.precio = parseFloat(res.turno.precio_pasaje_turno) || turnoSeleccionado.precio;
      document.getElementById('lblInfoPrecio').textContent = 'Bs. ' + turnoSeleccionado.precio.toFixed(2);

      pisosPlano     = res.pisos || [];
      elementosPlano = res.elementos || [];

      normalizarOrientacionHorizontal();

      // Se descarta de la selección cualquier asiento que ya no exista o que
      // otro cajero haya vendido mientras tanto.
      const idsValidos = new Set(
        elementosPlano.filter(e => e.es_asiento && !e.ocupado).map(e => String(e.id_elemento))
      );
      Array.from(asientosSeleccionados.keys()).forEach(k => {
        if (!idsValidos.has(k)) asientosSeleccionados.delete(k);
      });

      if (!pisosPlano.length) {
        contenedor.innerHTML = '<div class="text-center text-xs text-secondary py-4">Este modelo de vehículo no tiene un plano de asientos configurado.</div>';
        recalcularTotalAsientos();
        renderizarListaPasajeros();
        return;
      }

      pisoActivoPlano = pisosPlano[0].id_piso;
      actualizarPaginadorPisos();
      renderizarPlanoPiso();
      recalcularTotalAsientos();
      renderizarListaPasajeros();
    })
    .catch(() => {
      contenedor.innerHTML = '<div class="text-center text-xs text-danger py-4">Error al cargar el plano del vehículo.</div>';
    });
}

function actualizarPaginadorPisos() {
  const wrapper = document.getElementById('wrapperPisosAsientos');
  const lbl = document.getElementById('lblPisoActual');
  const btnAnterior = document.getElementById('btnPisoAnterior');
  const btnSiguiente = document.getElementById('btnPisoSiguiente');

  if (pisosPlano.length <= 1) {
    wrapper.classList.remove('d-flex');
    wrapper.classList.add('d-none');
    return;
  }

  const indiceActual = pisosPlano.findIndex(p => p.id_piso === pisoActivoPlano);
  const piso = pisosPlano[indiceActual];
  const etiqueta = piso.nombre_piso ? piso.nombre_piso : ('Piso ' + piso.numero_piso);

  wrapper.classList.remove('d-none');
  wrapper.classList.add('d-flex');
  lbl.textContent = `${etiqueta} (${indiceActual + 1} de ${pisosPlano.length})`;

  btnAnterior.disabled = (indiceActual <= 0);
  btnSiguiente.disabled = (indiceActual >= pisosPlano.length - 1);
}

function cambiarPisoPaginador(delta) {
  const indiceActual = pisosPlano.findIndex(p => p.id_piso === pisoActivoPlano);
  const nuevoIndice = indiceActual + delta;

  if (nuevoIndice < 0 || nuevoIndice >= pisosPlano.length) return;

  pisoActivoPlano = pisosPlano[nuevoIndice].id_piso;
  actualizarPaginadorPisos();
  renderizarPlanoPiso();
}

function iconoElementoEspecial(tipo) {
  const t = (tipo || '').toLowerCase();
  if (t.indexOf('chofer') !== -1) return 'directions_car';
  if (t.indexOf('baño') !== -1 || t.indexOf('bano') !== -1) return 'wc';
  if (t.indexOf('escalera') !== -1) return 'stairs';
  if (t.indexOf('televis') !== -1) return 'tv';
  if (t.indexOf('puerta') !== -1) return 'sensor_door';
  return 'square';
}

function renderizarPlanoPiso() {
  const contenedor = document.getElementById('contenedorPlanoAsientos');
  const piso = pisosPlano.find(p => p.id_piso === pisoActivoPlano);

  if (!piso) {
    contenedor.innerHTML = '<div class="text-center text-xs text-secondary py-4">Piso no encontrado.</div>';
    return;
  }

  const elementosPiso = elementosPlano.filter(e => e.id_piso === piso.id_piso);
  let filas    = Math.max(1, parseInt(piso.filas_piso) || 1);
  let columnas = Math.max(1, parseInt(piso.columnas_piso) || 1);

  elementosPiso.forEach(el => {
    filas    = Math.max(filas, parseInt(el.fila_elemento) + 1);
    columnas = Math.max(columnas, parseInt(el.columna_elemento) + 1);
  });

  let html = `<div class="d-flex justify-content-between align-items-center mb-3 px-2 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-gradient-dark">${piso.nombre_piso ? escaparHtmlAsientos(piso.nombre_piso) : ('Piso ' + piso.numero_piso)}</span>
                  <span class="text-xs text-secondary font-weight-bold">Frente (Izquierda) ➔ Fondo (Derecha)</span>
                </div>
              </div>`;

  html += `<div class="grid-asientos-piso" style="grid-template-columns: repeat(${columnas}, 60px); grid-template-rows: repeat(${filas}, 60px);">`;

  for (let r = 0; r < filas; r++) {
    for (let c = 0; c < columnas; c++) {
      const el = elementosPiso.find(e => parseInt(e.fila_elemento) === r && parseInt(e.columna_elemento) === c);
      const estilo = `grid-column: ${c + 1}; grid-row: ${r + 1};`;

      if (!el) {
        html += `<div class="celda-elemento" style="${estilo}"></div>`;
        continue;
      }

      if (el.es_asiento) {
        const seleccionado = asientosSeleccionados.has(String(el.id_elemento));
        const clases = ['celda-elemento', 'celda-asiento'];
        if (el.ocupado) clases.push('ocupado');
        if (seleccionado && !el.ocupado) clases.push('seleccionado');

        const titulo = el.ocupado
          ? `Asiento ${el.dato_elemento} — Ocupado${el.pasajero_nombre ? ' por ' + el.pasajero_nombre : ''}`
          : `Asiento ${el.dato_elemento} — Disponible`;

        html += `<div class="${clases.join(' ')}" style="${estilo}" title="${escaparHtmlAsientos(titulo)}"
                      onclick="toggleAsiento(${el.id_elemento}, ${el.ocupado ? 'true' : 'false'})">
                    <span class="material-symbols-rounded text-sm">event_seat</span>
                    <span>${escaparHtmlAsientos(el.dato_elemento)}</span>
                  </div>`;
      } else {
        const esPasillo = (el.tipo_elemento || '').toLowerCase().indexOf('pasillo') !== -1;
        if (esPasillo) {
          html += `<div class="celda-elemento celda-pasillo" style="${estilo}" title="Pasillo"></div>`;
        } else {
          const icono = iconoElementoEspecial(el.tipo_elemento);
          html += `<div class="celda-elemento celda-especial" style="${estilo}" title="${escaparHtmlAsientos(el.tipo_elemento)}">
                      <div class="d-flex flex-column align-items-center">
                        <span class="material-symbols-rounded text-sm">${icono}</span>
                        <span>${escaparHtmlAsientos(el.dato_elemento)}</span>
                      </div>
                    </div>`;
        }
      }
    }
  }

  html += `</div>`;
  html += `<div class="mt-3 pt-2 border-top text-center">
             <span class="text-xxs text-uppercase text-secondary font-weight-bolder">=== Parte Posterior / Salida de Emergencia ===</span>
           </div>`;

  contenedor.innerHTML = html;
}

// Si el piso quedó configurado más "alto" que "ancho", se transpone para que
// el plano SIEMPRE se vea horizontal, como un vehículo real.
function normalizarOrientacionHorizontal() {
  pisosPlano.forEach(piso => {
    const filas    = parseInt(piso.filas_piso) || 1;
    const columnas = parseInt(piso.columnas_piso) || 1;

    if (filas > columnas) {
      piso.filas_piso    = columnas;
      piso.columnas_piso = filas;

      elementosPlano
        .filter(el => el.id_piso === piso.id_piso)
        .forEach(el => {
          const filaOriginal = el.fila_elemento;
          el.fila_elemento    = el.columna_elemento;
          el.columna_elemento = filaOriginal;
        });
    }
  });
}

function toggleAsiento(idElemento, ocupado) {
  if (ocupado) return;

  const key = String(idElemento);
  if (asientosSeleccionados.has(key)) {
    asientosSeleccionados.delete(key);
  } else {
    const el = elementosPlano.find(e => String(e.id_elemento) === key);
    asientosSeleccionados.set(key, {
      id_elemento: key,
      etiqueta: el ? el.dato_elemento : key,
      usar_comprador: true,
      ci: '', nombres: '', paterno: '', materno: '', celular: ''
    });
  }

  renderizarPlanoPiso();
  recalcularTotalAsientos();
  renderizarListaPasajeros();
}

function recalcularTotalAsientos() {
  const n = asientosSeleccionados.size;
  const precio = turnoSeleccionado ? turnoSeleccionado.precio : 0;
  document.getElementById('lblCantAsientos').textContent = n + ' asiento(s)';
  document.getElementById('lblTotalPagarAsientos').textContent = 'Bs. ' + (n * precio).toFixed(2);
}

// --- Pasajero por asiento (tabla con DataTables, igual patrón que tablaBultos en encomiendas/new.php) ---
function inicializarDataTablePasajeros() {
  if (dtPasajeros) return;

  if (typeof inicializarDataTable === 'function') {
    dtPasajeros = inicializarDataTable('#tablaPasajerosAsientos', {
      ordering: false,
      placeholder: 'Buscar asiento...',
      pageLength: 5,
      columns: [
        { data: 'asiento', className: 'text-xs font-weight-bold' },
        { data: 'pasajero', className: 'text-xs font-weight-bold' },
        { data: 'tipo', className: 'text-xs' },
        { data: 'acciones', className: 'text-end', orderable: false }
      ]
    });
  }
}

function renderizarListaPasajeros() {
  inicializarDataTablePasajeros();
  if (!dtPasajeros) return;

  dtPasajeros.clear();

  const comp = datosComprador();
  const dataRows = [];

  asientosSeleccionados.forEach((a, key) => {
    const p = a.usar_comprador ? comp : a;
    const nombre = nombreCompleto(p) || (a.usar_comprador ? '(complete los datos del comprador en el paso 1)' : '-');
    const ciTxt = p.ci ? ` <span class="text-xxs text-secondary">C.I. ${escaparHtmlAsientos(p.ci)}</span>` : '';
    const tipoBadge = a.usar_comprador
      ? '<span class="badge badge-sm bg-gradient-secondary">Comprador</span>'
      : '<span class="badge badge-sm bg-gradient-info">Otra persona</span>';

    dataRows.push({
      asiento: `<span class="badge bg-gradient-success">Asiento ${escaparHtmlAsientos(a.etiqueta)}</span>`,
      pasajero: `${escaparHtmlAsientos(nombre)}${ciTxt}`,
      tipo: tipoBadge,
      acciones: `<div class="d-flex align-items-center justify-content-end gap-1">
                   ${a.usar_comprador ? '' : `<button type="button" class="btn btn-link text-secondary p-1 m-0" title="Volver al comprador" onclick="restablecerPasajeroAsiento('${key}')"><i class="material-symbols-rounded text-sm">undo</i></button>`}
                   <button type="button" class="btn btn-link text-success p-1 m-0" title="Cambiar pasajero" onclick="editarPasajeroAsiento('${key}')"><i class="material-symbols-rounded text-sm">edit</i></button>
                 </div>`
    });
  });

  dtPasajeros.rows.add(dataRows).draw();
}

function editarPasajeroAsiento(key) {
  const a = asientosSeleccionados.get(key);
  if (!a) return;

  const base = a.usar_comprador ? { ci: '', nombres: '', paterno: '', materno: '', celular: '' } : a;

  document.getElementById('pas_id_elemento').value = key;
  document.getElementById('lblAsientoModal').textContent = a.etiqueta;
  setValorInput('pas_ci', base.ci);
  setValorInput('pas_nombres', base.nombres);
  setValorInput('pas_paterno', base.paterno);
  setValorInput('pas_materno', base.materno);
  setValorInput('pas_celular', base.celular);

  const modalEl = document.getElementById('modalPasajeroAsiento');
  (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
}

function guardarPasajeroAsiento() {
  const key = document.getElementById('pas_id_elemento').value;
  const a = asientosSeleccionados.get(key);
  if (!a) return;

  const ci = valorInput('pas_ci');
  const nombres = valorInput('pas_nombres');
  const paterno = valorInput('pas_paterno');

  if (!ci || !nombres || !paterno) {
    Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Complete el C.I., nombres y apellido paterno del pasajero.' });
    return;
  }

  if (ci === datosComprador().ci) {
    // Mismo C.I. que el comprador: es la misma persona.
    a.usar_comprador = true;
  } else {
    a.usar_comprador = false;
    a.ci = ci;
    a.nombres = nombres;
    a.paterno = paterno;
    a.materno = valorInput('pas_materno');
    a.celular = valorInput('pas_celular');
  }

  const modalEl = document.getElementById('modalPasajeroAsiento');
  const modalObj = bootstrap.Modal.getInstance(modalEl);
  if (modalObj) modalObj.hide();

  renderizarListaPasajeros();
}

function restablecerPasajeroAsiento(key) {
  const a = asientosSeleccionados.get(key);
  if (!a) return;
  a.usar_comprador = true;
  renderizarListaPasajeros();
}

// Lista enviada al servidor: una entrada por asiento, con su pasajero.
function payloadAsientos() {
  return Array.from(asientosSeleccionados.values()).map(a => {
    const base = { id_elemento: parseInt(a.id_elemento), usar_comprador: a.usar_comprador };
    if (a.usar_comprador) return base;
    return Object.assign(base, { ci: a.ci, nombres: a.nombres, paterno: a.paterno, materno: a.materno, celular: a.celular });
  });
}

function calcularTotalVenta() {
  return turnoSeleccionado ? asientosSeleccionados.size * turnoSeleccionado.precio : 0;
}

// --- Navegación del wizard ---
function validarPaso(paso) {
  if (paso === 1) {
    const ci = valorInput('comprador_ci');
    const nombres = valorInput('comprador_nombres');
    const paterno = valorInput('comprador_paterno');

    if (!turnoSeleccionado) {
      Swal.fire({ icon: 'warning', title: 'Seleccione un turno', text: 'Debe elegir el turno (chofer/vehículo) para el que vende el pasaje.' });
      return false;
    }
    if (!ci || !nombres || !paterno) {
      Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Complete el C.I., nombres y apellido paterno del comprador.' });
      return false;
    }
  }

  if (paso === 2) {
    if (asientosSeleccionados.size === 0) {
      Swal.fire({ icon: 'warning', title: 'Sin Asientos', text: 'Debe seleccionar al menos un asiento disponible.' });
      return false;
    }
  }
  return true;
}

function cambiarPaso(direccion) {
  const nuevoPaso = pasoActual + direccion;
  if (nuevoPaso < 1 || nuevoPaso > 3) return;

  if (direccion === 1 && !validarPaso(pasoActual)) return;

  irAlPasoDirecto(nuevoPaso);
}

// Salto desde el stepper: hacia adelante se valida cada paso intermedio.
function irAlPaso(destino) {
  if (destino <= pasoActual) {
    irAlPasoDirecto(destino);
    return;
  }
  while (pasoActual < destino) {
    if (!validarPaso(pasoActual)) return;
    irAlPasoDirecto(pasoActual + 1);
  }
}

function irAlPasoDirecto(paso) {
  document.getElementById(`step-${pasoActual}`).classList.add('d-none');
  pasoActual = paso;
  document.getElementById(`step-${pasoActual}`).classList.remove('d-none');

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

  if (pasoActual === 2 && turnoSeleccionado) {
    cargarAsientosTurno();
  }

  if (pasoActual === 3) {
    initCustomNavPills();
    previsualizarBoleto();
  }
}

function previsualizarBoleto() {
  const comp = datosComprador();

  document.getElementById('resComprador').textContent = nombreCompleto(comp);
  document.getElementById('resCi').textContent = comp.ci || '-';
  document.getElementById('resCelular').textContent = comp.celular || '-';

  const total = calcularTotalVenta();
  document.getElementById('lblTotalFinal').textContent = 'Bs. ' + total.toFixed(2);
  document.getElementById('resTotal').textContent = 'Bs. ' + total.toFixed(2);

  if (turnoSeleccionado) {
    document.getElementById('resRuta').textContent = `Trinidad ➔ ${turnoSeleccionado.destino} — ${turnoSeleccionado.vehiculo} — ${turnoSeleccionado.chofer}`;
  }

  const lista = Array.from(asientosSeleccionados.values()).map(a => {
    const p = a.usar_comprador ? comp : a;
    return `<div>Asiento ${escaparHtmlAsientos(a.etiqueta)} — ${escaparHtmlAsientos(nombreCompleto(p))} <span class="text-secondary font-weight-normal">(C.I. ${escaparHtmlAsientos(p.ci)})</span></div>`;
  }).join('');
  document.getElementById('resAsientos').innerHTML = lista || '-';
}

function actualizarMetodoPago(idMetodo) {
  document.getElementById('selectMetodoPago').value = idMetodo;
}

document.addEventListener('DOMContentLoaded', function() {
  const btnSave = document.getElementById('btnSave');
  if (btnSave) {
    btnSave.addEventListener('click', function() {
      if (!turnoSeleccionado || asientosSeleccionados.size === 0) {
        Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Seleccione un turno y al menos un asiento.' });
        return;
      }

      btnSave.disabled = true;

      const formData = new FormData();
      formData.append('comprador_ci', valorInput('comprador_ci'));
      formData.append('comprador_nombres', valorInput('comprador_nombres'));
      formData.append('comprador_paterno', valorInput('comprador_paterno'));
      formData.append('comprador_materno', valorInput('comprador_materno'));
      formData.append('comprador_celular', valorInput('comprador_celular'));
      formData.append('comprador_direccion', valorInput('comprador_direccion'));
      formData.append('metodo_cobro', document.getElementById('selectMetodoPago').value);
      formData.append('id_turno', turnoSeleccionado.id);
      formData.append('asientos_json', JSON.stringify(payloadAsientos()));

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

            // Si el error fue porque un asiento ya se vendió, se refresca el
            // plano para que el cajero vea la ocupación real.
            if (/vendido/i.test(res.message || '')) {
              cargarAsientosTurno();
            }
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
          btnSave.disabled = false;
        });
    });
  }
});
</script>