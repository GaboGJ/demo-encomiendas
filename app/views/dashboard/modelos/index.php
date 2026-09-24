   <!-- CONTENIDO PRINCIPAL: CONFIGURACIÓN DE VEHÍCULOS -->
    <div class="container-fluid py-3 flex-grow-1">
      
      <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
      <div class="row mb-4">
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Modelos Configurados</p>
                    <h5 class="font-weight-bolder text-dark mb-0">2 Modelos</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">directions_bus</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Esquemas Activos</p>
                    <h5 class="font-weight-bolder text-dark mb-0">2 Distribuciones</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-dark shadow-dark text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">event_seat</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-4 col-sm-6">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Estado de Configuración</p>
                    <h5 class="font-weight-bolder text-info mb-0">Sincronizado</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">check_circle</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TABLA PRINCIPAL DE VEHÍCULOS CONFIGURADOS -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
            
            <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
              <div>
                <h5 class="font-weight-bolder text-dark mb-0">Configuración de Vehículos y Distribución de Asientos</h5>
                <p class="text-xs text-secondary mb-0">Gestión de modelos de vehículos para la personalización de su plano de asientos y comodidades internas</p>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn bg-gradient-success mb-0 d-flex align-items-center gap-1 shadow-sm text-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoVehiculo">
                  <i class="material-symbols-rounded text-sm">add</i> Nuevo Modelo
                </button>
              </div>
            </div>

            <hr class="horizontal dark my-0 opacity-2">

            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table id="datatable-vehiculos" class="table table-borderless align-items-center mb-0 w-100">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-5 pe-4 border-top border-bottom border-light">Modelo / Vehículo</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Placa Referencial</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Capacidad Total</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado Configuración</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-5 ps-4 border-top border-bottom border-light">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Ejemplo 1: Ipsum -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">directions_car</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Toyota Ipsum</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Miniván Familiar / Pasajeros</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">PLY-788</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">7 Asientos</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Distribución 3 filas</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Configurado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="dashboard-configuracion-asientos.html" class="btn btn-outline-dark btn-sm mb-0 d-flex align-items-center gap-1 px-3 py-1 text-xs">
                            <span class="material-symbols-rounded text-sm">event_seat</span> Configurar Asientos
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- Ejemplo 2: Noa -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">directions_bus</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Toyota Noa</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Miniván de Alta Capacidad</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">NOA-412</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">8 Asientos</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Distribución 4 filas</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Configurado</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="dashboard-configuracion-asientos.html" class="btn btn-outline-dark btn-sm mb-0 d-flex align-items-center gap-1 px-3 py-1 text-xs">
                            <span class="material-symbols-rounded text-sm">event_seat</span> Configurar Asientos
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

    <!-- MODAL: REGISTRAR NUEVO VEHÍCULO -->
    <div class="modal fade" id="modalNuevoVehiculo" tabindex="-1" aria-labelledby="modalNuevoVehiculoLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-success text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalNuevoVehiculoLabel">
              <i class="material-symbols-rounded text-sm me-1 align-middle">directions_bus</i> Registrar Nuevo Modelo
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Nombre del Modelo / Vehículo</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" class="form-control border-0 ps-2" placeholder="Ej. Toyota Ipsum / Toyota Noa">
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Placa de Referencia</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" placeholder="Ej. PLY-788">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Tipo de Vehículo</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <select class="form-select border-0 ps-2 text-xs">
                      <option selected>Miniván (7-8 pasajeros)</option>
                      <option>Minibús (14-15 pasajeros)</option>
                      <option>Bus Mediano (30-35 pasajeros)</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Capacidad Estimada de Pasajeros</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="number" class="form-control border-0 ps-2" placeholder="Ej. 7">
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-success mb-0 btn-sm px-4">Guardar Modelo</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>