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
                                <a href="dashboard-encomiendas-new.html" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center gap-2 w-100 w-sm-auto justify-content-center">
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
                                            <!-- Ejemplo 1: En Almacén -> Gris (Secondary) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">inventory_2</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#G-8493</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Remitente: <span class="text-dark">Juan Carlos Mamani</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Destinatario: María Luz Benítez</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ Santa Cruz</span></span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-success">Bs. 35.00</span>
                                                    <span class="d-block text-xxs text-success font-weight-bold">Pagado en Origen</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">En Almacén</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Guía"><i class="material-symbols-rounded text-sm">print</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Guía"><i class="material-symbols-rounded text-sm">delete</i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Ejemplo 2: Asignado -> Azul (Primary / Info) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">markunread_mailbox</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#G-8492</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Remitente: <span class="text-dark">Gabriel Guayhua</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Destinatario: Isabel Guayhua</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ San Borja</span></span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-dark">Bs. 20.00</span>
                                                    <span class="d-block text-xxs text-danger font-weight-bold">COD (Por Cobrar)</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Asignado</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Guía"><i class="material-symbols-rounded text-sm">print</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Guía"><i class="material-symbols-rounded text-sm">delete</i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Ejemplo 3: Entregado -> Verde (Success) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">inventory_2</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#G-8491</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Remitente: <span class="text-dark">Roberto Rivero</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Destinatario: Carmen Rosa Viera</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ La Paz</span></span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-success">Bs. 50.00</span>
                                                    <span class="d-block text-xxs text-success font-weight-bold">Pagado en Origen</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Entregado</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Guía"><i class="material-symbols-rounded text-sm">print</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Guía"><i class="material-symbols-rounded text-sm">delete</i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Ejemplo 4: En Almacén -> Gris (Secondary) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">markunread_mailbox</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#G-8490</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Remitente: <span class="text-dark">Lucía Suarez</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Destinatario: Jorge Justiniano</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ Cochabamba</span></span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-dark">Bs. 35.00</span>
                                                    <span class="d-block text-xxs text-danger font-weight-bold">COD (Por Cobrar)</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">En Almacén</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Guía"><i class="material-symbols-rounded text-sm">print</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Guía"><i class="material-symbols-rounded text-sm">delete</i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Ejemplo 5: Enviado -> Verde (Success) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">inventory_2</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#G-8489</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Remitente: <span class="text-dark">Mario Alpire</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Destinatario: Sofía Arrien</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ Santa Cruz</span></span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-success">Bs. 10.00</span>
                                                    <span class="d-block text-xxs text-success font-weight-bold">Pagado en Origen</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Enviado</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Guía"><i class="material-symbols-rounded text-sm">print</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Guía"><i class="material-symbols-rounded text-sm">delete</i></a>
                                                    </div>
                                                </td>
                                            </tr>
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
                                <a href="dashboard-encomiendas-recepcion.html" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center gap-2 w-100 w-sm-auto justify-content-center">
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
                                            <!-- Ejemplo 1: En Almacén -> Azul (Info) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">move_to_inbox</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#SCZ-3420</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Origen: <span class="text-success font-weight-bold">Santa Cruz</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Dest: Alejandro Melgar</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Remit: Comercial Sur S.R.L.</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-success">Bs. 40.00</span>
                                                    <span class="d-block text-xxs text-success font-weight-bold">Pagado en Origen</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">En Almacén</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="dashboard-encomiendas-entregas.html" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Registrar Entrega al Destinatario">
                                                            <i class="material-symbols-rounded text-sm">how_to_reg</i>
                                                        </a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Comprobante"><i class="material-symbols-rounded text-sm">print</i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Ejemplo 2: En Almacén -> Azul (Info) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">move_to_inbox</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#LPZ-1290</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Origen: <span class="text-success font-weight-bold">La Paz</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Dest: Daniela Vaca Diez</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Remit: Imprenta Central</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-dark">Bs. 80.00</span>
                                                    <span class="d-block text-xxs text-danger font-weight-bold">COD (Cobrar en Destino)</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">En Almacén</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="dashboard-encomiendas-entregas.html" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Registrar Entrega al Destinatario">
                                                            <i class="material-symbols-rounded text-sm">how_to_reg</i>
                                                        </a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Comprobante"><i class="material-symbols-rounded text-sm">print</i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Ejemplo 3: Entregado -> Verde (Success) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">move_to_inbox</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#CBBA-9021</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Origen: <span class="text-success font-weight-bold">Cochabamba</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Dest: Ronald Suarez</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Remit: TecnoHogar Ltda.</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-success">Bs. 30.00</span>
                                                    <span class="d-block text-xxs text-success font-weight-bold">Pagado en Origen</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Entregado</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0 opacity-5" data-bs-toggle="tooltip" title="Ya Entregado"><i class="material-symbols-rounded text-sm">task_alt</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Comprobante"><i class="material-symbols-rounded text-sm">print</i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Ejemplo 4: En Almacén -> Azul (Info) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">move_to_inbox</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#SBR-0441</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Origen: <span class="text-success font-weight-bold">San Borja</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Dest: Carmen Rosa Janco</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Remit: Agropecuaria del Beni</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-dark">Bs. 25.00</span>
                                                    <span class="d-block text-xxs text-danger font-weight-bold">COD (Cobrar en Destino)</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">En Almacén</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="dashboard-encomiendas-entregas.html" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Registrar Entrega al Destinatario">
                                                            <i class="material-symbols-rounded text-sm">how_to_reg</i>
                                                        </a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Comprobante"><i class="material-symbols-rounded text-sm">print</i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Ejemplo 5: Entregado -> Verde (Success) -->
                                            <tr>
                                                <td class="py-3 ps-4 ps-md-5 pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                                                            <i class="material-symbols-rounded">move_to_inbox</i>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#RIB-5512</h6>
                                                            <span class="text-xxs text-secondary font-weight-bold">Origen: <span class="text-success font-weight-bold">Riberalta</span></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 align-middle">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-xs font-weight-bold text-dark">Dest: Julio Cesar Pareja</span>
                                                        <span class="text-xxs text-secondary font-weight-bold">Remit: Cooperativa Norte</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="text-sm font-weight-bolder text-success">Bs. 55.00</span>
                                                    <span class="d-block text-xxs text-success font-weight-bold">Pagado en Origen</span>
                                                </td>
                                                <td class="align-middle text-center py-3 px-3">
                                                    <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Entregado</span>
                                                </td>
                                                <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0 opacity-5" data-bs-toggle="tooltip" title="Ya Entregado"><i class="material-symbols-rounded text-sm">task_alt</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                                                        <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Comprobante"><i class="material-symbols-rounded text-sm">print</i></a>
                                                    </div>
                                                </td>
                                            </tr>
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
            // 1. Inicializar la tabla visible por defecto
            inicializarDataTable('#datatable-encomiendas', { ordering: false, placeholder: 'Buscar envío...' });

            // 2. Variable para controlar si la segunda tabla ya fue inicializada
            var llegadasInicializada = false;

            // 3. Escuchar el cambio de pestañas de Bootstrap
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                var targetTab = $(e.target).attr('href'); // Obtiene el href (ej. #tab-llegadas)
                
                if (targetTab === '#tab-llegadas' && !llegadasInicializada) {
                    inicializarDataTable('#datatable-llegadas', { ordering: false, placeholder: 'Buscar llegada...' });
                    llegadasInicializada = true;
                }

                // Forzar ajuste de columnas por si acaso
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });
        });
    </script>