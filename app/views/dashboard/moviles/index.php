  <!-- CONTENIDO PRINCIPAL: VEHÍCULOS / MÓVILES -->
    <div class="container-fluid py-3 flex-grow-1">
      
      <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
      <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Unidades Registradas</p>
                    <h5 class="font-weight-bolder text-dark mb-0">24 Vehículos</h5>
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

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Flotas en Ruta</p>
                    <h5 class="font-weight-bolder text-dark mb-0">
                      14 Activas <span class="text-success text-xs font-weight-bolder">En viaje</span>
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-dark shadow-dark text-center border-radius-md">
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Disponibles</p>
                    <h5 class="font-weight-bolder text-info mb-0">8 Unidades</h5>
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

        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">En Mantenimiento</p>
                    <h5 class="font-weight-bolder text-warning mb-0">2 Unidades</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">build</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

   

      <!-- TABLA PRINCIPAL DE FLOTAS Y UNIDADES -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
            
            <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
              <div>
                <h5 class="font-weight-bolder text-dark mb-0">Parque Automotor y Unidades Asignadas</h5>
                <p class="text-xs text-secondary mb-0">Listado completo de minibuses, buses y flotas operativas del sindicato de transportes</p>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn bg-gradient-success mb-0 d-flex align-items-center gap-1 shadow-sm text-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaFlota">
                  <i class="material-symbols-rounded text-sm">add</i> Registrar Vehículo
                </button>
              </div>
            </div>

            <hr class="horizontal dark my-0 opacity-2">

            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table id="datatable-flotas" class="table table-borderless align-items-center mb-0 w-100">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-5 pe-4 border-top border-bottom border-light">Unidad / Placa</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Socio Asignado</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Capacidad / Tipo</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado Actual</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-5 ps-4 border-top border-bottom border-light">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">directions_bus</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Unidad 14</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Placa: 4892-XYZ (Minibús)</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Carlos Pérez (Socio Titular)</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">15 Pasajeros</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Con maletero adaptado</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">En Ruta</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Unidad">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">directions_bus</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Unidad 08</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Placa: 3102-ABC (Bus Mediano)</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Esteban Tineo (Socio Titular)</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">32 Pasajeros</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Doble compartimiento</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Disponible</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Unidad">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-warning border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">build</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Unidad 03</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Placa: 2049-DEF (Minibús)</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Raúl Navia (Socio Titular)</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">14 Pasajeros</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Uso mixto pasajeros/carga</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">Mantenimiento</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Detalles">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Unidad">
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

    <!-- MODAL: REGISTRAR NUEVA FLOTA / VEHÍCULO -->
    <div class="modal fade" id="modalNuevaFlota" tabindex="-1" aria-labelledby="modalNuevaFlotaLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-success text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalNuevaFlotaLabel">
              <i class="material-symbols-rounded text-sm me-1 align-middle">directions_bus</i> Registrar Nueva Unidad
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Número de Unidad / Interno</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" class="form-control border-0 ps-2" placeholder="Ej. Unidad 25">
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Número de Placa</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" placeholder="Ej. 5400-XYZ">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Tipo de Vehículo</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <select class="form-select border-0 ps-2 text-xs">
                      <option selected>Minibús (14-15 pasajeros)</option>
                      <option>Bus Mediano (30-35 pasajeros)</option>
                      <option>Bus Panorámico / Grande</option>
                      <option>Furgón de Carga</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Socio Titular Asignado</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <select class="form-select border-0 ps-2 text-xs">
                    <option selected>Carlos Pérez</option>
                    <option>Esteban Tineo</option>
                    <option>Raúl Navia</option>
                    <option>Marcos Soliz</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Capacidad de Carga / Maletero (kg)</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="number" class="form-control border-0 ps-2" placeholder="Ej. 500">
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-success mb-0 btn-sm px-4">Guardar Unidad</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>