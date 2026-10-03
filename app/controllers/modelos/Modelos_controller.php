<?php
require_once __DIR__ . '/../../models/modelos/Modelos_model.php';
require_once __DIR__ . '/../../helpers/modelos/ValidarModelos.php';

/**
 * Módulo Modelos de Vehículos.
 * Rutas (router: /carpeta/accion):
 *   /modelos                    index       listado
 *   /modelos/new                new         formulario nuevo
 *   /modelos/update?id=X        update      formulario de edición
 *   /modelos/configurar?id=X    configurar  editor del plano de asientos
 *   /modelos/papelera           papelera    modelos eliminados (borrado suave)
 *   AJAX/JSON: detalle (GET) · guardar · actualizar · cambiarEstado · eliminar · restaurar · guardarConfiguracion (POST)
 *
 * Los modelos son compartidos por todos los sindicatos, por eso solo el sindicato
 * principal (el que se registró en Auth) puede administrarlos.
 */
class Modelos_controller {
    private $modelosModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->modelosModel = new Modelos_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->acceso(false);
        $this->vista('modelos/index', [
            'modelos'       => $this->modelosModel->getModelos(),
            'totalPapelera' => $this->modelosModel->contarEliminados(),
        ]);
    }

    public function new() {
        $this->acceso(false);
        $this->vista('modelos/new', []);
    }

    public function update() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $modelo = $id > 0 ? $this->modelosModel->obtenerModelo($id) : null;
        if (!$modelo) {
            Flash::set(false, 'El modelo no existe o fue eliminado.', 'Modelo no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/modelos');
            exit;
        }

        $this->vista('modelos/update', ['modelo' => $modelo]);
    }

    public function papelera() {
        $this->acceso(false);
        $this->vista('modelos/papelera', ['eliminados' => $this->modelosModel->getEliminados()]);
    }

    /** Editor del plano: /modelos/configurar?id=X */
    public function configurar() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $modelo = $id > 0 ? $this->modelosModel->obtenerModelo($id) : null;
        if (!$modelo) {
            Flash::set(false, 'El modelo no existe o fue eliminado.', 'Modelo no encontrado');
            header('Location: ' . rtrim(URL, '/') . '/modelos');
            exit;
        }

        $tipos = array_map(function ($t) {
            return [
                'id'         => (int)$t['id_tipo_elemento'],
                'nombre'     => $t['nombre_tipo_elemento'],
                'es_asiento' => ValidarModelos::esTipoAsiento($t['nombre_tipo_elemento']),
            ];
        }, $this->modelosModel->getTiposElementos());

        $this->vista('modelos/configurar', [
            'modelo'    => $modelo,
            'tipos'     => $tipos,
            'pisos'     => $this->modelosModel->getPisosConfig($id),
            'bloqueado' => $this->modelosModel->tieneVentasAbiertas($id),
        ]);
    }

    /* ============================ AJAX / JSON ============================ */

    /** Detalle para el modal: datos + resumen por piso. */
    public function detalle() {
        $this->acceso(true);

        $id = intval($_GET['id'] ?? 0);
        $modelo = $id > 0 ? $this->modelosModel->obtenerModelo($id) : null;
        if (!$modelo) {
            $this->json(['success' => false, 'message' => 'El modelo no existe o fue eliminado.']);
        }

        $resumen = [];
        foreach ($this->modelosModel->getPisosConfig($id) as $p) {
            $asientos = 0;
            $especiales = 0;
            foreach ($p['elementos'] as $e) {
                if (ValidarModelos::esTipoAsiento($e['tipo_elemento'])) $asientos++;
                elseif (stripos($e['tipo_elemento'], 'pasillo') === false) $especiales++;
            }
            $resumen[] = [
                'numero'     => (int)$p['numero_piso'],
                'nombre'     => $p['nombre_piso'],
                'filas'      => (int)$p['filas_piso'],
                'columnas'   => (int)$p['columnas_piso'],
                'asientos'   => $asientos,
                'especiales' => $especiales,
            ];
        }
        $modelo['pisos'] = $resumen;

        $this->json(['success' => true, 'data' => $modelo]);
    }

    public function guardar() {
        $this->acceso(true);
        $this->soloPost();

        $d = ValidarModelos::normalizar($_POST);
        $error = ValidarModelos::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            $dup = $this->modelosModel->nombreDuplicado($d['nombre']);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $id = $this->modelosModel->crear($d['nombre']);

            Flash::set(true, 'El modelo "' . $d['nombre'] . '" fue registrado. Ahora configure su plano de asientos.', 'Modelo Registrado');
            $this->json(['success' => true, 'id' => $id]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'guardar');
        }
    }

    public function actualizar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_modelo'] ?? 0);
        $modelo = $id > 0 ? $this->modelosModel->obtenerModelo($id) : null;
        if (!$modelo) {
            $this->json(['success' => false, 'message' => 'El modelo no existe o fue eliminado.']);
        }

        $d = ValidarModelos::normalizar($_POST);
        $error = ValidarModelos::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }

        try {
            $dup = $this->modelosModel->nombreDuplicado($d['nombre'], $id);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $this->modelosModel->actualizar($id, $d['nombre']);

            Flash::set(true, 'El modelo "' . $d['nombre'] . '" fue actualizado correctamente.', 'Modelo Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'actualizar');
        }
    }

    /** Activar / desactivar (reversible). Un modelo inactivo deja de ofrecerse al registrar móviles. */
    public function cambiarEstado() {
        $this->acceso(true);
        $this->soloPost();

        $id     = intval($_POST['id_modelo'] ?? 0);
        $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;
        if ($id <= 0 || !$this->modelosModel->obtenerModelo($id)) {
            $this->json(['success' => false, 'message' => 'El modelo no existe o fue eliminado.']);
        }

        try {
            if (!$this->modelosModel->cambiarEstado($id, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (no hubo cambios).']);
            }
            Flash::set(true, $estado ? 'El modelo fue activado.' : 'El modelo fue desactivado.', 'Estado Actualizado');
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->errorBd($e, 'cambiarEstado');
        }
    }

    /** Borrado suave. */
    public function eliminar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_modelo'] ?? 0);
        $modelo = $id > 0 ? $this->modelosModel->obtenerModelo($id) : null;
        if (!$modelo) {
            $this->json(['success' => false, 'message' => 'No se pudo eliminar: el modelo no existe o ya fue eliminado.']);
        }

        $vehiculos = (int)$modelo['total_vehiculos'];
        if ($vehiculos > 0) {
            $this->json(['success' => false, 'message' =>
                'No se puede eliminar: hay ' . $vehiculos . ' vehículo(s) registrados con este modelo. '
                . 'Cámbieles el modelo primero, o desactive el modelo.']);
        }

        try {
            if (!$this->modelosModel->eliminar($id, date('Y-m-d H:i:s'))) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar el modelo.']);
            }
            Flash::set(true, 'El modelo "' . $modelo['nombre_modelo'] . '" fue enviado a la papelera.', 'Modelo Eliminado');
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->errorBd($e, 'eliminar');
        }
    }

    public function restaurar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_modelo'] ?? 0);
        $modelo = $id > 0 ? $this->modelosModel->obtenerModelo($id, true) : null;
        if (!$modelo || $modelo['delete_modelo'] === null) {
            $this->json(['success' => false, 'message' => 'El modelo no está en la papelera.']);
        }

        try {
            if (!$this->modelosModel->restaurar($id)) {
                $this->json(['success' => false, 'message' => 'No se pudo restaurar el modelo.']);
            }
            Flash::set(true, 'El modelo "' . $modelo['nombre_modelo'] . '" fue restaurado y quedó activo.', 'Modelo Restaurado');
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->errorBd($e, 'restaurar');
        }
    }

    /** POST id_modelo + config_json (pisos y elementos del editor). */
    public function guardarConfiguracion() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_modelo'] ?? 0);
        $modelo = $id > 0 ? $this->modelosModel->obtenerModelo($id) : null;
        if (!$modelo) {
            $this->json(['success' => false, 'message' => 'El modelo no existe o fue eliminado.']);
        }

        try {
            if ($this->modelosModel->tieneVentasAbiertas($id)) {
                $this->json(['success' => false, 'message' =>
                    'No se puede modificar el plano: hay pasajes vendidos en turnos abiertos con este modelo. Despache o cancele esos turnos primero.']);
            }

            $tipos = [];
            foreach ($this->modelosModel->getTiposElementos() as $t) {
                $tipos[(int)$t['id_tipo_elemento']] = $t['nombre_tipo_elemento'];
            }

            $res = ValidarModelos::validarConfiguracion(json_decode($_POST['config_json'] ?? '[]', true), $tipos);
            if ($res['error'] !== null) {
                $this->json(['success' => false, 'message' => $res['error']]);
            }

            $this->modelosModel->guardarConfiguracion($id, $res['pisos'], $res['total']);

            Flash::set(true, 'El plano de "' . $modelo['nombre_modelo'] . '" fue guardado (' . $res['total'] . ' asientos).', 'Plano Guardado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'guardarConfiguracion');
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
            $msg = 'Solo el sindicato principal puede administrar los modelos de vehículos.';
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
        return 'Ya existe un modelo con ese nombre.' . ($dup['eliminado'] ? ' Está en la papelera: restáurelo desde allí.' : '');
    }

    /** Nunca se expone el SQL al cliente. */
    private function errorBd(PDOException $e, $accion) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            $this->json(['success' => false, 'message' => 'Ya existe un modelo con ese nombre o un elemento duplicado en el plano.']);
        }
        error_log('Modelos ' . $accion . ': ' . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Error de base de datos. Intente nuevamente o revise el log de PHP.']);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'modelos';
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