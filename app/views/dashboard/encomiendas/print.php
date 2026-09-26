<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guía #<?= htmlspecialchars($encomienda['guia_encomienda'] ?? $encomienda['id_encomienda']) ?></title>
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

        /* Encabezado con marca */
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

        /* Barra de meta datos (fecha / sucursal / QR) */
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

        /* Ruta origen -> destino */
        .ruta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 20px;
            border-bottom: 1px dashed var(--border);
        }
        .ruta .punto { text-align: center; }
        .ruta .punto .ciudad { font-size: 15px; font-weight: 700; color: var(--text-dark); }
        .ruta .punto .sucursal { font-size: 10px; color: var(--text-muted); }
        .ruta .flecha { color: var(--brand); font-size: 22px; }

        /* Remitente / Destinatario */
        .personas {
            display: flex;
            gap: 0;
            border-bottom: 1px solid var(--border);
        }
        .persona {
            flex: 1;
            padding: 14px 20px;
        }
        .persona + .persona { border-left: 1px dashed var(--border); }
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

        /* Contenido declarado */
        .declaracion {
            padding: 10px 20px;
            border-bottom: 1px solid var(--border);
            font-size: 11.5px;
        }
        .declaracion strong { color: var(--text-muted); }

        /* Tabla de bultos */
        .bultos { padding: 14px 20px 4px 20px; }
        .bultos h6 {
            margin: 0 0 8px 0;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--brand);
            font-weight: 700;
        }
        table.tabla-bultos { width: 100%; border-collapse: collapse; }
        table.tabla-bultos th {
            background: #f5f6f8;
            font-size: 10px;
            text-transform: uppercase;
            color: var(--text-muted);
            text-align: left;
            padding: 6px 8px;
            border-bottom: 1px solid var(--border);
        }
        table.tabla-bultos td {
            padding: 7px 8px;
            font-size: 12px;
            border-bottom: 1px solid #eef0f2;
        }
        table.tabla-bultos td.num, table.tabla-bultos th.num { text-align: right; }

        /* Resumen de pago */
        .resumen {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: #fafafa;
        }
        .resumen .pago .metodo { font-size: 11.5px; }
        .resumen .pago .condicion {
            display: inline-block;
            margin-top: 3px;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
        }
        .condicion.pagado { background: #e6f4ea; color: #1e7e34; }
        .condicion.cod { background: #fdecea; color: #c0392b; }
        .resumen .total { text-align: right; }
        .resumen .total .lbl { font-size: 10px; color: var(--text-muted); text-transform: uppercase; }
        .resumen .total .monto { font-size: 22px; font-weight: 800; color: var(--text-dark); }

        /* Firmas (inspirado en la guía física de recojo) */
        .firmas {
            display: flex;
            border-top: 1px solid var(--border);
        }
        .firma {
            flex: 1;
            padding: 22px 20px 12px 20px;
            text-align: center;
        }
        .firma + .firma { border-left: 1px dashed var(--border); }
        .firma .linea { border-top: 1px solid var(--text-dark); margin-bottom: 4px; }
        .firma .etiqueta { font-size: 10px; color: var(--text-muted); }

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
            <span class="material-symbols-rounded">local_shipping</span>
            <div>
                <div class="brand-name">TransExpress</div>
                <div class="brand-tag">Sistema de Encomiendas y Carga</div>
            </div>
        </div>
        <div class="guia-box">
            <div class="guia-label">Guía de Encomienda</div>
            <div class="guia-num">#<?= htmlspecialchars($encomienda['guia_encomienda'] ?? $encomienda['id_encomienda']) ?></div>
        </div>
    </div>

    <div class="meta-row">
        <div class="meta-item">Emitida el <strong><?= !empty($encomienda['create_encomienda']) ? date('d/m/Y H:i', strtotime($encomienda['create_encomienda'])) : date('d/m/Y H:i') ?></strong></div>
        <div id="qrcodeModal"></div>
    </div>

    <div class="ruta">
        <div class="punto">
            <div class="ciudad"><?= htmlspecialchars($encomienda['sucursal_origen_ciudad'] ?? 'N/A') ?></div>
            <div class="sucursal"><?= htmlspecialchars($encomienda['sucursal_origen_nombre'] ?? '') ?></div>
        </div>
        <span class="material-symbols-rounded flecha">trending_flat</span>
        <div class="punto">
            <div class="ciudad"><?= htmlspecialchars($encomienda['sucursal_destino_ciudad'] ?? 'N/A') ?></div>
            <div class="sucursal"><?= htmlspecialchars($encomienda['sucursal_destino_nombre'] ?? '') ?></div>
        </div>
    </div>

    <div class="personas">
        <div class="persona">
            <h6>Remitente</h6>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($encomienda['remitente_nombre'] ?? 'N/A') ?></p>
            <p><strong>C.I.:</strong> <?= htmlspecialchars($encomienda['remitente_ci'] ?? 'N/A') ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($encomienda['remitente_celular'] ?? 'N/A') ?></p>
        </div>
        <div class="persona">
            <h6>Destinatario</h6>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($encomienda['destinatario_nombre'] ?? 'N/A') ?></p>
            <p><strong>C.I.:</strong> <?= htmlspecialchars($encomienda['destinatario_ci'] ?? 'N/A') ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($encomienda['destinatario_celular'] ?? 'N/A') ?></p>
        </div>
    </div>

    <?php if (!empty($encomienda['declaracion_encomienda'])): ?>
    <div class="declaracion">
        <strong>Declaración de contenido:</strong> <?= htmlspecialchars($encomienda['declaracion_encomienda']) ?>
    </div>
    <?php endif; ?>

    <div class="bultos">
        <h6>Detalle de Bultos</h6>
        <table class="tabla-bultos">
            <thead>
                <tr>
                    <th>Contenido</th>
                    <th>Descripción</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($encomienda['bultos'])): ?>
                    <?php foreach ($encomienda['bultos'] as $bulto): ?>
                        <tr>
                            <td><?= htmlspecialchars($bulto['nombre_encomienda_contenido'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($bulto['descripcion_detalle_encomienda'] ?? '-') ?></td>
                            <td class="num">Bs. <?= number_format($bulto['subtotal_detalle_encomienda'] ?? 0, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">Sin detalles registrados</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="resumen">
        <div class="pago">
            <div class="metodo"><strong>Método de pago:</strong> <?= htmlspecialchars($encomienda['nombre_metodo_pago'] ?? 'N/A') ?></div>
            <?php if (!empty($encomienda['estado_pago_encomienda'])): ?>
                <span class="condicion pagado">Pagado en Origen</span>
            <?php else: ?>
                <span class="condicion cod">Por Cobrar en Destino (COD)</span>
            <?php endif; ?>
        </div>
        <div class="total">
            <div class="lbl">Total</div>
            <div class="monto">Bs. <?= number_format($encomienda['monto_encomienda'] ?? 0, 2) ?></div>
        </div>
    </div>

    <div class="firmas">
        <div class="firma">
            <div class="linea"></div>
            <div class="etiqueta">Firma de quien entrega</div>
        </div>
        <div class="firma">
            <div class="linea"></div>
            <div class="etiqueta">Recibí conforme (destino)</div>
        </div>
    </div>

    <div class="ticket-footer">
        TransExpress System &middot; Documento generado electrónicamente &middot; Conserve esta guía hasta la entrega
    </div>
</div>

<!-- Librería para generar el código QR -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    (function() {
        const codigoGuia = "<?= htmlspecialchars($encomienda['guia_encomienda'] ?? $encomienda['id_encomienda'] ?? '') ?>";
        const container = document.getElementById("qrcodeModal");
        if (container && typeof QRCode !== 'undefined') {
            container.innerHTML = "";
            new QRCode(container, { text: codigoGuia, width: 64, height: 64, correctLevel: QRCode.CorrectLevel.M });
        }
    })();
</script>

</body>
</html>