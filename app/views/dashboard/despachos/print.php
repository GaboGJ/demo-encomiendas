<?php
/**
 * views/dashboard/despachos/print.php
 * Hoja de Ruta / Guía de Pasajeros y Carga.
 * Variables: $turno, $pasajeros, $encomiendas, $despachador
 */
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

$codigoTurno = 'T-' . str_pad($turno['id_turno'], 3, '0', STR_PAD_LEFT);
$capacidad   = intval($turno['total_asientos_modelo'] ?? 0);
$despachado  = (stripos($turno['nombre_estado_turno'], 'despachado') !== false);

// Totales
$totalPasajes = array_sum(array_column($pasajeros, 'precio_detalle_pasaje'));
$encPagadas   = array_filter($encomiendas, function ($e) { return !empty($e['pagado']); });
$encCod       = array_filter($encomiendas, function ($e) { return empty($e['pagado']); });
$montoPagado  = array_sum(array_column($encPagadas, 'monto_encomienda'));
$montoCod     = array_sum(array_column($encCod, 'monto_encomienda'));
$totalBultos  = array_sum(array_column($encomiendas, 'total_bultos'));
$totalPeso    = array_sum(array_column($encomiendas, 'peso_total'));

// Filas en blanco para anotaciones manuales (como el talonario físico)
$filasPas = max(count($pasajeros), $capacidad, 7);

$fechaSalida = !empty($turno['fecha_salida_turno']) ? date('d/m/Y', strtotime($turno['fecha_salida_turno'])) : date('d/m/Y');
$horaSalida  = !empty($turno['hora_salida_turno']) ? substr($turno['hora_salida_turno'], 0, 5) : '--:--';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hoja de Ruta #<?= $h($codigoTurno) ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        :root { --brand:#2e7d32; --brand-light:#e8f5e9; --text-dark:#1a1a1a; --text-muted:#6b7280; --border:#d9dde1; }
        * { box-sizing: border-box; }
        html, body { margin:0; padding:0; background:#fff; color:var(--text-dark); font-family:'Segoe UI', Arial, Helvetica, sans-serif; font-size:12px; }
        .hoja { max-width:820px; margin:14px auto; border:1px solid var(--border); border-radius:10px; overflow:hidden; }

        .hoja-header { background:linear-gradient(87deg,var(--brand) 0,#4caf50 100%); color:#fff; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .brand { display:flex; align-items:center; gap:10px; }
        .brand .material-symbols-rounded { font-size:28px; }
        .brand-name { font-weight:700; font-size:15px; line-height:1.15; }
        .brand-tag { font-size:10px; opacity:.9; }
        .titulo { text-align:center; flex:1; }
        .titulo .t1 { font-size:15px; font-weight:800; letter-spacing:.03em; }
        .titulo .t2 { font-size:9.5px; opacity:.9; }
        .num-box { text-align:right; }
        .num-box .lbl { font-size:9px; text-transform:uppercase; letter-spacing:.06em; opacity:.85; }
        .num-box .num { font-size:19px; font-weight:800; }

        .meta-row { display:flex; align-items:center; justify-content:space-between; padding:8px 20px; background:var(--brand-light); border-bottom:1px solid var(--border); gap:10px; }
        .meta-item { font-size:11px; color:var(--text-muted); }
        .meta-item strong { color:var(--text-dark); font-weight:600; }
        .estado { display:inline-block; font-size:10px; font-weight:700; padding:2px 10px; border-radius:999px; }
        .estado.ok { background:#e6f4ea; color:#1e7e34; }
        .estado.pend { background:#fdf4e3; color:#b8860b; }
        #qrcodeTurno { line-height:0; }

        .datos { display:grid; grid-template-columns:repeat(3,1fr); gap:0; border-bottom:1px solid var(--border); }
        .dato { padding:8px 20px; border-bottom:1px dashed var(--border); }
        .dato .k { font-size:9px; text-transform:uppercase; letter-spacing:.06em; color:var(--brand); font-weight:700; }
        .dato .v { font-size:12.5px; font-weight:600; }

        .seccion { padding:12px 20px 4px 20px; }
        .seccion h6 { margin:0 0 6px 0; font-size:10px; text-transform:uppercase; letter-spacing:.07em; color:var(--brand); font-weight:700; display:flex; justify-content:space-between; }
        .seccion h6 span { color:var(--text-muted); font-weight:600; letter-spacing:0; text-transform:none; }
        table { width:100%; border-collapse:collapse; }
        th { background:#f5f6f8; font-size:9.5px; text-transform:uppercase; color:var(--text-muted); text-align:left; padding:5px 7px; border:1px solid var(--border); }
        td { padding:5px 7px; font-size:11.5px; border:1px solid #e3e6e9; height:22px; }
        .num { text-align:right; } .ctr { text-align:center; }
        .sub { display:block; font-size:9.5px; color:var(--text-muted); }
        .tag { display:inline-block; font-size:9px; font-weight:700; padding:1px 7px; border-radius:999px; }
        .tag.pag { background:#e6f4ea; color:#1e7e34; }
        .tag.cod { background:#fdecea; color:#c0392b; }
        tr { page-break-inside: avoid; }

        .resumen { display:grid; grid-template-columns:repeat(4,1fr); border-top:1px solid var(--border); margin-top:10px; background:#fafafa; }
        .res { padding:10px 14px; border-right:1px dashed var(--border); }
        .res:last-child { border-right:none; }
        .res .lbl { font-size:9px; color:var(--text-muted); text-transform:uppercase; }
        .res .val { font-size:16px; font-weight:800; }
        .res .det { font-size:9.5px; color:var(--text-muted); }
        .res.cod .val { color:#c0392b; }
        .res.tot { background:var(--brand-light); }

        .firmas { display:flex; border-top:1px solid var(--border); }
        .firma { flex:1; padding:26px 16px 10px 16px; text-align:center; }
        .firma + .firma { border-left:1px dashed var(--border); }
        .firma .linea { border-top:1px solid var(--text-dark); margin-bottom:4px; }
        .firma .etiqueta { font-size:10px; color:var(--text-muted); }
        .obs { padding:8px 20px; font-size:10.5px; color:var(--text-muted); border-top:1px solid var(--border); }
        .obs .caja { border:1px solid var(--border); height:34px; margin-top:4px; border-radius:4px; }
        .foot { text-align:center; padding:7px; font-size:9.5px; color:var(--text-muted); border-top:1px solid var(--border); }

        @media print {
            html, body { background:#fff; }
            .hoja { margin:0 auto; border:none; }
            @page { margin:8mm; }
            * { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }
    </style>
</head>
<body>
<div class="hoja">

    <div class="hoja-header">
        <div class="brand">
            <span class="material-symbols-rounded">local_shipping</span>
            <div>
                <div class="brand-name"><?= $h($turno['nombre_sindicato'] ?: 'TransExpress') ?></div>
                <div class="brand-tag">
                    <?= $h($turno['nombre_sucursal_origen'] ?? '') ?> · <?= $h($turno['ciudad_origen']) ?>
                    <?php if (!empty($turno['telefono_sindicato'])): ?> · Cel: <?= $h($turno['telefono_sindicato']) ?><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="titulo">
            <div class="t1">GUÍA DE PASAJEROS Y CARGA</div>
            <div class="t2">Hoja de Ruta · <?= $h($turno['ciudad_origen']) ?> ➔ <?= $h($turno['ciudad_destino']) ?></div>
        </div>
        <div class="num-box">
            <div class="lbl">Nº Hoja</div>
            <div class="num">#<?= $h($codigoTurno) ?></div>
        </div>
    </div>

    <div class="meta-row">
        <div class="meta-item">
            Impresa el <strong><?= date('d/m/Y H:i') ?></strong>
            &nbsp;·&nbsp;
            <span class="estado <?= $despachado ? 'ok' : 'pend' ?>"><?= $despachado ? 'DESPACHADO' : 'PRE-MANIFIESTO (sin despachar)' ?></span>
        </div>
        <div id="qrcodeTurno"></div>
    </div>

    <div class="datos">
        <div class="dato"><div class="k">Nombre del Chofer</div><div class="v"><?= $h($turno['nombre_chofer'] ?? '-') ?></div></div>
        <div class="dato"><div class="k">Licencia</div><div class="v"><?= $h($turno['licencia_chofer'] ?? '-') ?></div></div>
        <div class="dato"><div class="k">Celular Chofer</div><div class="v"><?= $h($turno['celular_chofer'] ?? '-') ?></div></div>
        <div class="dato"><div class="k">Placa</div><div class="v"><?= $h($turno['placa_vehiculo'] ?: 'S/P') ?></div></div>
        <div class="dato"><div class="k">Marca / Modelo</div><div class="v"><?= $h($turno['nombre_modelo'] ?? '-') ?></div></div>
        <div class="dato"><div class="k">Color · Unidad Nº</div><div class="v"><?= $h($turno['color_vehiculo'] ?: '-') ?> · <?= $h($turno['numero_interno_vehiculo'] ?? 'S/N') ?></div></div>
        <div class="dato"><div class="k">Fecha de Salida</div><div class="v"><?= $h($fechaSalida) ?></div></div>
        <div class="dato"><div class="k">Hora de Salida</div><div class="v"><?= $h($horaSalida) ?></div></div>
        <div class="dato"><div class="k">Destino Final</div><div class="v"><?= $h($turno['ciudad_destino']) ?></div></div>
    </div>

    <!-- PASAJEROS -->
    <div class="seccion">
        <h6>Pasajeros <span><?= count($pasajeros) ?> / <?= $capacidad ?> asientos · Bs. <?= number_format($totalPasajes, 2) ?></span></h6>
        <table>
            <thead>
                <tr>
                    <th style="width:28px;" class="ctr">Nº</th>
                    <th style="width:60px;" class="ctr">Asiento</th>
                    <th>Nombre y Apellidos del Pasajero</th>
                    <th style="width:100px;">N.º C.I.</th>
                    <th style="width:90px;">Venta</th>
                    <th style="width:75px;" class="num">Precio (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $filasPas; $i++): $p = $pasajeros[$i] ?? null; ?>
                    <tr>
                        <td class="ctr"><?= $i + 1 ?></td>
                        <td class="ctr"><?= $p ? $h($p['asiento'] ?? 'S/A') : '' ?></td>
                        <td><?= $p ? $h($p['pasajero']) : '' ?></td>
                        <td><?= $p ? $h($p['pasajero_ci']) : '' ?></td>
                        <td><?= $p ? '#' . $h($p['codigo_pasaje']) : '' ?></td>
                        <td class="num"><?= $p ? number_format($p['precio_detalle_pasaje'], 2) : '' ?></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <!-- ENCOMIENDAS -->
    <div class="seccion">
        <h6>Encomiendas <span><?= count($encomiendas) ?> guía(s) · <?= (int)$totalBultos ?> bulto(s)<?= $totalPeso > 0 ? ' · ' . number_format($totalPeso, 1) . ' Kg' : '' ?></span></h6>
        <table>
            <thead>
                <tr>
                    <th style="width:28px;" class="ctr">Nº</th>
                    <th style="width:88px;">Nº Guía</th>
                    <th>Contenido</th>
                    <th>Remitente ➔ Destinatario</th>
                    <th style="width:80px;">Celular</th>
                    <th style="width:100px;" class="num">Bs. / Cobro</th>
                </tr>
            </thead>
 <tbody>
    <?php if (!empty($encomiendas)): ?>
        <?php foreach ($encomiendas as $i => $e): ?>
            <tr>
                <td class="ctr"><?= $i + 1 ?></td>
                <td>#<?= $h($e['guia_encomienda']) ?></td>
                <td>
                    <?= $h($e['declaracion_encomienda'] ?: '-') ?>
                    <span class="sub"><?= (int)$e['total_bultos'] ?> bulto(s)<?= $e['peso_total'] > 0 ? ' · ' . number_format($e['peso_total'], 1) . ' Kg' : '' ?></span>
                </td>
                <td><?= $h($e['remitente']) ?> ➔ <strong><?= $h($e['destinatario']) ?></strong></td>
                <td><?= $h($e['destinatario_celular']) ?></td>
                <td class="num">
                    <?= number_format($e['monto_encomienda'], 2) ?>
                    <span class="tag <?= $e['pagado'] ? 'pag' : 'cod' ?>"><?= $e['pagado'] ? 'PAGADO' : 'COD' ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" class="ctr" style="color:var(--text-muted);">Sin encomiendas asignadas a este turno</td>
        </tr>
    <?php endif; ?>
</tbody>
        </table>
    </div>

    <!-- RESUMEN -->
    <div class="resumen">
        <div class="res">
            <div class="lbl">Pasajes</div>
            <div class="val">Bs. <?= number_format($totalPasajes, 2) ?></div>
            <div class="det"><?= count($pasajeros) ?> pasajero(s)</div>
        </div>
        <div class="res">
            <div class="lbl">Encomiendas pagadas (origen)</div>
            <div class="val">Bs. <?= number_format($montoPagado, 2) ?></div>
            <div class="det"><?= count($encPagadas) ?> guía(s)</div>
        </div>
        <div class="res cod">
            <div class="lbl">Por cobrar en destino (COD)</div>
            <div class="val">Bs. <?= number_format($montoCod, 2) ?></div>
            <div class="det"><?= count($encCod) ?> guía(s) · cobra el chofer/destino</div>
        </div>
        <div class="res tot">
            <div class="lbl">Recaudado en origen</div>
            <div class="val">Bs. <?= number_format($totalPasajes + $montoPagado, 2) ?></div>
            <div class="det">Pasajes + encomiendas pagadas</div>
        </div>
    </div>

    <div class="obs">
        Observaciones (pasajeros o carga de última hora, novedades del viaje):
        <div class="caja"></div>
    </div>

    <div class="firmas">
        <div class="firma"><div class="linea"></div><div class="etiqueta">Chofer (recibí conforme)<br><?= $h($turno['nombre_chofer'] ?? '') ?></div></div>
        <div class="firma"><div class="linea"></div><div class="etiqueta">Despachado por<br><?= $h($despachador) ?></div></div>
        <div class="firma"><div class="linea"></div><div class="etiqueta">Recibí conforme (destino)<br><?= $h($turno['ciudad_destino']) ?></div></div>
    </div>

    <div class="foot">
        TransExpress System &middot; Documento generado electrónicamente &middot; Hoja de Ruta #<?= $h($codigoTurno) ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    (function () {
        var c = document.getElementById('qrcodeTurno');
        if (c && typeof QRCode !== 'undefined') {
            c.innerHTML = '';
            new QRCode(c, { text: "<?= $h($codigoTurno) ?>", width: 60, height: 60, correctLevel: QRCode.CorrectLevel.M });
        }
    })();
</script>
</body>
</html>