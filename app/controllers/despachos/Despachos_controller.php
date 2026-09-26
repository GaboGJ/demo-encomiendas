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
                // registrará recién cuando el turno sea despachado.
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