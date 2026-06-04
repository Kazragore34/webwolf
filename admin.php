<?php
// ═══════════════════════════════════════════════════
//  WolfFilms — Panel de Administración unificado
// ═══════════════════════════════════════════════════
define('UPLOAD_PASSWORD', 'wolffilms2024');
session_start();

$msg = ''; $msg_tipo = '';

// ── Auth ──────────────────────────────────────────
if (isset($_POST['password'])) {
    if ($_POST['password'] === UPLOAD_PASSWORD) $_SESSION['admin_logged'] = true;
    else { $msg = 'Contraseña incorrecta.'; $msg_tipo = 'error'; }
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

// ── Helpers ───────────────────────────────────────
function leerJSON($p) { if (!file_exists($p)) return []; $d=json_decode(file_get_contents($p),true); return is_array($d)?$d:[]; }
function guardarJSON($p,$d) { file_put_contents($p,json_encode($d,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }
function ytId($url) { preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',$url,$m); return $m[1]??null; }

// ── Contador de proformas ─────────────────────────
function getNextNum() {
    $file = 'proforma_counter.json';
    $today = date('Ymd');
    $d = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $count = (isset($d['date']) && $d['date'] === $today) ? ($d['count'] + 1) : 1;
    file_put_contents($file, json_encode(['date'=>$today,'count'=>$count]));
    return 'WF-' . $today . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
}
function revisionNum($n) {
    // WF-xxx-001 → WF-xxx-001-R1 | WF-xxx-001-R1 → WF-xxx-001-R2
    if (preg_match('/-R(\d+)$/', $n, $m))
        return preg_replace('/-R\d+$/', '-R'.($m[1]+1), $n);
    return $n.'-R1';
}

// ── Logo SVG ──────────────────────────────────────
// Función que devuelve el SVG con un prefijo de ID único para evitar
// duplicados cuando se incrusta varias veces en la misma página.
$logo_svg_raw = '';
$logo_path = 'imagenes/logo wolf.svg';
if (file_exists($logo_path)) {
    $raw = file_get_contents($logo_path);
    // Quitar declaración XML
    $raw = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $raw);
    // Asegurar fill negro explícito en paths (por si hereda color incorrecto)
    $raw = str_replace('<path ', '<path fill="#111111" ', $raw);
    $logo_svg_raw = $raw;
}
$_logo_counter = 0;
function logo_svg() {
    global $logo_svg_raw, $_logo_counter;
    if (!$logo_svg_raw) return '<div style="height:40px"></div>';
    $_logo_counter++;
    $prefix = 'wflogo'.$_logo_counter;
    // Reemplazar IDs para evitar duplicados
    $svg = preg_replace('/\bid="([^"]+)"/', 'id="'.$prefix.'_$1"', $logo_svg_raw);
    return $svg;
}

// ── Acciones (sólo si autenticado) ───────────────
if (isset($_SESSION['admin_logged'])) {

    // IMÁGENES ─────────────────────────────────────
    // Eliminar imagen
    if (isset($_GET['del_img'])) {
        $f = basename($_GET['del_img']);
        $alts = leerJSON('imagenes.json');
        if (file_exists("imagenes/$f") && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i',$f)) { unlink("imagenes/$f"); unset($alts[$f]); guardarJSON('imagenes.json',$alts); }
        header('Location: admin.php?tab=imagenes'); exit;
    }
    // Subir imágenes
    if (!empty($_FILES['imagenes']['name'][0])) {
        $ok=$err=0;
        foreach ($_FILES['imagenes']['tmp_name'] as $i=>$tmp) {
            if ($_FILES['imagenes']['error'][$i]!==UPLOAD_ERR_OK){$err++;continue;}
            if (!in_array($_FILES['imagenes']['type'][$i],['image/jpeg','image/png','image/gif','image/webp'])){$err++;continue;}
            if ($_FILES['imagenes']['size'][$i]>15*1024*1024){$err++;continue;}
            $ext=strtolower(pathinfo($_FILES['imagenes']['name'][$i],PATHINFO_EXTENSION));
            if(move_uploaded_file($tmp,'imagenes/'.uniqid('img_').'.'.$ext))$ok++;else$err++;
        }
        $msg="$ok imagen(es) subida(s)".($err?", $err con error.":"."); $msg_tipo=$err&&!$ok?'error':'ok';
    }
    // Guardar alt texts
    if (isset($_POST['save_alts'])) {
        $alts = leerJSON('imagenes.json');
        foreach ($_POST['alt']??[] as $fn=>$txt) { $fn=basename($fn); if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i',$fn)) $alts[$fn]=trim($txt); }
        guardarJSON('imagenes.json',$alts);
        $msg='Descripciones guardadas.'; $msg_tipo='ok';
    }

    // VÍDEOS ───────────────────────────────────────
    // Añadir YouTube
    if (isset($_POST['yt_url']) && !empty($_POST['yt_url'])) {
        $id=ytId(trim($_POST['yt_url']));
        if($id){ $vs=leerJSON('videos.json'); $vs[]=['tipo'=>'youtube','id'=>$id,'titulo'=>trim($_POST['yt_titulo']??'')]; guardarJSON('videos.json',$vs); $msg='Vídeo añadido.'; $msg_tipo='ok'; }
        else { $msg='URL de YouTube no válida.'; $msg_tipo='error'; }
    }
    // Subir vídeo local
    if (!empty($_FILES['video_local']['name'])) {
        $vf=$_FILES['video_local'];
        if ($vf['error']===UPLOAD_ERR_OK && in_array($vf['type'],['video/mp4','video/webm','video/quicktime','video/x-msvideo']) && $vf['size']<=500*1024*1024) {
            if(!is_dir('videos'))mkdir('videos',0755,true);
            $ext=strtolower(pathinfo($vf['name'],PATHINFO_EXTENSION));
            $nombre=uniqid('video_').'.'.$ext;
            if(move_uploaded_file($vf['tmp_name'],'videos/'.$nombre)){
                $vs=leerJSON('videos.json'); $vs[]=['tipo'=>'local','archivo'=>$nombre,'titulo'=>trim($_POST['video_titulo']??'')]; guardarJSON('videos.json',$vs);
                $msg='Vídeo subido.'; $msg_tipo='ok';
            } else { $msg='Error al subir.'; $msg_tipo='error'; }
        } else { $msg='Formato no válido o muy grande (máx 500 MB).'; $msg_tipo='error'; }
    }
    // Eliminar vídeo
    if (isset($_GET['del_video'])) {
        $idx=(int)$_GET['del_video']; $vs=leerJSON('videos.json');
        if(isset($vs[$idx])){ if($vs[$idx]['tipo']==='local'&&file_exists('videos/'.$vs[$idx]['archivo']))unlink('videos/'.$vs[$idx]['archivo']); array_splice($vs,$idx,1); guardarJSON('videos.json',$vs); }
        header('Location: admin.php?tab=videos'); exit;
    }

    // PROFORMAS ────────────────────────────────────
    if (!is_dir('proformas')) mkdir('proformas', 0755, true);

    // Guardar proforma
    if (isset($_POST['save_proforma'])) {
        $pdata = json_decode($_POST['pf_json'] ?? '{}', true) ?: [];
        $num = trim($pdata['num'] ?? getNextNum());
        // Nombre de archivo seguro
        $fname = 'proformas/' . preg_replace('/[^a-zA-Z0-9\-]/', '', $num) . '.json';
        $pdata['saved_at'] = date('Y-m-d H:i:s');
        $pdata['num'] = $num;
        file_put_contents($fname, json_encode($pdata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $msg = "Proforma $num guardada."; $msg_tipo = 'ok';
    }
    // Eliminar proforma del historial
    if (isset($_GET['del_pf'])) {
        $fname = 'proformas/' . preg_replace('/[^a-zA-Z0-9\-]/', '', basename($_GET['del_pf'])) . '.json';
        if (file_exists($fname)) unlink($fname);
        header('Location: admin.php?tab=historial'); exit;
    }

    // Datos para vista
    $imgs=[]; if(is_dir('imagenes/')) foreach(scandir('imagenes/')as $f) if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i',$f)) $imgs[]=$f;
    $alts = leerJSON('imagenes.json');
    $videos = leerJSON('videos.json');

    // Historial de proformas (ordenado por fecha desc)
    $historial = [];
    if (is_dir('proformas')) {
        foreach (scandir('proformas', SCANDIR_SORT_DESCENDING) as $f) {
            if (substr($f,-5)==='.json') {
                $d = json_decode(file_get_contents('proformas/'.$f), true);
                if ($d) $historial[] = $d;
            }
        }
    }
}

$tab = $_GET['tab'] ?? 'imagenes';
// Número para la proforma actual: si viene ?edit=NUM usa ese + revisión, si no genera nuevo
$num_auto = getNextNum();
$pf_load = null; // datos a pre-cargar en el formulario
if (isset($_GET['edit']) && isset($_SESSION['admin_logged'])) {
    $efname = 'proformas/' . preg_replace('/[^a-zA-Z0-9\-]/', '', basename($_GET['edit'])) . '.json';
    if (file_exists($efname)) {
        $pf_load = json_decode(file_get_contents($efname), true);
        $num_auto = revisionNum($pf_load['num'] ?? $num_auto);
        $tab = 'proformas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Panel Admin — WolfFilms</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:#111;color:#eee;min-height:100vh;}

/* ── Top bar ── */
.topbar{background:#0d0d0d;height:48px;display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;border-bottom:1px solid #1e1e1e;position:sticky;top:0;z-index:300;}
.topbar .brand{display:flex;align-items:center;gap:.6rem;}
.topbar .brand svg{width:90px;height:auto;opacity:.85;}
.topbar .logout{color:#555;text-decoration:none;font-size:.78rem;}
.topbar .logout:hover{color:#fff;}

/* ── Layout ── */
.shell{display:flex;height:calc(100vh - 48px);}

/* ── Sidebar ── */
.sidebar{width:200px;background:#0d0d0d;border-right:1px solid #1e1e1e;display:flex;flex-direction:column;flex-shrink:0;}
.sidebar nav{padding:.8rem 0;}
.nav-item{display:flex;align-items:center;gap:.6rem;padding:.7rem 1.2rem;color:#666;text-decoration:none;font-size:.82rem;cursor:pointer;border:none;background:none;width:100%;text-align:left;transition:color .15s,background .15s;}
.nav-item:hover{color:#ccc;background:#161616;}
.nav-item.active{color:#fff;background:#1a1a1a;border-left:2px solid #fff;}
.nav-item .ico{font-size:1rem;width:20px;text-align:center;}
.sidebar-foot{margin-top:auto;padding:1rem;border-top:1px solid #1e1e1e;font-size:.7rem;color:#333;text-align:center;}

/* ── Contenido ── */
.content{flex:1;overflow:auto;}

/* Paneles normales */
.panel{display:none;padding:1.8rem;}
.panel.active{display:block;}

/* Panel proforma = layout especial */
.pf-shell{display:none;height:100%;}
.pf-shell.active{display:grid;grid-template-columns:390px 1fr;}

/* ── Mensajes ── */
.alert{padding:.75rem 1rem;border-radius:7px;margin-bottom:1.2rem;font-size:.85rem;}
.alert.ok{background:#0d2b0d;border:1px solid #2ecc71;color:#2ecc71;}
.alert.error{background:#2b0d0d;border:1px solid #e74c3c;color:#e74c3c;}

/* ── Tipografía panel ── */
.section-lbl{font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:#555;margin-bottom:.8rem;padding-bottom:.4rem;border-bottom:1px solid #1e1e1e;}
.section-lbl:not(:first-child){margin-top:1.8rem;}

/* ── Upload zone ── */
.uzone{background:#161616;border:2px dashed #2a2a2a;border-radius:8px;padding:2rem;text-align:center;cursor:pointer;transition:border-color .2s;margin-bottom:1rem;}
.uzone:hover{border-color:#fff;}
.uzone input{display:none;}
.uzone .ico{font-size:2rem;margin-bottom:.4rem;}
.uzone p{color:#666;font-size:.78rem;margin-top:.3rem;}

.pill-list{display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.8rem;}
.pill{background:#1e1e1e;border:1px solid #2a2a2a;border-radius:4px;padding:.2rem .55rem;font-size:.75rem;color:#aaa;}

/* ── Gallery grid ── */
.gg{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:.8rem;margin-top:1rem;}
.gi{background:#161616;border:1px solid #1e1e1e;border-radius:7px;overflow:hidden;}
.gi img{width:100%;height:130px;object-fit:cover;display:block;}
.gi-foot{padding:.45rem .6rem;display:flex;flex-direction:column;gap:.35rem;}
.gi-foot input{width:100%;padding:.3rem .45rem;background:#111;border:1px solid #1e1e1e;border-radius:4px;color:#ccc;font-size:.72rem;}
.gi-foot-row{display:flex;justify-content:space-between;align-items:center;}
.gi-foot-row span{font-size:.65rem;color:#444;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:110px;}

/* ── Video list ── */
.vl{display:grid;gap:.7rem;margin-top:.8rem;}
.vi{background:#161616;border:1px solid #1e1e1e;border-radius:7px;padding:.8rem 1rem;display:flex;gap:1rem;align-items:center;}
.vi-thumb{width:90px;height:51px;background:#000;border-radius:4px;flex-shrink:0;overflow:hidden;}
.vi-thumb img,.vi-thumb video{width:100%;height:100%;object-fit:cover;}
.vi-info{flex:1;}
.vi-info strong{display:block;font-size:.85rem;margin-bottom:.15rem;}
.vi-info span{font-size:.72rem;color:#555;}
.badge{display:inline-block;padding:.1rem .45rem;border-radius:3px;font-size:.65rem;font-weight:700;margin-left:.3rem;}
.badge-yt{background:#ff0000;color:#fff;}
.badge-loc{background:#2a2a2a;color:#aaa;}

/* ── Form rows ── */
.frow{display:flex;gap:.6rem;margin-bottom:.7rem;flex-wrap:wrap;}
.frow input{flex:1;min-width:180px;padding:.65rem .85rem;background:#161616;border:1px solid #1e1e1e;border-radius:7px;color:#ddd;font-size:.85rem;}
.frow input::placeholder{color:#444;}
.btn-add{background:#fff;color:#111;padding:.65rem 1.3rem;border:none;border-radius:7px;font-weight:700;cursor:pointer;white-space:nowrap;font-size:.85rem;}
.btn-add:hover{background:#ddd;}

.btn{padding:.55rem 1.1rem;border:none;border-radius:6px;cursor:pointer;font-size:.78rem;font-weight:600;}
.btn-white{background:#fff;color:#111;}
.btn-white:hover{background:#ddd;}
.btn-red{background:#c0392b;color:#fff;}
.btn-red:hover{background:#a93226;}
.btn-full{width:100%;padding:.8rem;font-size:.9rem;}

/* ═══════════════════════════════════════
   PROFORMA — Formulario (izquierda)
═══════════════════════════════════════ */
.pf-form{background:#0d0d0d;border-right:1px solid #1e1e1e;overflow-y:auto;padding:1.2rem;}
.pf-form h3{font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;color:#444;margin:1rem 0 .5rem;padding-bottom:.3rem;border-bottom:1px solid #1e1e1e;}
.pf-form h3:first-child{margin-top:0;}
.fld{margin-bottom:.6rem;}
.fld label{display:block;font-size:.7rem;color:#666;margin-bottom:.2rem;}
.fld input,.fld textarea,.fld select{width:100%;padding:.5rem .7rem;background:#111;border:1px solid #1e1e1e;border-radius:6px;color:#ddd;font-size:.8rem;font-family:inherit;transition:border-color .2s;}
.fld input:focus,.fld textarea:focus{outline:none;border-color:#555;}
.fld textarea{resize:vertical;min-height:60px;}

.ds-row{display:flex;gap:.4rem;margin-bottom:1rem;}
.ds-btn{flex:1;padding:.5rem .3rem;border:1px solid #1e1e1e;border-radius:6px;background:none;color:#555;font-size:.73rem;cursor:pointer;transition:all .2s;text-align:center;}
.ds-btn.active{border-color:#fff;color:#fff;background:#1a1a1a;}
.ds-btn:hover:not(.active){border-color:#444;color:#aaa;}

.rg{display:grid;grid-template-columns:1fr 90px 24px;gap:.3rem;align-items:center;margin-bottom:.3rem;}
.rg input{padding:.4rem .5rem;background:#111;border:1px solid #1e1e1e;border-radius:5px;color:#ddd;font-size:.76rem;width:100%;}
.rg input:focus{outline:none;border-color:#555;}
.del-r{background:none;border:1px solid #1e1e1e;border-radius:5px;color:#444;cursor:pointer;width:24px;height:24px;font-size:.9rem;display:flex;align-items:center;justify-content:center;}
.del-r:hover{border-color:#e74c3c;color:#e74c3c;}
.add-r{background:none;border:1px dashed #1e1e1e;border-radius:6px;width:100%;padding:.4rem;color:#444;font-size:.74rem;cursor:pointer;margin-top:.15rem;}
.add-r:hover{border-color:#555;color:#aaa;}

.pf-print{background:#fff;color:#111;border:none;padding:.75rem;border-radius:7px;font-weight:700;font-size:.88rem;cursor:pointer;width:100%;margin-top:1rem;}
.pf-print:hover{background:#ddd;}
.pf-hint{text-align:center;font-size:.68rem;color:#333;margin-top:.35rem;}

/* ═══════════════════════════════════════
   PROFORMA — Preview (derecha)
═══════════════════════════════════════ */
.pf-prev{background:#777;overflow-y:auto;display:flex;justify-content:center;padding:1.5rem;}

/* ─── Documento base ─── */
.doc{width:210mm;height:297mm;font-family:Arial,Helvetica,sans-serif;color:#111;box-shadow:0 4px 30px rgba(0,0,0,.5);}

/* ── Diseño 1: blanco + watermark 45° ── */
.d1{background:#fff;padding:18mm 20mm;position:relative;overflow:hidden;
    display:flex;flex-direction:column;height:297mm;}
/* Marca de agua: 45°, grande, cubre toda la diagonal */
.wm{position:absolute;top:50%;left:50%;
    transform:translate(-50%,-50%) rotate(-45deg);
    width:320mm;   /* más ancho que la diagonal del A4 */
    opacity:.06;pointer-events:none;z-index:0;}
.wm svg{width:100%;height:auto;}
.d1>*:not(.wm){position:relative;z-index:1;}
.d1-logo{display:flex;justify-content:center;margin-bottom:9mm;}
.d1-logo .lw{width:65mm;}
.d1-logo .lw svg{width:100%;height:auto;}
.d1-tit{text-align:center;font-size:18pt;font-weight:900;text-transform:uppercase;
    letter-spacing:.04em;margin-bottom:9mm;}
.d1-meta{display:flex;justify-content:space-between;margin-bottom:7mm;
    font-size:8.5pt;color:#444;padding-bottom:4mm;border-bottom:1px solid #eee;}
.d1-cli{background:#f5f5f5;border-left:3px solid #111;padding:4mm 5mm;
    margin-bottom:7mm;font-size:9pt;}
.d1-cli strong{display:block;font-weight:800;margin-bottom:.5mm;}
.d1-det{margin-bottom:9mm;}
.d1-det p{font-size:9.5pt;margin-bottom:2.5mm;line-height:1.5;}
.d1-det strong{font-weight:700;}
/* tabla ocupa espacio flexible */
.d1-tbl-wrap{flex:1;display:flex;flex-direction:column;}
.d1-tbl{width:100%;border-collapse:collapse;margin-bottom:7mm;}
.d1-tbl th{background:#111;color:#fff;padding:3mm 5mm;font-size:9.5pt;font-weight:700;text-align:left;}
.d1-tbl th:last-child{text-align:right;width:32mm;}
.d1-tbl td{padding:3mm 5mm;font-size:9pt;border-bottom:1px solid #e8e8e8;}
.d1-tbl td:last-child{text-align:right;font-weight:600;}
.d1-tbl tr.sr td{background:#f5f5f5;font-weight:700;font-size:9.5pt;}
.d1-tbl tr.ir td{font-size:8pt;color:#777;border-bottom:none;background:#f5f5f5;}
.d1-tbl tr.tr td{background:#111;color:#fff;font-weight:900;font-size:11pt;border:none;padding:4mm 5mm;}
.d1-opts{margin-bottom:7mm;}
.d1-opts strong{display:block;font-size:10pt;font-weight:800;margin-bottom:2mm;}
.d1-opts p{font-size:9pt;line-height:1.8;white-space:pre-line;}
.d1-notas{background:#f9f9f9;padding:4mm 5mm;margin-bottom:7mm;}
.d1-notas strong{display:block;font-size:8.5pt;font-weight:700;margin-bottom:1.5mm;color:#555;}
.d1-notas p{font-size:8pt;color:#666;line-height:1.6;white-space:pre-line;}
.d1-spacer{flex:1;} /* empuja footer al fondo */
.d1-val{text-align:center;font-size:8pt;color:#888;margin-bottom:5mm;}
.d1-foot{border-top:1px solid #ddd;padding-top:4mm;display:flex;
    justify-content:space-between;font-size:7.5pt;color:#888;}

/* ── Diseño 2: header negro ── */
.d2{background:#fff;height:297mm;display:flex;flex-direction:column;}
.d2-head{background:#111;padding:10mm 20mm;display:flex;align-items:center;
    justify-content:space-between;flex-shrink:0;}
.d2-head .lw{width:60mm;}
.d2-head .lw svg{width:100%;height:auto;filter:invert(1);}
.d2-hr{text-align:right;}
.d2-hr .num{font-size:8pt;color:#777;letter-spacing:.08em;text-transform:uppercase;}
.d2-hr .tit{font-size:12pt;font-weight:800;color:#fff;margin-top:1.5mm;}
.d2-hr .dt{font-size:8pt;color:#666;margin-top:1mm;}
/* cuerpo del D2 con padding */
.d2-body{padding:10mm 20mm;display:flex;flex-direction:column;flex:1;}
.d2-cli{border:1px solid #eee;border-radius:2mm;padding:4mm 5mm;
    margin-bottom:7mm;font-size:9.5pt;}
.d2-cli strong{display:block;font-weight:800;margin-bottom:.5mm;}
.d2-cli span{color:#888;font-size:8.5pt;}
.d2-det{display:grid;grid-template-columns:1fr 1fr;gap:2.5mm;margin-bottom:8mm;}
.d2-di{background:#f7f7f7;padding:3mm 4mm;border-radius:1.5mm;}
.d2-di .lbl{font-size:7pt;text-transform:uppercase;letter-spacing:.08em;
    color:#aaa;margin-bottom:.5mm;}
.d2-di .val{font-size:9pt;font-weight:600;}
.d2-tbl{width:100%;border-collapse:collapse;margin-bottom:7mm;}
.d2-tbl th{background:#111;color:#fff;padding:3mm 5mm;font-size:9pt;font-weight:700;text-align:left;}
.d2-tbl th:last-child{text-align:right;width:32mm;}
.d2-tbl td{padding:3mm 5mm;font-size:9pt;border-bottom:1px solid #f0f0f0;}
.d2-tbl td:last-child{text-align:right;font-weight:600;}
.d2-tbl tr.sr td{background:#f7f7f7;font-weight:700;}
.d2-tbl tr.ir td{font-size:8pt;color:#aaa;border-bottom:none;}
.d2-tbl tr.tr td{background:#111;color:#fff;font-weight:900;font-size:11pt;border:none;padding:4mm 5mm;}
.d2-opts{border-top:1px solid #eee;padding-top:5mm;margin-bottom:7mm;}
.d2-opts strong{display:block;font-size:8.5pt;font-weight:800;text-transform:uppercase;
    letter-spacing:.06em;color:#555;margin-bottom:2mm;}
.d2-opts p{font-size:9pt;color:#777;line-height:1.8;white-space:pre-line;}
.d2-notas{border:1px solid #eee;border-radius:2mm;padding:4mm 5mm;margin-bottom:7mm;}
.d2-notas strong{display:block;font-size:8pt;font-weight:700;color:#aaa;
    text-transform:uppercase;margin-bottom:1.5mm;}
.d2-notas p{font-size:8pt;color:#888;white-space:pre-line;line-height:1.5;}
.d2-spacer{flex:1;}
.d2-val{text-align:center;font-size:8pt;color:#bbb;margin-bottom:4mm;}
.d2-foot{border-top:1px solid #eee;padding-top:4mm;display:flex;
    justify-content:space-between;font-size:7.5pt;color:#bbb;}

/* ── Print ── */
@media print{
    body{background:#fff!important;}
    .topbar,.sidebar,.pf-form{display:none!important;}
    .shell{display:block!important;height:auto!important;}
    .pf-shell{display:block!important;height:auto!important;}
    .pf-prev{background:#fff!important;padding:0!important;display:block!important;}
    .doc{box-shadow:none!important;width:100%!important;}
    [data-design="1"] .d2,[data-design="2"] .d1{display:none!important;}
}
</style>
</head>
<body>

<?php if (!isset($_SESSION['admin_logged'])): ?>
<!-- ═══════════ LOGIN ═══════════ -->
<div style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
    <div style="background:#161616;border:1px solid #1e1e1e;border-radius:12px;padding:2.5rem;width:360px;text-align:center;">
        <div style="width:120px;margin:0 auto 1.5rem;"><?= logo_svg() ?></div>
        <h2 style="margin-bottom:1.5rem;font-size:1.2rem;font-weight:600;">Acceso al panel</h2>
        <?php if($msg):?><div class="alert <?=$msg_tipo?>"><?=htmlspecialchars($msg)?></div><?php endif;?>
        <form method="POST">
            <input type="password" name="password" placeholder="Contraseña" autofocus
                   style="width:100%;padding:.8rem 1rem;background:#111;border:1px solid #1e1e1e;border-radius:8px;color:#fff;font-size:1rem;margin-bottom:1rem;">
            <button type="submit" class="btn btn-white btn-full">Entrar</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ═══════════ PANEL ═══════════ -->

<div class="topbar">
    <div class="brand">
        <div style="width:100px;"><?= logo_svg() ?></div>
        <span style="color:#333;font-size:.78rem;margin-left:.4rem;">Admin</span>
    </div>
    <a href="?logout=1" class="logout">Cerrar sesión</a>
</div>

<div class="shell">

    <!-- ── Sidebar ── -->
    <div class="sidebar">
        <nav>
            <button class="nav-item <?= $tab==='imagenes'?'active':'' ?>" onclick="goTab('imagenes')">
                <span class="ico">📷</span> Imágenes
            </button>
            <button class="nav-item <?= $tab==='videos'?'active':'' ?>" onclick="goTab('videos')">
                <span class="ico">🎬</span> Vídeos
            </button>
            <button class="nav-item <?= $tab==='proformas'?'active':'' ?>" onclick="goTab('proformas')">
                <span class="ico">📄</span> Proformas
            </button>
            <button class="nav-item <?= $tab==='historial'?'active':'' ?>" onclick="goTab('historial')" style="padding-left:2rem;">
                <span class="ico">🗂</span> Historial
            </button>
        </nav>
        <div class="sidebar-foot">wolffilms.es</div>
    </div>

    <!-- ── Contenido ── -->
    <div class="content" id="content">

    <?php if ($msg): ?>
    <div style="padding:1rem 1.8rem 0">
        <div class="alert <?=$msg_tipo?>"><?=htmlspecialchars($msg)?></div>
    </div>
    <?php endif; ?>

    <!-- ══════════════ IMÁGENES ══════════════ -->
    <div class="panel <?= $tab==='imagenes'?'active':'' ?>" id="tab-imagenes">
        <div class="section-lbl">Subir imágenes <span style="margin-left:.4rem;background:#1e1e1e;color:#777;border-radius:20px;padding:.1rem .5rem;font-size:.68rem;"><?= count($imgs) ?></span></div>
        <form method="POST" enctype="multipart/form-data">
            <div class="uzone" onclick="document.getElementById('fi').click()">
                <div class="ico">📁</div>
                <strong>Clic o arrastra las fotos aquí</strong>
                <p>JPG, PNG, GIF, WEBP · máx 15 MB · Vertical 3:4 · Horizontal 4:3</p>
                <input type="file" id="fi" name="imagenes[]" multiple accept="image/*" onchange="showPills(this,'fp')">
            </div>
            <div class="pill-list" id="fp"></div>
            <button type="submit" class="btn btn-white" id="fi-btn" style="display:none">⬆ Subir</button>
        </form>

        <?php if(!empty($imgs)): ?>
        <div class="section-lbl" style="margin-top:1.5rem">Galería · edita la descripción SEO de cada foto</div>
        <form method="POST">
            <div class="gg">
                <?php foreach($imgs as $img): ?>
                <div class="gi">
                    <img src="imagenes/<?=htmlspecialchars($img)?>" alt="">
                    <div class="gi-foot">
                        <input type="text" name="alt[<?=htmlspecialchars($img)?>]"
                               value="<?=htmlspecialchars($alts[$img]??'')?>"
                               placeholder="Describe la foto (SEO)...">
                        <div class="gi-foot-row">
                            <span><?=htmlspecialchars($img)?></span>
                            <a href="?del_img=<?=urlencode($img)?>&tab=imagenes" class="btn btn-red" style="padding:.3rem .6rem;font-size:.7rem;" onclick="return confirm('¿Eliminar?')">✕</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:1rem">
                <button type="submit" name="save_alts" class="btn btn-white">💾 Guardar descripciones</button>
            </div>
        </form>
        <?php else: ?>
            <p style="color:#444;text-align:center;padding:2rem">No hay imágenes aún. Sube la primera arriba.</p>
        <?php endif; ?>
    </div>

    <!-- ══════════════ VÍDEOS ══════════════ -->
    <div class="panel <?= $tab==='videos'?'active':'' ?>" id="tab-videos">

        <div class="section-lbl">Añadir vídeo de YouTube</div>
        <form method="POST">
            <div class="frow">
                <input type="url" name="yt_url" placeholder="https://www.youtube.com/watch?v=...">
                <input type="text" name="yt_titulo" placeholder="Título (opcional)">
                <button type="submit" class="btn-add">+ Añadir</button>
            </div>
        </form>

        <div class="section-lbl" style="margin-top:1.5rem">Subir vídeo propio (MP4 · Instagram · etc.)</div>
        <p style="color:#444;font-size:.78rem;margin-bottom:.8rem;">Descarga desde Instagram → ⋮ → Descargar · Máx 500 MB</p>
        <form method="POST" enctype="multipart/form-data">
            <div class="uzone" onclick="document.getElementById('vi').click()">
                <div class="ico">🎬</div>
                <strong>Clic para seleccionar el vídeo</strong>
                <p>MP4, WebM, MOV · máx 500 MB</p>
                <input type="file" id="vi" name="video_local" accept="video/*" onchange="showPills(this,'vp')">
            </div>
            <div class="pill-list" id="vp"></div>
            <input type="text" name="video_titulo" placeholder="Título del vídeo (opcional)"
                   style="width:100%;padding:.6rem .8rem;background:#161616;border:1px solid #1e1e1e;border-radius:7px;color:#ddd;font-size:.82rem;margin-bottom:.7rem;">
            <button type="submit" class="btn btn-white" id="vi-btn" style="display:none">⬆ Subir vídeo</button>
        </form>

        <div class="section-lbl" style="margin-top:1.5rem">Vídeos en el Reel <span style="background:#1e1e1e;color:#777;border-radius:20px;padding:.1rem .5rem;font-size:.68rem;margin-left:.3rem;"><?=count($videos)?></span></div>
        <?php if(empty($videos)): ?>
            <p style="color:#444;text-align:center;padding:2rem">No hay vídeos aún.</p>
        <?php else: ?>
        <div class="vl">
            <?php foreach($videos as $i=>$v): ?>
            <div class="vi">
                <div class="vi-thumb">
                    <?php if($v['tipo']==='youtube'): ?>
                        <img src="https://img.youtube.com/vi/<?=htmlspecialchars($v['id'])?>/mqdefault.jpg" alt="">
                    <?php else: ?>
                        <video src="videos/<?=htmlspecialchars($v['archivo'])?>" muted></video>
                    <?php endif; ?>
                </div>
                <div class="vi-info">
                    <strong><?=htmlspecialchars($v['titulo']?:($v['tipo']==='youtube'?$v['id']:$v['archivo']))?>
                        <span class="badge <?=$v['tipo']==='youtube'?'badge-yt':'badge-loc'?>"><?=$v['tipo']==='youtube'?'YouTube':'Local'?></span>
                    </strong>
                    <span><?=$v['tipo']==='youtube'?'youtu.be/'.$v['id']:'videos/'.$v['archivo']?></span>
                </div>
                <a href="?del_video=<?=$i?>&tab=videos" class="btn btn-red" onclick="return confirm('¿Eliminar?')">Eliminar</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ══════════════ PROFORMAS (fuera de .content normal) ══════════════ -->
    </div><!-- /content -->

    <!-- Layout split para proformas -->
    <div class="pf-shell <?= $tab==='proformas'?'active':'' ?>" id="tab-proformas">

        <!-- Formulario -->
        <div class="pf-form">
            <div class="ds-row">
                <button class="ds-btn active" onclick="setD(1,this)">📋 Diseño 1 · PDF original</button>
                <button class="ds-btn"        onclick="setD(2,this)">✨ Diseño 2 · Alternativo</button>
            </div>

            <h3>Nº y fecha</h3>
            <div class="fld"><label>Número</label><input type="text" id="fn" value="<?=htmlspecialchars($num_auto)?>"></div>
            <div class="fld"><label>Fecha</label><input type="date" id="ff" value="<?=date('Y-m-d')?>"></div>
            <div class="fld"><label>Validez</label><input type="text" id="fv" value="30 días"></div>

            <h3>Título</h3>
            <div class="fld"><input type="text" id="ft" value="PRESUPUESTO SESIÓN FOTOGRÁFICA"></div>

            <h3>Cliente</h3>
            <div class="fld"><label>Nombre</label><input type="text" id="fcn" placeholder="Nombre completo"></div>
            <div class="fld"><label>Email</label><input type="email" id="fce" placeholder="email@ejemplo.com"></div>
            <div class="fld"><label>Teléfono</label><input type="text" id="fct" placeholder="+34 600 000 000"></div>

            <h3>Detalles de la sesión</h3>
            <div class="fld"><label>Duración</label><input type="text" id="fdu" value="4-5 horas"></div>
            <div class="fld"><label>Ubicación</label><input type="text" id="fub" value="Espacio proporcionado por el cliente"></div>
            <div class="fld"><label>Tipo de fotografía</label><input type="text" id="fti" value="Retrato cosmético"></div>
            <div class="fld"><label>Entrega</label><input type="text" id="fen" value="20-30 fotografías editadas levemente en alta resolución"></div>

            <h3>Conceptos</h3>
            <div style="display:grid;grid-template-columns:1fr 90px 24px;gap:.3rem;margin-bottom:.25rem;">
                <span style="font-size:.65rem;color:#333">Concepto</span>
                <span style="font-size:.65rem;color:#333">Precio</span>
                <span></span>
            </div>
            <div id="rl"></div>
            <button class="add-r" onclick="addR()">+ Añadir línea</button>

            <h3>IVA</h3>
            <div class="fld" style="display:flex;gap:.5rem;align-items:center;">
                <input type="number" id="fiva" value="21" min="0" max="100" style="width:60px;">
                <span style="font-size:.74rem;color:#444">% (0 = sin desglose IVA)</span>
            </div>

            <h3>Opcionales</h3>
            <div class="fld"><textarea id="fopts">Versión con mayor retoque de piel o edición avanzada +10 €/foto
Fotografía adicional fuera del lote (más de 30 fotos) +10 €/foto
Entrega exprés en 48h +50 €</textarea></div>

            <h3>Notas adicionales</h3>
            <div class="fld"><textarea id="fnot" placeholder="Condiciones de pago, etc."></textarea></div>

            <div style="display:flex;gap:.5rem;margin-top:1rem;">
                <button class="pf-print" style="flex:1" onclick="window.print()">🖨 Imprimir / PDF</button>
                <button class="pf-print" style="flex:1;background:#2ecc71;color:#fff;" onclick="guardarPF()">💾 Guardar</button>
            </div>
            <p class="pf-hint">Guardar añade al historial · Imprimir abre el diálogo PDF</p>

            <!-- Form oculto para guardar -->
            <form id="save-form" method="POST" style="display:none">
                <input type="hidden" name="save_proforma" value="1">
                <input type="hidden" name="pf_json" id="pf-json-input">
            </form>
        </div>

        <!-- Preview -->
        <div class="pf-prev" id="pfp" data-design="1">

            <!-- D1: blanco + watermark -->
            <div class="doc d1" id="doc1">
                <div class="wm"><?= logo_svg() ?></div>
                <div class="d1-logo"><div class="lw"><?= logo_svg() ?></div></div>
                <div class="d1-tit" id="p1t">PRESUPUESTO SESIÓN FOTOGRÁFICA</div>
                <div class="d1-meta">
                    <span>Nº <strong id="p1n"><?=htmlspecialchars($num_auto)?></strong></span>
                    <span>Fecha: <strong id="p1f"><?=date('d/m/Y')?></strong></span>
                </div>
                <div class="d1-cli" id="p1cli" style="display:none"><strong id="p1cn"></strong><span id="p1cc"></span></div>
                <div class="d1-det">
                    <p><strong>Duración estimada:</strong> <span id="p1du">4-5 horas</span></p>
                    <p><strong>Ubicación:</strong> <span id="p1ub">Espacio proporcionado por el cliente</span></p>
                    <p><strong>Tipo de fotografía:</strong> <span id="p1ti">Retrato cosmético</span></p>
                    <p><strong>Entrega:</strong> <span id="p1en">20-30 fotografías editadas levemente en alta resolución</span></p>
                </div>
                <table class="d1-tbl"><thead><tr><th>Concepto</th><th>Precio (€)</th></tr></thead>
                    <tbody id="p1tb"></tbody><tfoot id="p1tf"></tfoot></table>
                <div class="d1-opts" id="p1ob" style="display:none"><strong>Opcionales:</strong><p id="p1op"></p></div>
                <div class="d1-notas" id="p1nb" style="display:none"><strong>Notas</strong><p id="p1no"></p></div>
                <div class="d1-spacer"></div>
                <div class="d1-val" id="p1va">Presupuesto válido durante 30 días</div>
                <div class="d1-foot"><span>WolfFilms — Ángel Fragoso Sánchez</span><span>angelsanchez@wolffilms.es · +34 628 55 82 25</span><span>wolffilms.es</span></div>
            </div>

            <!-- D2: header negro + body padding -->
            <div class="doc d2" id="doc2" style="display:none">
                <div class="d2-head">
                    <div class="lw"><?= logo_svg() ?></div>
                    <div class="d2-hr">
                        <div class="num">Presupuesto <span id="p2n"><?=htmlspecialchars($num_auto)?></span></div>
                        <div class="tit" id="p2t">PRESUPUESTO SESIÓN FOTOGRÁFICA</div>
                        <div class="dt" id="p2f"><?=date('d/m/Y')?></div>
                    </div>
                </div>
                <div class="d2-body">
                    <div class="d2-cli" id="p2cli" style="display:none"><strong id="p2cn"></strong><span id="p2cc"></span></div>
                    <div class="d2-det">
                        <div class="d2-di"><div class="lbl">Duración</div><div class="val" id="p2du">4-5 horas</div></div>
                        <div class="d2-di"><div class="lbl">Tipo</div><div class="val" id="p2ti">Retrato cosmético</div></div>
                        <div class="d2-di"><div class="lbl">Ubicación</div><div class="val" id="p2ub">Espacio del cliente</div></div>
                        <div class="d2-di"><div class="lbl">Entrega</div><div class="val" id="p2en">20-30 fotografías</div></div>
                    </div>
                    <table class="d2-tbl"><thead><tr><th>Concepto</th><th>Precio (€)</th></tr></thead>
                        <tbody id="p2tb"></tbody><tfoot id="p2tf"></tfoot></table>
                    <div class="d2-opts" id="p2ob" style="display:none"><strong>Opcionales</strong><p id="p2op"></p></div>
                    <div class="d2-notas" id="p2nb" style="display:none"><strong>Notas</strong><p id="p2no"></p></div>
                    <div class="d2-spacer"></div>
                    <div class="d2-val" id="p2va">Presupuesto válido durante 30 días</div>
                    <div class="d2-foot"><span>WolfFilms — Ángel Fragoso Sánchez</span><span>angelsanchez@wolffilms.es · +34 628 55 82 25</span><span>wolffilms.es</span></div>
                </div>
            </div>

        </div><!-- /pf-prev -->
    </div><!-- /pf-shell -->

    <!-- ══════════════ HISTORIAL ══════════════ -->
    <div class="panel <?= $tab==='historial'?'active':'' ?>" id="tab-historial" style="<?= $tab==='historial'?'display:block':'display:none' ?>">
        <div class="section-lbl">Historial de proformas <span style="background:#1e1e1e;color:#777;border-radius:20px;padding:.1rem .5rem;font-size:.68rem;margin-left:.3rem;"><?=count($historial??[])?></span></div>

        <?php if(empty($historial)): ?>
            <p style="color:#444;text-align:center;padding:3rem">No hay proformas guardadas aún.<br>
            <span style="font-size:.8rem">Crea una en el apartado «Proformas» y pulsa 💾 Guardar.</span></p>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #1e1e1e;">
                    <th style="text-align:left;padding:.6rem .8rem;font-size:.72rem;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:.07em;">Número</th>
                    <th style="text-align:left;padding:.6rem .8rem;font-size:.72rem;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:.07em;">Cliente</th>
                    <th style="text-align:left;padding:.6rem .8rem;font-size:.72rem;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:.07em;">Título</th>
                    <th style="text-align:left;padding:.6rem .8rem;font-size:.72rem;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:.07em;">Guardado</th>
                    <th style="padding:.6rem .8rem;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($historial as $h): ?>
                <?php $num=htmlspecialchars($h['num']??'—'); $esRev=str_contains($h['num']??'','-R'); ?>
                <tr style="border-bottom:1px solid #161616;" onmouseover="this.style.background='#161616'" onmouseout="this.style.background=''">
                    <td style="padding:.65rem .8rem;font-size:.85rem;font-family:monospace;">
                        <?=$num?>
                        <?php if($esRev):?><span style="background:#f39c12;color:#000;border-radius:3px;padding:.1rem .35rem;font-size:.65rem;font-weight:700;margin-left:.3rem">REV</span><?php endif;?>
                    </td>
                    <td style="padding:.65rem .8rem;font-size:.85rem;color:#aaa;"><?=htmlspecialchars($h['cliente_nombre']??'—')?></td>
                    <td style="padding:.65rem .8rem;font-size:.82rem;color:#666;"><?=htmlspecialchars($h['titulo']??'—')?></td>
                    <td style="padding:.65rem .8rem;font-size:.78rem;color:#555;"><?=htmlspecialchars(substr($h['saved_at']??'',0,10))?></td>
                    <td style="padding:.65rem .8rem;display:flex;gap:.4rem;justify-content:flex-end;">
                        <a href="?tab=proformas&edit=<?=urlencode($h['num']??'')?>"
                           class="btn btn-white" style="font-size:.75rem;padding:.35rem .7rem;">✏️ Editar</a>
                        <a href="?del_pf=<?=urlencode($h['num']??'')?>&tab=historial"
                           class="btn btn-red" style="font-size:.75rem;padding:.35rem .7rem;"
                           onclick="return confirm('¿Eliminar <?=$num?>?')">✕</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div><!-- /shell -->

<script>
// ── Navegación tabs ───────────────────────────────
function goTab(t) {
    ['imagenes','videos','historial'].forEach(n=>{
        const el=document.getElementById('tab-'+n);
        if(el) el.classList.toggle('active', n===t);
        if(el) el.style.display = (n===t) ? 'block' : 'none';
    });
    const pf=document.getElementById('tab-proformas');
    if(pf){ pf.classList.toggle('active', t==='proformas'); }
    document.querySelectorAll('.nav-item').forEach(b=>{
        b.classList.toggle('active', b.getAttribute('onclick')?.includes("'"+t+"'"));
    });
    history.replaceState(null,'','?tab='+t);
}

// ── Upload pills ──────────────────────────────────
function showPills(input, listId) {
    const list = document.getElementById(listId);
    list.innerHTML = '';
    const btn = listId==='fp' ? document.getElementById('fi-btn') : document.getElementById('vi-btn');
    [...input.files].forEach(f => {
        const s=document.createElement('span'); s.className='pill'; s.textContent=f.name; list.appendChild(s);
    });
    if(btn) btn.style.display = input.files.length ? 'inline-block' : 'none';
}

// ═══════════ PROFORMAS ═══════════
const DEFAULT_ROWS=[
    ['Horarios de sesión','250'],
    ['Edición leve (color, detalle de piel)','100'],
    ['Desplazamiento / montaje de equipo','Incluido'],
    ['Entrega digital','Incluido'],
    ['Derechos de uso comercial','75'],
];
function e(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');}
function fm(n){return parseFloat(n).toFixed(2).replace('.',',')+' €';}
function set(id,t){const el=document.getElementById(id);if(el)el.textContent=t;}
function val(id){return document.getElementById(id)?.value??'';}

function addR(c='',p=''){
    const d=document.createElement('div'); d.className='rg';
    d.innerHTML=`<input type="text" placeholder="Concepto" value="${e(c)}">
                 <input type="text" placeholder="Precio/Incluido" value="${e(p)}">
                 <button class="del-r" onclick="this.parentElement.remove();upd()">×</button>`;
    document.getElementById('rl').appendChild(d); upd();
}
DEFAULT_ROWS.forEach(r=>addR(r[0],r[1]));

let activeD=1;
function setD(n,btn){
    activeD=n;
    document.querySelectorAll('.ds-btn').forEach(b=>b.classList.remove('active')); btn.classList.add('active');
    document.getElementById('doc1').style.display=n===1?'':'none';
    document.getElementById('doc2').style.display=n===2?'':'none';
    document.getElementById('pfp').dataset.design=n;
}

// ── Guardar proforma ──────────────────────────────
function guardarPF(){
    const rows=[];
    document.querySelectorAll('.rg').forEach(r=>{
        const ins=r.querySelectorAll('input');
        const c=ins[0].value.trim(), p=ins[1].value.trim();
        if(c) rows.push({c,p});
    });
    const data={
        num: val('fn'),
        titulo: val('ft'),
        fecha: val('ff'),
        validez: val('fv'),
        cliente_nombre: val('fcn'),
        cliente_email: val('fce'),
        cliente_tel: val('fct'),
        duracion: val('fdu'),
        ubicacion: val('fub'),
        tipo: val('fti'),
        entrega: val('fen'),
        iva: val('fiva'),
        opts: val('fopts'),
        notas: val('fnot'),
        design: activeD,
        rows: rows
    };
    document.getElementById('pf-json-input').value = JSON.stringify(data);
    document.getElementById('save-form').submit();
}

// ── Cargar proforma desde historial ──────────────
<?php if($pf_load): ?>
window.addEventListener('DOMContentLoaded', function(){
    const d = <?= json_encode($pf_load) ?>;
    const sv = id => { const el=document.getElementById(id); if(el) el.value=d[id]||''; };
    document.getElementById('fn').value = <?= json_encode($num_auto) ?>;
    ['ft','ff','fv','fcn','fce','fct','fdu','fub','fti','fen','fiva','fopts','fnot']
        .forEach(k=>{ const el=document.getElementById(k); if(el&&d[k.replace('f','')]){}});
    // mapeo de campos
    const map={ft:'titulo',ff:'fecha',fv:'validez',fcn:'cliente_nombre',fce:'cliente_email',
               fct:'cliente_tel',fdu:'duracion',fub:'ubicacion',fti:'tipo',fen:'entrega',
               fiva:'iva',fopts:'opts',fnot:'notas'};
    Object.entries(map).forEach(([eid,key])=>{
        const el=document.getElementById(eid);
        if(el && d[key]!==undefined) el.value=d[key];
    });
    // Filas
    if(d.rows && d.rows.length){
        document.getElementById('rl').innerHTML='';
        d.rows.forEach(r=>addR(r.c,r.p));
    }
    // Diseño
    if(d.design===2){
        const btn=document.querySelectorAll('.ds-btn')[1]; if(btn)setD(2,btn);
    }
    upd();
});
<?php endif; ?>

function upd(){
    const fd=val('ff'); const fmtD=fd?fd.split('-').reverse().join('/') : '';
    // D1
    set('p1t',val('ft')||'PRESUPUESTO'); set('p1n',val('fn')); set('p1f',fmtD);
    set('p1du',val('fdu')); set('p1ub',val('fub')); set('p1ti',val('fti')); set('p1en',val('fen'));
    // D2
    set('p2t',val('ft')||'PRESUPUESTO'); set('p2n',val('fn')); set('p2f',fmtD);
    set('p2du',val('fdu')); set('p2ub',val('fub')); set('p2ti',val('fti')); set('p2en',val('fen'));
    // Validez
    const vl=val('fv'); const vtxt=vl?`Presupuesto válido durante ${vl}`:'';
    set('p1va',vtxt); set('p2va',vtxt);
    // Cliente
    const cn=val('fcn').trim(),ce=val('fce').trim(),ct=val('fct').trim(),cc=[ce,ct].filter(Boolean).join(' · ');
    ['p1','p2'].forEach(p=>{
        const box=document.getElementById(p+'cli'); if(!box)return;
        box.style.display=(cn||ce||ct)?'':'none';
        set(p+'cn',cn); set(p+'cc',cc);
    });
    // Conceptos
    const rows=document.querySelectorAll('.rg');
    let sub=0, thtml='';
    rows.forEach(r=>{
        const ins=r.querySelectorAll('input'); const c=ins[0].value.trim(),p=ins[1].value.trim(); if(!c)return;
        const isn=p!==''&&!isNaN(parseFloat(p)); if(isn)sub+=parseFloat(p);
        thtml+=`<tr><td>${e(c)}</td><td>${isn?fm(p):e(p||'—')}</td></tr>`;
    });
    const iva=parseFloat(val('fiva'))||0, ivaA=sub*(iva/100), tot=sub+ivaA;
    let fhtml='';
    if(iva>0){
        fhtml=`<tr class="sr"><td>Subtotal</td><td>${fm(sub)}</td></tr>
               <tr class="ir"><td>IVA (${iva}%)</td><td>${fm(ivaA)}</td></tr>
               <tr class="tr"><td>TOTAL</td><td>${fm(tot)}</td></tr>`;
    } else {
        fhtml=`<tr class="tr"><td>Total estimado</td><td>${fm(sub)} +IVA</td></tr>`;
    }
    ['p1','p2'].forEach(p=>{
        const tb=document.getElementById(p+'tb'),tf=document.getElementById(p+'tf');
        if(tb)tb.innerHTML=thtml; if(tf)tf.innerHTML=fhtml;
    });
    // Opcionales
    const opts=val('fopts').trim();
    ['p1','p2'].forEach(p=>{
        const b=document.getElementById(p+'ob'),el=document.getElementById(p+'op'); if(!b||!el)return;
        b.style.display=opts?'':'none'; el.textContent=opts;
    });
    // Notas
    const not=val('fnot').trim();
    ['p1','p2'].forEach(p=>{
        const b=document.getElementById(p+'nb'),el=document.getElementById(p+'no'); if(!b||!el)return;
        b.style.display=not?'':'none'; el.textContent=not;
    });
}

document.querySelector('.pf-form').addEventListener('input',upd);
upd();
</script>

<?php endif; ?>
</body>
</html>
