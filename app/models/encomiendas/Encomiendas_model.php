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
                        e.estado_pago_encomienda,
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
                        e.estado_pago_encomienda,
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
        $stmt->execute([
            ':guia'        => $guia,
            ':origen'      => $data['id_sucursal_origen'],
            ':destino'     => $data['id_sucursal_destino'],
            ':remitente'   => $data['id_persona_remitente'],
            ':destinatario'=> $data['id_persona_destinatario'],
            ':turno'       => $data['id_turno'] ?? null,
            ':usuario'     => $data['id_usuario'],
            ':declaracion' => $data['declaracion_encomienda'],
            ':monto'       => $data['monto_encomienda'],
            ':estado_pago' => $data['estado_pago_encomienda'],
            ':metodo_pago' => $data['id_metodo_pago']
        ]);

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
}
?>