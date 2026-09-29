<?php
require_once __DIR__ . '/../../models/despachos/Despachos_model.php';

class Despachos_controller {
    private $despachosModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->despachosModel = new Despachos_model();
    }

    public function index() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'despachos';
        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;

        $despachos = $this->despachosModel->getDespachosPorSucursal($id_sucursal_actual);

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';
        
        if (file_exists($viewPath . 'despachos/index.php')) {
             require_once $viewPath . 'despachos/index.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    /**
     * Muestra la vista con el formulario para registrar un despacho/turno
     */
    public function new() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'despachos';
        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;

        $sucursalesDestino = $this->despachosModel->getSucursalesDestino($id_sucursal_actual);
        $vehiculosChoferes = $this->despachosModel->getVehiculosChoferes();

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';
        
        if (file_exists($viewPath . 'despachos/new.php')) {
             require_once $viewPath . 'despachos/new.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    /**
     * Procesa la inserción del nuevo despacho/turno.
     * Responde en JSON si la petición viene por AJAX (fetch con el header
     * X-Requested-With), listo para cuando el formulario viva en un modal;
     * si no, mantiene el flujo actual de redirect + Flash/SweetAlert.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            $id_sucursal_origen  = $_SESSION['id_sucursal'] ?? 1;
            $id_usuario          = $_SESSION['id_usuario'] ?? 1;

            $id_sucursal_destino = $_POST['id_sucursal_destino'] ?? '';
            $id_vehiculo_chofer  = $_POST['id_vehiculo_chofer'] ?? '';
            $precio_pasaje       = $_POST['precio_pasaje'] ?? '';

            // Solo se piden estos dos datos + el precio; la fecha la pone el
            // sistema y la hora no se define en este punto (ver más abajo).
            if (empty($id_sucursal_destino) || empty($id_vehiculo_chofer) || $precio_pasaje === '') {
                $this->responderStore($esAjax, false, 'Debe seleccionar la sucursal destino, el vehículo/chofer e indicar el precio del pasaje.', 'Datos Incompletos');
                return;
            }

            $data = [
                'id_sucursal_origen'  => $id_sucursal_origen,
                'id_sucursal_destino' => $id_sucursal_destino,
                'id_vehiculo_chofer'  => $id_vehiculo_chofer,
                // La fecha de salida siempre es la fecha actual del sistema.
                'fecha_salida'        => date('Y-m-d'),
                // La hora de salida NO se registra al crear el turno; se
                // registra al despachar (despacharTurno()).
                'hora_salida'         => null,
                'precio_pasaje'       => $precio_pasaje,
                'id_estado_turno'     => 1, // Estado inicial: En Turno
                'id_usuario'          => $id_usuario
            ];

            try {
                $res = $this->despachosModel->crearTurno($data);

                if ($res) {
                    $this->responderStore($esAjax, true, 'El turno fue programado correctamente.', 'Turno Registrado');
                } else {
                    $this->responderStore($esAjax, false, 'No se pudo registrar el turno. Intente nuevamente.', 'Error al Guardar');
                }
            } catch (Exception $e) {
                $this->responderStore($esAjax, false, 'Error en el servidor: ' . $e->getMessage(), 'Error al Guardar');
            }
        }
    }

    /* =====================================================================
     * ASIGNACIÓN Y DESPACHO DEL TURNO
     * Pasajes: solo lectura (se venden en pasajes/new).
     * Encomiendas: se asignan/quitan aquí. Despachar cierra todo.
     * ===================================================================== */

    /** Vista wizard: Turno -> Pasajes -> Encomiendas -> Manifiesto/Despachar. */
    public function asign() {
        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;
        $id_turno = intval($_GET['id'] ?? 0);

        $turno = $this->despachosModel->obtenerTurnoCompleto($id_turno);
        if (!$turno || intval($turno['id_sucursal_origen']) !== intval($id_sucursal_actual)) {
            $this->volverAlListado(false, 'El turno no existe o pertenece a otra sucursal.', 'Turno no válido');
        }
        if (!$this->despachosModel->esTurnoAbierto($turno['nombre_estado_turno'])) {
            $this->volverAlListado(false, 'Este turno ya no está "En Turno" (estado: ' . $turno['nombre_estado_turno'] . '), no se puede modificar.', 'Turno cerrado');
        }

        $pasajeros      = $this->despachosModel->getPasajerosPorTurno($id_turno);
        $encAsignadas   = $this->despachosModel->getEncomiendasAsignadas($id_turno);
        $encPendientes  = $this->despachosModel->getEncomiendasPendientes($turno['id_sucursal_origen'], $turno['id_sucursal_destino']);

        $resumen = [
            'pasajeros'        => count($pasajeros),
            'monto_pasajes'    => array_sum(array_column($pasajeros, 'precio_detalle_pasaje')),
            'guias'            => count($encAsignadas),
            'monto_encomiendas'=> array_sum(array_column($encAsignadas, 'monto_encomienda')),
            'capacidad'        => intval($turno['total_asientos_modelo'] ?? 0),
        ];
        $pasoInicial = min(4, max(1, intval($_GET['paso'] ?? 1)));

        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'despachos';

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . 'despachos/asign.php')) {
            require_once $viewPath . 'despachos/asign.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    /** AJAX POST: id_turno + ids_json (["12","15"]) -> vincula guías al turno. */
    public function asignarEncomiendas() {
        try {
            $turno = $this->turnoAbiertoPropioOFallar();
            $ids = json_decode($_POST['ids_json'] ?? '[]', true);
            if (!is_array($ids)) $ids = [];

            $n = $this->despachosModel->asignarEncomiendas(
                $turno['id_turno'], $turno['id_sucursal_origen'], $turno['id_sucursal_destino'], $ids
            );
            $this->json(['success' => true, 'message' => "$n guía(s) asignada(s) al turno."]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /** AJAX POST: id_turno + id_encomienda -> desvincula la guía. */
    public function quitarEncomienda() {
        try {
            $turno = $this->turnoAbiertoPropioOFallar();
            $id_enc = intval($_POST['id_encomienda'] ?? 0);
            if ($id_enc <= 0) throw new Exception('Guía inválida.');

            $this->despachosModel->quitarEncomienda($turno['id_turno'], $id_enc);
            $this->json(['success' => true, 'message' => 'La guía fue retirada del turno.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /** AJAX POST: id_turno -> despacha (hora, estados de turno/pasajes/encomiendas). */
    public function despachar() {
        try {
            $this->turnoAbiertoPropioOFallar(); // valida método, sucursal y estado
            $id_turno = intval($_POST['id_turno']);
            $id_sucursal = $_SESSION['id_sucursal'] ?? 1;

            $this->despachosModel->despacharTurno($id_turno, $id_sucursal);

            Flash::set(true, 'El turno #T-' . str_pad($id_turno, 3, '0', STR_PAD_LEFT) . ' fue despachado correctamente.', 'Turno Despachado');
            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* ------------------------------ helpers ------------------------------ */

    /** Valida POST + que el turno sea de la sucursal del usuario y siga abierto. */
    private function turnoAbiertoPropioOFallar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Método no permitido.');
        }
        $id_turno = intval($_POST['id_turno'] ?? 0);
        $turno = $this->despachosModel->obtenerTurnoCompleto($id_turno);
        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;

        if (!$turno || intval($turno['id_sucursal_origen']) !== intval($id_sucursal_actual)) {
            throw new Exception('El turno no existe o pertenece a otra sucursal.');
        }
        if (!$this->despachosModel->esTurnoAbierto($turno['nombre_estado_turno'])) {
            throw new Exception('El turno ya fue despachado o cancelado.');
        }
        return $turno;
    }

    private function json(array $payload) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    private function volverAlListado($success, $mensaje, $titulo) {
        Flash::set($success, $mensaje, $titulo);
        header('Location: ' . rtrim(URL, '/') . '/despachos');
        exit;
    }

    /**
     * Unifica la respuesta de store(): JSON limpio para AJAX (modal), o el
     * flujo clásico de Flash + redirect para un envío de formulario normal.
     */
    private function responderStore($esAjax, $success, $mensaje, $titulo) {
        if ($esAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => $success, 'message' => $mensaje]);
            exit;
        }

        Flash::set($success, $mensaje, $titulo);
        header('Location: ' . URL . ($success ? '/despachos' : '/despachos/new'));
        exit;
    }
}
?>