<?php
define('UPLOAD_PASSWORD', 'wolffilms2024');
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: admin-upload.php');
    exit;
}

// Generar número de presupuesto automático
$num_base = date('Ymd');
$contador_file = 'proforma_counter.txt';
$counter = file_exists($contador_file) ? (int)file_get_contents($contador_file) : 0;
$numero_auto = 'WF-' . $num_base . '-' . str_pad($counter + 1, 3, '0', STR_PAD_LEFT);

// Logo SVG inline
$logo_svg = '';
$logo_path = 'imagenes/logo wolf.svg';
if (file_exists($logo_path)) {
    $logo_svg = file_get_contents($logo_path);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Proformas — WolfFilms</title>
    <style>
        /* ── Estilos del panel (no se imprimen) ── */
        @media screen {
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family:'Segoe UI',sans-serif; background:#111; color:#eee; }

            .panel-header { background:#1a1a1a; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #2a2a2a; position:sticky; top:0; z-index:100; }
            .panel-header h1 { font-size:1rem; font-weight:600; letter-spacing:.05em; }
            .panel-header a { color:#666; text-decoration:none; font-size:.85rem; }
            .panel-header a:hover { color:#fff; }

            .layout { display:grid; grid-template-columns:420px 1fr; gap:0; height:calc(100vh - 56px); overflow:hidden; }

            /* Formulario izquierda */
            .form-panel { background:#1a1a1a; border-right:1px solid #2a2a2a; overflow-y:auto; padding:1.5rem; }
            .form-panel h2 { font-size:.75rem; letter-spacing:.12em; text-transform:uppercase; color:#666; margin-bottom:1rem; padding-bottom:.5rem; border-bottom:1px solid #2a2a2a; }
            .form-panel h2:not(:first-child) { margin-top:1.5rem; }

            .field { margin-bottom:.8rem; }
            .field label { display:block; font-size:.75rem; color:#888; margin-bottom:.3rem; letter-spacing:.05em; }
            .field input, .field textarea, .field select {
                width:100%; padding:.6rem .8rem; background:#111; border:1px solid #2a2a2a;
                border-radius:6px; color:#eee; font-size:.85rem; font-family:inherit;
                transition:border-color .2s;
            }
            .field input:focus, .field textarea:focus { outline:none; border-color:#555; }
            .field textarea { resize:vertical; min-height:70px; }

            /* Filas de conceptos */
            .concepto-row { display:grid; grid-template-columns:1fr 100px 28px; gap:.4rem; align-items:center; margin-bottom:.4rem; }
            .concepto-row input { padding:.5rem .6rem; background:#111; border:1px solid #2a2a2a; border-radius:5px; color:#eee; font-size:.82rem; width:100%; }
            .concepto-row input:focus { outline:none; border-color:#555; }
            .btn-del-row { background:none; border:1px solid #333; border-radius:5px; color:#666; cursor:pointer; width:28px; height:28px; font-size:1rem; display:flex; align-items:center; justify-content:center; }
            .btn-del-row:hover { background:#2a2a2a; color:#e74c3c; }
            #add-row { background:none; border:1px dashed #333; border-radius:6px; width:100%; padding:.5rem; color:#666; font-size:.8rem; cursor:pointer; margin-top:.3rem; }
            #add-row:hover { border-color:#666; color:#aaa; }

            .btn-print { background:#fff; color:#111; border:none; padding:.85rem 2rem; border-radius:8px; font-weight:700; font-size:.95rem; cursor:pointer; width:100%; margin-top:1.5rem; }
            .btn-print:hover { background:#ddd; }

            /* Preview derecha */
            .preview-panel { background:#e8e8e8; overflow-y:auto; display:flex; align-items:flex-start; justify-content:center; padding:2rem; }
        }

        /* ── Documento (se ve en preview Y se imprime) ── */
        .proforma-doc {
            background:#fff;
            width:210mm;
            min-height:297mm;
            padding:16mm 18mm;
            font-family:'Helvetica Neue', Arial, sans-serif;
            color:#1a1a1a;
            box-shadow:0 4px 30px rgba(0,0,0,.25);
        }

        .doc-header { display:flex; flex-direction:column; align-items:center; margin-bottom:10mm; }
        .doc-logo { width:60mm; height:auto; }
        .doc-logo svg { width:100%; height:auto; }
        .doc-titulo {
            font-size:16pt;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:.08em;
            margin-top:6mm;
            text-align:center;
        }

        .doc-meta { display:flex; justify-content:space-between; margin-bottom:7mm; font-size:8pt; color:#555; }
        .doc-meta span { }

        .doc-cliente { background:#f5f5f5; border-left:3px solid #111; padding:4mm 5mm; margin-bottom:7mm; font-size:9pt; }
        .doc-cliente strong { display:block; font-size:10pt; margin-bottom:1mm; }

        .doc-detalles { margin-bottom:7mm; }
        .doc-detalles p { font-size:9pt; margin-bottom:1.5mm; line-height:1.4; }
        .doc-detalles strong { font-weight:700; }

        .doc-table { width:100%; border-collapse:collapse; margin-bottom:5mm; }
        .doc-table th { background:#111; color:#fff; padding:3mm 4mm; font-size:9pt; text-align:left; }
        .doc-table th:last-child { text-align:right; width:32mm; }
        .doc-table td { padding:2.5mm 4mm; font-size:9pt; border-bottom:1px solid #eee; }
        .doc-table td:last-child { text-align:right; font-weight:600; }
        .doc-table tr.total-row td { background:#f5f5f5; font-weight:800; font-size:10pt; border-top:2px solid #111; }
        .doc-table tr.iva-row td { font-size:8pt; color:#777; border-bottom:none; }
        .doc-table tr.grandtotal-row td { background:#111; color:#fff; font-weight:800; font-size:10.5pt; }

        .doc-opcionales { margin-top:5mm; }
        .doc-opcionales h4 { font-size:9.5pt; font-weight:800; margin-bottom:2mm; }
        .doc-opcionales p { font-size:8.5pt; color:#444; line-height:1.6; white-space:pre-line; }

        .doc-notas { margin-top:5mm; padding:3mm 4mm; border:1px solid #ddd; border-radius:2mm; }
        .doc-notas h4 { font-size:8.5pt; font-weight:700; margin-bottom:1.5mm; color:#555; }
        .doc-notas p { font-size:8pt; color:#666; white-space:pre-line; line-height:1.5; }

        .doc-footer { margin-top:10mm; padding-top:4mm; border-top:1px solid #ddd; display:flex; justify-content:space-between; font-size:7.5pt; color:#888; }

        .doc-validez { margin-top:4mm; font-size:8pt; color:#888; text-align:center; }

        /* ── Print ── */
        @media print {
            body { background:#fff !important; }
            .panel-header, .form-panel { display:none !important; }
            .layout { display:block !important; height:auto !important; }
            .preview-panel { background:#fff !important; padding:0 !important; }
            .proforma-doc { box-shadow:none !important; width:100% !important; padding:10mm 12mm !important; }
        }
    </style>
</head>
<body>

<div class="panel-header">
    <h1>📄 Generador de Proformas — WolfFilms</h1>
    <a href="admin-upload.php">← Volver al panel</a>
</div>

<div class="layout">

    <!-- ════ FORMULARIO ════ -->
    <div class="form-panel">

        <h2>Número y fecha</h2>
        <div class="field">
            <label>Número de presupuesto</label>
            <input type="text" id="f-numero" value="<?= htmlspecialchars($numero_auto) ?>">
        </div>
        <div class="field">
            <label>Fecha</label>
            <input type="date" id="f-fecha" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="field">
            <label>Validez del presupuesto</label>
            <input type="text" id="f-validez" value="30 días" placeholder="Ej: 30 días">
        </div>

        <h2>Título del documento</h2>
        <div class="field">
            <input type="text" id="f-titulo" value="PRESUPUESTO SESIÓN FOTOGRÁFICA" placeholder="PRESUPUESTO SESIÓN FOTOGRÁFICA">
        </div>

        <h2>Datos del cliente</h2>
        <div class="field">
            <label>Nombre completo</label>
            <input type="text" id="f-cliente-nombre" placeholder="Ej: María García López">
        </div>
        <div class="field">
            <label>Email</label>
            <input type="email" id="f-cliente-email" placeholder="cliente@email.com">
        </div>
        <div class="field">
            <label>Teléfono</label>
            <input type="text" id="f-cliente-tel" placeholder="+34 600 000 000">
        </div>

        <h2>Detalles de la sesión</h2>
        <div class="field">
            <label>Duración estimada</label>
            <input type="text" id="f-duracion" value="4-5 horas" placeholder="Ej: 4-5 horas">
        </div>
        <div class="field">
            <label>Ubicación</label>
            <input type="text" id="f-ubicacion" value="Espacio proporcionado por el cliente" placeholder="Ej: Aranjuez o según acuerdo">
        </div>
        <div class="field">
            <label>Tipo de fotografía</label>
            <input type="text" id="f-tipo" value="Retrato cosmético" placeholder="Ej: Retrato, Boda, Evento...">
        </div>
        <div class="field">
            <label>Entrega</label>
            <input type="text" id="f-entrega" value="20-30 fotografías editadas levemente en alta resolución" placeholder="Ej: 20-30 fotos en alta resolución">
        </div>

        <h2>Conceptos y precios</h2>
        <div style="display:grid;grid-template-columns:1fr 100px 28px;gap:.4rem;margin-bottom:.4rem;">
            <span style="font-size:.72rem;color:#666">Concepto</span>
            <span style="font-size:.72rem;color:#666">Precio</span>
            <span></span>
        </div>
        <div id="conceptos-list">
            <!-- filas generadas por JS -->
        </div>
        <button id="add-row" onclick="addRow()">+ Añadir concepto</button>

        <h2>IVA</h2>
        <div class="field" style="display:flex;gap:.5rem;align-items:center">
            <input type="number" id="f-iva" value="21" min="0" max="100" style="width:70px">
            <label style="margin:0;color:#888;font-size:.85rem">% — escribe 0 para mostrar solo subtotal</label>
        </div>

        <h2>Opcionales</h2>
        <div class="field">
            <textarea id="f-opcionales" placeholder="Versión con mayor retoque de piel o edición avanzada +10 €/foto
Fotografía adicional fuera del lote (más de 30 fotos) +10 €/foto
Entrega exprés en 48h +50 €">Versión con mayor retoque de piel o edición avanzada +10 €/foto
Fotografía adicional fuera del lote (más de 30 fotos) +10 €/foto
Entrega exprés en 48h +50 €</textarea>
        </div>

        <h2>Notas adicionales</h2>
        <div class="field">
            <textarea id="f-notas" placeholder="Formas de pago, condiciones, etc."></textarea>
        </div>

        <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
        <p style="text-align:center;font-size:.75rem;color:#555;margin-top:.6rem">Ctrl+P → «Guardar como PDF»</p>
    </div>

    <!-- ════ PREVIEW ════ -->
    <div class="preview-panel">
        <div class="proforma-doc" id="proforma-preview">

            <div class="doc-header">
                <div class="doc-logo">
                    <?= $logo_svg ?>
                </div>
                <div class="doc-titulo" id="p-titulo">PRESUPUESTO SESIÓN FOTOGRÁFICA</div>
            </div>

            <div class="doc-meta">
                <span>Nº <strong id="p-numero"><?= htmlspecialchars($numero_auto) ?></strong></span>
                <span>Fecha: <strong id="p-fecha"><?= date('d/m/Y') ?></strong></span>
            </div>

            <div class="doc-cliente" id="p-cliente-box" style="display:none">
                <strong id="p-cliente-nombre"></strong>
                <span id="p-cliente-contacto"></span>
            </div>

            <div class="doc-detalles" id="p-detalles">
                <p><strong>Duración estimada:</strong> <span id="p-duracion">4-5 horas</span></p>
                <p><strong>Ubicación:</strong> <span id="p-ubicacion">Espacio proporcionado por el cliente</span></p>
                <p><strong>Tipo de fotografía:</strong> <span id="p-tipo">Retrato cosmético</span></p>
                <p><strong>Entrega:</strong> <span id="p-entrega">20-30 fotografías editadas levemente en alta resolución</span></p>
            </div>

            <table class="doc-table">
                <thead>
                    <tr><th>Concepto</th><th>Precio (€)</th></tr>
                </thead>
                <tbody id="p-table-body">
                    <!-- filas generadas por JS -->
                </tbody>
                <tfoot id="p-table-foot">
                    <!-- totales generados por JS -->
                </tfoot>
            </table>

            <div class="doc-opcionales" id="p-opcionales-box" style="display:none">
                <h4>Opcionales:</h4>
                <p id="p-opcionales"></p>
            </div>

            <div class="doc-notas" id="p-notas-box" style="display:none">
                <h4>Notas</h4>
                <p id="p-notas"></p>
            </div>

            <div class="doc-validez" id="p-validez">Presupuesto válido durante 30 días</div>

            <div class="doc-footer">
                <span>WolfFilms — Ángel Fragoso Sánchez</span>
                <span>angelsanchez@wolffilms.es · +34 628 55 82 25</span>
                <span>wolffilms.es</span>
            </div>
        </div>
    </div>

</div>

<script>
// ── Datos iniciales por defecto ──────────────────────────────────────────────
const defaultRows = [
    { concepto: 'Horarios de sesión',                    precio: '250' },
    { concepto: 'Edición leve (color, detalle de piel)', precio: '100' },
    { concepto: 'Desplazamiento / montaje de equipo',    precio: 'Incluido' },
    { concepto: 'Entrega digital',                       precio: 'Incluido' },
    { concepto: 'Derechos de uso comercial',             precio: '75' },
];

// ── Inicializar filas ─────────────────────────────────────────────────────────
function addRow(concepto = '', precio = '') {
    const list = document.getElementById('conceptos-list');
    const idx = list.children.length;
    const div = document.createElement('div');
    div.className = 'concepto-row';
    div.innerHTML = `
        <input type="text" placeholder="Concepto" value="${escHtml(concepto)}"
               oninput="updatePreview()" onchange="updatePreview()">
        <input type="text" placeholder="Precio o Incluido" value="${escHtml(precio)}"
               oninput="updatePreview()" onchange="updatePreview()">
        <button class="btn-del-row" onclick="this.parentElement.remove();updatePreview()">×</button>
    `;
    list.appendChild(div);
    updatePreview();
}

function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;'); }

defaultRows.forEach(r => addRow(r.concepto, r.precio));

// ── Actualizar preview en tiempo real ─────────────────────────────────────────
function fmt(n) {
    if (isNaN(n) || n === '') return '—';
    return parseFloat(n).toFixed(2).replace('.', ',') + ' €';
}

function updatePreview() {
    // Campos simples
    const bind = [
        ['f-titulo',    'p-titulo',    v => v || 'PRESUPUESTO'],
        ['f-numero',    'p-numero',    v => v],
        ['f-duracion',  'p-duracion',  v => v],
        ['f-ubicacion', 'p-ubicacion', v => v],
        ['f-tipo',      'p-tipo',      v => v],
        ['f-entrega',   'p-entrega',   v => v],
    ];
    bind.forEach(([fid, pid, fn]) => {
        const el = document.getElementById(pid);
        if (el) el.textContent = fn(document.getElementById(fid)?.value || '');
    });

    // Fecha formateada
    const fechaVal = document.getElementById('f-fecha')?.value;
    if (fechaVal) {
        const [y,m,d] = fechaVal.split('-');
        document.getElementById('p-fecha').textContent = `${d}/${m}/${y}`;
    }

    // Validez
    const validez = document.getElementById('f-validez')?.value;
    document.getElementById('p-validez').textContent =
        validez ? `Presupuesto válido durante ${validez}` : '';

    // Cliente
    const nombre = document.getElementById('f-cliente-nombre')?.value.trim();
    const email  = document.getElementById('f-cliente-email')?.value.trim();
    const tel    = document.getElementById('f-cliente-tel')?.value.trim();
    const clienteBox = document.getElementById('p-cliente-box');
    if (nombre || email || tel) {
        clienteBox.style.display = '';
        document.getElementById('p-cliente-nombre').textContent = nombre || '';
        let contacto = [email, tel].filter(Boolean).join(' · ');
        document.getElementById('p-cliente-contacto').textContent = contacto;
    } else {
        clienteBox.style.display = 'none';
    }

    // Conceptos
    const rows = document.querySelectorAll('#conceptos-list .concepto-row');
    const tbody = document.getElementById('p-table-body');
    const tfoot = document.getElementById('p-table-foot');
    tbody.innerHTML = '';
    let subtotal = 0;

    rows.forEach(row => {
        const inputs = row.querySelectorAll('input');
        const concepto = inputs[0].value.trim();
        const precioRaw = inputs[1].value.trim();
        if (!concepto) return;

        const isNum = !isNaN(parseFloat(precioRaw)) && precioRaw !== '';
        if (isNum) subtotal += parseFloat(precioRaw);

        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${escHtml(concepto)}</td><td>${isNum ? fmt(precioRaw) : escHtml(precioRaw || '—')}</td>`;
        tbody.appendChild(tr);
    });

    // Totales
    const ivaPct = parseFloat(document.getElementById('f-iva')?.value) || 0;
    const ivaAmount = subtotal * (ivaPct / 100);
    const total = subtotal + ivaAmount;

    tfoot.innerHTML = '';
    if (ivaPct > 0) {
        tfoot.innerHTML = `
            <tr class="total-row"><td>Subtotal</td><td>${fmt(subtotal)}</td></tr>
            <tr class="iva-row"><td>IVA (${ivaPct}%)</td><td>${fmt(ivaAmount)}</td></tr>
            <tr class="grandtotal-row"><td>TOTAL</td><td>${fmt(total)}</td></tr>
        `;
    } else {
        tfoot.innerHTML = `
            <tr class="grandtotal-row"><td>Total estimado</td><td>${fmt(subtotal)}</td></tr>
        `;
    }

    // Opcionales
    const opcs = document.getElementById('f-opcionales')?.value.trim();
    const opcsBox = document.getElementById('p-opcionales-box');
    if (opcs) {
        opcsBox.style.display = '';
        document.getElementById('p-opcionales').textContent = opcs;
    } else {
        opcsBox.style.display = 'none';
    }

    // Notas
    const notas = document.getElementById('f-notas')?.value.trim();
    const notasBox = document.getElementById('p-notas-box');
    if (notas) {
        notasBox.style.display = '';
        document.getElementById('p-notas').textContent = notas;
    } else {
        notasBox.style.display = 'none';
    }
}

// ── Escuchar todos los inputs ─────────────────────────────────────────────────
document.querySelectorAll('#\\31 a1a1a input, #\\31 a1a1a textarea, .form-panel input, .form-panel textarea, .form-panel select')
    .forEach(el => el.addEventListener('input', updatePreview));

// Escucha delegada en el panel
document.querySelector('.form-panel').addEventListener('input', updatePreview);

// Render inicial
updatePreview();
</script>

</body>
</html>
