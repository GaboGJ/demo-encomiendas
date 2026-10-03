<!-- SCRIPTS -->
  <script src="<?= URL; ?>/public/assets/js/core/popper.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/core/bootstrap.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/plugins/chartjs.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/tabs-animadas.js"></script>

  <!-- UTILIDADES GLOBALES REUTILIZABLES (el orden importa: utils-ui primero) -->
  <script src="<?= URL; ?>/public/assets/js/utils-ui.js"></script>
  <script src="<?= URL; ?>/public/assets/js/autocompletar.js"></script>
  <script src="<?= URL; ?>/public/assets/js/plano-vehiculo.js"></script>

  <!-- JQUERY & DATATABLES SCRIPTS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/plugins/jquery.dataTables.min.js"></script>
  <script src="<?= URL; ?>/public/assets/js/vendor/dataTables.bootstrap5.js"></script>
  <script src="<?= URL; ?>/public/assets/js/vendor/dataTables.responsive.min.js"></script>

<!-- FUNCIÓN GLOBAL DE IMPRESIÓN REUTILIZABLE -->
<script>
    /** Imprime la Hoja de Ruta / Guía de pasajeros y carga del turno */
    function imprimirManifiestoDespacho(idTurno, onFinish) {
        if (!idTurno) return;
        const url = '<?= URL ?>/despachos/imprimirManifiesto?id=' + idTurno;
        lanzarImpresionIframe(url, onFinish);
    }
    /**
     * Imprime la Guía de encomienda usando el iframe oculto
     */
    function imprimirGuiaEncomienda(idEncomienda, onFinish) {
        if (!idEncomienda) return;
        const url = '<?= URL ?>/encomiendas/imprimir?id=' + idEncomienda;
        lanzarImpresionIframe(url, onFinish);
    }

    /**
     * Imprime el Acta de Entrega usando el mismo iframe oculto
     */
    function imprimirActaEntrega(idEncomienda, onFinish) {
        if (!idEncomienda) return;
        const url = '<?= URL ?>/encomiendas/imprimirActa?id=' + idEncomienda;
        lanzarImpresionIframe(url, onFinish);
    }

    /**
     * Imprime el Boleto de pasaje usando el mismo iframe oculto
     */
    function imprimirBoletoPasaje(idPasaje, onFinish) {
        if (!idPasaje) return;
        const url = '<?= URL ?>/pasajes/imprimir?id=' + idPasaje;
        lanzarImpresionIframe(url, onFinish);
    }

    /**
     * Función interna que administra la carga en el iframe y el disparo de print()
     */
    function lanzarImpresionIframe(url, onFinish) {
        let iframe = document.getElementById('iframeImpresionGlobal');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'iframeImpresionGlobal';
            iframe.style.position = 'fixed';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.setAttribute('aria-hidden', 'true');
            document.body.appendChild(iframe);
        }

        iframe.onload = function () {
            const ventana = iframe.contentWindow;
            if (!ventana) return;

            ventana.onafterprint = function () {
                if (typeof onFinish === 'function') onFinish();
            };

            try {
                ventana.focus();
                ventana.print();
            } catch (e) {
                console.error('No se pudo abrir el diálogo de impresión:', e);
            }
        };

        iframe.src = url;
    }
</script>

<!-- GRÁFICAS DEL DASHBOARD (solo si existen en la página) -->
<script>
    (function () {
      var opcionesBase = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: '#e5e5e5' }, ticks: { color: '#737373' } },
          x: { grid: { display: false }, ticks: { color: '#737373' } }
        }
      };

      var elBars = document.getElementById("chart-bars");
      if (elBars) {
        new Chart(elBars.getContext("2d"), {
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
          options: opcionesBase
        });
      }

      var elLine = document.getElementById("chart-line");
      if (elLine) {
        new Chart(elLine.getContext("2d"), {
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
          options: opcionesBase
        });
      }

      var elTasks = document.getElementById("chart-line-tasks");
      if (elTasks) {
        new Chart(elTasks.getContext("2d"), {
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
          options: opcionesBase
        });
      }
    })();
</script>

<!-- HELPER GLOBAL DE DATATABLES -->
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

<!-- INICIALIZACIÓN DE TABLAS POR VISTA (cada una solo actúa si existe en la página) -->
<script>
    $(document).ready(function() {

        // --- Encomiendas (2 pestañas: la segunda se inicializa al mostrarse) ---
        inicializarDataTable('#datatable-encomiendas', { ordering: false, placeholder: 'Buscar envío...' });

        var llegadasInicializada = false;
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var targetTab = $(e.target).attr('href');

            if (targetTab === '#tab-llegadas' && !llegadasInicializada) {
                inicializarDataTable('#datatable-llegadas', { ordering: false, placeholder: 'Buscar llegada...' });
                llegadasInicializada = true;
            }

            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        // --- Pasajes ---
        inicializarDataTable('#datatable-pasajes', { ordering: false, placeholder: 'Buscar boleto o pasajero...' });

        // Tabla "Pasajero por asiento" de pasajes/new. Las filas las dibuja
        // pasajes-new.js; aquí solo se define la tabla y qué columnas se
        // ocultan primero en pantallas chicas (menor número = más importante).
        inicializarDataTable('#tablaPasajerosAsientos', {
            ordering: false,
            placeholder: 'Buscar asiento...',
            pageLength: 5,
            columns: [
                { data: 'asiento',  className: 'text-xs font-weight-bold', responsivePriority: 1 },
                { data: 'pasajero', className: 'text-xs font-weight-bold', responsivePriority: 2 },
                { data: 'tipo',     className: 'text-xs',                  responsivePriority: 4 },
                { data: 'acciones', className: 'text-end', orderable: false, responsivePriority: 3 }
            ]
        });

        // --- Otras vistas ---
        inicializarDataTable('#datatable-despachos',    { ordering: false, placeholder: 'Buscar turno o chofer...' });
        inicializarDataTable('#datatable-cajas',        { ordering: false, placeholder: 'Buscar caja o cajero...' });
        inicializarDataTable('#datatable-flotas',       { ordering: false, placeholder: 'Buscar unidad o placa...' });
        inicializarDataTable('#datatable-conductores',  { ordering: false, placeholder: 'Buscar conductor o socio...' });
        inicializarDataTable('#datatable-vehiculos',    { ordering: false, placeholder: 'Buscar vehículo o placa...' });
        inicializarDataTable('#datatable-usuarios',     { ordering: false, placeholder: 'Buscar usuario...' });
        inicializarDataTable('#datatable-roles',        { ordering: false, placeholder: 'Buscar rol...' });
        inicializarDataTable('#datatable-sindicatos',   { ordering: false, placeholder: 'Buscar sindicato o secretario...' });
        inicializarDataTable('#datatable-reportes',     { ordering: false, placeholder: 'Buscar en el reporte...' });
        // La función inicializarDataTable vive en layouts/script.php (se carga después de esta vista)
        inicializarDataTable('#datatable-sindicatos-papelera', { ordering: false, placeholder: 'Buscar en la papelera...' });
        // --- Rutas + modal de edición de tarifa ---
        inicializarDataTable('#datatable-rutas', { ordering: false, placeholder: 'Buscar ruta o destino...' });
        inicializarDataTable('#datatable-choferes',          { ordering: false, placeholder: 'Buscar chofer, C.I. o licencia...' });
        inicializarDataTable('#datatable-choferes-papelera', { ordering: false, placeholder: 'Buscar en la papelera...' });
        inicializarDataTable('#datatable-moviles-papelera', { ordering: false, placeholder: 'Buscar en la papelera...' });

        var modalEditarTarifa = document.getElementById('modalEditarTarifa');
        if (modalEditarTarifa) {
            modalEditarTarifa.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                modalEditarTarifa.querySelector('#inputRutaNombre').value = button.getAttribute('data-ruta');
                modalEditarTarifa.querySelector('#inputTarifaPasaje').value = button.getAttribute('data-pasaje');
                modalEditarTarifa.querySelector('#inputTarifaEncomienda').value = button.getAttribute('data-encomienda');
            });
        }
    });

    function cargarPermisosRol(rol) {
      document.getElementById('nombreRolSeleccionadoText').innerHTML = 'Configurando matriz CRUD para: <strong>' + rol + '</strong>';
    }
</script>

  <!-- LÓGICA ESPECÍFICA DE PASAJES/NEW (solo actúa si existe #formVentaPasaje) -->
  <script src="<?= URL; ?>/public/assets/js/pasajes/pasajes-new.js"></script>

  <script src="<?= URL; ?>/public/assets/js/material-dashboard.min.js?v=3.2.0"></script>

  </body>
</html>