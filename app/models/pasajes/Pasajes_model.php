<?php
class Pasajes_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Obtiene los pasajes asociados a una sucursal de origen
     */
    public function getPasajesPorSucursal($id_sucursal_origen) {
        try {
            $sql = "SELECT 
                        dp.id_detalle_pasaje,
                        p.codigo_pasaje,
                        CONCAT(per.nombre_persona, ' ', per.apellido_paterno_persona, ' ', IFNULL(per.apellido_materno_persona, '')) AS pasajero,
                        so.ciudad_sucursal AS origen,
                        sd.ciudad_sucursal AS destino,
                        e.dato_elemento AS numero_asiento,
                        v.numero_interno_vehiculo,
                        m.nombre_modelo AS tipo_bus,
                        dp.precio_detalle_pasaje,
                        mp.nombre_metodo_pago,
                        ep.nombre_estado_pasaje
                    FROM detalles_pasajes dp
                    INNER JOIN pasajes p ON dp.id_pasaje = p.id_pasaje
                    INNER JOIN personas per ON dp.id_persona_pasajero = per.id_persona
                    INNER JOIN turnos t ON dp.id_turno = t.id_turno
                    INNER JOIN sucursales so ON t.id_sucursal_origen = so.id_sucursal
                    INNER JOIN sucursales sd ON t.id_sucursal_destino = sd.id_sucursal
                    INNER JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
                    INNER JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                    INNER JOIN modelos m ON v.id_modelo = m.id_modelo
                    INNER JOIN elementos e ON dp.id_elemento = e.id_elemento
                    INNER JOIN metodos_pagos mp ON p.id_metodo_pago = mp.id_metodo_pago
                    INNER JOIN estados_pasajes ep ON dp.id_estado_pasaje = ep.id_estado_pasaje
                    WHERE t.id_sucursal_origen = :id_origen AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)
                    ORDER BY dp.id_detalle_pasaje DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_origen' => $id_sucursal_origen]);
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
     * Inserta la cabecera de la venta de pasaje
     */
    public function insertarPasaje($data) {
        $codigo = $this->generarCodigoCorrelativo();

        $sql = "INSERT INTO pasajes 
            (codigo_pasaje, id_usuario, monto_total_pasaje, id_metodo_pago, estado_pasaje, create_pasaje) 
            VALUES 
            (:codigo, :usuario, :monto, :metodo_pago, 1, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':codigo', $codigo);
        $stmt->bindValue(':usuario', $data['id_usuario']);
        $stmt->bindValue(':monto', $data['monto_total_pasaje']);
        $stmt->bindValue(':metodo_pago', $data['id_metodo_pago']);

        $stmt->execute();

        return [
            'id_pasaje' => $this->pdo->lastInsertId(),
            'codigo'    => $codigo
        ];
    }

    /**
     * Inserta el detalle de un boleto emitido
     */
    public function insertarDetallePasaje($data) {
        $sql = "INSERT INTO detalles_pasajes 
            (id_pasaje, id_turno, id_persona_pasajero, id_elemento, precio_detalle_pasaje, id_estado_pasaje, estado_detalle_pasaje, create_detalle_pasaje) 
            VALUES 
            (:id_pasaje, :id_turno, :id_pasajero, :id_elemento, :precio, :id_estado_pasaje, 1, NOW())";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_pasaje'        => $data['id_pasaje'],
            ':id_turno'         => $data['id_turno'],
            ':id_pasajero'      => $data['id_persona_pasajero'],
            ':id_elemento'      => $data['id_elemento'],
            ':precio'           => $data['precio_detalle_pasaje'],
            ':id_estado_pasaje' => $data['id_estado_pasaje'] ?? 1
        ]);
    }

    /**
     * Obtiene los datos detallados de un pasaje para impresión o consulta
     */
    public function obtenerPasajeCompleto($id_detalle_pasaje) {
        try {
            $sql = "SELECT 
                        dp.*,
                        p.codigo_pasaje,
                        p.monto_total_pasaje,
                        CONCAT(IFNULL(per.nombre_persona,''), ' ', IFNULL(per.apellido_paterno_persona,''), ' ', IFNULL(per.apellido_materno_persona, '')) AS pasajero_nombre,
                        per.carnet_persona AS pasajero_ci,
                        per.telefono_persona AS pasajero_celular,
                        so.nombre_sucursal AS origen_nombre,
                        so.ciudad_sucursal AS origen_ciudad,
                        sd.nombre_sucursal AS destino_nombre,
                        sd.ciudad_sucursal AS destino_ciudad,
                        t.hora_partida_turno,
                        t.fecha_turno,
                        e.dato_elemento AS numero_asiento,
                        v.numero_interno_vehiculo,
                        m.nombre_modelo AS tipo_bus,
                        mp.nombre_metodo_pago,
                        ep.nombre_estado_pasaje
                    FROM detalles_pasajes dp
                    INNER JOIN pasajes p ON dp.id_pasaje = p.id_pasaje
                    LEFT JOIN personas per ON dp.id_persona_pasajero = per.id_persona
                    LEFT JOIN turnos t ON dp.id_turno = t.id_turno
                    LEFT JOIN sucursales so ON t.id_sucursal_origen = so.id_sucursal
                    LEFT JOIN sucursales sd ON t.id_sucursal_destino = sd.id_sucursal
                    LEFT JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
                    LEFT JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                    LEFT JOIN modelos m ON v.id_modelo = m.id_modelo
                    LEFT JOIN elementos e ON dp.id_elemento = e.id_elemento
                    LEFT JOIN metodos_pagos mp ON p.id_metodo_pago = mp.id_metodo_pago
                    LEFT JOIN estados_pasajes ep ON dp.id_estado_pasaje = ep.id_estado_pasaje
                    WHERE dp.id_detalle_pasaje = :id
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id_detalle_pasaje]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Anula un boleto emitido cambiando su estado a 0
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