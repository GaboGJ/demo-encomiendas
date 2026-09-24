   <!-- CONTENIDO PRINCIPAL: ROLES Y PERMISOS -->
    <div class="container-fluid py-3 flex-grow-1">
      
      <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
      <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Total Roles</p>
                    <h5 class="font-weight-bolder text-dark mb-0">5</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <span class="material-symbols-rounded text-white opacity-10">shield_person</span>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Usuarios Asignados</p>
                    <h5 class="font-weight-bolder text-dark mb-0">42</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <span class="material-symbols-rounded text-white opacity-10">group</span>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Módulos</p>
                    <h5 class="font-weight-bolder text-dark mb-0">7</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-dark shadow-dark text-center border-radius-md">
                    <span class="material-symbols-rounded text-white opacity-10">apps</span>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Roles Críticos</p>
                    <h5 class="font-weight-bolder text-dark mb-0">2</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center border-radius-md">
                    <span class="material-symbols-rounded text-white opacity-10">admin_panel_settings</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN PRINCIPAL: LISTA DE ROLES Y BOTÓN DE CREAR ROL -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
            
            <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
              <div>
                <h5 class="font-weight-bolder text-dark mb-0">Listado de Roles del Sistema</h5>
                <p class="text-xs text-secondary mb-0">Administra los perfiles y accede a la asignación de permisos CRUD por rol</p>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn bg-gradient-success mb-0 d-flex align-items-center gap-1 shadow-sm text-sm" data-bs-toggle="modal" data-bs-target="#modalCrearRol">
                  <span class="material-symbols-rounded text-sm">add_moderator</span> Crear Nuevo Rol
                </button>
              </div>
            </div>

            <hr class="horizontal dark my-0 opacity-2">

            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table id="datatable-roles" class="table table-borderless align-items-center mb-0 w-100">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-5 pe-4 border-top border-bottom border-light">Rol / Perfil</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Descripción</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Usuarios</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-5 ps-4 border-top border-bottom border-light">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="py-3 ps-5 pe-4 text-xs font-weight-bold text-dark">Administrador General</td>
                      <td class="py-3 px-3 text-xs text-secondary">Control total del sistema y configuración</td>
                      <td class="py-3 px-3 align-middle text-center text-xs font-weight-bold">3</td>
                      <td class="py-3 px-3 align-middle text-center"><span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Activo</span></td>
                      <td class="py-3 pe-5 ps-4 align-middle text-end">
                        <button type="button" class="btn btn-outline-success btn-sm mb-0 px-3 d-inline-flex align-items-center gap-1 text-xs" data-bs-toggle="modal" data-bs-target="#modalPermisosRol" onclick="cargarPermisosRol('Administrador General')">
                          <span class="material-symbols-rounded text-sm">lock_open</span> Asignar Permisos
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4 text-xs font-weight-bold text-dark">Boletero (POS)</td>
                      <td class="py-3 px-3 text-xs text-secondary">Venta de pasajes y emisión de boletos locales</td>
                      <td class="py-3 px-3 align-middle text-center text-xs font-weight-bold">15</td>
                      <td class="py-3 px-3 align-middle text-center"><span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Activo</span></td>
                      <td class="py-3 pe-5 ps-4 align-middle text-end">
                        <button type="button" class="btn btn-outline-success btn-sm mb-0 px-3 d-inline-flex align-items-center gap-1 text-xs" data-bs-toggle="modal" data-bs-target="#modalPermisosRol" onclick="cargarPermisosRol('Boletero (POS)')">
                          <span class="material-symbols-rounded text-sm">lock_open</span> Asignar Permisos
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4 text-xs font-weight-bold text-dark">Encargado Encomiendas</td>
                      <td class="py-3 px-3 text-xs text-secondary">Gestión de paquetería y tracking de cargas</td>
                      <td class="py-3 px-3 align-middle text-center text-xs font-weight-bold">8</td>
                      <td class="py-3 px-3 align-middle text-center"><span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Activo</span></td>
                      <td class="py-3 pe-5 ps-4 align-middle text-end">
                        <button type="button" class="btn btn-outline-success btn-sm mb-0 px-3 d-inline-flex align-items-center gap-1 text-xs" data-bs-toggle="modal" data-bs-target="#modalPermisosRol" onclick="cargarPermisosRol('Encargado Encomiendas')">
                          <span class="material-symbols-rounded text-sm">lock_open</span> Asignar Permisos
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4 text-xs font-weight-bold text-dark">Controlador de Salidas</td>
                      <td class="py-3 px-3 text-xs text-secondary">Despacho de flotas y control de manifiestos</td>
                      <td class="py-3 px-3 align-middle text-center text-xs font-weight-bold">6</td>
                      <td class="py-3 px-3 align-middle text-center"><span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Activo</span></td>
                      <td class="py-3 pe-5 ps-4 align-middle text-end">
                        <button type="button" class="btn btn-outline-success btn-sm mb-0 px-3 d-inline-flex align-items-center gap-1 text-xs" data-bs-toggle="modal" data-bs-target="#modalPermisosRol" onclick="cargarPermisosRol('Controlador de Salidas')">
                          <span class="material-symbols-rounded text-sm">lock_open</span> Asignar Permisos
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4 text-xs font-weight-bold text-dark">Gerencia / Sindicato</td>
                      <td class="py-3 px-3 text-xs text-secondary">Supervisión ejecutiva y revisión de reportes</td>
                      <td class="py-3 px-3 align-middle text-center text-xs font-weight-bold">10</td>
                      <td class="py-3 px-3 align-middle text-center"><span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Activo</span></td>
                      <td class="py-3 pe-5 ps-4 align-middle text-end">
                        <button type="button" class="btn btn-outline-success btn-sm mb-0 px-3 d-inline-flex align-items-center gap-1 text-xs" data-bs-toggle="modal" data-bs-target="#modalPermisosRol" onclick="cargarPermisosRol('Gerencia / Sindicato')">
                          <span class="material-symbols-rounded text-sm">lock_open</span> Asignar Permisos
                        </button>
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

    <!-- MODAL: ASIGNAR PERMISOS CRUD CON SWITCH -->
    <div class="modal fade" id="modalPermisosRol" tabindex="-1" aria-labelledby="modalPermisosRolLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-success text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalPermisosRolLabel">
              <span class="material-symbols-rounded text-sm me-1 align-middle">shield_person</span> Asignar Permisos CRUD al Rol
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <p class="text-xs text-secondary mb-3" id="nombreRolSeleccionadoText">Configurando matriz CRUD para: <strong>Administrador General</strong></p>
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Módulo del Sistema</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ver (Read)</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Crear (Create)</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Editar (Update)</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Eliminar (Delete)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-xs font-weight-bold text-dark ps-2">Panel Principal y Métricas</td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" disabled></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" disabled></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" disabled></div></td>
                  </tr>
                  <tr>
                    <td class="text-xs font-weight-bold text-dark ps-2">Venta de Pasajes (POS)</td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox"></div></td>
                  </tr>
                  <tr>
                    <td class="text-xs font-weight-bold text-dark ps-2">Gestión de Encomiendas y Carga</td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox"></div></td>
                  </tr>
                  <tr>
                    <td class="text-xs font-weight-bold text-dark ps-2">Despacho de Flotas y Turnos</td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox"></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox"></div></td>
                  </tr>
                  <tr>
                    <td class="text-xs font-weight-bold text-dark ps-2">Caja Diaria y Liquidaciones</td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox"></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox"></div></td>
                  </tr>
                  <tr>
                    <td class="text-xs font-weight-bold text-dark ps-2">Configuración de Rutas y Tarifas</td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                  </tr>
                  <tr>
                    <td class="text-xs font-weight-bold text-dark ps-2">Administración de Usuarios y Roles</td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                    <td class="text-center"><div class="form-check form-switch ps-0 d-flex justify-content-center"><input class="form-check-input ms-0" type="checkbox" checked></div></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" class="btn bg-gradient-success mb-0 btn-sm px-4" data-bs-dismiss="modal">Guardar Permisos</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: CREAR NUEVO ROL -->
    <div class="modal fade" id="modalCrearRol" tabindex="-1" aria-labelledby="modalCrearRolLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-success text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalCrearRolLabel">
              <span class="material-symbols-rounded text-sm me-1 align-middle">add_moderator</span> Crear Nuevo Rol
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Nombre del Rol</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" class="form-control border-0 ps-2" placeholder="Ej. Supervisor de Agencia">
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Descripción de Funciones</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <textarea class="form-control border-0 ps-2" rows="3" placeholder="Breve descripción del alcance del rol..."></textarea>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Permiso Base de Herencia (Opcional)</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <select class="form-select border-0 ps-2 text-xs">
                    <option selected>Sin herencia (Personalizar desde cero)</option>
                    <option>Copiar permisos de Boletero / POS</option>
                    <option>Copiar permisos de Controlador de Salidas</option>
                  </select>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-success mb-0 btn-sm px-4">Crear Rol</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>