<!-- views/dashboard/encomiendas/delivery.php -->

<div class="container-fluid py-2 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-11 mx-auto px-1 px-sm-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">
        
        <!-- HEADER STEPPER -->
        <div class="card-header bg-white p-3">
          <div class="row align-items-center g-2">
            <div class="col-12 text-center text-md-start">
              <h5 class="font-weight-bolder text-dark mb-0 fs-5 fs-md-4">Asistente de Entrega en Destino</h5>
              <p class="text-xs text-secondary mb-0">Guía #<?= htmlspecialchars($encomienda['guia_encomienda']) ?> | Verificación de receptor, revisión de bultos y acta de cierre</p>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center text-center px-0 px-md-4 mt-3 mt-md-4 py-1 py-md-2" id="contenedor-pasos-entrega">
            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-entrega-1">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gradient-success text-white mb-1 shadow-none" onclick="irAlPasoEntregaDirecto(1)">
                <i class="material-symbols-rounded text-sm">badge</i>
              </button>
              <span class="d-none d-md-block text-xs font-weight-bold text-dark text-truncate">1. Receptor y Cobro</span>
            </div>
            <div class="border-top border-2 flex-fill opacity-3" id="line-entrega-2"></div>
            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-entrega-2">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPasoEntregaDirecto(2)">
                <i class="material-symbols-rounded text-sm">inventory_2</i>
              </button>
              <span class="d-none d-md-block text-xs font-weight-bold text-secondary text-truncate">2. Verificación Bultos</span>
            </div>
            <div class="border-top border-2 flex-fill opacity-3" id="line-entrega-3"></div>
            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-entrega-3">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPasoEntregaDirecto(3)">
                <i class="material-symbols-rounded text-sm">print</i>
              </button>
              <span class="d-none d-md-block text-xs font-weight-bold text-secondary text-truncate">3. Acta e Impresión</span>
            </div>
          </div>
          <hr class="horizontal dark my-0 opacity-2">
        </div>

        <div class="card-body p-2 p-sm-3 p-md-4">
          <form id="formAsistenteEntrega" onsubmit="return false;">
            <input type="hidden" id="id_encomienda" value="<?= $encomienda['id_encomienda'] ?>">

            <!-- PASO 1: RECEPTOR Y COBRO -->
            <div class="wizard-step-entrega" id="step-entrega-1">
              <div class="d-flex align-items-center mb-3">
                <span class="material-symbols-rounded text-success me-2">badge</span>
                <div>
                  <h6 class="text-xs text-sm-sm font-weight-bold text-uppercase text-success mb-0">Paso 1: Receptor y Estado de Cuenta</h6>
                  <p class="text-xxs text-secondary mb-0">Verifique los datos de entrega y gestione los cobros correspondientes</p>
                </div>
              </div>

              <!-- RESUMEN ENCOMIENDA -->
              <div class="p-3 border border-radius-md bg-gray-100 mb-3">
                <div class="row g-3 align-items-center">
                  <div class="col-12 col-sm-6 col-md-4">
                    <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Guía Seleccionada</span>
                    <span class="badge bg-gradient-success text-xs">#<?= htmlspecialchars($encomienda['guia_encomienda']) ?></span>
                  </div>
                  <div class="col-12 col-sm-6 col-md-4">
                    <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Ruta</span>
                    <p class="text-xs font-weight-bold text-dark mb-0 text-truncate"><?= htmlspecialchars($encomienda['sucursal_origen_ciudad']) ?> ➔ <?= htmlspecialchars($encomienda['sucursal_destino_ciudad']) ?></p>
                  </div>
                  <div class="col-12 col-md-4">
                    <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Destinatario Registrado</span>
                    <p class="text-xs font-weight-bold text-dark mb-0 text-truncate"><?= htmlspecialchars($encomienda['destinatario_nombre']) ?> (CI: <?= htmlspecialchars($encomienda['destinatario_ci']) ?>)</p>
                  </div>
                </div>
              </div>

              <div class="row g-3 g-md-4">
                <!-- FORMULARIO DE RECEPTOR -->
                <div class="col-12 col-xl-6">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <div class="mb-3">
                      <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-1">Identificación de Quien Recibe</h6>
                    </div>
                    <div class="row g-3">
                      <div class="col-12 col-sm-5">
                        <label class="form-label text-xs font-weight-bold">Carnet de Identidad (C.I.) *</label>
                        <div class="input-group input-group-outline is-filled">
                          <input type="text" class="form-control" id="inputCiReceptor" value="<?= htmlspecialchars($encomienda['destinatario_ci']) ?>" required>
                        </div>
                      </div>
                      <div class="col-12 col-sm-7">
                        <label class="form-label text-xs font-weight-bold">Nombres *</label>
                        <div class="input-group input-group-outline is-filled">
                          <input type="text" class="form-control" id="inputNombreReceptor" value="<?= htmlspecialchars($encomienda['destinatario_nombres'] ?? '') ?>" required>
                        </div>
                      </div>
                      <div class="col-12 col-sm-6">
                        <label class="form-label text-xs font-weight-bold">Apellido Paterno</label>
                        <div class="input-group input-group-outline is-filled">
                          <input type="text" class="form-control" id="inputApellidoPaternoReceptor" value="<?= htmlspecialchars($encomienda['destinatario_paterno'] ?? '') ?>">
                        </div>
                        <span class="text-xxs text-secondary d-block mt-1">Obligatorio si quíen retira NO es el destinatario registrado.</span>
                      </div>
                      <div class="col-12 col-sm-6">
                        <label class="form-label text-xs font-weight-bold">Apellido Materno</label>
                        <div class="input-group input-group-outline is-filled">
                          <input type="text" class="form-control" id="inputApellidoMaternoReceptor" value="<?= htmlspecialchars($encomienda['destinatario_materno'] ?? '') ?>">
                        </div>
                      </div>
                      <div class="col-12">
                        <label class="form-label text-xs font-weight-bold">Celular de Contacto</label>
                        <div class="input-group input-group-outline is-filled">
                          <input type="text" class="form-control" id="inputCelularReceptor" value="<?= htmlspecialchars($encomienda['destinatario_celular']) ?>">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- CONTROL DE COBRO -->
                <div class="col-12 col-xl-6">
                  <div class="p-3 border border-radius-md bg-white h-100 d-flex flex-column justify-content-between">
                    <div>
                      <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3">Control de Cobro</h6>

                      <div class="mb-3">
                        <label class="form-label text-xs font-weight-bold mb-2 d-block">Condición de Pago</label>
                        <div class="d-flex flex-column flex-sm-row gap-2" id="pillsCondicionPago">
                          <span class="badge d-flex align-items-center justify-content-center gap-1 px-3 py-2 text-xs flex-fill <?= $encomienda['estado_pago_encomienda'] == 1 ? 'bg-gradient-success text-white' : 'bg-gray-200 text-secondary opacity-6' ?>">
                            <i class="material-symbols-rounded text-sm">payments</i> Pagado en Origen
                          </span>
                          <span class="badge d-flex align-items-center justify-content-center gap-1 px-3 py-2 text-xs flex-fill <?= $encomienda['estado_pago_encomienda'] == 0 ? 'bg-gradient-warning text-white' : 'bg-gray-200 text-secondary opacity-6' ?>">
                            <i class="material-symbols-rounded text-sm">local_shipping</i> Por Cobrar (COD)
                          </span>
                        </div>
                      </div>

                      <?php if ($encomienda['estado_pago_encomienda'] == 0): ?>
                      <div id="panelMetodoCobro" class="mb-2">
                        <label class="form-label text-xs font-weight-bold text-danger d-block mb-2">Método de Cobro en Destino * (seleccione una opción)</label>
                        <input type="hidden" id="selectMetodoCobroDestino" value="<?= !empty($metodos_pago) ?$metodos_pago[0]['id_metodo_pago'] : 1 ?>">

                        <!-- NAV-TABS ADAPTATIVOS SIN COLAPSO -->
                        <div class="custom-nav-wrapper">
                          <ul class="custom-nav-pills d-flex flex-nowrap overflow-x-auto w-100 mb-0 ps-0" id="pills-metodos-pago" role="tablist" style="scrollbar-width: thin;">
                            <?php if (!empty($metodos_pago)): ?>
                              <?php foreach ($metodos_pago as $index =>$mp): ?>
                                <li class="nav-item flex-shrink-0" role="presentation">
                                  <button type="button"
                                          class="nav-link text-xs font-weight-bold px-3 py-2 <?= $index === 0 ? 'active' : '' ?>"
                                          data-id-metodo="<?= $mp['id_metodo_pago'] ?>"
                                          onclick="seleccionarMetodoPagoTab(this, <?= $mp['id_metodo_pago'] ?>)">
                                    <?= htmlspecialchars($mp['nombre_metodo_pago']) ?>
                                  </button>
                                </li>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <li class="nav-item"><span class="text-xxs text-secondary p-2">No hay métodos de pago activos configurados.</span></li>
                            <?php endif; ?>
                          </ul>
                        </div>
                      </div>
                      <?php else: ?>
                      <div class="mb-2 p-2 border-radius-md bg-success bg-opacity-10">
                        <span class="text-xs font-weight-bold text-success d-flex align-items-center gap-1">
                          <i class="material-symbols-rounded text-sm">task_alt</i> Esta guía ya fue pagada en origen. No se requiere cobro adicional.
                        </span>
                      </div>
                      <?php endif; ?>
                    </div>

                    <div class="p-3 bg-gray-100 border-radius-lg mt-3">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-xs text-secondary">Estado Financiero:</span>
                        <span class="badge <?= $encomienda['estado_pago_encomienda'] == 1 ? 'bg-gradient-success' : 'bg-gradient-warning' ?> text-xxs">
                          <?= $encomienda['estado_pago_encomienda'] == 1 ? 'Ya Pagado' : 'Por Cobrar (COD)' ?>
                        </span>
                      </div>
                      <div class="d-flex justify-content-between align-items-center">
                        <span class="text-xs font-weight-bold text-dark">Monto A Cobrar:</span>
                        <h4 class="text-success mb-0 font-weight-bolder fs-4">Bs. <?= number_format($encomienda['estado_pago_encomienda'] == 0 ?$encomienda['monto_encomienda'] : 0, 2) ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- PASO 2: BULTOS CON DATATABLES -->
            <div class="wizard-step-entrega d-none" id="step-entrega-2">
              <div class="d-flex align-items-center mb-3">
                <span class="material-symbols-rounded text-success me-2">inventory_2</span>
                <div>
                  <h6 class="text-xs text-sm-sm font-weight-bold text-uppercase text-success mb-0">Paso 2: Verificación de Bultos</h6>
                  <p class="text-xxs text-secondary mb-0">Confirme la recepción conforme de la mercancía antes del alta final</p>
                </div>
              </div>
              <div class="card border border-radius-md bg-white p-2 p-md-3">
                <div class="table-responsive p-0">
                  <table class="table table-borderless align-items-center mb-0 w-100" id="tablaBultosEntrega">
                    <thead>
                      <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-3">Código Precinto</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3">Descripción Bulto</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3">Peso (Kg)</th>
                        <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-3">Estado Verificación</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($encomienda['bultos'])): ?>
                        <?php foreach ($encomienda['bultos'] as$bulto): ?>
                        <tr>
                          <td class="py-2 ps-3"><span class="text-xs font-weight-bold text-dark"><?= htmlspecialchars($bulto['codigo_detalle_encomienda']) ?></span></td>
                          <td class="py-2 px-3"><span class="text-xs font-weight-bold text-dark"><?= htmlspecialchars($bulto['descripcion_detalle_encomienda']) ?></span></td>
                          <td class="py-2 px-3 text-center"><span class="text-xs font-weight-bold text-dark"><?= $bulto['peso_detalle_encomienda'] ?$bulto['peso_detalle_encomienda'] . ' Kg' : 'N/E' ?></span></td>
                          <td class="py-2 pe-3 text-end"><span class="badge bg-gradient-success text-xxs">Conforme</span></td>
                        </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- PASO 3: RESUMEN Y CIERRE -->
            <div class="wizard-step-entrega d-none" id="step-entrega-3">
              <div class="d-flex align-items-center mb-3">
                <span class="material-symbols-rounded text-success me-2">print</span>
                <div>
                  <h6 class="text-xs text-sm-sm font-weight-bold text-uppercase text-success mb-0">Paso 3: Confirmación y Acta de Entrega</h6>
                  <p class="text-xxs text-secondary mb-0">Revise la vista previa antes de registrar en el sistema</p>
                </div>
              </div>
              <div class="card border border-radius-lg shadow-none bg-white p-3 p-md-4 position-relative">
                <h5 class="font-weight-bolder text-dark mb-2 fs-5">ACTA DE ENTREGA CONFORME</h5>
                <p class="text-xs text-dark mb-1"><strong>Receptor:</strong> <span id="resumenNombre"><?= htmlspecialchars($encomienda['destinatario_nombre']) ?></span> (CI: <span id="resumenCi"><?= htmlspecialchars($encomienda['destinatario_ci']) ?></span>)</p>
                <p class="text-xs text-dark mb-0"><strong>Guía:</strong> #<?= htmlspecialchars($encomienda['guia_encomienda']) ?></p>
              </div>
            </div>
          </form>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <!-- PIE DE NAVEGACIÓN RESPONSIVO -->
        <div class="card-footer bg-white p-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
          <a href="<?= URL ?>/encomiendas" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold text-center" id="btnCancelEntrega">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>

          <button type="button" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold d-none text-center" id="btnPrevEntrega" onclick="cambiarPasoEntrega(-1)">
            <i class="material-symbols-rounded me-1 text-sm align-middle">arrow_back</i> Anterior
          </button>
          
          <div class="ms-sm-auto d-flex flex-column flex-sm-row gap-2">
            <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md px-4 font-weight-bold text-center" id="btnNextEntrega" onclick="cambiarPasoEntrega(1)">
              Siguiente <i class="material-symbols-rounded ms-1 text-sm align-middle">arrow_forward</i>
            </button>
            
            <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold d-none text-center" id="btnSaveEntrega" onclick="procesarGuardadoEntrega()">
              <i class="material-symbols-rounded me-1 text-sm align-middle">print</i> Finalizar e Imprimir Acta
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
let pasoActualEntrega = 1;
const totalPasosEntrega = 3;
let bultosDataTableInicializado = false;

function seleccionarMetodoPagoTab(element, idMetodo) {
  document.getElementById('selectMetodoCobroDestino').value = idMetodo;
}

function cambiarPasoEntrega(delta) {
  if (delta > 0 && !validarPasoActualEntrega(pasoActualEntrega)) {
    return;
  }

  const nuevoPaso = pasoActualEntrega + delta;
  if (nuevoPaso >= 1 && nuevoPaso <= totalPasosEntrega) {
    irAlPasoEntregaDirecto(nuevoPaso);
  }
}

function irAlPasoEntregaDirecto(pasoObjetivo) {
  if (pasoObjetivo > pasoActualEntrega && !validarPasoActualEntrega(pasoActualEntrega)) {
    return;
  }
  
  // Ocultar paso anterior
  document.getElementById(`step-entrega-${pasoActualEntrega}`).classList.add('d-none');
  
  pasoActualEntrega = pasoObjetivo;
  
  // Mostrar paso actual
  document.getElementById(`step-entrega-${pasoActualEntrega}`).classList.remove('d-none');
  
  // Inicialización de DataTable Lazy para el paso 2
  if (pasoActualEntrega === 2 && !bultosDataTableInicializado) {
    if (typeof inicializarDataTable === 'function') {
      inicializarDataTable('#tablaBultosEntrega', { 
        ordering: false, 
        placeholder: 'Buscar bulto...' 
      });
      bultosDataTableInicializado = true;
    }
  }

  if ($.fn.DataTable) {
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  }

  actualizarIndicadoresWizardEntrega();
}

function validarPasoActualEntrega(paso) {
  if (paso === 1) {
    const ci = document.getElementById('inputCiReceptor').value.trim();
    const nombre = document.getElementById('inputNombreReceptor').value.trim();
    if (ci === '' || nombre === '') {
      Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Complete el carnet de identidad y el nombre del receptor.' });
      return false;
    }
  }
  return true;
}

function actualizarIndicadoresWizardEntrega() {
  for (let i = 1; i <= totalPasosEntrega; i++) {
    const ind = document.getElementById(`indicator-entrega-${i}`);
    const btn = ind.querySelector('button');
    const label = ind.querySelector('span');
    
    if (i <= pasoActualEntrega) {
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

  if (pasoActualEntrega === 3) {
    const nombreCompleto = [
      document.getElementById('inputNombreReceptor').value.trim(),
      document.getElementById('inputApellidoPaternoReceptor').value.trim(),
      document.getElementById('inputApellidoMaternoReceptor').value.trim()
    ].filter(Boolean).join(' ');

    document.getElementById('resumenCi').innerText = document.getElementById('inputCiReceptor').value.trim() || "S/N";
    document.getElementById('resumenNombre').innerText = nombreCompleto || "Sin Nombre";
  }

  const esPrimerPaso = (pasoActualEntrega === 1);
  const esUltimoPaso = (pasoActualEntrega === totalPasosEntrega);

  document.getElementById('btnCancelEntrega').classList.toggle('d-none', !esPrimerPaso);
  document.getElementById('btnPrevEntrega').classList.toggle('d-none', esPrimerPaso);
  document.getElementById('btnNextEntrega').classList.toggle('d-none', esUltimoPaso);
  document.getElementById('btnSaveEntrega').classList.toggle('d-none', !esUltimoPaso);
}

function procesarGuardadoEntrega() {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const btnSave = document.getElementById('btnSaveEntrega');

  const payload = {
    id_encomienda: $('#id_encomienda').val(),
    receptor_ci: $('#inputCiReceptor').val(),
    receptor_nombres: $('#inputNombreReceptor').val(),
    receptor_paterno: $('#inputApellidoPaternoReceptor').val(),
    receptor_materno: $('#inputApellidoMaternoReceptor').val(),
    receptor_celular: $('#inputCelularReceptor').val(),
    monto_entrega: '<?= $encomienda['estado_pago_encomienda'] == 0 ?$encomienda['monto_encomienda'] : 0 ?>',
    id_metodo_pago: $('#selectMetodoCobroDestino').val() || 1
  };

  if (btnSave) btnSave.disabled = true;

  $.post(baseUrl + '/encomiendas/guardarEntrega', payload, function(res) {
    if (res.success) {
      imprimirActaEntrega(res.id_encomienda, function() {
        window.location.href = baseUrl + '/encomiendas';
      });
    } else {
      Swal.fire('Error', res.message || 'Error al procesar la entrega.', 'error');
      if (btnSave) btnSave.disabled = false;
    }
  }, 'json').fail(function() {
    Swal.fire('Error', 'Ocurrió un error al comunicarse con el servidor.', 'error');
    if (btnSave) btnSave.disabled = false;
  });
}

document.addEventListener('DOMContentLoaded', function() {
  actualizarIndicadoresWizardEntrega();
  if (typeof initCustomNavPills === 'function') {
    initCustomNavPills();
  }
});
</script>