<?php
/**
 * Socios_model - CRUD de socios con borrado suave.
 * BD: Uk_socio_persona (una persona = un socio, incluso eliminado) y UK_codigo_socio (único global).
 * Por eso los duplicados NO filtran delete_socio; se recupera desde la papelera.
 *
 * $id_sindicato = 0 significa "todos los sindicatos" (solo lo usa el sindicato principal).
 */
class Socios_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        if ($this->pdo) $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function baseSelect() {
        return "SELECT
                    s.id_socio, s.id_persona, s.id_sindicato, s.codigo_socio, s.fecha_afiliacion_socio,
                    CAST(IFNULL(s.estado_socio, 1) AS UNSIGNED) AS estado_socio,
                    s.create_socio, s.update_socio, s.delete_socio,
                    sn.nombre_sindicato, sn.sigla_sindicato,
                    CAST(IFNULL(sn.es_principal_sindicato, 0) AS UNSIGNED) AS sindicato_principal,
                    p.carnet_persona, p.nombre_persona, p.apellido_paterno_persona, p.apellido_materno_persona,
                    p.telefono_persona, p.direccion_persona,
                    TRIM(CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona, ' ', IFNULL(p.apellido_materno_persona, ''))) AS nombre_completo,
                    ch.id_chofer, ch.licencia_chofer, ch.categoria_licencia_chofer, ch.vencimiento_licencia_chofer,
                    DATEDIFF(ch.vencimiento_licencia_chofer, CURDATE()) AS dias_vencimiento,
                    (SELECT COUNT(*) FROM vehiculos v
                      WHERE v.id_socio = s.id_socio AND v.delete_vehiculo IS NULL) AS total_vehiculos
                FROM socios s
                INNER JOIN personas p ON s.id_persona = p.id_persona
                INNER JOIN sindicatos sn ON s.id_sindicato = sn.id_sindicato
                LEFT JOIN choferes ch ON ch.id_persona = s.id_persona AND ch.delete_chofer IS NULL ";
    }

    /** Condición opcional por sindicato (0 = todos). */
    private function filtro($id_sindicato, array &$p) {
        if ((int)$id_sindicato > 0) {
            $p[':s'] = (int)$id_sindicato;
            return " AND s.id_sindicato = :s";
        }
        return '';
    }

    public function getSocios($id_sindicato = 0) {
        try {
            $p = [];
            $sql = $this->baseSelect() . "WHERE s.delete_socio IS NULL" . $this->filtro($id_sindicato, $p)
                 . " ORDER BY sn.nombre_sindicato ASC, s.id_socio DESC";
            $st = $this->pdo->prepare($sql);
            $st->execute($p);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { error_log('Socios_model::getSocios: ' . $e->getMessage()); return []; }
    }

    public function getEliminados($id_sindicato = 0) {
        try {
            $p = [];
            $sql = $this->baseSelect() . "WHERE s.delete_socio IS NOT NULL" . $this->filtro($id_sindicato, $p)
                 . " ORDER BY s.delete_socio DESC";
            $st = $this->pdo->prepare($sql);
            $st->execute($p);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { error_log('Socios_model::getEliminados: ' . $e->getMessage()); return []; }
    }

    public function contarEliminados($id_sindicato = 0) {
        try {
            $p = [];
            $sql = "SELECT COUNT(*) FROM socios s WHERE s.delete_socio IS NOT NULL" . $this->filtro($id_sindicato, $p);
            $st = $this->pdo->prepare($sql);
            $st->execute($p);
            return (int)$st->fetchColumn();
        } catch (PDOException $e) { return 0; }
    }

    public function obtenerSocio($id, $id_sindicato = 0, $incluirEliminados = false) {
        try {
            $p = [':id' => $id];
            $sql = $this->baseSelect() . "WHERE s.id_socio = :id" . $this->filtro($id_sindicato, $p);
            if (!$incluirEliminados) $sql .= " AND s.delete_socio IS NULL";
            $st = $this->pdo->prepare($sql . " LIMIT 1");
            $st->execute($p);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) { error_log('Socios_model::obtenerSocio: ' . $e->getMessage()); return null; }
    }

    /** Sindicatos vigentes y activos (para el select). $soloId > 0 limita a uno. */
    public function getSindicatosActivos($soloId = 0) {
        try {
            $sql = "SELECT id_sindicato, nombre_sindicato, sigla_sindicato,
                           CAST(IFNULL(es_principal_sindicato, 0) AS UNSIGNED) AS es_principal
                    FROM sindicatos
                    WHERE delete_sindicato IS NULL AND (estado_sindicato = 1 OR estado_sindicato IS NULL)";
            if ((int)$soloId > 0) $sql .= " AND id_sindicato = " . (int)$soloId;
            return $this->pdo->query($sql . " ORDER BY es_principal DESC, nombre_sindicato ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function sindicatoValido($id) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM sindicatos
             WHERE id_sindicato = :id AND delete_sindicato IS NULL AND (estado_sindicato = 1 OR estado_sindicato IS NULL)"
        );
        $st->execute([':id' => (int)$id]);
        return (int)$st->fetchColumn() > 0;
    }

    /** ¿La persona ya es socio (vigente O eliminado, de cualquier sindicato)? */
    public function buscarPorPersona($id_persona) {
        $st = $this->pdo->prepare("SELECT id_socio, delete_socio FROM socios WHERE id_persona = :p LIMIT 1");
        $st->execute([':p' => $id_persona]);
        $f = $st->fetch(PDO::FETCH_ASSOC);
        return $f ? ['id_socio' => (int)$f['id_socio'], 'eliminado' => $f['delete_socio'] !== null] : null;
    }

    public function codigoDuplicado($codigo, $excluirId = 0) {
        $st = $this->pdo->prepare("SELECT delete_socio FROM socios WHERE LOWER(codigo_socio) = :c AND id_socio <> :x LIMIT 1");
        $st->execute([':c' => mb_strtolower($codigo, 'UTF-8'), ':x' => (int)$excluirId]);
        $f = $st->fetch(PDO::FETCH_ASSOC);
        return $f ? ['eliminado' => $f['delete_socio'] !== null] : null;
    }

    /** Siguiente código libre tipo SOC-000001. */
    public function siguienteCodigo() {
        $n = (int)$this->pdo->query("SELECT IFNULL(MAX(id_socio), 0) + 1 FROM socios")->fetchColumn();
        do {
            $codigo = 'SOC-' . str_pad($n++, 6, '0', STR_PAD_LEFT);
        } while ($this->codigoDuplicado($codigo));
        return $codigo;
    }

    public function crear($id_persona, $id_sindicato, $d) {
        $st = $this->pdo->prepare(
            "INSERT INTO socios (id_persona, id_sindicato, codigo_socio, fecha_afiliacion_socio, estado_socio, create_socio)
             VALUES (:p, :s, :c, :f, 1, NOW())"
        );
        $st->execute([
            ':p' => $id_persona, ':s' => $id_sindicato, ':c' => $d['codigo'],
            ':f' => $d['fecha_afiliacion'] !== '' ? $d['fecha_afiliacion'] : date('Y-m-d')
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /** Actualiza código, fecha y sindicato ($d['id_sindicato']). */
    public function actualizar($id, $d) {
        $st = $this->pdo->prepare(
            "UPDATE socios SET codigo_socio = :c, fecha_afiliacion_socio = :f, id_sindicato = :s, update_socio = NOW()
             WHERE id_socio = :id AND delete_socio IS NULL"
        );
        return $st->execute([
            ':c' => $d['codigo'],
            ':f' => $d['fecha_afiliacion'] !== '' ? $d['fecha_afiliacion'] : null,
            ':s' => (int)$d['id_sindicato'],
            ':id' => $id
        ]);
    }

    /** Cambia el C.I. de la persona (la validación de duplicados la hace el controlador). */
    public function actualizarCarnet($id_persona, $ci) {
        $st = $this->pdo->prepare("UPDATE personas SET carnet_persona = :ci, update_persona = NOW() WHERE id_persona = :id");
        return $st->execute([':ci' => $ci, ':id' => $id_persona]);
    }

    public function cambiarEstado($id, $estado) {
        $st = $this->pdo->prepare("UPDATE socios SET estado_socio = :e, update_socio = NOW() WHERE id_socio = :id AND delete_socio IS NULL");
        $st->bindValue(':e', (int)$estado, PDO::PARAM_INT);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    public function eliminar($id, $fyh) {
        $st = $this->pdo->prepare("UPDATE socios SET delete_socio = :f, estado_socio = 0 WHERE id_socio = :id AND delete_socio IS NULL");
        $st->bindValue(':f', $fyh, PDO::PARAM_STR);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    public function restaurar($id) {
        $st = $this->pdo->prepare("UPDATE socios SET delete_socio = NULL, estado_socio = 1, update_socio = NOW() WHERE id_socio = :id AND delete_socio IS NOT NULL");
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }
}
?>