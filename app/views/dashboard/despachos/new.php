<!-- FORMULARIO DE NUEVO DESPACHO / TURNO -->
<div class="container-fluid py-3 flex-grow-1">
  <div class="row">
    <div class="col-12 col-lg-10 mx-auto">
      <div class="card border-0 shadow-sm border-radius-xl">
        
        <!-- ENCABEZADO SIN BOTÓN VOLVER -->
        <div class="card-header bg-white p-4">
          <h5 class="font-weight-bolder text-dark mb-0">Programar Nuevo Despacho / Turno</h5>
          <p class="text-xs text-secondary mb-0">Asigna la unidad, chofer, ruta y el sistema registrará el precio correspondiente</p>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body p-4">
          <form action="<?= URL ?>/despachos/store" method="POST">
            
            <!-- INPUT OCULTO PARA EL PRECIO DEL PASAJE -->
            <input type="hidden" name="precio_pasaje" id="precio_pasaje" value="">

            <!-- FILA PRINCIPAL: Destino y Vehículo/Chofer -->
            <div class="row g-3 mb-3">
              <!-- Destino de la Ruta -->
              <div class="col-md-6 position-relative">
                <label class="form-label text-xs font-weight-bold text-dark text-uppercase">Sucursal Destino *</label>
                <input type="text" id="buscadorSucursalDestino" class="form-control border px-3 py-2 border-radius-md text-sm" placeholder="Escriba la ciudad o sucursal..." autocomplete="off">
                <input type="hidden" name="id_sucursal_destino" id="id_sucursal_destino">
                <div id="listaSucursalesDestino" class="list-group position-absolute w-100 shadow-sm border-radius-md mt-1" style="z-index: 1055; max-height: 260px; overflow-y: auto; display: none;"></div>
              </div>

              <!-- Vehículo y Chofer -->
              <div class="col-md-6 position-relative">
                <label class="form-label text-xs font-weight-bold text-dark text-uppercase">Vehículo / Chofer Asignado *</label>
                <input type="text" id="buscadorVehiculoChofer" class="form-control border px-3 py-2 border-radius-md text-sm" placeholder="Escriba el chofer, placa o Nº de unidad..." autocomplete="off">
                <input type="hidden" name="id_vehiculo_chofer" id="id_vehiculo_chofer">
                <div id="listaVehiculosChoferes" class="list-group position-absolute w-100 shadow-sm border-radius-md mt-1" style="z-index: 1055; max-height: 260px; overflow-y: auto; display: none;"></div>
              </div>
            </div>

            <!-- NOTA INFORMATIVA -->
            <div class="alert alert-light border d-flex align-items-center gap-2 mb-0 py-2 px-3">
              <i class="material-symbols-rounded text-secondary text-sm">info</i>
              <div class="text-xs text-secondary">
                La <strong>fecha de salida</strong> se registra con la fecha actual (<?= date('d/m/Y') ?>). La <strong>hora de salida</strong> quedará asentada al despachar el turno.
              </div>
            </div>

            <!-- BOTONES DE ACCIÓN EN CADA EXTREMO -->
            <div class="d-flex align-items-center justify-content-between gap-2 mt-4 pt-3 border-top">
              <a href="<?= URL ?>/despachos" class="btn btn-light mb-0 border-radius-md px-4">
                Cancelar
              </a>
              <button type="submit" class="btn bg-gradient-success text-white mb-0 border-radius-md px-4 d-inline-flex align-items-center gap-2">
                <i class="material-symbols-rounded text-sm">save</i>
                <span class="font-weight-bold">Guardar Turno</span>
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const vehiculosChoferesData = <?= json_encode(array_map(function ($vc) {
      return [
          'id'     => $vc['id_vehiculo_chofer'],
          'label'  => 'Unidad ' . $vc['numero_interno_vehiculo'] . ' (Placa: ' . $vc['placa_vehiculo'] . ') - Chofer: ' .$vc['nombre_chofer'],
          'buscar' => mb_strtolower(
              $vc['numero_interno_vehiculo'] . ' ' .$vc['placa_vehiculo'] . ' ' . $vc['nombre_chofer'] . ' ' . ($vc['nombre_modelo'] ?? ''),
              'UTF-8'
          )
      ];
  }, $vehiculosChoferes ?? []), JSON_UNESCAPED_UNICODE) ?>;

  const sucursalesDestinoData = <?= json_encode(array_map(function ($s) {
      return [
          'id'          => $s['id_sucursal'],
          'label'       => $s['ciudad_sucursal'] . ' - ' .$s['nombre_sucursal'],
          'precio_base' => $s['precio_base'],
          'buscar'      => mb_strtolower($s['ciudad_sucursal'] . ' ' . $s['nombre_sucursal'], 'UTF-8')       ];   },$sucursalesDestino ?? []), JSON_UNESCAPED_UNICODE) ?>;

  function escaparHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  function crearBuscador({ inputId, hiddenId, listaId, data, mensajeValidacion, onSelect }) {
    const inputBuscador = document.getElementById(inputId);
    const hiddenInput   = document.getElementById(hiddenId);
    const listaContainer= document.getElementById(listaId);
    let indiceActivo = -1;
    let resultadosActuales = [];

    function renderLista(items) {
      resultadosActuales = items;
      indiceActivo = -1;

      if (!items.length) {
        listaContainer.innerHTML = '<div class="list-group-item text-xs text-secondary">Sin resultados...</div>';
        listaContainer.style.display = 'block';
        return;
      }

      listaContainer.innerHTML = items.map(function (item, i) {
        return `<button type="button" class="list-group-item list-group-item-action text-xs py-2" data-index="${i}">${escaparHtml(item.label)}</button>`;
      }).join('');
      listaContainer.style.display = 'block';
    }

    function seleccionar(item) {
      hiddenInput.value = item.id;
      inputBuscador.value = item.label;
      inputBuscador.classList.remove('is-invalid');
      listaContainer.style.display = 'none';

      if (typeof onSelect === 'function') {
        onSelect(item);
      }
    }

    function marcarActivo() {
      const botones = listaContainer.querySelectorAll('[data-index]');
      botones.forEach(function (b, i) {
        b.classList.toggle('active', i === indiceActivo);
      });
    }

    inputBuscador.addEventListener('input', function () {
      hiddenInput.value = '';
      const q = this.value.trim().toLowerCase();

      if (!q) {
        listaContainer.style.display = 'none';
        return;
      }

      const filtrados = data.filter(function (v) {
        return v.buscar.includes(q);
      }).slice(0, 8);

      renderLista(filtrados);
    });

    inputBuscador.addEventListener('focus', function () {
      if (this.value.trim() && listaContainer.innerHTML) {
        listaContainer.style.display = 'block';
      }
    });

    inputBuscador.addEventListener('keydown', function (e) {
      if (listaContainer.style.display === 'none' || !resultadosActuales.length) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        indiceActivo = Math.min(indiceActivo + 1, resultadosActuales.length - 1);
        marcarActivo();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        indiceActivo = Math.max(indiceActivo - 1, 0);
        marcarActivo();
      } else if (e.key === 'Enter') {
        e.preventDefault();
        const elegido = indiceActivo >= 0 ? resultadosActuales[indiceActivo] : resultadosActuales[0];
        if (elegido) seleccionar(elegido);
      } else if (e.key === 'Escape') {
        listaContainer.style.display = 'none';
      }
    });

    listaContainer.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-index]');
      if (!btn) return;
      const item = resultadosActuales[parseInt(btn.getAttribute('data-index'), 10)];
      if (item) seleccionar(item);
    });

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#' + inputId) && !e.target.closest('#' + listaId)) {
        listaContainer.style.display = 'none';
      }
    });

    return {
      validar: function () {
        if (!hiddenInput.value) {
          inputBuscador.classList.add('is-invalid');
          return { valido: false, input: inputBuscador, mensaje: mensajeValidacion };
        }
        return { valido: true };
      }
    };
  }

  // Al seleccionar la sucursal asignamos su precio al input oculto
  const buscadorDestino = crearBuscador({
    inputId: 'buscadorSucursalDestino',
    hiddenId: 'id_sucursal_destino',
    listaId: 'listaSucursalesDestino',
    data: sucursalesDestinoData,
    mensajeValidacion: 'Debe seleccionar la sucursal destino.',
    onSelect: function (item) {
      const inputOcultoPrecio = document.getElementById('precio_pasaje');
      if (inputOcultoPrecio) {
        inputOcultoPrecio.value = parseFloat(item.precio_base).toFixed(2);
      }
    }
  });

  const buscadorVehiculo = crearBuscador({
    inputId: 'buscadorVehiculoChofer',
    hiddenId: 'id_vehiculo_chofer',
    listaId: 'listaVehiculosChoferes',
    data: vehiculosChoferesData,
    mensajeValidacion: 'Debe seleccionar el vehículo/chofer asignado.'
  });

  const formulario = document.getElementById('buscadorVehiculoChofer').closest('form');
  formulario.addEventListener('submit', function (e) {
    const resultados = [buscadorDestino.validar(), buscadorVehiculo.validar()];
    const errores = resultados.filter(function (r) { return !r.valido; });

    if (errores.length) {
      e.preventDefault();
      errores[0].input.focus();
      const mensaje = errores.map(function (r) { return r.mensaje; }).join('<br>');
      if (typeof Swal !== 'undefined') {
        Swal.fire('Datos Incompletos', mensaje, 'warning');
      } else {
        alert(errores.map(function (r) { return r.mensaje; }).join('\n'));
      }
    }
  });
})();
</script>