<?php
require_once __DIR__ . '/../../models/reportes/Reportes_model.php';
require_once __DIR__ . '/../../helpers/reportes/ValidarReportes.php';

/**
 * Módulo Reportes (informes económicos del sindicato). Solo lectura.
 *   /reportes                              index                     pantalla (filtros + tablas)
 *   /reportes/datos?...                    datos                     JSON con el reporte
 *   /reportes/obtenerSindicatosDestino?destino=X   JSON: sindicatos con ruta a ese destino
 *   /reportes/exportar?...                 exportar                  Excel (.xls)
 *   /reportes/imprimir?...                 imprimir                  hoja imprimible (iframe global)
 * GET: tipo (consolidado|diario|semanal|mensual|anual|rango|destino), dia, mes, semana, anio,
 *      desde/hasta, saldo, sucursal, finanzas, parada, destino, sind (ids con coma),
 *      precios ("id:monto,id:monto"). Alcance: sindicato de la sesión; solo el administrador
 *      ve todas las sucursales.
 */
class Reportes_controller {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new Reportes_model();
    }

    public function index() {
        $this->acceso('vista');
        $permitidas = $this->sucursalesPermitidas();
        $ids = array_map('intval', array_column($permitidas, 'id_sucursal'));

        $this->vista('reportes/index', [
            'tipos'      => ValidarReportes::TIPOS,
            'sucursales' => $permitidas,
            'destinos'   => $this->model->getDestinos($ids),
            'veTodas'    => $this->veTodas(),
            'mesActual'  => date('Y-m'),
            'anioActual' => date('Y'),
            'hoy'        => date('Y-m-d'),
            'inicioMes'  => date('Y-m-01'),
        ]);
    }

    public function datos() {
        $this->acceso('json');
        list($f, $error) = $this->filtros();
        if ($error !== null) $this->json(['success' => false, 'message' => $error]);

        try {
            $reporte = $this->model->reporte($f);
        } catch (PDOException $e) {
            error_log('Reportes datos: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error de base de datos al generar el reporte. Revise el log de PHP.']);
        }
        $this->json(['success' => true, 'reporte' => $reporte]);
    }

    /** Sindicatos que tienen ruta hacia el destino elegido (para el filtro "Por destino"). */
    public function obtenerSindicatosDestino() {
        $this->acceso('json');
        $destino = mb_substr(trim((string)($_GET['destino'] ?? '')), 0, ValidarReportes::MAX_DESTINO, 'UTF-8');
        if ($destino === '') {
            $this->json(['success' => false, 'message' => 'Seleccione el destino.']);
        }

        $ids = array_map('intval', array_column($this->sucursalesPermitidas(), 'id_sucursal'));
        $this->json(['success' => true, 'sindicatos' => $this->model->getSindicatosDestino($destino, $ids)]);
    }

    /** Excel: tablas HTML con cabecera .xls (abre en Excel/LibreOffice sin librerías externas). */
    public function exportar() {
        $this->acceso('doc');
        $d = $this->datosDocumento();
        $r = $d['r'];
        $e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
        $nombre = 'reporte_' . $d['f']['tipo'] . '_' . $d['f']['ini'] . '_' . $d['f']['fin'] . '.xls';

        if (ob_get_length()) ob_clean();
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Cache-Control: max-age=0');

        $celda = function ($v, $tipo, $negrita = false) use ($e) {
            $st = $negrita ? 'font-weight:bold;' : '';
            if ($v === null || $v === '') return '<td style="' . $st . '"></td>';
            if ($tipo === 'monto')  return '<td style="' . $st . 'mso-number-format:\'0\.00\'">' . number_format((float)$v, 2, '.', '') . '</td>';
            if ($tipo === 'entero') return '<td style="' . $st . 'mso-number-format:\'0\'">' . (int)$v . '</td>';
            return '<td style="' . $st . 'mso-number-format:\'\@\'">' . $e($v) . '</td>';
        };

        echo "\xEF\xBB\xBF<html><head><meta charset=\"utf-8\"></head><body>";
        echo '<p style="font-size:16px;font-weight:bold">' . $e($d['sindicato']) . '</p>';
        echo '<p style="font-weight:bold">' . $e($r['titulo']) . '</p>';
        if (!empty($r['saldo_label'])) {
            echo '<p>' . $e($r['saldo_label']) . ': ' . number_format($r['saldo'], 2) . ' · ' . $e($d['sucursalTxt']) . '</p>';
        } else {
            echo '<p>' . $e($d['sucursalTxt']) . '</p>';
        }

        foreach ($r['tablas'] as $t) {
            if ($t['titulo']) echo '<p style="font-weight:bold">' . $e($t['titulo']) . '</p>';
            echo '<table border="1" cellspacing="0" cellpadding="4"><tr>';
            foreach ($t['cols'] as $c) echo '<th style="background:#2e7d32;color:#fff">' . $e($c[0]) . '</th>';
            echo '</tr>';
            foreach ($t['filas'] as $fila) {
                echo '<tr>';
                foreach ($t['cols'] as $i => $c) echo $celda($fila[$i] ?? null, $c[1]);
                echo '</tr>';
            }
            if ($t['pie']) {
                echo '<tr>';
                foreach ($t['cols'] as $i => $c) echo $celda($t['pie'][$i] ?? null, $c[1], true);
                echo '</tr>';
            }
            echo '</table><br>';
        }
        echo '<p>Encargado de Finanzas: ' . $e($d['f']['finanzas']) . '</p><p>Encargado de parada: ' . $e($d['f']['parada']) . '</p>';
        echo '</body></html>';
        exit;
    }

    public function imprimir() {
        $this->acceso('doc');
        extract($this->datosDocumento());
        $impreso = $_SESSION['nombre_usuario'] ?? '';
        require_once __DIR__ . '/../../views/dashboard/reportes/print.php';
        exit;
    }

    /* ============================ PRIVADOS ============================ */

    private function idSindicato() { return (int)($_SESSION['id_sindicato'] ?? 0); }

    private function veTodas() {
        return mb_strtolower((string)($_SESSION['nombre_rol'] ?? ''), 'UTF-8') === Permisos::PROTEGIDO;
    }

    private function sucursalesPermitidas() {
        return $this->model->getSucursalesPermitidas(
            $this->idSindicato(),
            $this->veTodas() ? 0 : (int)($_SESSION['id_sucursal'] ?? 0)
        );
    }

    /** @return array [filtros, mensajeError|null, sucursalesPermitidas] */
    private function filtros() {
        $permitidas = $this->sucursalesPermitidas();
        $ids = array_map('intval', array_column($permitidas, 'id_sucursal'));
        $primer = (($_GET['tipo'] ?? '') === 'consolidado') ? $this->model->primerMes($ids) : null;
        $f = ValidarReportes::normalizar($_GET, $ids, $primer);
        return [$f, ValidarReportes::validar($f), $permitidas];
    }

    private function datosDocumento() {
        list($f, $error, $permitidas) = $this->filtros();
        if ($error !== null) die('Error: ' . $error);

        try {
            $r = $this->model->reporte($f);
        } catch (PDOException $e) {
            error_log('Reportes documento: ' . $e->getMessage());
            die('Error: no se pudo generar el reporte.');
        }

        $sucursalTxt = 'Todas las sucursales';
        if ($f['sucursal'] > 0 || count($permitidas) === 1) {
            foreach ($permitidas as $s) {
                if ($f['sucursal'] > 0 ? (int)$s['id_sucursal'] === $f['sucursal'] : true) {
                    $sucursalTxt = $s['ciudad_sucursal'] . ' · ' . $s['nombre_sucursal'];
                }
            }
        }

        return ['f' => $f, 'r' => $r, 'sindicato' => $_SESSION['nombre_sindicato'] ?? 'TransExpress', 'sucursalTxt' => $sucursalTxt];
    }

    /** $modo: 'vista' redirige, 'json' responde JSON, 'doc' corta con texto. */
    private function acceso($modo) {
        if (!empty($_SESSION['id_usuario'])) return;
        if ($modo === 'json') $this->json(['success' => false, 'message' => 'Su sesión expiró. Vuelva a iniciar sesión.']);
        if ($modo === 'doc')  die('Error: su sesión expiró. Vuelva a iniciar sesión.');
        header('Location: ' . rtrim(URL, '/') . '/auth/auth_controller/index');
        exit;
    }

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'reportes';
        extract($datos);

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . $vista . '.php')) {
            require_once $viewPath . $vista . '.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    private function json(array $payload) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        exit;
    }
}
?>