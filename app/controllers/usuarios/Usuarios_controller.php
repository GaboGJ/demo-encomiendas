<?php
require_once __DIR__ . '/../../models/usuarios/Usuarios_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../helpers/auth/ValidarPersona.php';
require_once __DIR__ . '/../../helpers/auth/ValidarPassword.php';

class Usuarios_controller {
    private $usuariosModel;
    private $personasModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->usuariosModel = new Usuarios_model();
        $this->personasModel = new Personas_model();
    }

    private function idSindicato() {
        return (int)($_SESSION['id_sindicato'] ?? 0);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'usuarios';
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

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->vista('usuarios/index', [
            'usuarios'        => $this->usuariosModel->getUsuarios($this->idSindicato()),
            'idUsuarioActual' => $_SESSION['id_usuario'] ?? 0,
        ]);
    }

    public function new() {
        $this->vista('usuarios/new', [
            'roles'      => $this->usuariosModel->getRolesActivos(),
            'sucursales' => $this->usuariosModel->getSucursalesActivas($this->idSindicato()),
        ]);
    }

    public function update() {
        $id = intval($_GET['id'] ?? 0);
        $usuario = $id > 0 ? $this->usuariosModel->obtenerUsuario($id, $this->idSindicato()) : null;

        if (!$usuario) {
            Flash::set(false, 'El usuario no existe o fue eliminado.', 'Usuario no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/usuarios');
            exit;
        }

        $this->vista('usuarios/update', [
            'usuario'    => $usuario,
            'roles'      => $this->usuariosModel->getRolesActivos(),
            'sucursales' => $this->usuariosModel->getSucursalesActivas($this->idSindicato()),
        ]);
    }

    /* ============================ AJAX / JSON ============================ */

    public function detalle() {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Identificador de usuario no válido.']);
        }

        $usuario = $this->usuariosModel->obtenerUsuario($id, $this->idSindicato());
        if (!$usuario) {
            $this->json(['success' => false, 'message' => 'El usuario no existe o fue eliminado.']);
        }

        $this->json(['success' => true, 'data' => $usuario]);
    }

    public function buscarPersona() {
        $ci = ValidarPersona::normalizarCi($_GET['ci'] ?? '');
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

            $ci        = ValidarPersona::normalizarCi($_POST['ci'] ?? '');
            $nombres   = trim($_POST['nombres'] ?? '');
            $paterno   = trim($_POST['paterno'] ?? '');
            $materno   = trim($_POST['materno'] ?? '');
            $celular   = trim($_POST['celular'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $idRol     = intval($_POST['id_rol'] ?? 0);
            $idSucursal= intval($_POST['id_sucursal'] ?? 0);
            $password  = (string)($_POST['password'] ?? '');
            $password2 = (string)($_POST['password_confirm'] ?? '');

            $error = ValidarPersona::validar($ci, $nombres, $paterno, $materno, $celular);
            if ($error !== null) {
                $this->json(['success' => false, 'message' => $error]);
            }
            if ($idRol <= 0 || $idSucursal <= 0) {
                $this->json(['success' => false, 'message' => 'Seleccione el rol y la sucursal.']);
            }
            $error = ValidarPassword::validar($password, $password2);
            if ($error !== null) {
                $this->json(['success' => false, 'message' => $error]);
            }

            // Alcance: la sucursal debe ser de MI sindicato y el rol debe estar vigente
            if (!$this->usuariosModel->sucursalValida($idSucursal, $this->idSindicato())) {
                $this->json(['success' => false, 'message' => 'La sucursal seleccionada no pertenece a su sindicato o está inactiva.']);
            }
            if (!$this->usuariosModel->rolValido($idRol)) {
                $this->json(['success' => false, 'message' => 'El rol seleccionado no existe o está inactivo.']);
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
            $usuario   = $idUsuario > 0 ? $this->usuariosModel->obtenerUsuario($idUsuario, $this->idSindicato()) : null;
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

            $error = ValidarPersona::validar($ci, $nombres, $paterno, $materno, $celular);
            if ($error !== null) {
                $this->json(['success' => false, 'message' => $error]);
            }
            if ($idRol <= 0 || $idSucursal <= 0) {
                $this->json(['success' => false, 'message' => 'Seleccione el rol y la sucursal.']);
            }
            if ($password !== '') {
                $error = ValidarPassword::validar($password, $password2);
                if ($error !== null) {
                    $this->json(['success' => false, 'message' => $error]);
                }
            }

            // Alcance: sucursal de MI sindicato y rol vigente
            if (!$this->usuariosModel->sucursalValida($idSucursal, $this->idSindicato())) {
                $this->json(['success' => false, 'message' => 'La sucursal seleccionada no pertenece a su sindicato o está inactiva.']);
            }
            if (!$this->usuariosModel->rolValido($idRol)) {
                $this->json(['success' => false, 'message' => 'El rol seleccionado no existe o está inactivo.']);
            }

            global $pdo;
            $pdo->beginTransaction();

            try {
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

            if (!$this->usuariosModel->cambiarEstado($id, $estado, $this->idSindicato())) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (el usuario no existe, no es de su sindicato o no hubo cambios).']);
            }

            Flash::set(true, $estado ? 'La cuenta fue activada.' : 'La cuenta fue desactivada.', 'Estado Actualizado');
            $this->json(['success' => true]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

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

            if (!$this->usuariosModel->Eliminar_usuario($id, date('Y-m-d H:i:s'), $this->idSindicato())) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar: el usuario no existe, ya fue eliminado o no es de su sindicato.']);
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