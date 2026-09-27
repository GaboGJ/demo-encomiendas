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
     * capacidad) y el PLANO COMPLETO del vehículo (todos los pisos, con sus
     * dimensiones, y TODOS los elementos: asientos + especiales como Chofer,
     * Baño, Escalera, TV, etc.), marcando qué asientos ya están vendidos EN
     * ESE turno. Con esto el front-end dibuja el plano visual del bus en vez
     * de listar los asientos en una tabla.
     *
     * Se llama al elegir un turno en el paso 1 y al entrar al paso 2 del
     * wizard (para refrescar la ocupación real).
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

        $pisos     = $this->elementosModel->getPisosPorModelo($turno['id_modelo']);
        $elementos = $this->elementosModel->getElementosPorModelo($turno['id_modelo'], $id_turno);

        // Rango real (mínimo/máximo) de fila y columna que ocupa cada piso.
        // IMPORTANTE: no se asume que fila_elemento/columna_elemento empiecen
        // en 0 -algunos datos se cargaron empezando en 1, como en este caso-,
        // así que se normalizan restando el mínimo encontrado. Sin esto,
        // aparecía una fila y una columna en blanco de más (desplazando todo
        // el plano) cuando los datos venían en base 1.
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
            // No hay fila en `pisos` (o quedó inactiva): se arma un piso
            // "virtual" por cada id_piso que sí tenga elementos, para no
            // dejar el plano en blanco.
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

                // Siempre el mayor entre lo declarado en `pisos` y lo que
                // realmente ocupan los elementos: así nunca se recorta el
                // plano, aunque el registro de `pisos` haya quedado
                // desactualizado frente a la cantidad real de asientos.
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
     * Registra la venta. Dos modalidades según si llega id_turno o no:
     *   - CON turno: se exige al menos un asiento (asientos_json) y el precio
     *     se toma del turno (precio_pasaje_turno); el pasaje queda "Asignado".
     *   - SIN turno (en espera): se piden cantidad_pasajes y precio_manual;
     *     el pasaje queda "Pendiente" de que se le asigne chofer/vehículo.
     *
     * IMPORTANTE (columnas nullable): la venta "en espera" guarda
     * detalles_pasajes con id_turno / id_elemento en NULL. Si tu base de
     * datos todavía no tiene esas columnas como NULL (el modelo original las
     * trae NOT NULL), aplica la migración
     * database/migrations/002_detalles_pasajes_turno_opcional.sql antes de
     * usar esta opción, o esa rama fallará con un error de integridad.
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
            $resPasaje = null;
            $idPasaje  = null;

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
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                // MUY IMPORTANTE: antes se asumía que CUALQUIER error de
                // integridad (SQLSTATE clase 23000) era "el asiento ya fue
                // vendido", pero esa clase también cubre violaciones de
                // NOT NULL y de llave foránea (por ejemplo id_estado_pasaje
                // o id_turno/id_elemento apuntando a un registro que no
                // existe, o columnas que en tu base todavía son NOT NULL).
                // Eso ocultaba el error real y confundía, porque el mensaje
                // decía "ya fue vendido" sin serlo. Ahora se distingue el
                // código NATIVO de MySQL (1062 = duplicado real) del resto.
                $codigoMysql = $e->errorInfo[1] ?? null;

                if ($codigoMysql == 1062) {
                    throw new Exception('Uno de los asientos seleccionados ya fue vendido. Actualice la lista e intente nuevamente.');
                }

                // Para cualquier otro error de base de datos, se muestra el
                // motivo real (mensaje de MySQL) para poder diagnosticarlo,
                // en vez de una excusa genérica que no corresponde.
                throw new Exception('No se pudo registrar la venta (' . $e->getMessage() . ')');
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
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