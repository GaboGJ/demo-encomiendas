<?php
require_once __DIR__ . '/../../models/auth/Auth_model.php';

class Auth_controller {

    private $authModel;

    public function __construct() {
        $this->authModel = new Auth_model();
    }

    /* ============================ VISTA ============================ */

    public function index() {
        // Si ya hay sesión, no mostrar el login
        if (!empty($_SESSION['id_usuario'])) {
            header('Location: ' . rtrim(URL, '/') . '/dashboard');
            exit;
        }

        $viewsPath = __DIR__ . '/../../views/web/';

        require_once $viewsPath . 'layouts/header.php';
        require_once $viewsPath . 'layouts/navbar.php';
        require_once $viewsPath . 'auth/index.php';
        require_once $viewsPath . 'layouts/footer.php';
        require_once $viewsPath . 'layouts/script.php';
    }

    /* ============================ LOGIN ============================ */

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido.']);
        }

        $ci       = $this->post('ci');
        $password = (string)($_POST['password'] ?? '');

        if ($ci === '' || $password === '') {
            $this->json(['success' => false, 'message' => 'Ingrese su C.I. y su contraseña.']);
        }

        try {
            $u = $this->authModel->obtenerUsuarioPorCarnet($ci);

            // Mismo mensaje para "no existe" y "contraseña incorrecta"
            if (!$u || !password_verify($password, $u['password_usuario'])) {
                $this->json(['success' => false, 'message' => 'C.I. o contraseña incorrectos.']);
            }

            if ((int)$u['estado_usuario'] !== 1) {
                $this->json(['success' => false, 'message' => 'Su cuenta está inactiva. Contacte al administrador.']);
            }
            if ((int)$u['estado_rol'] !== 1) {
                $this->json(['success' => false, 'message' => 'Su rol está deshabilitado. Contacte al administrador.']);
            }
            if ((int)$u['estado_sucursal'] !== 1) {
                $this->json(['success' => false, 'message' => 'Su sucursal está deshabilitada. Contacte al administrador.']);
            }

            if (password_needs_rehash($u['password_usuario'], PASSWORD_BCRYPT)) {
                $this->authModel->actualizarHash($u['id_usuario'], $password);
            }

            // Evita fijación de sesión
            session_regenerate_id(true);

            $_SESSION['id_usuario']     = (int)$u['id_usuario'];
            $_SESSION['id_persona']     = (int)$u['id_persona'];
            $_SESSION['id_sucursal']    = (int)$u['id_sucursal'];
            $_SESSION['id_sindicato']   = (int)$u['id_sindicato'];
            $_SESSION['id_rol']         = (int)$u['id_rol'];
            $_SESSION['nombre_rol']     = $u['nombre_rol'];
            $_SESSION['nombre_usuario'] = trim($u['nombre_persona'] . ' ' . $u['apellido_paterno_persona']);
            $_SESSION['ciudad']         = $u['ciudad_sucursal'];

            $this->json([
                'success'  => true,
                'redirect' => rtrim(URL, '/') . '/dashboard'
            ]);

        } catch (PDOException $e) {
            error_log('Auth login: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error de base de datos al iniciar sesión.']);
        }
    }

    /* ============================ REGISTRO CLIENTE ============================ */

    public function registrarCliente() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido.']);
        }

        $ci       = $this->post('ci');
        $nombres  = $this->post('nombres');
        $paterno  = $this->post('paterno');
        $materno  = $this->post('materno');
        $telefono = $this->post('telefono');
        $password = (string)($_POST['password'] ?? '');
        $password2= (string)($_POST['password_confirm'] ?? '');

        $this->validarPersona($ci, $nombres, $paterno, $materno, $telefono);
        $this->validarPassword($password, $password2);

        global $pdo;

        try {
            $pdo->beginTransaction();

            $persona = $this->authModel->buscarPersonaPorCi($ci);
            if ($persona) {
                $idPersona = (int)$persona['id_persona'];
                if ($this->authModel->existeUsuarioVigentePorPersona($idPersona)) {
                    throw new Exception('Ya existe una cuenta registrada con ese C.I.');
                }
                $this->authModel->actualizarTelefonoPersona($idPersona, $telefono);
            } else {
                $idPersona = $this->authModel->insertarPersona($ci, $nombres, $paterno, $materno, $telefono);
            }

            $idSucursal = $this->authModel->obtenerSucursalPorDefecto();
            if (!$idSucursal) {
                throw new Exception('El sistema aún no tiene sucursales configuradas. Contacte al administrador.');
            }

            $idRol = $this->authModel->obtenerOCrearRol(Auth_model::ROL_CLIENTE, 'Cliente con acceso a rastreo de encomiendas');
            $this->authModel->crearUsuario($idPersona, $idSucursal, $idRol, $password);

            $pdo->commit();

            $this->json(['success' => true, 'message' => 'Cuenta creada correctamente. Ya puede iniciar sesión con su C.I.', 'ci' => $ci]);

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Auth registrarCliente: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No se pudo registrar (error de base de datos). Revise el log de PHP.']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ============================ REGISTRO SINDICATO ============================ */

    public function registrarSindicato() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido.']);
        }

        $nombreSind = $this->post('nombre_sindicato');
        $nit        = $this->post('nit');
        $telSind    = $this->post('telefono_sindicato');
        $ciudad     = $this->post('ciudad');

        $ci       = $this->post('rep_ci');
        $nombres  = $this->post('rep_nombres');
        $paterno  = $this->post('rep_paterno');
        $materno  = $this->post('rep_materno');
        $celular  = $this->post('rep_celular');

        $password = (string)($_POST['password'] ?? '');
        $password2= (string)($_POST['password_confirm'] ?? '');

        if ($nombreSind === '' || $nit === '' || $telSind === '' || $ciudad === '') {
            $this->json(['success' => false, 'message' => 'Complete los datos de la institución (nombre, NIT, teléfono y ciudad).']);
        }
        foreach ([$nombreSind, $nit, $telSind, $ciudad] as $v) {
            if (mb_strlen($v, 'UTF-8') > 50) {
                $this->json(['success' => false, 'message' => 'Los datos de la institución no pueden superar 50 caracteres.']);
            }
        }
        $this->validarPersona($ci, $nombres, $paterno, $materno, $celular);
        $this->validarPassword($password, $password2);

        global $pdo;

        try {
            $pdo->beginTransaction();

            if ($this->authModel->existeSindicato($nombreSind)) {
                throw new Exception('Ya existe un sindicato registrado con ese nombre.');
            }

            // Representante legal = administrador del sindicato
            $persona = $this->authModel->buscarPersonaPorCi($ci);
            if ($persona) {
                $idPersona = (int)$persona['id_persona'];
                if ($this->authModel->existeUsuarioVigentePorPersona($idPersona)) {
                    throw new Exception('Ya existe una cuenta registrada con el C.I. del representante.');
                }
                $this->authModel->actualizarTelefonoPersona($idPersona, $celular);
            } else {
                $idPersona = $this->authModel->insertarPersona($ci, $nombres, $paterno, $materno, $celular);
            }

            $idSindicato = $this->authModel->insertarSindicato([
                'nombre'    => $nombreSind,
                'sigla'     => '',
                'nit'       => $nit,
                'telefono'  => $telSind,
                'direccion' => $ciudad
            ]);

            // usuarios.id_sucursal es NOT NULL: se crea la sucursal sede del sindicato
            $idSucursal = $this->authModel->insertarSucursal($idSindicato, 'Casa Matriz', $ciudad, $ciudad);

            $idRol = $this->authModel->obtenerOCrearRol(Auth_model::ROL_ADMIN_SIN, 'Administrador de un sindicato afiliado');
            $this->authModel->crearUsuario($idPersona, $idSucursal, $idRol, $password);

            $pdo->commit();

            $this->json(['success' => true, 'message' => 'Sindicato registrado correctamente. Ingrese con el C.I. del representante.', 'ci' => $ci]);

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Auth registrarSindicato: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No se pudo registrar el sindicato (error de base de datos). Revise el log de PHP.']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ============================ LOGOUT ============================ */

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        header('Location: ' . rtrim(URL, '/') . '/auth');
        exit;
    }

    /* ============================ HELPERS ============================ */

    private function post($key) {
        return trim((string)($_POST[$key] ?? ''));
    }

    private function validarPersona($ci, $nombres, $paterno, $materno, $telefono) {
        if ($ci === '' || $nombres === '' || $paterno === '' || $telefono === '') {
            $this->json(['success' => false, 'message' => 'C.I., nombres, apellido paterno y teléfono son obligatorios.']);
        }
        foreach ([$ci, $nombres, $paterno, $materno, $telefono] as $v) {
            if (mb_strlen($v, 'UTF-8') > 50) {
                $this->json(['success' => false, 'message' => 'Ningún dato personal puede superar 50 caracteres.']);
            }
        }
        if (!preg_match('/^[0-9+\-\s]{6,20}$/', $telefono)) {
            $this->json(['success' => false, 'message' => 'El teléfono solo puede contener números (6 a 20 dígitos).']);
        }
    }

    private function validarPassword($password, $password2) {
        if (strlen($password) < 6) {
            $this->json(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
        }
        if ($password !== $password2) {
            $this->json(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
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