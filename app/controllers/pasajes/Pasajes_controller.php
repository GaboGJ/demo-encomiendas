<?php
require_once __DIR__ . '/../../models/pasajes/Pasajes_model.php';
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../models/despachos/Despachos_model.php';
require_once __DIR__ . '/../../models/elementos/Elementos_model.php';
require_once __DIR__ . '/../../models/metodos_pagos/Metodos_pagos_model.php';
require_once __DIR__ . '/../../models/cajas/Cajas_model.php';

class Pasajes_controller {

    /**
     * Venta "en espera" (sin turno). DESACTIVADA por ahora: todavía no existe
     * la pantalla que toma esos detalles (id_turno NULL) y les asigna turno +
     * asiento, así que quedarían huérfanos. La rama de código sigue en
     * guardar(); para reactivarla hay que poner esto en true, volver a mostrar
     * la opción "Sin turno" en views/dashboard/pasajes/new.php y construir la
     * asignación posterior (UPDATE de id_turno / id_elemento).
     */
    const PERMITIR_VENTA_EN_ESPERA = false;

    private $pasajesModel;
    private $personasModel;
    private $despachosModel;
    private $elementosModel;
    private $metodosPagosModel;
    private $cajasModel;

    public function __construct() {
        $this->pasajesModel      = new Pasajes_model();
        $this->personasModel     = new Personas_model();
        $this->despachosModel    = new Despachos_model();
        $this->elementosModel    = new Elementos_model();
        $this->metodosPagosModel = new Metodos_pagos_model();
        $this->cajasModel        = new Cajas_model();
    }

    /**
     * Listado de VENTAS (una fila por venta). El detalle de asientos/pasajeros
     * se ve en el modal (acción detalle()).
     */
    public function index() {
        $viewPath = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'pasajes';
        $id_sucursal_actual = $_SESSION['id_sucursal'] ?? 1;

        $ventas = $this->pasajesModel->getVentasPasajesPorSucursal($id_sucursal_actual);

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
     * capacidad) y el PLANO COMPLETO del vehículo (pisos + todos los
     * elementos), marcando qué asientos ya están vendidos EN ESE turno.
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

        $id_sucursal_actual = (int)($_SESSION['id_sucursal'] ?? 0);
        if ((int)$turno['id_sucursal_origen'] !== $id_sucursal_actual
            || !$this->despachosModel->esTurnoAbierto($turno['nombre_estado_turno'])) {
            echo json_encode(['success' => false, 'message' => 'El turno no pertenece a su sucursal o ya no está abierto.']);
            exit;
        }

        $pisos     = $this->elementosModel->getPisosPorModelo($turno['id_modelo']);
        $elementos = $this->elementosModel->getElementosPorModelo($turno['id_modelo'], $id_turno);

        // Rango real (mínimo/máximo) de fila y columna de cada piso. No se
        // asume que fila/columna empiecen en 0: se normalizan restando el
        // mínimo para no dejar una fila/columna en blanco de más.
        $rangoPorPiso = [];
        foreach ($elementos as $el) {
            $idPiso = $el['id_piso'];
            $fila   = intval($el['fila_elemento']);
            $col    = intval($el['columna_elemento']);

            if (!isset($rangoPorPiso[$idPiso])) {
                $rangoPorPiso[$idPiso] = [
                    'minFila' => $fila, 'maxFila' => $fila,
                    'minCol'  => $col,  'maxCol'  => $col,
                    'numero_piso' => $el['numero_piso']
                ];
            } else {
                $rangoPorPiso[$idPiso]['minFila'] = min($rangoPorPiso[$idPiso]['minFila'], $fila);
                $rangoPorPiso[$idPiso]['maxFila'] = max($rangoPorPiso[$idPiso]['maxFila'], $fila);
                $rangoPorPiso[$idPiso]['minCol']  = min($rangoPorPiso[$idPiso]['minCol'], $col);
                $rangoPorPiso[$idPiso]['maxCol']  = max($rangoPorPiso[$idPiso]['maxCol'], $col);
            }
        }

        foreach ($elementos as &$el) {
            $idPiso = $el['id_piso'];
            if (isset($rangoPorPiso[$idPiso])) {
                $el['fila_elemento']    = intval($el['fila_elemento'])    - $rangoPorPiso[$idPiso]['minFila'];
                $el['columna_elemento'] = intval($el['columna_elemento']) - $rangoPorPiso[$idPiso]['minCol'];
            }
        }
        unset($el);

        if (empty($pisos)) {
            $pisos = [];
            foreach ($rangoPorPiso as $idPiso => $r) {
                $pisos[] = [
                    'id_piso'       => $idPiso,
                    'numero_piso'   => $r['numero_piso'],
                    'nombre_piso'   => null,
                    'filas_piso'    => ($r['maxFila'] - $r['minFila'] + 1),
                    'columnas_piso' => ($r['maxCol'] - $r['minCol'] + 1)
                ];
            }
        } else {
            foreach ($pisos as &$piso) {
                $idPiso = $piso['id_piso'];
                $necesitaFilas = isset($rangoPorPiso[$idPiso]) ? ($rangoPorPiso[$idPiso]['maxFila'] - $rangoPorPiso[$idPiso]['minFila'] + 1) : 0;
                $necesitaCols  = isset($rangoPorPiso[$idPiso]) ? ($rangoPorPiso[$idPiso]['maxCol']  - $rangoPorPiso[$idPiso]['minCol']  + 1) : 0;

                $piso['filas_piso']    = max(intval($piso['filas_piso']), $necesitaFilas, 1);
                $piso['columnas_piso'] = max(intval($piso['columnas_piso']), $necesitaCols, 1);
            }
            unset($piso);
        }

        echo json_encode([
            'success'   => true,
            'turno'     => $turno,
            'pisos'     => $pisos,
            'elementos' => $elementos
        ]);
        exit;
    }

    /**
     * Busca una persona por C.I. y, si no existe, la registra. Lanza
     * Exception (mensaje legible) si faltan datos obligatorios.
     */
    private function resolverPersona($ci, $nombres, $paterno, $materno, $celular, $direccion, $etiqueta) {
        $ci = trim((string)$ci);
        if ($ci === '') {
            throw new Exception("El C.I. del {$etiqueta} es obligatorio");
        }

        $existente = $this->personasModel->buscarPorCi($ci);
        if ($existente) {
            return $existente['id_persona'];
        }

        $nombres = trim((string)$nombres);
        $paterno = trim((string)$paterno);
        if ($nombres === '' || $paterno === '') {
            throw new Exception("Nombres y apellido paterno del {$etiqueta} son obligatorios");
        }

        return $this->personasModel->insertarPersona(
            $ci,
            $nombres,
            $paterno,
            trim((string)$materno),
            trim((string)$celular),
            trim((string)$direccion)
        );
    }

    /**
     * Convierte asientos_json en una lista [id_elemento, id_persona_pasajero].
     * Cada asiento llega como objeto:
     *   {"id_elemento":12,"usar_comprador":true}
     *   {"id_elemento":15,"usar_comprador":false,"ci":"..","nombres":"..","paterno":"..","materno":"","celular":".."}
     * Si usar_comprador es true (o no viene), el pasajero es el comprador; si
     * no, se busca/registra a esa otra persona. También acepta el formato
     * antiguo (ids sueltos) por compatibilidad.
     */
    private function normalizarAsientos($asientos, $id_comprador) {
        if (empty($asientos) || !is_array($asientos)) {
            throw new Exception('Debe seleccionar al menos un asiento del turno.');
        }

        $vistos = [];
        $resultado = [];

        foreach ($asientos as $a) {
            if (!is_array($a)) {
                $a = ['id_elemento' => $a, 'usar_comprador' => true];
            }

            $idElemento = intval($a['id_elemento'] ?? 0);
            if ($idElemento <= 0) {
                throw new Exception('Se recibió un asiento inválido.');
            }
            if (isset($vistos[$idElemento])) {
                throw new Exception('Un mismo asiento fue seleccionado más de una vez.');
            }
            $vistos[$idElemento] = true;

            $usarComprador = !array_key_exists('usar_comprador', $a) || !empty($a['usar_comprador']);
            if ($usarComprador) {
                $idPasajero = $id_comprador;
            } else {
                $idPasajero = $this->resolverPersona(
                    $a['ci'] ?? '',
                    $a['nombres'] ?? '',
                    $a['paterno'] ?? '',
                    $a['materno'] ?? '',
                    $a['celular'] ?? '',
                    '',
                    'pasajero del asiento'
                );
            }

            $resultado[] = ['id_elemento' => $idElemento, 'id_persona_pasajero' => $idPasajero];
        }

        return $resultado;
    }

    /**
     * Registra la venta. TODO ocurre en una sola transacción (incluido el
     * alta de comprador y pasajeros nuevos): si algo falla, no queda nada a
     * medias.
     *
     * pasajes.id_persona_comprador     = quien paga.
     * detalles_pasajes.id_persona_pasajero = quien viaja en cada asiento
     *                                        (por defecto el comprador).
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

            $id_usuario     = $_SESSION['id_usuario'] ?? 1;
            $id_metodo_pago = intval($_POST['metodo_cobro'] ?? 1);
            $id_turno       = !empty($_POST['id_turno']) ? intval($_POST['id_turno']) : null;

            if (!$id_turno && !self::PERMITIR_VENTA_EN_ESPERA) {
                throw new Exception('Debe seleccionar un turno para emitir el boleto.');
            }
                        
            $id_historial_caja = $this->cajasModel->idHistorialAbiertoDeUsuario($id_usuario);
            if ($id_historial_caja <= 0) {
                throw new Exception('Debe aperturar una caja antes de vender pasajes (módulo Control de Cajas).');
            }

            $pdo->beginTransaction();

            $id_comprador = $this->resolverPersona(
                $_POST['comprador_ci'] ?? '',
                $_POST['comprador_nombres'] ?? '',
                $_POST['comprador_paterno'] ?? '',
                $_POST['comprador_materno'] ?? '',
                $_POST['comprador_celular'] ?? '',
                $_POST['comprador_direccion'] ?? '',
                'comprador'
            );

            if ($id_turno) {
                // --- Venta CON turno: uno o varios asientos concretos ---
                $turnoInfo = $this->despachosModel->obtenerTurnoConVehiculo($id_turno);
                if (!$turnoInfo) {
                    throw new Exception('El turno seleccionado ya no está disponible.');
                }

                if ((int)$turnoInfo['id_sucursal_origen'] !== (int)($_SESSION['id_sucursal'] ?? 0)
                    || !$this->despachosModel->esTurnoAbierto($turnoInfo['nombre_estado_turno'])) {
                    throw new Exception('El turno no pertenece a su sucursal o ya fue despachado/cancelado.');
                }

                $pasajeros = $this->normalizarAsientos(
                    json_decode($_POST['asientos_json'] ?? '[]', true),
                    $id_comprador
                );

                $precioUnitario = floatval($turnoInfo['precio_pasaje_turno']);
                $totalPasaje    = $precioUnitario * count($pasajeros);
                $idEstado       = $this->pasajesModel->obtenerIdEstadoAsignado();

                $resPasaje = $this->pasajesModel->insertarPasaje([
                    'id_persona_comprador' => $id_comprador,
                    'id_historial_caja'    => $id_historial_caja,
                    'id_usuario'           => $id_usuario,
                    'id_metodo_pago'       => $id_metodo_pago,
                    'total_pasaje'         => $totalPasaje
                ]);
                $idPasaje = $resPasaje['id_pasaje'];

                foreach ($pasajeros as $p) {
                    $this->pasajesModel->insertarDetallePasaje([
                        'id_pasaje'             => $idPasaje,
                        'id_turno'              => $id_turno,
                        'id_persona_pasajero'   => $p['id_persona_pasajero'],
                        'id_elemento'           => $p['id_elemento'],
                        'precio_detalle_pasaje' => $precioUnitario,
                        'id_estado_pasaje'      => $idEstado
                    ]);
                }
            } else {
                // --- Venta SIN turno ("en espera"): solo si PERMITIR_VENTA_EN_ESPERA ---
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

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // 1062 = duplicado real (UK_turno_asiento). Otros errores de la
            // clase 23000 (NOT NULL, llave foránea) NO significan "asiento
            // vendido", así que se muestra el motivo real.
            $codigoMysql = $e->errorInfo[1] ?? null;
            if ($codigoMysql == 1062) {
                $mensaje = 'Uno de los asientos seleccionados ya fue vendido. Actualice la lista e intente nuevamente.';
            } else {
                $mensaje = 'No se pudo registrar la venta (' . $e->getMessage() . ')';
            }

            echo json_encode(['success' => false, 'message' => $mensaje]);
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Imprime el boleto completo (todos los asientos de una misma venta).
     * $id es pasajes.id_pasaje (la cabecera).
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
     * Detalle en JSON (cabecera + todos los asientos con su pasajero) para el
     * modal "Ver Detalle". $id es pasajes.id_pasaje.
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
     * Anula la VENTA COMPLETA (POST id_pasaje): todos sus boletos.
     */
    public function anular() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $id = intval($_POST['id_pasaje'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Identificador inválido.']);
                exit;
            }

            $this->pasajesModel->anularVenta($id);
            Flash::set(true, 'La venta ha sido anulada correctamente.', 'Anulación Exitosa');
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Anula UN boleto/asiento (POST id_detalle_pasaje) sin tocar los demás
     * asientos de la misma venta.
     */
    public function anularDetalle() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $id = intval($_POST['id_detalle_pasaje'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Identificador inválido.']);
                exit;
            }

            $this->pasajesModel->anularDetalle($id);
            Flash::set(true, 'El boleto ha sido anulado correctamente.', 'Anulación Exitosa');
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
?>