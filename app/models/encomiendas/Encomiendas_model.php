<?php
class Encomiendas_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getEnviosPorSucursal($id_sucursal_origen) {
        try {
            $sql = "SELECT 
                        e.id_encomienda,
                        e.guia_encomienda,
                        e.monto_encomienda,
                        CAST(e.estado_pago_encomienda AS UNSIGNED) AS estado_pago_encomienda,
                        ee.nombre_estado_encomienda,
                        CONCAT(p_rem.nombre_persona, ' ', p_rem.apellido_paterno_persona) AS remitente,
                        CONCAT(p_dest.nombre_persona, ' ', p_dest.apellido_paterno_persona) AS destinatario,
                        s_orig.ciudad_sucursal AS ciudad_origen,
                        s_dest.ciudad_sucursal AS ciudad_destino
                    FROM encomiendas e
                    INNER JOIN personas p_rem ON e.id_persona_remitente = p_rem.id_persona
                    INNER JOIN personas p_dest ON e.id_persona_destinatario = p_dest.id_persona
                    INNER JOIN sucursales s_orig ON e.id_sucursal_origen = s_orig.id_sucursal
                    INNER JOIN sucursales s_dest ON e.id_sucursal_destino = s_dest.id_sucursal
                    INNER JOIN estados_encomiendas ee ON e.id_estado_encomienda = ee.id_estado_encomienda
                    WHERE e.id_sucursal_origen = :id_origen AND (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)
                    ORDER BY e.id_encomienda DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_origen' => $id_sucursal_origen]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getLlegadasPorSucursal($id_sucursal_destino) {
        try {
            $sql = "SELECT 
                        e.id_encomienda,
                        e.guia_encomienda,
                        e.monto_encomienda,
                        CAST(e.estado_pago_encomienda AS UNSIGNED) AS estado_pago_encomienda,
                        ee.nombre_estado_encomienda,
                        CONCAT(p_rem.nombre_persona, ' ', p_rem.apellido_paterno_persona) AS remitente,
                        CONCAT(p_dest.nombre_persona, ' ', p_dest.apellido_paterno_persona) AS destinatario,
                        s_orig.ciudad_sucursal AS ciudad_origen,
                        s_dest.ciudad_sucursal AS ciudad_destino
                    FROM encomiendas e
                    INNER JOIN personas p_rem ON e.id_persona_remitente = p_rem.id_persona
                    INNER JOIN personas p_dest ON e.id_persona_destinatario = p_dest.id_persona
                    INNER JOIN sucursales s_orig ON e.id_sucursal_origen = s_orig.id_sucursal
                    INNER JOIN sucursales s_dest ON e.id_sucursal_destino = s_dest.id_sucursal
                    INNER JOIN estados_encomiendas ee ON e.id_estado_encomienda = ee.id_estado_encomienda
                    WHERE e.id_sucursal_destino = :id_destino AND (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)
                    ORDER BY e.id_encomienda DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_destino' => $id_sucursal_destino]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene los contenidos/tarifas disponibles para una ruta específica (Origen -> Destino)
     */
    public function getContenidosPorRuta($id_origen, $id_destino) {
        try {
            $sql = "SELECT DISTINCT 
                        ec.id_encomienda_contenido, 
                        ec.nombre_encomienda_contenido,
                        te.precio_tarifa_encomienda
                    FROM tarifas_encomiendas te
                    INNER JOIN encomiendas_contenidos ec ON te.id_encomienda_contenido = ec.id_encomienda_contenido
                    WHERE te.id_sucursal_origen = :origen 
                      AND te.id_sucursal_destino = :destino
                      AND (te.estado_tarifa_encomienda = 1 OR te.estado_tarifa_encomienda IS NULL)
                      AND (ec.estado_encomienda_contenido = 1 OR ec.estado_encomienda_contenido IS NULL)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':origen'  => $id_origen,
                ':destino' => $id_destino
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Catálogo completo de tipos de contenido, SIN filtrar por ruta ni tarifa.
     * Se usa en la Recepción (reception.php): la carga llega desde una sucursal
     * externa que no maneja este sistema, así que no existe (ni existirá) una
     * fila en tarifas_encomiendas para esa ruta. Aquí solo se necesita clasificar
     * qué tipo de contenido trae cada bulto, no calcular un precio.
     */
    public function getContenidosActivos() {
        try {
            $sql = "SELECT id_encomienda_contenido, nombre_encomienda_contenido
                    FROM encomiendas_contenidos
                    WHERE (estado_encomienda_contenido = 1 OR estado_encomienda_contenido IS NULL)
                    ORDER BY nombre_encomienda_contenido ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerTarifa($id_origen, $id_destino, $id_contenido, $peso = 0) {
        $sql = "SELECT precio_tarifa_encomienda 
                FROM tarifas_encomiendas 
                WHERE id_sucursal_origen = :origen 
                  AND id_sucursal_destino = :destino 
                  AND id_encomienda_contenido = :contenido 
                  AND (estado_tarifa_encomienda = 1 OR estado_tarifa_encomienda IS NULL) 
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':origen'    => $id_origen,
            ':destino'   => $id_destino,
            ':contenido' => $id_contenido
        ]);
        
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? floatval($res['precio_tarifa_encomienda']) : null;
    }

  /**
     * Genera un código de guía correlativo secuencial (Ej: ENC-000001)
     */
    private function generarGuiaCorrelativa() {
        $sql = "SELECT id_encomienda FROM encomiendas ORDER BY id_encomienda DESC LIMIT 1";
        $stmt = $this->pdo->query($sql);
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

        $siguienteId = $ultimo ? (intval($ultimo['id_encomienda']) + 1) : 1;
        
        // Genera formato ENC-000001, ENC-000002, etc.
        return 'ENC-' . str_pad($siguienteId, 6, '0', STR_PAD_LEFT);
    }

    public function insertarEncomienda($data) {
        $guia = $this->generarGuiaCorrelativa();

        $sql = "INSERT INTO encomiendas 
            (guia_encomienda, id_sucursal_origen, id_sucursal_destino, id_persona_remitente, id_persona_destinatario, id_turno, id_usuario, declaracion_encomienda, monto_encomienda, estado_pago_encomienda, id_metodo_pago, id_estado_encomienda, estado_encomienda, create_encomienda) 
            VALUES 
            (:guia, :origen, :destino, :remitente, :destinatario, :turno, :usuario, :declaracion, :monto, :estado_pago, :metodo_pago, 1, 1, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':guia', $guia);
        $stmt->bindValue(':origen', $data['id_sucursal_origen']);
        $stmt->bindValue(':destino', $data['id_sucursal_destino']);
        $stmt->bindValue(':remitente', $data['id_persona_remitente']);
        $stmt->bindValue(':destinatario', $data['id_persona_destinatario']);
        $stmt->bindValue(':turno', $data['id_turno'] ?? null);
        $stmt->bindValue(':usuario', $data['id_usuario']);
        $stmt->bindValue(':declaracion', $data['declaracion_encomienda']);
        $stmt->bindValue(':monto', $data['monto_encomienda']);
        $stmt->bindValue(':estado_pago', (int)$data['estado_pago_encomienda'], PDO::PARAM_INT); // Se fuerza explícitamente a entero
        $stmt->bindValue(':metodo_pago', $data['id_metodo_pago']);

        $stmt->execute();

        return [
            'id_encomienda' => $this->pdo->lastInsertId(),
            'guia'          => $guia
        ];
    }

    /**
     * Inserta el detalle del bulto usando el código correlativo derivado de la guía padre (Ej: ENC-000001-01)
     */
    public function insertarDetalleEncomienda($id_encomienda, $guia_padre, $index_bulto, $bulto) {
        $sql = "INSERT INTO detalles_encomiendas 
            (id_encomienda, codigo_detalle_encomienda, descripcion_detalle_encomienda, peso_detalle_encomienda, id_encomienda_contenido, subtotal_detalle_encomienda, estado_detalle_encomienda, create_detalle_encomienda) 
            VALUES 
            (:id_encomienda, :codigo, :descripcion, :peso, :id_contenido, :subtotal, 1, NOW())";

        // Formatea el subcódigo correlativo: ENC-000001-01, ENC-000001-02, etc.
        $codigoDetalle = $guia_padre . '-' . str_pad($index_bulto, 2, '0', STR_PAD_LEFT);
        $peso = (!empty($bulto['peso']) && floatval($bulto['peso']) > 0) ? floatval($bulto['peso']) : null;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_encomienda' => $id_encomienda,
            ':codigo'        => $codigoDetalle,
            ':descripcion'   => $bulto['descripcion'],
            ':peso'          => $peso,
            ':id_contenido'  => $bulto['id_contenido'],
            ':subtotal'      => $bulto['subtotal']
        ]);
    }

    public function obtenerEncomiendaCompleta($id_encomienda) {
        try {
            $sql = "SELECT 
                        e.*,
                        CAST(e.estado_pago_encomienda AS UNSIGNED) AS estado_pago_encomienda,
                        CONCAT(IFNULL(p_rem.nombre_persona,''), ' ', IFNULL(p_rem.apellido_paterno_persona,''), ' ', IFNULL(p_rem.apellido_materno_persona, '')) AS remitente_nombre,
                        p_rem.carnet_persona AS remitente_ci,
                        p_rem.telefono_persona AS remitente_celular,
                        p_rem.direccion_persona AS remitente_direccion,
                        CONCAT(IFNULL(p_dest.nombre_persona,''), ' ', IFNULL(p_dest.apellido_paterno_persona,''), ' ', IFNULL(p_dest.apellido_materno_persona, '')) AS destinatario_nombre,
                        p_dest.nombre_persona AS destinatario_nombres,
                        p_dest.apellido_paterno_persona AS destinatario_paterno,
                        p_dest.apellido_materno_persona AS destinatario_materno,
                        p_dest.carnet_persona AS destinatario_ci,
                        p_dest.telefono_persona AS destinatario_celular,
                        p_dest.direccion_persona AS destinatario_direccion,
                        s_orig.nombre_sucursal AS sucursal_origen_nombre,
                        s_orig.ciudad_sucursal AS sucursal_origen_ciudad,
                        s_dest.nombre_sucursal AS sucursal_destino_nombre,
                        s_dest.ciudad_sucursal AS sucursal_destino_ciudad,
                        mp.nombre_metodo_pago
                    FROM encomiendas e
                    LEFT JOIN personas p_rem ON e.id_persona_remitente = p_rem.id_persona
                    LEFT JOIN personas p_dest ON e.id_persona_destinatario = p_dest.id_persona
                    LEFT JOIN sucursales s_orig ON e.id_sucursal_origen = s_orig.id_sucursal
                    LEFT JOIN sucursales s_dest ON e.id_sucursal_destino = s_dest.id_sucursal
                    LEFT JOIN metodos_pagos mp ON e.id_metodo_pago = mp.id_metodo_pago
                    LEFT JOIN usuarios u ON e.id_usuario = u.id_usuario
                    WHERE e.id_encomienda = :id
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id_encomienda]);
            $encomienda = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$encomienda) {
                return null;
            }

            // Obtener bultos/detalles vinculados
            $sqlDetalles = "SELECT 
                                de.*,
                                ec.nombre_encomienda_contenido
                            FROM detalles_encomiendas de
                            LEFT JOIN encomiendas_contenidos ec ON de.id_encomienda_contenido = ec.id_encomienda_contenido
                            WHERE de.id_encomienda = :id";
            
            $stmtDet = $this->pdo->prepare($sqlDetalles);
            $stmtDet->execute([':id' => $id_encomienda]);
            $encomienda['bultos'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            return $encomienda;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Registra el ingreso/recepción de una encomienda recibida desde otra sucursal.
     */
    public function insertarRecepcionEncomienda($data) {
        $sql = "INSERT INTO encomiendas 
            (guia_encomienda, id_sucursal_origen, id_sucursal_destino, id_persona_remitente, id_persona_destinatario, id_turno, id_usuario, declaracion_encomienda, monto_encomienda, estado_pago_encomienda, id_metodo_pago, id_estado_encomienda, estado_encomienda, create_encomienda) 
            VALUES 
            (:guia, :origen, :destino, :remitente, :destinatario, :turno, :usuario, :declaracion, :monto, :estado_pago, :metodo_pago, 1, 1, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':guia', $data['guia_encomienda']);
        $stmt->bindValue(':origen', $data['id_sucursal_origen']);
        $stmt->bindValue(':destino', $data['id_sucursal_destino']);
        $stmt->bindValue(':remitente', $data['id_persona_remitente']);
        $stmt->bindValue(':destinatario', $data['id_persona_destinatario']);
        $stmt->bindValue(':turno', $data['id_turno'] ?? null);
        $stmt->bindValue(':usuario', $data['id_usuario']);
        $stmt->bindValue(':declaracion', $data['declaracion_encomienda']);
        $stmt->bindValue(':monto', $data['monto_encomienda']);
        $stmt->bindValue(':estado_pago', (int)$data['estado_pago_encomienda'], PDO::PARAM_INT);
        $stmt->bindValue(':metodo_pago', $data['id_metodo_pago']);

        $stmt->execute();

        return [
            'id_encomienda' => $this->pdo->lastInsertId(),
            'guia'          => $data['guia_encomienda']
        ];
    }

    public function registrarEntrega($data) {
        $sql = "INSERT INTO entregas_encomiendas 
                (id_encomienda, id_persona_retiro, id_usuario, monto_entrega_encomienda, id_metodo_pago, observacion_entrega_encomienda, create_entrega_encomienda) 
                VALUES 
                (:id_encomienda, :id_persona_retiro, :id_usuario, :monto, :id_metodo_pago, :observacion, NOW())";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_encomienda'       => $data['id_encomienda'],
            ':id_persona_retiro'   => $data['id_persona_retiro'],
            ':id_usuario'          => $data['id_usuario'],
            ':monto'               => $data['monto_entrega_encomienda'],
            ':id_metodo_pago'      => $data['id_metodo_pago'],
            ':observacion'         => $data['observacion_entrega_encomienda']
        ]);
    }

    /**
     * Resuelve el id_estado_encomienda correspondiente a "Entregado" por NOMBRE en vez
     * de asumir un id fijo (el script de BD no trae datos semilla de estados_encomiendas,
     * así que un "3" hardcodeado no se puede verificar contra el modelo y es frágil si
     * los estados se sembraron en otro orden). Si por algún motivo no existe un estado
     * con ese nombre, cae de vuelta a 3 para no romper instalaciones ya en producción.
     */
    private function obtenerIdEstadoEntregado() {
        $sql = "SELECT id_estado_encomienda 
                FROM estados_encomiendas 
                WHERE LOWER(nombre_estado_encomienda) = 'entregado' 
                LIMIT 1";
        $stmt = $this->pdo->query($sql);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['id_estado_encomienda']) : 3;
    }

    /**
     * Marca la encomienda como Entregada. Al completarse la entrega el saldo queda
     * liquidado (ya sea porque se pagó en origen, o porque se acaba de cobrar el COD
     * en destino), así que estado_pago_encomienda siempre pasa a 1 (Pagado).
     */
    public function marcarComoEntregada($id_encomienda) {
        $idEstadoEntregado = $this->obtenerIdEstadoEntregado();

        $sql = "UPDATE encomiendas 
                SET id_estado_encomienda = :id_estado, estado_pago_encomienda = 1, update_encomienda = NOW() 
                WHERE id_encomienda = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_estado' => $idEstadoEntregado,
            ':id'        => $id_encomienda
        ]);
    }

    /**
     * Trae la última entrega registrada para una encomienda, junto con los datos de
     * quien retiró y el método de pago usado, para el acta de entrega imprimible.
     */
    public function obtenerUltimaEntrega($id_encomienda) {
        try {
            $sql = "SELECT 
                        ee.*,
                        CONCAT(IFNULL(p.nombre_persona,''), ' ', IFNULL(p.apellido_paterno_persona,''), ' ', IFNULL(p.apellido_materno_persona,'')) AS receptor_nombre,
                        p.carnet_persona AS receptor_ci,
                        p.telefono_persona AS receptor_celular,
                        mp.nombre_metodo_pago
                    FROM entregas_encomiendas ee
                    LEFT JOIN personas p ON ee.id_persona_retiro = p.id_persona
                    LEFT JOIN metodos_pagos mp ON ee.id_metodo_pago = mp.id_metodo_pago
                    WHERE ee.id_encomienda = :id
                    ORDER BY ee.id_entrega_encomienda DESC
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id_encomienda]);
            $entrega = $stmt->fetch(PDO::FETCH_ASSOC);
            return $entrega ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>