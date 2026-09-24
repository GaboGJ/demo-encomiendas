  <!-- CONTENIDO PRINCIPAL: GESTIÓN DE USUARIOS -->
    <div class="container-fluid py-3 flex-grow-1">
      
      <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
      <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Usuarios Totales</p>
                    <h5 class="font-weight-bolder text-dark mb-0">
                      24 Activos
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">group</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Roles Asignados</p>
                    <h5 class="font-weight-bolder text-dark mb-0">
                      5 Definidos
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-dark shadow-dark text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">admin_panel_settings</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Boleteros / Cajeros</p>
                    <h5 class="font-weight-bolder text-dark mb-0">
                      12 Operadores
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">point_of_sale</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Sesiones Activas</p>
                    <h5 class="font-weight-bolder text-success mb-0">
                      8 En línea
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">verified_user</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    

      <!-- TABLA PRINCIPAL DE USUARIOS -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
            
            <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
              <div>
                <h5 class="font-weight-bolder text-dark mb-0">Listado de Usuarios Registrados en el Sistema</h5>
                <p class="text-xs text-secondary mb-0">Personal autorizado, rol asignado, sucursales y estado de cuenta</p>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn bg-gradient-success mb-0 d-flex align-items-center gap-1 shadow-sm text-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                  <i class="material-symbols-rounded text-sm">person_add</i> Nuevo Usuario
                </button>
              </div>
            </div>

            <hr class="horizontal dark my-0 opacity-2">

            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table id="datatable-usuarios" class="table table-borderless align-items-center mb-0 w-100">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-5 pe-4 border-top border-bottom border-light">Usuario / Personal</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Rol Asignado</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Sucursal / Terminal</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-5 ps-4 border-top border-bottom border-light">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white font-weight-bold">
                            GG
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Gabriel Guayhua Janco</h6>
                            <span class="text-xxs text-secondary font-weight-bold">gabriel.admin@trans-express.bo</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="badge badge-sm bg-gradient-dark border-radius-pill px-3 py-1 font-weight-bold">Administrador General</span>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Terminal Central Trinidad</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Activo</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Usuario">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Desactivar Cuenta">
                            <i class="material-symbols-rounded text-sm">block</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white font-weight-bold">
                            MR
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Marcos Rivero Roca</h6>
                            <span class="text-xxs text-secondary font-weight-bold">marcos.cajero@trans-express.bo</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Boletero / POS</span>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Boletería Ventanilla 01</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Activo</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Usuario">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Desactivar Cuenta">
                            <i class="material-symbols-rounded text-sm">block</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-warning border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white font-weight-bold">
                            LT
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Lucía Tapia Melgar</h6>
                            <span class="text-xxs text-secondary font-weight-bold">lucia.encomiendas@trans-express.bo</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="badge badge-sm bg-gradient-warning border-radius-pill px-3 py-1 font-weight-bold">Encargado de Encomiendas</span>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Depósito Carga Norte</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Activo</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Usuario">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-danger p-2 mb-0" data-bs-toggle="tooltip" title="Desactivar Cuenta">
                            <i class="material-symbols-rounded text-sm">block</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-secondary border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white font-weight-bold">
                            RV
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Roberto Vargas Sotomayor</h6>
                            <span class="text-xxs text-secondary font-weight-bold">roberto.control@trans-express.bo</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">Controlador de Salidas</span>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Caseta de Andenes</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">Inactivo</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Usuario">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-success p-2 mb-0" data-bs-toggle="tooltip" title="Activar Cuenta">
                            <i class="material-symbols-rounded text-sm">check_circle</i>
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

    <!-- MODAL: REGISTRAR NUEVO USUARIO -->
    <div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-success text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalNuevoUsuarioLabel">
              <i class="material-symbols-rounded text-sm me-1 align-middle">person_add</i> Registrar Nuevo Usuario
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Nombre Completo del Personal</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" class="form-control border-0 ps-2" placeholder="Ej. Carlos Mendoza Silva">
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Correo Electrónico (Login)</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="email" class="form-control border-0 ps-2" placeholder="usuario@trans-express.bo">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Contraseña Temporal</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="password" class="form-control border-0 ps-2" placeholder="••••••••">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Rol Asignado</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <select class="form-select border-0 ps-2 text-xs">
                      <option selected>Boletero / Cajero</option>
                      <option>Encargado de Encomiendas</option>
                      <option>Controlador de Salidas</option>
                      <option>Administrador General</option>
                      <option>Gerencia / Sindicato</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Sucursal / Terminal Asignada</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <select class="form-select border-0 ps-2 text-xs">
                      <option selected>Terminal Central Trinidad</option>
                      <option>Agencia San Borja</option>
                      <option>Agencia San Ignacio</option>
                      <option>Depósito Carga Norte</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-success mb-0 btn-sm px-4">Guardar Usuario</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>