    <!-- CONTENIDO PRINCIPAL: CONDUCTORES Y SOSCIOS -->
    <div class="container-fluid py-3 flex-grow-1">
      
      <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
      <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Socios Titulares</p>
                    <h5 class="font-weight-bolder text-dark mb-0">24 Registrados</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Choferes Asociados</p>
                    <h5 class="font-weight-bolder text-dark mb-0">30 Titulares/Plantilla</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-dark shadow-dark text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">badge</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Licencias Vigentes</p>
                    <h5 class="font-weight-bolder text-info mb-0">96% Activas</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">verified</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Aportes Sindicales</p>
                    <h5 class="font-weight-bolder text-success mb-0">Al día (Mes)</h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">payments</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

   

      <!-- TABLA PRINCIPAL DE CONDUCTORES Y SOCIOS -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
            
            <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
              <div>
                <h5 class="font-weight-bolder text-dark mb-0">Listado General de Personal</h5>
                <p class="text-xs text-secondary mb-0">Directorio de choferes titulares, socios del sindicato y vigencia de licencias</p>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn bg-gradient-dark mb-0 d-flex align-items-center gap-1 shadow-sm text-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoChofer">
                  <i class="material-symbols-rounded text-sm">person_add</i> Registrar Chofer / Socio
                </button>
              </div>
            </div>

            <hr class="horizontal dark my-0 opacity-2">

            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table id="datatable-conductores" class="table table-borderless align-items-center mb-0 w-100">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-5 pe-4 border-top border-bottom border-light">Conductor / C.I.</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Licencia / Categoría</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Rol / Afiliación</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Contacto / Móvil</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-5 ps-4 border-top border-bottom border-light">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">person</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Carlos Pérez</h6>
                            <span class="text-xxs text-secondary font-weight-bold">C.I.: 4819201 Beni</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Cat. C (Vigente)</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Exp: SEGIP</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Socio Titular</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">71123456</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Expediente">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Conductor">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-info border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">person</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Esteban Tineo</h6>
                            <span class="text-xxs text-secondary font-weight-bold">C.I.: 3910293 Beni</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Cat. C (Vigente)</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Exp: SEGIP</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-info border-radius-pill px-3 py-1 font-weight-bold">Socio / Chofer</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">72891029</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Expediente">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Conductor">
                            <i class="material-symbols-rounded text-sm">edit</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-secondary border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">person</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">Raúl Navia</h6>
                            <span class="text-xxs text-secondary font-weight-bold">C.I.: 5192031 Beni</span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <span class="text-xs font-weight-bold text-dark">Cat. C (Vigente)</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Exp: SEGIP</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">Chofer Asalariado</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">73019283</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Expediente">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Editar Conductor">
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

    <!-- MODAL: REGISTRAR NUEVO CHOFER / SOCIO -->
    <div class="modal fade" id="modalNuevoChofer" tabindex="-1" aria-labelledby="modalNuevoChoferLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-dark text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalNuevoChoferLabel">
              <i class="material-symbols-rounded text-sm me-1 align-middle">person_add</i> Registrar Chofer o Socio
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Nombre Completo</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" class="form-control border-0 ps-2" placeholder="Nombres y Apellidos">
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Cédula de Identidad (C.I.)</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" placeholder="Nro de documento">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Número de Licencia</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" placeholder="Licencia conducir">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Categoría</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <select class="form-select border-0 ps-2 text-xs">
                      <option selected>Categoría C</option>
                      <option>Categoría P</option>
                      <option>Categoría B</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Rol en el Sindicato</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <select class="form-select border-0 ps-2 text-xs">
                      <option selected>Socio Titular</option>
                      <option>Chofer Asalariado</option>
                      <option>Socio Colaborador</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Teléfono de Contacto</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" class="form-control border-0 ps-2" placeholder="Celular / WhatsApp">
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-dark mb-0 btn-sm px-4">Guardar Registro</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>