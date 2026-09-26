<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boleto <?= htmlspecialchars($pasaje['codigo_pasaje'] ?? $pasaje['id_pasaje']) ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

    <style>
        :root {
            --brand: #2e7d32;
            --brand-light: #e8f5e9;
            --text-dark: #1a1a1a;
            --text-muted: #6b7280;
            --border: #d9dde1;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: var(--text-dark);
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 13px;
        }
        .ticket {
            max-width: 720px;
            margin: 18px auto;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }
        .ticket-header {
            background: linear-gradient(87deg, var(--brand) 0, #4caf50 100%);
            color: #fff;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .ticket-header .brand { display: flex; align-items: center; gap: 10px; }
        .ticket-header .brand .material-symbols-rounded { font-size: 26px; }
        .ticket-header .brand-name { font-weight: 700; font-size: 16px; line-height: 1.1; }
        .ticket-header .brand-tag { font-size: 10px; opacity: .9; }
        .ticket-header .guia-box { text-align: right; }
        .ticket-header .guia-label { font-size: 9px; text-transform: uppercase; letter-spacing: .06em; opacity: .85; }
        .ticket-header .guia-num { font-size: 19px; font-weight: 800; letter-spacing: .02em; }

        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background: var(--brand-light);
            border-bottom: 1px solid var(--border);
        }
        .meta-row .meta-item { font-size: 11px; color: var(--text-muted); }
        .meta-row .meta-item strong { color: var(--text-dark); font-weight: 600; }
        .meta-row #qrcodeModal { line-height: 0; }

        .persona {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
        }
        .persona h6 {
            margin: 0 0 8px 0;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--brand);
            font-weight: 700;
        }
        .persona p { margin: 0 0 3px 0; font-size: 12px; }
        .persona p strong { color: var(--text-muted); font-weight: 600; }

        .asientos { padding: 14px 20px 4px 20px; }
        .asientos h6 {
            margin: 0 0 8px 0;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--brand);
            font-weight: 700;
        }
        table.tabla-asientos { width: 100%; border-collapse: collapse; }
        table.tabla-asientos th {
            background: #f5f6f8;
            font-size: 10px;
            text-transform: uppercase;
            color: var(--text-muted);
            text-align: left;
            padding: 6px 8px;
            border-bottom: 1px solid var(--border);
        }
        table.tabla-asientos td {
            padding: 7px 8px;
            font-size: 12px;
            border-bottom: 1px solid #eef0f2;
        }
        table.tabla-asientos td.num, table.tabla-asientos th.num { text-align: right; }
        .badge-pendiente {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            background: #fdf4e3;
            color: #b8860b;
        }

        .resumen {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: #fafafa;
        }
        .resumen .pago .metodo { font-size: 11.5px; }
        .resumen .total { text-align: right; }
        .resumen .total .lbl { font-size: 10px; color: var(--text-muted); text-transform: uppercase; }
        .resumen .total .monto { font-size: 22px; font-weight: 800; color: var(--text-dark); }

        .ticket-footer {
            text-align: center;
            padding: 8px;
            font-size: 9.5px;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
        }

        @media print {
            html, body { background: #fff; }
            .ticket { margin: 0 auto; border: none; }
            @page { margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="ticket" id="areaImpresion">

    <div class="ticket-header">
        <div class="brand">
            <span class="material-symbols-rounded">confirmation_number</span>
            <div>
                <div class="brand-name">TransExpress</div>
                <div class="brand-tag">Boleto de Viaje</div>
            </div>
        </div>
        <div class="guia-box">
            <div class="guia-label">Código de Venta</div>
            <div class="guia-num">#<?= htmlspecialchars($pasaje['codigo_pasaje'] ?? $pasaje['id_pasaje']) ?></div>
        </div>
    </div>

    <div class="meta-row">
        <div class="meta-item">Emitido el <strong><?= !empty($pasaje['create_pasaje']) ? date('d/m/Y H:i', strtotime($pasaje['create_pasaje'])) : date('d/m/Y H:i') ?></strong></div>
        <div id="qrcodeModal"></div>
    </div>

    <div class="persona">
        <h6>Comprador / Pasajero</h6>
        <p><strong>Nombre:</strong> <?= htmlspecialchars(trim($pasaje['comprador_nombre'] ?? '') ?: 'N/A') ?></p>
        <p><strong>C.I.:</strong> <?= htmlspecialchars($pasaje['comprador_ci'] ?? 'N/A') ?></p>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($pasaje['comprador_celular'] ?? 'N/A') ?></p>
    </div>

    <div class="asientos">
        <h6>Detalle de Viaje</h6>
        <table class="tabla-asientos">
            <thead>
                <tr>
                    <th>Ruta</th>
                    <th>Vehículo / Chofer</th>
                    <th>Asiento</th>
                    <th class="num">Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pasaje['detalles'])): ?>
                    <?php foreach ($pasaje['detalles'] as $d): ?>
                        <tr>
                            <td>
                                <?php if (!empty($d['id_turno'])): ?>
                                    <?= htmlspecialchars($d['origen_ciudad'] ?? '') ?> ➔ <?= htmlspecialchars($d['destino_ciudad'] ?? '') ?>
                                <?php else: ?>
                                    <span class="badge-pendiente">Pendiente de asignar turno</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($d['id_turno'])): ?>
                                    Móvil <?= htmlspecialchars($d['numero_interno_vehiculo'] ?? '-') ?> / <?= htmlspecialchars($d['nombre_chofer'] ?? '-') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($d['numero_asiento'] ?? 'Por asignar') ?></td>
                            <td class="num">Bs. <?= number_format($d['precio_detalle_pasaje'] ?? 0, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">Sin detalles registrados</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="resumen">
        <div class="pago">
            <div class="metodo"><strong>Método de pago:</strong> <?= htmlspecialchars($pasaje['nombre_metodo_pago'] ?? 'N/A') ?></div>
        </div>
        <div class="total">
            <div class="lbl">Total</div>
            <div class="monto">Bs. <?= number_format($pasaje['total_pasaje'] ?? 0, 2) ?></div>
        </div>
    </div>

    <div class="ticket-footer">
        TransExpress System &middot; Documento generado electrónicamente &middot; Conserve este boleto durante el viaje
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    (function() {
        const codigo = "<?= htmlspecialchars($pasaje['codigo_pasaje'] ?? $pasaje['id_pasaje'] ?? '') ?>";
        const container = document.getElementById("qrcodeModal");
        if (container && typeof QRCode !== 'undefined') {
            container.innerHTML = "";
            new QRCode(container, { text: codigo, width: 64, height: 64, correctLevel: QRCode.CorrectLevel.M });
        }
    })();
</script>

</body>
</html>