<?php require_once __DIR__ . '/detail.php'; ?>
<!-- CONTENEDOR PRINCIPAL -->
<div class="container-fluid py-3 flex-grow-1">
  
  <!-- SECCIÓN SUPERIOR: TÍTULO Y DESCRIPCIÓN -->
  <div class="row mb-3">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl p-3 p-md-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
          <div>
            <h4 class="font-weight-bolder text-dark mb-1">Gestión Integral de Boletería y Pasajes</h4>
            <p class="text-xs text-secondary mb-0">Administra la venta de pasajes, asignación de asientos en buses y control de manifiestos de pasajeros.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- LISTADO DE VENTAS (una fila por venta; los asientos/pasajeros se ven en el detalle) -->
  <div class="row">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
        
        <div class="card-header bg-white p-3 p-md-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
          <div>
            <h6 class="font-weight-bolder text-dark mb-0">Listado de Ventas de Pasajes</h6>
            <p class="text-xs text-secondary mb-0">Cada fila es una venta; use el ojo para ver sus asientos y pasajeros.</p>
          </div>
          <a href="<?= URL ?>/pasajes/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center gap-2 w-100 w-sm-auto justify-content-center">
            <i class="material-symbols-rounded text-sm">add_box</i>
            <span class="font-weight-bold">Vender Nuevo Pasaje</span>
          </a>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-pasajes" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Nº Venta / Comprador</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Ruta / Asientos</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Total & Pago</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($ventas)): ?>
                  <?php foreach ($ventas as $v): 
                    // Una venta puede tener boletos en distinto estado: si es uno solo se
                    // muestra tal cual, si no se marca como "Mixto".
                    $estados   = array_values(array_filter(array_map('trim', explode(',', $v['estados'] ?? ''))));
                    $estadoTxt = count($estados) === 1 ? $estados[0] : 'Mixto';
                    $estadoKey = strtolower($estadoTxt);

                    $badgeClass = 'bg-gradient-warning';
                    $textAsientoClass = 'text-warning';
                    if ($estadoKey === 'despachado') {
                        $badgeClass = 'bg-gradient-success';
                        $textAsientoClass = 'text-success';
                    } elseif ($estadoKey === 'asignado') {
                        $badgeClass = 'bg-gradient-info';
                        $textAsientoClass = 'text-info';
                    }

                    $totalAsientos = intval($v['total_asientos']);
                  ?>
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md <?= $badgeClass; ?> border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">confirmation_number</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#<?= htmlspecialchars($v['codigo_pasaje']); ?></h6>
                            <span class="text-xxs text-secondary font-weight-bold">Comprador: <span class="text-dark"><?= htmlspecialchars(trim($v['comprador'])); ?></span></span>
                            <span class="text-xxs text-secondary"><?= !empty($v['create_pasaje']) ? date('d/m/Y H:i', strtotime($v['create_pasaje'])) : ''; ?></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">
                            Ruta: <?= !empty($v['rutas']) ? htmlspecialchars($v['rutas']) : '<span class="text-warning">Sin turno (en espera)</span>'; ?>
                          </span>
                          <span class="text-xxs text-secondary font-weight-bold">
                            <?= $totalAsientos; ?> boleto(s) ·
                            Asientos:
                            <span class="<?= $textAsientoClass; ?> font-weight-bold">
                              <?= !empty($v['asientos']) ? htmlspecialchars($v['asientos']) : 'Por asignar'; ?>
                            </span>
                          </span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. <?= number_format($v['total_venta'], 2); ?></span>
                        <span class="d-block text-xxs text-success font-weight-bold"><?= htmlspecialchars($v['nombre_metodo_pago'] ?? 'Pagado'); ?></span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm <?= $badgeClass; ?> border-radius-pill px-3 py-1 font-weight-bold"><?= htmlspecialchars($estadoTxt); ?></span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-success p-2 mb-0" title="Ver Detalle" onclick="verDetallePasaje(<?= (int)$v['id_pasaje']; ?>)">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </button>
                          <button type="button" class="btn btn-link text-success p-2 mb-0" title="Imprimir Boleto" onclick="imprimirBoletoPasaje(<?= (int)$v['id_pasaje']; ?>)">
                            <i class="material-symbols-rounded text-sm">print</i>
                          </button>
                          <?php if ($estadoKey !== 'despachado'): ?>
                          <button type="button" class="btn btn-link text-danger p-2 mb-0" title="Anular Venta Completa" onclick="anularVentaPasaje(<?= (int)$v['id_pasaje']; ?>, '<?= htmlspecialchars($v['codigo_pasaje'], ENT_QUOTES); ?>')">
                            <i class="material-symbols-rounded text-sm">delete</i>
                          </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

</div>