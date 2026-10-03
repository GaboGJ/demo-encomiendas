<?php
require_once __DIR__ . '/../../models/moviles/Moviles_model.php';
require_once __DIR__ . '/../../helpers/moviles/ValidarMoviles.php';

/**
 * Módulo Móviles (vehículos).
 * Rutas (router: /carpeta/accion):
 *   /moviles                  index       listado
 *   /moviles/new              new         formulario nuevo
 *   /moviles/update?id=X      update      formulario de edición
 *   /moviles/papelera         papelera    móviles eliminados (borrado suave)
 *   AJAX/JSON: detalle (GET) · guardar · actualizar · cambiarEstado · eliminar · restaurar (POST)
 *
 * Todo se filtra por el sindicato de la sesión (vehiculos -> socios.id_sindicato).
 */
class Moviles_controller {
    private $movilesModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->movilesModel = new Moviles_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->acceso(false);

        $this->vista('moviles/index', [
            'moviles'       => $this->movilesModel->getMoviles($this->idSindicato()),
            'totalPapelera' => $this->movilesModel->contarEliminados($this->idSindicato()),
        ]);
    }

    public function new() {
        $this->acceso(false);

        $this->vista('moviles/new', $this->datosFormulario());
    }

    /** Formulario de edición: /moviles/update?id=X */
    public function update() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $movil = $id > 0 ? $this->movilesModel->obtenerMovil($id, $this->idSindicato()) : null;

        if (!$movil) {
            Flash::set(false, 'El móvil no existe o fue eliminado.', 'Móvil no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/moviles');
            exit;
        }

        $this->vista('moviles/update', $this->datosFormulario() + ['movil' => $movil]);
    }

    public function papelera() {
        $this->acceso(false);

        $this->vista('moviles/papelera', [
            'eliminados' => $this->movilesModel->getEliminados($this->idSindicato()),
        ]);
    }

    /* ============================ AJAX / JSON ============================ */

    /** Detalle para el modal (incluye choferes asignados). */
    public function detalle() {
        $this->acceso(true);

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Identificador de móvil no válido.']);
        }

        $movil = $this->movilesModel->obtenerMovil($id, $this->idSindicato());
        if (!$movil) {
            $this->json(['success' => false, 'message' => 'El móvil no existe o fue eliminado.']);
        }

        $movil['choferes'] = $this->movilesModel->getChoferesAsignados($id);
        $this->json(['success' => true, 'data' => $movil]);
    }

    public function guardar() {
        $this->acceso(true);
        $this->soloPost();

        $d = ValidarMoviles::normalizar($_POST);
        $this->validarDatos($d);

        try {
            $this->validarDuplicado($d);

            $id = $this->movilesModel->crear($d);

            Flash::set(true, 'El móvil "Unidad ' . $d['numero'] . '" fue registrado correctamente.', 'Móvil Registrado');
            $this->json(['success' => true, 'id' => $id]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'guardar');
        }
    }

    public function actualizar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_vehiculo'] ?? 0);
        $movil = $id > 0 ? $this->movilesModel->obtenerMovil($id, $this->idSindicato()) : null;
        if (!$movil) {
            $this->json(['success' => false, 'message' => 'El móvil no existe o fue eliminado.']);
        }

        $d = ValidarMoviles::normalizar($_POST);
        $this->validarDatos($d);

        try {
            $this->validarDuplicado($d, $id);

            $this->movilesModel->actualizar($id, $d);

            Flash::set(true, 'Los datos de la "Unidad ' . $d['numero'] . '" fueron actualizados correctamente.', 'Móvil Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'actualizar');
        }
    }

    /** Activar / desactivar (reversible). */
    public function cambiarEstado() {
        $this->acceso(true);
        $this->soloPost();

        $id     = intval($_POST['id_vehiculo'] ?? 0);
        $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;

        $movil = $id > 0 ? $this->movilesModel->obtenerMovil($id, $this->idSindicato()) : null;
        if (!$movil) {
            $this->json(['success' => false, 'message' => 'El móvil no existe o fue eliminado.']);
        }

        try {
            if ($estado === 0 && $this->movilesModel->tieneTurnosAbiertos($id)) {
                $this->json(['success' => false, 'message' => 'No se puede desactivar: el móvil tiene turnos abiertos. Despáchelos o cancélelos primero.']);
            }

            if (!$this->movilesModel->cambiarEstado($id, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (no hubo cambios).']);
            }

            Flash::set(true, $estado ? 'El móvil fue activado.' : 'El móvil fue desactivado.', 'Estado Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'cambiarEstado');
        }
    }

    /** Borrado suave. */
    public function eliminar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_vehiculo'] ?? 0);
        $movil = $id > 0 ? $this->movilesModel->obtenerMovil($id, $this->idSindicato()) : null;
        if (!$movil) {
            $this->json(['success' => false, 'message' => 'No se pudo eliminar: el móvil no existe o ya fue eliminado.']);
        }

        try {
            if ($this->movilesModel->tieneTurnosAbiertos($id)) {
                $this->json(['success' => false, 'message' => 'No se puede eliminar: el móvil tiene turnos abiertos. Despáchelos o cancélelos primero.']);
            }

            if (!$this->movilesModel->eliminar($id, date('Y-m-d H:i:s'))) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar el móvil.']);
            }

            Flash::set(true, 'La "Unidad ' . $movil['numero_interno_vehiculo'] . '" fue enviada a la papelera.', 'Móvil Eliminado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'eliminar');
        }
    }

    /** Saca un móvil de la papelera (queda activo). */
    public function restaurar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_vehiculo'] ?? 0);
        $movil = $id > 0 ? $this->movilesModel->obtenerMovil($id, $this->idSindicato(), true) : null;
        if (!$movil || $movil['delete_vehiculo'] === null) {
            $this->json(['success' => false, 'message' => 'El móvil no está en la papelera.']);
        }

        try {
            // Evita restaurar si otro móvil vigente tomó el mismo número/placa mientras estaba eliminado
            $dup = $this->movilesModel->buscarDuplicado(
                $movil['numero_interno_vehiculo'], strtoupper((string)$movil['placa_vehiculo']), $this->idSindicato(), $id
            );
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            if (!$this->movilesModel->restaurar($id)) {
                $this->json(['success' => false, 'message' => 'No se pudo restaurar el móvil.']);
            }

            Flash::set(true, 'La "Unidad ' . $movil['numero_interno_vehiculo'] . '" fue restaurada y quedó activa.', 'Móvil Restaurado');
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
            'socios'  => $this->movilesModel->getSociosActivos($this->idSindicato()),
            'modelos' => $this->movilesModel->getModelosActivos(),
        ];
    }

    /** Formato + pertenencia del socio al sindicato + existencia del modelo. Responde JSON si falla. */
    private function validarDatos(array $d) {
        $error = ValidarMoviles::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }
        if (!$this->movilesModel->socioPerteneceASindicato($d['id_socio'], $this->idSindicato())) {
            $this->json(['success' => false, 'message' => 'El socio seleccionado no pertenece a su sindicato.']);
        }
        if (!$this->movilesModel->modeloExiste($d['id_modelo'])) {
            $this->json(['success' => false, 'message' => 'El modelo seleccionado no existe.']);
        }
    }

    private function validarDuplicado(array $d, $excluirId = 0) {
        $dup = $this->movilesModel->buscarDuplicado($d['numero'], $d['placa'], $this->idSindicato(), $excluirId);
        if ($dup) {
            $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
        }
    }

    /** Sesión iniciada. $json = true responde JSON (endpoints AJAX); false redirige (vistas). */
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
        return $dup['campo'] === 'placa'
            ? 'Ya existe un móvil registrado con esa placa.'
            : 'Ya existe un móvil con ese número de unidad en su sindicato.';
    }

    /** Nunca se expone el SQL al cliente. */
    private function errorBd(PDOException $e, $accion) {
        error_log('Moviles ' . $accion . ': ' . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Error de base de datos. Intente nuevamente o revise el log de PHP.']);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'moviles';
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