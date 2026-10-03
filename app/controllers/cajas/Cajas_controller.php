<?php
require_once __DIR__ . '/../../models/cajas/Cajas_model.php';
require_once __DIR__ . '/../../helpers/cajas/ValidarCajas.php';

/**
 * Módulo Cajas.
 * Rutas (router: /carpeta/accion):
 *   /cajas                  index          listado + métricas + apertura/cierre
 *   /cajas/new              new            formulario nuevo
 *   /cajas/update?id=X      update         formulario de edición
 *   /cajas/papelera         papelera       cajas eliminadas (borrado suave)
 *   /cajas/imprimirCierre?id=H             reporte imprimible de un turno
 *   AJAX/JSON: detalle · detalleTurno (GET) · guardar · actualizar · cambiarEstado ·
 *              eliminar · restaurar · abrir · cerrar (POST)
 *
 * Todo se filtra por el sindicato de la sesión (cajas -> sucursales.id_sindicato).
 */
class Cajas_controller {
    private $cajasModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->cajasModel = new Cajas_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->acceso(false);
        $this->vista('cajas/index', [
            'cajas'            => $this->cajasModel->getCajas($this->idSindicato()),
            'totalPapelera'    => $this->cajasModel->contarEliminadas($this->idSindicato()),
            'idSucursalActual' => (int)($_SESSION['id_sucursal'] ?? 0),
            'idUsuarioActual'  => $this->idUsuario(),
            'esPrincipal'      => (int)($_SESSION['es_principal'] ?? 0) === 1,
        ]);
    }

    public function new() {
        $this->acceso(false);
        $this->vista('cajas/new', ['sucursales' => $this->cajasModel->getSucursales($this->idSindicato())]);
    }

    public function update() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $caja = $id > 0 ? $this->cajasModel->obtenerCaja($id, $this->idSindicato()) : null;
        if (!$caja) {
            Flash::set(false, 'La caja no existe o fue eliminada.', 'Caja no encontrada');
            header('Location: ' . rtrim(URL, '/') . '/cajas');
            exit;
        }

        $this->vista('cajas/update', ['caja' => $caja, 'sucursales' => []]);
    }

    public function papelera() {
        $this->acceso(false);
        $this->vista('cajas/papelera', ['eliminados' => $this->cajasModel->getEliminadas($this->idSindicato())]);
    }

    /** Reporte imprimible de un turno (se abre en el iframe de lanzarImpresionIframe). */
    public function imprimirCierre() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $turno = $id > 0 ? $this->cajasModel->obtenerHistorial($id, $this->idSindicato()) : null;
        if (!$turno) {
            die('Error: El turno de caja no existe.');
        }
        $resumen = $this->cajasModel->getResumen($id, $turno['monto_inicial']);

        require_once __DIR__ . '/../../views/dashboard/cajas/print_cierre.php';
        exit;
    }

    /* ============================ AJAX / JSON ============================ */

    /** Detalle para el modal: datos de la caja + últimos turnos. */
    public function detalle() {
        $this->acceso(true);

        $id = intval($_GET['id'] ?? 0);
        $caja = $id > 0 ? $this->cajasModel->obtenerCaja($id, $this->idSindicato()) : null;
        if (!$caja) {
            $this->json(['success' => false, 'message' => 'La caja no existe o fue eliminada.']);
        }

        $caja['historial'] = $this->cajasModel->getHistorial($id, 10);
        $this->json(['success' => true, 'data' => $caja]);
    }

    /** Resumen en vivo de un turno (modal de arqueo). GET id = id_historial_caja. */
    public function detalleTurno() {
        $this->acceso(true);

        $id = intval($_GET['id'] ?? 0);
        $turno = $id > 0 ? $this->cajasModel->obtenerHistorial($id, $this->idSindicato()) : null;
        if (!$turno) {
            $this->json(['success' => false, 'message' => 'El turno de caja no existe.']);
        }

        $this->json([
            'success' => true,
            'caja'    => $turno['nombre_caja'] . ' · ' . $turno['nombre_sucursal'],
            'resumen' => $this->cajasModel->getResumen($id, $turno['monto_inicial']),
        ]);
    }

    public function guardar() {
        $this->acceso(true);
        $this->soloPost();

        $d = ValidarCajas::normalizar($_POST);
        $error = ValidarCajas::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            if (!$this->cajasModel->sucursalPerteneceASindicato($d['id_sucursal'], $this->idSindicato())) {
                $this->json(['success' => false, 'message' => 'La sucursal no pertenece a su sindicato o está inactiva.']);
            }
            $dup = $this->cajasModel->nombreDuplicado($d['id_sucursal'], $d['nombre']);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $id = $this->cajasModel->crear($d);

            Flash::set(true, 'La caja "' . $d['nombre'] . '" fue registrada correctamente.', 'Caja Registrada');
            $this->json(['success' => true, 'id' => $id]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'guardar');
        }
    }

    public function actualizar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_caja'] ?? 0);
        $caja = $id > 0 ? $this->cajasModel->obtenerCaja($id, $this->idSindicato()) : null;
        if (!$caja) {
            $this->json(['success' => false, 'message' => 'La caja no existe o fue eliminada.']);
        }

        $d = ValidarCajas::normalizar($_POST);
        $d['id_sucursal'] = (int)$caja['id_sucursal']; // la sucursal no se modifica
        $error = ValidarCajas::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            $dup = $this->cajasModel->nombreDuplicado($d['id_sucursal'], $d['nombre'], $id);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $this->cajasModel->actualizar($id, $d['nombre']);

            Flash::set(true, 'La caja "' . $d['nombre'] . '" fue actualizada correctamente.', 'Caja Actualizada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'actualizar');
        }
    }

    /** Activar / desactivar (reversible). No se puede desactivar con un turno abierto. */
    public function cambiarEstado() {
        $this->acceso(true);
        $this->soloPost();

        $id     = intval($_POST['id_caja'] ?? 0);
        $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;

        $caja = $id > 0 ? $this->cajasModel->obtenerCaja($id, $this->idSindicato()) : null;
        if (!$caja) {
            $this->json(['success' => false, 'message' => 'La caja no existe o fue eliminada.']);
        }

        try {
            if ($estado === 0 && $this->cajasModel->tieneTurnoAbierto($id)) {
                $this->json(['success' => false, 'message' => 'No se puede desactivar: la caja tiene un turno abierto. Ciérrelo primero.']);
            }
            if (!$this->cajasModel->cambiarEstado($id, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (no hubo cambios).']);
            }

            Flash::set(true, $estado ? 'La caja fue activada.' : 'La caja fue desactivada.', 'Estado Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'cambiarEstado');
        }
    }

    /** Borrado suave. */
    public function eliminar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_caja'] ?? 0);
        $caja = $id > 0 ? $this->cajasModel->obtenerCaja($id, $this->idSindicato()) : null;
        if (!$caja) {
            $this->json(['success' => false, 'message' => 'No se pudo eliminar: la caja no existe o ya fue eliminada.']);
        }

        try {
            if ($this->cajasModel->tieneTurnoAbierto($id)) {
                $this->json(['success' => false, 'message' => 'No se puede eliminar: la caja tiene un turno abierto. Ciérrelo primero.']);
            }
            if (!$this->cajasModel->eliminar($id, date('Y-m-d H:i:s'))) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar la caja.']);
            }

            Flash::set(true, 'La caja "' . $caja['nombre_caja'] . '" fue enviada a la papelera. Su historial de turnos se conserva.', 'Caja Eliminada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'eliminar');
        }
    }

    public function restaurar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_caja'] ?? 0);
        $caja = $id > 0 ? $this->cajasModel->obtenerCaja($id, $this->idSindicato(), true) : null;
        if (!$caja || $caja['delete_caja'] === null) {
            $this->json(['success' => false, 'message' => 'La caja no está en la papelera.']);
        }

        try {
            if (!$this->cajasModel->restaurar($id)) {
                $this->json(['success' => false, 'message' => 'No se pudo restaurar la caja.']);
            }

            Flash::set(true, 'La caja "' . $caja['nombre_caja'] . '" fue restaurada y quedó activa.', 'Caja Restaurada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'restaurar');
        }
    }

    /** POST id_caja + monto_inicial -> abre un turno de caja para el usuario de la sesión. */
    public function abrir() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_caja'] ?? 0);
        $caja = $id > 0 ? $this->cajasModel->obtenerCaja($id, $this->idSindicato()) : null;
        if (!$caja) {
            $this->json(['success' => false, 'message' => 'La caja no existe o fue eliminada.']);
        }
        if ((int)$caja['id_sucursal'] !== (int)($_SESSION['id_sucursal'] ?? 0)) {
            $this->json(['success' => false, 'message' => 'Solo puede aperturar cajas de su propia sucursal.']);
        }

        $monto = ValidarCajas::monto($_POST['monto_inicial'] ?? '');
        if ($monto === null) {
            $this->json(['success' => false, 'message' => 'Indique un monto inicial válido (0 o mayor).']);
        }

        try {
            $this->cajasModel->abrir($id, $this->idUsuario(), $monto);

            Flash::set(true, 'La caja "' . $caja['nombre_caja'] . '" fue aperturada con Bs. ' . number_format($monto, 2) . '.', 'Caja Aperturada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'abrir');
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /** POST id_historial + monto_declarado -> arqueo y cierre del turno. */
    public function cerrar() {
        $this->acceso(true);
        $this->soloPost();

        $idH = intval($_POST['id_historial'] ?? 0);
        $turno = $idH > 0 ? $this->cajasModel->obtenerHistorial($idH, $this->idSindicato()) : null;
        if (!$turno) {
            $this->json(['success' => false, 'message' => 'El turno de caja no existe.']);
        }
        if ((int)$turno['id_usuario'] !== $this->idUsuario() && (int)($_SESSION['es_principal'] ?? 0) !== 1) {
            $this->json(['success' => false, 'message' => 'Solo el cajero que abrió la caja (o el administrador) puede cerrarla.']);
        }

        $declarado = ValidarCajas::monto($_POST['monto_declarado'] ?? '');
        if ($declarado === null) {
            $this->json(['success' => false, 'message' => 'Indique el monto contado válido (0 o mayor).']);
        }

        try {
            $r = $this->cajasModel->cerrar($idH, $declarado);

            $msg = 'Sistema: Bs. ' . number_format($r['sistema'], 2) . ' · Contado: Bs. ' . number_format($declarado, 2)
                 . ($r['diferencia'] == 0 ? ' · Caja cuadrada.' : ' · Diferencia: Bs. ' . number_format($r['diferencia'], 2));
            Flash::set(true, $msg, 'Caja Cerrada');
            $this->json(['success' => true, 'id_historial' => $idH]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'cerrar');
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ============================ PRIVADOS ============================ */

    private function idSindicato() {
        return (int)($_SESSION['id_sindicato'] ?? 0);
    }

    private function idUsuario() {
        return (int)($_SESSION['id_usuario'] ?? 0);
    }

    /** Sesión iniciada. $json = true responde JSON (AJAX); false redirige (vistas). */
    private function acceso($json) {
        if (empty($_SESSION['id_usuario'])) {
            if ($json) {
                $this->json(['success' => false, 'message' => 'Su sesión expiró. Vuelva a iniciar sesión.']);
            }
            header('Location: ' . rtrim(URL, '/') . '/auth/auth_controller/index');
            exit;
        }
    }

    private function soloPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }
    }

    private function mensajeDuplicado(array $dup) {
        return 'Ya existe una caja con ese nombre en la sucursal.' . ($dup['eliminado'] ? ' Está en la papelera: restáurela desde allí.' : '');
    }

    /** 1062 = clave duplicada. Nunca se expone el SQL al cliente. */
    private function errorBd(PDOException $e, $accion) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            $this->json(['success' => false, 'message' => 'Ya existe una caja con ese nombre en la sucursal.']);
        }
        error_log('Cajas ' . $accion . ': ' . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Error de base de datos. Intente nuevamente o revise el log de PHP.']);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'cajas';
        extract($datos);

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . $vista . '.php')) {
            require_once $viewPath . $vista . '.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    private function json(array $payload) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>