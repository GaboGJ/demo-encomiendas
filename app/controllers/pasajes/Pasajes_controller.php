<?php
require_once __DIR__ . '/../../models/pasajes/Pasajes_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../models/sucursales/Sucursales_model.php';
require_once __DIR__ . '/../../models/metodos_pagos/Metodos_pagos_model.php';

class Pasajes_controller {
    private $pasajesModel;
    private $personasModel;
    private $sucursalesModel;
    private $metodosPagosModel;

    public function __construct() {
        $this->pasajesModel      = new Pasajes_model();
        $this->personasModel     = new Personas_model();
        $this->sucursalesModel   = new Sucursales_model();
        $this->metodosPagosModel = new Metodos_pagos_model();
    }

    public function index() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'pasajes';
        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;

        $pasajes = $this->pasajesModel->getPasajesPorSucursal($id_sucursal_actual);

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';
        
        if (file_exists($viewPath . 'pasajes/index.php')) {
            require_once $viewPath . 'pasajes/index.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    public function new() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'pasajes';

        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;
        $sucursales_destino = $this->sucursalesModel->getSucursalesDestino($id_sucursal_actual);
        $metodos_pago       = $this->metodosPagosModel->getMetodosPagosActivos();

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . 'pasajes/new.php')) {
            require_once $viewPath . 'pasajes/new.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    public function guardar() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $id_usuario = $_SESSION['id_usuario'] ?? 1;

            $ciPasajero = trim($_POST['pasajero_ci'] ?? '');
            if (empty($ciPasajero)) {
                echo json_encode(['success' => false, 'message' => 'El C.I. del pasajero es obligatorio']);
                exit;
            }

            // Buscar o registrar a la persona/pasajero
            $personaPasajero = $this->personasModel->buscarPorCi($ciPasajero);
            if ($personaPasajero) {
                $id_pasajero = $personaPasajero['id_persona'];
            } else {
                $id_pasajero = $this->personasModel->insertarPersona(
                    $ciPasajero,
                    trim($_POST['pasajero_nombres'] ?? ''),
                    trim($_POST['pasajero_paterno'] ?? ''),
                    trim($_POST['pasajero_materno'] ?? ''),
                    trim($_POST['pasajero_celular'] ?? ''),
                    trim($_POST['pasajero_direccion'] ?? '')
                );
            }

            $dataPasaje = [
                'id_usuario'         => $id_usuario,
                'monto_total_pasaje' => floatval($_POST['monto_total'] ?? 0),
                'id_metodo_pago'     => intval($_POST['metodo_cobro'] ?? 1)
            ];

            global $pdo;
            $pdo->beginTransaction();

            try {
                $resPasaje = $this->pasajesModel->insertarPasaje($dataPasaje);
                $idPasaje  = $resPasaje['id_pasaje'];
                $codigo    = $resPasaje['codigo'];

                $dataDetalle = [
                    'id_pasaje'           => $idPasaje,
                    'id_turno'            => intval($_POST['id_turno'] ?? 0),
                    'id_persona_pasajero' => $id_pasajero,
                    'id_elemento'         => intval($_POST['id_elemento'] ?? 0),
                    'precio_detalle_pasaje' => floatval($_POST['precio'] ?? 0),
                    'id_estado_pasaje'    => 1
                ];

                $this->pasajesModel->insertarDetallePasaje($dataDetalle);

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            Flash::set(
                true,
                "El pasaje #{$codigo} fue emitido y registrado correctamente.",
                'Boleto Emitido'
            );

            echo json_encode([
                'success'   => true, 
                'codigo'    => $codigo, 
                'id_pasaje' => $idPasaje
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
            exit;
        }
    }

    public function imprimir() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            die("Error: Identificador de pasaje no válido.");
        }

        $pasaje = $this->pasajesModel->obtenerPasajeCompleto($id);

        if (!$pasaje) {
            die("Error: El pasaje solicitado no existe.");
        }

        require_once __DIR__ . '/../../views/dashboard/pasajes/print.php';
        exit;
    }

    public function detalle() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador de pasaje no válido.']);
            exit;
        }

        $pasaje = $this->pasajesModel->obtenerPasajeCompleto($id);

        if (!$pasaje) {
            echo json_encode(['success' => false, 'message' => 'El pasaje solicitado no existe.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $pasaje]);
        exit;
    }

    public function anular() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Identificador inválido.']);
                exit;
            }

            if ($this->pasajesModel->anularPasaje($id)) {
                Flash::set(true, 'El pasaje ha sido anulado correctamente.', 'Anulación Exitosa');
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo anular el pasaje.']);
            }
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
            exit;
        }
    }
}
?>