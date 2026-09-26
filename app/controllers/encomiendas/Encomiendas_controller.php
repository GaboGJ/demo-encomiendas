<?php
require_once __DIR__ . '/../../models/encomiendas/Encomiendas_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../models/sucursales/Sucursales_model.php';
require_once __DIR__ . '/../../models/metodos_pagos/Metodos_pagos_model.php';
// Flash ya está disponible globalmente: se incluye una sola vez desde config/config.php

class Encomiendas_controller {
    private $encomiendasModel;
    private $personasModel;
    private $sucursalesModel;
    private $metodosPagosModel;

    public function __construct() {
        $this->encomiendasModel = new Encomiendas_model();
        $this->personasModel    = new Personas_model();
        $this->sucursalesModel  = new Sucursales_model();
        $this->metodosPagosModel = new Metodos_pagos_model();
    }

    public function index() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'encomiendas';
        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;

        $envios   = $this->encomiendasModel->getEnviosPorSucursal($id_sucursal_actual);
        $llegadas = $this->encomiendasModel->getLlegadasPorSucursal($id_sucursal_actual);

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';
        
        if (file_exists($viewPath . 'encomiendas/index.php')) {
            require_once $viewPath . 'encomiendas/index.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    public function new() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'encomiendas';

        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;
        $sucursales_destino = $this->sucursalesModel->getSucursalesDestino($id_sucursal_actual);
        $metodos_pago      = $this->metodosPagosModel->getMetodosPagosActivos();

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . 'encomiendas/new.php')) {
            require_once $viewPath . 'encomiendas/new.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    public function obtenerContenidosPorDestino() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id_origen  = $_SESSION['id_sucursal'] ?? 1;
        $id_destino = intval($_GET['id_destino'] ?? 0);

        if (!$id_destino) {
            echo json_encode(['success' => false, 'message' => 'Destino no especificado']);
            exit;
        }

        $contenidos = $this->encomiendasModel->getContenidosPorRuta($id_origen, $id_destino);
        echo json_encode(['success' => true, 'data' => $contenidos]);
        exit;
    }

    /**
     * Lista el catálogo de tipos de contenido para clasificar los bultos que llegan
     * en la RECEPCIÓN. A diferencia de "obtenerContenidosPorDestino" (que usa "new"),
     * aquí NO se filtra por ruta/tarifa: la carga llega desde una sucursal externa
     * que no maneja este sistema, así que nunca existirá una fila en
     * tarifas_encomiendas para esa ruta. Solo se necesita el nombre del tipo de
     * contenido para describir el bulto, no un precio.
     */
    public function obtenerContenidosPorOrigen() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id_origen = intval($_GET['id_origen'] ?? 0);
        if (!$id_origen) {
            echo json_encode(['success' => false, 'message' => 'Origen no especificado']);
            exit;
        }

        $contenidos = $this->encomiendasModel->getContenidosActivos();
        echo json_encode(['success' => true, 'data' => $contenidos]);
        exit;
    }

    public function obtenerTarifa() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id_origen    = $_SESSION['id_sucursal'] ?? 1;
        $id_destino   = intval($_GET['id_destino'] ?? 0);
        $id_contenido = intval($_GET['id_contenido'] ?? 0);

        if (!$id_destino || !$id_contenido) {
            echo json_encode(['success' => false, 'message' => 'Parámetros insuficientes']);
            exit;
        }

        $precio = $this->encomiendasModel->obtenerTarifa($id_origen, $id_destino, $id_contenido);

        if ($precio !== null) {
            echo json_encode(['success' => true, 'precio' => $precio]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No existe una tarifa configurada para esta ruta y contenido']);
        }
        exit;
    }

    public function guardar() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $id_sucursal_origen = $_SESSION['id_sucursal'] ?? 1;
            $id_usuario         = $_SESSION['id_usuario'] ?? 1;

            $ciRemitente = trim($_POST['remitente_ci'] ?? '');
            if (empty($ciRemitente)) {
                echo json_encode(['success' => false, 'message' => 'El C.I. del remitente es obligatorio']);
                exit;
            }

            $personaRemitente = $this->personasModel->buscarPorCi($ciRemitente);
            if ($personaRemitente) {
                $id_remitente = $personaRemitente['id_persona'];
            } else {
                $id_remitente = $this->personasModel->insertarPersona(
                    $ciRemitente,
                    trim($_POST['remitente_nombres'] ?? ''),
                    trim($_POST['remitente_paterno'] ?? ''),
                    trim($_POST['remitente_materno'] ?? ''),
                    trim($_POST['remitente_celular'] ?? ''),
                    trim($_POST['remitente_direccion'] ?? '')
                );
            }

            $ciDestinatario = trim($_POST['destinatario_ci'] ?? '');
            if (empty($ciDestinatario)) {
                echo json_encode(['success' => false, 'message' => 'El C.I. del destinatario es obligatorio']);
                exit;
            }

            $personaDestinatario = $this->personasModel->buscarPorCi($ciDestinatario);
            if ($personaDestinatario) {
                $id_destinatario = $personaDestinatario['id_persona'];
            } else {
                $id_destinatario = $this->personasModel->insertarPersona(
                    $ciDestinatario,
                    trim($_POST['destinatario_nombres'] ?? ''),
                    trim($_POST['destinatario_paterno'] ?? ''),
                    trim($_POST['destinatario_materno'] ?? ''),
                    trim($_POST['destinatario_celular'] ?? ''),
                    trim($_POST['destinatario_direccion'] ?? '')
                );
            }

            $dataEncomienda = [
                'id_sucursal_origen'     => $id_sucursal_origen,
                'id_sucursal_destino'    => $_POST['id_sucursal_destino'] ?? null,
                'id_persona_remitente'   => $id_remitente,
                'id_persona_destinatario'=> $id_destinatario,
                'id_turno'               => !empty($_POST['id_turno']) ? $_POST['id_turno'] : null,
                'declaracion_encomienda' => trim($_POST['contenido'] ?? ''),
                'monto_encomienda'       => floatval($_POST['monto_total'] ?? 0),
                'estado_pago_encomienda' => isset($_POST['modalidad_pago']) ? intval($_POST['modalidad_pago']) : 1,
                'id_metodo_pago'         => intval($_POST['metodo_cobro'] ?? 1),
                'id_usuario'             => $id_usuario
            ];

            $resEncomienda = $this->encomiendasModel->insertarEncomienda($dataEncomienda);
            $idEncomienda  = $resEncomienda['id_encomienda'];
            $guiaEncomienda = $resEncomienda['guia'];

            $bultos = json_decode($_POST['bultos_json'] ?? '[]', true);
            if (!empty($bultos) && is_array($bultos)) {
                foreach ($bultos as $index => $bulto) {
                    $numeroBulto = $index + 1;
                    $this->encomiendasModel->insertarDetalleEncomienda($idEncomienda, $guiaEncomienda, $numeroBulto, $bulto);
                }
            }

            // Mensaje que se mostrará automáticamente en la PRÓXIMA vista que cargue
            // (el listado, a donde el JS redirige después de imprimir la guía).
            Flash::set(
                true,
                "La guía #{$guiaEncomienda} fue generada y registrada correctamente.",
                'Guía Emitida'
            );

            echo json_encode([
                'success'       => true, 
                'guia'          => $guiaEncomienda, 
                'id_encomienda' => $idEncomienda
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
            exit;
        }
    }

    public function imprimir() {
        // Tomar el ID directamente del parámetro query (?id=X)
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            die("Error: Identificador de encomienda no válido.");
        }

        // Obtener los datos desde el modelo
        $encomienda = $this->encomiendasModel->obtenerEncomiendaCompleta($id);

        if (!$encomienda) {
            die("Error: La encomienda solicitada no existe.{$id}");
        }

        // Cargar únicamente la plantilla de la impresión
        require_once __DIR__ . '/../../views/dashboard/encomiendas/print.php';
        exit; // Detener ejecución para no cargar layouts o dashboards extra
    }

    public function detalle() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador de encomienda no válido.']);
            exit;
        }

        $encomienda = $this->encomiendasModel->obtenerEncomiendaCompleta($id);

        if (!$encomienda) {
            echo json_encode(['success' => false, 'message' => 'La encomienda solicitada no existe.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $encomienda]);
        exit;
    }

    public function reception() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'encomiendas';

        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1; // Sucursal Destino (Local)
        
        // Obtener sucursales excepto la actual para elegirlas como Origen
        $sucursales_origen = $this->sucursalesModel->getSucursalesDestino($id_sucursal_actual); 
        $metodos_pago      = $this->metodosPagosModel->getMetodosPagosActivos();

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . 'encomiendas/reception.php')) {
            require_once $viewPath . 'encomiendas/reception.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    public function guardarRecepcion() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $id_sucursal_destino = $_SESSION['id_sucursal'] ?? 1; // Destino es la sucursal actual
            $id_usuario          = $_SESSION['id_usuario'] ?? 1;

            $guiaManual = trim($_POST['guia_encomienda'] ?? '');
            if (empty($guiaManual)) {
                echo json_encode(['success' => false, 'message' => 'El código de guía origen es obligatorio']);
                exit;
            }

            // Remitente (Origen)
            $ciRemitente = trim($_POST['remitente_ci'] ?? '');
            if (empty($ciRemitente)) {
                echo json_encode(['success' => false, 'message' => 'El C.I. del remitente es obligatorio']);
                exit;
            }

            $personaRemitente = $this->personasModel->buscarPorCi($ciRemitente);
            if ($personaRemitente) {
                $id_remitente = $personaRemitente['id_persona'];
            } else {
                $id_remitente = $this->personasModel->insertarPersona(
                    $ciRemitente,
                    trim($_POST['remitente_nombres'] ?? ''),
                    trim($_POST['remitente_paterno'] ?? ''),
                    trim($_POST['remitente_materno'] ?? ''),
                    trim($_POST['remitente_celular'] ?? ''),
                    trim($_POST['remitente_direccion'] ?? '')
                );
            }

            // Destinatario (Local)
            $ciDestinatario = trim($_POST['destinatario_ci'] ?? '');
            if (empty($ciDestinatario)) {
                echo json_encode(['success' => false, 'message' => 'El C.I. del destinatario es obligatorio']);
                exit;
            }

            $personaDestinatario = $this->personasModel->buscarPorCi($ciDestinatario);
            if ($personaDestinatario) {
                $id_destinatario = $personaDestinatario['id_persona'];
            } else {
                $id_destinatario = $this->personasModel->insertarPersona(
                    $ciDestinatario,
                    trim($_POST['destinatario_nombres'] ?? ''),
                    trim($_POST['destinatario_paterno'] ?? ''),
                    trim($_POST['destinatario_materno'] ?? ''),
                    trim($_POST['destinatario_celular'] ?? ''),
                    trim($_POST['destinatario_direccion'] ?? '')
                );
            }

            // Evaluación del cobro y estado de pago
            $estadoPago = isset($_POST['estado_pago_encomienda']) ? intval($_POST['estado_pago_encomienda']) : 1; // 1 = Pagado, 0 = Pendiente/COD
            $idMetodoPago = intval($_POST['id_metodo_pago'] ?? 1);

            $dataEncomienda = [
                'guia_encomienda'        => $guiaManual,
                'id_sucursal_origen'     => intval($_POST['id_sucursal_origen'] ?? 0),
                'id_sucursal_destino'    => $id_sucursal_destino,
                'id_persona_remitente'   => $id_remitente,
                'id_persona_destinatario'=> $id_destinatario,
                'id_turno'               => !empty($_POST['id_turno']) ? $_POST['id_turno'] : null,
                'declaracion_encomienda' => trim($_POST['declaracion_encomienda'] ?? ''),
                'monto_encomienda'       => floatval($_POST['monto_encomienda'] ?? 0),
                'estado_pago_encomienda' => $estadoPago,
                'id_metodo_pago'         => $idMetodoPago,
                'id_usuario'             => $id_usuario
            ];

            // La recepción no maneja tarifa (la sucursal externa no usa este sistema,
            // así que no existe un precio local para esa ruta). Solo se exige el tipo
            // de contenido para clasificar el bulto; detalles_encomiendas.id_encomienda_contenido
            // es NOT NULL en la BD, así que se valida antes de tocarla. El subtotal por
            // bulto no aplica aquí (se fuerza a 0 más abajo): el monto a cobrar real
            // ya se registra a nivel de la encomienda (monto_encomienda).
            $bultos = json_decode($_POST['bultos_json'] ?? '[]', true);
            if (empty($bultos) || !is_array($bultos)) {
                echo json_encode(['success' => false, 'message' => 'Debe registrar al menos un bulto']);
                exit;
            }
            foreach ($bultos as $bulto) {
                if (empty($bulto['id_contenido'])) {
                    echo json_encode(['success' => false, 'message' => 'Cada bulto debe tener un tipo de contenido seleccionado']);
                    exit;
                }
            }

            global $pdo;
            $pdo->beginTransaction();

            try {
                $resEncomienda = $this->encomiendasModel->insertarRecepcionEncomienda($dataEncomienda);
                $idEncomienda  = $resEncomienda['id_encomienda'];

                foreach ($bultos as $index => $bulto) {
                    $numeroBulto = $index + 1;
                    $bulto['subtotal'] = 0; // No aplica tarifa local en recepción; se fuerza en servidor.
                    $this->encomiendasModel->insertarDetalleEncomienda($idEncomienda, $guiaManual, $numeroBulto, $bulto);
                }

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            Flash::set(
                true,
                "La encomienda con guía #{$guiaManual} ha sido recibida e ingresada al almacén local correctamente.",
                'Recepción Confirmada'
            );

            echo json_encode([
                'success'       => true, 
                'guia'          => $guiaManual, 
                'id_encomienda' => $idEncomienda
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
            exit;
        }
    }

    public function delivery() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'encomiendas';

        $id_encomienda = intval($_GET['id'] ?? 0);
        if ($id_encomienda <= 0) {
            Flash::set(false, 'Identificador de encomienda inválido.', 'Error');
            header('Location: ' . BASE_URL . 'encomiendas');
            exit;
        }

        $encomienda = $this->encomiendasModel->obtenerEncomiendaCompleta($id_encomienda);
        if (!$encomienda) {
            Flash::set(false, 'La encomienda no fue encontrada.', 'Error');
            header('Location: ' . BASE_URL . 'encomiendas');
            exit;
        }

        $metodos_pago = $this->metodosPagosModel->getMetodosPagosActivos();

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . 'encomiendas/delivery.php')) {
            require_once $viewPath . 'encomiendas/delivery.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    public function guardarEntrega() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $id_encomienda = intval($_POST['id_encomienda'] ?? 0);
            $ciReceptor    = trim($_POST['receptor_ci'] ?? '');
            $montoEntrega  = floatval($_POST['monto_entrega'] ?? 0);
            $idMetodoPago  = intval($_POST['id_metodo_pago'] ?? 1);
            $id_usuario    = $_SESSION['id_usuario'] ?? 1;

            if ($id_encomienda <= 0 || empty($ciReceptor)) {
                echo json_encode(['success' => false, 'message' => 'Datos insuficientes para registrar la entrega.']);
                exit;
            }

            // Buscar o registrar a la persona que retira la encomienda
            $personaReceptor = $this->personasModel->buscarPorCi($ciReceptor);
            if ($personaReceptor) {
                $id_persona_retiro = $personaReceptor['id_persona'];
            } else {
                // Persona nueva (alguien distinto al destinatario original retira la
                // encomienda): apellido_paterno_persona es NOT NULL en el modelo de BD,
                // así que se exige aquí para no insertar personas con datos incompletos.
                $receptorPaterno = trim($_POST['receptor_paterno'] ?? '');
                if (empty($receptorPaterno)) {
                    echo json_encode(['success' => false, 'message' => 'Debe indicar el apellido paterno de quien retira (persona no registrada previamente).']);
                    exit;
                }

                $id_persona_retiro = $this->personasModel->insertarPersona(
                    $ciReceptor,
                    trim($_POST['receptor_nombres'] ?? ''),
                    $receptorPaterno,
                    trim($_POST['receptor_materno'] ?? ''),
                    trim($_POST['receptor_celular'] ?? ''),
                    ''
                );
            }

            $dataEntrega = [
                'id_encomienda'             => $id_encomienda,
                'id_persona_retiro'         => $id_persona_retiro,
                'id_usuario'                => $id_usuario,
                'monto_entrega_encomienda'  => $montoEntrega,
                'id_metodo_pago'            => $idMetodoPago,
                'observacion_entrega_encomienda' => trim($_POST['observacion'] ?? 'Entrega efectuada en sucursal destino')
            ];

            global $pdo;
            $pdo->beginTransaction();

            try {
                // 1. Insertar registro en entregas_encomiendas
                $this->encomiendasModel->registrarEntrega($dataEntrega);

                // 2. Actualizar estado operativo a "Entregado" y liquidar el pago: al
                // completarse la entrega el saldo siempre queda cobrado (ya sea porque
                // se pagó en origen, o porque se acaba de cobrar el COD en destino).
                $this->encomiendasModel->marcarComoEntregada($id_encomienda);

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            Flash::set(true, 'La encomienda ha sido entregada y finalizada correctamente.', 'Entrega Exitosa');
            echo json_encode(['success' => true, 'id_encomienda' => $id_encomienda]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Acta de entrega imprimible. delivery.php ya llamaba a
     * "encomiendas/imprimirActa?id=..." tras guardar la entrega, pero este método
     * no existía en el controlador (por eso la ventana de impresión no abría nada
     * válido). Sigue el mismo patrón que imprimir() para la guía de salida.
     */
    public function imprimirActa() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            die("Error: Identificador de encomienda no válido.");
        }

        $encomienda = $this->encomiendasModel->obtenerEncomiendaCompleta($id);
        if (!$encomienda) {
            die("Error: La encomienda solicitada no existe.");
        }

        $entrega = $this->encomiendasModel->obtenerUltimaEntrega($id);
        if (!$entrega) {
            die("Error: Esta encomienda todavía no tiene una entrega registrada.");
        }

        require_once __DIR__ . '/../../views/dashboard/encomiendas/print_acta.php';
        exit;
    }
}
?>