<?php
/**
 * helpers/Permisos.php
 * Verificador de permisos (rol x módulo x operación).
 *
 * - cargarEnSesion(): lee los permisos del rol del usuario y los guarda en $_SESSION['perm'].
 *   Se llama al iniciar sesión y se refresca solo cada TTL segundos, para que un cambio
 *   hecho en "Roles y Permisos" se aplique sin obligar a cerrar sesión.
 * - puede('Encomiendas', 'Crear'): ¿el rol puede hacer esa acción en ese módulo?
 * - autorizarRuta($carpeta, $accion): guarda central que usa public/index.php antes de
 *   ejecutar cualquier acción de un controlador.
 *
 * El rol "Administrador Sindicato" (el que crea el registro de Auth) tiene acceso total.
 * El Panel Principal es siempre accesible (a él se redirige cuando algo se deniega).
 */
class Permisos {

    const TTL       = 60; // segundos entre recargas desde la BD
    const PROTEGIDO = 'administrador sindicato';

    /** carpeta del router / clave de menú  =>  módulo (normalizado) */
    const MAPA = [
        'dashboard'   => 'panel principal',
        'encomiendas' => 'encomiendas',
        'pasajes'     => 'pasajes',
        'despachos'   => 'despachos',
        'cajas'       => 'cajas',
        'socios'      => 'socios',
        'choferes'    => 'choferes',
        'moviles'     => 'moviles',
        'rutas'       => 'rutas',
        'empresa'     => 'empresa',
        'sindicatos'  => 'sindicatos',
        'modelos'     => 'modelos',
        'usuarios'    => 'usuarios',
        'roles'       => 'roles',
        'reportes'    => 'reportes',
    ];

    /** Acción (método del controlador, en minúsculas) => tipo de operación. El resto se infiere. */
    const ACCIONES = [
        // Crear
        'new' => 'crear', 'guardar' => 'crear', 'reception' => 'crear', 'guardarrecepcion' => 'crear',
        // Editar
        'update' => 'editar', 'actualizar' => 'editar', 'cambiarestado' => 'editar',
        'delivery' => 'editar', 'guardarentrega' => 'editar',
        'asign' => 'editar', 'asignarencomiendas' => 'editar', 'quitarencomienda' => 'editar', 'despachar' => 'editar',
        'guardarchoferes' => 'editar', 'configurar' => 'editar', 'guardarconfiguracion' => 'editar',
        'permisos' => 'editar', 'guardarpermisos' => 'editar', 'restaurar' => 'editar', 'store' => 'crear',
        // Eliminar
        'eliminar' => 'eliminar', 'anular' => 'eliminar', 'cancelar' => 'eliminar',
        // Ver
        'index' => 'ver', 'papelera' => 'ver', 'detalle' => 'ver',
    ];

    /* ============================ CARGA ============================ */

    /** Minúsculas y sin tildes: "Móviles" == "moviles". */
    public static function clave($txt) {
        return strtr(mb_strtolower(trim((string)$txt), 'UTF-8'),
            ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }

    /** Lee de la BD los permisos del rol de la sesión y los deja en $_SESSION['perm']. */
    public static function cargarEnSesion() {
        global $pdo;

        $idRol = (int)($_SESSION['id_rol'] ?? 0);
        $perm  = ['rol' => $idRol, 'total' => false, 'mapa' => [], 'ts' => time()];

        try {
            if ($idRol > 0 && $pdo) {
                // Rol vigente (no eliminado ni inactivo)
                $st = $pdo->prepare(
                    "SELECT LOWER(nombre_rol) FROM roles
                     WHERE id_rol = :r AND delete_rol IS NULL AND (estado_rol = 1 OR estado_rol IS NULL)"
                );
                $st->execute([':r' => $idRol]);
                $nombre = $st->fetchColumn();

                if ($nombre !== false) {
                    if ($nombre === self::PROTEGIDO) {
                        $perm['total'] = true;
                    } else {
                        $st = $pdo->prepare(
                            "SELECT m.nombre_modulo, t.nombre_tipooperacion
                             FROM permisos pe
                             INNER JOIN operaciones op ON pe.id_operacion = op.id_operacion
                             INNER JOIN modulos m ON op.id_modulo = m.id_modulo
                             INNER JOIN tipos_operacion t ON op.id_tipooperacion = t.id_tipooperacion
                             WHERE pe.id_rol = :r
                               AND op.delete_operacion IS NULL AND (op.estado_operacion = 1 OR op.estado_operacion IS NULL)
                               AND m.delete_modulo IS NULL AND t.delete_tipooperacion IS NULL"
                        );
                        $st->execute([':r' => $idRol]);
                        foreach ($st->fetchAll(PDO::FETCH_NUM) as $f) {
                            $perm['mapa'][self::clave($f[0])][self::clave($f[1])] = true;
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            error_log('Permisos::cargarEnSesion: ' . $e->getMessage());
        }

        $_SESSION['perm'] = $perm;
        return $perm;
    }

    private static function datos() {
        $p = $_SESSION['perm'] ?? null;
        $vigente = $p
            && (int)$p['rol'] === (int)($_SESSION['id_rol'] ?? 0)
            && (time() - (int)$p['ts']) < self::TTL;
        return $vigente ? $p : self::cargarEnSesion();
    }

    /* ============================ CONSULTA ============================ */

    /** Acepta la carpeta del router ('moviles') o el nombre del módulo ('Móviles'). */
    private static function resolverModulo($modulo) {
        $k = self::clave($modulo);
        return self::MAPA[$k] ?? $k;
    }

    /** ¿Puede el rol de la sesión realizar $accion (Ver|Crear|Editar|Eliminar) en $modulo? */
    public static function puede($modulo, $accion = 'Ver') {
        if (empty($_SESSION['id_usuario'])) return false;

        $m = self::resolverModulo($modulo);
        if ($m === 'panel principal') return true; // destino de los redireccionamientos

        $p = self::datos();
        if ($p['total']) return true;

        return !empty($p['mapa'][$m][self::clave($accion)]);
    }

    /* ============================ GUARDA ============================ */

    /**
     * Guarda central (public/index.php). Carpetas que no son módulos (auth, etc.) pasan.
     * Si se deniega: JSON {success:false,message} en peticiones AJAX/POST, texto en impresiones,
     * o Flash + redirección al dashboard en las vistas.
     */
    public static function autorizarRuta($carpeta, $accion) {
        $carpeta = self::clave($carpeta);
        if (!isset(self::MAPA[$carpeta])) return; // auth, etc.

        $metodo = strtolower((string)$accion);
        $esPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
        $esJson = $esPost
            || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || preg_match('/^(detalle|buscar|obtener|choferes)/', $metodo);

        if (empty($_SESSION['id_usuario'])) {
            self::denegar('Su sesión expiró. Vuelva a iniciar sesión.', 'Sesión expirada', $esJson, '/auth', $metodo);
        }

        $tipo = self::ACCIONES[$metodo] ?? ($esPost ? 'editar' : 'ver');
        if (strpos($metodo, 'imprimir') === 0) $tipo = 'ver';

        if (!self::puede($carpeta, $tipo)) {
            $modulo = ucfirst(self::MAPA[$carpeta]);
            self::denegar("Su rol no tiene permiso para '{$tipo}' en el módulo {$modulo}.", 'Acceso denegado', $esJson, '/dashboard', $metodo);
        }
    }

    private static function denegar($mensaje, $titulo, $json, $destino, $metodo) {
        if ($json) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            // 200 a propósito: el front muestra res.message (con 403 algunos $.post caen en .fail genérico)
            echo json_encode(['success' => false, 'message' => $mensaje], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (strpos($metodo, 'imprimir') === 0) {
            die('Error: ' . $mensaje);
        }
        Flash::set(false, $mensaje, $titulo);
        header('Location: ' . rtrim(URL, '/') . $destino);
        exit;
    }
}
?>