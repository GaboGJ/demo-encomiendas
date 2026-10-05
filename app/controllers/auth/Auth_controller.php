<?php
require_once __DIR__ . '/../../models/auth/Auth_model.php';
require_once __DIR__ . '/../../helpers/auth/ValidarPersona.php';
require_once __DIR__ . '/../../helpers/auth/ValidarPassword.php';
require_once __DIR__ . '/../../helpers/auth/ValidarSindicato.php';
require_once __DIR__ . '/../../helpers/auth/ValidarLogin.php';

class Auth_controller {

    // Hash bcrypt válido y descartable: se verifica cuando el C.I. no existe,
    // para que el tiempo de respuesta no delate qué carnets están registrados.
    const HASH_FALSO = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    const MSG_CREDENCIALES = 'C.I. o contraseña incorrectos.';

    private $authModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->authModel = new Auth_model();
    }

    /* ============================ VISTA ============================ */

    public function index() {
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
        $this->soloPost();

        $ci       = ValidarPersona::normalizarCi($this->post('ci'));
        $password = (string)($_POST['password'] ?? '');

        $error = ValidarLogin::validar($ci, $password);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            $u = $this->authModel->obtenerUsuarioPorCarnet($ci);

            // Un solo mensaje para "C.I. inexistente" y "contraseña incorrecta"
            if (!$u) {
                password_verify($password, self::HASH_FALSO);
                $this->json(['success' => false, 'message' => self::MSG_CREDENCIALES]);
            }
            if (!password_verify($password, $u['password_usuario'])) {
                $this->json(['success' => false, 'message' => self::MSG_CREDENCIALES]);
            }

            // Solo con credenciales correctas se informa el estado de la cuenta
            if ((int)$u['estado_usuario'] !== 1) {
                $this->json(['success' => false, 'message' => 'Su cuenta está inactiva. Contacte al administrador.']);
            }
            if ((int)$u['activo_rol'] !== 1) {
                $this->json(['success' => false, 'message' => 'Su rol está deshabilitado. Contacte al administrador.']);
            }
            if ((int)$u['activo_sucursal'] !== 1) {
                $this->json(['success' => false, 'message' => 'Su sucursal está deshabilitada. Contacte al administrador.']);
            }
            if ((int)$u['activo_sindicato'] !== 1) {
                $this->json(['success' => false, 'message' => 'Su sindicato está deshabilitado. Contacte al administrador.']);
            }

            if (password_needs_rehash($u['password_usuario'], PASSWORD_BCRYPT)) {
                $this->authModel->actualizarHash($u['id_usuario'], $password);
            }

            session_regenerate_id(true);

            $_SESSION['id_usuario']      = (int)$u['id_usuario'];
            $_SESSION['id_persona']      = (int)$u['id_persona'];
            $_SESSION['id_sucursal']     = (int)$u['id_sucursal'];
            $_SESSION['id_sindicato']    = (int)$u['id_sindicato'];
            $_SESSION['es_principal']    = (int)$u['es_principal_sindicato'];
            $_SESSION['id_rol']          = (int)$u['id_rol'];
            $_SESSION['nombre_rol']      = $u['nombre_rol'];
            $_SESSION['nombre_usuario']  = trim($u['nombre_persona'] . ' ' . $u['apellido_paterno_persona']);
            $_SESSION['nombre_sindicato']= $u['nombre_sindicato'];
            $_SESSION['ciudad']          = $u['ciudad_sucursal'];

            Flash::set(
                true,
                'Has ingresado como ' . $u['nombre_rol'] . ' en ' . $u['nombre_sindicato'] . '.',
                '¡Bienvenido, ' . $_SESSION['nombre_usuario'] . '!',
                'success'
            );

            $this->json([
                'success'  => true,
                'redirect' => rtrim(URL, '/') . '/dashboard'
            ]);

        } catch (PDOException $e) {
            error_log('Auth login: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error de base de datos al iniciar sesión.']);
        }
    }

    /* ============================ REGISTRO SINDICATO ============================ */

    public function registrarSindicato() {
        $this->soloPost();

        $nombreSind = $this->post('nombre_sindicato');
        $nit        = $this->post('nit');
        $telSind    = $this->post('telefono_sindicato');
        $ciudad     = $this->post('ciudad');
        $direccion  = $this->post('direccion');

        $ci       = ValidarPersona::normalizarCi($this->post('rep_ci'));
        $nombres  = $this->post('rep_nombres');
        $paterno  = $this->post('rep_paterno');
        $materno  = $this->post('rep_materno');
        $celular  = $this->post('rep_celular');

        $password  = (string)($_POST['password'] ?? '');
        $password2 = (string)($_POST['password_confirm'] ?? '');

        $error = ValidarSindicato::validar($nombreSind, $nit, $telSind, $ciudad, $direccion)
              ?? ValidarPersona::validar($ci, $nombres, $paterno, $materno, $celular)
              ?? ValidarPassword::validar($password, $password2);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            $this->authModel->iniciarTransaccion();

            if ($this->authModel->existeSindicato($nombreSind)) {
                throw new Exception('Ya existe un sindicato registrado con ese nombre.');
            }
            if ($this->authModel->existeNit($nit)) {
                throw new Exception('Ya existe un sindicato registrado con ese NIT.');
            }

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
                'direccion' => $direccion
            ]);

            $idSucursal = $this->authModel->insertarSucursal($idSindicato, 'Casa Matriz', $ciudad, $direccion);

            $idRol = $this->authModel->obtenerOCrearRol(Auth_model::ROL_ADMIN_SIN, 'Administrador');
            $this->authModel->crearUsuario($idPersona, $idSucursal, $idRol, $password);

            $this->authModel->confirmar();

            $this->json(['success' => true, 'message' => 'Sindicato registrado correctamente. Ingrese con el C.I. del representante.', 'ci' => $ci]);

        } catch (PDOException $e) {
            $this->authModel->revertir();
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                $this->json(['success' => false, 'message' => 'Ya existe un registro con esos datos (nombre del sindicato, NIT o C.I.).']);
            }
            error_log('Auth registrarSindicato: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No se pudo registrar el sindicato (error de base de datos). Revise el log de PHP.']);
        } catch (Exception $e) {
            $this->authModel->revertir();
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

    /* ============================ UTILIDADES ============================ */

    private function post($key) {
        return trim((string)($_POST[$key] ?? ''));
    }

    private function soloPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->json(['success' => false, 'message' => 'Método no permitido.']);
        }
    }

    private function json(array $payload) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}