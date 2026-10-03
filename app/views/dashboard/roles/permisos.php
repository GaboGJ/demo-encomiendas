<?php
/**
 * views/dashboard/roles/permisos.php
 * Matriz de permisos en DataTable: módulos (filas) x tipos de operación (columnas).
 * Variables (Roles_controller::permisos): $rol, $modulos, $tipos, $matriz, $protegido
 */
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

$totalCasillas = 0;
$marcadas = 0;
foreach ($modulos as $m) {
    foreach ($tipos as $t) {
        $c = $matriz[(int)$m['id_modulo']][(int)$t['id_tipooperacion']] ?? null;
        if ($c) { $totalCasillas++; if ($c['activo'] || $protegido) $marcadas++; }
    }
}
$thBase = 'text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 border-top border-bottom border-light';
?>
<div class="container-fluid py-3 flex-grow-1">
  <div class="row">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">

        <div class="card-header bg-white p-4 d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
          <div>
            <h5 class="font-weight-bolder text-dark mb-0">Permisos · <?= $h($rol['nombre_rol']) ?></h5>
            <p class="text-xs text-secondary mb-0">Marque lo que este rol puede hacer en cada módulo. Cualquier acción requiere poder <strong>Ver</strong> el módulo.</p>
          </div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-gradient-success px-3 py-2"><span id="lblMarcadas"><?= $marcadas ?></span> / <?= $totalCasillas ?> permisos</span>
            <?php if (!$protegido): ?>
              <button type="button" class="btn btn-sm btn-outline-success mb-0" id="btnTodo">Marcar todo</button>
              <button type="button" class="btn btn-sm btn-outline-secondary mb-0" id="btnNada">Quitar todo</button>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($protegido): ?>
          <div class="px-4 pb-3">
            <div class="alert alert-info text-white text-xs mb-0 py-2 d-flex align-items-center gap-2">
              <i class="material-symbols-rounded text-sm">lock</i>
              Rol del sistema (administrador): siempre tiene acceso total y sus permisos no se modifican.
            </div>
          </div>
        <?php elseif (empty($modulos) || empty($tipos)): ?>
          <div class="px-4 pb-3">
            <div class="alert alert-danger text-white text-xs mb-0 py-2">
              No se pudo cargar el catálogo. Revise las tablas <strong>modulos</strong> y <strong>tipos_operacion</strong>.
            </div>
          </div>
        <?php endif; ?>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="tablaPermisos" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th class="<?= $thBase ?> ps-4 ps-md-5 pe-4">Módulo del Sistema</th>
                  <?php foreach ($tipos as $t): ?>
                    <th class="<?= $thBase ?> text-center px-3"><?= $h($t['nombre_tipooperacion']) ?></th>
                  <?php endforeach; ?>
                  <th class="<?= $thBase ?> text-center pe-4 pe-md-5 ps-3">Todos</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($modulos as $m): ?>
                  <tr>
                    <td class="py-3 ps-4 ps-md-5 pe-4">
                      <span class="text-xs font-weight-bold text-dark d-block"><?= $h($m['nombre_modulo']) ?></span>
                      <span class="text-xxs text-secondary"><?= $h($m['descripcion_modulo']) ?></span>
                    </td>
                    <?php foreach ($tipos as $t): ?>
                      <?php
                        $c = $matriz[(int)$m['id_modulo']][(int)$t['id_tipooperacion']] ?? null;
                        $esVer = mb_strtolower($t['nombre_tipooperacion'], 'UTF-8') === 'ver';
                      ?>
                      <td class="align-middle text-center py-3 px-3">
                        <?php if ($c): ?>
                          <div class="form-check form-switch ps-0 d-flex justify-content-center mb-0">
                            <input class="form-check-input ms-0 chk-op <?= $esVer ? 'chk-ver' : '' ?>" type="checkbox"
                                   data-op="<?= (int)$c['id_operacion'] ?>"
                                   title="<?= $h($t['nombre_tipooperacion'] . ' · ' . $m['nombre_modulo']) ?>"
                                   <?= ($c['activo'] || $protegido) ? 'checked' : '' ?>
                                   <?= $protegido ? 'disabled' : '' ?>>
                          </div>
                        <?php else: ?>
                          <span class="text-xxs text-secondary">-</span>
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>
                    <td class="align-middle text-center py-3 pe-4 pe-md-5 ps-3">
                      <div class="form-check form-switch ps-0 d-flex justify-content-center mb-0">
                        <input class="form-check-input ms-0 chk-fila" type="checkbox" title="Marcar toda la fila" <?= $protegido ? 'checked disabled' : '' ?>>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <a href="<?= rtrim(URL, '/') ?>/roles" class="btn btn-sm bg-gradient-secondary mb-0 border-radius-md px-4 font-weight-bold">
            <i class="material-symbols-rounded me-1 text-sm align-middle">close</i> Volver
          </a>
          <button type="button" class="btn btn-sm bg-gradient-success text-white mb-0 border-radius-md px-4 font-weight-bold" id="btnGuardarPermisos"
                  <?= ($protegido || empty($modulos) || empty($tipos)) ? 'disabled' : '' ?>>
            <i class="material-symbols-rounded me-1 text-sm align-middle">save</i> Guardar Permisos
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl = '<?= rtrim(URL, "/") ?>';
  const ID_ROL  = <?= (int)$rol['id_rol'] ?>;
  const tabla   = document.getElementById('tablaPermisos');

  /* La tabla la inicializa el script general (layouts/script.php) después de esta vista.
     Con paginación DataTables saca del DOM las filas de otras páginas pero conserva sus
     nodos (y el estado de sus casillas), por eso, una vez inicializada, todo se lee con
     dt.rows().nodes(). Antes de inicializarse, todas las filas están en el DOM. */
  const getDt = () => (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable(tabla)) ? $(tabla).DataTable() : null;
  const todasLasFilas = () => { const dt = getDt(); return dt ? $(dt.rows().nodes()) : $(tabla).find('tbody tr'); };

  function contador() {
    document.getElementById('lblMarcadas').textContent = todasLasFilas().find('.chk-op:checked').length;
  }

  function sincronizarFila(tr) {
    const $tr = $(tr);
    $tr.find('.chk-fila').prop('checked', $tr.find('.chk-op').length > 0 && $tr.find('.chk-op:not(:checked)').length === 0);
  }

  // Delegado sobre la tabla: sirve en cualquier página (los nodos se reinsertan al paginar)
  tabla.addEventListener('change', function (e) {
    const el = e.target, tr = el.closest('tr');
    if (!tr) return;
    const $tr = $(tr);

    if (el.classList.contains('chk-fila')) {
      $tr.find('.chk-op').prop('checked', el.checked);
    } else if (el.classList.contains('chk-op')) {
      if (el.classList.contains('chk-ver')) {
        if (!el.checked) $tr.find('.chk-op').prop('checked', false); // sin "Ver" no hay otras acciones
      } else if (el.checked) {
        $tr.find('.chk-ver').prop('checked', true);
      }
    }
    sincronizarFila(tr);
    contador();
  });

  function marcarTodo(v) {
    todasLasFilas().each(function () {
      $(this).find('.chk-op').prop('checked', v);
      sincronizarFila(this);
    });
    contador();
  }
  const btnTodo = document.getElementById('btnTodo'), btnNada = document.getElementById('btnNada');
  if (btnTodo) btnTodo.addEventListener('click', () => marcarTodo(true));
  if (btnNada) btnNada.addEventListener('click', () => marcarTodo(false));

  // Estado inicial de los interruptores "Todos" (todas las filas siguen en el DOM antes de que DataTables inicialice)
  document.addEventListener('DOMContentLoaded', function () {
    todasLasFilas().each(function () { sincronizarFila(this); });
    contador();
  });

  document.getElementById('btnGuardarPermisos').addEventListener('click', function () {
    const btn = this;
    const ids = todasLasFilas().find('.chk-op:checked').map(function () { return parseInt(this.dataset.op); }).get();

    const fd = new FormData();
    fd.append('id_rol', ID_ROL);
    fd.append('ids_json', JSON.stringify(ids));

    btn.disabled = true;
    fetch(baseUrl + '/roles/guardarPermisos', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          // El mensaje de éxito sale en el listado (Flash)
          window.location.href = baseUrl + '/roles';
        } else {
          Swal.fire('No se pudo guardar', res.message || 'Error al guardar los permisos.', 'error');
          btn.disabled = false;
        }
      })
      .catch(() => {
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
        btn.disabled = false;
      });
  });
})();
</script>