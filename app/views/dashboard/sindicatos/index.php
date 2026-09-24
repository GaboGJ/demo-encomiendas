<!-- CONTENIDO PRINCIPAL: SINDICATOS DE TRANSPORTE -->
    <div class="container-fluid py-3 flex-grow-1">
      
      <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
      <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Sindicatos Afiliados</p>
                    <h5 class="font-weight-bolder text-dark mb-0">8 Registrados</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">domain</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Total Operadores / Base</p>
                    <h5 class="font-weight-bolder text-dark mb-0">345 Activos</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-dark shadow-dark text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">groups</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Rutas Habilitadas</p>
                    <h5 class="font-weight-bolder text-info mb-0">14 Destinos</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">route</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Estado Legal</p>
                    <h5 class="font-weight-bolder text-success mb-0">100% Regular</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">verified_user</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TABLA PRINCIPAL DE SINDICATOS -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
            
            <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
              <div>
                <h5 class="font-weight-bolder text-dark mb-0">Directorio de Sindicatos de Transporte</h5>
                <p class="text-xs text-secondary mb-0">Listado general de organizaciones afiliadas, rutas operativas y secretarios generales</p>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn bg-gradient-dark mb-0 d-flex align-items-center gap-1 shadow-sm text-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoSindicato">
                  <i class="material-symbols-rounded text-sm">add_business</i> Registrar Sindicato
                </button>
              </div>
            </div>

            <hr class="horizontal dark my-0 opacity-2">

            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table id="datatable-sindicatos" class="table table-borderless align-items-center mb-0 w-100">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-5 pe-4 border-top border-bottom border-light">Sindicato / Sigla</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Secretario General</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Ruta / Zona Operativa</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Afiliados</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-5 ps-4 border-top border-bottom border-light">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- EJEMPLO 1 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">local_shipping</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Sindicato 1ro de Mayo</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Ruta Norte</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Juan Muñoz</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Cel: 71123456</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Trinidad - Riberalta</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">55 Socios</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Sindicato">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Sindicato">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <!-- EJEMPLO 2 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">directions_car</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Sindicato 8 de Septiembre</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Interprovincial</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Mario Suárez</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Cel: 72891029</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Trinidad - San Borja</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">42 Socios</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Sindicato"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Sindicato"><i class="material-symbols-rounded text-sm">edit</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- EJEMPLO 3 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-warning border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">airport_shuttle</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Sindicato Mamoré</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Transporte Fluvial y Mixto</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Esteban Tineo</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Cel: 73019283</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">Puerto Almacén - Loreto</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">38 Socios</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Sindicato"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Sindicato"><i class="material-symbols-rounded text-sm">edit</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- EJEMPLO 4 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">commute</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Sindicato 24 de Septiembre</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Microbuses Urbanos</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Roberto Zambrana</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Cel: 79281023</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Circuito Central - Sur</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">60 Socios</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Sindicato"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Sindicato"><i class="material-symbols-rounded text-sm">edit</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- EJEMPLO 5 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-secondary border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">local_taxi</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Asociación de Taxis El Trigal</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Transporte Libres</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Carmen Rosa Rivero</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Cel: 70491023</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">Zona Urbana Trinidad</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">45 Socios</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Sindicato"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Sindicato"><i class="material-symbols-rounded text-sm">edit</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- EJEMPLO 6 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">rv_hookup</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Sindicato Beniano de Carga</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Camiones Pesados</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Héctor Vargas</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Cel: 71593821</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-dark border-radius-pill px-3 py-1 font-weight-bold">Ruta Departamental / Nacional</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">30 Socios</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Sindicato"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Sindicato"><i class="material-symbols-rounded text-sm">edit</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- EJEMPLO 7 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">two_wheeler</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Asociación de Mototaxis Los Flamboyanes</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Transporte Rápido</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Luis Fernando Roca</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Cel: 78392019</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Zona Norte y Universitaria</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">65 Socios</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Sindicato"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Sindicato"><i class="material-symbols-rounded text-sm">edit</i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- EJEMPLO 8 -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-warning border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">bus_alert</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Sindicato Interprovincial Moxos</h6>
                            <span class="text-xxs text-secondary font-weight-bold">Buses y Minivans</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Walter Chavez</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Cel: 72019384</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">Trinidad - San Ignacio</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">27 Socios</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Sindicato"><i class="material-symbols-rounded text-sm">visibility</i></a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Sindicato"><i class="material-symbols-rounded text-sm">edit</i></a>
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

    <!-- MODAL: REGISTRAR NUEVO SINDICATO -->
    <div class="modal fade" id="modalNuevoSindicato" tabindex="-1" aria-labelledby="modalNuevoSindicatoLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-dark text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalNuevoSindicatoLabel">
              <i class="material-symbols-rounded text-sm me-1 align-middle">add_business</i> Registrar Nuevo Sindicato
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Nombre del Sindicato / Asociación</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" class="form-control border-0 ps-2" placeholder="Ej. Sindicato 16 de Julio">
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Secretario General</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" placeholder="Nombre completo">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Teléfono / Contacto</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" placeholder="Celular de referencia">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Ruta / Zona Operativa</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" placeholder="Ej. Trinidad - San Ignacio">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Cantidad de Socios</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" placeholder="Nro de afiliados">
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-dark mb-0 btn-sm px-4">Guardar Sindicato</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>