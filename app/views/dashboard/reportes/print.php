<?php
/**
 * views/dashboard/reportes/print.php
 * Variables: $f, $r (reporte), $sindicato, $sucursalTxt, $impreso
 * Formato de la hoja física: título, saldo inicial, tablas y firmas de Finanzas y Parada.
 */
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $h($r['titulo']) ?></title>
    <style>
        :root { --brand:#2e7d32; --brand-light:#e8f5e9; --text-dark:#1a1a1a; --text-muted:#6b7280; --border:#9aa0a6; }
        * { box-sizing: border-box; }
        html, body { margin:0; padding:0; background:#fff; color:var(--text-dark); font-family:'Segoe UI', Arial, sans-serif; font-size:12px; }
        .hoja { max-width:900px; margin:14px auto; padding:18px 26px; border:1px solid #d9dde1; border-radius:10px; }
        .inst { text-align:center; }
        .inst .n { font-size:15px; font-weight:800; }
        .inst .s { font-size:10px; color:var(--text-muted); }
        h1 { text-align:center; font-size:14px; margin:14px 0 6px; text-transform:uppercase; }
        .saldo { font-size:12px; font-weight:700; margin:8px 0 10px; }
        h2 { font-size:11.5px; text-transform:uppercase; margin:16px 0 4px; }
        table { width:100%; border-collapse:collapse; margin-bottom:6px; }
        th, td { border:1px solid var(--border); padding:6px 8px; font-size:11.5px; }
        th { background:#f5f6f8; text-transform:uppercase; font-size:10px; text-align:left; }
        .der { text-align:right; white-space:nowrap; }
        tfoot td { background:var(--brand-light); font-weight:800; }
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        .pie-nombres { margin-top:18px; font-size:12px; }
        .pie-nombres p { margin:3px 0; }
        .firmas { display:flex; justify-content:space-around; gap:30px; margin-top:60px; }
        .firma { flex:1; text-align:center; }
        .firma .linea { border-top:1px solid var(--text-dark); margin-bottom:4px; }
        .firma .et { font-size:10.5px; font-weight:700; text-transform:uppercase; }
        .firma .sub { font-size:9.5px; color:var(--text-muted); }
        .foot { text-align:center; margin-top:18px; font-size:9.5px; color:var(--text-muted); }
        @media print {
            .hoja { margin:0 auto; border:none; max-width:none; padding:0; }
            @page { size: A4 portrait; margin: 12mm; }
            * { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }
    </style>
</head>
<body>
<div class="hoja">
    <div class="inst">
        <div class="n"><?= $h($sindicato) ?></div>
        <div class="s"><?= $h($sucursalTxt) ?></div>
    </div>

    <h1><?= $h($r['titulo']) ?></h1>
    <div class="saldo"><?= $h($r['saldo_label']) ?>: <?= number_format($r['saldo'], 2) ?></div>

    <?php foreach ($r['tablas'] as $t): ?>
        <?php if ($t['titulo']): ?><h2><?= $h($t['titulo']) ?></h2><?php endif; ?>
        <table>
            <thead>
                <tr>
                    <?php foreach ($t['cols'] as $c): ?>
                        <th class="<?= $c[1] === 'texto' ? '' : 'der' ?>"><?= $h($c[0]) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($t['filas'] as $fila): ?>
                    <tr>
                        <?php foreach ($t['cols'] as $i => $c): ?>
                            <td class="<?= $c[1] === 'texto' ? '' : 'der' ?>"><?= $h(ValidarReportes::formatear($fila[$i] ?? null, $c[1])) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if ($t['pie']): ?>
            <tfoot>
                <tr>
                    <?php foreach ($t['cols'] as $i => $c): ?>
                        <td class="<?= $c[1] === 'texto' ? '' : 'der' ?>"><?= $h(ValidarReportes::formatear($t['pie'][$i] ?? null, $c[1])) ?></td>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    <?php endforeach; ?>

    <div class="pie-nombres">
        <p>Encargado de Finanzas: <strong><?= $h($f['finanzas']) ?></strong></p>
        <p>Encargado de parada: <strong><?= $h($f['parada']) ?></strong></p>
    </div>

    <div class="firmas">
        <div class="firma"><div class="linea"></div><div class="et"><?= $h($f['finanzas'] ?: ' ') ?></div><div class="sub">Encargado de Finanzas<br><?= $h($sindicato) ?></div></div>
        <div class="firma"><div class="linea"></div><div class="et"><?= $h($f['parada'] ?: ' ') ?></div><div class="sub">Encargado de Parada<br><?= $h($sindicato) ?></div></div>
    </div>

    <div class="foot">Impreso el <?= date('d/m/Y H:i') ?> por <?= $h($impreso) ?> · TransExpress System</div>
</div>
</body>
</html>