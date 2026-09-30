<?php
require_once __DIR__ . '/../../models/auth/Auth_model.php';

/**
 * Auth_controller
 *
 * Rutas (según el router de public/index.php):
 *   /auth                     -> index()            vista de login / registro
 *   /auth/login               -> login()            POST (JSON)
 *   /auth/registrarCliente    -> registrarCliente() POST (JSON)
 *   /auth/registrarSindicato  -> registrarSindicato() POST (JSON)
 *   /auth/logout              -> logout()           redirige a /auth
 */
class Auth_controller {

    const MAX_INTENTOS     = 5;    // intentos fallidos permitidos
    const BLOQUEO_SEGUNDOS = 60;   // tiempo de bloqueo tras superarlos

    private $authModel;

    public function __construct() {
        $this->authModel = new Auth_model();
    }

    /* ============================ VISTA ============================ */

    public function index() {
        // Si ya hay sesión iniciada, no tiene sentido mostrar el login
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
        try {
            $this->soloPost();

            $ci       = trim($_POST['ci'] ?? '');
            $password = (string)($_POST['password'] ?? '');

            if ($ci === '' || $password === '') {
                $this->json(['success' => false, 'message' => 'Ingrese su C.I. y su contraseña.']);
            }

            $this->verificarBloqueo();

            $u = $this->authModel->obtenerUsuarioPorCarnet($ci);

            // Mismo mensaje si no existe el usuario o si la contraseña es incorrecta
            if (!$u || !password_verify($password, $u['password_usuario'])) {
                $this->registrarIntentoFallido();
                $this->json(['success' => false, 'message' => 'C.I. o contraseña incorrectos.']);
            }

            // Credenciales válidas: ahora sí se revisa el estado de la cuenta
            if (!(int)$u['estado_usuario']) {
                $this->json(['success' => false, 'message' => 'Su cuenta está desactivada. Comuníquese con el administrador.']);
            }
            if (!(int)$u['estado_rol'] || !(int)$u['estado_sucursal']) {
                $this->json(['success' => false, 'message' => 'Su rol o su sucursal se encuentran inactivos. Comuníquese con el administrador.']);
            }

            // Evita fijación de sesión y limpia el contador de intentos
            session_regenerate_id(true);
            unset($_SESSION['login_intentos'], $_SESSION['login_bloqueo_hasta']);

            $_SESSION['id_usuario']      = (int)$u['id_usuario'];
            $_SESSION['id_persona']      = (int)$u['id_persona'];
            $_SESSION['id_rol']          = (int)$u['id_rol'];
            $_SESSION['nombre_rol']      = $u['nombre_rol'];
            $_SESSION['id_sucursal']     = (int)$u['id_sucursal'];
            $_SESSION['nombre_sucursal'] = $u['nombre_sucursal'];
            $_SESSION['ciudad_sucursal'] = $u['ciudad_sucursal'];
            $_SESSION['id_sindicato']    = (int)$u['id_sindicato'];
            $_SESSION['carnet']          = $u['carnet_persona'];
            $_SESSION['nombre_usuario']  = trim($u['nombre_persona'] . ' ' . $u['apellido_paterno_persona']);

            // Si el algoritmo/costo del hash cambió, se actualiza de forma transparente
            if (password_needs_rehash($u['password_usuario'], PASSWORD_BCRYPT)) {
                $this->authModel->actualizarHash($u['id_usuario'], $password);
            }

            $this->json([
                'success'  => true,
                'message'  => 'Bienvenido, ' . $_SESSION['nombre_usuario'] . '.',
                'redirect' => rtrim(URL, '/') . '/dashboard'
            ]);

        } catch (PDOException $e) {
            error_log('[Auth::login] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error del servidor al iniciar sesión. Intente nuevamente.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

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

    /* ======================= REGISTRO DE CLIENTE ======================= */

    public function registrarCliente() {
        try {
            $this->soloPost();

            $ci        = trim($_POST['ci'] ?? '');
            $nombres   = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $telefono  = trim($_POST['telefono'] ?? '');
            $password  = (string)($_POST['password'] ?? '');
            $password2 = (string)($_POST['password_confirm'] ?? '');

            if ($ci === '' || $nombres === '' || $apellidos === '' || $telefono === '') {
                $this->json(['success' => false, 'message' => 'Nombres, apellidos, teléfono y C.I. son obligatorios.']);
            }
            $this->validarLongitudes(['C.I.' => $ci, 'Nombres' => $nombres, 'Teléfono' => $telefono], 50);
            $this->validarPassword($password, $password2);

            list($paterno, $materno) = $this->separarApellidos($apellidos);
            if ($paterno === '') {
                $this->json(['success' => false, 'message' => 'Ingrese al menos el apellido paterno.']);
            }

            global $pdo;
            $pdo->beginTransaction();

            try {
                $persona = $this->authModel->buscarPersonaPorCi($ci);

                if ($persona) {
                    $idPersona = (int)$persona['id_persona'];
                    if ($this->authModel->existeUsuarioVigentePorPersona($idPersona)) {
                        throw new Exception('Ya existe una cuenta registrada con ese C.I. Inicie sesión.');
                    }
                    $this->authModel->actualizarTelefonoPersona($idPersona, $telefono);
                } else {
                    $idPersona = $this->authModel->insertarPersona($ci, $nombres, $paterno, $materno, $telefono);
                }

                $idRol = $this->authModel->obtenerOCrearRol(
                    Auth_model::ROL_CLIENTE,
                    'Cliente con acceso al rastreo de sus encomiendas'
                );

                $idSucursal = $this->authModel->obtenerSucursalPorDefecto();
                if (!$idSucursal) {
                    throw new Exception('Todavía no hay sucursales registradas en el sistema. Registre primero un sindicato.');
                }

                $this->authModel->crearUsuario($idPersona, $idSucursal, $idRol, $password);

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }

            $this->json([
                'success' => true,
                'message' => 'Su cuenta fue creada. Ya puede iniciar sesión con su C.I. y su contraseña.',
                'ci'      => $ci
            ]);

        } catch (PDOException $e) {
            error_log('[Auth::registrarCliente] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error del servidor al registrar la cuenta. Intente nuevamente.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ====================== REGISTRO DE SINDICATO ====================== */

    public function registrarSindicato() {
        try {
            $this->soloPost();

            $nombreSind = trim($_POST['nombre_sindicato'] ?? '');
            $nit        = trim($_POST['nit'] ?? '');
            $telSind    = trim($_POST['telefono_sindicato'] ?? '');
            $ciudad     = trim($_POST['ciudad'] ?? '');

            $repNombre  = trim($_POST['rep_nombre'] ?? '');
            $repCi      = trim($_POST['rep_ci'] ?? '');
            $repCel     = trim($_POST['rep_celular'] ?? '');

            $password   = (string)($_POST['password'] ?? '');
            $password2  = (string)($_POST['password_confirm'] ?? '');

            if ($nombreSind === '' || $nit === '' || $telSind === '' || $ciudad === '') {
                $this->json(['success' => false, 'message' => 'Complete los datos de la institución (paso 1).']);
            }
            if ($repNombre === '' || $repCi === '' || $repCel === '') {
                $this->json(['success' => false, 'message' => 'Complete los datos del representante legal (paso 2).']);
            }
            $this->validarLongitudes([
                'Nombre del sindicato' => $nombreSind, 'NIT' => $nit, 'Teléfono central' => $telSind,
                'Ciudad' => $ciudad, 'C.I. del representante' => $repCi, 'Celular' => $repCel
            ], 50);
            $this->validarPassword($password, $password2);

            list($nombres, $paterno, $materno) = $this->separarNombreCompleto($repNombre);
            if ($nombres === '' || $paterno === '') {
                $this->json(['success' => false, 'message' => 'Ingrese el nombre y al menos el apellido paterno del representante.']);
            }

            global $pdo;
            $pdo->beginTransaction();

            try {
                if ($this->authModel->existeSindicato($nombreSind)) {
                    throw new Exception('Ya existe un sindicato registrado con ese nombre.');
                }

                // Persona del representante (si ya existía, se reutiliza sin pisar sus nombres)
                $persona = $this->authModel->buscarPersonaPorCi($repCi);
                if ($persona) {
                    $idPersona = (int)$persona['id_persona'];
                    if ($this->authModel->existeUsuarioVigentePorPersona($idPersona)) {
                        throw new Exception('El representante ya tiene una cuenta con ese C.I. Inicie sesión.');
                    }
                    $this->authModel->actualizarTelefonoPersona($idPersona, $repCel);
                } else {
                    $idPersona = $this->authModel->insertarPersona($repCi, $nombres, $paterno, $materno, $repCel);
                }

                $idSindicato = $this->authModel->insertarSindicato([
                    'nombre'    => $nombreSind,
                    'sigla'     => '',
                    'nit'       => $nit,
                    'telefono'  => $telSind,
                    'direccion' => ''
                ]);

                // direccion_sucursal es NOT NULL: se usa la ciudad hasta que la editen
                $idSucursal = $this->authModel->insertarSucursal($idSindicato, 'Casa Matriz', $ciudad, $ciudad);

                $idRol = $this->authModel->obtenerOCrearRol(
                    Auth_model::ROL_ADMIN_SIN,
                    'Administrador de un sindicato de transporte'
                );

                $this->authModel->crearUsuario($idPersona, $idSucursal, $idRol, $password);

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }

            $this->json([
                'success' => true,
                'message' => 'El sindicato fue registrado. Ingrese con el C.I. del representante y su contraseña.',
                'ci'      => $repCi
            ]);

        } catch (PDOException $e) {
            error_log('[Auth::registrarSindicato] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error del servidor al registrar el sindicato. Intente nuevamente.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ============================ HELPERS ============================ */

    private function soloPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido.']);
        }
    }

    private function validarPassword($password, $password2) {
        if (strlen($password) < 6) {
            $this->json(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
        }
        // bcrypt solo considera los primeros 72 bytes
        if (strlen($password) > 72) {
            $this->json(['success' => false, 'message' => 'La contraseña no puede superar los 72 caracteres.']);
        }
        if ($password !== $password2) {
            $this->json(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
        }
    }

    private function validarLongitudes(array $campos, $max) {
        foreach ($campos as $nombre => $valor) {
            if (mb_strlen($valor, 'UTF-8') > $max) {
                $this->json(['success' => false, 'message' => "El campo \"{$nombre}\" no puede superar los {$max} caracteres."]);
            }
        }
    }

    /** "Pérez Gómez" -> ['Pérez', 'Gómez'] ; "Pérez" -> ['Pérez', ''] */
    private function separarApellidos($apellidos) {
        $p = preg_split('/\s+/u', trim($apellidos), 2);
        return [$p[0] ?? '', $p[1] ?? ''];
    }

    /**
     * "Roberto Suárez"              -> [Roberto, Suárez, '']
     * "Roberto Suárez Rojas"        -> [Roberto, Suárez, Rojas]
     * "Juan Carlos Suárez Rojas"    -> [Juan Carlos, Suárez, Rojas]
     */
    private function separarNombreCompleto($completo) {
        $w = preg_split('/\s+/u', trim($completo));
        $n = count($w);
        if ($n < 2) return [$w[0] ?? '', '', ''];
        if ($n === 2) return [$w[0], $w[1], ''];
        if ($n === 3) return [$w[0], $w[1], $w[2]];
        return [$w[0] . ' ' . $w[1], $w[2], implode(' ', array_slice($w, 3))];
    }

    /* --------- Freno básico contra fuerza bruta (por sesión) --------- */

    private function verificarBloqueo() {
        $hasta = (int)($_SESSION['login_bloqueo_hasta'] ?? 0);
        if ($hasta > time()) {
            $resta = $hasta - time();
            $this->json(['success' => false, 'message' => "Demasiados intentos fallidos. Espere {$resta} segundos e intente de nuevo."]);
        }
    }

    private function registrarIntentoFallido() {
        $n = (int)($_SESSION['login_intentos'] ?? 0) + 1;
        $_SESSION['login_intentos'] = $n;
        if ($n >= self::MAX_INTENTOS) {
            $_SESSION['login_bloqueo_hasta'] = time() + self::BLOQUEO_SEGUNDOS;
            $_SESSION['login_intentos'] = 0;
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