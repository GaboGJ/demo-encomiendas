 <!-- CONTENIDO PRINCIPAL: RUTAS Y TARIFARIOS -->
    <div class="container-fluid py-3 flex-grow-1">
      
      <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
      <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Rutas Habilitadas</p>
                    <h5 class="font-weight-bolder text-dark mb-0">
                      9 Destinos
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">alt_route</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Tarifa Promedio Pasaje</p>
                    <h5 class="font-weight-bolder text-dark mb-0">
                      Bs. 65.00
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-dark shadow-dark text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">payments</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Tarifa Base Encomienda</p>
                    <h5 class="font-weight-bolder text-dark mb-0">
                      Bs. 15.00
                      <span class="text-secondary text-xs font-weight-normal">/ sobre o caja pequeña</span>
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">inventory_2</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Frecuencia Diaria</p>
                    <h5 class="font-weight-bolder text-success mb-0">
                      16 Salidas
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">schedule</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TABLA PRINCIPAL DE RUTAS Y TARIFAS -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
            
            <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
              <div>
                <h5 class="font-weight-bolder text-dark mb-0">Tabla Oficial de Rutas y Tarifarios Vigentes</h5>
                <p class="text-xs text-secondary mb-0">Detalle de trayectos interprovinciales/departamentales, tiempos estimados y costos de pasajes y carga</p>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn bg-gradient-success mb-0 d-flex align-items-center gap-1 shadow-sm text-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaRuta">
                  <i class="material-symbols-rounded text-sm">add_road</i> Nueva Ruta
                </button>
              </div>
            </div>

            <hr class="horizontal dark my-0 opacity-2">

            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table id="datatable-rutas" class="table table-borderless align-items-center mb-0 w-100">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-5 pe-4 border-top border-bottom border-light">Origen / Destino</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Distancia y Tiempo Estimado</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Tarifa Pasaje (Bs.)</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Tarifa Encomienda (Bs.)</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-5 ps-4 border-top border-bottom border-light">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Fila 1 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">route</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Trinidad → San Borja</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Escalas: San Ignacio (Opcional)</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">230 km</span>
                          <span class="text-xxs text-secondary font-weight-bold">Duración aprox: <span class="text-dark">5 hrs 30 min</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 80.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Asiento normal / pullman</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Bs. 20.00 base</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bs. 3.00 / kg adicional</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditarTarifa" data-ruta="Trinidad → San Borja" data-pasaje="80.00" data-encomienda="20.00" title="Modificar Tarifa">
                            <i class="material-symbols-rounded text-sm">request_quote</i>
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Ruta">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- Fila 2 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">route</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Trinidad → San Ignacio de Moxos</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta pavimentada / directa</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">90 km</span>
                          <span class="text-xxs text-secondary font-weight-bold">Duración aprox: <span class="text-dark">1 hr 45 min</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 35.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Minibús / Bus</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Bs. 15.00 base</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bs. 2.50 / kg adicional</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditarTarifa" data-ruta="Trinidad → San Ignacio de Moxos" data-pasaje="35.00" data-encomienda="15.00" title="Modificar Tarifa">
                            <i class="material-symbols-rounded text-sm">request_quote</i>
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Ruta">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- Fila 3 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">route</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Trinidad → Santa Ana del Yacuma</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Vía troncal norte</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">170 km</span>
                          <span class="text-xxs text-secondary font-weight-bold">Duración aprox: <span class="text-dark">4 hrs 00 min</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 60.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Asiento estándar</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-dark border-radius-pill px-3 py-1 font-weight-bold">Bs. 18.00 base</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bs. 3.00 / kg adicional</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditarTarifa" data-ruta="Trinidad → Santa Ana del Yacuma" data-pasaje="60.00" data-encomienda="18.00" title="Modificar Tarifa">
                            <i class="material-symbols-rounded text-sm">request_quote</i>
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Ruta">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- Fila 4 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-warning border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">route</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Trinidad → Riberalta</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta larga interprovincial</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">540 km</span>
                          <span class="text-xxs text-secondary font-weight-bold">Duración aprox: <span class="text-dark">12 hrs 00 min</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 150.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bus leito / pullman</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">Bs. 30.00 base</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bs. 5.00 / kg adicional</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditarTarifa" data-ruta="Trinidad → Riberalta" data-pasaje="150.00" data-encomienda="30.00" title="Modificar Tarifa">
                            <i class="material-symbols-rounded text-sm">request_quote</i>
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Ruta">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- Fila 5 (Ejemplo 1) -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">route</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Trinidad → Guayaramerín</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Carretera troncal norte</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">650 km</span>
                          <span class="text-xxs text-secondary font-weight-bold">Duración aprox: <span class="text-dark">14 hrs 00 min</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 180.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bus pullman</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Bs. 35.00 base</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bs. 5.00 / kg adicional</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditarTarifa" data-ruta="Trinidad → Guayaramerín" data-pasaje="180.00" data-encomienda="35.00" title="Modificar Tarifa">
                            <i class="material-symbols-rounded text-sm">request_quote</i>
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Ruta">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- Fila 6 (Ejemplo 2) -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">route</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Trinidad → San Ramón</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Eje interdepartamental sur</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">340 km</span>
                          <span class="text-xxs text-secondary font-weight-bold">Duración aprox: <span class="text-dark">6 hrs 30 min</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 100.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Minibús / Bus</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Bs. 20.00 base</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bs. 3.50 / kg adicional</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditarTarifa" data-ruta="Trinidad → San Ramón" data-pasaje="100.00" data-encomienda="20.00" title="Modificar Tarifa">
                            <i class="material-symbols-rounded text-sm">request_quote</i>
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Ruta">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- Fila 7 (Ejemplo 3) -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">route</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Trinidad → Magdalena</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Vía llanos de Mojos este</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">300 km</span>
                          <span class="text-xxs text-secondary font-weight-bold">Duración aprox: <span class="text-dark">7 hrs 00 min</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 90.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bus estándar</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-dark border-radius-pill px-3 py-1 font-weight-bold">Bs. 25.00 base</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bs. 4.00 / kg adicional</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditarTarifa" data-ruta="Trinidad → Magdalena" data-pasaje="90.00" data-encomienda="25.00" title="Modificar Tarifa">
                            <i class="material-symbols-rounded text-sm">request_quote</i>
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Ruta">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- Fila 8 (Ejemplo 4) -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-warning border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">route</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Trinidad → Santa Cruz de la Sierra</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Conexión departamental principal</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">500 km</span>
                          <span class="text-xxs text-secondary font-weight-bold">Duración aprox: <span class="text-dark">10 hrs 00 min</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 140.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bus cama / leito</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">Bs. 30.00 base</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bs. 4.50 / kg adicional</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditarTarifa" data-ruta="Trinidad → Santa Cruz de la Sierra" data-pasaje="140.00" data-encomienda="30.00" title="Modificar Tarifa">
                            <i class="material-symbols-rounded text-sm">request_quote</i>
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Ruta">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- Fila 9 (Ejemplo 5) -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">route</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Trinidad → Cochabamba</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta interregional altiplano-llano</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">820 km</span>
                          <span class="text-xxs text-secondary font-weight-bold">Duración aprox: <span class="text-dark">18 hrs 00 min</span></span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 210.00</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bus pullman VIP</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Bs. 45.00 base</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Bs. 6.00 / kg adicional</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditarTarifa" data-ruta="Trinidad → Cochabamba" data-pasaje="210.00" data-encomienda="45.00" title="Modificar Tarifa">
                            <i class="material-symbols-rounded text-sm">request_quote</i>
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Ruta">
                            <i class="material-symbols-rounded text-sm">edit</i>
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

    <!-- MODAL: REGISTRAR NUEVA RUTA -->
    <div class="modal fade" id="modalNuevaRuta" tabindex="-1" aria-labelledby="modalNuevaRutaLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-success text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalNuevaRutaLabel">
              <i class="material-symbols-rounded text-sm me-1 align-middle">add_road</i> Registrar Nueva Ruta
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Ciudad de Origen</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" value="Trinidad" readonly>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Ciudad de Destino</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" placeholder="Ej. Guayaramerín">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Distancia Estimada (km)</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" placeholder="Ej. 350">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Tiempo Estimado de Viaje</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" placeholder="Ej. 8 hrs">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Tarifa Pasaje Oficial (Bs.)</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" placeholder="Ej. 90.00">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Tarifa Encomienda Base (Bs.)</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" placeholder="Ej. 20.00">
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-success mb-0 btn-sm px-4">Guardar Ruta</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: MODIFICAR TARIFA POR RUTA (Específico por fila) -->
    <div class="modal fade" id="modalEditarTarifa" tabindex="-1" aria-labelledby="modalEditarTarifaLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-info text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalEditarTarifaLabel">
              <i class="material-symbols-rounded text-sm me-1 align-middle">request_quote</i> Modificar Tarifa de Ruta
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form id="formEditarTarifa">
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Ruta Seleccionada</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" id="inputRutaNombre" class="form-control border-0 ps-2 bg-gray-100" readonly>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Costo Pasaje (Bs.)</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" id="inputTarifaPasaje" class="form-control border-0 ps-2" step="0.01" required>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Tarifa Base Encomienda (Bs.)</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" id="inputTarifaEncomienda" class="form-control border-0 ps-2" step="0.01" required>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Motivo del Ajuste individual</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <textarea class="form-control border-0 p-2 text-xs" rows="2" placeholder="Ej. Actualización por tramo o resolución específica..."></textarea>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-info mb-0 btn-sm px-4">Guardar Cambios</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
