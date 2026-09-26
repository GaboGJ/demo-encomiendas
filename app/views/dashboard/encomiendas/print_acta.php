<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acta de Entrega #<?= htmlspecialchars($encomienda['guia_encomienda'] ?? $encomienda['id_encomienda']) ?></title>
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
        .acta {
            max-width: 720px;
            margin: 18px auto;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }
        .acta-header {
            background: linear-gradient(87deg, var(--brand) 0, #4caf50 100%);
            color: #fff;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .acta-header .brand { display: flex; align-items: center; gap: 10px; }
        .acta-header .brand .material-symbols-rounded { font-size: 26px; }
        .acta-header .brand-name { font-weight: 700; font-size: 16px; line-height: 1.1; }
        .acta-header .brand-tag { font-size: 10px; opacity: .9; }
        .acta-header .guia-box { text-align: right; }
        .acta-header .guia-num { font-size: 19px; font-weight: 800; }

        .section-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--brand);
            font-weight: 700;
            margin-bottom: 8px;
        }
        .info-box { padding: 14px 20px; border-bottom: 1px solid var(--border); }
        .info-grid { display: flex; gap: 20px; }
        .info-col { flex: 1; }

        table.tabla-bultos { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.tabla-bultos th { background: #f5f6f8; font-size: 10px; text-transform: uppercase; color: var(--text-muted); text-align: left; padding: 6px 8px; border-bottom: 1px solid var(--border); }
        table.tabla-bultos td { padding: 7px 8px; font-size: 12px; border-bottom: 1px solid #eef0f2; }

        .firmas { display: flex; border-top: 1px solid var(--border); margin-top: 30px; }
        .firma { flex: 1; padding: 22px 20px 12px 20px; text-align: center; }
        .firma + .firma { border-left: 1px dashed var(--border); }
        .firma .linea { border-top: 1px solid var(--text-dark); margin-bottom: 4px; }
        .firma .etiqueta { font-size: 10px; color: var(--text-muted); }

        @media print {
            html, body { background: #fff; }
            .acta { margin: 0 auto; border: none; }
            @page { margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="acta">
    <div class="acta-header">
        <div class="brand">
            <span class="material-symbols-rounded">task_alt</span>
            <div>
                <div class="brand-name">TransExpress</div>
                <div class="brand-tag">Acta de Entrega Conforme</div>
            </div>
        </div>
        <div class="guia-box">
            <div style="font-size: 9px; opacity: 0.85;">Guía N°</div>
            <div class="guia-num">#<?= htmlspecialchars($encomienda['guia_encomienda'] ?? $encomienda['id_encomienda']) ?></div>
        </div>
    </div>

    <div class="info-box">
        <div class="info-grid">
            <div class="info-col">
                <div class="section-title">Datos del Receptor</div>
                <p style="margin:2px 0;"><strong>Nombre:</strong> <?= htmlspecialchars($encomienda['receptor_nombre'] ?? $encomienda['destinatario_nombre'] ?? 'N/A') ?></p>
                <p style="margin:2px 0;"><strong>C.I.:</strong> <?= htmlspecialchars($encomienda['receptor_ci'] ?? $encomienda['destinatario_ci'] ?? 'N/A') ?></p>
                <p style="margin:2px 0;"><strong>Teléfono:</strong> <?= htmlspecialchars($encomienda['receptor_celular'] ?? $encomienda['destinatario_celular'] ?? 'N/A') ?></p>
            </div>
            <div class="info-col">
                <div class="section-title">Detalles del Despacho</div>
                <p style="margin:2px 0;"><strong>Origen:</strong> <?= htmlspecialchars($encomienda['sucursal_origen_ciudad'] ?? 'N/A') ?></p>
                <p style="margin:2px 0;"><strong>Fecha Entrega:</strong> <?= date('d/m/Y H:i') ?></p>
                <p style="margin:2px 0;"><strong>Estado Pago:</strong> <?= ($encomienda['estado_pago_encomienda'] ?? 0) == 1 ? 'Pagado en Origen' : 'Cobrado en Destino' ?></p>
            </div>
        </div>
    </div>

    <div class="info-box">
        <div class="section-title">Bultos Entregados</div>
        <table class="tabla-bultos">
            <thead>
                <tr>
                    <th>Código Precinto</th>
                    <th>Descripción</th>
                    <th style="text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($encomienda['bultos'])): ?>
                    <?php foreach ($encomienda['bultos'] as $bulto): ?>
                        <tr>
                            <td><?= htmlspecialchars($bulto['codigo_detalle_encomienda'] ?? 'S/C') ?></td>
                            <td><?= htmlspecialchars($bulto['descripcion_detalle_encomienda'] ?? '') ?></td>
                            <td style="text-align: center;">Entregado Conforme</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">1 Bulto / Carga General</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="firmas">
        <div class="firma">
            <div class="linea"></div>
            <div class="etiqueta">Firma del Receptor</div>
        </div>
        <div class="firma">
            <div class="linea"></div>
            <div class="etiqueta">Entregado por (Agente / Operador)</div>
        </div>
    </div>
</div>

<script>
    // Se ejecuta automáticamente al cargar la vista para abrir la ventana de impresión
    window.onload = function() {
        window.print();
    };
</script>
</body>
</html>