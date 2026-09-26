<?php
require_once __DIR__ . '/../../models/pasajes/Pasajes_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../models/despachos/Despachos_model.php';
require_once __DIR__ . '/../../models/elementos/Elementos_model.php';
require_once __DIR__ . '/../../models/metodos_pagos/Metodos_pagos_model.php';

class Pasajes_controller {
    private $pasajesModel;
    private $personasModel;
    private $despachosModel;
    private $elementosModel;
    private $metodosPagosModel;

    public function __construct() {
        $this->pasajesModel      = new Pasajes_model();
        $this->personasModel     = new Personas_model();
        $this->despachosModel    = new Despachos_model();
        $this->elementosModel    = new Elementos_model();
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

    /**
     * Formulario de venta. Trae los turnos "en turno" de la sucursal actual
     * (con su chofer y vehículo ya resueltos) para que el cajero elija uno;
     * si no elige ninguno, guardar() registra el pasaje "en espera".
     */
    public function new() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'pasajes';

        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;
        $turnos_disponibles = $this->despachosModel->getTurnosEnTurnoPorSucursal($id_sucursal_actual);
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

    /**
     * AJAX: dado un turno, responde con sus datos (vehículo, chofer, precio,
     * capacidad) y el mapa de asientos de su modelo, marcando cuáles ya están
     * vendidos EN ESE turno. Se llama al elegir un turno en el paso 1 y al
     * entrar al paso 2 del wizard (para refrescar la ocupación real).
     */
    public function obtenerConfiguracionTurno() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id_turno = intval($_GET['id_turno'] ?? 0);
        if (!$id_turno) {
            echo json_encode(['success' => false, 'message' => 'Turno no especificado']);
            exit;
        }

        $turno = $this->despachosModel->obtenerTurnoConVehiculo($id_turno);
        if (!$turno) {
            echo json_encode(['success' => false, 'message' => 'El turno seleccionado no existe o ya no está disponible']);
            exit;
        }

        $asientos = $this->elementosModel->getMapaAsientosPorModelo($turno['id_modelo'], $id_turno);

        echo json_encode([
            'success'  => true,
            'turno'    => $turno,
            'asientos' => $asientos
        ]);
        exit;
    }

    /**
     * Registra la venta. Dos modalidades según si llega id_turno o no:
     *   - CON turno: se exige al menos un asiento (asientos_json) y el precio
     *     se toma del turno (precio_pasaje_turno); el pasaje queda "Asignado".
     *   - SIN turno (en espera): se piden cantidad_pasajes y precio_manual;
     *     el pasaje queda "Pendiente" de que se le asigne chofer/vehículo.
     */
    public function guardar() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $id_usuario = $_SESSION['id_usuario'] ?? 1;
            $id_metodo_pago = intval($_POST['metodo_cobro'] ?? 1);

            // Comprador / pasajero
            $ciComprador = trim($_POST['comprador_ci'] ?? '');
            if (empty($ciComprador)) {
                echo json_encode(['success' => false, 'message' => 'El C.I. del comprador es obligatorio']);
                exit;
            }

            $personaComprador = $this->personasModel->buscarPorCi($ciComprador);
            if ($personaComprador) {
                $id_comprador = $personaComprador['id_persona'];
            } else {
                $nombresComprador = trim($_POST['comprador_nombres'] ?? '');
                $paternoComprador = trim($_POST['comprador_paterno'] ?? '');
                if (empty($nombresComprador) || empty($paternoComprador)) {
                    echo json_encode(['success' => false, 'message' => 'Nombres y apellido paterno del comprador son obligatorios']);
                    exit;
                }
                $id_comprador = $this->personasModel->insertarPersona(
                    $ciComprador,
                    $nombresComprador,
                    $paternoComprador,
                    trim($_POST['comprador_materno'] ?? ''),
                    trim($_POST['comprador_celular'] ?? ''),
                    trim($_POST['comprador_direccion'] ?? '')
                );
            }

            $id_turno = !empty($_POST['id_turno']) ? intval($_POST['id_turno']) : null;

            global $pdo;
            $pdo->beginTransaction();

            try {
                if ($id_turno) {
                    // --- Venta CON turno: uno o varios asientos concretos ---
                    $turnoInfo = $this->despachosModel->obtenerTurnoConVehiculo($id_turno);
                    if (!$turnoInfo) {
                        throw new Exception('El turno seleccionado ya no está disponible.');
                    }

                    $asientos = json_decode($_POST['asientos_json'] ?? '[]', true);
                    if (empty($asientos) || !is_array($asientos)) {
                        throw new Exception('Debe seleccionar al menos un asiento del turno.');
                    }

                    $precioUnitario = floatval($turnoInfo['precio_pasaje_turno']);
                    $totalPasaje    = $precioUnitario * count($asientos);
                    $idEstado       = $this->pasajesModel->obtenerIdEstadoAsignado();

                    $resPasaje = $this->pasajesModel->insertarPasaje([
                        'id_persona_comprador' => $id_comprador,
                        'id_usuario'           => $id_usuario,
                        'id_metodo_pago'       => $id_metodo_pago,
                        'total_pasaje'         => $totalPasaje
                    ]);
                    $idPasaje = $resPasaje['id_pasaje'];

                    foreach ($asientos as $idElemento) {
                        $this->pasajesModel->insertarDetallePasaje([
                            'id_pasaje'             => $idPasaje,
                            'id_turno'              => $id_turno,
                            'id_persona_pasajero'   => $id_comprador,
                            'id_elemento'           => intval($idElemento),
                            'precio_detalle_pasaje' => $precioUnitario,
                            'id_estado_pasaje'      => $idEstado
                        ]);
                    }
                } else {
                    // --- Venta SIN turno: queda "en espera" de asignación ---
                    $cantidad = max(1, intval($_POST['cantidad_pasajes'] ?? 1));
                    $precioUnitario = floatval($_POST['precio_manual'] ?? 0);
                    if ($precioUnitario <= 0) {
                        throw new Exception('Indique el precio del pasaje para la venta en espera.');
                    }

                    $totalPasaje = $precioUnitario * $cantidad;
                    $idEstado    = $this->pasajesModel->obtenerIdEstadoPendiente();

                    $resPasaje = $this->pasajesModel->insertarPasaje([
                        'id_persona_comprador' => $id_comprador,
                        'id_usuario'           => $id_usuario,
                        'id_metodo_pago'       => $id_metodo_pago,
                        'total_pasaje'         => $totalPasaje
                    ]);
                    $idPasaje = $resPasaje['id_pasaje'];

                    for ($i = 0; $i < $cantidad; $i++) {
                        $this->pasajesModel->insertarDetallePasaje([
                            'id_pasaje'             => $idPasaje,
                            'id_turno'              => null,
                            'id_persona_pasajero'   => $id_comprador,
                            'id_elemento'           => null,
                            'precio_detalle_pasaje' => $precioUnitario,
                            'id_estado_pasaje'      => $idEstado
                        ]);
                    }
                }

                $pdo->commit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                // Choque contra la UNIQUE (id_turno, id_elemento): alguien más
                // vendió ese asiento entre que se cargó la tabla y se guardó.
                if ((int) $e->getCode() === 23000 || strpos($e->getMessage(), '1062') !== false) {
                    throw new Exception('Uno de los asientos seleccionados ya fue vendido. Actualice la lista e intente nuevamente.');
                }
                throw $e;
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            Flash::set(
                true,
                "El pasaje #{$resPasaje['codigo']} fue emitido y registrado correctamente.",
                'Boleto Emitido'
            );

            echo json_encode([
                'success'   => true,
                'codigo'    => $resPasaje['codigo'],
                'id_pasaje' => $idPasaje
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Imprime el boleto completo (todos los asientos de una misma venta).
     * $id es pasajes.id_pasaje (la cabecera), no un detalle individual.
     */
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

    /**
     * Detalle en JSON (cabecera + todos los asientos) para el modal de
     * "Ver Detalle". $id es pasajes.id_pasaje.
     */
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

    /**
     * Anula un asiento/boleto puntual (id_detalle_pasaje), sin tocar los
     * demás asientos de la misma venta.
     */
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