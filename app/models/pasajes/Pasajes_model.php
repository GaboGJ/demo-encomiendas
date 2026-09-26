<?php
class Pasajes_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Listado de pasajes vendidos en una sucursal. Se filtra por la sucursal
     * del USUARIO que hizo la venta (usuarios.id_sucursal), no por el turno,
     * porque el turno ahora es opcional: un pasaje "en espera" todavía no
     * tiene turno asignado y por lo tanto no tiene una ruta/vehículo que
     * consultar. El turno, la ruta y el vehículo se traen con LEFT JOIN para
     * que esas filas igual aparezcan en el listado (con esos datos en NULL).
     */
    public function getPasajesPorSucursal($id_sucursal) {
        try {
            $sql = "SELECT 
                        dp.id_detalle_pasaje,
                        dp.id_pasaje,
                        p.codigo_pasaje,
                        CONCAT(per.nombre_persona, ' ', per.apellido_paterno_persona, ' ', IFNULL(per.apellido_materno_persona, '')) AS pasajero,
                        so.ciudad_sucursal AS origen,
                        sd.ciudad_sucursal AS destino,
                        e.dato_elemento AS numero_asiento,
                        v.numero_interno_vehiculo,
                        mo.nombre_modelo AS tipo_bus,
                        dp.precio_detalle_pasaje,
                        mp.nombre_metodo_pago,
                        ep.nombre_estado_pasaje,
                        dp.id_turno
                    FROM detalles_pasajes dp
                    INNER JOIN pasajes p ON dp.id_pasaje = p.id_pasaje
                    INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                    INNER JOIN personas per ON dp.id_persona_pasajero = per.id_persona
                    LEFT JOIN turnos t ON dp.id_turno = t.id_turno
                    LEFT JOIN sucursales so ON t.id_sucursal_origen = so.id_sucursal
                    LEFT JOIN sucursales sd ON t.id_sucursal_destino = sd.id_sucursal
                    LEFT JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
                    LEFT JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                    LEFT JOIN modelos mo ON v.id_modelo = mo.id_modelo
                    LEFT JOIN elementos e ON dp.id_elemento = e.id_elemento
                    INNER JOIN metodos_pagos mp ON p.id_metodo_pago = mp.id_metodo_pago
                    INNER JOIN estados_pasajes ep ON dp.id_estado_pasaje = ep.id_estado_pasaje
                    WHERE u.id_sucursal = :id_sucursal 
                      AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)
                    ORDER BY dp.id_detalle_pasaje DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_sucursal' => $id_sucursal]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Genera un código correlativo para el pasaje (Ej: PAS-000001)
     */
    private function generarCodigoCorrelativo() {
        $sql = "SELECT id_pasaje FROM pasajes ORDER BY id_pasaje DESC LIMIT 1";
        $stmt = $this->pdo->query($sql);
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

        $siguienteId = $ultimo ? (intval($ultimo['id_pasaje']) + 1) : 1;
        return 'PAS-' . str_pad($siguienteId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Resuelve el id_estado_pasaje correspondiente a un nombre (ej. "Pendiente",
     * "Asignado") en vez de asumir un id fijo, igual que Encomiendas_model hace
     * con obtenerIdEstadoEntregado(). Si el estado no existe cae al $fallback
     * para no romper instalaciones sin ese estado sembrado.
     */
    private function obtenerIdEstadoPorNombre($nombre, $fallback = 1) {
        $sql = "SELECT id_estado_pasaje 
                FROM estados_pasajes 
                WHERE LOWER(nombre_estado_pasaje) = :nombre 
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nombre' => strtolower($nombre)]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['id_estado_pasaje']) : $fallback;
    }

    /**
     * Estado para un pasaje que todavía NO tiene turno/chofer/vehículo
     * asignado (se vendió "en espera").
     */
    public function obtenerIdEstadoPendiente() {
        return $this->obtenerIdEstadoPorNombre('pendiente', 1);
    }

    /**
     * Estado para un pasaje que ya tiene turno y asiento asignados.
     */
    public function obtenerIdEstadoAsignado() {
        return $this->obtenerIdEstadoPorNombre('asignado', 1);
    }

    /**
     * Inserta la cabecera de la venta de pasaje.
     * Columnas reales de `pasajes`: codigo_pasaje, id_persona_comprador,
     * id_usuario, id_metodo_pago, total_pasaje.
     */
    public function insertarPasaje($data) {
        $codigo = $this->generarCodigoCorrelativo();

        $sql = "INSERT INTO pasajes 
            (codigo_pasaje, id_persona_comprador, id_usuario, id_metodo_pago, total_pasaje, estado_pasaje, create_pasaje) 
            VALUES 
            (:codigo, :comprador, :usuario, :metodo_pago, :total, 1, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':codigo'      => $codigo,
            ':comprador'   => $data['id_persona_comprador'],
            ':usuario'     => $data['id_usuario'],
            ':metodo_pago' => $data['id_metodo_pago'],
            ':total'       => $data['total_pasaje']
        ]);

        return [
            'id_pasaje' => $this->pdo->lastInsertId(),
            'codigo'    => $codigo
        ];
    }

    /**
     * Inserta un detalle (un boleto/asiento) de una venta de pasaje.
     * id_turno e id_elemento pueden llegar NULL: es la venta "en espera" que
     * todavía no tiene chofer/vehículo/asiento asignado (ver migración
     * database/migrations/002_detalles_pasajes_turno_opcional.sql).
     */
    public function insertarDetallePasaje($data) {
        $sql = "INSERT INTO detalles_pasajes 
            (id_pasaje, id_turno, id_persona_pasajero, id_elemento, precio_detalle_pasaje, id_estado_pasaje, estado_detalle_pasaje, create_detalle_pasaje) 
            VALUES 
            (:id_pasaje, :id_turno, :id_pasajero, :id_elemento, :precio, :id_estado_pasaje, 1, NOW())";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_pasaje'        => $data['id_pasaje'],
            ':id_turno'         => $data['id_turno'] ?: null,
            ':id_pasajero'      => $data['id_persona_pasajero'],
            ':id_elemento'      => $data['id_elemento'] ?: null,
            ':precio'           => $data['precio_detalle_pasaje'],
            ':id_estado_pasaje' => $data['id_estado_pasaje']
        ]);
    }

    /**
     * Datos completos de una venta (cabecera + TODOS sus asientos/boletos),
     * para el detalle en modal y para la impresión. $id_pasaje es la cabecera
     * (pasajes.id_pasaje), no un detalle individual, porque una sola venta
     * puede incluir varios asientos.
     */
    public function obtenerPasajeCompleto($id_pasaje) {
        try {
            $sql = "SELECT 
                        p.id_pasaje,
                        p.codigo_pasaje,
                        p.total_pasaje,
                        p.create_pasaje,
                        CONCAT(IFNULL(comp.nombre_persona,''), ' ', IFNULL(comp.apellido_paterno_persona,''), ' ', IFNULL(comp.apellido_materno_persona,'')) AS comprador_nombre,
                        comp.carnet_persona AS comprador_ci,
                        comp.telefono_persona AS comprador_celular,
                        mp.nombre_metodo_pago
                    FROM pasajes p
                    LEFT JOIN personas comp ON p.id_persona_comprador = comp.id_persona
                    LEFT JOIN metodos_pagos mp ON p.id_metodo_pago = mp.id_metodo_pago
                    WHERE p.id_pasaje = :id
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id_pasaje]);
            $pasaje = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pasaje) {
                return null;
            }

            $sqlDetalles = "SELECT 
                                dp.id_detalle_pasaje,
                                dp.id_turno,
                                dp.precio_detalle_pasaje,
                                e.dato_elemento AS numero_asiento,
                                so.ciudad_sucursal AS origen_ciudad,
                                sd.ciudad_sucursal AS destino_ciudad,
                                t.fecha_salida_turno,
                                t.hora_salida_turno,
                                v.numero_interno_vehiculo,
                                mo.nombre_modelo,
                                CONCAT(p_chof.nombre_persona, ' ', p_chof.apellido_paterno_persona) AS nombre_chofer,
                                ep.nombre_estado_pasaje
                            FROM detalles_pasajes dp
                            LEFT JOIN elementos e ON dp.id_elemento = e.id_elemento
                            LEFT JOIN turnos t ON dp.id_turno = t.id_turno
                            LEFT JOIN sucursales so ON t.id_sucursal_origen = so.id_sucursal
                            LEFT JOIN sucursales sd ON t.id_sucursal_destino = sd.id_sucursal
                            LEFT JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
                            LEFT JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                            LEFT JOIN modelos mo ON v.id_modelo = mo.id_modelo
                            LEFT JOIN choferes ch ON vc.id_chofer = ch.id_chofer
                            LEFT JOIN personas p_chof ON ch.id_persona = p_chof.id_persona
                            INNER JOIN estados_pasajes ep ON dp.id_estado_pasaje = ep.id_estado_pasaje
                            WHERE dp.id_pasaje = :id
                              AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)
                            ORDER BY dp.id_detalle_pasaje ASC";

            $stmtDet = $this->pdo->prepare($sqlDetalles);
            $stmtDet->execute([':id' => $id_pasaje]);
            $pasaje['detalles'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            return $pasaje;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Anula un boleto/asiento puntual (una fila de detalles_pasajes), sin
     * afectar los demás asientos que pudiera tener la misma venta.
     */
    public function anularPasaje($id_detalle_pasaje) {
        $sql = "UPDATE detalles_pasajes 
                SET estado_detalle_pasaje = 0, update_detalle_pasaje = NOW() 
                WHERE id_detalle_pasaje = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id_detalle_pasaje]);
    }
}
?>