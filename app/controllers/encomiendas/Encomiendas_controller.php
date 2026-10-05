<?php
require_once __DIR__ . '/../../models/encomiendas/Encomiendas_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../models/sucursales/Sucursales_model.php';
require_once __DIR__ . '/../../models/metodos_pagos/Metodos_pagos_model.php';
require_once __DIR__ . '/../../models/cajas/Cajas_model.php';

class Encomiendas_controller {
    const MAX_MONTO = 99999999.99;

    private $encomiendasModel;
    private $personasModel;
    private $sucursalesModel;
    private $metodosPagosModel;
    private $cajasModel;

    public function __construct() {
        $this->encomiendasModel  = new Encomiendas_model();
        $this->personasModel     = new Personas_model();
        $this->sucursalesModel   = new Sucursales_model();
        $this->metodosPagosModel = new Metodos_pagos_model();
        $this->cajasModel        = new Cajas_model();
    }

    private function idSindicato() {
        return (int)($_SESSION['id_sindicato'] ?? 0);
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

        $contenidos = $this->encomiendasModel->getContenidosPorRuta($id_origen, $id_destino, $this->idSindicato());
        echo json_encode(['success' => true, 'data' => $contenidos]);
        exit;
    }

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
        $peso         = floatval($_GET['peso'] ?? 0);

        if (!$id_destino || !$id_contenido) {
            echo json_encode(['success' => false, 'message' => 'Parámetros insuficientes']);
            exit;
        }

        $precio = $this->encomiendasModel->obtenerTarifa($id_origen, $id_destino, $id_contenido, $peso, $this->idSindicato());

        if ($precio !== null) {
            echo json_encode(['success' => true, 'precio' => $precio]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No existe una tarifa configurada para esta ruta y contenido']);
        }
        exit;
    }

    /** Busca la persona por C.I. o la registra (nombres y apellido paterno obligatorios si es nueva). */
    private function resolverPersona($prefijo, $etiqueta) {
        $ci = trim($_POST[$prefijo . '_ci'] ?? '');
        if ($ci === '') {
            throw new InvalidArgumentException("El C.I. del {$etiqueta} es obligatorio");
        }

        $persona = $this->personasModel->buscarPorCi($ci);
        if ($persona) {
            return $persona['id_persona'];
        }

        $nombres = trim($_POST[$prefijo . '_nombres'] ?? '');
        $paterno = trim($_POST[$prefijo . '_paterno'] ?? '');
        if ($nombres === '' || $paterno === '') {
            throw new InvalidArgumentException("Nombres y apellido paterno del {$etiqueta} son obligatorios");
        }

        return $this->personasModel->insertarPersona(
            $ci,
            $nombres,
            $paterno,
            trim($_POST[$prefijo . '_materno'] ?? ''),
            trim($_POST[$prefijo . '_celular'] ?? ''),
            trim($_POST[$prefijo . '_direccion'] ?? '')
        );
    }

    /** Número >= 0 con 2 decimales, o null si es inválido. */
    private function montoValido($v) {
        $v = str_replace(',', '.', trim((string)$v));
        if ($v === '') return 0.0;
        if (!is_numeric($v)) return null;
        $n = round((float)$v, 2);
        return ($n >= 0 && $n <= self::MAX_MONTO) ? $n : null;
    }

    /**
     * Emite la guía. Los precios se calculan en el SERVIDOR: el subtotal de cada
     * bulto sale de tarifas_encomiendas y el total es subtotal + seguro - descuento.
     * Lo que envía el navegador (monto_total, subtotal) se ignora.
     */
    public function guardar() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        global $pdo;

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $id_sucursal_origen = (int)($_SESSION['id_sucursal'] ?? 0);
            $id_usuario         = (int)($_SESSION['id_usuario'] ?? 0);
            $id_sindicato       = $this->idSindicato();

            $id_historial_caja = $this->cajasModel->idHistorialAbiertoDeUsuario($id_usuario);
            if ($id_historial_caja <= 0) {
                throw new InvalidArgumentException('Debe aperturar una caja antes de emitir guías (módulo Control de Cajas).');
            }

            // Destino válido (activo y distinto del origen)
            $id_destino = intval($_POST['id_sucursal_destino'] ?? 0);
            $validos = array_map('intval', array_column($this->sucursalesModel->getSucursalesDestino($id_sucursal_origen), 'id_sucursal'));
            if ($id_destino <= 0 || $id_destino === $id_sucursal_origen || !in_array($id_destino, $validos, true)) {
                throw new InvalidArgumentException('La agencia de destino no es válida.');
            }

            $declaracion = trim($_POST['contenido'] ?? '');
            if ($declaracion === '') {
                throw new InvalidArgumentException('Indique la declaración del contenido.');
            }

            // Modalidad y método de cobro
            $estadoPago = (($_POST['modalidad_pago'] ?? '1') === '0') ? 0 : 1;
            $idMetodo   = intval($_POST['metodo_cobro'] ?? 0);
            $metodosOk  = array_map('intval', array_column($this->metodosPagosModel->getMetodosPagosActivos(), 'id_metodo_pago'));
            if (!in_array($idMetodo, $metodosOk, true)) {
                throw new InvalidArgumentException('El método de cobro no es válido.');
            }

            // Bultos: el subtotal sale de la tarifa en base de datos
            $bultos = json_decode($_POST['bultos_json'] ?? '[]', true);
            if (empty($bultos) || !is_array($bultos)) {
                throw new InvalidArgumentException('Debe registrar al menos un bulto.');
            }

            $subtotal = 0.0;
            foreach ($bultos as $i => &$b) {
                $n = $i + 1;
                $b['descripcion']  = trim((string)($b['descripcion'] ?? ''));
                $b['id_contenido'] = intval($b['id_contenido'] ?? 0);
                $peso = floatval($b['peso'] ?? 0);
                if ($b['descripcion'] === '' || mb_strlen($b['descripcion'], 'UTF-8') > 50) {
                    throw new InvalidArgumentException("Bulto #{$n}: la descripción es obligatoria (máximo 50 caracteres).");
                }
                if ($b['id_contenido'] <= 0) {
                    throw new InvalidArgumentException("Bulto #{$n}: seleccione el tipo de contenido.");
                }
                if ($peso < 0 || $peso > self::MAX_MONTO) {
                    throw new InvalidArgumentException("Bulto #{$n}: el peso no es válido.");
                }
                $b['peso'] = $peso;

                $precio = $this->encomiendasModel->obtenerTarifa($id_sucursal_origen, $id_destino, $b['id_contenido'], $peso, $id_sindicato);
                if ($precio === null) {
                    throw new InvalidArgumentException("Bulto #{$n}: no existe una tarifa para esa ruta, contenido" . ($peso > 0 ? ' y peso.' : '.'));
                }
                $b['subtotal'] = round($precio, 2);
                $subtotal += $b['subtotal'];
            }
            unset($b);
            $subtotal = round($subtotal, 2);

            // Seguro y descuento: validados y acotados
            $seguro    = $this->montoValido($_POST['monto_seguro'] ?? '');
            $descuento = $this->montoValido($_POST['monto_descuento'] ?? '');
            if ($seguro === null || $descuento === null) {
                throw new InvalidArgumentException('El seguro y el descuento deben ser números mayores o iguales a 0.');
            }
            if ($descuento > $subtotal) {
                throw new InvalidArgumentException('El descuento no puede ser mayor al subtotal de los bultos (Bs. ' . number_format($subtotal, 2) . ').');
            }
            $montoTotal = round($subtotal + $seguro - $descuento, 2);

            $pdo->beginTransaction();

            $id_remitente    = $this->resolverPersona('remitente', 'remitente');
            $id_destinatario = $this->resolverPersona('destinatario', 'destinatario');

            $resEncomienda = $this->encomiendasModel->insertarEncomienda([
                'id_sucursal_origen'      => $id_sucursal_origen,
                'id_sucursal_destino'     => $id_destino,
                'id_persona_remitente'    => $id_remitente,
                'id_persona_destinatario' => $id_destinatario,
                'id_turno'                => null,
                'declaracion_encomienda'  => $declaracion,
                'monto_encomienda'        => $montoTotal,
                'estado_pago_encomienda'  => $estadoPago,
                'id_metodo_pago'          => $idMetodo,
                'id_historial_caja'       => $id_historial_caja,
                'id_usuario'              => $id_usuario
            ]);
            $idEncomienda   = $resEncomienda['id_encomienda'];
            $guiaEncomienda = $resEncomienda['guia'];

            foreach ($bultos as $index => $bulto) {
                $this->encomiendasModel->insertarDetalleEncomienda($idEncomienda, $guiaEncomienda, $index + 1, $bulto);
            }

            $pdo->commit();

            Flash::set(true, "La guía #{$guiaEncomienda} fue generada y registrada correctamente.", 'Guía Emitida');

            echo json_encode([
                'success'       => true,
                'guia'          => $guiaEncomienda,
                'id_encomienda' => $idEncomienda,
                'monto_total'   => $montoTotal
            ]);
            exit;

        } catch (InvalidArgumentException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Encomiendas guardar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error en el servidor al guardar la encomienda.']);
            exit;
        }
    }

    public function imprimir() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            die("Error: Identificador de encomienda no válido.");
        }

        $encomienda = $this->encomiendasModel->obtenerEncomiendaCompleta($id);

        if (!$encomienda) {
            die("Error: La encomienda solicitada no existe.{$id}");
        }

        require_once __DIR__ . '/../../views/dashboard/encomiendas/print.php';
        exit;
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

        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;
        
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

            $id_sucursal_destino = $_SESSION['id_sucursal'] ?? 1;
            $id_usuario          = $_SESSION['id_usuario'] ?? 1;
            $id_historial_caja = $this->cajasModel->idHistorialAbiertoDeUsuario($id_usuario) ?: null;

            $guiaManual = trim($_POST['guia_encomienda'] ?? '');
            if (empty($guiaManual)) {
                echo json_encode(['success' => false, 'message' => 'El código de guía origen es obligatorio']);
                exit;
            }

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

            $estadoPago = isset($_POST['estado_pago_encomienda']) ? intval($_POST['estado_pago_encomienda']) : 1;
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
                'id_usuario'             => $id_usuario,
                'id_historial_caja'      => $id_historial_caja
            ];

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
                    $bulto['subtotal'] = 0;
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
            header('Location: ' . rtrim(URL, '/') . '/encomiendas');
            exit;
        }

        $encomienda = $this->encomiendasModel->obtenerEncomiendaCompleta($id_encomienda);
        if (!$encomienda) {
            Flash::set(false, 'La encomienda no fue encontrada.', 'Error');
            header('Location: ' . rtrim(URL, '/') . '/encomiendas');
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

            $id_historial_caja = $this->cajasModel->idHistorialAbiertoDeUsuario($id_usuario);
            if ($id_historial_caja <= 0 && $montoEntrega > 0) {
                echo json_encode(['success' => false, 'message' => 'Debe aperturar una caja para cobrar esta encomienda contra entrega (módulo Control de Cajas).']);
                exit;
            }

            if ($id_encomienda <= 0 || empty($ciReceptor)) {
                echo json_encode(['success' => false, 'message' => 'Datos insuficientes para registrar la entrega.']);
                exit;
            }


            $personaReceptor = $this->personasModel->buscarPorCi($ciReceptor);
            if ($personaReceptor) {
                $id_persona_retiro = $personaReceptor['id_persona'];
            } else {
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
                'id_historial_caja'         => $id_historial_caja,
                'id_usuario'                => $id_usuario,
                'monto_entrega_encomienda'  => $montoEntrega,
                'id_metodo_pago'            => $idMetodoPago,
                'observacion_entrega_encomienda' => trim($_POST['observacion'] ?? 'Entrega efectuada en sucursal destino')
            ];

            global $pdo;
            $pdo->beginTransaction();

            try {
                $this->encomiendasModel->registrarEntrega($dataEntrega);
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

    public function anular() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit; }

        $id = intval($_POST['id_encomienda'] ?? 0);
        try {
            if ($id <= 0 || !$this->encomiendasModel->anularEncomienda($id, (int)($_SESSION['id_sucursal'] ?? 0))) {
                echo json_encode(['success' => false, 'message' => 'No se puede anular: la guía ya fue asignada a un turno, entregada o no es de su sucursal.']);
                exit;
            }
            Flash::set(true, 'La guía fue anulada correctamente.', 'Guía Anulada');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error en el servidor al anular la guía.']);
        }
        exit;
    }
}
?>