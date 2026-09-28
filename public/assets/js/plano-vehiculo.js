/* =========================================================
   plano-vehiculo.js
   Dibuja el plano de un piso de un vehículo (asientos + elementos especiales).
   Requiere: plano-vehiculo.css y utils-ui.js

   PlanoVehiculo.normalizarHorizontal(pisos, elementos)
   PlanoVehiculo.render(contenedor, piso, elementos, estaSeleccionado(id))
   PlanoVehiculo.alClickAsiento(contenedor, fn(idElemento))   // delegación de eventos
   ========================================================= */
(function (w) {
  'use strict';

  var esc = function (s) { return w.UIUtils.escaparHtml(s); };

  function icono(tipo) {
    var t = (tipo || '').toLowerCase();
    if (t.indexOf('chofer') !== -1) return 'directions_car';
    if (t.indexOf('baño') !== -1 || t.indexOf('bano') !== -1) return 'wc';
    if (t.indexOf('escalera') !== -1) return 'stairs';
    if (t.indexOf('televis') !== -1) return 'tv';
    if (t.indexOf('puerta') !== -1) return 'sensor_door';
    return 'square';
  }

  // Si un piso es más "alto" que "ancho" se transpone para verse siempre horizontal.
  function normalizarHorizontal(pisos, elementos) {
    pisos.forEach(function (piso) {
      var filas = parseInt(piso.filas_piso) || 1;
      var cols = parseInt(piso.columnas_piso) || 1;
      if (filas <= cols) return;

      piso.filas_piso = cols;
      piso.columnas_piso = filas;
      elementos.forEach(function (el) {
        if (el.id_piso !== piso.id_piso) return;
        var f = el.fila_elemento;
        el.fila_elemento = el.columna_elemento;
        el.columna_elemento = f;
      });
    });
  }

  function render(contenedor, piso, elementos, estaSeleccionado) {
    if (!piso) {
      contenedor.innerHTML = '<div class="text-center text-xs text-secondary py-4">Piso no encontrado.</div>';
      return;
    }

    var delPiso = elementos.filter(function (e) { return e.id_piso === piso.id_piso; });
    var filas = Math.max(1, parseInt(piso.filas_piso) || 1);
    var cols = Math.max(1, parseInt(piso.columnas_piso) || 1);
    delPiso.forEach(function (el) {
      filas = Math.max(filas, parseInt(el.fila_elemento) + 1);
      cols = Math.max(cols, parseInt(el.columna_elemento) + 1);
    });

    var nombrePiso = piso.nombre_piso ? esc(piso.nombre_piso) : ('Piso ' + piso.numero_piso);

    var html =
      '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 px-2 pb-2 border-bottom">' +
        '<span class="badge bg-gradient-dark">' + nombrePiso + '</span>' +
        '<span class="text-xxs text-secondary font-weight-bold">Frente (Izq.) ➔ Fondo (Der.)</span>' +
      '</div>' +
      '<div class="grid-asientos-piso" style="grid-template-columns: repeat(' + cols + ', var(--celda-plano)); grid-template-rows: repeat(' + filas + ', var(--celda-plano));">';

    for (var r = 0; r < filas; r++) {
      for (var c = 0; c < cols; c++) {
        var el = delPiso.find(function (e) { return parseInt(e.fila_elemento) === r && parseInt(e.columna_elemento) === c; });
        var pos = 'grid-column:' + (c + 1) + ';grid-row:' + (r + 1) + ';';

        if (!el) { html += '<div class="celda-elemento" style="' + pos + '"></div>'; continue; }

        if (el.es_asiento) {
          var sel = estaSeleccionado(el.id_elemento);
          var clases = 'celda-elemento celda-asiento' + (el.ocupado ? ' ocupado' : '') + (sel && !el.ocupado ? ' seleccionado' : '');
          var titulo = el.ocupado
            ? 'Asiento ' + el.dato_elemento + ' — Ocupado' + (el.pasajero_nombre ? ' por ' + el.pasajero_nombre : '')
            : 'Asiento ' + el.dato_elemento + ' — Disponible';

          html += '<div class="' + clases + '" style="' + pos + '" title="' + esc(titulo) + '"' +
                  ' data-asiento="' + el.id_elemento + '" data-ocupado="' + (el.ocupado ? 'true' : 'false') + '"' +
                  ' role="button" tabindex="' + (el.ocupado ? '-1' : '0') + '" aria-pressed="' + (sel ? 'true' : 'false') + '">' +
                  '<span class="material-symbols-rounded text-sm">event_seat</span>' +
                  '<span>' + esc(el.dato_elemento) + '</span></div>';
        } else if ((el.tipo_elemento || '').toLowerCase().indexOf('pasillo') !== -1) {
          html += '<div class="celda-elemento celda-pasillo" style="' + pos + '" title="Pasillo"></div>';
        } else {
          html += '<div class="celda-elemento celda-especial" style="' + pos + '" title="' + esc(el.tipo_elemento) + '">' +
                  '<div class="d-flex flex-column align-items-center">' +
                  '<span class="material-symbols-rounded text-sm">' + icono(el.tipo_elemento) + '</span>' +
                  '<span>' + esc(el.dato_elemento) + '</span></div></div>';
        }
      }
    }

    html += '</div>' +
      '<div class="mt-3 pt-2 border-top text-center">' +
        '<span class="text-xxs text-uppercase text-secondary font-weight-bolder">=== Parte Posterior / Salida de Emergencia ===</span>' +
      '</div>';

    contenedor.innerHTML = html;
  }

  // Un solo listener en el contenedor (sirve aunque el HTML se regenere)
  function alClickAsiento(contenedor, fn) {
    function manejar(e) {
      var celda = e.target.closest('[data-asiento]');
      if (!celda || celda.dataset.ocupado === 'true') return;
      fn(celda.dataset.asiento);
    }
    contenedor.addEventListener('click', manejar);
    contenedor.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); manejar(e); }
    });
  }

  w.PlanoVehiculo = { normalizarHorizontal: normalizarHorizontal, render: render, alClickAsiento: alClickAsiento };
})(window);