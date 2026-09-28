<div class="container-fluid py-3 py-md-4 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-11 mx-auto px-2 px-md-3">

      <div class="card border-0 shadow-sm border-radius-xl">

        <!-- HEADER STEPPER -->
        <div class="card-header bg-white p-3">
          <div class="text-center text-md-start">
            <h5 class="font-weight-bolder text-dark mb-0">Venta de Pasaje</h5>
            <p class="text-xs text-secondary mb-0">Seleccione el turno, registre al comprador, elija los asientos y a nombre de quién viaja cada uno</p>
          </div>

          <div class="stepper-pasos d-flex justify-content-between align-items-center text-center px-0 px-md-4 mt-3 py-2">
            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-1">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gradient-success text-white mb-1 shadow-none" onclick="irAlPaso(1)">
                <i class="material-symbols-rounded text-sm">departure_board</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-dark text-truncate">1. Turno y Comprador</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-2">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPaso(2)">
                <i class="material-symbols-rounded text-sm">event_seat</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-dark text-truncate">2. Asientos y Pasajeros</span>
            </div>

            <div class="border-top border-2 flex-fill opacity-3"></div>

            <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-3">
              <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-1 shadow-none" onclick="irAlPaso(3)">
                <i class="material-symbols-rounded text-sm">print</i>
              </button>
              <span class="d-none d-sm-block text-xs font-weight-bold text-dark text-truncate">3. Pago y Emisión</span>
            </div>
          </div>

          <!-- Solo en móvil: reemplaza a las etiquetas ocultas -->
          <p class="lbl-paso-movil d-sm-none text-success" id="lblPasoMovil">Paso 1 de 3 · Turno y Comprador</p>

          <hr class="horizontal dark my-0 opacity-2">
        </div>

        <!-- CUERPO DEL WIZARD -->
        <div class="card-body p-3 p-md-4">
          <form id="formVentaPasaje" onsubmit="return false;">

            <!-- PASO 1: TURNO Y COMPRADOR -->
            <div class="wizard-step" id="step-1">
              <div class="row g-3 g-md-4">

                <!-- Turno -->
                <div class="col-12 col-lg-6">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <div class="d-flex align-items-center mb-3">
                      <span class="material-symbols-rounded text-success me-2">directions_bus</span>
                      <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Chofer / Vehículo en Turno</h6>
                    </div>

                    <div class="mb-2 position-relative">
                      <label class="form-label text-xs font-weight-bold text-dark text-uppercase">Turno Disponible *</label>
                      <input type="text" id="buscadorTurno" class="form-control border px-3 py-2 border-radius-md text-sm"
                             placeholder="Escriba destino, chofer, placa o Nº de unidad..." autocomplete="off"
                             <?= empty($turnos_disponibles) ? 'disabled' : '' ?>>
                      <input type="hidden" id="selectTurno" value="">
                      <div id="listaTurnos" class="autocompletar-lista list-group position-absolute w-100 shadow-sm border-radius-md mt-1"></div>
                    </div>

                    <?php if (empty($turnos_disponibles)): ?>
                      <p class="text-xxs text-warning font-weight-bold mt-2 mb-0">
                        <i class="material-symbols-rounded text-xs align-middle">info</i>
                        No hay ningún turno activo en esta sucursal. Registre uno en
                        <a href="<?= rtrim(URL, '/') ?>/despachos/new" class="text-success">Despachos</a>
                        antes de vender pasajes.
                      </p>
                    <?php endif; ?>

                    <div id="infoTurnoSeleccionado" class="d-none mt-3 p-2 bg-gray-100 border-radius-md">
                      <div class="d-flex justify-content-between gap-2">
                        <span class="text-xxs text-secondary font-weight-bold">Destino:</span>
                        <span class="text-xxs font-weight-bold text-dark text-end texto-quiebra" id="lblInfoDestino">-</span>
                      </div>
                      <div class="d-flex justify-content-between gap-2">
                        <span class="text-xxs text-secondary font-weight-bold">Vehículo:</span>
                        <span class="text-xxs font-weight-bold text-dark text-end texto-quiebra" id="lblInfoVehiculo">-</span>
                      </div>
                      <div class="d-flex justify-content-between gap-2">
                        <span class="text-xxs text-secondary font-weight-bold">Chofer:</span>
                        <span class="text-xxs font-weight-bold text-dark text-end texto-quiebra" id="lblInfoChofer">-</span>
                      </div>
                      <div class="d-flex justify-content-between gap-2">
                        <span class="text-xxs text-secondary font-weight-bold">Precio por asiento:</span>
                        <span class="text-xxs font-weight-bold text-success" id="lblInfoPrecio">Bs. 0.00</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Comprador -->
                <div class="col-12 col-lg-6">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <div class="d-flex align-items-center mb-3">
                      <span class="material-symbols-rounded text-success me-2">person</span>
                      <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Datos del Comprador (quien paga)</h6>
                    </div>

                    <div class="row g-2">
                      <div class="col-12 col-sm-6">
                        <div class="input-group input-group-outline my-1">
                          <label class="form-label">Nº Carnet / C.I. *</label>
                          <input type="text" class="form-control" id="comprador_ci" onblur="buscarComprador()" required>
                        </div>
                      </div>
                      <div class="col-12 col-sm-6">
                        <div class="input-group input-group-outline my-1">
                          <label class="form-label">Celular de Contacto *</label>
                          <input type="text" class="form-control" id="comprador_celular" required>
                        </div>
                      </div>
                      <div class="col-12 col-md-4">
                        <div class="input-group input-group-outline my-1">
                          <label class="form-label">Nombres *</label>
                          <input type="text" class="form-control" id="comprador_nombres" required>
                        </div>
                      </div>
                      <div class="col-12 col-sm-6 col-md-4">
                        <div class="input-group input-group-outline my-1">
                          <label class="form-label">Apellido Paterno *</label>
                          <input type="text" class="form-control" id="comprador_paterno" required>
                        </div>
                      </div>
                      <div class="col-12 col-sm-6 col-md-4">
                        <div class="input-group input-group-outline my-1">
                          <label class="form-label">Apellido Materno</label>
                          <input type="text" class="form-control" id="comprador_materno">
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="input-group input-group-outline my-1">
                          <label class="form-label">Dirección / Ref.</label>
                          <input type="text" class="form-control" id="comprador_direccion">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <!-- PASO 2: PLANO Y PASAJEROS -->
            <div class="wizard-step d-none" id="step-2">
              <div class="row g-3">

                <!-- Plano -->
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
                      <p class="text-xxs text-secondary mb-2">Toque un asiento disponible para seleccionarlo.</p>

                      <div id="contenedorPlanoAsientos" class="vehicle-blueprint-horizontal mx-auto position-relative p-3 bg-white overflow-auto">
                        <div class="text-center text-xs text-secondary py-4">Cargando plano del vehículo...</div>
                      </div>

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

                    <div class="d-flex justify-content-between align-items-center p-3 bg-gray-100 border-radius-lg mt-3">
                      <span class="text-xs font-weight-bold text-dark" id="lblCantAsientos">0 asiento(s)</span>
                      <h5 class="text-success mb-0 font-weight-bolder" id="lblTotalPagarAsientos">Bs. 0.00</h5>
                    </div>
                  </div>
                </div>

                <!-- Pasajero por asiento -->
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

            <!-- PASO 3: PAGO Y EMISIÓN -->
            <div class="wizard-step d-none" id="step-3">
              <div class="row g-3">

                <div class="col-12 col-lg-6">
                  <div class="p-3 border border-radius-md bg-white h-100">
                    <h6 class="text-xs font-weight-bolder text-uppercase text-dark mb-3">Método de Pago</h6>

                    <div class="custom-nav-wrapper">
                      <ul class="custom-nav-pills d-flex flex-wrap gap-1" role="tablist" id="pillsMetodosPago">
                        <?php if (!empty($metodos_pago)): ?>
                          <?php foreach ($metodos_pago as $index => $metodo): ?>
                            <li class="nav-item flex-fill text-center">
                              <a class="nav-link text-xs py-2 px-2 <?= $index === 0 ? 'active' : '' ?>"
                                 href="javascript:;" role="tab"
                                 onclick="actualizarMetodoPago('<?= (int)$metodo['id_metodo_pago'] ?>')">
                                <?= htmlspecialchars($metodo['nombre_metodo_pago']) ?>
                              </a>
                            </li>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <li class="nav-item flex-fill text-center">
                            <a class="nav-link text-xs py-2 px-2 active" href="javascript:;" onclick="actualizarMetodoPago('1')">Efectivo</a>
                          </li>
                        <?php endif; ?>
                      </ul>
                      <div class="moving-tab"></div>
                    </div>
                    <input type="hidden" id="selectMetodoPago" value="<?= !empty($metodos_pago) ? (int)$metodos_pago[0]['id_metodo_pago'] : 1 ?>">

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

                <div class="col-12 col-lg-6">
                  <div class="card border border-radius-lg shadow-none bg-white p-3 position-relative overflow-hidden h-100">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
                      <div class="d-flex align-items-center">
                        <span class="material-symbols-rounded text-success fs-2 me-2">confirmation_number</span>
                        <div>
                          <h5 class="font-weight-bolder text-dark mb-0">TransExpress</h5>
                          <p class="text-xxs text-secondary mb-0">Previsualización del Boleto</p>
                        </div>
                      </div>
                      <span class="badge bg-gradient-success badge-wrap text-xs px-3 py-2">-- PENDIENTE DE EMISIÓN --</span>
                    </div>

                    <div class="p-2 bg-gray-100 border-radius-md mb-2">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Ruta / Turno</span>
                      <p class="text-xs font-weight-bold text-dark mb-0 texto-quiebra" id="resRuta">-</p>
                    </div>

                    <div class="p-2 border border-radius-md mb-2">
                      <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Comprador</span>
                      <p class="text-xs font-weight-bold text-dark mb-0 texto-quiebra" id="resComprador">-</p>
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
        <div class="wizard-footer card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/pasajes" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold w-sm-auto" id="btnCancel">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Cancelar
          </a>

          <button type="button" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold d-none w-sm-auto" id="btnPrev" onclick="cambiarPaso(-1)">
            <i class="material-symbols-rounded me-1 text-sm align-middle">arrow_back</i> Anterior
          </button>

          <div class="ms-sm-auto d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md px-4 font-weight-bold w-sm-auto" id="btnNext" onclick="cambiarPaso(1)">
              Siguiente <i class="material-symbols-rounded ms-1 text-sm align-middle">arrow_forward</i>
            </button>
            <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold d-none w-sm-auto" id="btnSave">
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
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">
      <div class="modal-header bg-gradient-success text-white">
        <h5 class="modal-title text-white font-weight-bold">
          <i class="material-symbols-rounded me-1 align-middle">person_pin</i> Pasajero del Asiento <span id="lblAsientoModal"></span>
        </h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3 p-md-4">
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

<!-- ÚNICO SCRIPT DE LA VISTA: solo pasa datos de PHP a pasajes-new.js -->
<script>
window.PASAJES_NEW = {
  baseUrl: '<?= rtrim(URL, "/") ?>',
  turnos: <?= json_encode(array_map(function ($t) {
      $capacidad = intval($t['total_asientos_modelo'] ?? 0);
      $ocupados  = intval($t['asientos_ocupados'] ?? 0);
      $lleno     = ($capacidad > 0 && $ocupados >= $capacidad);
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
  }, $turnos_disponibles ?? []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>
};
</script>