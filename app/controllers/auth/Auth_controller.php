<?php
require_once __DIR__ . '/../../models/auth/Auth_model.php';
require_once __DIR__ . '/../../helpers/auth/ValidarPersona.php';
require_once __DIR__ . '/../../helpers/auth/ValidarPassword.php';
require_once __DIR__ . '/../../helpers/auth/ValidarSindicato.php';
require_once __DIR__ . '/../../helpers/auth/ValidarLogin.php';

class Auth_controller {

    private $authModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
        $this->soloPost();

        $ci       = ValidarPersona::normalizarCi($this->post('ci'));
        $password = (string)($_POST['password'] ?? '');

        $error = ValidarLogin::validar($ci, $password);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            $u = $this->authModel->obtenerUsuarioPorCarnet($ci);

// 1. Verificar si el C.I. existe
if (!$u) {
    $this->json(['success' => false, 'message' => 'C.I. o contraseña incorrectos.']);
}

// 2. Verificar contraseña
$ok = password_verify($password, $u['password_usuario']);
if (!$ok) {
    $this->json(['success' => false, 'message' => 'Contraseña incorrecta.']);
}

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

            // Evita fijación de sesión
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

            // Mensaje de bienvenida (se muestra con SweetAlert en el dashboard)
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

    /**
     * Crea en una sola transacción: persona (representante) -> sindicato (principal)
     * -> sucursal "Casa Matriz" -> usuario administrador.
     */
    public function registrarSindicato() {
        $this->soloPost();

        // Paso 1: institución
        $nombreSind = $this->post('nombre_sindicato');
        $nit        = $this->post('nit');
        $telSind    = $this->post('telefono_sindicato');
        $ciudad     = $this->post('ciudad');
        $direccion  = $this->post('direccion');

        // Paso 2: representante legal (será el administrador)
        $ci       = ValidarPersona::normalizarCi($this->post('rep_ci'));
        $nombres  = $this->post('rep_nombres');
        $paterno  = $this->post('rep_paterno');
        $materno  = $this->post('rep_materno');
        $celular  = $this->post('rep_celular');

        // Paso 3: acceso
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

            // El modelo lo inserta con es_principal_sindicato = 1
            $idSindicato = $this->authModel->insertarSindicato([
                'nombre'    => $nombreSind,
                'sigla'     => '',
                'nit'       => $nit,
                'telefono'  => $telSind,
                'direccion' => $direccion
            ]);

            // usuarios.id_sucursal es NOT NULL: se crea la sucursal sede del sindicato
            $idSucursal = $this->authModel->insertarSucursal($idSindicato, 'Casa Matriz', $ciudad, $direccion);

            // El usuario creado con el sindicato es administrador
            $idRol = $this->authModel->obtenerOCrearRol(Auth_model::ROL_ADMIN_SIN, 'Administrador');
            $this->authModel->crearUsuario($idPersona, $idSucursal, $idRol, $password);

            $this->authModel->confirmar();

            $this->json(['success' => true, 'message' => 'Sindicato registrado correctamente. Ingrese con el C.I. del representante.', 'ci' => $ci]);

        } catch (PDOException $e) {
            $this->authModel->revertir();
            // 1062 = clave duplicada (otra petición se adelantó a las verificaciones de arriba)
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