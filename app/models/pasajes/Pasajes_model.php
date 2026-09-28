<?php
class Pasajes_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Listado de VENTAS (una fila por pasajes.id_pasaje, no por asiento).
     * Se filtra por la sucursal del USUARIO que hizo la venta. El turno, la
     * ruta y los asientos se resumen con GROUP_CONCAT: una venta puede tener
     * varios asientos e incluso (en teoría) varios turnos.
     *
     * Solo cuenta detalles activos: si todos los boletos de una venta fueron
     * anulados, la venta deja de aparecer en el listado.
     */
    public function getVentasPasajesPorSucursal($id_sucursal) {
        try {
            $sql = "SELECT 
                        p.id_pasaje,
                        p.codigo_pasaje,
                        p.create_pasaje,
                        CONCAT(IFNULL(comp.nombre_persona,''), ' ', IFNULL(comp.apellido_paterno_persona,'')) AS comprador,
                        comp.carnet_persona AS comprador_ci,
                        mp.nombre_metodo_pago,
                        COUNT(dp.id_detalle_pasaje) AS total_asientos,
                        IFNULL(SUM(dp.precio_detalle_pasaje), 0) AS total_venta,
                        GROUP_CONCAT(DISTINCT CONCAT(so.ciudad_sucursal, ' ➔ ', sd.ciudad_sucursal) SEPARATOR ' | ') AS rutas,
                        GROUP_CONCAT(DISTINCT e.dato_elemento ORDER BY e.dato_elemento SEPARATOR ', ') AS asientos,
                        GROUP_CONCAT(DISTINCT ep.nombre_estado_pasaje SEPARATOR ',') AS estados
                    FROM pasajes p
                    INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                    INNER JOIN personas comp ON p.id_persona_comprador = comp.id_persona
                    INNER JOIN metodos_pagos mp ON p.id_metodo_pago = mp.id_metodo_pago
                    INNER JOIN detalles_pasajes dp ON dp.id_pasaje = p.id_pasaje
                    INNER JOIN estados_pasajes ep ON dp.id_estado_pasaje = ep.id_estado_pasaje
                    LEFT JOIN turnos t ON dp.id_turno = t.id_turno
                    LEFT JOIN sucursales so ON t.id_sucursal_origen = so.id_sucursal
                    LEFT JOIN sucursales sd ON t.id_sucursal_destino = sd.id_sucursal
                    LEFT JOIN elementos e ON dp.id_elemento = e.id_elemento
                    WHERE u.id_sucursal = :id_sucursal
                      AND (p.estado_pasaje = 1 OR p.estado_pasaje IS NULL)
                      AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)
                    GROUP BY p.id_pasaje, p.codigo_pasaje, p.create_pasaje,
                             comp.nombre_persona, comp.apellido_paterno_persona, comp.carnet_persona,
                             mp.nombre_metodo_pago
                    ORDER BY p.id_pasaje DESC";

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
     * "Asignado") en vez de asumir un id fijo. Si el estado no existe cae al
     * $fallback para no romper instalaciones sin ese estado sembrado.
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

    /** Estado para un pasaje sin turno/chofer/vehículo (venta en espera). */
    public function obtenerIdEstadoPendiente() {
        return $this->obtenerIdEstadoPorNombre('pendiente', 1);
    }

    /** Estado para un pasaje que ya tiene turno y asiento asignados. */
    public function obtenerIdEstadoAsignado() {
        return $this->obtenerIdEstadoPorNombre('asignado', 1);
    }

    /**
     * Inserta la cabecera de la venta de pasaje.
     * pasajes.id_persona_comprador = quien PAGA la venta.
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
     * Inserta un detalle (un boleto/asiento) de una venta.
     * detalles_pasajes.id_persona_pasajero = quien VIAJA en ese asiento, que
     * puede ser distinto del comprador. id_turno / id_elemento pueden llegar
     * NULL (venta en espera).
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
     * Venta completa: cabecera (comprador) + TODOS sus boletos, cada uno con
     * el pasajero que viaja en ese asiento. Se usa en el modal de detalle y
     * en la impresión. $id_pasaje es la cabecera (pasajes.id_pasaje).
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
                                CONCAT(IFNULL(pas.nombre_persona,''), ' ', IFNULL(pas.apellido_paterno_persona,''), ' ', IFNULL(pas.apellido_materno_persona,'')) AS pasajero_nombre,
                                pas.carnet_persona AS pasajero_ci,
                                dp.id_persona_pasajero,
                                so.ciudad_sucursal AS origen_ciudad,
                                sd.ciudad_sucursal AS destino_ciudad,
                                t.fecha_salida_turno,
                                t.hora_salida_turno,
                                v.numero_interno_vehiculo,
                                mo.nombre_modelo,
                                CONCAT(p_chof.nombre_persona, ' ', p_chof.apellido_paterno_persona) AS nombre_chofer,
                                ep.nombre_estado_pasaje
                            FROM detalles_pasajes dp
                            LEFT JOIN personas pas ON dp.id_persona_pasajero = pas.id_persona
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
     * Anula UN boleto (una fila de detalles_pasajes) sin tocar los demás
     * asientos de la venta, y recalcula el total de la cabecera.
     *
     * OJO: además de marcar estado_detalle_pasaje = 0, se pone id_elemento en
     * NULL. Motivo: UK_turno_asiento (id_turno, id_elemento) sigue contando
     * las filas anuladas, y sin esto el asiento aparecería libre en el plano
     * pero no se podría volver a vender (error 1062).
     *
     * Lanza Exception con un mensaje legible si no se puede anular.
     */
    public function anularDetalle($id_detalle_pasaje) {
        $sql = "SELECT dp.id_pasaje, LOWER(ep.nombre_estado_pasaje) AS estado
                FROM detalles_pasajes dp
                INNER JOIN estados_pasajes ep ON dp.id_estado_pasaje = ep.id_estado_pasaje
                WHERE dp.id_detalle_pasaje = :id
                  AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id_detalle_pasaje]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            throw new Exception('El boleto no existe o ya fue anulado.');
        }
        if ($fila['estado'] === 'despachado') {
            throw new Exception('No se puede anular un boleto de un turno que ya fue despachado.');
        }

        $this->pdo->beginTransaction();
        try {
            $upd = $this->pdo->prepare(
                "UPDATE detalles_pasajes 
                 SET estado_detalle_pasaje = 0, id_elemento = NULL, update_detalle_pasaje = NOW() 
                 WHERE id_detalle_pasaje = :id"
            );
            $upd->execute([':id' => $id_detalle_pasaje]);

            $this->recalcularVenta($fila['id_pasaje']);
            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
        return true;
    }

    /**
     * Anula la venta completa: todos sus boletos activos + la cabecera.
     * Lanza Exception si alguno ya fue despachado.
     */
    public function anularVenta($id_pasaje) {
        $sql = "SELECT COUNT(*) AS total,
                       SUM(CASE WHEN LOWER(ep.nombre_estado_pasaje) = 'despachado' THEN 1 ELSE 0 END) AS despachados
                FROM detalles_pasajes dp
                INNER JOIN estados_pasajes ep ON dp.id_estado_pasaje = ep.id_estado_pasaje
                WHERE dp.id_pasaje = :id
                  AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id_pasaje]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila || intval($fila['total']) === 0) {
            throw new Exception('La venta no existe o ya fue anulada.');
        }
        if (intval($fila['despachados']) > 0) {
            throw new Exception('No se puede anular la venta: tiene boletos de un turno ya despachado.');
        }

        $this->pdo->beginTransaction();
        try {
            $upd = $this->pdo->prepare(
                "UPDATE detalles_pasajes 
                 SET estado_detalle_pasaje = 0, id_elemento = NULL, update_detalle_pasaje = NOW() 
                 WHERE id_pasaje = :id 
                   AND (estado_detalle_pasaje = 1 OR estado_detalle_pasaje IS NULL)"
            );
            $upd->execute([':id' => $id_pasaje]);

            $this->recalcularVenta($id_pasaje);
            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
        return true;
    }

    /**
     * Recalcula pasajes.total_pasaje a partir de los detalles activos y marca
     * la cabecera como inactiva si ya no le queda ningún boleto.
     */
    private function recalcularVenta($id_pasaje) {
        $sql = "UPDATE pasajes 
                SET total_pasaje = (
                        SELECT IFNULL(SUM(dp1.precio_detalle_pasaje), 0)
                        FROM detalles_pasajes dp1
                        WHERE dp1.id_pasaje = :id_a
                          AND (dp1.estado_detalle_pasaje = 1 OR dp1.estado_detalle_pasaje IS NULL)
                    ),
                    estado_pasaje = IF((
                        SELECT COUNT(*)
                        FROM detalles_pasajes dp2
                        WHERE dp2.id_pasaje = :id_b
                          AND (dp2.estado_detalle_pasaje = 1 OR dp2.estado_detalle_pasaje IS NULL)
                    ) = 0, 0, 1),
                    update_pasaje = NOW()
                WHERE id_pasaje = :id_c";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_a' => $id_pasaje, ':id_b' => $id_pasaje, ':id_c' => $id_pasaje]);
    }
}
?>