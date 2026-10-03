<?php
/**
 * views/dashboard/cajas/print_cierre.php
 * Variables: $turno (historial + caja + cajero), $resumen
 */
$h  = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$bs = function ($n) { return 'Bs. ' . number_format((float)$n, 2); };
$abierta = (int)$turno['abierta'] === 1;
$sistema = $abierta ? $resumen['total'] : (float)$turno['monto_final_sistema'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Caja #<?= (int)$turno['id_historial_caja'] ?></title>
    <style>
        :root { --brand:#2e7d32; --text-dark:#1a1a1a; --text-muted:#6b7280; --border:#d9dde1; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:'Segoe UI', Arial, sans-serif; font-size:13px; color:var(--text-dark); }
        .hoja { max-width:620px; margin:18px auto; border:1px solid var(--border); border-radius:10px; overflow:hidden; }
        .head { background:linear-gradient(87deg,var(--brand) 0,#4caf50 100%); color:#fff; padding:14px 20px; display:flex; justify-content:space-between; align-items:center; }
        .head .t1 { font-weight:700; font-size:16px; } .head .t2 { font-size:10px; opacity:.9; }
        .head .num { font-size:18px; font-weight:800; text-align:right; }
        .datos { display:flex; flex-wrap:wrap; border-bottom:1px solid var(--border); }
        .dato { width:50%; padding:8px 20px; border-bottom:1px dashed var(--border); }
        .dato .k { font-size:9px; text-transform:uppercase; letter-spacing:.06em; color:var(--brand); font-weight:700; }
        .dato .v { font-weight:600; }
        table { width:100%; border-collapse:collapse; }
        td { padding:7px 20px; border-bottom:1px solid #eef0f2; }
        td.num { text-align:right; font-weight:600; }
        .total td { background:#e8f5e9; font-weight:800; font-size:14px; }
        .dif-ok { color:#1e7e34; } .dif-mal { color:#c0392b; }
        .firmas { display:flex; margin-top:30px; border-top:1px solid var(--border); }
        .firma { flex:1; padding:28px 16px 10px; text-align:center; }
        .firma + .firma { border-left:1px dashed var(--border); }
        .firma .linea { border-top:1px solid var(--text-dark); margin-bottom:4px; }
        .firma .et { font-size:10px; color:var(--text-muted); }
        .foot { text-align:center; padding:8px; font-size:9.5px; color:var(--text-muted); border-top:1px solid var(--border); }
        @media print { .hoja { margin:0 auto; border:none; } @page { margin:10mm; } * { -webkit-print-color-adjust:exact; print-color-adjust:exact; } }
    </style>
</head>
<body>
<div class="hoja">
    <div class="head">
        <div><div class="t1">TransExpress</div><div class="t2"><?= $abierta ? 'Pre-arqueo de Caja (turno abierto)' : 'Reporte de Cierre de Caja' ?></div></div>
        <div class="num">#<?= (int)$turno['id_historial_caja'] ?></div>
    </div>

    <div class="datos">
        <div class="dato"><div class="k">Caja</div><div class="v"><?= $h($turno['nombre_caja']) ?></div></div>
        <div class="dato"><div class="k">Sucursal</div><div class="v"><?= $h($turno['ciudad_sucursal'] . ' · ' . $turno['nombre_sucursal']) ?></div></div>
        <div class="dato"><div class="k">Cajero</div><div class="v"><?= $h($turno['cajero']) ?></div></div>
        <div class="dato"><div class="k">Apertura</div><div class="v"><?= date('d/m/Y H:i', strtotime($turno['fecha_apertura'])) ?></div></div>
        <div class="dato"><div class="k">Cierre</div><div class="v"><?= $turno['fecha_cierre'] ? date('d/m/Y H:i', strtotime($turno['fecha_cierre'])) : 'Abierta' ?></div></div>
        <div class="dato"><div class="k">Impreso</div><div class="v"><?= date('d/m/Y H:i') ?></div></div>
    </div>

    <table>
        <tr><td>Fondo inicial</td><td class="num"><?= $bs($resumen['inicial']) ?></td></tr>
        <tr><td>Pasajes vendidos (<?= (int)$resumen['n_pasajes'] ?>)</td><td class="num"><?= $bs($resumen['pasajes']) ?></td></tr>
        <tr><td>Encomiendas pagadas en origen (<?= (int)$resumen['n_encomiendas'] ?>)</td><td class="num"><?= $bs($resumen['encomiendas']) ?></td></tr>
        <tr><td>Cobros COD en destino</td><td class="num"><?= $bs($resumen['entregas']) ?></td></tr>
        <tr><td>Otros ingresos</td><td class="num"><?= $bs($resumen['ingresos_extra']) ?></td></tr>
        <tr><td>Egresos</td><td class="num">- <?= $bs($resumen['egresos']) ?></td></tr>
        <tr class="total"><td>TOTAL EN SISTEMA</td><td class="num"><?= $bs($sistema) ?></td></tr>
        <?php if (!$abierta): ?>
            <tr><td>Efectivo contado</td><td class="num"><?= $bs($turno['monto_final_declarado']) ?></td></tr>
            <?php $dif = (float)$turno['diferencia_historial_caja']; ?>
            <tr><td>Diferencia</td><td class="num <?= $dif == 0 ? 'dif-ok' : 'dif-mal' ?>"><?= $bs($dif) ?><?= $dif == 0 ? ' (cuadrada)' : ($dif < 0 ? ' (faltante)' : ' (sobrante)') ?></td></tr>
        <?php endif; ?>
    </table>

    <div class="firmas">
        <div class="firma"><div class="linea"></div><div class="et">Cajero<br><?= $h($turno['cajero']) ?></div></div>
        <div class="firma"><div class="linea"></div><div class="et">Revisado por (Administración)</div></div>
    </div>
    <div class="foot">TransExpress System · Documento generado electrónicamente</div>
</div>
<script>window.onload = function () { window.print(); };</script>
</body>
</html>