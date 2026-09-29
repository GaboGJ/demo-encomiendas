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

// Encomiendas: solo lo PAGADO en origen cuenta como cobrado. Las COD se
// cobran en destino, así que se muestran aparte y no se suman al total cobrado.
$encPagadas     = array_filter($encAsignadas, function ($e) { return !empty($e['pagado']); });
$encCod         = array_filter($encAsignadas, function ($e) { return empty($e['pagado']); });
$montoEncPagado = array_sum(array_column($encPagadas, 'monto_encomienda'));
$montoEncCod    = array_sum(array_column($encCod, 'monto_encomienda'));

// Clases comunes de cabecera/celda: iguales que en el resto de tablas del sistema
// para que el control "+" de DataTables Responsive se vea idéntico en todas.
$thBase  = 'text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 border-top border-bottom border-light';
$thFirst = $thBase . ' ps-4 ps-md-5 pe-3';
?>
<style>
  /* Modal de guías: aprovechar el ancho disponible en pantallas chicas */
  @media (max-width: 575.98px) {
    #modalAsignarEncomiendas .modal-dialog { max-width: calc(100% - 1rem); margin: .5rem auto; }
  }
  #modalAsignarEncomiendas .btn-sel { white-space: nowrap; min-width: 92px; }
</style>

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
                    <table class="table table-borderless align-items-center mb-0 w-100" id="tablaPasajesTurno">
                      <thead>
                        <tr>
                          <th data-priority="1" class="<?= $thFirst ?>">Asiento</th>
                          <th data-priority="2" class="<?= $thBase ?> px-3">Pasajero</th>
                          <th data-priority="4" class="<?= $thBase ?> px-3">Venta</th>
                          <th data-priority="3" class="<?= $thBase ?> text-end pe-4">Monto</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($pasajeros as $p): ?>
                          <tr>
                            <td class="py-3 ps-4 text-xs"><span class="badge bg-gradient-success">Asiento <?= $h($p['asiento'] ?? 'S/A') ?></span></td>
                            <td class="py-3 px-3 text-xs font-weight-bold text-dark"><?= $h($p['pasajero']) ?><span class="d-block text-xxs text-secondary">C.I. <?= $h($p['pasajero_ci']) ?></span></td>
                            <td class="py-3 px-3 text-xs text-secondary">#<?= $h($p['codigo_pasaje']) ?></td>
                            <td class="py-3 pe-4 text-end text-xs font-weight-bold text-dark">Bs. <?= number_format($p['precio_detalle_pasaje'], 2) ?></td>
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
                        <?= $resumen['guias'] ?> guía(s) · Pagadas: Bs. <?= number_format($montoEncPagado, 2) ?> · COD: Bs. <?= number_format($montoEncCod, 2) ?> · <?= count($encPendientes) ?> pendiente(s) hacia <?= $h($turno['ciudad_destino']) ?>
                      </p>
                    </div>
                    <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md py-2 px-3 text-capitalize shadow-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#modalAsignarEncomiendas">
                      <i class="material-symbols-rounded text-sm me-1">add</i> Agregar guías
                    </button>
                  </div>

                  <div class="table-responsive p-0">
                  <table class="table table-borderless align-items-center mb-0 w-100" id="tablaEncomiendasTurno">
  <thead>
    <tr>
      <th data-priority="1" class="<?= $thFirst ?>">Guía</th>
      <th data-priority="3" class="<?= $thBase ?> px-3">Remitente ➔ Destinatario</th>
      <th data-priority="2" class="<?= $thBase ?> px-3 text-end">Cobro</th>
      <th data-priority="1" class="<?= $thBase ?> text-end pe-4">Acción</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($encAsignadas as $e): ?>
      <tr>
        <td class="py-3 ps-4 ps-md-5 pe-3 text-xs font-weight-bold text-dark">#<?= $h($e['guia_encomienda']) ?></td>
        <td class="py-3 px-3 text-xs text-dark">
          <?= $h($e['remitente']) ?> ➔ <?= $h($e['destinatario']) ?>
          <?php if (!empty($e['declaracion_encomienda'])): ?>
            <span class="d-block text-xxs text-secondary"><?= $h($e['declaracion_encomienda']) ?></span>
          <?php endif; ?>
        </td>
        <td class="py-3 px-3 text-end text-xs font-weight-bold text-nowrap">
          Bs. <?= number_format($e['monto_encomienda'], 2) ?> 
          <span class="badge badge-sm <?= $e['pagado'] ? 'bg-gradient-success' : 'bg-gradient-warning' ?>">
            <?= $e['pagado'] ? 'Pagado' : 'COD' ?>
          </span>
        </td>
        <td class="py-3 pe-4 text-end">
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
                    <p class="text-xs font-weight-bold text-dark mb-1"><?= $resumen['guias'] ?> guía(s) consolidadas</p>
                    <p class="text-xxs text-success font-weight-bold mb-0">Cobrado en origen: Bs. <?= number_format($montoEncPagado, 2) ?> (<?= count($encPagadas) ?> guía(s))</p>
                    <p class="text-xxs text-warning font-weight-bold mb-0">Por cobrar en destino (COD): Bs. <?= number_format($montoEncCod, 2) ?> (<?= count($encCod) ?> guía(s))</p>
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
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">
      
      <!-- HEADER -->
      <div class="modal-header bg-gradient-success text-white p-3">
        <h5 class="modal-title text-white font-weight-bold fs-6 fs-md-5 d-flex align-items-center mb-0">
          <i class="material-symbols-rounded me-2">inventory_2</i>
          <span class="text-truncate">Guías pendientes hacia <?= $h($turno['ciudad_destino']) ?></span>
        </h5>
        <button type="button" class="btn-close text-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-2 p-sm-3">
        
        <!-- BARRA SUPERIOR DE ACCIÓN -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 px-1">
          <p class="text-xs text-secondary mb-0">Seleccione las encomiendas que desea asignar al turno</p>
          <button type="button" class="btn btn-sm btn-outline-success border-radius-md mb-0 d-inline-flex align-items-center justify-content-center gap-1 w-100 w-sm-auto" id="btnSelTodas">
            <i class="material-symbols-rounded text-sm">done_all</i>
            <span>Seleccionar todas</span>
          </button>
        </div>

        <!-- TABLA CONTENIDA -->
        <div class="table-responsive p-0">
          <table class="table table-borderless align-items-center mb-0 w-100" id="tablaPendientes">
            <thead>
              <tr>
                <th data-priority="1" class="<?= $thFirst ?>">Guía</th>
                <th data-priority="3" class="<?= $thBase ?> px-2 px-md-3">Remitente ➔ Destinatario</th>
                <th data-priority="2" class="<?= $thBase ?> px-2 px-md-3 text-end">Cobro</th>
                <th data-priority="1" class="<?= $thBase ?> text-end pe-3 pe-md-4">Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($encPendientes as $e): ?>
                <tr>
                  <td class="py-3 ps-4 ps-md-5 pe-3 text-xs font-weight-bold text-dark">
                    #<?= $h($e['guia_encomienda']) ?>
                  </td>
                  <td class="py-3 px-2 px-md-3 text-xs text-dark">
                    <div class="d-flex flex-column">
                      <span class="font-weight-bold text-dark text-wrap"><?= $h($e['remitente']) ?> ➔ <?= $h($e['destinatario']) ?></span>
                    </div>
                  </td>
                  <td class="py-3 px-2 px-md-3 text-end text-xs font-weight-bold text-nowrap">
                    <div class="d-flex flex-column align-items-end">
                      <span>Bs. <?= number_format($e['monto_encomienda'], 2) ?></span>
                      <span class="badge badge-sm <?= $e['pagado'] ? 'bg-gradient-success' : 'bg-gradient-warning' ?> mt-1">
                        <?= $e['pagado'] ? 'Pagado' : 'COD' ?>
                      </span>
                    </div>
                  </td>
                  <td class="py-3 pe-3 pe-md-4 text-end">
                    <button type="button" class="btn btn-sm btn-outline-success btn-sel mb-0 px-2 py-1 border-radius-md d-inline-flex align-items-center justify-content-center" data-id="<?= (int)$e['id_encomienda'] ?>"></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      </div>

      <!-- FOOTER -->
      <div class="modal-footer bg-gray-100 p-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
        <span class="text-xs font-weight-bold text-secondary text-center text-sm-start w-100 w-sm-auto" id="lblSeleccionadas">0 seleccionada(s)</span>
        <div class="d-flex gap-2 w-100 w-sm-auto justify-content-end">
          <button type="button" class="btn btn-sm bg-gradient-secondary border-radius-md mb-0 w-50 w-sm-auto" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-sm bg-gradient-success border-radius-md mb-0 w-50 w-sm-auto" id="btnAsignarSel" onclick="asignarSeleccionadas()">Asignar al turno</button>
        </div>
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
  const seleccion = new Set();

  // Siempre se usa el helper global inicializarDataTable() para que todas las tablas
  // (incluido el control "+" de Responsive) se vean igual.
  function iniciarTabla(id, placeholder, extra) {
    if (tablasInit[id] || typeof inicializarDataTable !== 'function') return null;
    const dt = inicializarDataTable('#' + id, Object.assign({ ordering: false, placeholder: placeholder, pageLength: 5 }, extra || {}));
    tablasInit[id] = true;
    return dt;
  }

  function ajustarTablas() {
    if (window.jQuery && $.fn.DataTable) {
      setTimeout(function () {
        const tablas = $.fn.dataTable.tables({ visible: true, api: true });
        tablas.columns.adjust();
        if ($.fn.dataTable.Responsive) tablas.responsive.recalc();
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

  /* ---- Modal: selección de guías pendientes ----
     La selección vive en un Set (por id) y cada fila tiene un botón Elegir/Elegida.
     Con paginación las filas de otras páginas no están en pantalla, por eso los
     nodos se leen siempre a través de la API de DataTables. */
  function botonesTodos() {
    return dtPendientes ? $(dtPendientes.rows().nodes()).find('.btn-sel') : $('#tablaPendientes .btn-sel');
  }
  function botonesFiltrados() {
    return dtPendientes ? $(dtPendientes.rows({ search: 'applied' }).nodes()).find('.btn-sel') : $('#tablaPendientes .btn-sel');
  }

  function pintarBoton(btn) {
    const activo = seleccion.has(btn.getAttribute('data-id'));
    btn.classList.toggle('bg-gradient-success', activo);
    btn.classList.toggle('text-white', activo);
    btn.classList.toggle('btn-outline-success', !activo);
    btn.innerHTML = activo
      ? '<i class="material-symbols-rounded text-sm me-1">check_circle</i>Elegida'
      : '<i class="material-symbols-rounded text-sm me-1">add_circle</i>Elegir';
  }

  function actualizarContador() {
    const l = document.getElementById('lblSeleccionadas');
    if (l) l.textContent = seleccion.size + ' seleccionada(s)';

    const filtrados = botonesFiltrados().map(function () { return this.getAttribute('data-id'); }).get();
    const todas = filtrados.length > 0 && filtrados.every(function (id) { return seleccion.has(id); });
    const lblTodas = document.querySelector('#btnSelTodas span');
    if (lblTodas) lblTodas.textContent = todas ? 'Quitar selección' : 'Seleccionar todas';
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-sel');
    if (btn) {
      const id = btn.getAttribute('data-id');
      if (seleccion.has(id)) seleccion.delete(id); else seleccion.add(id);
      pintarBoton(btn);
      actualizarContador();
      return;
    }

    if (e.target.closest('#btnSelTodas')) {
      const btns = botonesFiltrados();
      const ids = btns.map(function () { return this.getAttribute('data-id'); }).get();
      const todas = ids.length > 0 && ids.every(function (id) { return seleccion.has(id); });
      ids.forEach(function (id) { if (todas) seleccion.delete(id); else seleccion.add(id); });
      btns.each(function () { pintarBoton(this); });
      actualizarContador();
    }
  });

  window.asignarSeleccionadas = function () {
    const ids = Array.from(seleccion);
    if (!ids.length) { Swal.fire({ icon: 'warning', title: 'Sin selección', text: 'Elija al menos una guía.' }); return; }
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
    // Al abrir: limpiar selección previa y pintar todos los botones como "Elegir"
    modalEl.addEventListener('show.bs.modal', function () {
      seleccion.clear();
      botonesTodos().each(function () { pintarBoton(this); });
      actualizarContador();
    });
    // Ya visible el modal: recién ahora DataTables puede medir bien las columnas
    modalEl.addEventListener('shown.bs.modal', function () {
      if (!dtPendientes && typeof inicializarDataTable === 'function') {
        dtPendientes = inicializarDataTable('#tablaPendientes', {
          ordering: false,
          placeholder: 'Buscar guía, remitente o destinatario...',
          pageLength: 5
        });
        // Repintar los botones de las filas nuevas en cada redibujado (cambio de página/filtro)
        dtPendientes.on('draw', function () {
          botonesTodos().each(function () { pintarBoton(this); });
          actualizarContador();
        });
      } else if (dtPendientes) {
        dtPendientes.columns.adjust();
        if (dtPendientes.responsive) dtPendientes.responsive.recalc();
      }
      botonesTodos().each(function () { pintarBoton(this); });
      actualizarContador();
    });
  }

  // Estado inicial de los botones aunque el modal aún no se haya abierto
  $('#tablaPendientes .btn-sel').each(function () { pintarBoton(this); });

  document.addEventListener('DOMContentLoaded', function () {
    const inicial = Math.min(TOTAL_PASOS, Math.max(1, <?= (int)$pasoInicial ?>));
    document.getElementById('lblPasoMovil').textContent = 'Paso 1 de ' + TOTAL_PASOS + ' · ' + NOMBRES[0];
    if (inicial !== 1) irAlPasoDirecto(inicial);
  });
})();
</script>