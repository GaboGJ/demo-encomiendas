<?php
/**
 * Elementos_model
 *
 * Gestiona la tabla `elementos` (los asientos y demás elementos del plano de
 * un vehículo: asiento, chofer, baño, escalera, televisión, etc., ubicados
 * en filas/columnas dentro de un `piso`, y el `piso` pertenece a un `modelo`
 * de vehículo). Se separa de Pasajes_model porque el plano de asientos es un
 * concepto propio de vehículos/modelos que otras pantallas (configuración de
 * asientos, despachos) también van a necesitar reutilizar.
 */
class Elementos_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Plano de asientos (solo elementos de tipo "asiento") de un modelo de
     * vehículo. Si se indica $id_turno, cada asiento vendido en ESE turno se
     * marca como ocupado (trae el id_detalle_pasaje y el nombre del
     * pasajero); si no se indica, devuelve el plano completo sin marcar
     * ocupación (todos disponibles).
     */
    public function getMapaAsientosPorModelo($id_modelo, $id_turno = null) {
        try {
            $sql = "SELECT 
                        e.id_elemento,
                        e.dato_elemento,
                        e.fila_elemento,
                        e.columna_elemento,
                        p.numero_piso,
                        dp.id_detalle_pasaje,
                        dp.id_persona_pasajero,
                        CONCAT(per.nombre_persona, ' ', per.apellido_paterno_persona) AS pasajero_nombre
                    FROM elementos e
                    INNER JOIN pisos p ON e.id_piso = p.id_piso
                    INNER JOIN tipos_elementos te ON e.id_tipo_elemento = te.id_tipo_elemento
                    LEFT JOIN detalles_pasajes dp 
                           ON dp.id_elemento = e.id_elemento 
                          AND dp.id_turno = :id_turno
                          AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)
                    LEFT JOIN personas per ON dp.id_persona_pasajero = per.id_persona
                    WHERE p.id_modelo = :id_modelo
                      AND LOWER(te.nombre_tipo_elemento) LIKE '%asiento%'
                      AND (e.estado_elemento = 1 OR e.estado_elemento IS NULL)
                    ORDER BY p.numero_piso ASC, e.fila_elemento ASC, e.columna_elemento ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_modelo' => $id_modelo,
                ':id_turno'  => $id_turno
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>