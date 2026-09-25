<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid py-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-lg-11 mx-auto px-2 px-md-3">
      
      <div class="card border-0 shadow-sm border-radius-xl">
        
        <!-- HEADER STEPPER -->
        <div class="card-header bg-white p-3">
          <div class="row align-items-center g-3">
            <div class="col-12 text-center text-md-start">
              <h5 class="font-weight-bolder text-dark mb-0">Emisión Asistida de Encomienda</h5>
              <p class="text-xs text-secondary mb-0">Registro unificado de ruta, remitente, destinatario, bultos y liquidación de carga</p>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center text-center px-1 px-md-4 mt-4 py-2" id="contenedor-pasos">
            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-1">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gradient-success text-white mb-1 shadow-none" onclick="irAlPasoDirecto(1)">
                <i class="material-symbols-rounded text-sm">badge</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-dark text-truncate">1. Ruta y Actores</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3" id="line-step-2"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-2">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPasoDirecto(2)">
                <i class="material-symbols-rounded text-sm">inventory</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-secondary text-truncate">2. Bultos e Inventario</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3" id="line-step-3"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-3">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPasoDirecto(3)">
                <i class="material-symbols-rounded text-sm">payments</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-secondary text-truncate">3. Tarifas y Cobro</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3" id="line-step-4"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-4">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPasoDirecto(4)">
                <i class="material-symbols-rounded text-sm">print</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-secondary text-truncate">4. Emisión de Guía</span>
            </div>
          </div>

          <hr class="horizontal dark my-0 opacity-2">
        </div>

        <!-- CUERPO DEL WIZARD -->
        <div class="card-body p-3 p-md-4">
          <form id="formNuevaEncomienda" onsubmit="return false;">

            <!-- PASO 1 -->
            <div class="wizard-step" id="step-1">
              <div class="p-3 border border-radius-md bg-white mb-4">
                <h6 class="text-xs font-weight-bold text-uppercase text-success mb-3">
                  <i class="material-symbols-rounded me-1 align-middle text-sm">map</i> Configuración de Origen y Destino
                </h6>
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <div class="input-group input-group-outline is-filled my-2">
                      <label class="form-label">Origen (Sucursal Emisora)</label>
                      <input type="text" class="form-control font-weight-bold" value="Trinidad - Central Terminal" readonly>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="input-group input-group-outline is-filled my-2">
                      <label class="form-label">Destino (Agencia de Retiro) *</label>
                      <select class="form-control" id="selectDestino" required>
                        <option value="" selected disabled>Seleccione destino...</option>
                        <?php if (!empty($sucursales_destino)): ?>
                          <?php foreach ($sucursales_destino as $suc): ?>
                            <option value="<?= $suc['id_sucursal'] ?>"><?= htmlspecialchars($suc['ciudad_sucursal'] . ' (' . $suc['nombre_sucursal'] . ')') ?></option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- DATOS REMITENTE Y DESTINATARIO -->
              <div class="row g-4">
                <div class="col-12 col-lg-6">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <div class="d-flex align-items-center mb-3">
                      <span class="material-symbols-rounded text-success me-2">person_pin</span>
                      <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Datos del Remitente</h6>
                    </div>
                    <div class="row g-2">
                      <div class="col-12 col-md-5">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Nº Carnet / NIT *</label>
                          <input type="text" class="form-control" id="remitente_ci" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-7">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Nombres *</label>
                          <input type="text" class="form-control" id="remitente_nombres" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Apellido Paterno *</label>
                          <input type="text" class="form-control" id="remitente_paterno" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Apellido Materno</label>
                          <input type="text" class="form-control" id="remitente_materno">
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Celular / WhatsApp *</label>
                          <input type="text" class="form-control" id="remitente_celular" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Dirección / Ref.</label>
                          <input type="text" class="form-control" id="remitente_direccion">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <div class="d-flex align-items-center mb-3">
                      <span class="material-symbols-rounded text-success me-2">person_pin</span>
                      <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Datos del Destinatario</h6>
                    </div>
                    <div class="row g-2">
                      <div class="col-12 col-md-5">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Nº Carnet (C.I.) *</label>
                          <input type="text" class="form-control" id="destinatario_ci" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-7">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Nombres *</label>
                          <input type="text" class="form-control" id="destinatario_nombres" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Apellido Paterno *</label>
                          <input type="text" class="form-control" id="destinatario_paterno" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Apellido Materno</label>
                          <input type="text" class="form-control" id="destinatario_materno">
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Celular Notificación *</label>
                          <input type="text" class="form-control" id="destinatario_celular" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="input-group input-group-outline my-2">
                          <label class="form-label">Dirección / Ref.</label>
                          <input type="text" class="form-control" id="destinatario_direccion">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- PASO 2: BULTOS DATATABLE -->
            <div class="wizard-step d-none" id="step-2">
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mb-3">
                <div>
                  <h6 class="text-xs font-weight-bold text-uppercase text-success mb-0">
                    <i class="material-symbols-rounded me-1 align-middle text-sm">inventory_2</i> Desglose de Bultos e Inventario
                  </h6>
                </div>
                <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md py-2 px-3 text-capitalize shadow-sm" onclick="abrirModalBulto()">
                  <i class="material-symbols-rounded text-sm me-1">add</i> Agregar Bulto
                </button>
              </div>

              <div class="table-responsive p-3 border border-radius-md bg-white">
                <table class="table align-items-center mb-0 w-100" id="tablaBultos">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2"># Bulto</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Descripción Bulto</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tipo / Tarifa</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Peso (Kg)</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subtotal</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">Acciones</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>

            <!-- PASO 3: TARIFAS Y COBRO (TAB ANIMADO Y BUTTON GROUP) -->
            <div class="wizard-step d-none" id="step-3">
              <div class="d-flex align-items-center mb-3">
                <span class="material-symbols-rounded text-success me-2">payments</span>
                <div>
                  <h6 class="text-xs font-weight-bold text-uppercase text-success mb-0">Paso 3: Declaración de Contenido y Tarifas</h6>
                  <p class="text-xxs text-secondary mb-0">Detalle el contenido de la encomienda y configure la liquidación del flete</p>
                </div>
              </div>

              <div class="row g-3">
                <!-- Columna Izquierda: Descripción y Seguro -->
                <div class="col-12 col-lg-5">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3">Descripción de la Carga</h6>
                    
                    <div class="mb-3">
                      <label class="form-label text-xs font-weight-bold">Declaración del Contenido / Descripción *</label>
                      <div class="input-group input-group-outline">
                        <input type="text" class="form-control" id="inputContenidoGeneral" required placeholder="Ej. Documentos legales, repuestos, caja de ropa...">
                      </div>
                    </div>

                    <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3 mt-4">Conceptos de Cobro y Tarifario</h6>
                    
                    <div class="mb-3">
                      <label class="form-label text-xs font-weight-bold">Resumen de Bultos Registrados</label>
                      <div class="input-group input-group-outline is-filled">
                        <input type="text" class="form-control font-weight-bold bg-gray-100 text-dark" id="infoCalculoBultos" readonly>
                      </div>
                    </div>

                    <div class="row g-2">
                      <div class="col-12 col-md-6">
                        <label class="form-label text-xs font-weight-bold">Monto Seguro / Fragilidad (Bs.)</label>
                        <div class="input-group input-group-outline is-filled">
                          <input type="number" class="form-control" id="montoSeguro" value="0.00" step="0.5" oninput="calcularTotalLiquidacion()">
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label text-xs font-weight-bold">Descuento Aplicado (Bs.)</label>
                        <div class="input-group input-group-outline is-filled">
                          <input type="number" class="form-control" id="montoDescuento" value="0.00" step="0.5" oninput="calcularTotalLiquidacion()">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Columna Derecha: Modalidades y Métodos con Pestañas Animadas -->
                <div class="col-12 col-lg-7">
                <div class="p-3 border border-radius-md bg-white h-100 d-flex flex-column justify-content-between">
                    <div>
                    <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3">Modalidad y Forma de Pago</h6>
                    
                    <!-- 1. MODALIDAD DE LIQUIDACIÓN (TAB ANIMADO) -->
                    <div class="mb-3">
                        <label class="form-label text-xs font-weight-bold mb-1">Forma de Liquidación *</label>
                        <div class="custom-nav-wrapper" id="wrapperModalidad">
                        <ul class="custom-nav-pills" role="tablist">
                            <li class="nav-item">
                            <a class="nav-link active" id="tab-contado" data-bs-toggle="tab" href="#content-contado" role="tab" onclick="seleccionarModalidad(1)">
                                <i class="material-symbols-rounded text-sm me-1 align-middle">payments</i> Pagado en Origen
                            </a>
                            </li>
                            <li class="nav-item">
                            <a class="nav-link" id="tab-cod" data-bs-toggle="tab" href="#content-cod" role="tab" onclick="seleccionarModalidad(0)">
                                <i class="material-symbols-rounded text-sm me-1 align-middle">local_shipping</i> Por Cobrar (COD)
                            </a>
                            </li>
                        </ul>
                        <div class="moving-tab"></div>
                        </div>
                        <input type="hidden" id="selectModalidadPago" value="1">
                    </div>

                    <!-- 2. MÉTODO DE COBRO (TAB ANIMADO GRID) -->
                    <div class="mb-3" id="secMetodoCobro">
                        <label class="form-label text-xs font-weight-bold mb-1">Método de Cobro (En Origen)</label>
                        <div class="custom-nav-wrapper">
                        <ul class="custom-nav-pills d-flex flex-wrap gap-1" role="tablist" id="pillsMetodosCobro">
                            <?php if (!empty($metodos_pago)): ?>
                            <?php foreach ($metodos_pago as $index => $metodo): ?>
                                <li class="nav-item flex-fill text-center">
                                <a class="nav-link text-xs py-2 px-2 <?= $index === 0 ? 'active' : '' ?>" 
                                    id="tab-metodo-<?= $metodo['id_metodo_pago'] ?>" 
                                    data-bs-toggle="tab" 
                                    href="#metodo-<?= $metodo['id_metodo_pago'] ?>" 
                                    role="tab" 
                                    onclick="actualizarMetodoCobro(<?= $metodo['id_metodo_pago'] ?>)">
                                    <i class="material-symbols-rounded text-sm me-1 align-middle"><?= !empty($metodo['icono_metodo_pago']) ? $metodo['icono_metodo_pago'] : 'payments' ?></i>
                                    <?= htmlspecialchars($metodo['nombre_metodo_pago']) ?>
                                </a>
                                </li>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link text-xs py-2 px-2 active" id="tab-metodo-1" data-bs-toggle="tab" role="tab" onclick="actualizarMetodoCobro(1)">
                                <i class="material-symbols-rounded text-sm me-1 align-middle">payments</i> Efectivo
                                </a>
                            </li>
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link text-xs py-2 px-2" id="tab-metodo-2" data-bs-toggle="tab" role="tab" onclick="actualizarMetodoCobro(2)">
                                <i class="material-symbols-rounded text-sm me-1 align-middle">qr_code_2</i> QR
                                </a>
                            </li>
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link text-xs py-2 px-2" id="tab-metodo-3" data-bs-toggle="tab" role="tab" onclick="actualizarMetodoCobro(3)">
                                <i class="material-symbols-rounded text-sm me-1 align-middle">smartphone</i> Tigo Money
                                </a>
                            </li>
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link text-xs py-2 px-2" id="tab-metodo-4" data-bs-toggle="tab" role="tab" onclick="actualizarMetodoCobro(4)">
                                <i class="material-symbols-rounded text-sm me-1 align-middle">credit_card</i> Tarjeta
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <div class="moving-tab"></div>
                        </div>
                        <input type="hidden" id="selectMetodoCobro" value="1">
                    </div>
                    </div>

                    <!-- TARJETA RESUMEN TOTAL -->
                    <div class="p-3 bg-gray-100 border-radius-lg mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-xs text-secondary">Estado Tarifario:</span>
                        <span class="badge bg-gradient-success text-xxs" id="lblEstadoCalculo">Autocálculo por Bultos</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-xs font-weight-bold text-dark">Total a Cobrar:</span>
                        <h4 class="text-success mb-0 font-weight-bolder" id="lblTotalMonto">Bs. 0.00</h4>
                    </div>
                    </div>

                </div>
                </div>
              </div>
            </div>

            <!-- PASO 4: EMISIÓN DE GUÍA Y CÓDIGO QR -->
            <div class="wizard-step d-none" id="step-4">
              <div class="d-flex align-items-center mb-3">
                <span class="material-symbols-rounded text-success me-2">print</span>
                <div>
                  <h6 class="text-xs font-weight-bold text-uppercase text-success mb-0">Paso 4: Previsualización de Guía y Código QR</h6>
                  <p class="text-xxs text-secondary mb-0">Verifique todos los datos antes de emitir e imprimir la guía con código QR de rastreo rápido</p>
                </div>
              </div>

              <!-- CONTENEDOR TIPO TICKET / GUÍA OFICIAL -->
              <div class="card border border-radius-lg shadow-none bg-white p-3 p-md-4 position-relative overflow-hidden" id="guiaImpresionContainer">
                
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
                  <div class="d-flex align-items-center">
                    <span class="material-symbols-rounded text-success fs-2 me-2">local_shipping</span>
                    <div>
                      <h5 class="font-weight-bolder text-dark mb-0">TRANSEXPRESS S.R.L.</h5>
                      <p class="text-xxs text-secondary mb-0">Sistema de Encomiendas y Carga Regional</p>
                    </div>
                  </div>
                  <div class="text-start text-sm-end w-100 w-sm-auto">
                    <span class="badge bg-gradient-success text-sm px-3 py-2 mb-1">
                      GUÍA #<span id="prevGuiaNumero">TRX-2026-8493</span>
                    </span>
                    <p class="text-xxs text-secondary mb-0" id="prevFechaEmision">Fecha: --/--/----</p>
                  </div>
                </div>

                <div class="row g-4 align-items-center">
                  <div class="col-12 col-md-8">
                    <div class="row g-3">
                      <div class="col-12 col-sm-6">
                        <div class="p-2 bg-gray-100 border-radius-md h-100">
                          <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Ruta de Envío</span>
                          <p class="text-xs font-weight-bold text-dark mb-1"><strong>Origen:</strong> Trinidad (Central)</p>
                          <p class="text-xs font-weight-bold text-dark mb-0"><strong>Destino:</strong> <span id="prevGuiaDestino">-</span></p>
                        </div>
                      </div>

                      <div class="col-12 col-sm-6">
                        <div class="p-2 bg-gray-100 border-radius-md h-100">
                          <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Condición de Pago</span>
                          <p class="text-xs font-weight-bold text-dark mb-1"><strong>Modalidad:</strong> <span id="prevGuiaModalidadTxt">Pagado en Origen</span></p>
                          <p class="text-xs font-weight-bold text-success mb-0"><strong>Total Flete:</strong> <span id="prevGuiaTotalFlete">Bs. 0.00</span></p>
                        </div>
                      </div>

                      <div class="col-12 col-sm-6">
                        <div class="p-2 border border-radius-md h-100">
                          <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Remitente</span>
                          <p class="text-xs font-weight-bold text-dark mb-0" id="prevGuiaRemitente">-</p>
                          <p class="text-xxs text-secondary mb-0">CI: <span id="prevGuiaRemitenteCi">-</span> | Cel: <span id="prevGuiaRemitenteCel">-</span></p>
                        </div>
                      </div>

                      <div class="col-12 col-sm-6">
                        <div class="p-2 border border-radius-md h-100">
                          <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Destinatario (Retiro)</span>
                          <p class="text-xs font-weight-bold text-dark mb-0" id="prevGuiaDestinatario">-</p>
                          <p class="text-xxs text-secondary mb-0">CI: <span id="prevGuiaDestinatarioCi">-</span> | Cel: <span id="prevGuiaCelular">-</span></p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 col-md-4 text-center">
                    <div class="p-3 bg-gray-100 border-radius-lg d-inline-block w-100">
                      <span class="text-xxs font-weight-bold text-uppercase text-secondary d-block mb-2">Código QR de Búsqueda y Rastreo</span>
                      <div class="bg-white p-3 border-radius-md d-inline-block shadow-sm mb-2">
                        <div class="d-flex justify-content-center align-items-center" style="width: 120px; height: 120px; margin: 0 auto; background: #fff; border: 2px dashed #344767; border-radius: 8px;">
                          <div class="text-center">
                            <span class="material-symbols-rounded text-dark" style="font-size: 56px;">qr_code_2</span>
                            <span class="text-xxs font-weight-bold text-dark d-block" id="prevGuiaQrCode">TRX-8493</span>
                          </div>
                        </div>
                      </div>
                      <p class="text-xxs text-secondary mb-0">Escanee este código con el lector POS para buscar o despachar la guía instantáneamente en el sistema.</p>
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
          <a href="<?= rtrim(URL, "/") ?>/encomiendas" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold w-100 w-sm-auto" id="btnCancel">
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
              <i class="material-symbols-rounded me-1 text-sm align-middle">print</i> Emitir e Imprimir Guía
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- MODAL AGREGAR BULTO -->
<div class="modal fade" id="modalAgregarBulto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">
      <div class="modal-header bg-gradient-success text-white">
        <h5 class="modal-title text-white font-weight-bold">
          <i class="material-symbols-rounded me-1 align-middle">inventory_2</i> Registrar Nuevo Bulto
        </h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formModalBulto" onsubmit="return false;">
          <div class="mb-3">
            <label class="form-label text-xs font-weight-bold">Descripción del Bulto *</label>
            <div class="input-group input-group-outline">
              <input type="text" class="form-control" id="modalDescripcion" required placeholder="Ej. Caja de cartón, Saco de ropa...">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs font-weight-bold">Tipo de Contenido / Tarifa *</label>
            <div class="input-group input-group-outline is-filled">
              <select class="form-control" id="modalContenido" required>
                <option value="" selected disabled>Cargando opciones del destino...</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-xs font-weight-bold">Peso Est. (Kg) (Opcional)</label>
            <div class="input-group input-group-outline">
              <input type="number" step="0.1" class="form-control" id="modalPeso" placeholder="Ej. 5.5">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer bg-gray-100">
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-sm bg-gradient-success mb-0" onclick="agregarBultoDesdeModal()">Agregar Bulto</button>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPTS DE PESTAÑAS ANIMADAS (TAB MOVING) -->
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

    function setInitialPosition() {
      var currentActive = navPills.querySelector('.nav-link.active') || navPills.querySelector('.nav-link');
      if (currentActive) updateTabPosition(currentActive);
    }

    setInitialPosition();

    var tabLinks = navPills.querySelectorAll('.nav-link');
    tabLinks.forEach(function (tab) {
      tab.addEventListener('click', function (e) {
        tabLinks.forEach(function (l) { l.classList.remove('active'); });
        e.currentTarget.classList.add('active');
        updateTabPosition(e.currentTarget);
      });
    });

    if (window.ResizeObserver) {
      var resizeObserver = new ResizeObserver(function () {
        var active = navPills.querySelector('.nav-link.active');
        if (active) updateTabPosition(active);
      });
      resizeObserver.observe(wrapper);
    }
  });
}
</script>

<!-- SCRIPTS DE WIZARD, DATATABLES Y EVENTOS -->
<script>
let pasoActual = 1;
let listaBultos = [];
let dtBultos = null;
const baseUrl = '<?php echo rtrim(URL, "/"); ?>';

// Inicialización usando tu función global
function inicializarDataTableBultos() {
  if (dtBultos) return;
  
  dtBultos = inicializarDataTable('#tablaBultos', {
    ordering: false,
    placeholder: 'Buscar bulto...',
    pageLength: 5,
    columns: [
      { data: 'index', className: 'text-xs font-weight-bold' },
      { data: 'descripcion', className: 'text-xs font-weight-bold' },
      { data: 'contenido', className: 'text-xs font-weight-bold' },
      { data: 'peso', className: 'text-center text-xs font-weight-bold' },
      { data: 'subtotal', className: 'text-end text-xs font-weight-bold' },
      { data: 'acciones', className: 'text-end', orderable: false }
    ]
  });
}

// Renderizado de bultos sobre tu DataTable nativa
function renderizarBultos() {
  inicializarDataTableBultos();
  
  dtBultos.clear();

  const dataRows = listaBultos.map((b, i) => {
    return {
      index: `Bulto #${i + 1}`,
      descripcion: b.descripcion,
      contenido: b.nombre_contenido,
      peso: b.peso > 0 ? `${b.peso} Kg` : '-',
      subtotal: `Bs. ${b.subtotal.toFixed(2)}`,
      acciones: `<button type="button" class="btn btn-link text-danger p-0 m-0" onclick="eliminarBulto(${i})">
                  <i class="material-symbols-rounded text-sm">delete</i>
                </button>`
    };
  });

  dtBultos.rows.add(dataRows).draw();
}

function actualizarModalidadSeleccionada(valor) {
  document.getElementById('selectModalidadPago').value = valor;
}

function actualizarMetodoCobro(idMetodo) {
  document.getElementById('selectMetodoCobro').value = idMetodo;
}

function cambiarPaso(direccion) {
  const nuevoPaso = pasoActual + direccion;
  if (nuevoPaso < 1 || nuevoPaso > 4) return;

  if (pasoActual === 1 && direccion === 1) {
    const destino = document.getElementById('selectDestino').value;
    const remCi = document.getElementById('remitente_ci').value;
    const destCi = document.getElementById('destinatario_ci').value;

    if (!destino) {
      Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe seleccionar una agencia de destino.' });
      return;
    }
    if (!remCi || !destCi) {
      Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Complete los carnets/CI del remitente y destinatario.' });
      return;
    }
  }

  if (pasoActual === 2 && direccion === 1) {
    if (listaBultos.length === 0) {
      Swal.fire({ icon: 'warning', title: 'Sin Bultos', text: 'Debe agregar al menos un bulto antes de continuar.' });
      return;
    }
  }

  irAlPasoDirecto(nuevoPaso);
}

function irAlPasoDirecto(paso) {
  const stepActualEl = document.getElementById(`step-${pasoActual}`);
  if (stepActualEl) stepActualEl.classList.add('d-none');
  
  const btnAnt = document.querySelector(`#indicator-step-${pasoActual} button`);
  if (btnAnt) {
    btnAnt.classList.remove('bg-gradient-success', 'text-white');
    btnAnt.classList.add('bg-gray-200', 'text-secondary');
  }

  pasoActual = paso;

  const stepNuevoEl = document.getElementById(`step-${pasoActual}`);
  if (stepNuevoEl) stepNuevoEl.classList.remove('d-none');
  
  const btnNuevo = document.querySelector(`#indicator-step-${pasoActual} button`);
  if (btnNuevo) {
    btnNuevo.classList.remove('bg-gray-200', 'text-secondary');
    btnNuevo.classList.add('bg-gradient-success', 'text-white');
  }

  document.getElementById('btnCancel').classList.toggle('d-none', pasoActual !== 1);
  document.getElementById('btnPrev').classList.toggle('d-none', pasoActual === 1);
  document.getElementById('btnNext').classList.toggle('d-none', pasoActual === 4);
  document.getElementById('btnSave').classList.toggle('d-none', pasoActual !== 4);

  if (pasoActual === 2) {
    inicializarDataTableBultos();
    setTimeout(() => { dtBultos.columns.adjust().draw(); }, 100);
  }

  if (pasoActual === 3) {
    initCustomNavPills();
  }

  if (pasoActual === 4) previsualizarGuiaFisica();
}

function abrirModalBulto() {
  const idDestino = document.getElementById('selectDestino').value;
  if (!idDestino) {
    Swal.fire({ icon: 'info', title: 'Seleccione Destino', text: 'Seleccione primero el destino en el Paso 1.' });
    irAlPasoDirecto(1);
    return;
  }

  const selectContenido = document.getElementById('modalContenido');
  selectContenido.innerHTML = '<option value="" selected disabled>Cargando tarifas...</option>';

  fetch(`${baseUrl}/encomiendas/obtenerContenidosPorDestino?id_destino=${idDestino}`)
    .then(r => r.json())
    .then(res => {
      if (res.success && res.data.length > 0) {
        let options = '<option value="" selected disabled>Seleccione Contenido / Tarifa</option>';
        res.data.forEach(item => {
          options += `<option value="${item.id_encomienda_contenido}" data-precio="${item.precio_tarifa_encomienda}">
            ${item.nombre_encomienda_contenido} - Bs. ${parseFloat(item.precio_tarifa_encomienda).toFixed(2)}
          </option>`;
        });
        selectContenido.innerHTML = options;
      } else {
        selectContenido.innerHTML = '<option value="" disabled>No hay tarifas registradas para esta ruta</option>';
      }
    })
    .catch(() => {
      selectContenido.innerHTML = '<option value="" disabled>Error al cargar contenidos</option>';
    });

  const modalEl = document.getElementById('modalAgregarBulto');
  const modalObj = new bootstrap.Modal(modalEl);
  modalObj.show();
}

function agregarBultoDesdeModal() {
  const descripcion = document.getElementById('modalDescripcion').value.trim();
  const selectContenido = document.getElementById('modalContenido');
  const idContenido = selectContenido.value;
  
  const optionSelected = selectContenido.options[selectContenido.selectedIndex];
  const nombreContenido = optionSelected ? optionSelected.text : '';
  const pesoVal = document.getElementById('modalPeso').value.trim();
  const peso = pesoVal !== '' ? parseFloat(pesoVal) : 0;
  const idDestino = document.getElementById('selectDestino').value;

  if (!descripcion || !idContenido) {
    Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Complete la descripción y seleccione un tipo de contenido.' });
    return;
  }

  fetch(`${baseUrl}/encomiendas/obtenerTarifa?id_destino=${idDestino}&id_contenido=${idContenido}`)
    .then(r => r.json())
    .then(res => {
      let subtotal = res.success ? parseFloat(res.precio) : 0;

      listaBultos.push({
        descripcion: descripcion,
        id_contenido: idContenido,
        nombre_contenido: nombreContenido,
        peso: peso,
        subtotal: subtotal
      });

      renderizarBultos();
      calcularTotalLiquidacion();

      document.getElementById('formModalBulto').reset();
      const modalEl = document.getElementById('modalAgregarBulto');
      const modalObj = bootstrap.Modal.getInstance(modalEl);
      if (modalObj) modalObj.hide();
    });
}

function eliminarBulto(index) {
  listaBultos.splice(index, 1);
  renderizarBultos();
  calcularTotalLiquidacion();
}

function renderizarBultos() {
  inicializarDataTableBultos();
  dtBultos.clear();

  const dataRows = listaBultos.map((b, i) => {
    return {
      index: `Bulto #${i + 1}`,
      descripcion: b.descripcion,
      contenido: b.nombre_contenido,
      peso: b.peso > 0 ? `${b.peso} Kg` : '-',
      subtotal: `Bs. ${b.subtotal.toFixed(2)}`,
      acciones: `<button type="button" class="btn btn-link text-danger p-0 m-0" onclick="eliminarBulto(${i})">
                  <i class="material-symbols-rounded text-sm">delete</i>
                </button>`
    };
  });

  dtBultos.rows.add(dataRows).draw();
}

function calcularTotalLiquidacion() {
  let subtotal = listaBultos.reduce((acc, b) => acc + b.subtotal, 0);
  let seguro = parseFloat(document.getElementById('montoSeguro').value) || 0;
  let descuento = parseFloat(document.getElementById('montoDescuento').value) || 0;

  document.getElementById('infoCalculoBultos').value = `${listaBultos.length} bulto(s) registrado(s) (Subtotal Bs. ${subtotal.toFixed(2)})`;
  let total = Math.max(0, subtotal + seguro - descuento);
  document.getElementById('lblTotalMonto').textContent = 'Bs. ' + total.toFixed(2);
}

function previsualizarGuiaFisica() {
  const remNom = document.getElementById('remitente_nombres').value;
  const remPat = document.getElementById('remitente_paterno').value;
  const remCi  = document.getElementById('remitente_ci').value;
  const remCel = document.getElementById('remitente_celular').value;

  const destNom = document.getElementById('destinatario_nombres').value;
  const destPat = document.getElementById('destinatario_paterno').value;
  const destCi  = document.getElementById('destinatario_ci').value;
  const destCel = document.getElementById('destinatario_celular').value;

  const hoy = new Date();
  document.getElementById('prevFechaEmision').textContent = `Fecha: ${hoy.getDate()}/${hoy.getMonth()+1}/${hoy.getFullYear()}`;
  document.getElementById('prevGuiaRemitente').textContent = `${remNom} ${remPat}`.trim();
  document.getElementById('prevGuiaRemitenteCi').textContent = remCi;
  document.getElementById('prevGuiaRemitenteCel').textContent = remCel;
  
  document.getElementById('prevGuiaDestinatario').textContent = `${destNom} ${destPat}`.trim();
  document.getElementById('prevGuiaDestinatarioCi').textContent = destCi;
  document.getElementById('prevGuiaCelular').textContent = destCel;
  
  const sel = document.getElementById('selectDestino');
  document.getElementById('prevGuiaDestino').textContent = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '-';
  
  const esPagado = document.getElementById('selectModalidadPago').value === '1';
  document.getElementById('prevGuiaModalidadTxt').textContent = esPagado ? 'Pagado en Origen' : 'Por Cobrar en Destino (COD)';
  document.getElementById('prevGuiaTotalFlete').textContent = document.getElementById('lblTotalMonto').textContent;
}

function imprimirGuiaDirecta() {
  window.print();
}

document.addEventListener('DOMContentLoaded', function() {
  const btnSave = document.getElementById('btnSave');
  if (btnSave) {
    btnSave.addEventListener('click', function() {
      Swal.fire({
        title: '¿Confirmar registro?',
        text: 'Se registrará la encomienda y emitirá el comprobante correlativo.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4caf50',
        cancelButtonColor: '#f44336',
        confirmButtonText: 'Sí, emitir',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          btnSave.disabled = true;

          const formData = new FormData();
          formData.append('id_sucursal_destino', document.getElementById('selectDestino').value);
          formData.append('remitente_ci', document.getElementById('remitente_ci').value);
          formData.append('remitente_nombres', document.getElementById('remitente_nombres').value);
          formData.append('remitente_paterno', document.getElementById('remitente_paterno').value);
          formData.append('remitente_materno', document.getElementById('remitente_materno').value);
          formData.append('remitente_celular', document.getElementById('remitente_celular').value);
          formData.append('remitente_direccion', document.getElementById('remitente_direccion').value);

          formData.append('destinatario_ci', document.getElementById('destinatario_ci').value);
          formData.append('destinatario_nombres', document.getElementById('destinatario_nombres').value);
          formData.append('destinatario_paterno', document.getElementById('destinatario_paterno').value);
          formData.append('destinatario_materno', document.getElementById('destinatario_materno').value);
          formData.append('destinatario_celular', document.getElementById('destinatario_celular').value);
          formData.append('destinatario_direccion', document.getElementById('destinatario_direccion').value);

          formData.append('contenido', document.getElementById('inputContenidoGeneral').value);
          formData.append('monto_seguro', document.getElementById('montoSeguro').value);
          formData.append('monto_descuento', document.getElementById('montoDescuento').value);
          formData.append('monto_total', document.getElementById('lblTotalMonto').textContent.replace('Bs. ', ''));
          formData.append('modalidad_pago', document.getElementById('selectModalidadPago').value);
          formData.append('metodo_cobro', document.getElementById('selectMetodoCobro').value);
          formData.append('bultos_json', JSON.stringify(listaBultos));

          fetch(baseUrl + '/encomiendas/guardar', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(res => {
            if (res.success) {
              document.getElementById('prevGuiaNumero').textContent = res.guia;
              document.getElementById('prevGuiaQrCode').textContent = res.guia;
              Swal.fire({
                icon: 'success',
                title: '¡Registrado Exitosamente!',
                text: 'Guía N°: ' + res.guia,
                showCancelButton: true,
                confirmButtonText: 'Imprimir Guía',
                cancelButtonText: 'Ir a Lista'
              }).then((resPrint) => {
                if (resPrint.isConfirmed) {
                  window.print();
                }
                window.location.href = baseUrl + '/encomiendas';
              });
            } else {
              Swal.fire('Error', res.message, 'error');
              btnSave.disabled = false;
            }
          })
          .catch(error => {
            Swal.fire('Error', 'Error de conexión: ' + error, 'error');
            btnSave.disabled = false;
          });
        }
      });
    });
  }
});
</script>