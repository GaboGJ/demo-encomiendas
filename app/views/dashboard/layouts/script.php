<!-- SCRIPTS -->
  <script src="<?= URL; ?>/public/assets/js/core/popper.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/core/bootstrap.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/plugins/chartjs.min.js"></script>

  <!-- JQUERY & DATATABLES SCRIPTS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/plugins/jquery.dataTables.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/vendor/dataTables.bootstrap5.js"></script>
  <script src="<?= URL; ?>/public/assets/js/vendor/dataTables.responsive.min.js"></script>

  <script>
    var ctx = document.getElementById("chart-bars").getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
        datasets: [{
          label: "Paquetes",
          tension: 0.4,
          borderWidth: 0,
          borderRadius: 4,
          borderSkipped: false,
          backgroundColor: "#4CAF50",
          data: [65, 80, 72, 95, 120, 142, 40],
          barThickness: 'flex'
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: '#e5e5e5' }, ticks: { color: "#737373" } },
          x: { grid: { display: false }, ticks: { color: '#737373' } }
        }
      }
    });

    var ctx2 = document.getElementById("chart-line").getContext("2d");
    new Chart(ctx2, {
      type: "line",
      data: {
        labels: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep"],
        datasets: [{
          label: "Fletes",
          tension: 0,
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: "#4CAF50",
          borderColor: "#4CAF50",
          data: [12000, 14500, 13200, 18000, 21000, 25000, 23000, 28000, 31000],
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: '#e5e5e5' }, ticks: { color: '#737373' } },
          x: { grid: { display: false }, ticks: { color: '#737373' } }
        }
      }
    });

    var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");
    new Chart(ctx3, {
      type: "line",
      data: {
        labels: ["Sem 1", "Sem 2", "Sem 3", "Sem 4"],
        datasets: [{
          label: "Efectividad %",
          tension: 0,
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: "#4CAF50",
          borderColor: "#4CAF50",
          data: [96, 98, 97, 99],
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: '#e5e5e5' }, ticks: { color: '#737373' } },
          x: { grid: { display: false }, ticks: { color: '#737373' } }
        }
      }
    });

  </script>
  <script>
    function inicializarDataTable(tableId, customOptions = {}) {
        var tableElement = $(tableId);
        if (tableElement.length === 0) return;

        if ($.fn.DataTable.isDataTable(tableElement)) {
            tableElement.DataTable().destroy();
        }
        
        var defaultOptions = {
            dom: '<"row align-items-center px-3 py-2 gy-2"<"col-12 col-md-6 text-center text-md-start"l><"col-12 col-md-6 text-center text-md-end"f>>' +
                 '<"row"<"col-12"tr>>' +
                 '<"row align-items-center p-3 gy-2"<"col-12 col-md-5 text-center text-md-start"i><"col-12 col-md-7 d-flex justify-content-center justify-content-md-end"p>>',
            buttons: [],
            responsive: {
                details: {
                    type: 'inline',
                    target: 0,
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col) {
                            return col.hidden ?
                                '<div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light gap-2">' +
                                    '<span class="text-secondary text-xxs font-weight-bolder text-uppercase">' + col.title + ':</span>' +
                                    '<span class="text-xs font-weight-bold text-dark text-wrap ms-auto">' + col.data + '</span>' +
                                '</div>' : '';
                        }).join('');

                        return data ? $('<div class="card card-body bg-gray-100 shadow-none p-2 my-2 border-radius-md w-100 overflow-hidden"/>').append(data) : false;
                    }
                }
            },
            autoWidth: false,
            pageLength: 5,
            lengthMenu: [
                [5, 10, 25, 50, -1],
                ['5', '10', '25', '50', 'Todos']
            ],
            language: {
                lengthMenu: "Mostrar _MENU_ registros", 
                emptyTable: "No hay registros disponibles",
                info: "<span class='text-xs font-weight-bold text-secondary'>Mostrando _START_ a _END_ de _TOTAL_ Registros</span>",
                infoEmpty: "<span class='text-xs font-weight-bold text-secondary'>Mostrando 0 a 0 de 0 Registros</span>",
                infoFiltered: "(Filtrado de _MAX_ total Registros)",
                search: "",
                zeroRecords: "Sin resultados encontrados",
                paginate: {
                    first: "«",
                    last: "»",
                    next: "›",
                    previous: "‹"
                }
            },
            initComplete: function() {
                var placeholderText = customOptions.placeholder || 'Buscar...'; 
                var dt = this.api(); 
                var tableIdSelector = '#' + dt.table().node().id;

                var lengthSelectDiv = $(tableIdSelector + '_length');
                var lengthSelect = lengthSelectDiv.find('select');
                
                lengthSelect.removeClass('form-control form-control-sm')
                            .addClass('form-select form-select-sm border-radius-md mx-2 d-inline-block')
                            .css({ 'width': 'auto', 'font-size': '0.75rem' });

                lengthSelectDiv.find('label').addClass('text-xs font-weight-bold text-secondary mb-0 d-inline-flex align-items-center');

                var filterContainer = $(tableIdSelector + '_filter'); 
                var dtLabel = filterContainer.find('label');
                var input = dtLabel.find('input[type="search"]');   

                input.removeClass('form-control-sm')
                     .addClass('form-control form-control-sm border border-radius-md px-2')
                     .attr('placeholder', placeholderText)
                     .css({
                         'display': 'inline-block',
                         'width': '100%',
                         'max-width': '220px'
                     });

                input.detach();
                dtLabel.empty().append(input);

                var paginateContainer = $(tableIdSelector + '_paginate');
                paginateContainer.find('.pagination').addClass('pagination-success pagination-sm mb-0');

                dt.on('draw', function() {
                  paginateContainer.find('.page-item.active .page-link').addClass('text-white');
                });
                paginateContainer.find('.page-item.active .page-link').addClass('text-white');
            }
        };

        var finalOptions = $.extend(true, {}, defaultOptions, customOptions);
        return tableElement.DataTable(finalOptions);
    }

   
  </script>
  <script>
    $(document).ready(function() {
        // 1. Inicializar la tabla visible por defecto
        inicializarDataTable('#datatable-encomiendas', { ordering: false, placeholder: 'Buscar envío...' });

        // 2. Variable para controlar si la segunda tabla ya fue inicializada
        var llegadasInicializada = false;

        // 3. Escuchar el cambio de pestañas de Bootstrap
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var targetTab = $(e.target).attr('href');
            
            if (targetTab === '#tab-llegadas' && !llegadasInicializada) {
                inicializarDataTable('#datatable-llegadas', { ordering: false, placeholder: 'Buscar llegada...' });
                llegadasInicializada = true;
            }

            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    });
      $(document).ready(function() {
        inicializarDataTable('#datatable-pasajes', { ordering: false, placeholder: 'Buscar boleto o pasajero...' });
    });
    $(document).ready(function() {
        inicializarDataTable('#datatable-despachos', { ordering: false, placeholder: 'Buscar turno o chofer...' });
    });
        $(document).ready(function() {
        inicializarDataTable('#datatable-cajas', { ordering: false, placeholder: 'Buscar caja o cajero...' });
    });
      $(document).ready(function() {
        inicializarDataTable('#datatable-flotas', { ordering: false, placeholder: 'Buscar unidad o placa...' });
    });
      $(document).ready(function() {
        inicializarDataTable('#datatable-conductores', { ordering: false, placeholder: 'Buscar conductor o socio...' });
    });
     $(document).ready(function() {
        inicializarDataTable('#datatable-rutas', { ordering: false, placeholder: 'Buscar ruta o destino...' });

        var modalEditarTarifa = document.getElementById('modalEditarTarifa');
        if (modalEditarTarifa) {
          modalEditarTarifa.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var ruta = button.getAttribute('data-ruta');
            var pasaje = button.getAttribute('data-pasaje');
            var encomienda = button.getAttribute('data-encomienda');

            var modalInputRuta = modalEditarTarifa.querySelector('#inputRutaNombre');
            var modalInputPasaje = modalEditarTarifa.querySelector('#inputTarifaPasaje');
            var modalInputEncomienda = modalEditarTarifa.querySelector('#inputTarifaEncomienda');

            modalInputRuta.value = ruta;
            modalInputPasaje.value = pasaje;
            modalInputEncomienda.value = encomienda;
          });
        }
    });
      $(document).ready(function() {
        inicializarDataTable('#datatable-vehiculos', { ordering: false, placeholder: 'Buscar vehículo o placa...' });
    });
     $(document).ready(function() {
        inicializarDataTable('#datatable-usuarios', { ordering: false, placeholder: 'Buscar usuario...' });
    });
       $(document).ready(function() {
        inicializarDataTable('#datatable-roles', { ordering: false, placeholder: 'Buscar rol...' });
    });

    function cargarPermisosRol(rol) {
      document.getElementById('nombreRolSeleccionadoText').innerHTML = 'Configurando matriz CRUD para: <strong>' + rol + '</strong>';
    }
    $(document).ready(function() {
        // Inicializar la tabla de sindicatos
        inicializarDataTable('#datatable-sindicatos', { ordering: false, placeholder: 'Buscar sindicato o secretario...' });
    });
    // Inicializar DataTable para la tabla de reportes
  $(document).ready(function() {
    inicializarDataTable('#datatable-reportes', { ordering: false, placeholder: 'Buscar en el reporte...' });
  });
</script>
  <script src="<?= URL; ?>/public/assets/js/material-dashboard.min.js?v=3.2.0"></script>

  </body>
</html>