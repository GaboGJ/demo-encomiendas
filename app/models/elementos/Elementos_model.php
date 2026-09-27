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
     *
     * NOTA: se mantiene por compatibilidad con código existente. Para pintar
     * el plano visual completo (asientos + elementos especiales como chofer,
     * baño, escalera, etc.) usar getElementosPorModelo().
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

    /**
     * Dimensiones (filas x columnas) de cada piso configurado para un modelo,
     * necesarias para dibujar la grilla del plano en el front-end (igual que
     * en la pantalla de "Configuración de Asientos").
     */
    public function getPisosPorModelo($id_modelo) {
        try {
            $sql = "SELECT 
                        id_piso,
                        numero_piso,
                        nombre_piso,
                        filas_piso,
                        columnas_piso
                    FROM pisos
                    WHERE id_modelo = :id_modelo
                      AND (estado_piso = 1 OR estado_piso IS NULL)
                    ORDER BY numero_piso ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_modelo' => $id_modelo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Plano COMPLETO de un modelo: TODOS los elementos (asientos y también
     * los especiales: Chofer, Baño, Escalera, Televisión, etc.), con su
     * posición (fila/columna/piso), para poder dibujar visualmente el bus
     * tal cual se armó en "Configuración de Asientos", en vez de listar los
     * asientos en una tabla plana.
     *
     * Cada elemento devuelve:
     *   - tipo_elemento: nombre del tipo ("Asiento", "Chofer", "Baño", ...)
     *   - es_asiento: true/false, para que el front-end sepa cuáles son
     *     seleccionables y cuáles son solo decorativos/informativos.
     *   - ocupado / id_detalle_pasaje / pasajero_nombre: solo aplican a
     *     asientos, y solo si se indica $id_turno (igual que en
     *     getMapaAsientosPorModelo).
     */
    public function getElementosPorModelo($id_modelo, $id_turno = null) {
        try {
            $sql = "SELECT 
                        e.id_elemento,
                        e.id_piso,
                        e.dato_elemento,
                        e.fila_elemento,
                        e.columna_elemento,
                        p.numero_piso,
                        te.nombre_tipo_elemento AS tipo_elemento,
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
                      AND (e.estado_elemento = 1 OR e.estado_elemento IS NULL)
                    ORDER BY p.numero_piso ASC, e.fila_elemento ASC, e.columna_elemento ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_modelo' => $id_modelo,
                ':id_turno'  => $id_turno
            ]);
            $elementos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Textos de elementos especiales que NUNCA deben tratarse como
            // asientos seleccionables, aunque por error de carga de datos
            // hayan quedado con un id_tipo_elemento que sí contiene la
            // palabra "asiento" (ej. el tipo real 'Asiento Chofer').
            $textosEspeciales = ['chofer', 'baño', 'bano', 'escalera', 'television', 'televisión', 'tv', 'puerta', 'pasillo'];

            foreach ($elementos as &$el) {
                $tipoLower = mb_strtolower(trim($el['tipo_elemento']), 'UTF-8');
                $datoLower = mb_strtolower(trim($el['dato_elemento']), 'UTF-8');

                // 'Asiento Chofer' contiene la palabra "asiento" pero NO es
                // un asiento vendible: se excluye si el propio nombre del
                // tipo menciona "chofer" (o cualquier otro elemento
                // especial), y como refuerzo también si el dato coincide
                // con alguno de esos textos.
                $excluirPorTipo = false;
                foreach ($textosEspeciales as $palabra) {
                    if ($palabra !== 'pasillo' && strpos($tipoLower, $palabra) !== false) {
                        $excluirPorTipo = true;
                        break;
                    }
                }
                $excluirPorDato = in_array($datoLower, $textosEspeciales, true);

                $esAsiento = (strpos($tipoLower, 'asiento') !== false) && !$excluirPorTipo && !$excluirPorDato;
                $el['es_asiento'] = $esAsiento;
                $el['ocupado']    = $esAsiento && !empty($el['id_detalle_pasaje']);
            }
            unset($el);

            return $elementos;
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>