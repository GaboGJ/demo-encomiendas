<?php
require_once __DIR__ . '/../../models/roles/Roles_model.php';
require_once __DIR__ . '/../../helpers/roles/ValidarRoles.php';

/**
 * Módulo Roles y Permisos.
 * Rutas (router: /carpeta/accion):
 *   /roles                  index       listado
 *   /roles/new              new         formulario nuevo
 *   /roles/update?id=X      update      formulario de edición
 *   /roles/permisos?id=X    permisos    matriz de permisos (módulo x operación)
 *   /roles/papelera         papelera    roles eliminados (borrado suave)
 *   AJAX/JSON: detalle (GET) · guardar · actualizar · cambiarEstado · eliminar · restaurar · guardarPermisos (POST)
 *
 * Los roles son globales (compartidos por todos los sindicatos), por eso solo el
 * sindicato principal puede administrarlos.
 */
class Roles_controller {
    private $rolesModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->rolesModel = new Roles_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->acceso(false);
        $this->vista('roles/index', [
            'roles'         => $this->rolesModel->getRoles(),
            'totalPapelera' => $this->rolesModel->contarEliminados(),
            'idRolActual'   => (int)($_SESSION['id_rol'] ?? 0),
        ]);
    }

    public function new() {
        $this->acceso(false);
        $this->vista('roles/new', ['rolesCopia' => $this->rolesModel->getRoles()]);
    }

    public function update() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $rol = $id > 0 ? $this->rolesModel->obtenerRol($id) : null;
        if (!$rol) {
            Flash::set(false, 'El rol no existe o fue eliminado.', 'Rol no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/roles');
            exit;
        }

        $this->vista('roles/update', ['rol' => $rol, 'rolesCopia' => []]);
    }

    public function papelera() {
        $this->acceso(false);
        $this->vista('roles/papelera', ['eliminados' => $this->rolesModel->getEliminados()]);
    }

    /** Matriz de permisos: /roles/permisos?id=X */
    public function permisos() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $rol = $id > 0 ? $this->rolesModel->obtenerRol($id) : null;
        if (!$rol) {
            Flash::set(false, 'El rol no existe o fue eliminado.', 'Rol no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/roles');
            exit;
        }

        try {
            $this->rolesModel->asegurarCatalogo();
        } catch (PDOException $e) {
            error_log('Roles permisos/asegurarCatalogo: ' . $e->getMessage());
            Flash::set(false, 'No se pudo preparar el catálogo de módulos y operaciones. Revise el log de PHP.', 'Error de base de datos');
            header('Location: ' . rtrim(URL, '/') . '/roles');
            exit;
        }

        $this->vista('roles/permisos', [
            'rol'        => $rol,
            'modulos'    => $this->rolesModel->getModulos(),
            'tipos'      => $this->rolesModel->getTipos(),
            'matriz'     => $this->rolesModel->getMatriz($id),
            'protegido'  => (int)$rol['es_protegido'] === 1,
        ]);
    }

    /* ============================ AJAX / JSON ============================ */

    public function detalle() {
        $this->acceso(true);

        $id = intval($_GET['id'] ?? 0);
        $rol = $id > 0 ? $this->rolesModel->obtenerRol($id) : null;
        if (!$rol) {
            $this->json(['success' => false, 'message' => 'El rol no existe o fue eliminado.']);
        }

        $rol['permisos'] = $this->rolesModel->getResumenPermisos($id);
        $this->json(['success' => true, 'data' => $rol]);
    }

    public function guardar() {
        $this->acceso(true);
        $this->soloPost();

        $d = ValidarRoles::normalizar($_POST);
        $error = ValidarRoles::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        global $pdo;
        try {
            $dup = $this->rolesModel->nombreDuplicado($d['nombre']);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $pdo->beginTransaction();
            $id = $this->rolesModel->crear($d);

            if ($d['copiar_de'] > 0) {
                if (!$this->rolesModel->obtenerRol($d['copiar_de'])) {
                    throw new Exception('El rol del que desea copiar permisos no existe.');
                }
                $this->rolesModel->copiarPermisos($d['copiar_de'], $id);
            }
            $pdo->commit();

            Flash::set(true, 'El rol "' . $d['nombre'] . '" fue registrado. Ahora configure sus permisos.', 'Rol Registrado');
            $this->json(['success' => true, 'id' => $id]);

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

        $id = intval($_POST['id_rol'] ?? 0);
        $rol = $id > 0 ? $this->rolesModel->obtenerRol($id) : null;
        if (!$rol) {
            $this->json(['success' => false, 'message' => 'El rol no existe o fue eliminado.']);
        }

        $d = ValidarRoles::normalizar($_POST);
        if ((int)$rol['es_protegido'] === 1) {
            $d['nombre'] = $rol['nombre_rol']; // el rol del sistema no se renombra
        }
        $error = ValidarRoles::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            $dup = $this->rolesModel->nombreDuplicado($d['nombre'], $id);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $this->rolesModel->actualizar($id, $d);

            Flash::set(true, 'El rol "' . $d['nombre'] . '" fue actualizado correctamente.', 'Rol Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'actualizar');
        }
    }

    /** Activar / desactivar (reversible). Un rol inactivo impide el ingreso de sus usuarios. */
    public function cambiarEstado() {
        $this->acceso(true);
        $this->soloPost();

        $id     = intval($_POST['id_rol'] ?? 0);
        $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;

        $rol = $id > 0 ? $this->rolesModel->obtenerRol($id) : null;
        if (!$rol) {
            $this->json(['success' => false, 'message' => 'El rol no existe o fue eliminado.']);
        }
        if ($estado === 0 && (int)$rol['es_protegido'] === 1) {
            $this->json(['success' => false, 'message' => 'El rol del sistema no se puede desactivar.']);
        }
        if ($estado === 0 && $id === intval($_SESSION['id_rol'] ?? 0)) {
            $this->json(['success' => false, 'message' => 'No puede desactivar su propio rol.']);
        }

        try {
            if (!$this->rolesModel->cambiarEstado($id, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (no hubo cambios).']);
            }
            Flash::set(true, $estado ? 'El rol fue activado.' : 'El rol fue desactivado.', 'Estado Actualizado');
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->errorBd($e, 'cambiarEstado');
        }
    }

    /** Borrado suave. */
    public function eliminar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_rol'] ?? 0);
        $rol = $id > 0 ? $this->rolesModel->obtenerRol($id) : null;
        if (!$rol) {
            $this->json(['success' => false, 'message' => 'No se pudo eliminar: el rol no existe o ya fue eliminado.']);
        }
        if ((int)$rol['es_protegido'] === 1) {
            $this->json(['success' => false, 'message' => 'El rol del sistema no se puede eliminar.']);
        }

        $usuarios = (int)$rol['total_usuarios'];
        if ($usuarios > 0) {
            $this->json(['success' => false, 'message' =>
                'No se puede eliminar: hay ' . $usuarios . ' usuario(s) con este rol. '
                . 'Reasígnelos a otro rol primero, o desactive el rol.']);
        }

        try {
            if (!$this->rolesModel->eliminar($id, date('Y-m-d H:i:s'))) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar el rol.']);
            }
            Flash::set(true, 'El rol "' . $rol['nombre_rol'] . '" fue enviado a la papelera.', 'Rol Eliminado');
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->errorBd($e, 'eliminar');
        }
    }

    public function restaurar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_rol'] ?? 0);
        $rol = $id > 0 ? $this->rolesModel->obtenerRol($id, true) : null;
        if (!$rol || $rol['delete_rol'] === null) {
            $this->json(['success' => false, 'message' => 'El rol no está en la papelera.']);
        }

        try {
            if (!$this->rolesModel->restaurar($id)) {
                $this->json(['success' => false, 'message' => 'No se pudo restaurar el rol.']);
            }
            Flash::set(true, 'El rol "' . $rol['nombre_rol'] . '" fue restaurado y quedó activo.', 'Rol Restaurado');
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->errorBd($e, 'restaurar');
        }
    }

    /** POST id_rol + ids_json (lista de id_operacion marcados en la matriz). */
    public function guardarPermisos() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_rol'] ?? 0);
        $rol = $id > 0 ? $this->rolesModel->obtenerRol($id) : null;
        if (!$rol) {
            $this->json(['success' => false, 'message' => 'El rol no existe o fue eliminado.']);
        }
        if ((int)$rol['es_protegido'] === 1) {
            $this->json(['success' => false, 'message' => 'El rol del sistema tiene acceso total y sus permisos no se modifican.']);
        }

        $ids = ValidarRoles::normalizarOperaciones($_POST['ids_json'] ?? '[]');
        if ($ids === null) {
            $this->json(['success' => false, 'message' => 'Los datos de la matriz no son válidos.']);
        }

        try {
            $total = $this->rolesModel->guardarPermisos($id, $ids);

            Flash::set(true, 'Los permisos del rol "' . $rol['nombre_rol'] . '" fueron guardados (' . $total . ' permiso(s)).', 'Permisos Guardados');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'guardarPermisos');
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ============================ PRIVADOS ============================ */

    /** Sesión + sindicato principal. $json = true responde JSON (AJAX); false redirige (vistas). */
    private function acceso($json) {
        if (empty($_SESSION['id_usuario'])) {
            if ($json) {
                $this->json(['success' => false, 'message' => 'Su sesión expiró. Vuelva a iniciar sesión.']);
            }
            header('Location: ' . rtrim(URL, '/') . '/auth/auth_controller/index');
            exit;
        }

        if ((int)($_SESSION['es_principal'] ?? 0) !== 1) {
            $msg = 'Solo el sindicato principal puede administrar los roles y permisos.';
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
        return 'Ya existe un rol con ese nombre.' . ($dup['eliminado'] ? ' Está en la papelera: restáurelo desde allí.' : '');
    }

    /** Nunca se expone el SQL al cliente. */
    private function errorBd(PDOException $e, $accion) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            $this->json(['success' => false, 'message' => 'Ya existe un rol con ese nombre o un permiso duplicado.']);
        }
        error_log('Roles ' . $accion . ': ' . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Error de base de datos. Intente nuevamente o revise el log de PHP.']);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'roles';
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