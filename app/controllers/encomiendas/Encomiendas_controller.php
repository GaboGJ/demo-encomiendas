<?php
require_once __DIR__ . '/../../models/encomiendas/Encomiendas_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../models/sucursales/Sucursales_model.php';
require_once __DIR__ . '/../../models/metodos_pagos/Metodos_pagos_model.php';

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
                'estado_pago_encomienda' => intval($_POST['modalidad_pago'] ?? 1),
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
}
?>