<?php
require_once __DIR__ . '/../../models/choferes/Choferes_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../helpers/choferes/ValidarChoferes.php';

/**
 * Módulo Choferes.
 * Rutas (router: /carpeta/accion):
 *   /choferes                  index       listado
 *   /choferes/new              new         formulario nuevo
 *   /choferes/update?id=X      update      formulario de edición
 *   /choferes/papelera         papelera    choferes eliminados (borrado suave)
 *   AJAX/JSON: detalle · buscarPersona (GET) · guardar · actualizar · cambiarEstado · eliminar · restaurar (POST)
 */
class Choferes_controller {
    private $choferesModel;
    private $personasModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->choferesModel = new Choferes_model();
        $this->personasModel = new Personas_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->acceso(false);
        $this->vista('choferes/index', [
            'choferes'      => $this->choferesModel->getChoferes(),
            'totalPapelera' => $this->choferesModel->contarEliminados(),
        ]);
    }

    public function new() {
        $this->acceso(false);
        $this->vista('choferes/new', []);
    }

    /** Formulario de edición: /choferes/update?id=X */
    public function update() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $chofer = $id > 0 ? $this->choferesModel->obtenerChofer($id) : null;

        if (!$chofer) {
            Flash::set(false, 'El chofer no existe o fue eliminado.', 'Chofer no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/choferes');
            exit;
        }

        $this->vista('choferes/update', ['chofer' => $chofer]);
    }

    public function papelera() {
        $this->acceso(false);
        $this->vista('choferes/papelera', [
            'eliminados' => $this->choferesModel->getEliminados(),
        ]);
    }

    /* ============================ AJAX / JSON ============================ */

    public function detalle() {
        $this->acceso(true);

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Identificador de chofer no válido.']);
        }

        $chofer = $this->choferesModel->obtenerChofer($id);
        if (!$chofer) {
            $this->json(['success' => false, 'message' => 'El chofer no existe o fue eliminado.']);
        }

        $this->json(['success' => true, 'data' => $chofer]);
    }

    /** Autocompleta el formulario "Nuevo" a partir del C.I. */
    public function buscarPersona() {
        $this->acceso(true);

        $ci = ValidarPersona::normalizarCi($_GET['ci'] ?? '');
        if ($ci === '') {
            $this->json(['success' => false]);
        }

        $persona = $this->personasModel->buscarPorCi($ci);
        if (!$persona) {
            $this->json(['success' => false]);
        }

        $chofer = $this->choferesModel->buscarPorPersona($persona['id_persona']);
        $this->json([
            'success'      => true,
            'persona'      => $persona,
            'tiene_chofer' => $chofer !== null,
            'eliminado'    => $chofer ? $chofer['eliminado'] : false
        ]);
    }

    public function guardar() {
        $this->acceso(true);
        $this->soloPost();

        $d = ValidarChoferes::normalizar($_POST);
        $error = ValidarChoferes::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        global $pdo;
        try {
            $pdo->beginTransaction();

            $persona = $this->personasModel->buscarPorCi($d['ci']);
            if ($persona) {
                $idPersona = (int)$persona['id_persona'];
                $existente = $this->choferesModel->buscarPorPersona($idPersona);
                if ($existente) {
                    throw new Exception($existente['eliminado']
                        ? 'Esta persona ya fue registrada como chofer y está en la papelera: restáurela desde allí.'
                        : 'Esta persona ya está registrada como chofer.');
                }
                $this->choferesModel->actualizarTelefonoPersona($idPersona, $d['celular']);
            } else {
                $idPersona = (int)$this->personasModel->insertarPersona(
                    $d['ci'], $d['nombres'], $d['paterno'], $d['materno'], $d['celular'],
                    $d['direccion'] !== '' ? $d['direccion'] : null
                );
            }

            $dup = $this->choferesModel->buscarLicenciaDuplicada($d['licencia']);
            if ($dup) {
                throw new Exception($this->mensajeLicenciaDuplicada($dup));
            }

            $this->choferesModel->crear($idPersona, $d);
            $pdo->commit();

            Flash::set(true, 'El chofer ' . $d['nombres'] . ' ' . $d['paterno'] . ' fue registrado correctamente.', 'Chofer Registrado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->errorBd($e, 'guardar');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function actualizar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_chofer'] ?? 0);
        $chofer = $id > 0 ? $this->choferesModel->obtenerChofer($id) : null;
        if (!$chofer) {
            $this->json(['success' => false, 'message' => 'El chofer no existe o fue eliminado.']);
        }

        $d = ValidarChoferes::normalizar($_POST);
        $d['ci'] = $chofer['carnet_persona']; // el C.I. no se modifica
        $error = ValidarChoferes::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        global $pdo;
        try {
            $dup = $this->choferesModel->buscarLicenciaDuplicada($d['licencia'], $id);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeLicenciaDuplicada($dup)]);
            }

            $pdo->beginTransaction();
            $this->choferesModel->actualizarPersona($chofer['id_persona'], $d);
            $this->choferesModel->actualizar($id, $d);
            $pdo->commit();

            Flash::set(true, 'Los datos del chofer fueron actualizados correctamente.', 'Chofer Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->errorBd($e, 'actualizar');
        }
    }

    /** Activar / desactivar (reversible). */
    public function cambiarEstado() {
        $this->acceso(true);
        $this->soloPost();

        $id     = intval($_POST['id_chofer'] ?? 0);
        $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;

        if ($id <= 0 || !$this->choferesModel->obtenerChofer($id)) {
            $this->json(['success' => false, 'message' => 'El chofer no existe o fue eliminado.']);
        }

        try {
            if (!$this->choferesModel->cambiarEstado($id, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (no hubo cambios).']);
            }
            Flash::set(true, $estado ? 'El chofer fue activado.' : 'El chofer fue desactivado.', 'Estado Actualizado');
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->errorBd($e, 'cambiarEstado');
        }
    }

    /** Borrado suave. */
    public function eliminar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_chofer'] ?? 0);
        $chofer = $id > 0 ? $this->choferesModel->obtenerChofer($id) : null;
        if (!$chofer) {
            $this->json(['success' => false, 'message' => 'No se pudo eliminar: el chofer no existe o ya fue eliminado.']);
        }

        $vehiculos = (int)$chofer['total_vehiculos'];
        if ($vehiculos > 0) {
            $this->json(['success' => false, 'message' =>
                'No se puede eliminar: el chofer tiene ' . $vehiculos . ' vehículo(s) asignado(s). '
                . 'Quite la asignación primero, o desactive al chofer.']);
        }

        try {
            if (!$this->choferesModel->eliminarChofer($id, date('Y-m-d H:i:s'))) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar el chofer.']);
            }
            Flash::set(true, 'El chofer "' . trim($chofer['nombre_completo']) . '" fue enviado a la papelera.', 'Chofer Eliminado');
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->errorBd($e, 'eliminar');
        }
    }

    /** Saca un chofer de la papelera (queda activo). */
    public function restaurar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_chofer'] ?? 0);
        $chofer = $id > 0 ? $this->choferesModel->obtenerChofer($id, true) : null;
        if (!$chofer || $chofer['delete_chofer'] === null) {
            $this->json(['success' => false, 'message' => 'El chofer no está en la papelera.']);
        }

        try {
            if (!$this->choferesModel->restaurar($id)) {
                $this->json(['success' => false, 'message' => 'No se pudo restaurar el chofer.']);
            }
            Flash::set(true, 'El chofer "' . trim($chofer['nombre_completo']) . '" fue restaurado y quedó activo.', 'Chofer Restaurado');
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->errorBd($e, 'restaurar');
        }
    }

    /* ============================ PRIVADOS ============================ */

    /** Exige sesión iniciada. $json = true responde JSON (AJAX); false redirige (vistas). */
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

    private function mensajeLicenciaDuplicada(array $dup) {
        return 'Ya existe un chofer con ese número de licencia.'
             . ($dup['eliminado'] ? ' Está en la papelera: restáurelo desde allí.' : '');
    }

    /** 1062 = clave duplicada. Nunca se expone el SQL al cliente. */
    private function errorBd(PDOException $e, $accion) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            $this->json(['success' => false, 'message' => 'Ya existe un chofer con esa licencia o esa persona.']);
        }
        error_log('Choferes ' . $accion . ': ' . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Error de base de datos. Intente nuevamente o revise el log de PHP.']);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'choferes';
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