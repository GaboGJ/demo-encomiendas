<?php
require_once __DIR__ . '/../../models/usuarios/Usuarios_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../helpers/auth/ValidarPersona.php';

class Usuarios_controller {
    private $usuariosModel;
    private $personasModel;

    public function __construct() {
        $this->usuariosModel = new Usuarios_model();
        $this->personasModel = new Personas_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'usuarios';

        $usuarios          = $this->usuariosModel->getUsuarios();
        $idUsuarioActual   = $_SESSION['id_usuario'] ?? 0;

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . 'usuarios/index.php')) {
            require_once $viewPath . 'usuarios/index.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    public function new() {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'usuarios';

        $roles      = $this->usuariosModel->getRolesActivos();
        $sucursales = $this->usuariosModel->getSucursalesActivas();

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . 'usuarios/new.php')) {
            require_once $viewPath . 'usuarios/new.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    /** Formulario de edición: /usuarios/update?id=X */
    public function update() {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'usuarios';

        $id = intval($_GET['id'] ?? 0);
        $usuario = $id > 0 ? $this->usuariosModel->obtenerUsuario($id) : null;

        if (!$usuario) {
            Flash::set(false, 'El usuario no existe o fue eliminado.', 'Usuario no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/usuarios');
            exit;
        }

        $roles      = $this->usuariosModel->getRolesActivos();
        $sucursales = $this->usuariosModel->getSucursalesActivas();

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . 'usuarios/update.php')) {
            require_once $viewPath . 'usuarios/update.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    /* ============================ AJAX / JSON ============================ */

    /** Detalle para el modal (nunca devuelve el hash de la contraseña). */
    public function detalle() {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Identificador de usuario no válido.']);
        }

        $usuario = $this->usuariosModel->obtenerUsuario($id);
        if (!$usuario) {
            $this->json(['success' => false, 'message' => 'El usuario no existe o fue eliminado.']);
        }

        $this->json(['success' => true, 'data' => $usuario]);
    }

    /** Busca una persona por C.I. para autocompletar el formulario "Nuevo". */
    public function buscarPersona() {
        $ci = trim($_GET['ci'] ?? '');
        if ($ci === '') {
            $this->json(['success' => false]);
        }

        $persona = $this->personasModel->buscarPorCi($ci);
        if (!$persona) {
            $this->json(['success' => false]);
        }

        $this->json([
            'success'       => true,
            'persona'       => $persona,
            'tiene_usuario' => $this->usuariosModel->existeUsuarioVigentePorPersona($persona['id_persona'])
        ]);
    }

    public function guardar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Método no permitido']);
            }

            $ci        = trim($_POST['ci'] ?? '');
            $nombres   = trim($_POST['nombres'] ?? '');
            $paterno   = trim($_POST['paterno'] ?? '');
            $materno   = trim($_POST['materno'] ?? '');
            $celular   = trim($_POST['celular'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $idRol     = intval($_POST['id_rol'] ?? 0);
            $idSucursal= intval($_POST['id_sucursal'] ?? 0);
            $password  = (string)($_POST['password'] ?? '');
            $password2 = (string)($_POST['password_confirm'] ?? '');

            if ($ci === '' || $nombres === '' || $paterno === '' || $celular === '') {
                $this->json(['success' => false, 'message' => 'C.I., nombres, apellido paterno y celular son obligatorios.']);
            }
            if ($idRol <= 0 || $idSucursal <= 0) {
                $this->json(['success' => false, 'message' => 'Seleccione el rol y la sucursal.']);
            }
            if (strlen($password) < 6) {
                $this->json(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            }
            if ($password !== $password2) {
                $this->json(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            }

            global $pdo;
            $pdo->beginTransaction();

            try {
                $persona = $this->personasModel->buscarPorCi($ci);
                if ($persona) {
                    $idPersona = $persona['id_persona'];
                    if ($this->usuariosModel->existeUsuarioVigentePorPersona($idPersona)) {
                        throw new Exception('Ya existe un usuario registrado con ese C.I.');
                    }
                } else {
                    $idPersona = $this->personasModel->insertarPersona($ci, $nombres, $paterno, $materno, $celular, $direccion !== '' ? $direccion : null);
                }

                $this->usuariosModel->crearUsuario([
                    'id_persona'  => $idPersona,
                    'id_sucursal' => $idSucursal,
                    'id_rol'      => $idRol,
                    'password'    => $password
                ]);

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }

            Flash::set(true, "El usuario {$nombres} {$paterno} fue registrado correctamente.", 'Usuario Registrado');
            $this->json(['success' => true]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function actualizar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Método no permitido']);
            }

            $idUsuario = intval($_POST['id_usuario'] ?? 0);
            $usuario   = $idUsuario > 0 ? $this->usuariosModel->obtenerUsuario($idUsuario) : null;
            if (!$usuario) {
                $this->json(['success' => false, 'message' => 'El usuario no existe o fue eliminado.']);
            }

            $ci        = ValidarPersona::normalizarCi($_POST['ci'] ?? $usuario['carnet_persona']);
            $nombres   = trim($_POST['nombres'] ?? '');
            $paterno   = trim($_POST['paterno'] ?? '');
            $materno   = trim($_POST['materno'] ?? '');
            $celular   = trim($_POST['celular'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $idRol     = intval($_POST['id_rol'] ?? 0);
            $idSucursal= intval($_POST['id_sucursal'] ?? 0);
            $password  = (string)($_POST['password'] ?? '');
            $password2 = (string)($_POST['password_confirm'] ?? '');

            // Valida C.I. (mismo formato que el login), nombres y celular
            $error = ValidarPersona::validar($ci, $nombres, $paterno, $materno, $celular);
            if ($error !== null) {
                $this->json(['success' => false, 'message' => $error]);
            }
            if ($idRol <= 0 || $idSucursal <= 0) {
                $this->json(['success' => false, 'message' => 'Seleccione el rol y la sucursal.']);
            }
            if ($password !== '') {
                if (strlen($password) < 6) {
                    $this->json(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.']);
                }
                if ($password !== $password2) {
                    $this->json(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
                }
            }

            global $pdo;
            $pdo->beginTransaction();

            try {
                // C.I. modificado: no puede pertenecer a OTRA persona
                if (strcasecmp($ci, (string)$usuario['carnet_persona']) !== 0) {
                    $otra = $this->personasModel->buscarPorCi($ci);
                    if ($otra && (int)$otra['id_persona'] !== (int)$usuario['id_persona']) {
                        throw new Exception('Ya existe otra persona registrada con ese C.I.');
                    }
                    $this->usuariosModel->actualizarCarnet($usuario['id_persona'], $ci);
                }

                $this->usuariosModel->actualizarPersona($usuario['id_persona'], [
                    'nombres' => $nombres, 'paterno' => $paterno, 'materno' => $materno,
                    'celular' => $celular, 'direccion' => $direccion
                ]);

                $this->usuariosModel->actualizarUsuario($idUsuario, [
                    'id_rol' => $idRol, 'id_sucursal' => $idSucursal, 'password' => $password
                ]);

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }

            Flash::set(true, 'Los datos del usuario fueron actualizados correctamente.', 'Usuario Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                $this->json(['success' => false, 'message' => 'Ya existe otra persona registrada con ese C.I.']);
            }
            error_log('Usuarios actualizar: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error de base de datos al actualizar.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /** Activar / desactivar cuenta (reversible). */
    public function cambiarEstado() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Método no permitido']);
            }

            $id     = intval($_POST['id_usuario'] ?? 0);
            $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;

            if ($id <= 0) {
                $this->json(['success' => false, 'message' => 'Identificador inválido.']);
            }
            if ($id === intval($_SESSION['id_usuario'] ?? 0)) {
                $this->json(['success' => false, 'message' => 'No puede desactivar su propia cuenta.']);
            }

            if (!$this->usuariosModel->cambiarEstado($id, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (el usuario no existe o no hubo cambios).']);
            }

            Flash::set(true, $estado ? 'La cuenta fue activada.' : 'La cuenta fue desactivada.', 'Estado Actualizado');
            $this->json(['success' => true]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /** Borrado suave. */
    public function eliminar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Método no permitido']);
            }

            $id = intval($_POST['id_usuario'] ?? 0);
            if ($id <= 0) {
                $this->json(['success' => false, 'message' => 'Identificador inválido.']);
            }
            if ($id === intval($_SESSION['id_usuario'] ?? 0)) {
                $this->json(['success' => false, 'message' => 'No puede eliminar su propia cuenta.']);
            }

            $fyh = date('Y-m-d H:i:s');
            if (!$this->usuariosModel->Eliminar_usuario($id, $fyh)) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar: el usuario no existe o ya fue eliminado.']);
            }

            Flash::set(true, 'El usuario fue eliminado correctamente.', 'Usuario Eliminado');
            $this->json(['success' => true]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function json(array $payload) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>