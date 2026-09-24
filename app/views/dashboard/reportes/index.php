<!-- CONTENIDO PRINCIPAL: REPORTES ECONÓMICOS MULTIPERIODO -->
<div class="container-fluid py-3 flex-grow-1">

  <!-- TARJETAS DE MÉTRICAS RÁPIDAS (Se actualizan dinámicamente) -->
  <div class="row mb-4 no-print">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card border-0 shadow-sm border-radius-xl">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-xs text-secondary mb-0 font-weight-bold">Saldo Inicial / Vienen</p>
                <h5 class="font-weight-bolder text-dark mb-0" id="metricSaldoInicial">Bs. 8,452.00</h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-dark shadow-dark text-center border-radius-md">
                <i class="material-symbols-rounded opacity-10">account_balance_wallet</i>
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
                <p class="text-xs text-secondary mb-0 font-weight-bold">Ingresos Totales</p>
                <h5 class="font-weight-bolder text-success mb-0" id="metricIngresos">Bs. 181,452.00</h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
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
                <p class="text-xs text-secondary mb-0 font-weight-bold">Egresos y Préstamos</p>
                <h5 class="font-weight-bolder text-danger mb-0" id="metricEgresos">Bs. 148,572.00</h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-md">
                <i class="material-symbols-rounded opacity-10">trending_down</i>
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
                <p class="text-xs text-secondary mb-0 font-weight-bold">Saldo Caja Actual</p>
                <h5 class="font-weight-bolder text-success mb-0" id="metricSaldoFinal">Bs. 32,880.00</h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md">
                <i class="material-symbols-rounded opacity-10">savings</i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- PANEL DE FILTROS Y CONFIGURACIÓN (Oculto al imprimir) -->
  <div class="row mb-4 no-print">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-3">
          <div>
            <h5 class="font-weight-bolder text-dark mb-1">Generador de Informes Económicos</h5>
            <p class="text-xs text-secondary mb-0">Sindicato 1ro de Mayo Ruta Norte | Control Financiero Dinámico</p>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn bg-gradient-success mb-0 d-flex align-items-center gap-1 shadow-sm text-sm" onclick="imprimirReporteDatos()">
              <i class="material-symbols-rounded text-sm">print</i> Imprimir / Exportar Datos
            </button>
          </div>
        </div>

        <!-- SELECTORES DE PERIODO -->
        <div class="row g-3 pt-2 align-items-end">
          <div class="col-lg-3 col-md-4">
            <label class="form-label text-xs font-weight-bold text-dark">Tipo de Reporte</label>
            <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
              <select id="tipoReporteSelect" class="form-select border-0 ps-2 text-xs" onchange="cambiarTipoReporte(this.value)">
                <option value="consolidado" selected>Informe Consolidado (General)</option>
                <option value="diario">Informe Diario</option>
                <option value="semanal">Informe Semanal</option>
                <option value="mensual">Informe Mensual Específico</option>
                <option value="anual">Informe Anual</option>
                <option value="rango">Por Rango de Fecha</option>
              </select>
            </div>
          </div>

          <div class="col-lg-3 col-md-4" id="contenedorDinamicoLabel">
            <label class="form-label text-xs font-weight-bold text-dark" id="labelDinamicoText">Seleccionar Periodo</label>
            <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
              <select id="selectValorFiltro" class="form-select border-0 ps-2 text-xs">
                <option value="todos">Todos los meses registrados</option>
              </select>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 d-none" id="campoFechaInicio">
            <label class="form-label text-xs font-weight-bold text-dark">Fecha Inicio</label>
            <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
              <input type="date" id="inputFechaIni" class="form-control border-0 ps-2 text-xs" value="2026-03-01">
            </div>
          </div>

          <div class="col-lg-2 col-md-3 d-none" id="campoFechaFin">
            <label class="form-label text-xs font-weight-bold text-dark">Fecha Fin</label>
            <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
              <input type="date" id="inputFechaFin" class="form-control border-0 ps-2 text-xs" value="2026-03-31">
            </div>
          </div>

          <div class="col-lg-3 col-md-4">
            <button type="button" class="btn bg-gradient-dark w-100 mb-0 d-flex align-items-center justify-content-center gap-1 shadow-sm" onclick="aplicarFiltroPrueba()">
              <i class="material-symbols-rounded text-sm">filter_alt</i> Aplicar y Actualizar Datos
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- CONTENEDOR EXCLUSIVO PARA IMPRESIÓN / DATOS OBTENIDOS -->
  <div class="row" id="areaImpresionReporte">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">
        
        <div class="card-header bg-white p-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
          <div>
            <h6 class="font-weight-bolder text-dark mb-0" id="tituloReporteVista">Resumen Informe Económico Consolidado</h6>
            <p class="text-xxs text-secondary mb-0" id="subtituloReporteVista">Sindicato 1ro de Mayo Ruta Norte</p>
          </div>
          <div>
            <span class="badge bg-gradient-success text-xxs px-2 py-1" id="badgeFiltroActivo">Filtro: Consolidado</span>
          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="tablaReportesDinamica" class="table table-striped table-bordered align-items-center mb-0 w-100 text-xs">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 ps-3">Periodo / Fecha</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 px-2">Ingresos</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 px-2">Vienen</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 px-2">Total Ing.</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 px-2">Egresos</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 px-2">Préstamos</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 px-2">Saldo Final</th>
                </tr>
              </thead>
              <tbody id="cuerpoTablaDatos">
                <!-- Inyectado por JS -->
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

</div>

<!-- ESTILOS CSS PARA IMPRESIÓN LIMPIA Y SIN DESBORDAMIENTO -->
<style>
@media print {
  body * {
    visibility: hidden;
  }
  #areaImpresionReporte, #areaImpresionReporte * {
    visibility: visible;
  }
  #areaImpresionReporte {
    position: absolute;
    left: 0;
    top: 0;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .card {
    box-shadow: none !important;
    border: none !important;
  }
  table.table {
    width: 100% !important;
    font-size: 10px !important;
    border-collapse: collapse !important;
  }
  table.table th, table.table td {
    padding: 5px 6px !important;
  }
  @page {
    size: auto;
    margin: 10mm;
  }
}
</style>

<!-- SCRIPT DE DATOS Y DATATABLES -->
<script>
  const datosPrueba = {
    consolidado: [
      { periodo: "Saldo Diciembre", ingresos: "-", vienen: "8,452", total: "-", egresos: "-", prestamos: "-", saldo: "Bs. 8,452" },
      { periodo: "Enero 2026", ingresos: "28,770", vienen: "8,452", total: "37,222", egresos: "24,305", prestamos: "2,500", saldo: "Bs. 12,917" },
      { periodo: "Febrero 2026", ingresos: "18,530", vienen: "12,917", total: "31,447", egresos: "14,881", prestamos: "500", saldo: "Bs. 12,921" },
      { periodo: "Marzo 2026", ingresos: "19,200", vienen: "9,102", total: "28,302", egresos: "12,400", prestamos: "1,430", saldo: "Bs. 15,421" },
      { periodo: "Abril 2026", ingresos: "16,330", vienen: "12,921", total: "29,251", egresos: "22,649", prestamos: "5,780", saldo: "Bs. 10,232" },
      { periodo: "Mayo 2026", ingresos: "23,240", vienen: "15,421", total: "38,661", egresos: "13,650", prestamos: "0", saldo: "Bs. 15,372" },
      { periodo: "Junio 2026", ingresos: "18,790", vienen: "10,232", total: "29,022", egresos: "17,386", prestamos: "500", saldo: "Bs. 29,926" },
      { periodo: "Julio 2026", ingresos: "32,440", vienen: "15,372", total: "47,812", egresos: "12,746", prestamos: "0", saldo: "Bs. 32,840" },
      { periodo: "Agosto 2026 (20 Días)", ingresos: "15,680", vienen: "29,926", total: "45,586", egresos: "13,786", prestamos: "10,710", saldo: "Bs. 32,880" }
    ],
    diario: [
      { periodo: "19 Ago 2026 (Hoy)", ingresos: "850", vienen: "32,030", total: "32,880", egresos: "0", prestamos: "0", saldo: "Bs. 32,880" },
      { periodo: "18 Ago 2026", ingresos: "920", vienen: "31,110", total: "32,030", egresos: "0", prestamos: "0", saldo: "Bs. 32,030" },
      { periodo: "17 Ago 2026", ingresos: "780", vienen: "30,330", total: "31,110", egresos: "0", prestamos: "0", saldo: "Bs. 31,110" }
    ],
    semanal: [
      { periodo: "Semana 33 (Ago)", ingresos: "4,200", vienen: "28,680", total: "32,880", egresos: "1,200", prestamos: "0", saldo: "Bs. 31,680" },
      { periodo: "Semana 32 (Ago)", ingresos: "5,100", vienen: "24,580", total: "29,680", egresos: "1,000", prestamos: "0", saldo: "Bs. 28,680" }
    ],
    mensual: [
      { periodo: "Marzo 2026", ingresos: "19,200", vienen: "9,102", total: "28,302", egresos: "12,400", prestamos: "1,430", saldo: "Bs. 15,421" }
    ],
    anual: [
      { periodo: "Gestión 2025 (Histórico)", ingresos: "210,400", vienen: "6,200", total: "216,600", egresos: "198,100", prestamos: "10,000", saldo: "Bs. 8,452" },
      { periodo: "Gestión 2026 (Parcial)", ingresos: "153,000", vienen: "8,452", total: "161,452", egresos: "116,802", prestamos: "21,770", saldo: "Bs. 32,880" }
    ]
  };

  function cambiarTipoReporte(tipo) {
    const selectValor = document.getElementById('selectValorFiltro');
    const labelText = document.getElementById('labelDinamicoText');
    const campoInicio = document.getElementById('campoFechaInicio');
    const campoFin = document.getElementById('campoFechaFin');
    const contenedorLabel = document.getElementById('contenedorDinamicoLabel');

    selectValor.innerHTML = "";

    if (tipo === 'rango') {
      contenedorLabel.classList.add('d-none');
      campoInicio.classList.remove('d-none');
      campoFin.classList.remove('d-none');
    } else {
      contenedorLabel.classList.remove('d-none');
      campoInicio.classList.add('d-none');
      campoFin.classList.add('d-none');

      if (tipo === 'consolidado') {
        labelText.innerText = "Vista General";
        selectValor.innerHTML = '<option value="todos">Todos los meses (7 Meses + 20 Días)</option>';
      } else if (tipo === 'mensual') {
        labelText.innerText = "Seleccionar Mes";
        selectValor.innerHTML = '<option value="marzo" selected>Marzo 2026</option>';
      } else if (tipo === 'diario') {
        labelText.innerText = "Días de Agosto";
        selectValor.innerHTML = '<option value="ultimos3">Últimos 3 días (Prueba)</option>';
      } else if (tipo === 'semanal') {
        labelText.innerText = "Seleccionar Semana";
        selectValor.innerHTML = '<option value="semana33">Semana 33 (Agosto)</option>';
      } else if (tipo === 'anual') {
        labelText.innerText = "Seleccionar Gestión";
        selectValor.innerHTML = '<option value="2026" selected>Gestión 2026</option>';
      }
    }
  }

  function aplicarFiltroPrueba() {
    const tipo = document.getElementById('tipoReporteSelect').value;
    const tbody = document.getElementById('cuerpoTablaDatos');
    const tituloVista = document.getElementById('tituloReporteVista');
    const badge = document.getElementById('badgeFiltroActivo');

    let datosSeleccionados = datosPrueba[tipo] || datosPrueba.consolidado;

    tituloVista.innerText = `Reporte Económico - ${tipo.toUpperCase()}`;
    badge.innerText = `Filtro: ${tipo}`;

    // Destruir instancia previa de DataTable de forma limpia antes de repoblar
    if ($.fn.DataTable.isDataTable('#tablaReportesDinamica')) {
      $('#tablaReportesDinamica').DataTable().destroy();
    }

    tbody.innerHTML = "";
    datosSeleccionados.forEach(row => {
      let tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="py-2 ps-3"><span class="text-xs font-weight-bold text-dark">${row.periodo}</span></td>
        <td class="py-2 px-2"><span class="text-xs text-dark">${row.ingresos}</span></td>
        <td class="py-2 px-2"><span class="text-xs text-secondary">${row.vienen}</span></td>
        <td class="py-2 px-2"><span class="text-xs font-weight-bold text-success">${row.total}</span></td>
        <td class="py-2 px-2"><span class="text-xs font-weight-bold text-danger">${row.egresos}</span></td>
        <td class="py-2 px-2"><span class="text-xs text-dark">${row.prestamos}</span></td>
        <td class="text-center py-2 px-2"><span class="text-xs font-weight-bolder text-dark">${row.saldo}</span></td>
      `;
      tbody.appendChild(tr);
    });

    // Inicializar DataTable usando tu función estándar
    inicializarDataTable('#tablaReportesDinamica', { 
      ordering: false, 
      placeholder: 'Buscar en el reporte...' 
    });
  }

  function imprimirReporteDatos() {
    window.print();
  }

  $(document).ready(function() {
    aplicarFiltroPrueba();
  });
</script>