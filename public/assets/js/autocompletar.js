/* =========================================================
   autocompletar.js
   Buscador tipo autocompletar reutilizable.

   window.crearAutocompletar({
     inputId, listaId,        // ids del <input> visible y del contenedor de la lista
     hiddenId,                // (opcional) input hidden que guarda el id elegido
     data,                    // [{ id, label, buscar, ...extra }]
     max: 8,                  // máximo de resultados
     esDeshabilitado: fn(item),// (opcional) true => opción visible pero no elegible
     onInput: fn(),           // (opcional) cuando el usuario escribe (selección anulada)
     onSelect: fn(item)       // cuando se elige una opción
   })
   Devuelve { cerrar() }.
   ========================================================= */
(function (w, d) {
  'use strict';

  w.crearAutocompletar = function (opts) {
    var input = d.getElementById(opts.inputId);
    var lista = d.getElementById(opts.listaId);
    var hidden = opts.hiddenId ? d.getElementById(opts.hiddenId) : null;
    if (!input || !lista) return null;

    var esc = w.UIUtils.escaparHtml;
    var max = opts.max || 8;
    var actuales = [];
    var indice = -1;

    function deshabilitado(item) {
      return typeof opts.esDeshabilitado === 'function' && opts.esDeshabilitado(item);
    }

    function cerrar() { lista.style.display = 'none'; }

    function render(items) {
      actuales = items;
      indice = -1;

      if (!items.length) {
        lista.innerHTML = '<div class="list-group-item text-xs text-secondary">Sin resultados...</div>';
      } else {
        lista.innerHTML = items.map(function (item, i) {
          var off = deshabilitado(item);
          return '<button type="button" class="list-group-item list-group-item-action text-xs py-2"' +
                 ' data-index="' + i + '"' + (off ? ' disabled' : '') + '>' + esc(item.label) + '</button>';
        }).join('');
      }
      lista.style.display = 'block';
    }

    function marcar() {
      lista.querySelectorAll('[data-index]').forEach(function (b, i) {
        b.classList.toggle('active', i === indice);
      });
    }

    function seleccionar(item) {
      if (!item || deshabilitado(item)) return;
      if (hidden) hidden.value = item.id;
      input.value = item.label;
      input.classList.remove('is-invalid');
      cerrar();
      if (typeof opts.onSelect === 'function') opts.onSelect(item);
    }

    input.addEventListener('input', function () {
      if (hidden) hidden.value = '';
      if (typeof opts.onInput === 'function') opts.onInput();

      var q = input.value.trim().toLowerCase();
      if (!q) { cerrar(); return; }

      render(opts.data.filter(function (it) { return it.buscar.indexOf(q) !== -1; }).slice(0, max));
    });

    input.addEventListener('focus', function () {
      if (input.value.trim() && lista.innerHTML) lista.style.display = 'block';
    });

    input.addEventListener('keydown', function (e) {
      if (lista.style.display === 'none' || !actuales.length) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        indice = Math.min(indice + 1, actuales.length - 1); marcar();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        indice = Math.max(indice - 1, 0); marcar();
      } else if (e.key === 'Enter') {
        e.preventDefault();
        seleccionar(indice >= 0 ? actuales[indice] : actuales[0]);
      } else if (e.key === 'Escape') {
        cerrar();
      }
    });

    lista.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-index]');
      if (!btn || btn.disabled) return;
      seleccionar(actuales[parseInt(btn.getAttribute('data-index'), 10)]);
    });

    d.addEventListener('click', function (e) {
      if (!e.target.closest('#' + opts.inputId) && !e.target.closest('#' + opts.listaId)) cerrar();
    });

    return { cerrar: cerrar };
  };
})(window, document);