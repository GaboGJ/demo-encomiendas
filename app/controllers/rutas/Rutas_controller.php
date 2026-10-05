<?php
require_once __DIR__ . '/../../models/rutas/Rutas_model.php';
require_once __DIR__ . '/../../helpers/rutas/ValidarRutas.php';

/**
 * Módulo Rutas y Tarifarios.
 * Se LISTAN las rutas de todos los sindicatos. Solo se pueden modificar las del
 * propio sindicato (el sindicato principal puede modificar todas).
 */
class Rutas_controller {
    private $rutasModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->rutasModel = new Rutas_model();
    }

    /* ============================ VISTAS ============================ */

    public function index() {
        $this->acceso(false);
        $this->vista('rutas/index', [
            'rutas'         => $this->rutasModel->getRutas(),
            'totalPapelera' => $this->rutasModel->contarEliminados(),
        ]);
    }

    public function new() {
        $this->acceso(false);
        $this->vista('rutas/new', $this->datosFormulario() + ['tarifas' => []]);
    }

    public function update() {
        $this->acceso(false);

        $id = intval($_GET['id'] ?? 0);
        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id) : null;
        if (!$ruta) {
            Flash::set(false, 'La ruta no existe o fue eliminada.', 'Ruta no encontrada');
            header('Location: ' . rtrim(URL, '/') . '/rutas');
            exit;
        }
        if (!$this->puedeModificar($ruta)) {
            Flash::set(false, 'Solo puede modificar las rutas de su propio sindicato.', 'Acceso restringido');
            header('Location: ' . rtrim(URL, '/') . '/rutas');
            exit;
        }

        $this->vista('rutas/update', $this->datosFormulario() + [
            'ruta'    => $ruta,
            'tarifas' => $this->rutasModel->getTarifas($ruta['id_sucursal_origen'], $ruta['id_sucursal_destino'], $ruta['id_sindicato']),
        ]);
    }

    public function papelera() {
        $this->acceso(false);
        $this->vista('rutas/papelera', ['eliminados' => $this->rutasModel->getEliminados()]);
    }

    /* ============================ AJAX / JSON ============================ */

    public function detalle() {
        $this->acceso(true);

        $id = intval($_GET['id'] ?? 0);
        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id) : null;
        if (!$ruta) {
            $this->json(['success' => false, 'message' => 'La ruta no existe o fue eliminada.']);
        }

        $ruta['tarifas'] = $this->rutasModel->getTarifas($ruta['id_sucursal_origen'], $ruta['id_sucursal_destino'], $ruta['id_sindicato']);
        $this->json(['success' => true, 'data' => $ruta]);
    }

    public function guardar() {
        $this->acceso(true);
        $this->soloPost();

        $d = ValidarRutas::normalizar($_POST);
        $d['id_origen'] = $this->idSucursal();
        if (!$this->esPrincipal()) $d['id_sindicato'] = $this->idSindicato();
        $this->validarDatos($d);

        try {
            if (!$this->rutasModel->origenPerteneceASindicato($d['id_origen'], $this->idSindicato())) {
                $this->json(['success' => false, 'message' => 'Su sucursal no pertenece a su sindicato o está inactiva.']);
            }
            if (!$this->rutasModel->destinoValido($d['id_destino'])) {
                $this->json(['success' => false, 'message' => 'La sucursal de destino no existe o está inactiva.']);
            }
            if (!$this->rutasModel->sindicatoValido($d['id_sindicato'])) {
                $this->json(['success' => false, 'message' => 'El sindicato seleccionado no existe o está inactivo.']);
            }
            $dup = $this->rutasModel->buscarDuplicado($d['id_origen'], $d['id_destino'], $d['id_sindicato']);
            if ($dup) {
                $this->json(['success' => false, 'message' => $this->mensajeDuplicado($dup)]);
            }

            $id = $this->rutasModel->crear($d);

            Flash::set(true, 'La ruta fue registrada correctamente.', 'Ruta Registrada');
            $this->json(['success' => true, 'id' => $id]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'guardar');
        }
    }

    public function actualizar() {
        $this->acceso(true);
        $this->soloPost();

        $ruta = $this->rutaModificable(intval($_POST['id_ruta'] ?? 0));
        $id = (int)$ruta['id_precio_pasaje'];

        $d = ValidarRutas::normalizar($_POST);
        $d['id_origen']    = (int)$ruta['id_sucursal_origen'];
        $d['id_destino']   = (int)$ruta['id_sucursal_destino'];
        $d['id_sindicato'] = (int)$ruta['id_sindicato'];
        $this->validarDatos($d);

        try {
            $this->rutasModel->actualizar($id, $ruta, $d);

            Flash::set(true, 'La ruta ' . $ruta['ciudad_origen'] . ' ➔ ' . $ruta['ciudad_destino'] . ' fue actualizada correctamente.', 'Ruta Actualizada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'actualizar');
        }
    }

    public function cambiarEstado() {
        $this->acceso(true);
        $this->soloPost();

        $estado = intval($_POST['estado'] ?? 0) === 1 ? 1 : 0;
        $ruta = $this->rutaModificable(intval($_POST['id_ruta'] ?? 0));
        $id = (int)$ruta['id_precio_pasaje'];

        try {
            if ($estado === 0 && $this->rutasModel->tieneTurnosAbiertos($ruta['id_sucursal_origen'], $ruta['id_sucursal_destino'], $ruta['id_sindicato'])) {
                $this->json(['success' => false, 'message' => 'No se puede desactivar: hay turnos abiertos en esta ruta. Despáchelos o cancélelos primero.']);
            }
            if (!$this->rutasModel->cambiarEstado($id, $ruta, $estado)) {
                $this->json(['success' => false, 'message' => 'No se pudo cambiar el estado (no hubo cambios).']);
            }

            Flash::set(true, $estado ? 'La ruta fue activada.' : 'La ruta fue desactivada.', 'Estado Actualizado');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'cambiarEstado');
        }
    }

    public function eliminar() {
        $this->acceso(true);
        $this->soloPost();

        $ruta = $this->rutaModificable(intval($_POST['id_ruta'] ?? 0));
        $id = (int)$ruta['id_precio_pasaje'];

        try {
            if ($this->rutasModel->tieneTurnosAbiertos($ruta['id_sucursal_origen'], $ruta['id_sucursal_destino'], $ruta['id_sindicato'])) {
                $this->json(['success' => false, 'message' => 'No se puede eliminar: hay turnos abiertos en esta ruta. Despáchelos o cancélelos primero.']);
            }
            if (!$this->rutasModel->eliminar($id, $ruta, date('Y-m-d H:i:s'))) {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar la ruta.']);
            }

            Flash::set(true, 'La ruta ' . $ruta['ciudad_origen'] . ' ➔ ' . $ruta['ciudad_destino'] . ' fue enviada a la papelera.', 'Ruta Eliminada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'eliminar');
        }
    }

    public function restaurar() {
        $this->acceso(true);
        $this->soloPost();

        $id = intval($_POST['id_ruta'] ?? 0);
        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id, true) : null;
        if (!$ruta || $ruta['delete_precio_pasaje'] === null) {
            $this->json(['success' => false, 'message' => 'La ruta no está en la papelera.']);
        }
        if (!$this->puedeModificar($ruta)) {
            $this->json(['success' => false, 'message' => 'Solo puede modificar las rutas de su propio sindicato.']);
        }

        try {
            if (!$this->rutasModel->restaurar($id, $ruta)) {
                $this->json(['success' => false, 'message' => 'No se pudo restaurar la ruta.']);
            }

            Flash::set(true, 'La ruta ' . $ruta['ciudad_origen'] . ' ➔ ' . $ruta['ciudad_destino'] . ' fue restaurada y quedó activa.', 'Ruta Restaurada');
            $this->json(['success' => true]);

        } catch (PDOException $e) {
            $this->errorBd($e, 'restaurar');
        }
    }

    /* ============================ PRIVADOS ============================ */

    private function idSindicato() { return (int)($_SESSION['id_sindicato'] ?? 0); }
    private function idSucursal()  { return (int)($_SESSION['id_sucursal'] ?? 0); }
    private function esPrincipal() { return (int)($_SESSION['es_principal'] ?? 0) === 1; }

    /** El principal modifica todas; los demás solo las de su sindicato. */
    private function puedeModificar(array $ruta) {
        return $this->esPrincipal() || (int)$ruta['id_sindicato'] === $this->idSindicato();
    }

    /** Ruta vigente que el usuario puede modificar; responde JSON y corta si no. */
    private function rutaModificable($id) {
        $ruta = $id > 0 ? $this->rutasModel->obtenerRuta($id) : null;
        if (!$ruta) {
            $this->json(['success' => false, 'message' => 'La ruta no existe o fue eliminada.']);
        }
        if (!$this->puedeModificar($ruta)) {
            $this->json(['success' => false, 'message' => 'Solo puede modificar las rutas de su propio sindicato.']);
        }
        return $ruta;
    }

    private function datosFormulario() {
        $miSucursal = $this->idSucursal();
        $origen = null;
        foreach ($this->rutasModel->getOrigenes($this->idSindicato()) as $s) {
            if ((int)$s['id_sucursal'] === $miSucursal) $origen = $s;
        }

        return [
            'origen'     => $origen,
            'sindicatos' => $this->rutasModel->getSindicatos($this->esPrincipal() ? 0 : $this->idSindicato()),
            'destinos'   => array_values(array_filter($this->rutasModel->getDestinos(), function ($s) use ($miSucursal) {
                return (int)$s['id_sucursal'] !== $miSucursal;
            })),
            'contenidos' => $this->rutasModel->getContenidosActivos(),
        ];
    }

    private function validarDatos(array $d) {
        $error = ValidarRutas::validar($d);
        if ($error !== null) {
            $this->json(['success' => false, 'message' => $error]);
        }
        $ids = array_values(array_unique(array_column($d['tarifas'], 'id_contenido')));
        if ($ids && $this->rutasModel->contenidosValidos($ids) !== count($ids)) {
            $this->json(['success' => false, 'message' => 'Algún tipo de contenido no existe o está inactivo. Recargue la página.']);
        }
    }

    private function acceso($json) {
        if (empty($_SESSION['id_usuario'])) {
            if ($json) {
                $this->json(['success' => false, 'message' => 'Su sesión expiró. Vuelva a iniciar sesión.']);
            }
            header('Location: ' . rtrim(URL, '/') . '/auth/auth_controller/index');
            exit;
        }
    }

    private function soloPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }
    }

    private function mensajeDuplicado(array $dup) {
        return 'Ya existe una ruta de ese sindicato entre esas dos sucursales.' . ($dup['eliminado'] ? ' Está en la papelera: restáurela desde allí.' : '');
    }

    private function errorBd(PDOException $e, $accion) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            $this->json(['success' => false, 'message' => 'Ya existe una ruta de ese sindicato entre esas dos sucursales.']);
        }
        error_log('Rutas ' . $accion . ': ' . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Error de base de datos. Intente nuevamente o revise el log de PHP.']);
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'rutas';
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