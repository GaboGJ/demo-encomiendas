  <!-- CONTENIDO PRINCIPAL: CAJAS Y RECEPCIONES DE ENCOMIENDAS -->
    <div class="container-fluid py-3 flex-grow-1">
      
      <!-- TARJETAS DE MÉTRICAS FINANCIERAS RÁPIDAS -->
      <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card border-0 shadow-sm border-radius-xl">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Caja Actual (Abierta)</p>
                    <h5 class="font-weight-bolder text-success mb-0">
                      Bs. 2,840.00
                      <span class="text-success text-xs font-weight-bolder">Activa</span>
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">point_of_sale</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Ingresos Recepción Hoy</p>
                    <h5 class="font-weight-bolder text-dark mb-0">
                      Bs. 1,620.00
                      <span class="text-success text-xs font-weight-bolder">24 Encomiendas</span>
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-dark shadow-dark text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">inventory_2</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Entregas Realizadas Hoy</p>
                    <h5 class="font-weight-bolder text-info mb-0">
                      19 Paquetes
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">local_shipping</i>
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
                    <p class="text-xs text-secondary mb-0 font-weight-bold">Cajas Cerradas (Mes)</p>
                    <h5 class="font-weight-bolder text-dark mb-0">
                      42 Turnos
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                    <i class="material-symbols-rounded opacity-10">task_alt</i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>


      <!-- TABLA PRINCIPAL: LISTADO DE CAJAS Y RECEPCIONES POR FECHA -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
            
            <div class="card-header bg-white p-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
              <div>
                <h5 class="font-weight-bolder text-dark mb-0">Historial de Cajas y Recepciones por Fecha</h5>
                <p class="text-xs text-secondary mb-0">Listado general de turnos de caja para recepción y entrega de encomiendas, con opción de arqueo en cajas abiertas</p>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn bg-gradient-success mb-0 d-flex align-items-center gap-1 shadow-sm text-sm" data-bs-toggle="modal" data-bs-target="#modalAperturaCaja">
                  <i class="material-symbols-rounded text-sm">lock_open</i> Aperturar Nueva Caja
                </button>
              </div>
            </div>

            <hr class="horizontal dark my-0 opacity-2">

            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table id="datatable-cajas" class="table table-borderless align-items-center mb-0 w-100">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-5 pe-4 border-top border-bottom border-light">Código / Fecha</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Cajero / Responsable</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Recepciones & Entregas</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Balance Total (Bs.)</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                      <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-5 ps-4 border-top border-bottom border-light">Acciones y Arqueo</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- CAJA ABIERTA (CON OPCIÓN DE ARQUEO Y CIERRE) -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-success border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">point_of_sale</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#CJ-104</h6>
                            <span class="text-xxs text-secondary font-weight-bold">14 Sep 2026 <span class="text-success">(Turno Mañana)</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Marcos Soliz (Ventanilla 1)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Apertura: 08:00 AM</span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">24 Rec. / 19 Ent.</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Encomiendas de Envíos</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-success">Bs. 2,840.00</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-success border-radius-pill px-3 py-1 font-weight-bold">Abierta</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-outline-success btn-xs mb-0 px-2 py-1 font-weight-bold d-flex align-items-center gap-1 shadow-none" data-bs-toggle="modal" data-bs-target="#modalArqueoCaja" title="Realizar Arqueo y Cierre">
                            <i class="material-symbols-rounded text-xs">calculate</i> Arqueo / Cierre
                          </button>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Historial de Movimientos">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                        </div>
                      </td>
                    </tr>

                    <!-- CAJAS CERRADAS (HISTORIAL) -->
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">inventory_2</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#CJ-103</h6>
                            <span class="text-xxs text-secondary font-weight-bold">13 Sep 2026 <span class="text-dark">(Turno Tarde)</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Esteban Tineo (Ventanilla 2)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Cierre: 20:30 PM</span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">32 Rec. / 28 Ent.</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Encomiendas de Envíos</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 3,450.00</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">Cerrada</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Historial de Movimientos">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Reporte de Caja">
                            <i class="material-symbols-rounded text-sm">print</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">inventory_2</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#CJ-102</h6>
                            <span class="text-xxs text-secondary font-weight-bold">13 Sep 2026 <span class="text-dark">(Turno Mañana)</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Marcos Soliz (Ventanilla 1)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Cierre: 14:00 PM</span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">19 Rec. / 22 Ent.</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Encomiendas de Envíos</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 2,150.00</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">Cerrada</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Historial de Movimientos">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Reporte de Caja">
                            <i class="material-symbols-rounded text-sm">print</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">inventory_2</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#CJ-101</h6>
                            <span class="text-xxs text-secondary font-weight-bold">12 Sep 2026 <span class="text-dark">(Turno Tarde)</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Esteban Tineo (Ventanilla 2)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Cierre: 21:00 PM</span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">27 Rec. / 25 Ent.</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Encomiendas de Envíos</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 3,100.00</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">Cerrada</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Historial de Movimientos">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Reporte de Caja">
                            <i class="material-symbols-rounded text-sm">print</i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="py-3 ps-5 pe-4">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-dark border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">inventory_2</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#CJ-100</h6>
                            <span class="text-xxs text-secondary font-weight-bold">12 Sep 2026 <span class="text-dark">(Turno Mañana)</span></span>
                          </div>
                        </div>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark">Marcos Soliz (Ventanilla 1)</span>
                          <span class="text-xxs text-secondary font-weight-bold">Cierre: 13:30 PM</span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-xs font-weight-bold text-dark">21 Rec. / 18 Ent.</span>
                        <span class="d-block text-xxs text-secondary font-weight-bold">Encomiendas de Envíos</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-dark">Bs. 2,420.00</span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm bg-gradient-secondary border-radius-pill px-3 py-1 font-weight-bold">Cerrada</span>
                      </td>
                      <td class="align-middle text-end py-3 pe-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Ver Historial de Movimientos">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </a>
                          <a href="javascript:;" class="btn btn-link text-dark p-2 mb-0" data-bs-toggle="tooltip" title="Imprimir Reporte de Caja">
                            <i class="material-symbols-rounded text-sm">print</i>
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

    <!-- MODAL: APERTURA DE NUEVA CAJA -->
    <div class="modal fade" id="modalAperturaCaja" tabindex="-1" aria-labelledby="modalAperturaCajaLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-success text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalAperturaCajaLabel">
              <i class="material-symbols-rounded text-sm me-1 align-middle">lock_open</i> Aperturar Turno de Caja
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Cajero Responsable</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" class="form-control border-0 ps-2" value="Marcos Soliz (Ventanilla 1)" readonly>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Monto Inicial / Fondo (Bs.)</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" value="200.00">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Fecha y Hora</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2" value="14 Sep 2026 - 08:00" readonly>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Turno Operativo</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <select class="form-select border-0 ps-2 text-xs">
                    <option selected>Turno Mañana (08:00 - 14:00)</option>
                    <option>Turno Tarde (14:00 - 21:00)</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Observaciones de Apertura</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <textarea class="form-control border-0 p-2" rows="2" placeholder="Fondo de cambio verificado y entregado en orden..."></textarea>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-success mb-0 btn-sm px-4">Abrir Caja</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: ARQUEO DE CAJA Y CIERRE (SOLO PARA CAJAS ABIERTAS) -->
    <div class="modal fade" id="modalArqueoCaja" tabindex="-1" aria-labelledby="modalArqueoCajaLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
          <div class="modal-header bg-gradient-success text-white">
            <h5 class="modal-title font-weight-bold text-white" id="modalArqueoCajaLabel">
              <i class="material-symbols-rounded text-sm me-1 align-middle">calculate</i> Arqueo y Cierre de Caja [#CJ-104]
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row mb-3">
              <div class="col-md-4">
                <div class="card bg-gray-100 border-0 p-3 shadow-none">
                  <span class="text-xxs font-weight-bolder text-secondary text-uppercase">Fondo Inicial:</span>
                  <h6 class="font-weight-bolder text-dark mb-0">Bs. 200.00</h6>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-gray-100 border-0 p-3 shadow-none">
                  <span class="text-xxs font-weight-bolder text-secondary text-uppercase">Ingresos Recepción POS:</span>
                  <h6 class="font-weight-bolder text-success mb-0">Bs. 2,640.00</h6>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-gray-100 border-0 p-3 shadow-none">
                  <span class="text-xxs font-weight-bolder text-secondary text-uppercase">Total Esperado en Sistema:</span>
                  <h6 class="font-weight-bolder text-dark mb-0">Bs. 2,840.00</h6>
                </div>
              </div>
            </div>

            <form>
              <h6 class="text-xs font-weight-bolder text-uppercase text-secondary mb-3">Conteo Físico de Efectivo (Billetes y Monedas)</h6>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Billetes de Bs. 200</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" value="10">
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Billetes de Bs. 100</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" value="5">
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Billetes de Bs. 50</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" value="4">
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Billetes de Bs. 20 / 10</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" value="12">
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Monedas (Total Bs.)</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="number" class="form-control border-0 ps-2" value="40.00">
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-xs font-weight-bold text-dark">Total Efectivo Contado</label>
                  <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                    <input type="text" class="form-control border-0 ps-2 font-weight-bold text-success" value="Bs. 2,840.00" readonly>
                  </div>
                </div>
              </div>

              <div class="mb-3 mt-2">
                <label class="form-label text-xs font-weight-bold text-dark">Diferencia / Descuere (Bs.)</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <input type="text" class="form-control border-0 ps-2 font-weight-bold text-dark" value="Bs. 0.00 (Sin Diferencia)" readonly>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label text-xs font-weight-bold text-dark">Observaciones del Arqueo y Cierre</label>
                <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
                  <textarea class="form-control border-0 p-2" rows="2" placeholder="Caja cuadrada sin observaciones ni faltantes..."></textarea>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary mb-0 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-danger mb-0 btn-sm px-4">Registrar Cierre Definitivo</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>