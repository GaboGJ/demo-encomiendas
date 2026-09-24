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
              <a href="dashboard-despachos-new.html" class="btn bg-gradient-success text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto">
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
                    <!-- 1. Turno Despachado -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">departure_board</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#T-402</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ Santa Cruz</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Unidad 14 (Bus Expreso)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Chofer: <span class="text-dark">Carlos Pérez Mamani</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 3,200.00</span>
                        <span class="d-block text-xs font-weight-bold text-dark mt-1">32 / 40 <span class="text-xxs text-secondary font-weight-bold">(Asientos Ocupados)</span></span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 850.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">18 Guías Consolidadas</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Despachado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <!-- Botón de asignación OMITIDO por estar despachado -->
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

                    <!-- 2. Turno Despachado -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">directions_car</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#T-401</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ San Borja</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Unidad 08 (Toyota Ipsum)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Chofer: <span class="text-dark">Mario Gómez Soliz</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 480.00</span>
                        <span class="d-block text-xs font-weight-bold text-dark mt-1">6 / 6 <span class="text-xxs text-success font-weight-bold">(Cupo Completo)</span></span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 120.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">4 Guías Consolidadas</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Despachado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <!-- Botón de asignación OMITIDO por estar despachado -->
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

                    <!-- 3. Turno Despachado -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">departure_board</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#T-400</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ La Paz</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Unidad 03 (Flota Pullmann)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Chofer: <span class="text-dark">Roberto Rivero Viera</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 6,750.00</span>
                        <span class="d-block text-xs font-weight-bold text-dark mt-1">45 / 45 <span class="text-xxs text-success font-weight-bold">(Cupo Completo)</span></span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 1,250.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">25 Guías Consolidadas</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Despachado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <!-- Botón de asignación OMITIDO por estar despachado -->
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

                    <!-- 4. Turno En Turno (SÍ INCLUYE EL BOTÓN) -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">schedule</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#T-399</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ Cochabamba</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Unidad 12 (Bus Leito)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Chofer: <span class="text-dark">Jorge Justiniano Roca</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 3,920.00</span>
                        <span class="d-block text-xs font-weight-bold text-dark mt-1">28 / 36 <span class="text-xxs text-secondary font-weight-bold">(Asientos Ocupados)</span></span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 640.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">14 Guías Consolidadas</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">En Turno</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <!-- Botón de asignación DISPONIBLE porque está En Turno -->
                          <a href="dashboard-despachos-asignar.html" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Asignar Pasajes y Encomiendas">
                            <i class="material-symbols-rounded text-sm">assignment</i>
                          </a>
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

                    <!-- 5. Turno Despachado -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">directions_car</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#T-398</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ San Ignacio</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Unidad 19 (Toyota Noha)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Chofer: <span class="text-dark">Mario Alpire Salvatierra</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 560.00</span>
                        <span class="d-block text-xs font-weight-bold text-dark mt-1">7 / 7 <span class="text-xxs text-success font-weight-bold">(Cupo Completo)</span></span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 180.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">5 Guías Consolidadas</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Despachado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <!-- Botón de asignación OMITIDO por estar despachado -->
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

                    <!-- 6. Turno En Turno (SÍ INCLUYE EL BOTÓN) -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">schedule</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#T-396</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta: <span class="text-success font-weight-bold">Trinidad ➔ Riberalta</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Unidad 15 (Bus Mixto)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Chofer: <span class="text-dark">Carlos Zelada Tineo</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 2,400.00</span>
                        <span class="d-block text-xs font-weight-bold text-dark mt-1">20 / 30 <span class="text-xxs text-secondary font-weight-bold">(Asientos Ocupados)</span></span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 1,120.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">22 Guías Consolidadas</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">En Turno</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <!-- Botón de asignación DISPONIBLE porque está En Turno -->
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Asignar Pasajes y Encomiendas">
                            <i class="material-symbols-rounded text-sm">assignment</i>
                          </a>
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
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>