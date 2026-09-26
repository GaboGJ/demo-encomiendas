<!-- TABLA PRINCIPAL DE DESPACHOS Y TURNOS -->
<div class="container-fluid py-3 flex-grow-1">
  <div class="row">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
        
        <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
          <div>
            <h5 class="font-weight-bolder text-dark mb-0">Control de Despachos y Turnos de Salida</h5>
            <p class="text-xs text-secondary mb-0">Administra los turnos de flotas, minibuses, vagonetas, asignación de choferes y consolidación de manifiestos</p>
          </div>
          <a href="<?= URL ?>/despachos/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto">
            <i class="material-symbols-rounded text-sm">add_circle</i>
            <span class="font-weight-bold">Nuevo Despacho</span>
          </a>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-despachos" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-5 pe-4 border-top border-bottom border-light">Nº Turno / Ruta</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Vehículo & Chofer</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Total Pasajes & Pasajeros</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Manifiesto & Carga</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($despachos)): ?>
                  <?php foreach ($despachos as $d): ?>
                    <?php 
                      $estadoNombre = strtolower($d['nombre_estado_turno'] ?? '');
                      $esEnTurno    = (strpos($estadoNombre, 'turno') !== false || $estadoNombre == 'pendiente');
                      $esDespachado = (strpos($estadoNombre, 'despachado') !== false);
                      
                      $badgeClass = 'bg-gradient-secondary';
                      if ($esDespachado) $badgeClass = 'bg-gradient-success';
                      elseif ($esEnTurno) $badgeClass = 'bg-gradient-info';
                    ?>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md <?= $badgeClass ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">
                              <?= $esDespachado ? 'departure_board' : 'schedule' ?>
                            </i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#T-<?= str_pad($d['id_turno'], 3, '0', STR_PAD_LEFT) ?></h6>
                            <span class="text-xxs text-secondary font-weight-bold">
                              Ruta: <span class="text-success font-weight-bold"><?= htmlspecialchars($d['ciudad_origen']) ?> ➔ <?= htmlspecialchars($d['ciudad_destino']) ?></span>
                            </span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">
                            Unidad <?= htmlspecialchars($d['numero_interno_vehiculo'] ?? 'S/N') ?> (<?= htmlspecialchars($d['nombre_modelo'] ?? 'Vehículo') ?>)
                          </span>
                          <span class="text-xxs text-secondary font-weight-bold">
                            Chofer: <span class="text-dark"><?= htmlspecialchars($d['nombre_chofer'] ?? 'Sin Chofer Asignado') ?></span>
                          </span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder <?= $esDespachado ? 'text-success' : 'text-dark' ?>">
                          Bs. <?= number_format($d['total_monto_pasajes'], 2) ?>
                        </span>
                        <span class="d-block text-xs font-weight-bold text-dark mt-1">
                          <?= $d['total_pasajeros'] ?> / <?= $d['total_asientos_modelo'] ?? 0 ?> 
                          <span class="text-xxs <?= $d['total_pasajeros'] >= ($d['total_asientos_modelo'] ?? 0) ? 'text-success' : 'text-secondary' ?> font-weight-bold">
                            (<?= $d['total_pasajeros'] >= ($d['total_asientos_modelo'] ?? 0) ? 'Cupo Completo' : 'Asientos Ocupados' ?>)
                          </span>
                        </span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder <?= $esDespachado ? 'text-success' : 'text-dark' ?>">
                          Bs. <?= number_format($d['total_monto_encomiendas'], 2) ?>
                        </span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">
                          <?= $d['total_guias'] ?> Guías Consolidadas
                        </span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm <?= $badgeClass ?> border-radius-pill px-3 py-1 font-weight-bold">
                          <?= htmlspecialchars($d['nombre_estado_turno']) ?>
                        </span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <?php if ($esEnTurno): ?>
                            <!-- Botón de asignación visible si está En Turno -->
                            <a href="<?= URL ?>despachos/asignar?id=<?= $d['id_turno'] ?>" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Asignar Pasajes y Encomiendas">
                              <i class="material-symbols-rounded text-sm">assignment</i>
                            </a>
                          <?php endif; ?>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Manifiesto">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Manifiesto">
                            <i class="material-symbols-rounded text-sm">print</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Cancelar Turno">
                            <i class="material-symbols-rounded text-sm">block</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center py-4 text-secondary text-sm">
                      No hay turnos ni despachos registrados para esta sucursal.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>