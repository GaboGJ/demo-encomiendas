<?php
require_once __DIR__ . '/../../models/sindicatos/Sindicatos_model.php';
require_once __DIR__ . '/../../helpers/sindicatos/ValidarSindicatos.php';

/**
 * Módulo Sindicatos Asociados.
 * Rutas (router: /carpeta/accion):
 *   /sindicatos                  index       listado
 *   /sindicatos/new              new         formulario nuevo
 *   /sindicatos/update?id=X      update      formulario de edición
 *   /sindicatos/papelera         papelera    sindicatos eliminados (borrado suave)
 *   AJAX/JSON: detalle (GET) · guardar · actualizar · cambiarEstado · eliminar · restaurar (POST)
 *
 * Solo el sindicato principal (el que se registró en Auth) administra a los asociados.
 */
class Sindicatos_controller {
    private $sindicatosModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->sindicatosModel = new Sindicatos_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->acceso(false);

        $this->vista('sindicatos/index', [
            'sindicatos'     => $this->sindicatosModel->getSindicatos(),
            'totalPapelera'  => $this->sindicatosModel->contarEliminados(),
        ]);
    }

    public function new() {
        $this->acceso(false);

        $this->vista('sindicatos/new', []);
    }

    /** Formulario de edición: /sindicatos/update?id=X */
    public function update() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $sindicato = $id > 0 ? $this->sindicatosModel->obtenerSindicato($id) : null;

        if (!$sindicato) {
            Flash::set(false, 'El sindicato no existe o fue eliminado.', 'Sindicato no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/sindicatos');
            exit;
        }

        $this->vista('sindicatos/update', ['sindicato' => $sindicato]);
    }

    public function papelera() {
        $this->acceso(false);

        $this->vista('sindicatos/papelera', [
            'eliminados' => $this->sindicatosModel->getEliminados(),
        ]);
    }

    /* ============================ AJAX / JSON ============================ */

    /** Detalle para el modal. */
    public function detalle() {
        $this->acceso(true);

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Identificador de sindicato no válido.']);
        }

        $sindicato = $this->sindicatosModel->obtenerSindicato($id);
        if (!$sindicato) {
            $this->json(['success' => false, 'message' => 'El sindicato no existe o fue eliminado.']);
        }

        $this->json(['success' => true, 'data' => $sindicato]);
    }

    public function guardar() {
        $this->acceso(true);
        $this->soloPost();

        $d = ValidarSindicatos::normalizar($_POST);
        $error = ValidarSindicatos::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            $dup = $this->sindicatosModel->buscarDuplicado($d['nombre'], $d['nit']);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $id = $this->sindicatosModel->crear($d);

            Flash::set(true, 'El sindicato "' . $d['nombre'] . '" fue registrado correctamente.', 'Sindicato Registrado');
            $this->json(['success' => true, 'id' => $id]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'guardar');
        }
    }

    public function actualizar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_sindicato'] ?? 0);
        $sindicato = $id > 0 ? $this->sindicatosModel->obtenerSindicato($id) : null;
        if (!$sindicato) {
            $this->json(['success' => false, 'message' => 'El sindicato no existe o fue eliminado.']);
        }

        $d = ValidarSindicatos::normalizar($_POST);
        $error = ValidarSindicatos::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            $dup = $this->sindicatosModel->buscarDuplicado($d['nombre'], $d['nit'], $id);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $this->sindicatosModel->actualizar($id, $d);

            Flash::set(true, 'Los datos de "' . $d['nombre'] . '" fueron actualizados correctamente.', 'Sindicato Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'actualizar');
        }
    }

    /** Activar / desactivar (reversible). El principal no se puede desactivar. */
    public function cambiarEstado() {
        $this->acceso(true);
        $this->soloPost();

        $id     = intval($_POST['id_sindicato'] ?? 0);
        $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;

        $sindicato = $id > 0 ? $this->sindicatosModel->obtenerSindicato($id) : null;
        if (!$sindicato) {
            $this->json(['success' => false, 'message' => 'El sindicato no existe o fue eliminado.']);
        }
        if ($estado === 0 && (int)$sindicato['es_principal_sindicato'] === 1) {
            $this->json(['success' => false, 'message' => 'El sindicato principal no se puede desactivar.']);
        }

        try {
            if (!$this->sindicatosModel->cambiarEstado($id, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (no hubo cambios).']);
            }

            Flash::set(true, $estado ? 'El sindicato fue activado.' : 'El sindicato fue desactivado.', 'Estado Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'cambiarEstado');
        }
    }

    /** Borrado suave. */
    public function eliminar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_sindicato'] ?? 0);
        $sindicato = $id > 0 ? $this->sindicatosModel->obtenerSindicato($id) : null;
        if (!$sindicato) {
            $this->json(['success' => false, 'message' => 'No se pudo eliminar: el sindicato no existe o ya fue eliminado.']);
        }
        if ((int)$sindicato['es_principal_sindicato'] === 1 || $id === intval($_SESSION['id_sindicato'] ?? 0)) {
            $this->json(['success' => false, 'message' => 'El sindicato principal no se puede eliminar.']);
        }

        $sucursales = (int)$sindicato['total_sucursales'];
        $socios     = (int)$sindicato['total_socios'];
        if ($sucursales > 0 || $socios > 0) {
            $this->json(['success' => false, 'message' =>
                'No se puede eliminar: tiene ' . $sucursales . ' sucursal(es) y ' . $socios . ' socio(s) vigentes. '
                . 'Elimínelos o reasígnelos primero, o desactive el sindicato.']);
        }

        try {
            $fyh = date('Y-m-d H:i:s');
            if (!$this->sindicatosModel->eliminarSindicato($id, $fyh)) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar el sindicato.']);
            }

            Flash::set(true, 'El sindicato "' . $sindicato['nombre_sindicato'] . '" fue enviado a la papelera.', 'Sindicato Eliminado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'eliminar');
        }
    }

    /** Saca un sindicato de la papelera (queda activo). */
    public function restaurar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_sindicato'] ?? 0);
        $sindicato = $id > 0 ? $this->sindicatosModel->obtenerSindicato($id, true) : null;
        if (!$sindicato || $sindicato['delete_sindicato'] === null) {
            $this->json(['success' => false, 'message' => 'El sindicato no está en la papelera.']);
        }

        try {
            // Evita restaurar si otro sindicato vigente tomó el mismo NIT mientras estaba eliminado
            $dup = $this->sindicatosModel->buscarDuplicado($sindicato['nombre_sindicato'], (string)$sindicato['personeria_sindicato'], $id);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            if (!$this->sindicatosModel->restaurar($id)) {
                $this->json(['success' => false, 'message' => 'No se pudo restaurar el sindicato.']);
            }

            Flash::set(true, 'El sindicato "' . $sindicato['nombre_sindicato'] . '" fue restaurado y quedó activo.', 'Sindicato Restaurado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'restaurar');
        }
    }

    /* ============================ PRIVADOS ============================ */

    /**
     * Sesión iniciada + sindicato principal. $json = true responde JSON (endpoints AJAX);
     * false redirige (vistas).
     */
    private function acceso($json) {
        if (empty($_SESSION['id_usuario'])) {
            if ($json) {
                $this->json(['success' => false, 'message' => 'Su sesión expiró. Vuelva a iniciar sesión.']);
            }
            header('Location: ' . rtrim(URL, '/') . '/auth/auth_controller/index');
            exit;
        }

        if ((int)($_SESSION['es_principal'] ?? 0) !== 1) {
            $msg = 'Solo el sindicato principal puede administrar los sindicatos asociados.';
            if ($json) {
                $this->json(['success' => false, 'message' => $msg]);
            }
            Flash::set(false, $msg, 'Acceso restringido');
            header('Location: ' . rtrim(URL, '/') . '/dashboard');
            exit;
        }
    }

    private function soloPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }
    }

    private function mensajeDuplicado(array $dup) {
        $que   = $dup['campo'] === 'nit' ? 'ese NIT' : 'ese nombre';
        $extra = $dup['eliminado'] ? ' Está en la papelera: restáurelo desde allí.' : '';
        return 'Ya existe un sindicato con ' . $que . '.' . $extra;
    }

    /** 1062 = clave duplicada (otra petición se adelantó a la verificación). Nunca se expone el SQL al cliente. */
    private function errorBd(PDOException $e, $accion) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            $this->json(['success' => false, 'message' => 'Ya existe un sindicato con ese nombre o NIT.']);
        }
        error_log('Sindicatos ' . $accion . ': ' . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Error de base de datos. Intente nuevamente o revise el log de PHP.']);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'sindicatos';
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