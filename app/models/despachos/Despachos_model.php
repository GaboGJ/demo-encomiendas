<?php
class Despachos_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Obtiene los turnos/despachos filtrados por sucursal de origen
     */
    public function getDespachosPorSucursal($id_sucursal_origen) {
        try {
            $sql = "SELECT 
                        t.id_turno,
                        t.fecha_salida_turno,
                        t.hora_salida_turno,
                        t.precio_pasaje_turno,
                        et.id_estado_turno,
                        et.nombre_estado_turno,
                        s_orig.ciudad_sucursal AS ciudad_origen,
                        s_dest.ciudad_sucursal AS ciudad_destino,
                        v.numero_interno_vehiculo,
                        m.nombre_modelo,
                        m.total_asientos_modelo,
                        CONCAT(p_chof.nombre_persona, ' ', p_chof.apellido_paterno_persona) AS nombre_chofer,
                        (SELECT COUNT(dp.id_detalle_pasaje) 
                         FROM detalles_pasajes dp 
                         WHERE dp.id_turno = t.id_turno AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)) AS total_pasajeros,
                        (SELECT IFNULL(SUM(dp.precio_detalle_pasaje), 0) 
                         FROM detalles_pasajes dp 
                         WHERE dp.id_turno = t.id_turno AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)) AS total_monto_pasajes,
                        (SELECT COUNT(e.id_encomienda) 
                         FROM encomiendas e 
                         WHERE e.id_turno = t.id_turno AND (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)) AS total_guias,
                        (SELECT IFNULL(SUM(e.monto_encomienda), 0) 
                         FROM encomiendas e 
                         WHERE e.id_turno = t.id_turno AND (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)) AS total_monto_encomiendas
                    FROM turnos t
                    INNER JOIN estados_turnos et ON t.id_estado_turno = et.id_estado_turno
                    INNER JOIN sucursales s_orig ON t.id_sucursal_origen = s_orig.id_sucursal
                    INNER JOIN sucursales s_dest ON t.id_sucursal_destino = s_dest.id_sucursal
                    LEFT JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
                    LEFT JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                    LEFT JOIN modelos m ON v.id_modelo = m.id_modelo
                    LEFT JOIN choferes ch ON vc.id_chofer = ch.id_chofer
                    LEFT JOIN personas p_chof ON ch.id_persona = p_chof.id_persona
                    WHERE t.id_sucursal_origen = :id_origen 
                      AND (t.estado_turno = 1 OR t.estado_turno IS NULL)
                    ORDER BY t.id_turno DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_origen' => $id_sucursal_origen]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene las sucursales disponibles exceptuando la de origen
     */
    public function getSucursalesDestino($id_sucursal_origen) {
        try {
            $sql = "SELECT 
                    s.id_sucursal, 
                    s.nombre_sucursal, 
                    s.ciudad_sucursal,
                    IFNULL(pp.base_precio_pasaje, 0.00) AS precio_base
                FROM sucursales s
                LEFT JOIN precios_pasajes pp 
                       ON pp.id_sucursal_origen = :id_origen 
                      AND pp.id_sucursal_destino = s.id_sucursal 
                      AND (pp.estado_precio_pasaje = 1 OR pp.estado_precio_pasaje IS NULL)
                WHERE s.id_sucursal != :id_origen 
                  AND (s.estado_sucursal = 1 OR s.estado_sucursal IS NULL)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_origen' => $id_sucursal_origen]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene los pares Vehículo-Chofer activos
     */
    public function getVehiculosChoferes() {
        try {
            $sql = "SELECT 
                        vc.id_vehiculo_chofer,
                        v.numero_interno_vehiculo,
                        v.placa_vehiculo,
                        m.nombre_modelo,
                        m.total_asientos_modelo,
                        CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona) AS nombre_chofer
                    FROM vehiculos_choferes vc
                    INNER JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                    INNER JOIN modelos m ON v.id_modelo = m.id_modelo
                    INNER JOIN choferes ch ON vc.id_chofer = ch.id_chofer
                    INNER JOIN personas p ON ch.id_persona = p.id_persona
                    WHERE (vc.estado_vehiculo_chofer = 1 OR vc.estado_vehiculo_chofer IS NULL)
                      AND (v.estado_vehiculo = 1 OR v.estado_vehiculo IS NULL)
                    ORDER BY v.numero_interno_vehiculo ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Registra un nuevo despacho/turno
     */
    public function crearTurno($data) {
        // OJO: ya no se atrapa la PDOException aquí; se deja subir al controlador
        // para poder mostrar el mensaje real vía Flash/SweetAlert, en vez de
        // fallar en silencio devolviendo false sin explicación (así se nos
        // escapó antes el error de la columna id_usuario).
        $sql = "INSERT INTO turnos (
                    fecha_salida_turno, 
                    hora_salida_turno, 
                    precio_pasaje_turno, 
                    id_sucursal_origen, 
                    id_sucursal_destino, 
                    id_vehiculo_chofer, 
                    id_estado_turno, 
                    id_usuario,
                    estado_turno,
                    create_turno
                ) VALUES (
                    :fecha, 
                    :hora, 
                    :precio, 
                    :origen, 
                    :destino, 
                    :vehiculo_chofer, 
                    :estado_turno, 
                    :usuario,
                    1,
                    NOW()
                )";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':fecha'           => $data['fecha_salida'],
            // hora_salida_turno es NULLable justo para este caso: la hora NO se
            // define al crear el turno, se registrará recién cuando se despache.
            ':hora'            => $data['hora_salida'] ?? null,
            ':precio'          => $data['precio_pasaje'],
            ':origen'          => $data['id_sucursal_origen'],
            ':destino'         => $data['id_sucursal_destino'],
            ':vehiculo_chofer' => $data['id_vehiculo_chofer'],
            ':estado_turno'    => $data['id_estado_turno'] ?? 1,
            // turnos.id_usuario es NOT NULL: faltaba este bind y por eso el
            // INSERT siempre fallaba (silenciosamente, por el catch de abajo).
            ':usuario'         => $data['id_usuario']
        ]);
    }
}
?>