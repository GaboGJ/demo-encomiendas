<?php
/**
 * Roles_model
 * CRUD de roles con borrado suave (delete_rol + estado_rol = 0) y matriz de permisos.
 *
 * Notas de la BD:
 *  - UK_nombre_rol: el nombre es UNIQUE incluso entre roles eliminados (se recupera desde la papelera).
 *  - permisos (id_rol, id_operacion) UNIQUE; operaciones = tipos_operacion x modulos (UNIQUE por par).
 *  - modulos, tipos_operacion y operaciones no traen datos semilla: asegurarCatalogo() los crea.
 *  - El rol "Administrador Sindicato" lo crea Auth_model y está protegido (acceso total, intocable).
 * Las lecturas devuelven [] / null ante error; las escrituras dejan subir la PDOException.
 */
class Roles_model {
    const PROTEGIDO = 'administrador sindicato';

    /** Módulos por defecto (coinciden con el menú lateral). */
    const MODULOS = [
        'Panel Principal' => 'Resumen de operaciones y métricas',
        'Encomiendas'     => 'Envíos, recepciones y entregas de carga',
        'Pasajes'         => 'Venta y anulación de pasajes',
        'Despachos'       => 'Turnos, asignación y despacho de unidades',
        'Cajas'           => 'Apertura, arqueo y cierre de cajas',
        'Socios'          => 'Gestión de socios del sindicato',
        'Choferes'        => 'Gestión de choferes y licencias',
        'Móviles'         => 'Parque automotor y asignación de choferes',
        'Rutas'           => 'Rutas y tarifarios',
        'Empresa'         => 'Datos institucionales',
        'Sindicatos'      => 'Sindicatos asociados',
        'Modelos'         => 'Modelos de vehículos y plano de asientos',
        'Usuarios'        => 'Usuarios del sistema',
        'Roles'           => 'Roles y permisos',
        'Reportes'        => 'Reportes operativos y económicos',
    ];

    /** Tipos de operación por defecto (el orden define las columnas de la matriz). */
    const TIPOS = [
        'Ver'      => 'Consultar información del módulo',
        'Crear'    => 'Registrar nuevos elementos',
        'Editar'   => 'Modificar elementos existentes',
        'Eliminar' => 'Eliminar o anular elementos',
    ];

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        if ($this->pdo) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    private function baseSelect() {
        return "SELECT
                    r.id_rol,
                    r.nombre_rol,
                    r.descripcion_rol,
                    CAST(IFNULL(r.estado_rol, 1) AS UNSIGNED) AS estado_rol,
                    r.create_rol,
                    r.update_rol,
                    r.delete_rol,
                    CAST(LOWER(r.nombre_rol) = '" . self::PROTEGIDO . "' AS UNSIGNED) AS es_protegido,
                    (SELECT COUNT(*) FROM usuarios u
                      WHERE u.id_rol = r.id_rol AND u.delete_usuario IS NULL) AS total_usuarios,
                    (SELECT COUNT(*) FROM permisos pe WHERE pe.id_rol = r.id_rol) AS total_permisos
                FROM roles r ";
    }

    /* ---------------- Lecturas ---------------- */

    public function getRoles() {
        try {
            $sql = $this->baseSelect() . "WHERE r.delete_rol IS NULL ORDER BY es_protegido DESC, r.nombre_rol ASC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Roles_model::getRoles: ' . $e->getMessage());
            return [];
        }
    }

    public function getEliminados() {
        try {
            $sql = $this->baseSelect() . "WHERE r.delete_rol IS NOT NULL ORDER BY r.delete_rol DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Roles_model::getEliminados: ' . $e->getMessage());
            return [];
        }
    }

    public function contarEliminados() {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM roles WHERE delete_rol IS NOT NULL")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function obtenerRol($id, $incluirEliminados = false) {
        try {
            $sql = $this->baseSelect() . "WHERE r.id_rol = :id";
            if (!$incluirEliminados) {
                $sql .= " AND r.delete_rol IS NULL";
            }
            $st = $this->pdo->prepare($sql . " LIMIT 1");
            $st->execute([':id' => $id]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Roles_model::obtenerRol: ' . $e->getMessage());
            return null;
        }
    }

    /** ¿Otro rol (vigente O eliminado) usa ese nombre? @return array|null ['eliminado' => bool] */
    public function nombreDuplicado($nombre, $excluirId = 0) {
        $st = $this->pdo->prepare(
            "SELECT delete_rol FROM roles WHERE LOWER(nombre_rol) = :n AND id_rol <> :x LIMIT 1"
        );
        $st->execute([':n' => mb_strtolower($nombre, 'UTF-8'), ':x' => (int)$excluirId]);
        $f = $st->fetch(PDO::FETCH_ASSOC);
        return $f ? ['eliminado' => $f['delete_rol'] !== null] : null;
    }

    /** Resumen de permisos por módulo para el modal de detalle. */
    public function getResumenPermisos($id_rol) {
        try {
            $sql = "SELECT m.nombre_modulo,
                           GROUP_CONCAT(t.nombre_tipooperacion ORDER BY t.id_tipooperacion SEPARATOR ', ') AS acciones
                    FROM permisos pe
                    INNER JOIN operaciones op ON pe.id_operacion = op.id_operacion
                    INNER JOIN modulos m ON op.id_modulo = m.id_modulo
                    INNER JOIN tipos_operacion t ON op.id_tipooperacion = t.id_tipooperacion
                    WHERE pe.id_rol = :r
                    GROUP BY m.id_modulo, m.nombre_modulo
                    ORDER BY m.id_modulo";
            $st = $this->pdo->prepare($sql);
            $st->execute([':r' => $id_rol]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /* ---------------- Escrituras del rol ---------------- */

    public function crear($d) {
        $st = $this->pdo->prepare(
            "INSERT INTO roles (nombre_rol, descripcion_rol, estado_rol, create_rol)
             VALUES (:n, :d, 1, NOW())"
        );
        $st->execute([':n' => $d['nombre'], ':d' => $d['descripcion'] !== '' ? $d['descripcion'] : null]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar($id, $d) {
        $st = $this->pdo->prepare(
            "UPDATE roles SET nombre_rol = :n, descripcion_rol = :d, update_rol = NOW()
             WHERE id_rol = :id AND delete_rol IS NULL"
        );
        return $st->execute([
            ':n'  => $d['nombre'],
            ':d'  => $d['descripcion'] !== '' ? $d['descripcion'] : null,
            ':id' => $id
        ]);
    }

    /** Activa/desactiva. El rol protegido nunca se desactiva (también se exige en SQL). */
    public function cambiarEstado($id, $estado) {
        $sql = "UPDATE roles SET estado_rol = :e, update_rol = NOW()
                WHERE id_rol = :id AND delete_rol IS NULL";
        if ((int)$estado === 0) {
            $sql .= " AND LOWER(nombre_rol) <> '" . self::PROTEGIDO . "'";
        }
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':e', (int)$estado, PDO::PARAM_INT);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    /** Borrado suave. El rol protegido no se elimina (también se exige en SQL). */
    public function eliminar($id, $fyh) {
        $st = $this->pdo->prepare(
            "UPDATE roles SET delete_rol = :f, estado_rol = 0
             WHERE id_rol = :id AND delete_rol IS NULL
               AND LOWER(nombre_rol) <> '" . self::PROTEGIDO . "'"
        );
        $st->bindValue(':f', $fyh, PDO::PARAM_STR);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    public function restaurar($id) {
        $st = $this->pdo->prepare(
            "UPDATE roles SET delete_rol = NULL, estado_rol = 1, update_rol = NOW()
             WHERE id_rol = :id AND delete_rol IS NOT NULL"
        );
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    /* ---------------- Catálogo (módulos / tipos / operaciones) ---------------- */

    /**
     * Crea los módulos y tipos de operación que falten y todas las combinaciones
     * módulo x tipo en `operaciones`. Idempotente. Las tablas tienen UNIQUE por nombre,
     * por eso se busca sin filtrar delete_*.
     */
    public function asegurarCatalogo() {
        $this->sembrar('modulos', 'nombre_modulo', 'descripcion_modulo', 'estado_modulo', 'create_modulo', self::MODULOS);
        $this->sembrar('tipos_operacion', 'nombre_tipooperacion', 'descripcion_tipooperacion', 'estado_tipooperacion', 'create_tipooperacion', self::TIPOS);

        $this->pdo->exec(
            "INSERT INTO operaciones (id_tipooperacion, id_modulo, estado_operacion, create_operacion)
             SELECT t.id_tipooperacion, m.id_modulo, 1, NOW()
             FROM tipos_operacion t
             CROSS JOIN modulos m
             WHERE NOT EXISTS (
                 SELECT 1 FROM operaciones o
                 WHERE o.id_tipooperacion = t.id_tipooperacion AND o.id_modulo = m.id_modulo
             )"
        );
    }

    /** $tabla/$col* son constantes internas (nunca vienen del usuario). */
    private function sembrar($tabla, $colNombre, $colDesc, $colEstado, $colCreate, array $items) {
        $existentes = array_map(function ($n) { return mb_strtolower($n, 'UTF-8'); },
            $this->pdo->query("SELECT $colNombre FROM $tabla")->fetchAll(PDO::FETCH_COLUMN));

        $ins = $this->pdo->prepare(
            "INSERT INTO $tabla ($colNombre, $colDesc, $colEstado, $colCreate) VALUES (:n, :d, 1, NOW())"
        );
        foreach ($items as $nombre => $desc) {
            if (!in_array(mb_strtolower($nombre, 'UTF-8'), $existentes, true)) {
                $ins->execute([':n' => $nombre, ':d' => $desc]);
            }
        }
    }

    public function getModulos() {
        try {
            return $this->pdo->query(
                "SELECT id_modulo, nombre_modulo, descripcion_modulo FROM modulos
                 WHERE (estado_modulo = 1 OR estado_modulo IS NULL) AND delete_modulo IS NULL
                 ORDER BY id_modulo ASC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTipos() {
        try {
            return $this->pdo->query(
                "SELECT id_tipooperacion, nombre_tipooperacion FROM tipos_operacion
                 WHERE (estado_tipooperacion = 1 OR estado_tipooperacion IS NULL) AND delete_tipooperacion IS NULL
                 ORDER BY id_tipooperacion ASC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Matriz del rol: [id_modulo][id_tipooperacion] => ['id_operacion' => int, 'activo' => 0|1].
     */
    public function getMatriz($id_rol) {
        try {
            $sql = "SELECT op.id_operacion, op.id_modulo, op.id_tipooperacion,
                           IF(pe.id_permiso IS NULL, 0, 1) AS activo
                    FROM operaciones op
                    INNER JOIN modulos m ON op.id_modulo = m.id_modulo
                         AND (m.estado_modulo = 1 OR m.estado_modulo IS NULL) AND m.delete_modulo IS NULL
                    INNER JOIN tipos_operacion t ON op.id_tipooperacion = t.id_tipooperacion
                         AND (t.estado_tipooperacion = 1 OR t.estado_tipooperacion IS NULL) AND t.delete_tipooperacion IS NULL
                    LEFT JOIN permisos pe ON pe.id_operacion = op.id_operacion AND pe.id_rol = :r
                    WHERE (op.estado_operacion = 1 OR op.estado_operacion IS NULL) AND op.delete_operacion IS NULL";
            $st = $this->pdo->prepare($sql);
            $st->execute([':r' => $id_rol]);

            $matriz = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $matriz[(int)$f['id_modulo']][(int)$f['id_tipooperacion']] = [
                    'id_operacion' => (int)$f['id_operacion'],
                    'activo'       => (int)$f['activo'],
                ];
            }
            return $matriz;
        } catch (PDOException $e) {
            error_log('Roles_model::getMatriz: ' . $e->getMessage());
            return [];
        }
    }

    /* ---------------- Permisos ---------------- */

    /**
     * Reemplaza los permisos del rol en UNA transacción.
     * Valida que cada operación exista y esté vigente, y fuerza "Ver" en todo módulo
     * que tenga alguna otra acción. Lanza Exception con mensaje legible.
     * @return int total de permisos guardados
     */
    public function guardarPermisos($id_rol, array $ids) {
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $st = $this->pdo->prepare(
                "SELECT COUNT(*) FROM operaciones op
                 INNER JOIN modulos m ON op.id_modulo = m.id_modulo AND m.delete_modulo IS NULL
                 INNER JOIN tipos_operacion t ON op.id_tipooperacion = t.id_tipooperacion AND t.delete_tipooperacion IS NULL
                 WHERE op.id_operacion IN ($in) AND op.delete_operacion IS NULL"
            );
            $st->execute($ids);
            if ((int)$st->fetchColumn() !== count($ids)) {
                throw new Exception('La matriz contiene operaciones no válidas. Recargue la página e intente de nuevo.');
            }

            // Forzar "Ver" en los módulos con alguna acción
            $st = $this->pdo->prepare("SELECT DISTINCT id_modulo FROM operaciones WHERE id_operacion IN ($in)");
            $st->execute($ids);
            $modulos = $st->fetchAll(PDO::FETCH_COLUMN);

            if ($modulos) {
                $inM = implode(',', array_fill(0, count($modulos), '?'));
                $st = $this->pdo->prepare(
                    "SELECT op.id_operacion FROM operaciones op
                     INNER JOIN tipos_operacion t ON op.id_tipooperacion = t.id_tipooperacion
                     WHERE LOWER(t.nombre_tipooperacion) = 'ver' AND op.id_modulo IN ($inM)"
                );
                $st->execute($modulos);
                $ids = array_values(array_unique(array_merge($ids, array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN)))));
            }
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("DELETE FROM permisos WHERE id_rol = :r")->execute([':r' => $id_rol]);

            $ins = $this->pdo->prepare("INSERT INTO permisos (id_rol, id_operacion) VALUES (:r, :o)");
            foreach ($ids as $idOp) {
                $ins->execute([':r' => $id_rol, ':o' => $idOp]);
            }

            $this->pdo->prepare("UPDATE roles SET update_rol = NOW() WHERE id_rol = :r")->execute([':r' => $id_rol]);
            $this->pdo->commit();
            return count($ids);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Copia los permisos de un rol origen al destino (se llama dentro de la transacción del llamador). */
    public function copiarPermisos($id_origen, $id_destino) {
        $st = $this->pdo->prepare(
            "INSERT INTO permisos (id_rol, id_operacion)
             SELECT :d, id_operacion FROM permisos WHERE id_rol = :o"
        );
        $st->execute([':d' => $id_destino, ':o' => $id_origen]);
        return $st->rowCount();
    }
}
?>