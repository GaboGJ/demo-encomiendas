<!-- CUERPO PRINCIPAL CON STEPPER -->
<div class="container-fluid py-2 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-11 mx-auto px-1 px-sm-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">
        
        <!-- HEADER CON TÍTULO Y STEPPER DE 3 PASOS -->
        <div class="card-header bg-white p-3">
          <div class="row align-items-center g-2">
            <div class="col-12 text-center text-md-start">
              <h5 class="font-weight-bolder text-dark mb-0 fs-5 fs-md-4">Recepción Asistida de Encomienda (Arribo)</h5>
              <p class="text-xs text-secondary mb-0">Registro y alta en almacén local de carga proveniente de otra sucursal o agencia</p>
            </div>
          </div>

          <!-- INDICADORES DEL STEPPER (3 PASOS) -->
          <div class="d-flex justify-content-between align-items-center text-center px-0 px-md-4 mt-3 mt-md-4 py-1 py-md-2" id="contenedor-pasos-recepcion">
            
            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-recepcion-1">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gradient-success text-white mb-1 shadow-none" onclick="irAlPasoRecepcionDirecto(1)">
                <i class="material-symbols-rounded text-sm">local_shipping</i>
              </button>
              <span class="d-none d-md-block text-xs font-weight-bold text-dark text-truncate">1. Origen y Guía</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3" id="line-recepcion-2"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-recepcion-2">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPasoRecepcionDirecto(2)">
                <i class="material-symbols-rounded text-sm">inventory_2</i>
              </button>
              <span class="d-none d-md-block text-xs font-weight-bold text-secondary text-truncate">2. Bultos y Precintos</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3" id="line-recepcion-3"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-recepcion-3">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPasoRecepcionDirecto(3)">
                <i class="material-symbols-rounded text-sm">how_to_reg</i>
              </button>
              <span class="d-none d-md-block text-xs font-weight-bold text-secondary text-truncate">3. Verificación y Alta</span>
            </div>

          </div>

          <hr class="horizontal dark my-0 opacity-2">
        </div>

        <!-- CUERPO DE FORMULARIO WIZARD -->
        <div class="card-body p-2 p-sm-3 p-md-4">
          <form id="formRecepcionEncomienda" onsubmit="return false;">

<!-- PASO 1: RUTA Y DATOS DE REMITENTE / DESTINATARIO -->
<div class="wizard-step-recepcion" id="step-recepcion-1">
  
  <!-- 1. SECCIÓN CONFIGURACIÓN INICIAL (ORIGEN, GUÍA Y CONDICIÓN DE COBRO) -->
  <div class="p-3 border border-radius-md bg-white mb-4 shadow-sm">
    <h6 class="text-xs font-weight-bold text-uppercase text-success mb-3">
      <i class="material-symbols-rounded me-1 align-middle text-sm">tune</i> Configuración Inicial del Envío
    </h6>
    
    <div class="row g-3">
      <!-- COLUMNA IZQUIERDA: INFORMACIÓN DEL ENVÍO (ORIGEN + GUÍA EN LA MISMA FILA, MONTO ABAJO) -->
      <div class="col-12 col-lg-6">
        <div class="p-3 border border-radius-md bg-white h-100">
          <div class="d-flex align-items-center mb-3">
            <span class="material-symbols-rounded text-success me-2">route</span>
            <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Información del Envío</h6>
          </div>
          
          <div class="row g-2">
            <!-- ORIGEN Y GUÍA EN LA MISMA FILA -->
            <div class="col-12 col-md-6">
              <div class="input-group input-group-outline is-filled my-2">
                <label class="form-label">Ciudad / Sucursal Origen *</label>
                <select class="form-control" id="selectOrigen" required>
                  <option value="" disabled selected>Seleccionar origen...</option>
                  <?php if (!empty($sucursales_origen)): ?>
                    <?php foreach ($sucursales_origen as $suc): ?>
                      <option value="<?= $suc['id_sucursal'] ?>"><?= htmlspecialchars($suc['nombre_sucursal']) ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="input-group input-group-outline is-filled my-2">
                <label class="form-label">Nº de Guía de Origen *</label>
                <input type="text" class="form-control" id="inputGuiaOrigen" required placeholder="Ej. SCZ-9921">
              </div>
            </div>

            <!-- MONTO A COBRAR ABAJO DE ORIGEN Y GUÍA (DESPLEGABLE SOLO SI ES COD) -->
            <div class="col-12 d-none mt-2" id="divCampoMontoCOD">
              <div class="input-group input-group-outline is-filled my-2">
                <label class="form-label">Monto A Cobrar (Bs.) *</label>
                <input type="number" step="0.01" class="form-control" id="inputMontoCobrar" placeholder="0.00">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- COLUMNA DERECHA: CONDICIÓN Y MÉTODO DE COBRO -->
      <div class="col-12 col-lg-6">
        <div class="p-3 border border-radius-md bg-white h-100 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex align-items-center mb-3">
              <span class="material-symbols-rounded text-success me-2">payments</span>
              <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Condición de Cobro</h6>
            </div>
            
            <!-- TABS CONDICIÓN DE COBRO -->
            <div class="mb-3">
              <div class="custom-nav-wrapper" id="wrapperCondicionCobro">
                <ul class="custom-nav-pills d-flex w-100 mb-0 ps-0" role="tablist">
                  <li class="nav-item flex-fill text-center">
                    <a class="nav-link active text-xs font-weight-bold py-2" id="tab-pagado" data-bs-toggle="tab" href="#content-pagado" role="tab" onclick="seleccionarCondicionCobro('pagado')">
                      <i class="material-symbols-rounded text-sm me-1 align-middle">check_circle</i> Pagado en Origen
                    </a>
                  </li>
                  <li class="nav-item flex-fill text-center">
                    <a class="nav-link text-xs font-weight-bold py-2" id="tab-cod" data-bs-toggle="tab" href="#content-cod" role="tab" onclick="seleccionarCondicionCobro('cod')">
                      <i class="material-symbols-rounded text-sm me-1 align-middle">pending_actions</i> Por Cobrar (COD)
                    </a>
                  </li>
                </ul>
              </div>
              <input type="hidden" id="selectCondicionCobro" value="pagado">
            </div>

            <!-- MÉTODOS DE PAGO (DESPLEGABLE SI ES COD) -->
            <div class="d-none border-top pt-2 mt-2" id="divMetodosPagoCOD">
              
              <div class="custom-nav-wrapper w-100" id="wrapperMetodoPago">
                <ul class="custom-nav-pills d-flex flex-wrap gap-1 w-100 mb-0 ps-0" role="tablist">
                  <?php if (!empty($metodos_pago)): ?>
                    <?php foreach ($metodos_pago as $index => $mp): ?>
                      <li class="nav-item flex-fill text-center">
                        <a class="nav-link text-xs py-2 px-2 <?= $index === 0 ? 'active' : '' ?>"
                           id="tab-metodo-rec-<?= $mp['id_metodo_pago'] ?>"
                           data-bs-toggle="tab"
                           href="#metodo-rec-<?= $mp['id_metodo_pago'] ?>"
                           role="tab"
                           onclick="actualizarMetodoPagoRecepcion('<?= $mp['id_metodo_pago'] ?>')">
                          <i class="material-symbols-rounded text-sm me-1 align-middle"><?= !empty($mp['icono_metodo_pago']) ? $mp['icono_metodo_pago'] : 'payments' ?></i>
                          <?= htmlspecialchars($mp['nombre_metodo_pago']) ?>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </ul>
              </div>
              <input type="hidden" id="selectMetodoPago" value="<?= !empty($metodos_pago) ? $metodos_pago[0]['id_metodo_pago'] : 1 ?>">
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. SECCIÓN DATOS DE REMITENTE Y DESTINATARIO -->
  <div class="row g-3 g-md-4">
    
    <!-- TARJETA: DATOS DEL REMITENTE (ORIGEN) -->
    <div class="col-12 col-lg-6">
      <div class="p-3 border border-radius-md bg-white h-100 shadow-sm">
        <div class="d-flex align-items-center mb-3">
          <span class="material-symbols-rounded text-success me-2">person_pin</span>
          <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Datos del Remitente (Origen)</h6>
        </div>

        <div class="row g-2">
          <!-- FILA 1: CI Y CELULAR -->
          <div class="col-12 col-sm-6">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Nº Carnet / NIT *</label>
              <input type="text" class="form-control" id="remitente_ci" onblur="buscarCliente('remitente')" required>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Celular de Origen</label>
              <input type="text" class="form-control" id="remitente_celular">
            </div>
          </div>

          <!-- FILA 2: NOMBRES Y APELLIDOS EN UNA SOLA FILA -->
          <div class="col-12 col-md-4">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Nombres / Empresa *</label>
              <input type="text" class="form-control" id="remitente_nombres" required>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Apellido Paterno</label>
              <input type="text" class="form-control" id="remitente_paterno">
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Apellido Materno</label>
              <input type="text" class="form-control" id="remitente_materno">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TARJETA: DATOS DEL DESTINATARIO (LOCAL) -->
    <div class="col-12 col-lg-6">
      <div class="p-3 border border-radius-md bg-white h-100 shadow-sm">
        <div class="d-flex align-items-center mb-3">
          <span class="material-symbols-rounded text-success me-2">badge</span>
          <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Datos del Destinatario (Local)</h6>
        </div>

        <div class="row g-2">
          <!-- FILA 1: CI Y CELULAR -->
          <div class="col-12 col-sm-6">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Nº Carnet (C.I.) *</label>
              <input type="text" class="form-control" id="destinatario_ci" onblur="buscarCliente('destinatario')" required>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Celular Notificación *</label>
              <input type="text" class="form-control" id="destinatario_celular" required>
            </div>
          </div>

          <!-- FILA 2: NOMBRES Y APELLIDOS EN UNA SOLA FILA -->
          <div class="col-12 col-md-4">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Nombres *</label>
              <input type="text" class="form-control" id="destinatario_nombres" required>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Apellido Paterno *</label>
              <input type="text" class="form-control" id="destinatario_paterno" required>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="input-group input-group-outline my-2">
              <label class="form-label">Apellido Materno</label>
              <input type="text" class="form-control" id="destinatario_materno">
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

            <!-- PASO 2: VERIFICACIÓN DE BULTOS CON DATATABLES (IGUAL A DELIVERY) -->
            <div class="wizard-step-recepcion d-none" id="step-recepcion-2">
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <div class="d-flex align-items-center">
                  <span class="material-symbols-rounded text-success me-2">inventory_2</span>
                  <div>
                    <h6 class="text-xs text-sm-sm font-weight-bold text-uppercase text-success mb-0">Paso 2: Verificación de Bultos Arribados</h6>
                    <p class="text-xxs text-secondary mb-0">Confirme los precintos y pesos físicos recibidos en almacén</p>
                  </div>
                </div>
                <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md py-2 px-3 text-capitalize shadow-sm w-100 w-sm-auto" onclick="abrirModalBulto()">
                  <i class="material-symbols-rounded text-sm me-1">add</i> Agregar Bulto Arribado
                </button>
              </div>

              <!-- TABLA ESTRUCTURADA COMO EN DELIVERY -->
              <div class="card border border-radius-md bg-white p-2 p-md-3">
                <div class="table-responsive p-0">
                  <table class="table table-borderless align-items-center mb-0 w-100" id="tablaBultosRecepcion">
                    <thead>
                      <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-3">Código Precinto</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3">Descripción Bulto</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3">Tipo Contenido</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3">Peso (Kg)</th>
                        <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-3">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="tbodyBultosRecepcion">
                      <!-- Filas agregadas dinámicamente -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- PASO 3: RESUMEN Y CONFIRMACIÓN DE INGRESO -->
            <div class="wizard-step-recepcion d-none" id="step-recepcion-3">
              <div class="d-flex align-items-center mb-3">
                <span class="material-symbols-rounded text-success me-2">how_to_reg</span>
                <div>
                  <h6 class="text-xs text-sm-sm font-weight-bold text-uppercase text-success mb-0">Paso 3: Confirmación de Ingreso a Almacén</h6>
                  <p class="text-xxs text-secondary mb-0">Revise la información y registre el alta definitiva para habilitar el retiro al destinatario</p>
                </div>
              </div>

              <div class="p-3 p-md-4 border border-radius-lg bg-white shadow-none">
                <div class="row g-3">
                  <div class="col-12 col-sm-6">
                    <div class="p-3 bg-gray-100 border-radius-md h-100">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Ruta y Guía</span>
                      <p class="text-xs font-weight-bold text-dark mb-1"><strong>Origen:</strong> <span id="resumenOrigen">---</span></p>
                      <p class="text-xs font-weight-bold text-dark mb-0"><strong>Nº Guía:</strong> <span id="resumenGuia">---</span></p>
                    </div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <div class="p-3 bg-gray-100 border-radius-md h-100">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Carga Registrada</span>
                      <p class="text-xs font-weight-bold text-dark mb-1" id="resumenTotalBultos"><strong>Bultos:</strong> 0 bulto(s)</p>
                      <p class="text-xs font-weight-bold text-success mb-0"><strong>Estado:</strong> Listo para Almacenamiento</p>
                    </div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <div class="p-3 bg-gray-100 border-radius-md h-100">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Remitente</span>
                      <p class="text-xs font-weight-bold text-dark mb-0" id="resumenRemitente">---</p>
                    </div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <div class="p-3 bg-gray-100 border-radius-md h-100">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Destinatario</span>
                      <p class="text-xs font-weight-bold text-dark mb-0" id="resumenDestinatario">---</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <!-- PIE DE NAVEGACIÓN RESPONSIVO (IGUAL A DELIVERY) -->
        <div class="card-footer bg-white p-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
          <a href="<?= URL ?>/encomiendas" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold text-center" id="btnCancelRecepcion">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>

          <button type="button" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold d-none text-center" id="btnPrevRecepcion" onclick="cambiarPasoRecepcion(-1)">
            <i class="material-symbols-rounded me-1 text-sm align-middle">arrow_back</i> Anterior
          </button>
          
          <div class="ms-sm-auto d-flex flex-column flex-sm-row gap-2">
            <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md px-4 font-weight-bold text-center" id="btnNextRecepcion" onclick="cambiarPasoRecepcion(1)">
              Siguiente <i class="material-symbols-rounded ms-1 text-sm align-middle">arrow_forward</i>
            </button>
            
            <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold d-none text-center" id="btnSaveRecepcion" onclick="registrarIngresoFinal()">
              <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> Registrar Llegada a Almacén
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- MODAL PARA AGREGAR BULTO -->
<div class="modal fade" id="modalAgregarBulto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">
      <div class="modal-header border-bottom p-3">
        <h6 class="modal-title font-weight-bold text-dark mb-0">
          <i class="material-symbols-rounded align-middle me-1 text-success">add_box</i> Agregar Bulto Relevado
        </h6>
        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <form id="formModalBulto">
          <div class="mb-3">
            <label class="form-label text-xs font-weight-bold mb-1">Código / Precinto (Opcional)</label>
            <div class="input-group input-group-outline">
              <input type="text" class="form-control" id="modalCodigoPrecinto">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs font-weight-bold mb-1">Tipo de Contenido *</label>
            <div class="input-group input-group-static">
              <select class="form-control" id="modalContenidoBulto" required>
                <option value="" selected disabled>Seleccione origen primero...</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs font-weight-bold mb-1">Descripción del Bulto *</label>
            <div class="input-group input-group-outline">
              <input type="text" class="form-control" id="modalDescripcionBulto" required placeholder="Ej. Caja de cartón, Paquete sellado">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs font-weight-bold mb-1">Peso Registrado (Kg)</label>
            <div class="input-group input-group-outline">
              <input type="number" step="0.01" class="form-control" id="modalPesoBulto" placeholder="0.00">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer border-top p-2">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md" onclick="agregarBultoTabla()">Guardar Bulto</button>
      </div>
    </div>
  </div>
</div>

<script>
let pasoActualRecepcion = 1;
const totalPasosRecepcion = 3;
let listaBultosArray = [];
let bultosRecepcionDataTableInicializado = false;

// Activa/Oculta la sección COD
function seleccionarCondicionCobro(valor) {
  document.getElementById('selectCondicionCobro').value = valor;
  const divCampoMontoCOD = document.getElementById('divCampoMontoCOD');
  const divMetodosPagoCOD = document.getElementById('divMetodosPagoCOD');

  if (valor === 'cod') {
    divCampoMontoCOD.classList.remove('d-none');
    divMetodosPagoCOD.classList.remove('d-none');
  } else {
    divCampoMontoCOD.classList.add('d-none');
    divMetodosPagoCOD.classList.add('d-none');
  }
}

function actualizarMetodoPagoRecepcion(idMetodo) {
  document.getElementById('selectMetodoPago').value = idMetodo;
}
function cambiarPasoRecepcion(delta) {
  if (delta > 0 && !validarPasoActualRecepcion(pasoActualRecepcion)) {
    return;
  }

  const nuevoPaso = pasoActualRecepcion + delta;
  if (nuevoPaso >= 1 && nuevoPaso <= totalPasosRecepcion) {
    irAlPasoRecepcionDirecto(nuevoPaso);
  }
}

function irAlPasoRecepcionDirecto(pasoObjetivo) {
  if (pasoObjetivo > pasoActualRecepcion && !validarPasoActualRecepcion(pasoActualRecepcion)) {
    return;
  }

  document.getElementById(`step-recepcion-${pasoActualRecepcion}`).classList.add('d-none');
  pasoActualRecepcion = pasoObjetivo;
  document.getElementById(`step-recepcion-${pasoActualRecepcion}`).classList.remove('d-none');

  // Inicialización e integración de DataTables (Lógica idéntica a Delivery)
  if (pasoActualRecepcion === 2) {
    if (!bultosRecepcionDataTableInicializado && typeof inicializarDataTable === 'function') {
      inicializarDataTable('#tablaBultosRecepcion', { 
        ordering: false, 
        placeholder: 'Buscar bulto...' 
      });
      bultosRecepcionDataTableInicializado = true;
    }
  }

  if ($.fn.DataTable) {
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  }

  actualizarIndicadoresWizardRecepcion();
}

function validarPasoActualRecepcion(paso) {
  if (paso === 1) {
    const origen = document.getElementById('selectOrigen').value;
    const guia = document.getElementById('inputGuiaOrigen').value.trim();
    const remCi = document.getElementById('remitente_ci').value.trim();
    const remNombres = document.getElementById('remitente_nombres').value.trim();
    const destCi = document.getElementById('destinatario_ci').value.trim();
    const destNombres = document.getElementById('destinatario_nombres').value.trim();
    const destPaterno = document.getElementById('destinatario_paterno').value.trim();
    const destCelular = document.getElementById('destinatario_celular').value.trim();

    if (!origen) {
      Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe seleccionar la sucursal de origen' });
      return false;
    }
    if (!guia) {
      Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe ingresar el número de guía de origen' });
      return false;
    }
    if (!remCi || !remNombres) {
      Swal.fire({ icon: 'warning', title: 'Atención', text: 'Complete la información requerida del remitente' });
      return false;
    }
    if (!destCi || !destNombres || !destPaterno || !destCelular) {
      Swal.fire({ icon: 'warning', title: 'Atención', text: 'Complete la información requerida del destinatario' });
      return false;
    }
  } else if (paso === 2) {
    if (listaBultosArray.length === 0) {
      Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe agregar al menos un bulto para proceder' });
      return false;
    }
  }
  return true;
}

function actualizarIndicadoresWizardRecepcion() {
  for (let i = 1; i <= totalPasosRecepcion; i++) {
    const ind = document.getElementById(`indicator-recepcion-${i}`);
    const btn = ind.querySelector('button');
    const label = ind.querySelector('span');
    
    if (i <= pasoActualRecepcion) {
      btn.className = 'btn btn-icon-only btn-rounded bg-gradient-success text-white mb-1 shadow-none';
      if (label) {
        label.classList.remove('text-secondary');
        label.classList.add('text-dark', 'font-weight-bold');
      }
    } else {
      btn.className = 'btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none';
      if (label) {
        label.classList.remove('text-dark', 'font-weight-bold');
        label.classList.add('text-secondary');
      }
    }
  }

  if (pasoActualRecepcion === 3) {
    prepararResumenPaso3();
  }

  const esPrimerPaso = (pasoActualRecepcion === 1);
  const esUltimoPaso = (pasoActualRecepcion === totalPasosRecepcion);

  document.getElementById('btnCancelRecepcion').classList.toggle('d-none', !esPrimerPaso);
  document.getElementById('btnPrevRecepcion').classList.toggle('d-none', esPrimerPaso);
  document.getElementById('btnNextRecepcion').classList.toggle('d-none', esUltimoPaso);
  document.getElementById('btnSaveRecepcion').classList.toggle('d-none', !esUltimoPaso);
}

function buscarCliente(tipo) {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const ciInput = document.getElementById(`${tipo}_ci`);
  const ci = ciInput ? ciInput.value.trim() : '';
  if (!ci) return;

  fetch(`${baseUrl}/personas/buscarPorCiJson?ci=${encodeURIComponent(ci)}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.persona) {
        if (document.getElementById(`${tipo}_nombres`)) document.getElementById(`${tipo}_nombres`).value = data.persona.nombres_persona || '';
        if (document.getElementById(`${tipo}_paterno`)) document.getElementById(`${tipo}_paterno`).value = data.persona.paterno_persona || '';
        if (document.getElementById(`${tipo}_materno`)) document.getElementById(`${tipo}_materno`).value = data.persona.materno_persona || '';
        if (document.getElementById(`${tipo}_celular`)) document.getElementById(`${tipo}_celular`).value = data.persona.celular_persona || '';
      }
    })
    .catch(() => {});
}

function abrirModalBulto() {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const idOrigen = document.getElementById('selectOrigen').value;
  if (!idOrigen) {
    Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione primero la sucursal de origen (Paso 1)' });
    irAlPasoRecepcionDirecto(1);
    return;
  }

  const selectContenido = document.getElementById('modalContenidoBulto');
  selectContenido.innerHTML = '<option value="" selected disabled>Cargando tipos de contenido...</option>';

  fetch(`${baseUrl}/encomiendas/obtenerContenidosPorOrigen?id_origen=${idOrigen}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.data.length > 0) {
        let options = '<option value="" selected disabled>Seleccione Tipo de Contenido</option>';
        data.data.forEach(item => {
          options += `<option value="${item.id_encomienda_contenido}" data-nombre="${item.nombre_encomienda_contenido}">
            ${item.nombre_encomienda_contenido}
          </option>`;
        });
        selectContenido.innerHTML = options;
      } else {
        selectContenido.innerHTML = '<option value="" disabled selected>No hay tipos de contenido registrados.</option>';
      }
    })
    .catch(() => {
      selectContenido.innerHTML = '<option value="" disabled selected>Error al cargar contenido</option>';
    });

  const modalEl = document.getElementById('modalAgregarBulto');
  const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  modalInstance.show();
}

function renderizarTablaBultos() {
  // Destruir instancia previa de DataTable para reconstruir
  if ($.fn.DataTable.isDataTable('#tablaBultosRecepcion')) {
    $('#tablaBultosRecepcion').DataTable().destroy();
  }

  const tbody = document.getElementById('tbodyBultosRecepcion');
  tbody.innerHTML = '';

  listaBultosArray.forEach((bulto, index) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="py-2 ps-3"><span class="text-xs font-weight-bold text-dark">${bulto.precinto}</span></td>
      <td class="py-2 px-3"><span class="text-xs font-weight-bold text-dark">${bulto.descripcion}</span></td>
      <td class="py-2 px-3"><span class="text-xs font-weight-bold text-dark">${bulto.nombre_contenido}</span></td>
      <td class="py-2 px-3 text-center"><span class="text-xs font-weight-bold text-dark">${parseFloat(bulto.peso).toFixed(2)} Kg</span></td>
      <td class="py-2 pe-3 text-end">
        <button type="button" class="btn btn-link text-danger text-gradient p-0 mb-0" onclick="eliminarBulto(${index})">
          <i class="material-symbols-rounded text-sm">delete</i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  // Re-inicializar DataTable con helper global de Delivery
  if (typeof inicializarDataTable === 'function') {
    inicializarDataTable('#tablaBultosRecepcion', { 
      ordering: false, 
      placeholder: 'Buscar bulto...' 
    });
    bultosRecepcionDataTableInicializado = true;
  }
}

function agregarBultoTabla() {
  const descInput = document.getElementById('modalDescripcionBulto');
  const precintoInput = document.getElementById('modalCodigoPrecinto');
  const pesoInput = document.getElementById('modalPesoBulto');
  const contenidoSelect = document.getElementById('modalContenidoBulto');

  const desc = descInput.value.trim();
  const precinto = precintoInput.value.trim() || 'S/P';
  const peso = parseFloat(pesoInput.value) || 0;
  const idContenido = contenidoSelect.value;

  if (!desc) {
    Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese la descripción del bulto' });
    return;
  }

  if (!idContenido) {
    Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione el tipo de contenido del bulto' });
    return;
  }

  const optionSelected = contenidoSelect.options[contenidoSelect.selectedIndex];
  const nombreContenido = optionSelected ? optionSelected.getAttribute('data-nombre') : '';

  listaBultosArray.push({
    descripcion: desc,
    precinto: precinto,
    peso: peso,
    id_contenido: idContenido,
    nombre_contenido: nombreContenido
  });

  renderizarTablaBultos();

  descInput.value = '';
  precintoInput.value = '';
  pesoInput.value = '';

  const modalEl = document.getElementById('modalAgregarBulto');
  const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  modalInstance.hide();
}

function eliminarBulto(index) {
  listaBultosArray.splice(index, 1);
  renderizarTablaBultos();
}

function prepararResumenPaso3() {
  const selectOrigen = document.getElementById('selectOrigen');
  const origenTexto = selectOrigen.options[selectOrigen.selectedIndex] ? selectOrigen.options[selectOrigen.selectedIndex].text : '---';
  
  document.getElementById('resumenOrigen').textContent = origenTexto;
  document.getElementById('resumenGuia').textContent = document.getElementById('inputGuiaOrigen').value;
  document.getElementById('resumenTotalBultos').innerHTML = `<strong>Bultos:</strong> ${listaBultosArray.length} bulto(s)`;
  
  const remNombre = document.getElementById('remitente_nombres').value;
  const remPaterno = document.getElementById('remitente_paterno').value;
  const remCi = document.getElementById('remitente_ci').value;
  document.getElementById('resumenRemitente').textContent = `${remNombre} ${remPaterno} (CI/NIT: ${remCi})`;

  const destNombre = document.getElementById('destinatario_nombres').value;
  const destPaterno = document.getElementById('destinatario_paterno').value;
  const destCi = document.getElementById('destinatario_ci').value;
  document.getElementById('resumenDestinatario').textContent = `${destNombre} ${destPaterno} (CI: ${destCi})`;
}

function registrarIngresoFinal() {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const btnSave = document.getElementById('btnSaveRecepcion');

  const formData = new FormData();
  formData.append('guia_encomienda', document.getElementById('inputGuiaOrigen').value.trim());
  formData.append('id_sucursal_origen', document.getElementById('selectOrigen').value);
  formData.append('declaracion_encomienda', 'Recepción en Almacén');
  
  formData.append('remitente_ci', document.getElementById('remitente_ci').value.trim());
  formData.append('remitente_nombres', document.getElementById('remitente_nombres').value.trim());
  formData.append('remitente_paterno', document.getElementById('remitente_paterno').value.trim());
  formData.append('remitente_materno', document.getElementById('remitente_materno').value.trim());
  formData.append('remitente_celular', document.getElementById('remitente_celular').value.trim());
  
  formData.append('destinatario_ci', document.getElementById('destinatario_ci').value.trim());
  formData.append('destinatario_nombres', document.getElementById('destinatario_nombres').value.trim());
  formData.append('destinatario_paterno', document.getElementById('destinatario_paterno').value.trim());
  formData.append('destinatario_materno', document.getElementById('destinatario_materno').value.trim());
  formData.append('destinatario_celular', document.getElementById('destinatario_celular').value.trim());

  const esPagado = document.getElementById('selectCondicionCobro').value === 'pagado' ? 1 : 0;
  formData.append('estado_pago_encomienda', esPagado);
  formData.append('monto_encomienda', document.getElementById('inputMontoCobrar').value || 0);
  formData.append('id_metodo_pago', document.getElementById('selectMetodoPago').value || 1);

  formData.append('bultos_json', JSON.stringify(listaBultosArray));

  if (btnSave) btnSave.disabled = true;

  fetch(`${baseUrl}/encomiendas/guardarRecepcion`, {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      window.location.href = `${baseUrl}/encomiendas`;
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo guardar la recepción.' });
      if (btnSave) btnSave.disabled = false;
    }
  })
  .catch(() => {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al procesar la solicitud.' });
    if (btnSave) btnSave.disabled = false;
  });
}

document.addEventListener('DOMContentLoaded', function() {
  actualizarIndicadoresWizardRecepcion();
  if (typeof initCustomNavPills === 'function') {
    initCustomNavPills();
  }
});
</script>