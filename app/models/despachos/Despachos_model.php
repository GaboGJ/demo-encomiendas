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

    /**
     * Turnos "en turno" (todavía no despachados/cancelados) que salen desde
     * una sucursal, con los datos de vehículo/modelo/chofer ya resueltos.
     * Se usa en la venta de pasajes para que el cajero elija el chofer que
     * está en turno; si no elige ninguno, el pasaje queda "en espera".
     */
    public function getTurnosEnTurnoPorSucursal($id_sucursal_origen) {
        try {
            $sql = "SELECT 
                        t.id_turno,
                        t.precio_pasaje_turno,
                        t.fecha_salida_turno,
                        t.hora_salida_turno,
                        et.nombre_estado_turno,
                        s_dest.ciudad_sucursal AS ciudad_destino,
                        s_dest.nombre_sucursal AS nombre_sucursal_destino,
                        v.id_vehiculo,
                        v.numero_interno_vehiculo,
                        v.placa_vehiculo,
                        mo.id_modelo,
                        mo.nombre_modelo,
                        mo.total_asientos_modelo,
                        CONCAT(p_chof.nombre_persona, ' ', p_chof.apellido_paterno_persona) AS nombre_chofer,
                        (SELECT COUNT(dp.id_detalle_pasaje)
                           FROM detalles_pasajes dp
                          WHERE dp.id_turno = t.id_turno
                            AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)) AS asientos_ocupados
                    FROM turnos t
                    INNER JOIN estados_turnos et ON t.id_estado_turno = et.id_estado_turno
                    INNER JOIN sucursales s_dest ON t.id_sucursal_destino = s_dest.id_sucursal
                    INNER JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
                    INNER JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                    INNER JOIN modelos mo ON v.id_modelo = mo.id_modelo
                    INNER JOIN choferes ch ON vc.id_chofer = ch.id_chofer
                    INNER JOIN personas p_chof ON ch.id_persona = p_chof.id_persona
                    WHERE t.id_sucursal_origen = :id_origen
                      AND (t.estado_turno = 1 OR t.estado_turno IS NULL)
                      AND (LOWER(et.nombre_estado_turno) LIKE '%turno%' OR LOWER(et.nombre_estado_turno) = 'pendiente')
                    ORDER BY t.id_turno DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_origen' => $id_sucursal_origen]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Un turno puntual con su vehículo/modelo/chofer/ruta resueltos, para
     * calcular el precio y cargar el plano de asientos correspondiente.
     */
    public function obtenerTurnoConVehiculo($id_turno) {
        try {
            $sql = "SELECT 
                        t.id_turno,
                        t.id_sucursal_origen,
                        t.id_sucursal_destino,
                        t.precio_pasaje_turno,
                        t.fecha_salida_turno,
                        t.hora_salida_turno,
                        et.nombre_estado_turno,
                        s_dest.ciudad_sucursal AS ciudad_destino,
                        s_dest.nombre_sucursal AS nombre_sucursal_destino,
                        v.numero_interno_vehiculo,
                        v.placa_vehiculo,
                        mo.id_modelo,
                        mo.nombre_modelo,
                        mo.total_asientos_modelo,
                        CONCAT(p_chof.nombre_persona, ' ', p_chof.apellido_paterno_persona) AS nombre_chofer
                    FROM turnos t
                    INNER JOIN estados_turnos et ON t.id_estado_turno = et.id_estado_turno
                    INNER JOIN sucursales s_dest ON t.id_sucursal_destino = s_dest.id_sucursal
                    INNER JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
                    INNER JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                    INNER JOIN modelos mo ON v.id_modelo = mo.id_modelo
                    INNER JOIN choferes ch ON vc.id_chofer = ch.id_chofer
                    INNER JOIN personas p_chof ON ch.id_persona = p_chof.id_persona
                    WHERE t.id_turno = :id_turno
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_turno' => $id_turno]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

     /**
     * Resuelve un id de estado por NOMBRE (acepta varios nombres alternativos,
     * se usa el primero que exista). Si no existe ninguno lanza Exception con
     * un mensaje claro, en vez de escribir un id equivocado en silencio.
     * $tabla/$colId/$colNombre son constantes internas (nunca vienen del usuario).
     */
    private function idEstado($tabla, $colId, $colNombre, array $nombres) {
        foreach ($nombres as $n) {
            $st = $this->pdo->prepare("SELECT $colId FROM $tabla WHERE LOWER($colNombre) = :n LIMIT 1");
            $st->execute([':n' => mb_strtolower($n, 'UTF-8')]);
            $r = $st->fetch(PDO::FETCH_NUM);
            if ($r) return (int)$r[0];
        }
        throw new Exception("Falta registrar en la tabla $tabla alguno de estos estados: " . implode(', ', $nombres));
    }
 
    /** Misma regla que ya usan despachos/index.php y getTurnosEnTurnoPorSucursal. */
    public function esTurnoAbierto($nombreEstado) {
        $n = mb_strtolower((string)$nombreEstado, 'UTF-8');
        return (strpos($n, 'turno') !== false || $n === 'pendiente');
    }
 
    /* ===================== CONSULTAS ===================== */
 
    /** Turno completo (vehículo, chofer con licencia/celular, ruta y estado). */
    public function obtenerTurnoCompleto($id_turno) {
        try {
            $sql = "SELECT 
                        t.id_turno, t.id_sucursal_origen, t.id_sucursal_destino,
                        t.fecha_salida_turno, t.hora_salida_turno, t.precio_pasaje_turno,
                        et.nombre_estado_turno,
                        s_o.ciudad_sucursal AS ciudad_origen,
                        s_o.nombre_sucursal AS nombre_sucursal_origen,
                        s_o.direccion_sucursal AS direccion_sucursal_origen,
                        sn.nombre_sindicato, sn.telefono_sindicato,
                        s_d.ciudad_sucursal AS ciudad_destino,
                        s_d.nombre_sucursal AS nombre_sucursal_destino,
                        v.numero_interno_vehiculo, v.placa_vehiculo, v.color_vehiculo,
                        mo.nombre_modelo, mo.total_asientos_modelo,
                        CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona) AS nombre_chofer,
                        ch.licencia_chofer,
                        p.telefono_persona AS celular_chofer
                    FROM turnos t
                    INNER JOIN estados_turnos et ON t.id_estado_turno = et.id_estado_turno
                    INNER JOIN sucursales s_o ON t.id_sucursal_origen = s_o.id_sucursal
                    LEFT JOIN sindicatos sn ON s_o.id_sindicato = sn.id_sindicato
                    INNER JOIN sucursales s_d ON t.id_sucursal_destino = s_d.id_sucursal
                    LEFT JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
                    LEFT JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                    LEFT JOIN modelos mo ON v.id_modelo = mo.id_modelo
                    LEFT JOIN choferes ch ON vc.id_chofer = ch.id_chofer
                    LEFT JOIN personas p ON ch.id_persona = p.id_persona
                    WHERE t.id_turno = :id AND (t.estado_turno = 1 OR t.estado_turno IS NULL)
                    LIMIT 1";
            $st = $this->pdo->prepare($sql);
            $st->execute([':id' => $id_turno]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
 
    /** Pasajeros ya vendidos para el turno (solo lectura en Despachos). */
    public function getPasajerosPorTurno($id_turno) {
        try {
            $sql = "SELECT 
                        dp.id_detalle_pasaje,
                        p.codigo_pasaje,
                        e.dato_elemento AS asiento,
                        CONCAT(pas.nombre_persona, ' ', pas.apellido_paterno_persona) AS pasajero,
                        pas.carnet_persona AS pasajero_ci,
                        dp.precio_detalle_pasaje
                    FROM detalles_pasajes dp
                    INNER JOIN pasajes p ON dp.id_pasaje = p.id_pasaje
                    INNER JOIN personas pas ON dp.id_persona_pasajero = pas.id_persona
                    LEFT JOIN elementos e ON dp.id_elemento = e.id_elemento
                    WHERE dp.id_turno = :id
                      AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)
                      AND (p.estado_pasaje = 1 OR p.estado_pasaje IS NULL)
                    ORDER BY dp.id_detalle_pasaje ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':id' => $id_turno]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
 
    private function selectEncomiendasBase() {
        return "SELECT 
                    e.id_encomienda, e.guia_encomienda, e.declaracion_encomienda, e.monto_encomienda,
                    CAST(e.estado_pago_encomienda AS UNSIGNED) AS pagado,
                    CONCAT(pr.nombre_persona, ' ', pr.apellido_paterno_persona) AS remitente,
                    CONCAT(pd.nombre_persona, ' ', pd.apellido_paterno_persona) AS destinatario,
                    pd.telefono_persona AS destinatario_celular,
                    (SELECT COUNT(*) FROM detalles_encomiendas de 
                      WHERE de.id_encomienda = e.id_encomienda 
                        AND (de.estado_detalle_encomienda = 1 OR de.estado_detalle_encomienda IS NULL)) AS total_bultos,
                    (SELECT IFNULL(SUM(de.peso_detalle_encomienda), 0) FROM detalles_encomiendas de 
                      WHERE de.id_encomienda = e.id_encomienda 
                        AND (de.estado_detalle_encomienda = 1 OR de.estado_detalle_encomienda IS NULL)) AS peso_total
                FROM encomiendas e
                INNER JOIN personas pr ON e.id_persona_remitente = pr.id_persona
                INNER JOIN personas pd ON e.id_persona_destinatario = pd.id_persona ";
    }
 
    /** Guías ya vinculadas a este turno. */
    public function getEncomiendasAsignadas($id_turno) {
        try {
            $sql = $this->selectEncomiendasBase() .
                   "WHERE e.id_turno = :t AND (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)
                    ORDER BY e.id_encomienda ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':t' => $id_turno]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
 
    /**
     * Guías sin turno que salen de esta sucursal hacia el destino del turno.
     * Las recepciones quedan fuera solas: su origen es una sucursal externa.
     */
    public function getEncomiendasPendientes($id_origen, $id_destino) {
        try {
            $sql = $this->selectEncomiendasBase() .
                   "WHERE e.id_sucursal_origen = :o AND e.id_sucursal_destino = :d
                      AND e.id_turno IS NULL
                      AND (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)
                    ORDER BY e.id_encomienda ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':o' => $id_origen, ':d' => $id_destino]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
 
    /* ===================== ESCRITURA ===================== */
 
    /**
     * Vincula guías al turno. Revalida en el UPDATE (misma ruta, sin turno,
     * activas): si alguna ya no cumple, se revierte todo.
     */
    public function asignarEncomiendas($id_turno, $id_origen, $id_destino, array $ids) {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) throw new Exception('Seleccione al menos una guía.');
 
        $idEstado = $this->idEstado('estados_encomiendas', 'id_estado_encomienda', 'nombre_estado_encomienda', ['asignado']);
        $in = implode(',', array_fill(0, count($ids), '?'));
 
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE encomiendas 
                    SET id_turno = ?, id_estado_encomienda = ?, update_encomienda = NOW()
                    WHERE id_encomienda IN ($in)
                      AND id_turno IS NULL
                      AND id_sucursal_origen = ? AND id_sucursal_destino = ?
                      AND (estado_encomienda = 1 OR estado_encomienda IS NULL)";
            $st = $this->pdo->prepare($sql);
            $st->execute(array_merge([$id_turno, $idEstado], $ids, [$id_origen, $id_destino]));
 
            if ($st->rowCount() !== count($ids)) {
                throw new Exception('Alguna guía ya fue asignada a otro turno o no corresponde a esta ruta. Actualice la lista.');
            }
            $this->pdo->commit();
            return count($ids);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
 
    /**
     * Desvincula una guía del turno (solo antes de despachar). Vuelve al
     * estado inicial 1, el mismo que le pone Encomiendas_model::insertarEncomienda().
     */
    public function quitarEncomienda($id_turno, $id_encomienda) {
        $sql = "UPDATE encomiendas 
                SET id_turno = NULL, id_estado_encomienda = 1, update_encomienda = NOW()
                WHERE id_encomienda = :e AND id_turno = :t";
        $st = $this->pdo->prepare($sql);
        $st->execute([':e' => $id_encomienda, ':t' => $id_turno]);
        if ($st->rowCount() === 0) {
            throw new Exception('La guía no pertenece a este turno.');
        }
        return true;
    }
 
    /**
     * Despacha el turno en UNA transacción: hora de salida, estado del turno,
     * pasajes (detalles) y encomiendas. El estado "Despachado" de los detalles
     * es el que bloquea luego las anulaciones en Pasajes_model.
     */
    public function despacharTurno($id_turno, $id_sucursal_origen) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "SELECT et.nombre_estado_turno 
                 FROM turnos t INNER JOIN estados_turnos et ON t.id_estado_turno = et.id_estado_turno
                 WHERE t.id_turno = :t AND t.id_sucursal_origen = :o 
                   AND (t.estado_turno = 1 OR t.estado_turno IS NULL)
                 FOR UPDATE"
            );
            $st->execute([':t' => $id_turno, ':o' => $id_sucursal_origen]);
            $fila = $st->fetch(PDO::FETCH_ASSOC);
 
            if (!$fila) throw new Exception('El turno no existe.');
            if (!$this->esTurnoAbierto($fila['nombre_estado_turno'])) {
                throw new Exception('El turno ya fue despachado o cancelado.');
            }
 
            $cP = $this->pdo->prepare("SELECT COUNT(*) FROM detalles_pasajes 
                WHERE id_turno = :t AND (estado_detalle_pasaje = 1 OR estado_detalle_pasaje IS NULL)");
            $cP->execute([':t' => $id_turno]);
            $cE = $this->pdo->prepare("SELECT COUNT(*) FROM encomiendas 
                WHERE id_turno = :t AND (estado_encomienda = 1 OR estado_encomienda IS NULL)");
            $cE->execute([':t' => $id_turno]);
 
            if ((int)$cP->fetchColumn() === 0 && (int)$cE->fetchColumn() === 0) {
                throw new Exception('El turno no tiene pasajes ni encomiendas. No hay nada que despachar.');
            }
 
            $idTurnoDesp = $this->idEstado('estados_turnos', 'id_estado_turno', 'nombre_estado_turno', ['despachado']);
            $idPasajeDesp = $this->idEstado('estados_pasajes', 'id_estado_pasaje', 'nombre_estado_pasaje', ['despachado']);
            $idEncDesp = $this->idEstado('estados_encomiendas', 'id_estado_encomienda', 'nombre_estado_encomienda', ['enviado', 'despachado', 'en Ruta', 'en transito']);
 
            $this->pdo->prepare("UPDATE turnos SET fecha_salida_turno = CURDATE(), hora_salida_turno = :h, id_estado_turno = :e, update_turno = NOW() WHERE id_turno = :t")
                ->execute([':h' => date('H:i:s'), ':e' => $idTurnoDesp, ':t' => $id_turno]);
            //$this->pdo->prepare("UPDATE turnos SET hora_salida_turno = :h, id_estado_turno = :e, update_turno = NOW() WHERE id_turno = :t")
               // ->execute([':h' => date('H:i:s'), ':e' => $idTurnoDesp, ':t' => $id_turno]);
 
            $this->pdo->prepare("UPDATE detalles_pasajes SET id_estado_pasaje = :e, update_detalle_pasaje = NOW()
                WHERE id_turno = :t AND (estado_detalle_pasaje = 1 OR estado_detalle_pasaje IS NULL)")
                ->execute([':e' => $idPasajeDesp, ':t' => $id_turno]);
 
            $this->pdo->prepare("UPDATE encomiendas SET id_estado_encomienda = :e, update_encomienda = NOW()
                WHERE id_turno = :t AND (estado_encomienda = 1 OR estado_encomienda IS NULL)")
                ->execute([':e' => $idEncDesp, ':t' => $id_turno]);
 
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getNombreUsuario($id_usuario) {
        try {
            $st = $this->pdo->prepare(
                "SELECT CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona)
                FROM usuarios u INNER JOIN personas p ON u.id_persona = p.id_persona
                WHERE u.id_usuario = :id LIMIT 1");
            $st->execute([':id' => $id_usuario]);
            return $st->fetchColumn() ?: '';
        } catch (PDOException $e) {
            return '';
        }
    }
}
?>