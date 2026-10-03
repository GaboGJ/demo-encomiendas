<?php
require_once __DIR__ . '/../../models/rutas/Rutas_model.php';
require_once __DIR__ . '/../../helpers/rutas/ValidarRutas.php';

/**
 * Módulo Rutas y Tarifarios.
 * Rutas (router: /carpeta/accion):
 *   /rutas                  index       listado
 *   /rutas/new              new         formulario nuevo
 *   /rutas/update?id=X      update      formulario de edición
 *   /rutas/papelera         papelera    rutas eliminadas (borrado suave)
 *   AJAX/JSON: detalle (GET) · guardar · actualizar · cambiarEstado · eliminar · restaurar (POST)
 *
 * Todo se filtra por el sindicato de la sesión (origen -> sucursales.id_sindicato).
 */
class Rutas_controller {
    private $rutasModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->rutasModel = new Rutas_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->acceso(false);
        $this->vista('rutas/index', [
            'rutas'         => $this->rutasModel->getRutas($this->idSindicato()),
            'totalPapelera' => $this->rutasModel->contarEliminados($this->idSindicato()),
        ]);
    }

    public function new() {
        $this->acceso(false);
        $this->vista('rutas/new', $this->datosFormulario() + ['tarifas' => []]);
    }

    public function update() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id, $this->idSindicato()) : null;
        if (!$ruta) {
            Flash::set(false, 'La ruta no existe o fue eliminada.', 'Ruta no encontrada');
            header('Location: ' . rtrim(URL, '/') . '/rutas');
            exit;
        }

        $this->vista('rutas/update', $this->datosFormulario() + [
            'ruta'    => $ruta,
            'tarifas' => $this->rutasModel->getTarifas($ruta['id_sucursal_origen'], $ruta['id_sucursal_destino']),
        ]);
    }

    public function papelera() {
        $this->acceso(false);
        $this->vista('rutas/papelera', ['eliminados' => $this->rutasModel->getEliminados($this->idSindicato())]);
    }

    /* ============================ AJAX / JSON ============================ */

    public function detalle() {
        $this->acceso(true);

        $id = intval($_GET['id'] ?? 0);
        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id, $this->idSindicato()) : null;
        if (!$ruta) {
            $this->json(['success' => false, 'message' => 'La ruta no existe o fue eliminada.']);
        }

        $ruta['tarifas'] = $this->rutasModel->getTarifas($ruta['id_sucursal_origen'], $ruta['id_sucursal_destino']);
        $this->json(['success' => true, 'data' => $ruta]);
    }

    public function guardar() {
        $this->acceso(true);
        $this->soloPost();

        $d = ValidarRutas::normalizar($_POST);
        $this->validarDatos($d);

        try {
            if (!$this->rutasModel->origenPerteneceASindicato($d['id_origen'], $this->idSindicato())) {
                $this->json(['success' => false, 'message' => 'La sucursal de origen no pertenece a su sindicato o está inactiva.']);
            }
            if (!$this->rutasModel->destinoValido($d['id_destino'])) {
                $this->json(['success' => false, 'message' => 'La sucursal de destino no existe o está inactiva.']);
            }
            $dup = $this->rutasModel->buscarDuplicado($d['id_origen'], $d['id_destino']);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $id = $this->rutasModel->crear($d);

            Flash::set(true, 'La ruta fue registrada correctamente.', 'Ruta Registrada');
            $this->json(['success' => true, 'id' => $id]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'guardar');
        }
    }

    public function actualizar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_ruta'] ?? 0);
        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id, $this->idSindicato()) : null;
        if (!$ruta) {
            $this->json(['success' => false, 'message' => 'La ruta no existe o fue eliminada.']);
        }

        $d = ValidarRutas::normalizar($_POST);
        // Origen y destino son la identidad de la ruta: no se modifican
        $d['id_origen']  = (int)$ruta['id_sucursal_origen'];
        $d['id_destino'] = (int)$ruta['id_sucursal_destino'];
        $this->validarDatos($d);

        try {
            $this->rutasModel->actualizar($id, $ruta, $d);

            Flash::set(true, 'La ruta ' . $ruta['ciudad_origen'] . ' ➔ ' . $ruta['ciudad_destino'] . ' fue actualizada correctamente.', 'Ruta Actualizada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'actualizar');
        }
    }

    /** Activar / desactivar (reversible). Una ruta inactiva deja de ofrecerse en turnos y encomiendas. */
    public function cambiarEstado() {
        $this->acceso(true);
        $this->soloPost();

        $id     = intval($_POST['id_ruta'] ?? 0);
        $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;

        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id, $this->idSindicato()) : null;
        if (!$ruta) {
            $this->json(['success' => false, 'message' => 'La ruta no existe o fue eliminada.']);
        }

        try {
            if ($estado === 0 && $this->rutasModel->tieneTurnosAbiertos($ruta['id_sucursal_origen'], $ruta['id_sucursal_destino'])) {
                $this->json(['success' => false, 'message' => 'No se puede desactivar: hay turnos abiertos en esta ruta. Despáchelos o cancélelos primero.']);
            }
            if (!$this->rutasModel->cambiarEstado($id, $ruta, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (no hubo cambios).']);
            }

            Flash::set(true, $estado ? 'La ruta fue activada.' : 'La ruta fue desactivada.', 'Estado Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'cambiarEstado');
        }
    }

    /** Borrado suave. */
    public function eliminar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_ruta'] ?? 0);
        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id, $this->idSindicato()) : null;
        if (!$ruta) {
            $this->json(['success' => false, 'message' => 'No se pudo eliminar: la ruta no existe o ya fue eliminada.']);
        }

        try {
            if ($this->rutasModel->tieneTurnosAbiertos($ruta['id_sucursal_origen'], $ruta['id_sucursal_destino'])) {
                $this->json(['success' => false, 'message' => 'No se puede eliminar: hay turnos abiertos en esta ruta. Despáchelos o cancélelos primero.']);
            }
            if (!$this->rutasModel->eliminar($id, $ruta, date('Y-m-d H:i:s'))) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar la ruta.']);
            }

            Flash::set(true, 'La ruta ' . $ruta['ciudad_origen'] . ' ➔ ' . $ruta['ciudad_destino'] . ' fue enviada a la papelera.', 'Ruta Eliminada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'eliminar');
        }
    }

    public function restaurar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_ruta'] ?? 0);
        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id, $this->idSindicato(), true) : null;
        if (!$ruta || $ruta['delete_precio_pasaje'] === null) {
            $this->json(['success' => false, 'message' => 'La ruta no está en la papelera.']);
        }

        try {
            if (!$this->rutasModel->restaurar($id, $ruta)) {
                $this->json(['success' => false, 'message' => 'No se pudo restaurar la ruta.']);
            }

            Flash::set(true, 'La ruta ' . $ruta['ciudad_origen'] . ' ➔ ' . $ruta['ciudad_destino'] . ' fue restaurada y quedó activa.', 'Ruta Restaurada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'restaurar');
        }
    }

    /* ============================ PRIVADOS ============================ */

    private function idSindicato() {
        return (int)($_SESSION['id_sindicato'] ?? 0);
    }

    private function datosFormulario() {
        return [
            'origenes'   => $this->rutasModel->getOrigenes($this->idSindicato()),
            'destinos'   => $this->rutasModel->getDestinos(),
            'contenidos' => $this->rutasModel->getContenidosActivos(),
        ];
    }

    /** Formato + existencia de los tipos de contenido. Responde JSON si falla. */
    private function validarDatos(array $d) {
        $error = ValidarRutas::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }
        $ids = array_column($d['tarifas'], 'id_contenido');
        if ($ids && $this->rutasModel->contenidosValidos($ids) !== count($ids)) {
            $this->json(['success' => false, 'message' => 'Algún tipo de contenido no existe o está inactivo. Recargue la página.']);
        }
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
        return 'Ya existe una ruta entre esas dos sucursales.' . ($dup['eliminado'] ? ' Está en la papelera: restáurela desde allí.' : '');
    }

    /** Nunca se expone el SQL al cliente. */
    private function errorBd(PDOException $e, $accion) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            $this->json(['success' => false, 'message' => 'Ya existe una ruta entre esas dos sucursales.']);
        }
        error_log('Rutas ' . $accion . ': ' . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Error de base de datos. Intente nuevamente o revise el log de PHP.']);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'rutas';
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