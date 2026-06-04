<?php
define('UPLOAD_PASSWORD', 'wolffilms2024');
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: admin-upload.php');
    exit;
}

// Número automático
$num_base = date('Ymd');
$numero_auto = 'WF-' . $num_base . '-001';

// Logo SVG: quitar declaración XML para incrustar inline
$logo_svg = '';
$logo_path = 'imagenes/logo wolf.svg';
if (file_exists($logo_path)) {
    $raw = file_get_contents($logo_path);
    // Eliminar <?xml ...?> y limpiar
    $logo_svg = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $raw);
    // Añadir atributos de tamaño si no los tiene
    $logo_svg = preg_replace('/<svg /', '<svg style="width:100%;height:auto;" ', $logo_svg, 1);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generador de Proformas — WolfFilms</title>
<style>
/* ══════════════════════ PANEL (sólo pantalla) ══════════════════════ */
@media screen {
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Segoe UI',sans-serif;background:#111;color:#eee;}

    .ph{background:#1a1a1a;padding:.8rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #2a2a2a;position:sticky;top:0;z-index:200;}
    .ph h1{font-size:.95rem;font-weight:600;letter-spacing:.05em;}
    .ph a{color:#666;text-decoration:none;font-size:.82rem;}
    .ph a:hover{color:#fff;}

    .layout{display:grid;grid-template-columns:400px 1fr;height:calc(100vh - 48px);overflow:hidden;}

    /* ── Formulario ── */
    .fp{background:#1a1a1a;border-right:1px solid #2a2a2a;overflow-y:auto;padding:1.2rem;}
    .fp h2{font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;color:#555;margin:1.2rem 0 .6rem;padding-bottom:.4rem;border-bottom:1px solid #222;}
    .fp h2:first-child{margin-top:0;}
    .fld{margin-bottom:.65rem;}
    .fld label{display:block;font-size:.72rem;color:#777;margin-bottom:.25rem;}
    .fld input,.fld textarea,.fld select{width:100%;padding:.55rem .75rem;background:#111;border:1px solid #252525;border-radius:6px;color:#ddd;font-size:.82rem;font-family:inherit;transition:border-color .2s;}
    .fld input:focus,.fld textarea:focus{outline:none;border-color:#555;}
    .fld textarea{resize:vertical;min-height:65px;}

    .row-grid{display:grid;grid-template-columns:1fr 95px 26px;gap:.35rem;align-items:center;margin-bottom:.35rem;}
    .row-grid input{padding:.45rem .55rem;background:#111;border:1px solid #252525;border-radius:5px;color:#ddd;font-size:.78rem;width:100%;}
    .row-grid input:focus{outline:none;border-color:#555;}
    .del-btn{background:none;border:1px solid #2a2a2a;border-radius:5px;color:#555;cursor:pointer;width:26px;height:26px;font-size:1rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .del-btn:hover{border-color:#e74c3c;color:#e74c3c;}
    .add-row-btn{background:none;border:1px dashed #2a2a2a;border-radius:6px;width:100%;padding:.45rem;color:#555;font-size:.78rem;cursor:pointer;margin-top:.2rem;}
    .add-row-btn:hover{border-color:#555;color:#999;}

    /* Selector de diseño */
    .design-sel{display:flex;gap:.5rem;margin-bottom:1rem;}
    .ds-btn{flex:1;padding:.55rem;border:1px solid #2a2a2a;border-radius:7px;background:none;color:#666;font-size:.78rem;cursor:pointer;transition:all .2s;}
    .ds-btn.active{border-color:#fff;color:#fff;background:#222;}
    .ds-btn:hover:not(.active){border-color:#555;color:#aaa;}

    .print-btn{background:#fff;color:#111;border:none;padding:.8rem;border-radius:8px;font-weight:700;font-size:.9rem;cursor:pointer;width:100%;margin-top:1.2rem;}
    .print-btn:hover{background:#ddd;}
    .hint{text-align:center;font-size:.72rem;color:#444;margin-top:.4rem;}

    /* ── Preview ── */
    .pp{background:#888;overflow-y:auto;display:flex;justify-content:center;padding:1.5rem;}
}

/* ══════════════════════ DISEÑO 1 — Fiel al PDF ══════════════════════ */
.d1{
    width:210mm;min-height:297mm;
    background:#b8d4e3; /* fondo azul claro como el PDF */
    padding:14mm 16mm;
    font-family:Arial,Helvetica,sans-serif;
    color:#111;
    position:relative;
    overflow:hidden;
    box-shadow:0 4px 30px rgba(0,0,0,.4);
}

/* Marca de agua */
.d1-wm{
    position:absolute;
    top:50%; left:50%;
    transform:translate(-50%,-50%) rotate(-15deg);
    width:180mm;
    opacity:.06;
    pointer-events:none;
    z-index:0;
}
.d1-wm svg{width:100%;height:auto;}

.d1>*:not(.d1-wm){position:relative;z-index:1;}

.d1-logo{display:flex;justify-content:center;margin-bottom:8mm;}
.d1-logo .logo-wrap{width:65mm;}
.d1-logo .logo-wrap svg{width:100%;height:auto;}

.d1-title{
    text-align:center;
    font-size:18pt;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:8mm;
    line-height:1.1;
}

.d1-meta{display:flex;justify-content:space-between;margin-bottom:6mm;font-size:8pt;color:#333;}

.d1-cliente{background:rgba(255,255,255,.45);border-left:3px solid #1a1a1a;padding:3mm 4mm;margin-bottom:5mm;font-size:8.5pt;}
.d1-cliente strong{display:block;font-size:9.5pt;font-weight:800;margin-bottom:.5mm;}

.d1-detalles{margin-bottom:7mm;}
.d1-detalles p{font-size:9pt;margin-bottom:1.5mm;line-height:1.4;}
.d1-detalles strong{font-weight:700;}

.d1-table{width:100%;border-collapse:collapse;margin-bottom:6mm;background:#fff;}
.d1-table th{background:#111;color:#fff;padding:2.5mm 4mm;font-size:9pt;font-weight:700;text-align:left;}
.d1-table th:last-child{text-align:right;width:28mm;}
.d1-table td{padding:2mm 4mm;font-size:8.5pt;border-bottom:1px solid #ddd;background:#fff;}
.d1-table td:last-child{text-align:right;font-weight:600;}
.d1-table tr.iva-r td{font-size:7.5pt;color:#666;border-bottom:none;background:#f5f5f5;}
.d1-table tr.sub-r td{background:#f5f5f5;font-weight:700;}
.d1-table tr.tot-r td{background:#111;color:#fff;font-weight:900;font-size:10pt;border:none;}

.d1-opts{margin-bottom:5mm;}
.d1-opts strong{display:block;font-size:9.5pt;font-weight:800;margin-bottom:1.5mm;}
.d1-opts p{font-size:8.5pt;line-height:1.7;white-space:pre-line;}

.d1-notas{background:rgba(255,255,255,.4);padding:3mm 4mm;border-radius:1mm;margin-bottom:4mm;}
.d1-notas strong{display:block;font-size:8pt;font-weight:700;margin-bottom:1mm;color:#444;}
.d1-notas p{font-size:7.5pt;color:#555;line-height:1.5;white-space:pre-line;}

.d1-validez{text-align:center;font-size:7.5pt;color:#555;margin-bottom:5mm;}

.d1-footer{border-top:1px solid rgba(0,0,0,.2);padding-top:3mm;display:flex;justify-content:space-between;font-size:7pt;color:#555;}

/* ══════════════════════ DISEÑO 2 — Alternativo ══════════════════════ */
.d2{
    width:210mm;min-height:297mm;
    background:#fff;
    padding:14mm 16mm;
    font-family:'Helvetica Neue',Arial,sans-serif;
    color:#1a1a1a;
    box-shadow:0 4px 30px rgba(0,0,0,.4);
}

.d2-header{background:#111;margin:-14mm -16mm 10mm;padding:10mm 16mm;display:flex;align-items:center;justify-content:space-between;}
.d2-header .logo-wrap{width:55mm;}
.d2-header .logo-wrap svg{width:100%;height:auto;filter:invert(1);}
.d2-header-right{text-align:right;color:#ccc;}
.d2-header-right .d2-num{font-size:9pt;letter-spacing:.08em;color:#888;text-transform:uppercase;}
.d2-header-right .d2-titulo{font-size:12pt;font-weight:800;color:#fff;margin-top:1mm;}
.d2-header-right .d2-fecha{font-size:8pt;color:#777;margin-top:1mm;}

.d2-cliente{border:1px solid #eee;border-radius:2mm;padding:4mm 5mm;margin-bottom:6mm;font-size:9pt;}
.d2-cliente strong{display:block;font-size:10pt;font-weight:800;margin-bottom:.5mm;}
.d2-cliente span{color:#666;font-size:8pt;}

.d2-detalles{display:grid;grid-template-columns:1fr 1fr;gap:2mm;margin-bottom:7mm;}
.d2-det-item{background:#f8f8f8;padding:2.5mm 4mm;border-radius:1.5mm;}
.d2-det-item .lbl{font-size:7pt;text-transform:uppercase;letter-spacing:.08em;color:#999;margin-bottom:.5mm;}
.d2-det-item .val{font-size:8.5pt;font-weight:600;color:#111;}

.d2-table{width:100%;border-collapse:collapse;margin-bottom:5mm;}
.d2-table th{background:#111;color:#fff;padding:2.5mm 4mm;font-size:8.5pt;font-weight:700;text-align:left;}
.d2-table th:last-child{text-align:right;width:28mm;}
.d2-table td{padding:2mm 4mm;font-size:8.5pt;border-bottom:1px solid #f0f0f0;}
.d2-table td:last-child{text-align:right;font-weight:600;}
.d2-table tr.iva-r td{font-size:7.5pt;color:#888;border-bottom:none;}
.d2-table tr.sub-r td{background:#f8f8f8;font-weight:700;}
.d2-table tr.tot-r td{background:#111;color:#fff;font-weight:900;font-size:10.5pt;border:none;}

.d2-opts{margin-top:5mm;border-top:1px solid #eee;padding-top:4mm;}
.d2-opts strong{display:block;font-size:8.5pt;font-weight:800;margin-bottom:1.5mm;color:#333;text-transform:uppercase;letter-spacing:.06em;}
.d2-opts p{font-size:8pt;color:#666;line-height:1.7;white-space:pre-line;}

.d2-notas{margin-top:4mm;border:1px solid #eee;border-radius:2mm;padding:3mm 4mm;}
.d2-notas strong{display:block;font-size:7.5pt;font-weight:700;color:#999;text-transform:uppercase;margin-bottom:1mm;}
.d2-notas p{font-size:8pt;color:#666;white-space:pre-line;line-height:1.5;}

.d2-validez{text-align:center;font-size:7.5pt;color:#aaa;margin:4mm 0;}

.d2-footer{border-top:1px solid #eee;padding-top:3mm;display:flex;justify-content:space-between;font-size:7pt;color:#aaa;}

/* ══════════════════════ PRINT ══════════════════════ */
@media print{
    body{background:#fff!important;}
    .ph,.fp{display:none!important;}
    .layout{display:block!important;height:auto!important;}
    .pp{background:#fff!important;padding:0!important;display:block!important;}
    .d1,.d2{box-shadow:none!important;width:100%!important;}
    [data-design="1"] .d2,[data-design="2"] .d1{display:none!important;}
}
</style>
</head>
<body>

<div class="ph">
    <h1>📄 Generador de Proformas — WolfFilms</h1>
    <a href="admin-upload.php">← Panel principal</a>
</div>

<div class="layout">

<!-- ══════════ FORMULARIO ══════════ -->
<div class="fp">

    <h2>Diseño</h2>
    <div class="design-sel">
        <button class="ds-btn active" onclick="setDesign(1,this)">📋 Diseño 1 — PDF original</button>
        <button class="ds-btn"        onclick="setDesign(2,this)">✨ Diseño 2 — Alternativo</button>
    </div>

    <h2>Nº y Fecha</h2>
    <div class="fld"><label>Número de presupuesto</label><input type="text" id="f-num" value="<?= htmlspecialchars($numero_auto) ?>"></div>
    <div class="fld"><label>Fecha</label><input type="date" id="f-fecha" value="<?= date('Y-m-d') ?>"></div>
    <div class="fld"><label>Validez</label><input type="text" id="f-validez" value="30 días"></div>

    <h2>Título</h2>
    <div class="fld"><input type="text" id="f-titulo" value="PRESUPUESTO SESIÓN FOTOGRÁFICA"></div>

    <h2>Cliente</h2>
    <div class="fld"><label>Nombre</label><input type="text" id="f-cnombre" placeholder="Ej: María García López"></div>
    <div class="fld"><label>Email</label><input type="email" id="f-cemail" placeholder="cliente@email.com"></div>
    <div class="fld"><label>Teléfono</label><input type="text" id="f-ctel" placeholder="+34 600 000 000"></div>

    <h2>Detalles de la sesión</h2>
    <div class="fld"><label>Duración estimada</label><input type="text" id="f-dur" value="4-5 horas"></div>
    <div class="fld"><label>Ubicación</label><input type="text" id="f-ubi" value="Espacio proporcionado por el cliente"></div>
    <div class="fld"><label>Tipo de fotografía</label><input type="text" id="f-tipo" value="Retrato cosmético"></div>
    <div class="fld"><label>Entrega</label><input type="text" id="f-ent" value="20-30 fotografías editadas levemente en alta resolución"></div>

    <h2>Conceptos</h2>
    <div style="display:grid;grid-template-columns:1fr 95px 26px;gap:.35rem;margin-bottom:.3rem;">
        <span style="font-size:.68rem;color:#444">Concepto</span>
        <span style="font-size:.68rem;color:#444">Precio</span>
        <span></span>
    </div>
    <div id="rows-list"></div>
    <button class="add-row-btn" onclick="addRow()">+ Añadir línea</button>

    <h2>IVA</h2>
    <div class="fld" style="display:flex;gap:.5rem;align-items:center;">
        <input type="number" id="f-iva" value="21" min="0" max="100" style="width:65px">
        <span style="font-size:.78rem;color:#666">% (0 = sólo total sin IVA)</span>
    </div>

    <h2>Opcionales</h2>
    <div class="fld"><textarea id="f-opts">Versión con mayor retoque de piel o edición avanzada +10 €/foto
Fotografía adicional fuera del lote (más de 30 fotos) +10 €/foto
Entrega exprés en 48h +50 €</textarea></div>

    <h2>Notas adicionales</h2>
    <div class="fld"><textarea id="f-notas" placeholder="Formas de pago, condiciones, etc."></textarea></div>

    <button class="print-btn" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
    <p class="hint">En el diálogo de impresión → «Guardar como PDF»</p>
</div>

<!-- ══════════ PREVIEW ══════════ -->
<div class="pp" id="pp" data-design="1">

    <!-- ─── DISEÑO 1 ─── -->
    <div class="d1" id="doc1">
        <!-- Marca de agua -->
        <div class="d1-wm"><?= $logo_svg ?></div>

        <!-- Logo -->
        <div class="d1-logo">
            <div class="logo-wrap"><?= $logo_svg ?></div>
        </div>

        <div class="d1-title" id="p1-titulo">PRESUPUESTO SESIÓN FOTOGRÁFICA</div>

        <div class="d1-meta">
            <span>Nº <strong id="p1-num"><?= htmlspecialchars($numero_auto) ?></strong></span>
            <span>Fecha: <strong id="p1-fecha"><?= date('d/m/Y') ?></strong></span>
        </div>

        <div class="d1-cliente" id="p1-cliente" style="display:none">
            <strong id="p1-cnombre"></strong>
            <span id="p1-ccontacto"></span>
        </div>

        <div class="d1-detalles">
            <p><strong>Duración estimada:</strong> <span id="p1-dur">4-5 horas</span></p>
            <p><strong>Ubicación:</strong> <span id="p1-ubi">Espacio proporcionado por el cliente</span></p>
            <p><strong>Tipo de fotografía:</strong> <span id="p1-tipo">Retrato cosmético</span></p>
            <p><strong>Entrega:</strong> <span id="p1-ent">20-30 fotografías editadas levemente en alta resolución</span></p>
        </div>

        <table class="d1-table">
            <thead><tr><th>Concepto</th><th>Precio (€)</th></tr></thead>
            <tbody id="p1-tbody"></tbody>
            <tfoot id="p1-tfoot"></tfoot>
        </table>

        <div class="d1-opts" id="p1-opts-box" style="display:none">
            <strong>Opcionales:</strong>
            <p id="p1-opts"></p>
        </div>

        <div class="d1-notas" id="p1-notas-box" style="display:none">
            <strong>Notas</strong>
            <p id="p1-notas"></p>
        </div>

        <div class="d1-validez" id="p1-validez">Presupuesto válido durante 30 días</div>

        <div class="d1-footer">
            <span>WolfFilms — Ángel Fragoso Sánchez</span>
            <span>angelsanchez@wolffilms.es · +34 628 55 82 25</span>
            <span>wolffilms.es</span>
        </div>
    </div>

    <!-- ─── DISEÑO 2 ─── -->
    <div class="d2" id="doc2" style="display:none">
        <div class="d2-header">
            <div class="logo-wrap"><?= $logo_svg ?></div>
            <div class="d2-header-right">
                <div class="d2-num">Presupuesto <span id="p2-num"><?= htmlspecialchars($numero_auto) ?></span></div>
                <div class="d2-titulo" id="p2-titulo">PRESUPUESTO SESIÓN FOTOGRÁFICA</div>
                <div class="d2-fecha" id="p2-fecha"><?= date('d/m/Y') ?></div>
            </div>
        </div>

        <div class="d2-cliente" id="p2-cliente" style="display:none">
            <strong id="p2-cnombre"></strong>
            <span id="p2-ccontacto"></span>
        </div>

        <div class="d2-detalles" id="p2-detalles">
            <div class="d2-det-item"><div class="lbl">Duración</div><div class="val" id="p2-dur">4-5 horas</div></div>
            <div class="d2-det-item"><div class="lbl">Tipo</div><div class="val" id="p2-tipo">Retrato cosmético</div></div>
            <div class="d2-det-item"><div class="lbl">Ubicación</div><div class="val" id="p2-ubi">Espacio del cliente</div></div>
            <div class="d2-det-item"><div class="lbl">Entrega</div><div class="val" id="p2-ent">20-30 fotografías</div></div>
        </div>

        <table class="d2-table">
            <thead><tr><th>Concepto</th><th>Precio (€)</th></tr></thead>
            <tbody id="p2-tbody"></tbody>
            <tfoot id="p2-tfoot"></tfoot>
        </table>

        <div class="d2-opts" id="p2-opts-box" style="display:none">
            <strong>Opcionales</strong>
            <p id="p2-opts"></p>
        </div>

        <div class="d2-notas" id="p2-notas-box" style="display:none">
            <strong>Notas</strong>
            <p id="p2-notas"></p>
        </div>

        <div class="d2-validez" id="p2-validez">Presupuesto válido durante 30 días</div>

        <div class="d2-footer">
            <span>WolfFilms — Ángel Fragoso Sánchez</span>
            <span>angelsanchez@wolffilms.es · +34 628 55 82 25</span>
            <span>wolffilms.es</span>
        </div>
    </div>

</div><!-- /pp -->
</div><!-- /layout -->

<script>
// ── Filas por defecto ─────────────────────────────────────────────────────────
const DEFAULT_ROWS = [
    ['Horarios de sesión',                     '250'],
    ['Edición leve (color, detalle de piel)',   '100'],
    ['Desplazamiento / montaje de equipo',      'Incluido'],
    ['Entrega digital',                         'Incluido'],
    ['Derechos de uso comercial',               '75'],
];

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;'); }
function fmt(n){ return parseFloat(n).toFixed(2).replace('.',',') + ' €'; }

function addRow(c='', p=''){
    const list = document.getElementById('rows-list');
    const d = document.createElement('div');
    d.className = 'row-grid';
    d.innerHTML = `<input type="text" placeholder="Concepto" value="${esc(c)}">
                   <input type="text" placeholder="Precio/Incluido" value="${esc(p)}">
                   <button class="del-btn" onclick="this.parentElement.remove();upd()">×</button>`;
    list.appendChild(d);
    upd();
}
DEFAULT_ROWS.forEach(r => addRow(r[0], r[1]));

// ── Diseño activo ─────────────────────────────────────────────────────────────
let activeDesign = 1;
function setDesign(n, btn){
    activeDesign = n;
    document.querySelectorAll('.ds-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('doc1').style.display = n===1 ? '' : 'none';
    document.getElementById('doc2').style.display = n===2 ? '' : 'none';
    document.getElementById('pp').dataset.design = n;
}

// ── Actualizar preview ────────────────────────────────────────────────────────
function upd(){
    const v = id => document.getElementById(id)?.value ?? '';
    const set = (id, txt) => { const el=document.getElementById(id); if(el) el.textContent=txt; };

    // Fecha formateada
    const fd = v('f-fecha');
    const fmtDate = fd ? fd.split('-').reverse().join('/') : '';

    // ── Diseño 1
    set('p1-titulo',  v('f-titulo')||'PRESUPUESTO');
    set('p1-num',     v('f-num'));
    set('p1-fecha',   fmtDate);
    set('p1-dur',     v('f-dur'));
    set('p1-ubi',     v('f-ubi'));
    set('p1-tipo',    v('f-tipo'));
    set('p1-ent',     v('f-ent'));

    // ── Diseño 2
    set('p2-titulo',  v('f-titulo')||'PRESUPUESTO');
    set('p2-num',     v('f-num'));
    set('p2-fecha',   fmtDate);
    set('p2-dur',     v('f-dur'));
    set('p2-ubi',     v('f-ubi'));
    set('p2-tipo',    v('f-tipo'));
    set('p2-ent',     v('f-ent'));

    // Validez
    const val = v('f-validez');
    set('p1-validez', val ? `Presupuesto válido durante ${val}` : '');
    set('p2-validez', val ? `Presupuesto válido durante ${val}` : '');

    // Cliente
    const cn = v('f-cnombre').trim();
    const ce = v('f-cemail').trim();
    const ct = v('f-ctel').trim();
    const cc = [ce, ct].filter(Boolean).join(' · ');
    ['p1','p2'].forEach(p=>{
        const box = document.getElementById(p+'-cliente');
        if(!box) return;
        if(cn||ce||ct){
            box.style.display='';
            set(p+'-cnombre', cn);
            set(p+'-ccontacto', cc);
        } else { box.style.display='none'; }
    });

    // Conceptos
    const rows = document.querySelectorAll('.row-grid');
    let sub = 0;
    let tbodyHTML = '';
    rows.forEach(r=>{
        const ins = r.querySelectorAll('input');
        const c = ins[0].value.trim();
        const p = ins[1].value.trim();
        if(!c) return;
        const isN = p !== '' && !isNaN(parseFloat(p));
        if(isN) sub += parseFloat(p);
        tbodyHTML += `<tr><td>${esc(c)}</td><td>${isN ? fmt(p) : esc(p||'—')}</td></tr>`;
    });

    const iva = parseFloat(v('f-iva'))||0;
    const ivaAmt = sub*(iva/100);
    const total = sub+ivaAmt;
    let tfootHTML = '';
    if(iva>0){
        tfootHTML = `<tr class="sub-r"><td>Subtotal</td><td>${fmt(sub)}</td></tr>
                     <tr class="iva-r"><td>IVA (${iva}%)</td><td>${fmt(ivaAmt)}</td></tr>
                     <tr class="tot-r"><td>TOTAL</td><td>${fmt(total)}</td></tr>`;
    } else {
        tfootHTML = `<tr class="tot-r"><td>Total estimado</td><td>${fmt(sub)} +IVA</td></tr>`;
    }

    ['p1','p2'].forEach(p=>{
        const tb = document.getElementById(p+'-tbody');
        const tf = document.getElementById(p+'-tfoot');
        if(tb) tb.innerHTML = tbodyHTML;
        if(tf) tf.innerHTML = tfootHTML;
    });

    // Opcionales
    const opts = v('f-opts').trim();
    ['p1','p2'].forEach(p=>{
        const box = document.getElementById(p+'-opts-box');
        const el  = document.getElementById(p+'-opts');
        if(!box||!el) return;
        box.style.display = opts ? '' : 'none';
        el.textContent = opts;
    });

    // Notas
    const notas = v('f-notas').trim();
    ['p1','p2'].forEach(p=>{
        const box = document.getElementById(p+'-notas-box');
        const el  = document.getElementById(p+'-notas');
        if(!box||!el) return;
        box.style.display = notas ? '' : 'none';
        el.textContent = notas;
    });
}

// ── Escuchar cambios ─────────────────────────────────────────────────────────
document.querySelector('.fp').addEventListener('input', upd);
upd();
</script>
</body>
</html>
