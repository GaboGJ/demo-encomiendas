<?php
/**
 * views/dashboard/despachos/asignar.php
 * Variables: $turno, $pasajeros, $encAsignadas, $encPendientes, $resumen, $pasoInicial
 */
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$codigoTurno = 'T-' . str_pad($turno['id_turno'], 3, '0', STR_PAD_LEFT);
$hayCarga    = ($resumen['pasajeros'] + $resumen['guias']) > 0;
$rutaTxt     = $turno['ciudad_origen'] . ' ➔ ' . $turno['ciudad_destino'];
$unidadTxt   = 'Unidad ' . ($turno['numero_interno_vehiculo'] ?? 'S/N') . ' (' . ($turno['nombre_modelo'] ?? 'Vehículo') . ')';
$pasos = [1 => ['badge', 'Turno y Unidad'], 2 => ['event_seat', 'Pasajes'], 3 => ['inventory_2', 'Encomiendas'], 4 => ['print', 'Manifiesto']];
?>
<div class="container-fluid py-3 flex-grow-1">
  <div class="row">
    <div class="col-12 col-xl-11 mx-auto px-2 px-md-3">
      <div class="card border-0 shadow-sm border-radius-xl">

        <!-- HEADER + STEPPER -->
        <div class="card-header bg-white p-3">
          <div class="text-center text-md-start">
            <h5 class="font-weight-bolder text-dark mb-0">Asignación y Despacho del Turno #<?= $h($codigoTurno) ?></h5>
            <p class="text-xs text-secondary mb-0">Revise los pasajes vendidos, asigne las encomiendas de la ruta y despache la unidad</p>
          </div>

          <div class="d-flex justify-content-between align-items-center text-center px-0 px-md-4 mt-4 py-2">
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

        <div class="card-body p-3 p-md-4">

          <!-- PASO 1: TURNO Y UNIDAD (solo lectura) -->
          <div class="wizard-step" id="step-1">
            <div class="p-3 border border-radius-md bg-white mb-4">
              <h6 class="text-xs font-weight-bold text-uppercase text-success mb-3">
                <i class="material-symbols-rounded me-1 align-middle text-sm">info</i> Información Operativa del Turno
              </h6>
              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <div class="input-group input-group-outline is-filled my-2">
                    <label class="form-label">Nº de Turno</label>
                    <input type="text" class="form-control font-weight-bold" value="#<?= $h($codigoTurno) ?>" readonly>
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <div class="input-group input-group-outline is-filled my-2">
                    <label class="form-label">Ruta</label>
                    <input type="text" class="form-control font-weight-bold" value="<?= $h($rutaTxt) ?>" readonly>
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <div class="input-group input-group-outline is-filled my-2">
                    <label class="form-label">Estado / Fecha de salida</label>
                    <input type="text" class="form-control font-weight-bold text-info" value="<?= $h($turno['nombre_estado_turno']) ?> · <?= $h(date('d/m/Y', strtotime($turno['fecha_salida_turno'] ?? 'now'))) ?>" readonly>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-4">
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

          <!-- PASO 2: PASAJES (solo lectura) -->
          <div class="wizard-step d-none" id="step-2">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
              <div>
                <h6 class="text-xs font-weight-bold text-uppercase text-success mb-0">
                  <i class="material-symbols-rounded me-1 align-middle text-sm">event_seat</i> Pasajes vendidos para este turno
                </h6>
                <p class="text-xxs text-secondary mb-0"><?= $resumen['pasajeros'] ?> / <?= $resumen['capacidad'] ?> asientos ocupados · Bs. <?= number_format($resumen['monto_pasajes'], 2) ?></p>
              </div>
              <a href="<?= rtrim(URL, '/') ?>/pasajes/new" class="btn btn-sm bg-gradient-success mb-0 border-radius-md py-2 px-3 text-capitalize shadow-sm w-100 w-sm-auto">
                <i class="material-symbols-rounded text-sm me-1">add</i> Vender pasaje
              </a>
            </div>

            <div class="table-responsive p-3 border border-radius-md bg-white">
              <table class="table align-items-center mb-0 w-100" id="tablaPasajesTurno">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Asiento</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pasajero</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Venta</th>
                    <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">Monto</th>
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
            <?php if (!$pasajeros): ?>
              <p class="text-xs text-secondary text-center mt-3 mb-0">Todavía no hay pasajes vendidos para este turno.</p>
            <?php endif; ?>
          </div>

          <!-- PASO 3: ENCOMIENDAS (se asignan aquí) -->
          <div class="wizard-step d-none" id="step-3">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
              <div>
                <h6 class="text-xs font-weight-bold text-uppercase text-success mb-0">
                  <i class="material-symbols-rounded me-1 align-middle text-sm">inventory_2</i> Encomiendas asignadas al turno
                </h6>
                <p class="text-xxs text-secondary mb-0"><?= $resumen['guias'] ?> guía(s) · Bs. <?= number_format($resumen['monto_encomiendas'], 2) ?> · <?= count($encPendientes) ?> pendiente(s) hacia <?= $h($turno['ciudad_destino']) ?></p>
              </div>
              <button type="button" class="btn btn-sm bg-gradient-success mb-0 border-radius-md py-2 px-3 text-capitalize shadow-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#modalAsignarEncomiendas">
                <i class="material-symbols-rounded text-sm me-1">add</i> Agregar guías
              </button>
            </div>

            <div class="table-responsive p-3 border border-radius-md bg-white">
              <table class="table align-items-center mb-0 w-100" id="tablaEncomiendasTurno">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Guía</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Remitente ➔ Destinatario</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Bultos / Peso</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cobro</th>
                    <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Flete</th>
                    <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">Acción</th>
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
            <?php if (!$encAsignadas): ?>
              <p class="text-xs text-secondary text-center mt-3 mb-0">Aún no hay guías en este turno. Use "Agregar guías".</p>
            <?php endif; ?>
          </div>

          <!-- PASO 4: MANIFIESTO -->
          <div class="wizard-step d-none" id="step-4">
            <div class="d-flex align-items-center mb-3">
              <span class="material-symbols-rounded text-success me-2">print</span>
              <div>
                <h6 class="text-xs font-weight-bold text-uppercase text-success mb-0">Paso 4: Manifiesto General de Salida</h6>
                <p class="text-xxs text-secondary mb-0">Verifique el resumen. Al despachar ya no se podrán modificar pasajes ni guías.</p>
              </div>
            </div>

            <div class="card border border-radius-lg shadow-none bg-white p-3 p-md-4">
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
                <div class="d-flex align-items-center">
                  <span class="material-symbols-rounded text-success fs-2 me-2">departure_board</span>
                  <div>
                    <h5 class="font-weight-bolder text-dark mb-0">MANIFIESTO DE SALIDA #<?= $h($codigoTurno) ?></h5>
                    <p class="text-xxs text-secondary mb-0">TransExpress · Terminal <?= $h($turno['ciudad_origen']) ?></p>
                  </div>
                </div>
                <span class="badge <?= $hayCarga ? 'bg-gradient-info' : 'bg-gradient-warning' ?> text-sm px-3 py-2"><?= $hayCarga ? 'LISTO PARA DESPACHO' : 'SIN PASAJES NI CARGA' ?></span>
              </div>

              <div class="row g-3">
                <div class="col-12 col-sm-6">
                  <div class="p-2 bg-gray-100 border-radius-md h-100">
                    <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Ruta y Unidad</span>
                    <p class="text-xs font-weight-bold text-dark mb-1"><strong>Ruta:</strong> <?= $h($rutaTxt) ?></p>
                    <p class="text-xs font-weight-bold text-dark mb-0"><strong>Unidad:</strong> <?= $h($unidadTxt) ?></p>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <div class="p-2 bg-gray-100 border-radius-md h-100">
                    <span class="text-xxs font-weight-bolder text-uppercase text-secondary d-block mb-1">Conductor</span>
                    <p class="text-xs font-weight-bold text-dark mb-1"><strong>Chofer:</strong> <?= $h($turno['nombre_chofer'] ?? '-') ?></p>
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

<!-- MODAL: AGREGAR GUÍAS PENDIENTES -->
<div class="modal fade" id="modalAsignarEncomiendas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">
      <div class="modal-header bg-gradient-success text-white">
        <h5 class="modal-title text-white font-weight-bold">
          <i class="material-symbols-rounded me-1 align-middle">inventory_2</i> Guías pendientes hacia <?= $h($turno['ciudad_destino']) ?>
        </h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <?php if (!$encPendientes): ?>
          <p class="text-xs text-secondary text-center my-4">No hay guías pendientes para esta ruta. Registre una en <a href="<?= rtrim(URL, '/') ?>/encomiendas/new" class="text-success">Encomiendas</a>.</p>
        <?php else: ?>
          <input type="text" id="filtroPendientes" class="form-control border px-3 py-2 border-radius-md text-sm mb-3" placeholder="Filtrar por guía, remitente o destinatario..." autocomplete="off">
          <div class="table-responsive">
            <table class="table align-items-center mb-0 w-100" id="tablaPendientes">
              <thead>
                <tr>
                  <th style="width:40px"><input type="checkbox" class="form-check-input" id="chkTodas" title="Seleccionar visibles"></th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Guía</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Remitente ➔ Destinatario</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Bultos</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Flete</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($encPendientes as $e): ?>
                  <tr data-buscar="<?= $h(mb_strtolower($e['guia_encomienda'] . ' ' . $e['remitente'] . ' ' . $e['destinatario'], 'UTF-8')) ?>">
                    <td><input type="checkbox" class="form-check-input chk-pend" value="<?= (int)$e['id_encomienda'] ?>"></td>
                    <td class="text-xs font-weight-bold text-dark">#<?= $h($e['guia_encomienda']) ?></td>
                    <td class="text-xs text-dark"><?= $h($e['remitente']) ?> ➔ <?= $h($e['destinatario']) ?></td>
                    <td class="text-center text-xs"><?= (int)$e['total_bultos'] ?></td>
                    <td class="text-end text-xs font-weight-bold">Bs. <?= number_format($e['monto_encomienda'], 2) ?> <span class="badge badge-sm <?= $e['pagado'] ? 'bg-gradient-success' : 'bg-gradient-warning' ?>"><?= $e['pagado'] ? 'Pagado' : 'COD' ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer bg-gray-100">
        <span class="text-xs text-secondary me-auto" id="lblSeleccionadas">0 seleccionada(s)</span>
        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" data-bs-dismiss="modal">Cancelar</button>
        <?php if ($encPendientes): ?>
          <button type="button" class="btn btn-sm bg-gradient-success mb-0" id="btnAsignarSel" onclick="asignarSeleccionadas()">Asignar al turno</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const ID_TURNO = <?= (int)$turno['id_turno'] ?>;
  const HAY_CARGA = <?= $hayCarga ? 'true' : 'false' ?>;
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const TOTAL_PASOS = 4;
  const NOMBRES = ['Turno y Unidad', 'Pasajes', 'Encomiendas', 'Manifiesto'];
  let pasoActual = 1;
  const tablasInit = {};

  function iniciarTabla(id, placeholder) {
    if (tablasInit[id] || typeof inicializarDataTable !== 'function') return;
    // Sin filas: DataTables muestra su propio "No hay registros", no hace falta <tr> vacío
    inicializarDataTable('#' + id, { ordering: false, placeholder: placeholder, pageLength: 5 });
    tablasInit[id] = true;
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

    if (pasoActual === 2) iniciarTabla('tablaPasajesTurno', 'Buscar pasajero o asiento...');
    if (pasoActual === 3) iniciarTabla('tablaEncomiendasTurno', 'Buscar guía...');
    if (window.jQuery && $.fn.DataTable) {
      setTimeout(function () { $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust(); }, 50);
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

  /* ---- Modal: selección de guías pendientes ---- */
  function seleccionadas() {
    return Array.from(document.querySelectorAll('.chk-pend:checked')).map(function (c) { return c.value; });
  }
  function actualizarContador() {
    const l = document.getElementById('lblSeleccionadas');
    if (l) l.textContent = seleccionadas().length + ' seleccionada(s)';
  }
  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('chk-pend')) actualizarContador();
    if (e.target.id === 'chkTodas') {
      document.querySelectorAll('#tablaPendientes tbody tr').forEach(function (tr) {
        if (tr.style.display !== 'none') tr.querySelector('.chk-pend').checked = e.target.checked;
      });
      actualizarContador();
    }
  });
  const filtro = document.getElementById('filtroPendientes');
  if (filtro) filtro.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('#tablaPendientes tbody tr').forEach(function (tr) {
      tr.style.display = tr.dataset.buscar.indexOf(q) !== -1 ? '' : 'none';
    });
  });

  window.asignarSeleccionadas = function () {
    const ids = seleccionadas();
    if (!ids.length) { Swal.fire({ icon: 'warning', title: 'Sin selección', text: 'Marque al menos una guía.' }); return; }
    const btn = document.getElementById('btnAsignarSel'); btn.disabled = true;
    post('/despachos/asignarEncomiendas', { id_turno: ID_TURNO, ids_json: JSON.stringify(ids) })
      .then(function (res) {
        if (res.success) recargarEnPaso(3);
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
        .then(function (res) { res.success ? recargarEnPaso(3) : Swal.fire('Error', res.message, 'error'); })
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

  // El modal se cierra al recargar; al abrirlo limpiar selección previa
  const modalEl = document.getElementById('modalAsignarEncomiendas');
  if (modalEl) modalEl.addEventListener('show.bs.modal', function () {
    document.querySelectorAll('.chk-pend, #chkTodas').forEach(function (c) { c.checked = false; });
    actualizarContador();
  });

  document.addEventListener('DOMContentLoaded', function () {
    const inicial = <?= (int)$pasoInicial ?>;
    document.getElementById('lblPasoMovil').textContent = 'Paso 1 de ' + TOTAL_PASOS + ' · ' + NOMBRES[0];
    if (inicial !== 1) irAlPasoDirecto(inicial);
  });
})();
</script>