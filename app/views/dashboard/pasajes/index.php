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

      <!-- CONTENIDO DE PASAJES VENDIDOS -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
            
            <div class="card-header bg-white p-3 p-md-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
              <div>
                <h6 class="font-weight-bolder text-dark mb-0">Listado de Pasajes Vendidos</h6>
                <p class="text-xs text-secondary mb-0">Boletos de viaje emitidos en ventanilla para las distintas flotas y rutas.</p>
              </div>
              <a href="dashboard-pasajes-new.html" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center gap-2 w-100 w-sm-auto justify-content-center">
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
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Nº Boleto / Pasajero</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Ruta / Asiento</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Costo & Pago</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- 1. Despachado -->
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">confirmation_number</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#BOL-5012</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Pasajero: <span class="text-dark">Gabriel Guayhua Janco</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Ruta: Trinidad ➔ Santa Cruz</span>
                          <span class="text-xxs text-secondary font-weight-bold">Asiento: <span class="text-success font-weight-bold">Nº 14 (Bus Expreso 02)</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 120.00</span>
                        <span class="d-block text-xxs text-success font-weight-bold">Pagado en Ventanilla</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Despachado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Boleto"><i class="material-symbols-rounded text-sm">print</i></a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Boleto"><i class="material-symbols-rounded text-sm">delete</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- 2. Asignado -->
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">confirmation_number</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#BOL-5013</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Pasajero: <span class="text-dark">Isabel Luciana Guayhua</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Ruta: Trinidad ➔ San Borja</span>
                          <span class="text-xxs text-secondary font-weight-bold">Asiento: <span class="text-info font-weight-bold">Nº 04 (Minibus 05)</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 60.00</span>
                        <span class="d-block text-xxs text-success font-weight-bold">Pagado en Ventanilla</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Asignado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Boleto"><i class="material-symbols-rounded text-sm">print</i></a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Boleto"><i class="material-symbols-rounded text-sm">delete</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- 3. En espera -->
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-warning border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">confirmation_number</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#BOL-5014</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Pasajero: <span class="text-dark">Carlos Mendoza Rios</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Ruta: Trinidad ➔ La Paz</span>
                          <span class="text-xxs text-secondary font-weight-bold">Asiento: <span class="text-warning font-weight-bold">Por Asignar (Bus Leito 01)</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 180.00</span>
                        <span class="d-block text-xxs text-success font-weight-bold">Pagado en Ventanilla</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">En espera</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Boleto"><i class="material-symbols-rounded text-sm">print</i></a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Boleto"><i class="material-symbols-rounded text-sm">delete</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- 4. Asignado -->
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">confirmation_number</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#BOL-5015</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Pasajero: <span class="text-dark">Maria Fernanda Rocha</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Ruta: Trinidad ➔ Santa Cruz</span>
                          <span class="text-xxs text-secondary font-weight-bold">Asiento: <span class="text-info font-weight-bold">Nº 08 (Bus Expreso 02)</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 120.00</span>
                        <span class="d-block text-xxs text-success font-weight-bold">Pagado en Ventanilla</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Asignado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Boleto"><i class="material-symbols-rounded text-sm">print</i></a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Boleto"><i class="material-symbols-rounded text-sm">delete</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- 5. Despachado -->
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">confirmation_number</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#BOL-5016</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Pasajero: <span class="text-dark">Jorge Luis Vargas</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Ruta: Trinidad ➔ San Ignacio</span>
                          <span class="text-xxs text-secondary font-weight-bold">Asiento: <span class="text-success font-weight-bold">Nº 12 (Flota Sur 03)</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 90.00</span>
                        <span class="d-block text-xxs text-success font-weight-bold">Pagado en Ventanilla</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Despachado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Boleto"><i class="material-symbols-rounded text-sm">print</i></a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Boleto"><i class="material-symbols-rounded text-sm">delete</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- 6. En espera -->
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-warning border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">confirmation_number</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#BOL-5017</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Pasajero: <span class="text-dark">Ana Patricia Suarez</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Ruta: Trinidad ➔ Cochabamba</span>
                          <span class="text-xxs text-secondary font-weight-bold">Asiento: <span class="text-warning font-weight-bold">Por Asignar (Bus Leito 02)</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 160.00</span>
                        <span class="d-block text-xxs text-success font-weight-bold">Pagado en Ventanilla</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">En espera</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Boleto"><i class="material-symbols-rounded text-sm">print</i></a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Boleto"><i class="material-symbols-rounded text-sm">delete</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- 7. Asignado -->
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">confirmation_number</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#BOL-5018</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Pasajero: <span class="text-dark">Roberto Limpias Paz</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Ruta: Trinidad ➔ San Borja</span>
                          <span class="text-xxs text-secondary font-weight-bold">Asiento: <span class="text-info font-weight-bold">Nº 09 (Minibus 05)</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 60.00</span>
                        <span class="d-block text-xxs text-success font-weight-bold">Pagado en Ventanilla</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Asignado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Boleto"><i class="material-symbols-rounded text-sm">print</i></a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Boleto"><i class="material-symbols-rounded text-sm">delete</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- 8. Despachado -->
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">confirmation_number</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#BOL-5019</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Pasajero: <span class="text-dark">Carmen Rosa Rivero</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Ruta: Trinidad ➔ Santa Cruz</span>
                          <span class="text-xxs text-secondary font-weight-bold">Asiento: <span class="text-success font-weight-bold">Nº 21 (Bus Expreso 02)</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 120.00</span>
                        <span class="d-block text-xxs text-success font-weight-bold">Pagado en Ventanilla</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Despachado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Boleto"><i class="material-symbols-rounded text-sm">print</i></a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Anular Boleto"><i class="material-symbols-rounded text-sm">delete</i></a>
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