<?php
/**
 * views/dashboard/despachos/asign.php
 * Variables: $turno, $pasajeros, $encAsignadas, $encPendientes, $resumen, $pasoInicial
 *
 * Pasos (3):
 *   1. Turno y Unidad          (solo lectura)
 *   2. Pasajes y Encomiendas   (pasajes solo lectura | encomiendas se asignan/quitan)
 *   3. Manifiesto y Despacho
 */
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$codigoTurno = 'T-' . str_pad($turno['id_turno'], 3, '0', STR_PAD_LEFT);
$hayCarga    = ($resumen['pasajeros'] + $resumen['guias']) > 0;
$rutaTxt     = $turno['ciudad_origen'] . ' ➔ ' . $turno['ciudad_destino'];
$unidadTxt   = 'Unidad ' . ($turno['numero_interno_vehiculo'] ?? 'S/N') . ' (' . ($turno['nombre_modelo'] ?? 'Vehículo') . ')';
$pasos = [
    1 => ['badge',       'Turno y Unidad'],
    2 => ['inventory_2', 'Pasajes y Encomiendas'],
    3 => ['print',       'Manifiesto'],
];
?>
<div class="container-fluid py-2 py-md-3 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-11 mx-auto px-1 px-sm-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <!-- HEADER + STEPPER -->
        <div class="card-header bg-white p-3">
          <div class="text-center text-md-start">
            <h5 class="font-weight-bolder text-dark mb-0 fs-5 fs-md-4">Asignación y Despacho del Turno #<?= $h($codigoTurno) ?></h5>
            <p class="text-xs text-secondary mb-0">Revise los pasajes del turno, asigne las encomiendas de la ruta y despache la unidad</p>
          </div>

          <div class="stepper-pasos d-flex justify-content-between align-items-center text-center px-0 px-md-4 mt-3 mt-md-4 py-2">
            <?php foreach ($pasos as $n => $p): ?>
              <?php if ($n > 1): ?><div class="border-top border-2 flex-fill opacity-3"></div><?php endif; ?>
              <div class="step-indicator flex-fill min-width-0 px-1" id="indicator-step-<?= $n ?>">
                <button type="button" class="btn btn-icon-only btn-rounded <?= $n === 1 ? 'bg-gradient-success text-white' : 'bg-gray-200 text-secondary' ?> mb-1 shadow-none" onclick="irAlPasoDirecto(<?= $n ?>)">
                  <i class="material-symbols-rounded text-sm"><?= $p[0] ?></i>
                </button>
                <span class="d-none d-sm-block text-xs font-weight-bold <?= $n === 1 ? 'text-dark' : 'text-secondary' ?> text-truncate"><?= $n ?>. <?= $p[1] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <p class="lbl-paso-movil d-sm-none text-success" id="lblPasoMovil"></p>
          <hr class="horizontal dark my-0 opacity-2">
        </div>

        <div class="card-body p-2 p-sm-3 p-md-4">

          <!-- PASO 1: TURNO Y UNIDAD (solo lectura) -->
          <div class="wizard-step" id="step-1">
            <div class="p-3 border border-radius-md bg-white mb-4">
              <h6 class="text-xs font-weight-bold text-uppercase text-success mb-3">
                <i class="material-symbols-rounded me-1 align-middle text-sm">info</i> Información Operativa del Turno
              </h6>
              <div class="row g-2 g-md-3">
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="input-group input-group-outline is-filled my-2">
                    <label class="form-label">Nº de Turno</label>
                    <input type="text" class="form-control font-weight-bold" value="#<?= $h($codigoTurno) ?>" readonly>
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="input-group input-group-outline is-filled my-2">
                    <label class="form-label">Ruta</label>
                    <input type="text" class="form-control font-weight-bold" value="<?= $h($rutaTxt) ?>" readonly>
                  </div>
                </div>
                <div class="col-12 col-lg-4">
                  <div class="input-group input-group-outline is-filled my-2">
                    <label class="form-label">Estado / Fecha de salida</label>
                    <input type="text" class="form-control font-weight-bold text-info" value="<?= $h($turno['nombre_estado_turno']) ?> · <?= $h(date('d/m/Y', strtotime($turno['fecha_salida_turno'] ?? 'now'))) ?>" readonly>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-3 g-md-4">
              <div class="col-12 col-lg-6">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">directions_bus</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Vehículo Asignado</h6>
                  </div>
                  <div class="row g-2">
                    <div class="col-12 col-md-6">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Unidad / Móvil</label>
                        <input type="text" class="form-control" value="Unidad <?= $h($turno['numero_interno_vehiculo'] ?? 'S/N') ?>" readonly>
                      </div>
                    </div>
                    <div class="col-12 col-md-6">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Modelo (Capacidad)</label>
                        <input type="text" class="form-control" value="<?= $h(($turno['nombre_modelo'] ?? '-') . ' (' . $resumen['capacidad'] . ' asientos)') ?>" readonly>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Placa</label>
                        <input type="text" class="form-control" value="<?= $h($turno['placa_vehiculo'] ?? '-') ?>" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-6">
                <div class="p-3 border border-radius-md bg-white h-100">
                  <div class="d-flex align-items-center mb-3">
                    <span class="material-symbols-rounded text-success me-2">badge</span>
                    <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark">Tripulación a Cargo</h6>
                  </div>
                  <div class="row g-2">
                    <div class="col-12">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Chofer</label>
                        <input type="text" class="form-control" value="<?= $h($turno['nombre_chofer'] ?? 'Sin chofer') ?>" readonly>
                      </div>
                    </div>
                    <div class="col-12 col-md-6">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Licencia</label>
                        <input type="text" class="form-control" value="<?= $h($turno['licencia_chofer'] ?? '-') ?>" readonly>
                      </div>
                    </div>
                    <div class="col-12 col-md-6">
                      <div class="input-group input-group-outline is-filled my-2">
                        <label class="form-label">Celular</label>
                        <input type="text" class="form-control" value="<?= $h($turno['celular_chofer'] ?? '-') ?>" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PASO 2: PASAJES (solo lectura) + ENCOMIENDAS (se asignan aquí), lado a lado -->
          <div class="wizard-step d-none" id="step-2">
            <div class="row g-3">

              <!-- IZQUIERDA: PASAJES -->
              <div class="col-12 col-xl-6">
                <div class="p-2 p-md-3 border border-radius-md bg-white h-100">
                  <div class="mb-3">
                    <h6 class="text-xs font-weight-bold text-uppercase text-success mb-0">
                      <i class="material-symbols-rounded me-1 align-middle text-sm">event_seat</i> Pasajes del turno
                    </h6>
                    <p class="text-xxs text-secondary mb-0">
                      <?= $resumen['pasajeros'] ?> / <?= $resumen['capacidad'] ?> asientos ocupados · Bs. <?= number_format($resumen['monto_pasajes'], 2) ?>
                    </p>
                  </div>

                  <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0 w-100" id="tablaPasajesTurno">
                      <thead>
                        <tr>
                          <th data-priority="1" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Asiento</th>
                          <th data-priority="2" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pasajero</th>
                          <th data-priority="4" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Venta</th>
                          <th data-priority="3" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">Monto</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($pasajeros as $p): ?>
                          <tr>
                            <td class="ps-2"><span class="badge bg-gradient-success">Asiento <?= $h($p['asiento'] ?? 'S/A') ?></span></td>
                            <td class="text-xs font-weight-bold text-dark"><?= $h($p['pasajero']) ?><span class="d-block text-xxs text-secondary">C.I. <?= $h($p['pasajero_ci']) ?></span></td>
                            <td class="text-xs text-secondary">#<?= $h($p['codigo_pasaje']) ?></td>
                            <td class="text-end text-xs font-weight-bold text-dark pe-3">Bs. <?= number_format($p['precio_detalle_pasaje'], 2) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- DERECHA: ENCOMIENDAS -->
              <div class="col-12 col-xl-6">
                <div class="p-2 p-md-3 border border-radius-md bg-white h-100">
                  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                    <div>
                      <h6 class="text-xs font-weight-bold text-uppercase text-success mb-0">
                        <i class="material-symbols-rounded me-1 align-middle text-sm">inventory_2</i> Encomiendas asignadas
                      </h6>
                      <p class="text-xxs text-secondary mb-0">
                        <?= $resumen['guias'] ?> guía(s) · Bs. <?= number_format($resumen['monto_encomiendas'], 2) ?> · <?= count($encPendientes) ?> pendiente(s) hacia <?= $h($turno['ciudad_destino']) ?>
                      </p>
                    </div>
                    <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md py-2 px-3 text-capitalize shadow-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#modalAsignarEncomiendas">
                      <i class="material-symbols-rounded text-sm me-1">add</i> Agregar guías
                    </button>
                  </div>

                  <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0 w-100" id="tablaEncomiendasTurno">
                      <thead>
                        <tr>
                          <th data-priority="1" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Guía</th>
                          <th data-priority="3" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Remitente ➔ Destinatario</th>
                          <th data-priority="5" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Bultos / Peso</th>
                          <th data-priority="4" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cobro</th>
                          <th data-priority="2" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Flete</th>
                          <th data-priority="1" class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">Acción</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($encAsignadas as $e): ?>
                          <tr>
                            <td class="ps-2 text-xs font-weight-bold text-dark">#<?= $h($e['guia_encomienda']) ?></td>
                            <td class="text-xs text-dark"><?= $h($e['remitente']) ?> ➔ <?= $h($e['destinatario']) ?>
                              <?php if (!empty($e['declaracion_encomienda'])): ?><span class="d-block text-xxs text-secondary"><?= $h($e['declaracion_encomienda']) ?></span><?php endif; ?></td>
                            <td class="text-center text-xs"><?= (int)$e['total_bultos'] ?> bulto(s)<span class="d-block text-xxs text-secondary"><?= $e['peso_total'] > 0 ? number_format($e['peso_total'], 1) . ' Kg' : 'Sin peso' ?></span></td>
                            <td class="text-center"><span class="badge badge-sm <?= $e['pagado'] ? 'bg-gradient-success' : 'bg-gradient-warning' ?>"><?= $e['pagado'] ? 'Pagado' : 'COD' ?></span></td>
                            <td class="text-end text-xs font-weight-bold text-dark">Bs. <?= number_format($e['monto_encomienda'], 2) ?></td>
                            <td class="text-end pe-3">
                              <button type="button" class="btn btn-link text-danger p-1 m-0" title="Quitar del turno" onclick="quitarEncomienda(<?= (int)$e['id_encomienda'] ?>, '<?= $h($e['guia_encomienda']) ?>')">
                                <i class="material-symbols-rounded text-sm">remove_circle</i>
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- PASO 3: MANIFIESTO -->
          <div class="wizard-step d-none" id="step-3">
            <div class="d-flex align-items-center mb-3">
              <span class="material-symbols-rounded text-success me-2">print</span>
              <div>
                <h6 class="text-xs font-weight-bold text-uppercase text-success mb-0">Paso 3: Manifiesto General de Salida</h6>
                <p class="text-xxs text-secondary mb-0">Verifique el resumen. Al despachar ya no se podrán modificar pasajes ni guías.</p>
              </div>
            </div>

            <div class="card border border-radius-lg shadow-none bg-white p-3 p-md-4">
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
                <div class="d-flex align-items-center">
                  <span class="material-symbols-rounded text-success fs-2 me-2">departure_board</span>
                  <div>
                    <h5 class="font-weight-bolder text-dark mb-0 fs-6 fs-md-5">MANIFIESTO DE SALIDA #<?= $h($codigoTurno) ?></h5>
                    <p class="text-xxs text-secondary mb-0">TransExpress · Terminal <?= $h($turno['ciudad_origen']) ?></p>
                  </div>
                </div>
                <span class="badge badge-wrap <?= $hayCarga ? 'bg-gradient-info' : 'bg-gradient-warning' ?> text-xs px-3 py-2"><?= $hayCarga ? 'LISTO PARA DESPACHO' : 'SIN PASAJES NI CARGA' ?></span>
              </div>

              <div class="row g-3">
                <div class="col-12 col-sm-6">
                  <div class="p-2 bg-gray-100 border-radius-md h-100">
                    <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Ruta y Unidad</span>
                    <p class="text-xs font-weight-bold text-dark mb-1 texto-quiebra"><strong>Ruta:</strong> <?= $h($rutaTxt) ?></p>
                    <p class="text-xs font-weight-bold text-dark mb-0 texto-quiebra"><strong>Unidad:</strong> <?= $h($unidadTxt) ?></p>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <div class="p-2 bg-gray-100 border-radius-md h-100">
                    <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Conductor</span>
                    <p class="text-xs font-weight-bold text-dark mb-1 texto-quiebra"><strong>Chofer:</strong> <?= $h($turno['nombre_chofer'] ?? '-') ?></p>
                    <p class="text-xs font-weight-bold text-dark mb-0"><strong>Licencia:</strong> <?= $h($turno['licencia_chofer'] ?? '-') ?></p>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <div class="p-2 border border-radius-md h-100">
                    <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Pasajeros</span>
                    <p class="text-xs font-weight-bold text-dark mb-0"><?= $resumen['pasajeros'] ?> ocupados / <?= $resumen['capacidad'] ?> asientos</p>
                    <p class="text-xxs text-success font-weight-bold mb-0">Recaudación pasajes: Bs. <?= number_format($resumen['monto_pasajes'], 2) ?></p>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <div class="p-2 border border-radius-md h-100">
                    <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Encomiendas</span>
                    <p class="text-xs font-weight-bold text-dark mb-0"><?= $resumen['guias'] ?> guía(s) consolidadas</p>
                    <p class="text-xxs text-success font-weight-bold mb-0">Fletes: Bs. <?= number_format($resumen['monto_encomiendas'], 2) ?></p>
                  </div>
                </div>
              </div>
              <p class="text-xxs text-secondary mt-3 mb-0">Al confirmar, la hora de salida quedará registrada y el turno pasará a <strong>Despachado</strong>.</p>
            </div>
          </div>

        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <!-- FOOTER -->
        <div class="wizard-footer card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/despachos" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold w-sm-auto" id="btnCancel">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Volver
          </a>
          <button type="button" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold d-none w-sm-auto" id="btnPrev" onclick="cambiarPaso(-1)">
            <i class="material-symbols-rounded me-1 text-sm align-middle">arrow_back</i> Anterior
          </button>
          <div class="ms-sm-auto d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
            <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md px-4 font-weight-bold w-sm-auto" id="btnNext" onclick="cambiarPaso(1)">
              Siguiente <i class="material-symbols-rounded ms-1 text-sm align-middle">arrow_forward</i>
            </button>
            <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold d-none w-sm-auto" id="btnSave" onclick="despacharTurno()">
              <i class="material-symbols-rounded me-1 text-sm align-middle">departure_board</i> Despachar Turno
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- MODAL: AGREGAR GUÍAS PENDIENTES (con DataTables) -->
<div class="modal fade" id="modalAsignarEncomiendas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-fullscreen-sm-down modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">
      <div class="modal-header bg-gradient-success text-white">
        <h5 class="modal-title text-white font-weight-bold fs-6 fs-md-5">
          <i class="material-symbols-rounded me-1 align-middle">inventory_2</i> Guías pendientes hacia <?= $h($turno['ciudad_destino']) ?>
        </h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-2 p-md-3">
        <div class="d-flex align-items-center gap-2 px-2 pb-2">
          <div class="form-check mb-0">
            <input type="checkbox" class="form-check-input" id="chkTodas">
            <label class="form-check-label text-xs font-weight-bold text-secondary" for="chkTodas">Seleccionar todas las filtradas</label>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table align-items-center mb-0 w-100" id="tablaPendientes">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Guía</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Remitente ➔ Destinatario</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Bultos</th>
                <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">Flete</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($encPendientes as $e): ?>
                <tr>
                  <td class="ps-3 text-xs font-weight-bold text-dark text-nowrap">
                    <div class="form-check mb-0 d-flex align-items-center gap-2">
                      <input type="checkbox" class="form-check-input chk-pend mt-0" value="<?= (int)$e['id_encomienda'] ?>" id="chk-<?= (int)$e['id_encomienda'] ?>">
                      <label class="form-check-label mb-0" for="chk-<?= (int)$e['id_encomienda'] ?>">#<?= $h($e['guia_encomienda']) ?></label>
                    </div>
                  </td>
                  <td class="text-xs text-dark"><?= $h($e['remitente']) ?> ➔ <?= $h($e['destinatario']) ?></td>
                  <td class="text-center text-xs"><?= (int)$e['total_bultos'] ?></td>
                  <td class="text-end text-xs font-weight-bold pe-3 text-nowrap">Bs. <?= number_format($e['monto_encomienda'], 2) ?> <span class="badge badge-sm <?= $e['pagado'] ? 'bg-gradient-success' : 'bg-gradient-warning' ?>"><?= $e['pagado'] ? 'Pagado' : 'COD' ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-gray-100 flex-wrap gap-2">
        <span class="text-xs text-secondary me-auto" id="lblSeleccionadas">0 seleccionada(s)</span>
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-sm bg-gradient-success mb-0" id="btnAsignarSel" onclick="asignarSeleccionadas()">Asignar al turno</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const ID_TURNO = <?= (int)$turno['id_turno'] ?>;
  const HAY_CARGA = <?= $hayCarga ? 'true' : 'false' ?>;
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const TOTAL_PASOS = 3;
  const NOMBRES = ['Turno y Unidad', 'Pasajes y Encomiendas', 'Manifiesto'];
  let pasoActual = 1;
  const tablasInit = {};
  let dtPendientes = null;

  // Sin filas, DataTables muestra su propio "No hay registros disponibles"
  function iniciarTabla(id, placeholder, extra) {
    if (tablasInit[id] || typeof inicializarDataTable !== 'function') return null;
    const dt = inicializarDataTable('#' + id, Object.assign({ ordering: false, placeholder: placeholder, pageLength: 5 }, extra || {}));
    tablasInit[id] = true;
    return dt;
  }

  function ajustarTablas() {
    if (window.jQuery && $.fn.DataTable) {
      setTimeout(function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        if ($.fn.dataTable.Responsive) $.fn.dataTable.tables({ visible: true, api: true }).responsive.recalc();
      }, 60);
    }
  }

  window.irAlPasoDirecto = function (paso) {
    document.getElementById('step-' + pasoActual).classList.add('d-none');
    pasoActual = paso;
    document.getElementById('step-' + pasoActual).classList.remove('d-none');

    document.querySelectorAll('.step-indicator').forEach(function (ind, i) {
      const btn = ind.querySelector('button'), lbl = ind.querySelector('span:not(.material-symbols-rounded)');
      const activo = (i + 1) <= pasoActual;
      btn.className = 'btn btn-icon-only btn-rounded ' + (activo ? 'bg-gradient-success text-white' : 'bg-gray-200 text-secondary') + ' mb-1 shadow-none';
      if (lbl) { lbl.classList.toggle('text-dark', activo); lbl.classList.toggle('text-secondary', !activo); }
    });

    document.getElementById('lblPasoMovil').textContent = 'Paso ' + pasoActual + ' de ' + TOTAL_PASOS + ' · ' + NOMBRES[pasoActual - 1];
    document.getElementById('btnCancel').classList.toggle('d-none', pasoActual !== 1);
    document.getElementById('btnPrev').classList.toggle('d-none', pasoActual === 1);
    document.getElementById('btnNext').classList.toggle('d-none', pasoActual === TOTAL_PASOS);
    document.getElementById('btnSave').classList.toggle('d-none', pasoActual !== TOTAL_PASOS);

    if (pasoActual === 2) {
      iniciarTabla('tablaPasajesTurno', 'Buscar pasajero o asiento...');
      iniciarTabla('tablaEncomiendasTurno', 'Buscar guía...');
    }
    ajustarTablas();

    // En móvil, volver al inicio del asistente al cambiar de paso
    if (window.innerWidth < 768) {
      const top = document.querySelector('.stepper-pasos');
      if (top) top.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  window.cambiarPaso = function (dir) {
    const n = pasoActual + dir;
    if (n >= 1 && n <= TOTAL_PASOS) irAlPasoDirecto(n);
  };

  function post(url, obj) {
    const fd = new FormData();
    Object.keys(obj).forEach(function (k) { fd.append(k, obj[k]); });
    return fetch(baseUrl + url, { method: 'POST', body: fd }).then(function (r) { return r.json(); });
  }
  function recargarEnPaso(paso) {
    window.location.href = baseUrl + '/despachos/asign?id=' + ID_TURNO + '&paso=' + paso;
  }
  function errorServidor() { Swal.fire('Error', 'Ocurrió un error en el servidor', 'error'); }

  /* ---- Modal: selección de guías pendientes (DataTables) ----
     Con paginación, las filas de otras páginas no están en pantalla, por eso
     se leen siempre a través de la API de DataTables (dt.$ / dt.rows). */
  function checksSeleccionados() {
    return dtPendientes ? dtPendientes.$('.chk-pend:checked') : $('.chk-pend:checked');
  }
  function seleccionadas() {
    return checksSeleccionados().map(function () { return this.value; }).get();
  }
  function actualizarContador() {
    const l = document.getElementById('lblSeleccionadas');
    if (l) l.textContent = seleccionadas().length + ' seleccionada(s)';
  }

  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('chk-pend')) {
      actualizarContador();
    }
    if (e.target.id === 'chkTodas' && dtPendientes) {
      const marcado = e.target.checked;
      // Todas las filas que cumplen el filtro actual, incluso de otras páginas
      $(dtPendientes.rows({ search: 'applied' }).nodes()).find('.chk-pend').prop('checked', marcado);
      actualizarContador();
    }
  });

  window.asignarSeleccionadas = function () {
    const ids = seleccionadas();
    if (!ids.length) { Swal.fire({ icon: 'warning', title: 'Sin selección', text: 'Marque al menos una guía.' }); return; }
    const btn = document.getElementById('btnAsignarSel'); btn.disabled = true;
    post('/despachos/asignarEncomiendas', { id_turno: ID_TURNO, ids_json: JSON.stringify(ids) })
      .then(function (res) {
        if (res.success) recargarEnPaso(2);
        else { Swal.fire('No se pudo asignar', res.message, 'error'); btn.disabled = false; }
      }).catch(function () { errorServidor(); btn.disabled = false; });
  };

  window.quitarEncomienda = function (idEnc, guia) {
    Swal.fire({
      title: '¿Quitar la guía #' + guia + '?', text: 'Volverá a quedar pendiente, sin turno.',
      icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, quitar', cancelButtonText: 'Cancelar', confirmButtonColor: '#f5365c'
    }).then(function (r) {
      if (!r.isConfirmed) return;
      post('/despachos/quitarEncomienda', { id_turno: ID_TURNO, id_encomienda: idEnc })
        .then(function (res) { res.success ? recargarEnPaso(2) : Swal.fire('Error', res.message, 'error'); })
        .catch(errorServidor);
    });
  };

  window.despacharTurno = function () {
    if (!HAY_CARGA) { Swal.fire({ icon: 'warning', title: 'Nada que despachar', text: 'El turno no tiene pasajes ni encomiendas.' }); return; }
    Swal.fire({
      title: '¿Despachar el turno #<?= $h($codigoTurno) ?>?',
      text: 'Se registrará la hora de salida y ya no podrá modificar pasajes ni guías.',
      icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, despachar', cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (!r.isConfirmed) return;
      const btn = document.getElementById('btnSave'); btn.disabled = true;
      post('/despachos/despachar', { id_turno: ID_TURNO })
        .then(function (res) {
          // El mensaje de éxito ya quedó en Flash (servidor) y sale en el listado
          if (res.success) window.location.href = baseUrl + '/despachos';
          else { Swal.fire('No se pudo despachar', res.message, 'error'); btn.disabled = false; }
        }).catch(function () { errorServidor(); btn.disabled = false; });
    });
  };

  const modalEl = document.getElementById('modalAsignarEncomiendas');
  if (modalEl) {
    // Al abrir: limpiar selección previa
    modalEl.addEventListener('show.bs.modal', function () {
      $('.chk-pend, #chkTodas').prop('checked', false);
      if (dtPendientes) dtPendientes.$('.chk-pend').prop('checked', false);
      actualizarContador();
    });
    // Ya visible el modal: recién ahora DataTables puede medir bien las columnas
    modalEl.addEventListener('shown.bs.modal', function () {
      if (!dtPendientes && typeof inicializarDataTable === 'function') {
        dtPendientes = inicializarDataTable('#tablaPendientes', {
          ordering: false,
          placeholder: 'Buscar guía, remitente o destinatario...',
          pageLength: 5,
          responsive: false // el wrapper .table-responsive da scroll horizontal; así no choca con los checkboxes
        });
      } else if (dtPendientes) {
        dtPendientes.columns.adjust();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const inicial = Math.min(TOTAL_PASOS, Math.max(1, <?= (int)$pasoInicial ?>));
    document.getElementById('lblPasoMovil').textContent = 'Paso 1 de ' + TOTAL_PASOS + ' · ' + NOMBRES[0];
    if (inicial !== 1) irAlPasoDirecto(inicial);
  });
})();
</script>