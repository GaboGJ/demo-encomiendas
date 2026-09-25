<!-- CONTENEDOR PRINCIPAL -->
<div class="container-fluid py-4 flex-grow-1">

    <!-- SECCIÓN SUPERIOR UNIFICADA (TÍTULO, DESCRIPCIÓN Y TABS) -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-radius-xl p-3 p-md-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <h4 class="font-weight-bolder text-dark mb-1">Gestión Integral de Encomiendas</h4>
                        <p class="text-xs text-secondary mb-0">Administra de manera centralizada el flujo de salidas locales y la recepción de carga proveniente de otras sucursales.</p>
                    </div>

                    <div class="col-12 col-md-5 col-lg-4 d-flex justify-content-center justify-content-md-end">
                        <div class="custom-nav-wrapper w-100 w-md-auto">
                            <ul class="nav nav-pills nav-fill p-1 bg-gray-100 border-radius-xl flex-row flex-nowrap" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link mb-0 px-2 px-md-3 py-2 active font-weight-bold d-flex align-items-center justify-content-center gap-1 gap-md-2 text-xxs" data-bs-toggle="tab" href="#tab-envios" role="tab" aria-selected="true">
                                        <i class="material-symbols-rounded">inventory_2</i> Envíos (Salidas)
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link mb-0 px-2 px-md-3 py-2 font-weight-bold d-flex align-items-center justify-content-center gap-1 gap-md-2 text-xxs" data-bs-toggle="tab" href="#tab-llegadas" role="tab" aria-selected="false">
                                        <i class="material-symbols-rounded">markunread_mailbox</i> Llegadas (Recepción)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="tab-content">

        <!-- TAB 1: ENVÍOS (SALIDAS) -->
        <div class="tab-pane fade show active" id="tab-envios" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">

                        <div class="card-header bg-white p-3 p-md-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                            <div>
                                <h6 class="font-weight-bolder text-dark mb-0">Listado de Envíos Registrados</h6>
                                <p class="text-xs text-secondary mb-0">Guías de encomiendas emitidas en oficina local para transporte terrestre.</p>
                            </div>
                            <a href="<?= URL ?>/encomiendas/new" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center gap-2 w-100 w-sm-auto justify-content-center">
                                <i class="material-symbols-rounded text-sm">add_box</i>
                                <span class="font-weight-bold">Nueva Encomienda</span>
                            </a>
                        </div>

                        <hr class="horizontal dark my-0 opacity-2">

                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table id="datatable-encomiendas" class="table table-borderless align-items-center mb-0 w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Nº Guía / Remitente</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Destinatario / Ruta</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Monto & Pago</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                                            <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($envios)): ?>
                                            <?php foreach ($envios as $envio): ?>
                                                <?php 
                                                    $estado = strtolower($envio['nombre_estado_encomienda']);
                                                    $badgeClass = 'bg-gradient-secondary';
                                                    if ($estado === 'asignado') $badgeClass = 'bg-gradient-info';
                                                    elseif ($estado === 'entregado' || $estado === 'enviado') $badgeClass = 'bg-gradient-success';
                                                ?>
                                                <tr>
                                                    <td class="py-3 ps-4 ps-md-5 pe-4">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                                <i class="material-symbols-rounded">inventory_2</i>
                                                            </div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">#<?= htmlspecialchars($envio['guia_encomienda']) ?></h6>
                                                                <span class="text-xxs text-secondary font-weight-bold">Remitente: <span class="text-dark"><?= htmlspecialchars($envio['remitente']) ?></span></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 px-3 align-middle">
                                                        <div class="d-flex flex-column">
                                                            <span class="text-xs font-weight-bold text-dark">Destinatario: <?= htmlspecialchars($envio['destinatario']) ?></span>
                                                            <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold"><?= htmlspecialchars($envio['ciudad_origen']) ?> ➔ <?= htmlspecialchars($envio['ciudad_destino']) ?></span></span>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-center py-3 px-3">
                                                        <span class="text-sm font-weight-bolder <?= $envio['estado_pago_encomienda'] ? 'text-success' : 'text-dark' ?>">
                                                            Bs. <?= number_format($envio['monto_encomienda'], 2) ?>
                                                        </span>
                                                        <?php if ($envio['estado_pago_encomienda']): ?>
                                                            <span class="d-block text-xxs text-success font-weight-bold">Pagado en Origen</span>
                                                        <?php else: ?>
                                                            <span class="d-block text-xxs text-danger font-weight-bold">COD (Por Cobrar)</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle text-center py-3 px-3">
                                                        <span class="badge badge-sm <?= $badgeClass ?> border-radius-pill px-3 py-1 font-weight-bold">
                                                            <?= htmlspecialchars($envio['nombre_estado_encomienda']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                                            <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                            <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Guía"><i class="material-symbols-rounded text-sm">print</i></a>
                                                            <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Guía"><i class="material-symbols-rounded text-sm">delete</i></a>
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

        <!-- TAB 2: LLEGADAS (RECEPCIÓN DESDE OTRAS SUCURSALES) -->
        <div class="tab-pane fade" id="tab-llegadas" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">

                        <div class="card-header bg-white p-3 p-md-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                            <div>
                                <h6 class="font-weight-bolder text-dark mb-0">Listado de Encomiendas Recibidas</h6>
                                <p class="text-xs text-secondary mb-0">Carga arribada desde otras ciudades lista para entrega o despacho local.</p>
                            </div>
                            <a href="<?= URL ?>/encomiendas/recepcion" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center gap-2 w-100 w-sm-auto justify-content-center">
                                <i class="material-symbols-rounded text-sm">move_to_inbox</i>
                                <span class="font-weight-bold">Nueva Recepción</span>
                            </a>
                        </div>

                        <hr class="horizontal dark my-0 opacity-2">

                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table id="datatable-llegadas" class="table table-borderless align-items-center mb-0 w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Nº Guía / Origen</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Remitente / Destinatario</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Cobro / Estado</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado Recepción</th>
                                            <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones / Entrega</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($llegadas)): ?>
                                            <?php foreach ($llegadas as $llegada): ?>
                                                <?php 
                                                    $estadoRec = strtolower($llegada['nombre_estado_encomienda']);
                                                    $badgeClassRec = ($estadoRec === 'entregado') ? 'bg-gradient-success' : 'bg-gradient-info';
                                                ?>
                                                <tr>
                                                    <td class="py-3 ps-4 ps-md-5 pe-4">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                                <i class="material-symbols-rounded">move_to_inbox</i>
                                                            </div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">#<?= htmlspecialchars($llegada['guia_encomienda']) ?></h6>
                                                                <span class="text-xxs text-secondary font-weight-bold">Origen: <span class="text-success font-weight-bold"><?= htmlspecialchars($llegada['ciudad_origen']) ?></span></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 px-3 align-middle">
                                                        <div class="d-flex flex-column">
                                                            <span class="text-xs font-weight-bold text-dark">Dest: <?= htmlspecialchars($llegada['destinatario']) ?></span>
                                                            <span class="text-xxs text-secondary font-weight-bold">Remit: <?= htmlspecialchars($llegada['remitente']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-center py-3 px-3">
                                                        <span class="text-sm font-weight-bolder <?= $llegada['estado_pago_encomienda'] ? 'text-success' : 'text-dark' ?>">
                                                            Bs. <?= number_format($llegada['monto_encomienda'], 2) ?>
                                                        </span>
                                                        <?php if ($llegada['estado_pago_encomienda']): ?>
                                                            <span class="d-block text-xxs text-success font-weight-bold">Pagado en Origen</span>
                                                        <?php else: ?>
                                                            <span class="d-block text-xxs text-danger font-weight-bold">COD (Cobrar en Destino)</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle text-center py-3 px-3">
                                                        <span class="badge badge-sm <?= $badgeClassRec ?> border-radius-pill px-3 py-1 font-weight-bold">
                                                            <?= htmlspecialchars($llegada['nombre_estado_encomienda']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                                            <?php if ($estadoRec === 'entregado'): ?>
                                                                <a href="javascript:;" class="btn btn-link text-success p-2 mb-0 opacity-5" data-bs-toggle="tooltip" title="Ya Entregado"><i class="material-symbols-rounded text-sm">task_alt</i></a>
                                                            <?php else: ?>
                                                                <a href="<?= URL ?>/encomiendas/entregas/<?= $llegada['id_encomienda'] ?>" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Registrar Entrega al Destinatario">
                                                                    <i class="material-symbols-rounded text-sm">how_to_reg</i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                            <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Comprobante"><i class="material-symbols-rounded text-sm">print</i></a>
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

    </div>

</div>

<script>
    $(document).ready(function() {
        // Inicializar la tabla de envíos
        if (typeof inicializarDataTable === 'function') {
            inicializarDataTable('#datatable-encomiendas', { ordering: false, placeholder: 'Buscar envío...' });
        }

        var llegadasInicializada = false;

        // Escuchar el cambio de pestaña para inicializar lazy-loading del DataTable de llegadas
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var targetTab = $(e.target).attr('href');
            
            if (targetTab === '#tab-llegadas' && !llegadasInicializada) {
                if (typeof inicializarDataTable === 'function') {
                    inicializarDataTable('#datatable-llegadas', { ordering: false, placeholder: 'Buscar llegada...' });
                }
                llegadasInicializada = true;
            }

            if ($.fn.DataTable) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            }
        });
    });
</script>