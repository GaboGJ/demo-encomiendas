<?php
require_once __DIR__ . '/../../models/socios/Socios_model.php';
require_once __DIR__ . '/../../models/choferes/Choferes_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../helpers/socios/ValidarSocios.php';

/**
 * Módulo Socios.  /socios · /socios/new · /socios/update?id=X · /socios/papelera
 * AJAX: detalle · buscarPersona (GET) · guardar · actualizar · cambiarEstado · eliminar · restaurar (POST)
 *
 * Un socio SIEMPRE es también chofer: guardar() escribe personas -> socios -> choferes
 * en una sola transacción, reutilizando la persona y el chofer si ya existían.
 *
 * Alcance: el sindicato principal ve y administra socios de TODOS los sindicatos y puede
 * elegir el sindicato; los demás solo ven y registran en el suyo.
 */
class Socios_controller {
    private $sociosModel, $choferesModel, $personasModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->sociosModel   = new Socios_model();
        $this->choferesModel = new Choferes_model();
        $this->personasModel = new Personas_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->acceso(false);
        $this->vista('socios/index', [
            'socios'        => $this->sociosModel->getSocios($this->alcance()),
            'totalPapelera' => $this->sociosModel->contarEliminados($this->alcance()),
            'esPrincipal'   => $this->esPrincipal(),
        ]);
    }

    public function new() {
        $this->acceso(false);
        $this->vista('socios/new', $this->datosFormulario());
    }

    public function update() {
        $this->acceso(false);
        $id = intval($_GET['id'] ?? 0);
        $socio = $id > 0 ? $this->sociosModel->obtenerSocio($id, $this->alcance()) : null;
        if (!$socio) {
            Flash::set(false, 'El socio no existe o fue eliminado.', 'Socio no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/socios');
            exit;
        }
        $this->vista('socios/update', $this->datosFormulario() + ['socio' => $socio]);
    }

    public function papelera() {
        $this->acceso(false);
        $this->vista('socios/papelera', ['eliminados' => $this->sociosModel->getEliminados($this->alcance())]);
    }

    /* ============================ AJAX / JSON ============================ */

    public function detalle() {
        $this->acceso(true);
        $id = intval($_GET['id'] ?? 0);
        $socio = $id > 0 ? $this->sociosModel->obtenerSocio($id, $this->alcance()) : null;
        if (!$socio) $this->json(['success' => false, 'message' => 'El socio no existe o fue eliminado.']);
        $this->json(['success' => true, 'data' => $socio]);
    }

    /** Autocompleta "Nuevo socio" por C.I.: datos de persona + licencia si ya era chofer. */
    public function buscarPersona() {
        $this->acceso(true);
        $ci = ValidarPersona::normalizarCi($_GET['ci'] ?? '');
        $persona = $ci !== '' ? $this->personasModel->buscarPorCi($ci) : null;
        if (!$persona) $this->json(['success' => false]);

        $socio = $this->sociosModel->buscarPorPersona($persona['id_persona']);
        $chofer = null;
        $ch = $this->choferesModel->buscarPorPersona($persona['id_persona']);
        if ($ch && !$ch['eliminado']) $chofer = $this->choferesModel->obtenerChofer($ch['id_chofer']);

        $this->json([
            'success'     => true,
            'persona'     => $persona,
            'chofer'      => $chofer ? [
                'licencia'    => $chofer['licencia_chofer'],
                'categoria'   => $chofer['categoria_licencia_chofer'],
                'vencimiento' => $chofer['vencimiento_licencia_chofer'],
            ] : null,
            'chofer_eliminado' => $ch ? $ch['eliminado'] : false,
            'tiene_socio' => $socio !== null,
            'eliminado'   => $socio ? $socio['eliminado'] : false,
        ]);
    }

    public function guardar() {
        $this->acceso(true);
        $this->soloPost();

        $d = ValidarSocios::normalizar($_POST);
        $error = ValidarSocios::validar($d);
        if ($error !== null) $this->json(['success' => false, 'message' => $error]);

        // Sindicato: el principal elige; los demás siempre el suyo
        $idSindicato = $this->esPrincipal() ? intval($_POST['id_sindicato'] ?? 0) : $this->idSindicato();
        if ($idSindicato <= 0 || !$this->sociosModel->sindicatoValido($idSindicato)) {
            $this->json(['success' => false, 'message' => 'Seleccione un sindicato válido (existente y activo).']);
        }

        global $pdo;
        try {
            $pdo->beginTransaction();

            $persona = $this->personasModel->buscarPorCi($d['ci']);
            if ($persona) {
                $idPersona = (int)$persona['id_persona'];
                $existente = $this->sociosModel->buscarPorPersona($idPersona);
                if ($existente) {
                    throw new Exception($existente['eliminado']
                        ? 'Esta persona ya fue registrada como socio y está en la papelera: restáurela desde allí.'
                        : 'Esta persona ya está registrada como socio.');
                }
                $this->choferesModel->actualizarTelefonoPersona($idPersona, $d['celular']);
            } else {
                $idPersona = (int)$this->personasModel->insertarPersona(
                    $d['ci'], $d['nombres'], $d['paterno'], $d['materno'], $d['celular'],
                    $d['direccion'] !== '' ? $d['direccion'] : null
                );
            }

            if ($d['codigo'] === '') $d['codigo'] = $this->sociosModel->siguienteCodigo();
            $dup = $this->sociosModel->codigoDuplicado($d['codigo']);
            if ($dup) throw new Exception($this->mensajeCodigoDuplicado($dup));

            // El socio también es chofer (se reutiliza si la persona ya lo era)
            $this->asegurarChofer($idPersona, $d);

            $this->sociosModel->crear($idPersona, $idSindicato, $d);
            $pdo->commit();

            Flash::set(true, 'El socio ' . $d['nombres'] . ' ' . $d['paterno'] . ' (' . $d['codigo'] . ') fue registrado correctamente.', 'Socio Registrado');
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

        $id = intval($_POST['id_socio'] ?? 0);
        $socio = $id > 0 ? $this->sociosModel->obtenerSocio($id, $this->alcance()) : null;
        if (!$socio) $this->json(['success' => false, 'message' => 'El socio no existe o fue eliminado.']);

        $d = ValidarSocios::normalizar($_POST);
        if ($d['ci'] === '') $d['ci'] = $socio['carnet_persona'];       // si no llega, se conserva
        if ($d['codigo'] === '') $d['codigo'] = $socio['codigo_socio'];
        $error = ValidarSocios::validar($d);
        if ($error !== null) $this->json(['success' => false, 'message' => $error]);

        // Sindicato: el principal puede cambiarlo; los demás conservan el actual
        $idSindicato = $this->esPrincipal() ? intval($_POST['id_sindicato'] ?? 0) : (int)$socio['id_sindicato'];
        if ($idSindicato <= 0) $idSindicato = (int)$socio['id_sindicato'];
        if ($idSindicato !== (int)$socio['id_sindicato']) {
            if (!$this->sociosModel->sindicatoValido($idSindicato)) {
                $this->json(['success' => false, 'message' => 'El sindicato seleccionado no existe o está inactivo.']);
            }
            if ((int)$socio['total_vehiculos'] > 0) {
                $this->json(['success' => false, 'message' =>
                    'No se puede cambiar de sindicato: el socio es titular de ' . (int)$socio['total_vehiculos'] . ' vehículo(s). Reasígnelos primero.']);
            }
        }
        $d['id_sindicato'] = $idSindicato;

        global $pdo;
        try {
            $dup = $this->sociosModel->codigoDuplicado($d['codigo'], $id);
            if ($dup) $this->json(['success' => false, 'message' => $this->mensajeCodigoDuplicado($dup)]);

            $pdo->beginTransaction();

            // C.I. editable: no puede pertenecer a OTRA persona
            if (strcasecmp($d['ci'], (string)$socio['carnet_persona']) !== 0) {
                $otra = $this->personasModel->buscarPorCi($d['ci']);
                if ($otra && (int)$otra['id_persona'] !== (int)$socio['id_persona']) {
                    throw new Exception('Ya existe otra persona registrada con ese C.I.');
                }
                $this->sociosModel->actualizarCarnet($socio['id_persona'], $d['ci']);
            }

            $this->choferesModel->actualizarPersona($socio['id_persona'], $d);
            $this->asegurarChofer((int)$socio['id_persona'], $d);
            $this->sociosModel->actualizar($id, $d);
            $pdo->commit();

            Flash::set(true, 'Los datos del socio fueron actualizados correctamente.', 'Socio Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->errorBd($e, 'actualizar');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cambiarEstado() {
        $this->acceso(true);
        $this->soloPost();

        $id     = intval($_POST['id_socio'] ?? 0);
        $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;
        if ($id <= 0 || !$this->sociosModel->obtenerSocio($id, $this->alcance())) {
            $this->json(['success' => false, 'message' => 'El socio no existe o fue eliminado.']);
        }
        try {
            if (!$this->sociosModel->cambiarEstado($id, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (no hubo cambios).']);
            }
            Flash::set(true, $estado ? 'El socio fue activado.' : 'El socio fue desactivado.', 'Estado Actualizado');
            $this->json(['success' => true]);
        } catch (PDOException $e) { $this->errorBd($e, 'cambiarEstado'); }
    }

    public function eliminar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_socio'] ?? 0);
        $socio = $id > 0 ? $this->sociosModel->obtenerSocio($id, $this->alcance()) : null;
        if (!$socio) $this->json(['success' => false, 'message' => 'No se pudo eliminar: el socio no existe o ya fue eliminado.']);

        $veh = (int)$socio['total_vehiculos'];
        if ($veh > 0) {
            $this->json(['success' => false, 'message' =>
                'No se puede eliminar: el socio es titular de ' . $veh . ' vehículo(s). Reasígnelos o elimínelos primero, o desactive al socio.']);
        }
        try {
            if (!$this->sociosModel->eliminar($id, date('Y-m-d H:i:s'))) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar el socio.']);
            }
            // Su registro de chofer NO se toca: sigue siendo chofer (se gestiona en Choferes).
            Flash::set(true, 'El socio "' . $socio['nombre_completo'] . '" fue enviado a la papelera.', 'Socio Eliminado');
            $this->json(['success' => true]);
        } catch (PDOException $e) { $this->errorBd($e, 'eliminar'); }
    }

    public function restaurar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_socio'] ?? 0);
        $socio = $id > 0 ? $this->sociosModel->obtenerSocio($id, $this->alcance(), true) : null;
        if (!$socio || $socio['delete_socio'] === null) {
            $this->json(['success' => false, 'message' => 'El socio no está en la papelera.']);
        }
        try {
            if (!$this->sociosModel->restaurar($id)) $this->json(['success' => false, 'message' => 'No se pudo restaurar el socio.']);
            Flash::set(true, 'El socio "' . $socio['nombre_completo'] . '" fue restaurado y quedó activo.', 'Socio Restaurado');
            $this->json(['success' => true]);
        } catch (PDOException $e) { $this->errorBd($e, 'restaurar'); }
    }

    /* ============================ PRIVADOS ============================ */

    /**
     * Garantiza el registro de chofer de la persona sin duplicarla:
     *  - ya es chofer vigente  -> se actualizan licencia/categoría/vencimiento
     *  - chofer en papelera    -> error (se restaura desde Choferes)
     *  - no es chofer          -> se crea
     * Debe llamarse dentro de la transacción del llamador.
     */
    private function asegurarChofer($idPersona, array $d) {
        $ch = $this->choferesModel->buscarPorPersona($idPersona);
        if ($ch) {
            if ($ch['eliminado']) {
                throw new Exception('Esta persona figura como chofer en la papelera: restáurela desde Choferes antes de registrarla como socio.');
            }
            $dup = $this->choferesModel->buscarLicenciaDuplicada($d['licencia'], $ch['id_chofer']);
            if ($dup) throw new Exception($this->mensajeLicenciaDuplicada($dup));
            $this->choferesModel->actualizar($ch['id_chofer'], $d);
            return $ch['id_chofer'];
        }
        $dup = $this->choferesModel->buscarLicenciaDuplicada($d['licencia']);
        if ($dup) throw new Exception($this->mensajeLicenciaDuplicada($dup));
        return $this->choferesModel->crear($idPersona, $d);
    }

    private function idSindicato() { return (int)($_SESSION['id_sindicato'] ?? 0); }
    private function esPrincipal() { return (int)($_SESSION['es_principal'] ?? 0) === 1; }

    /** 0 = todos los sindicatos (principal); si no, el sindicato de la sesión. */
    private function alcance() { return $this->esPrincipal() ? 0 : $this->idSindicato(); }

    private function datosFormulario() {
        return [
            'sindicatos'  => $this->sociosModel->getSindicatosActivos($this->esPrincipal() ? 0 : $this->idSindicato()),
            'esPrincipal' => $this->esPrincipal(),
            'idSindicatoSesion' => $this->idSindicato(),
        ];
    }

    private function acceso($json) {
        if (empty($_SESSION['id_usuario'])) {
            if ($json) $this->json(['success' => false, 'message' => 'Su sesión expiró. Vuelva a iniciar sesión.']);
            header('Location: ' . rtrim(URL, '/') . '/auth/auth_controller/index');
            exit;
        }
    }

    private function soloPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['success' => false, 'message' => 'Método no permitido']);
    }

    private function mensajeCodigoDuplicado(array $dup) {
        return 'Ya existe un socio con ese código.' . ($dup['eliminado'] ? ' Está en la papelera: restáurelo desde allí.' : '');
    }

    private function mensajeLicenciaDuplicada(array $dup) {
        return 'Ya existe un chofer con ese número de licencia.' . ($dup['eliminado'] ? ' Está en la papelera de Choferes.' : '');
    }

    private function errorBd(PDOException $e, $accion) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            $this->json(['success' => false, 'message' => 'Ya existe un socio, chofer o persona con ese código, licencia o C.I.']);
        }
        error_log('Socios ' . $accion . ': ' . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Error de base de datos. Intente nuevamente o revise el log de PHP.']);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'socios';
        extract($datos);
        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';
        if (file_exists($viewPath . $vista . '.php')) require_once $viewPath . $vista . '.php';
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