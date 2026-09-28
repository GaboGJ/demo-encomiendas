/* =========================================================
   pasajes-new.js
   Lógica del wizard "Venta de Pasaje" (views/dashboard/pasajes/new.php).
   Se carga en el script general; solo actúa si existe #formVentaPasaje.
   Datos desde PHP: window.PASAJES_NEW = { baseUrl, turnos }
   Requiere: utils-ui.js, autocompletar.js, plano-vehiculo.js
   ========================================================= */
(function (w, d) {
  'use strict';

  if (!d.getElementById('formVentaPasaje')) return;

  var CFG = w.PASAJES_NEW || {};
  var baseUrl = CFG.baseUrl || '';
  var turnosData = CFG.turnos || [];
  var U = w.UIUtils;
  var esc = U.escaparHtml;
  var valorInput = U.valorInput;
  var setValorInput = U.setValorInput;

  var TOTAL_PASOS = 3;
  var NOMBRES_PASOS = ['Turno y Comprador', 'Asientos y Pasajeros', 'Pago y Emisión'];

  var pasoActual = 1;
  var turnoSeleccionado = null;
  var pisosPlano = [];
  var elementosPlano = [];
  var pisoActivoPlano = null;
  var dtPasajeros = null;

  // key (id_elemento) -> datos del pasajero de ese asiento
  var asientosSeleccionados = new Map();

  /* ---------- Utilidades ---------- */
  function datosComprador() {
    return {
      ci: valorInput('comprador_ci'),
      nombres: valorInput('comprador_nombres'),
      paterno: valorInput('comprador_paterno'),
      materno: valorInput('comprador_materno'),
      celular: valorInput('comprador_celular')
    };
  }

  function nombreCompleto(p) {
    return [p.nombres, p.paterno, p.materno].filter(Boolean).join(' ');
  }

  function buscarPersonaPorCi(ci, cb) {
    if (!ci) return;
    fetch(baseUrl + '/personas/buscarPorCiJson?ci=' + encodeURIComponent(ci))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success && data.persona) {
          var p = data.persona;
          cb({
            nombres: p.nombres_persona || p.nombre_persona || '',
            paterno: p.paterno_persona || p.apellido_paterno_persona || '',
            materno: p.materno_persona || p.apellido_materno_persona || '',
            celular: p.celular_persona || p.telefono_persona || ''
          });
        }
      })
      .catch(function () {});
  }

  function buscarComprador() {
    buscarPersonaPorCi(valorInput('comprador_ci'), function (p) {
      setValorInput('comprador_nombres', p.nombres);
      setValorInput('comprador_paterno', p.paterno);
      setValorInput('comprador_materno', p.materno);
      setValorInput('comprador_celular', p.celular);
      renderizarListaPasajeros();
    });
  }

  function buscarPasajeroModal() {
    buscarPersonaPorCi(valorInput('pas_ci'), function (p) {
      setValorInput('pas_nombres', p.nombres);
      setValorInput('pas_paterno', p.paterno);
      setValorInput('pas_materno', p.materno);
      setValorInput('pas_celular', p.celular);
    });
  }

  /* ---------- Paso 1: buscador de turno ---------- */
  function seleccionarTurno(item) {
    // Cambiar de turno invalida la selección de asientos previa
    asientosSeleccionados.clear();
    pisosPlano = [];
    elementosPlano = [];
    pisoActivoPlano = null;
    renderizarListaPasajeros();

    turnoSeleccionado = {
      id: item.id,
      precio: parseFloat(item.precio) || 0,
      destino: item.destino,
      vehiculo: item.vehiculo,
      chofer: item.chofer,
      capacidad: item.capacidad,
      ocupados: item.ocupados
    };

    d.getElementById('lblInfoDestino').textContent = turnoSeleccionado.destino;
    d.getElementById('lblInfoVehiculo').textContent = turnoSeleccionado.vehiculo;
    d.getElementById('lblInfoChofer').textContent = turnoSeleccionado.chofer;
    d.getElementById('lblInfoPrecio').textContent = 'Bs. ' + turnoSeleccionado.precio.toFixed(2);
    d.getElementById('infoTurnoSeleccionado').classList.remove('d-none');
  }

  function iniciarBuscadorTurno() {
    w.crearAutocompletar({
      inputId: 'buscadorTurno',
      listaId: 'listaTurnos',
      hiddenId: 'selectTurno',
      data: turnosData,
      esDeshabilitado: function (t) { return t.lleno; },
      onInput: function () {
        turnoSeleccionado = null;
        d.getElementById('infoTurnoSeleccionado').classList.add('d-none');
      },
      onSelect: seleccionarTurno
    });
  }

  /* ---------- Paso 2: plano de asientos ---------- */
  function cargarAsientosTurno() {
    var contenedor = d.getElementById('contenedorPlanoAsientos');
    contenedor.innerHTML = '<div class="text-center text-xs text-secondary py-4">Cargando plano del vehículo...</div>';

    fetch(baseUrl + '/pasajes/obtenerConfiguracionTurno?id_turno=' + turnoSeleccionado.id)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.success) {
          contenedor.innerHTML = '<div class="text-center text-xs text-danger py-4">' + esc(res.message) + '</div>';
          return;
        }

        turnoSeleccionado.precio = parseFloat(res.turno.precio_pasaje_turno) || turnoSeleccionado.precio;
        d.getElementById('lblInfoPrecio').textContent = 'Bs. ' + turnoSeleccionado.precio.toFixed(2);

        pisosPlano = res.pisos || [];
        elementosPlano = res.elementos || [];
        w.PlanoVehiculo.normalizarHorizontal(pisosPlano, elementosPlano);

        // Descartar asientos que ya no existen o que otro cajero vendió
        var validos = new Set(
          elementosPlano.filter(function (e) { return e.es_asiento && !e.ocupado; })
                        .map(function (e) { return String(e.id_elemento); })
        );
        Array.from(asientosSeleccionados.keys()).forEach(function (k) {
          if (!validos.has(k)) asientosSeleccionados.delete(k);
        });

        if (!pisosPlano.length) {
          contenedor.innerHTML = '<div class="text-center text-xs text-secondary py-4">Este modelo de vehículo no tiene un plano de asientos configurado.</div>';
          recalcularTotalAsientos();
          renderizarListaPasajeros();
          return;
        }

        pisoActivoPlano = pisosPlano[0].id_piso;
        actualizarPaginadorPisos();
        renderizarPlanoPiso();
        recalcularTotalAsientos();
        renderizarListaPasajeros();
      })
      .catch(function () {
        contenedor.innerHTML = '<div class="text-center text-xs text-danger py-4">Error al cargar el plano del vehículo.</div>';
      });
  }

  function renderizarPlanoPiso() {
    var piso = pisosPlano.find(function (p) { return p.id_piso === pisoActivoPlano; });
    w.PlanoVehiculo.render(
      d.getElementById('contenedorPlanoAsientos'),
      piso,
      elementosPlano,
      function (id) { return asientosSeleccionados.has(String(id)); }
    );
  }

  function actualizarPaginadorPisos() {
    var wrapper = d.getElementById('wrapperPisosAsientos');

    if (pisosPlano.length <= 1) {
      wrapper.classList.remove('d-flex');
      wrapper.classList.add('d-none');
      return;
    }

    var i = pisosPlano.findIndex(function (p) { return p.id_piso === pisoActivoPlano; });
    var piso = pisosPlano[i];
    var etiqueta = piso.nombre_piso ? piso.nombre_piso : ('Piso ' + piso.numero_piso);

    wrapper.classList.remove('d-none');
    wrapper.classList.add('d-flex');
    d.getElementById('lblPisoActual').textContent = etiqueta + ' (' + (i + 1) + ' de ' + pisosPlano.length + ')';
    d.getElementById('btnPisoAnterior').disabled = (i <= 0);
    d.getElementById('btnPisoSiguiente').disabled = (i >= pisosPlano.length - 1);
  }

  function cambiarPisoPaginador(delta) {
    var i = pisosPlano.findIndex(function (p) { return p.id_piso === pisoActivoPlano; }) + delta;
    if (i < 0 || i >= pisosPlano.length) return;
    pisoActivoPlano = pisosPlano[i].id_piso;
    actualizarPaginadorPisos();
    renderizarPlanoPiso();
  }

  function toggleAsiento(idElemento) {
    var key = String(idElemento);
    if (asientosSeleccionados.has(key)) {
      asientosSeleccionados.delete(key);
    } else {
      var el = elementosPlano.find(function (e) { return String(e.id_elemento) === key; });
      asientosSeleccionados.set(key, {
        id_elemento: key,
        etiqueta: el ? el.dato_elemento : key,
        usar_comprador: true,
        ci: '', nombres: '', paterno: '', materno: '', celular: ''
      });
    }
    renderizarPlanoPiso();
    recalcularTotalAsientos();
    renderizarListaPasajeros();
  }

  function recalcularTotalAsientos() {
    var n = asientosSeleccionados.size;
    var precio = turnoSeleccionado ? turnoSeleccionado.precio : 0;
    d.getElementById('lblCantAsientos').textContent = n + ' asiento(s)';
    d.getElementById('lblTotalPagarAsientos').textContent = 'Bs. ' + (n * precio).toFixed(2);
  }

  /* ---------- Tabla de pasajero por asiento ---------- */
  function inicializarDataTablePasajeros() {
    if (dtPasajeros || typeof w.inicializarDataTable !== 'function') return;
    dtPasajeros = w.inicializarDataTable('#tablaPasajerosAsientos', {
      ordering: false,
      placeholder: 'Buscar asiento...',
      pageLength: 5,
      columns: [
        { data: 'asiento', className: 'text-xs font-weight-bold' },
        { data: 'pasajero', className: 'text-xs font-weight-bold' },
        { data: 'tipo', className: 'text-xs' },
        { data: 'acciones', className: 'text-end', orderable: false }
      ]
    });
  }

  function renderizarListaPasajeros() {
    inicializarDataTablePasajeros();
    if (!dtPasajeros) return;

    dtPasajeros.clear();
    var comp = datosComprador();
    var filas = [];

    asientosSeleccionados.forEach(function (a, key) {
      var p = a.usar_comprador ? comp : a;
      var nombre = nombreCompleto(p) || (a.usar_comprador ? '(complete los datos del comprador en el paso 1)' : '-');
      var ciTxt = p.ci ? ' <span class="text-xxs text-secondary d-block d-sm-inline">C.I. ' + esc(p.ci) + '</span>' : '';
      var tipo = a.usar_comprador
        ? '<span class="badge badge-sm bg-gradient-secondary">Comprador</span>'
        : '<span class="badge badge-sm bg-gradient-info">Otra persona</span>';

      filas.push({
        asiento: '<span class="badge bg-gradient-success">Asiento ' + esc(a.etiqueta) + '</span>',
        pasajero: '<span class="texto-quiebra">' + esc(nombre) + '</span>' + ciTxt,
        tipo: tipo,
        acciones:
          '<div class="d-flex align-items-center justify-content-end gap-1">' +
            (a.usar_comprador ? '' :
              '<button type="button" class="btn btn-link text-secondary p-1 m-0" title="Volver al comprador" data-accion="restablecer" data-key="' + key + '"><i class="material-symbols-rounded text-sm">undo</i></button>') +
            '<button type="button" class="btn btn-link text-success p-1 m-0" title="Cambiar pasajero" data-accion="editar" data-key="' + key + '"><i class="material-symbols-rounded text-sm">edit</i></button>' +
          '</div>'
      });
    });

    dtPasajeros.rows.add(filas).draw();
  }

  function editarPasajeroAsiento(key) {
    var a = asientosSeleccionados.get(key);
    if (!a) return;

    var base = a.usar_comprador ? { ci: '', nombres: '', paterno: '', materno: '', celular: '' } : a;
    d.getElementById('pas_id_elemento').value = key;
    d.getElementById('lblAsientoModal').textContent = a.etiqueta;
    setValorInput('pas_ci', base.ci);
    setValorInput('pas_nombres', base.nombres);
    setValorInput('pas_paterno', base.paterno);
    setValorInput('pas_materno', base.materno);
    setValorInput('pas_celular', base.celular);

    var modalEl = d.getElementById('modalPasajeroAsiento');
    (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
  }

  function guardarPasajeroAsiento() {
    var key = d.getElementById('pas_id_elemento').value;
    var a = asientosSeleccionados.get(key);
    if (!a) return;

    var ci = valorInput('pas_ci');
    var nombres = valorInput('pas_nombres');
    var paterno = valorInput('pas_paterno');

    if (!ci || !nombres || !paterno) {
      Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Complete el C.I., nombres y apellido paterno del pasajero.' });
      return;
    }

    if (ci === datosComprador().ci) {
      a.usar_comprador = true;
    } else {
      a.usar_comprador = false;
      a.ci = ci;
      a.nombres = nombres;
      a.paterno = paterno;
      a.materno = valorInput('pas_materno');
      a.celular = valorInput('pas_celular');
    }

    var modalObj = bootstrap.Modal.getInstance(d.getElementById('modalPasajeroAsiento'));
    if (modalObj) modalObj.hide();
    renderizarListaPasajeros();
  }

  function restablecerPasajeroAsiento(key) {
    var a = asientosSeleccionados.get(key);
    if (!a) return;
    a.usar_comprador = true;
    renderizarListaPasajeros();
  }

  function payloadAsientos() {
    return Array.from(asientosSeleccionados.values()).map(function (a) {
      var base = { id_elemento: parseInt(a.id_elemento), usar_comprador: a.usar_comprador };
      if (a.usar_comprador) return base;
      return Object.assign(base, { ci: a.ci, nombres: a.nombres, paterno: a.paterno, materno: a.materno, celular: a.celular });
    });
  }

  function calcularTotalVenta() {
    return turnoSeleccionado ? asientosSeleccionados.size * turnoSeleccionado.precio : 0;
  }

  /* ---------- Navegación del wizard ---------- */
  function validarPaso(paso) {
    if (paso === 1) {
      if (!turnoSeleccionado) {
        Swal.fire({ icon: 'warning', title: 'Seleccione un turno', text: 'Debe elegir el turno (chofer/vehículo) para el que vende el pasaje.' });
        return false;
      }
      if (!valorInput('comprador_ci') || !valorInput('comprador_nombres') || !valorInput('comprador_paterno')) {
        Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Complete el C.I., nombres y apellido paterno del comprador.' });
        return false;
      }
    }
    if (paso === 2 && asientosSeleccionados.size === 0) {
      Swal.fire({ icon: 'warning', title: 'Sin Asientos', text: 'Debe seleccionar al menos un asiento disponible.' });
      return false;
    }
    return true;
  }

  function cambiarPaso(direccion) {
    var nuevo = pasoActual + direccion;
    if (nuevo < 1 || nuevo > TOTAL_PASOS) return;
    if (direccion === 1 && !validarPaso(pasoActual)) return;
    irAlPasoDirecto(nuevo);
  }

  // Salto desde el stepper: hacia adelante valida cada paso intermedio
  function irAlPaso(destino) {
    if (destino <= pasoActual) { irAlPasoDirecto(destino); return; }
    while (pasoActual < destino) {
      if (!validarPaso(pasoActual)) return;
      irAlPasoDirecto(pasoActual + 1);
    }
  }

  function irAlPasoDirecto(paso) {
    d.getElementById('step-' + pasoActual).classList.add('d-none');
    pasoActual = paso;
    d.getElementById('step-' + pasoActual).classList.remove('d-none');

    d.querySelectorAll('.step-indicator button').forEach(function (b) {
      b.classList.remove('bg-gradient-success', 'text-white');
      b.classList.add('bg-gray-200', 'text-secondary');
    });
    for (var i = 1; i <= pasoActual; i++) {
      var btn = d.querySelector('#indicator-step-' + i + ' button');
      if (btn) {
        btn.classList.remove('bg-gray-200', 'text-secondary');
        btn.classList.add('bg-gradient-success', 'text-white');
      }
    }

    d.getElementById('lblPasoMovil').textContent = 'Paso ' + pasoActual + ' de ' + TOTAL_PASOS + ' · ' + NOMBRES_PASOS[pasoActual - 1];

    d.getElementById('btnCancel').classList.toggle('d-none', pasoActual !== 1);
    d.getElementById('btnPrev').classList.toggle('d-none', pasoActual === 1);
    d.getElementById('btnNext').classList.toggle('d-none', pasoActual === TOTAL_PASOS);
    d.getElementById('btnSave').classList.toggle('d-none', pasoActual !== TOTAL_PASOS);

    if (pasoActual === 2 && turnoSeleccionado) cargarAsientosTurno();
    if (pasoActual === 3) {
      w.initNavPillsAnimados();
      previsualizarBoleto();
    }

    // Al cambiar de paso en móvil, volver al inicio del formulario
    var card = d.getElementById('formVentaPasaje');
    if (card && w.innerWidth < 768) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function previsualizarBoleto() {
    var comp = datosComprador();
    d.getElementById('resComprador').textContent = nombreCompleto(comp);
    d.getElementById('resCi').textContent = comp.ci || '-';
    d.getElementById('resCelular').textContent = comp.celular || '-';

    var total = calcularTotalVenta();
    d.getElementById('lblTotalFinal').textContent = 'Bs. ' + total.toFixed(2);
    d.getElementById('resTotal').textContent = 'Bs. ' + total.toFixed(2);

    if (turnoSeleccionado) {
      d.getElementById('resRuta').textContent =
        'Trinidad ➔ ' + turnoSeleccionado.destino + ' — ' + turnoSeleccionado.vehiculo + ' — ' + turnoSeleccionado.chofer;
    }

    var lista = Array.from(asientosSeleccionados.values()).map(function (a) {
      var p = a.usar_comprador ? comp : a;
      return '<div class="texto-quiebra">Asiento ' + esc(a.etiqueta) + ' — ' + esc(nombreCompleto(p)) +
             ' <span class="text-secondary font-weight-normal">(C.I. ' + esc(p.ci) + ')</span></div>';
    }).join('');
    d.getElementById('resAsientos').innerHTML = lista || '-';
  }

  function actualizarMetodoPago(idMetodo) {
    d.getElementById('selectMetodoPago').value = idMetodo;
  }

  /* ---------- Guardado ---------- */
  function emitirVenta() {
    var btnSave = d.getElementById('btnSave');

    if (!turnoSeleccionado || asientosSeleccionados.size === 0) {
      Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Seleccione un turno y al menos un asiento.' });
      return;
    }

    btnSave.disabled = true;

    var fd = new FormData();
    fd.append('comprador_ci', valorInput('comprador_ci'));
    fd.append('comprador_nombres', valorInput('comprador_nombres'));
    fd.append('comprador_paterno', valorInput('comprador_paterno'));
    fd.append('comprador_materno', valorInput('comprador_materno'));
    fd.append('comprador_celular', valorInput('comprador_celular'));
    fd.append('comprador_direccion', valorInput('comprador_direccion'));
    fd.append('metodo_cobro', d.getElementById('selectMetodoPago').value);
    fd.append('id_turno', turnoSeleccionado.id);
    fd.append('asientos_json', JSON.stringify(payloadAsientos()));

    fetch(baseUrl + '/pasajes/guardar', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success && res.id_pasaje) {
          // El mensaje de éxito queda encolado en el servidor (Flash::set)
          w.imprimirBoletoPasaje(res.id_pasaje, function () { w.location.href = baseUrl + '/pasajes'; });
        } else {
          Swal.fire('Error al guardar', res.message || 'Error al guardar la venta de pasaje', 'error');
          btnSave.disabled = false;
          if (/vendido/i.test(res.message || '')) cargarAsientosTurno();
        }
      })
      .catch(function (err) {
        console.error(err);
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
        btnSave.disabled = false;
      });
  }

  /* ---------- Inicio ---------- */
  function iniciar() {
    iniciarBuscadorTurno();

    // Clic en asientos del plano (delegado)
    w.PlanoVehiculo.alClickAsiento(d.getElementById('contenedorPlanoAsientos'), toggleAsiento);

    // Botones de la tabla de pasajeros (delegado)
    d.getElementById('tablaPasajerosAsientos').addEventListener('click', function (e) {
      var b = e.target.closest('[data-accion]');
      if (!b) return;
      if (b.dataset.accion === 'editar') editarPasajeroAsiento(b.dataset.key);
      if (b.dataset.accion === 'restablecer') restablecerPasajeroAsiento(b.dataset.key);
    });

    d.getElementById('btnSave').addEventListener('click', emitirVenta);
    w.initNavPillsAnimados();
  }

  // Funciones usadas por atributos onclick/onblur de la vista
  w.irAlPaso = irAlPaso;
  w.cambiarPaso = cambiarPaso;
  w.cambiarPisoPaginador = cambiarPisoPaginador;
  w.buscarComprador = buscarComprador;
  w.buscarPasajeroModal = buscarPasajeroModal;
  w.guardarPasajeroAsiento = guardarPasajeroAsiento;
  w.actualizarMetodoPago = actualizarMetodoPago;

  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})(window, document);