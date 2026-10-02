<?php
/**
 * Sindicatos_model
 * CRUD de sindicatos asociados con borrado suave (delete_sindicato + estado_sindicato = 0).
 *
 * Notas de la BD:
 *  - UK_nombre_sindicato: el nombre es UNIQUE incluso entre sindicatos eliminados,
 *    por eso la validación de duplicados NO filtra delete_sindicato (igual que Auth_model).
 *  - El NIT se guarda en personeria_sindicato.
 *  - El sindicato principal (es_principal_sindicato = 1) lo crea el registro de Auth;
 *    desde este módulo todo sindicato nuevo se crea como NO principal.
 * Las lecturas devuelven [] / null ante un error; las escrituras dejan subir la PDOException
 * para que el controlador la maneje (p. ej. 1062 = clave duplicada).
 */
class Sindicatos_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        if ($this->pdo) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    /** SELECT base: sindicato + conteo de sucursales y socios vigentes. BIT casteados a entero. */
    private function baseSelect() {
        return "SELECT
                    s.id_sindicato,
                    s.nombre_sindicato,
                    s.sigla_sindicato,
                    s.personeria_sindicato,
                    s.telefono_sindicato,
                    s.direccion_sindicato,
                    CAST(IFNULL(s.es_principal_sindicato, 0) AS UNSIGNED) AS es_principal_sindicato,
                    CAST(IFNULL(s.estado_sindicato, 1) AS UNSIGNED)       AS estado_sindicato,
                    s.create_sindicato,
                    s.update_sindicato,
                    s.delete_sindicato,
                    (SELECT COUNT(*) FROM sucursales su
                      WHERE su.id_sindicato = s.id_sindicato AND su.delete_sucursal IS NULL) AS total_sucursales,
                    (SELECT COUNT(*) FROM socios so
                      WHERE so.id_sindicato = s.id_sindicato AND so.delete_socio IS NULL)     AS total_socios
                FROM sindicatos s ";
    }

    /* ---------------- Lecturas ---------------- */

    /** Sindicatos no eliminados (activos e inactivos); el principal primero. */
    public function getSindicatos() {
        try {
            $sql = $this->baseSelect() . "WHERE s.delete_sindicato IS NULL
                                          ORDER BY es_principal_sindicato DESC, s.nombre_sindicato ASC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Sindicatos_model::getSindicatos: ' . $e->getMessage());
            return [];
        }
    }

    /** Papelera: sindicatos eliminados, el más reciente primero. */
    public function getEliminados() {
        try {
            $sql = $this->baseSelect() . "WHERE s.delete_sindicato IS NOT NULL
                                          ORDER BY s.delete_sindicato DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Sindicatos_model::getEliminados: ' . $e->getMessage());
            return [];
        }
    }

    public function contarEliminados() {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM sindicatos WHERE delete_sindicato IS NOT NULL")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /** Un sindicato por id. Por defecto solo vigentes; $incluirEliminados = true para la papelera. */
    public function obtenerSindicato($id, $incluirEliminados = false) {
        try {
            $sql = $this->baseSelect() . "WHERE s.id_sindicato = :id";
            if (!$incluirEliminados) {
                $sql .= " AND s.delete_sindicato IS NULL";
            }
            $stmt = $this->pdo->prepare($sql . " LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Sindicatos_model::obtenerSindicato: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ¿Otro sindicato (vigente O eliminado) usa ese nombre o ese NIT?
     * $excluirId permite ignorar el propio registro al editar/restaurar.
     * @return array|null ['campo' => 'nombre'|'nit', 'eliminado' => bool]
     */
    public function buscarDuplicado($nombre, $nit, $excluirId = 0) {
        $sql = "SELECT nombre_sindicato, personeria_sindicato, delete_sindicato
                FROM sindicatos
                WHERE (LOWER(nombre_sindicato) = :n OR personeria_sindicato = :p)
                  AND id_sindicato <> :x";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':n' => mb_strtolower($nombre, 'UTF-8'),
            ':p' => $nit,
            ':x' => (int)$excluirId
        ]);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // El nombre tiene prioridad sobre el NIT al informar
        foreach ($filas as $f) {
            if (mb_strtolower($f['nombre_sindicato'], 'UTF-8') === mb_strtolower($nombre, 'UTF-8')) {
                return ['campo' => 'nombre', 'eliminado' => $f['delete_sindicato'] !== null];
            }
        }
        foreach ($filas as $f) {
            if ($nit !== '' && strcasecmp((string)$f['personeria_sindicato'], $nit) === 0) {
                return ['campo' => 'nit', 'eliminado' => $f['delete_sindicato'] !== null];
            }
        }
        return null;
    }

    /* ---------------- Escrituras ---------------- */

    /**
     * Crea un sindicato asociado (no principal, activo).
     * $d: nombre, sigla, nit, telefono, direccion
     */
    public function crear($d) {
        $sql = "INSERT INTO sindicatos
                    (nombre_sindicato, sigla_sindicato, personeria_sindicato, telefono_sindicato,
                     direccion_sindicato, es_principal_sindicato, estado_sindicato, create_sindicato)
                VALUES (:n, :s, :p, :t, :d, 0, 1, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->parametros($d));
        return (int)$this->pdo->lastInsertId();
    }

    /** Actualiza los datos (no toca es_principal ni estado). Solo sindicatos no eliminados. */
    public function actualizar($id, $d) {
        $sql = "UPDATE sindicatos SET
                    nombre_sindicato     = :n,
                    sigla_sindicato      = :s,
                    personeria_sindicato = :p,
                    telefono_sindicato   = :t,
                    direccion_sindicato  = :d,
                    update_sindicato     = NOW()
                WHERE id_sindicato = :id AND delete_sindicato IS NULL";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($this->parametros($d) + [':id' => $id]);
    }

    /** Activa (1) o desactiva (0). El principal nunca se desactiva (también se exige en SQL). */
    public function cambiarEstado($id, $estado) {
        $sql = "UPDATE sindicatos SET estado_sindicato = :estado, update_sindicato = NOW()
                WHERE id_sindicato = :id AND delete_sindicato IS NULL";
        if ((int)$estado === 0) {
            $sql .= " AND IFNULL(es_principal_sindicato, 0) = 0";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':estado', (int)$estado, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Borrado suave: marca delete_sindicato y deja estado_sindicato = 0.
     * No renombra nada (UK_nombre_sindicato sigue ocupando el nombre: se recupera desde la papelera).
     * El principal no se puede eliminar (también se exige en SQL).
     */
    public function eliminarSindicato($id, $fyh_eliminacion) {
        $sql = "UPDATE sindicatos SET delete_sindicato = :fyh, estado_sindicato = 0
                WHERE id_sindicato = :id
                  AND delete_sindicato IS NULL
                  AND IFNULL(es_principal_sindicato, 0) = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':fyh', $fyh_eliminacion, PDO::PARAM_STR);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /** Saca el sindicato de la papelera y lo deja activo. */
    public function restaurar($id) {
        $sql = "UPDATE sindicatos SET delete_sindicato = NULL, estado_sindicato = 1, update_sindicato = NOW()
                WHERE id_sindicato = :id AND delete_sindicato IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function parametros($d) {
        return [
            ':n' => $d['nombre'],
            ':s' => $d['sigla'] !== '' ? $d['sigla'] : null,
            ':p' => $d['nit'],
            ':t' => $d['telefono'],
            ':d' => $d['direccion'] !== '' ? $d['direccion'] : null
        ];
    }
}
?>