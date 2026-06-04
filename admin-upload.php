<?php
// Contraseña de acceso - CAMBIA ESTO antes de subir al servidor
define('UPLOAD_PASSWORD', 'wolffilms2024');

session_start();

$mensaje = '';
$tipo_mensaje = '';

// ─── Login / Logout ───────────────────────────────────────────────────────────
if (isset($_POST['password'])) {
    if ($_POST['password'] === UPLOAD_PASSWORD) {
        $_SESSION['admin_logged'] = true;
    } else {
        $mensaje = 'Contraseña incorrecta.';
        $tipo_mensaje = 'error';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-upload.php');
    exit;
}

// ─── Acciones protegidas ──────────────────────────────────────────────────────
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {

    $VIDEOS_JSON  = 'videos.json';
    $IMAGENES_JSON = 'imagenes.json';

    function leerAltTexts($path) {
        if (!file_exists($path)) return [];
        $d = json_decode(file_get_contents($path), true);
        return is_array($d) ? $d : [];
    }
    function guardarAltTexts($path, $data) {
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // Guardar alt texts editados
    if (isset($_POST['save_alts'])) {
        $alts = leerAltTexts($IMAGENES_JSON);
        foreach ($_POST['alt'] ?? [] as $filename => $texto) {
            $filename = basename($filename);
            if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $filename)) {
                $alts[$filename] = trim($texto);
            }
        }
        guardarAltTexts($IMAGENES_JSON, $alts);
        $mensaje = 'Descripciones guardadas correctamente.'; $tipo_mensaje = 'exito';
    }

    // Limpiar alt text de imagen eliminada
    if (isset($_GET['delete_img'])) {
        $f = basename($_GET['delete_img']);
        $alts = leerAltTexts($IMAGENES_JSON);
        unset($alts[$f]);
        guardarAltTexts($IMAGENES_JSON, $alts);
    }

    function leerVideos($path) {
        if (!file_exists($path)) return [];
        $v = json_decode(file_get_contents($path), true);
        return is_array($v) ? $v : [];
    }
    function guardarVideos($path, $videos) {
        file_put_contents($path, json_encode(array_values($videos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    function extraerYoutubeId($url) {
        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
        return $m[1] ?? null;
    }

    // Eliminar imagen
    if (isset($_GET['delete_img'])) {
        $f = basename($_GET['delete_img']);
        $ruta = 'imagenes/' . $f;
        if (file_exists($ruta) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f)) {
            unlink($ruta);
            $mensaje = 'Imagen eliminada.'; $tipo_mensaje = 'exito';
        }
    }

    // Subir imágenes
    if (!empty($_FILES['imagenes']['name'][0])) {
        $ok = $err = 0;
        foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmp) {
            if ($_FILES['imagenes']['error'][$i] !== UPLOAD_ERR_OK) { $err++; continue; }
            if (!in_array($_FILES['imagenes']['type'][$i], ['image/jpeg','image/png','image/gif','image/webp'])) { $err++; continue; }
            if ($_FILES['imagenes']['size'][$i] > 15*1024*1024) { $err++; continue; }
            $ext = strtolower(pathinfo($_FILES['imagenes']['name'][$i], PATHINFO_EXTENSION));
            if (move_uploaded_file($tmp, 'imagenes/' . uniqid('img_') . '.' . $ext)) $ok++; else $err++;
        }
        $mensaje = "$ok imagen(es) subida(s)" . ($err ? ", $err con error" : "."); $tipo_mensaje = $err && !$ok ? 'error' : 'exito';
    }

    // Añadir vídeo YouTube
    if (isset($_POST['youtube_url']) && !empty($_POST['youtube_url'])) {
        $id = extraerYoutubeId(trim($_POST['youtube_url']));
        if ($id) {
            $videos = leerVideos($VIDEOS_JSON);
            $videos[] = ['tipo' => 'youtube', 'id' => $id, 'titulo' => trim($_POST['youtube_titulo'] ?? '')];
            guardarVideos($VIDEOS_JSON, $videos);
            $mensaje = 'Vídeo de YouTube añadido.'; $tipo_mensaje = 'exito';
        } else {
            $mensaje = 'URL de YouTube no válida. Asegúrate de pegar la URL completa.'; $tipo_mensaje = 'error';
        }
    }

    // Subir vídeo local (MP4 / Instagram)
    if (!empty($_FILES['video_local']['name'])) {
        $permitidos = ['video/mp4','video/webm','video/quicktime','video/x-msvideo'];
        $vf = $_FILES['video_local'];
        if ($vf['error'] === UPLOAD_ERR_OK && in_array($vf['type'], $permitidos) && $vf['size'] <= 500*1024*1024) {
            if (!is_dir('videos')) mkdir('videos', 0755, true);
            $ext = strtolower(pathinfo($vf['name'], PATHINFO_EXTENSION));
            $nombre = uniqid('video_') . '.' . $ext;
            if (move_uploaded_file($vf['tmp_name'], 'videos/' . $nombre)) {
                $videos = leerVideos($VIDEOS_JSON);
                $videos[] = ['tipo' => 'local', 'archivo' => $nombre, 'titulo' => trim($_POST['video_titulo'] ?? '')];
                guardarVideos($VIDEOS_JSON, $videos);
                $mensaje = 'Vídeo local subido y añadido al reel.'; $tipo_mensaje = 'exito';
            } else {
                $mensaje = 'Error al mover el archivo. Comprueba los permisos del servidor.'; $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = 'Formato no permitido o fichero demasiado grande (máx. 500 MB). Formatos: MP4, WebM, MOV.'; $tipo_mensaje = 'error';
        }
    }

    // Eliminar vídeo
    if (isset($_GET['delete_video'])) {
        $idx = (int)$_GET['delete_video'];
        $videos = leerVideos($VIDEOS_JSON);
        if (isset($videos[$idx])) {
            if ($videos[$idx]['tipo'] === 'local' && file_exists('videos/' . $videos[$idx]['archivo'])) {
                unlink('videos/' . $videos[$idx]['archivo']);
            }
            array_splice($videos, $idx, 1);
            guardarVideos($VIDEOS_JSON, $videos);
            $mensaje = 'Vídeo eliminado.'; $tipo_mensaje = 'exito';
        }
        header('Location: admin-upload.php');
        exit;
    }

    // ─── Datos para la vista ──────────────────────────────────────────────────
    $imagenes_existentes = [];
    if (is_dir('imagenes/')) {
        foreach (scandir('imagenes/') as $f) {
            if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f)) $imagenes_existentes[] = $f;
        }
    }
    $alt_texts   = leerAltTexts($IMAGENES_JSON);
    $videos_lista = leerVideos($VIDEOS_JSON);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin — WolfFilms</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#111; color:#eee; min-height:100vh; }

        .header { background:#1a1a1a; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #2a2a2a; }
        .header h1 { font-size:1.1rem; color:#fff; font-weight:600; letter-spacing:.05em; }
        .header a { color:#666; text-decoration:none; font-size:.85rem; }
        .header a:hover { color:#fff; }

        .container { max-width:1100px; margin:0 auto; padding:2rem; }

        /* Login */
        .login-box { max-width:380px; margin:6rem auto; background:#1a1a1a; border:1px solid #2a2a2a; border-radius:12px; padding:2.5rem; text-align:center; }
        .login-box h2 { margin-bottom:1.5rem; }
        .login-box input[type=password] { width:100%; padding:.8rem 1rem; background:#222; border:1px solid #333; border-radius:8px; color:#fff; font-size:1rem; margin-bottom:1rem; }
        .btn-primary { background:#fff; color:#111; border:none; width:100%; padding:.8rem; border-radius:8px; font-size:1rem; font-weight:700; cursor:pointer; }
        .btn-primary:hover { background:#ddd; }

        /* Tabs */
        .tabs { display:flex; gap:.5rem; margin-bottom:2rem; border-bottom:1px solid #2a2a2a; }
        .tab { padding:.7rem 1.4rem; cursor:pointer; font-size:.85rem; letter-spacing:.08em; text-transform:uppercase; color:#666; border-bottom:2px solid transparent; transition:color .2s; }
        .tab.active, .tab:hover { color:#fff; border-bottom-color:#fff; }

        .tab-panel { display:none; }
        .tab-panel.active { display:block; }

        /* Mensajes */
        .msg { padding:.9rem 1.2rem; border-radius:8px; margin-bottom:1.5rem; font-size:.9rem; }
        .msg.exito { background:#0d2b0d; border:1px solid #2ecc71; color:#2ecc71; }
        .msg.error  { background:#2b0d0d; border:1px solid #e74c3c; color:#e74c3c; }
        .msg.aviso  { background:#2b1f0d; border:1px solid #f39c12; color:#f39c12; }

        /* Upload zones */
        .upload-zone { background:#1a1a1a; border:2px dashed #333; border-radius:10px; padding:2.5rem; text-align:center; cursor:pointer; transition:border-color .2s; margin-bottom:1.5rem; }
        .upload-zone:hover { border-color:#fff; }
        .upload-zone input[type=file] { display:none; }
        .upload-zone .icon { font-size:2.5rem; margin-bottom:.5rem; }
        .upload-zone p { color:#888; font-size:.85rem; margin-top:.4rem; }

        .preview-list { display:flex; flex-wrap:wrap; gap:.4rem; margin-bottom:1rem; }
        .preview-list span { background:#222; border:1px solid #333; border-radius:5px; padding:.25rem .6rem; font-size:.8rem; color:#bbb; }

        .btn-submit { background:#fff; color:#111; padding:.8rem 2rem; border:none; border-radius:8px; font-size:.95rem; font-weight:700; cursor:pointer; display:block; margin:0 auto; }
        .btn-submit:hover { background:#ddd; }

        /* Form inline */
        .form-row { display:flex; gap:.8rem; margin-bottom:1rem; flex-wrap:wrap; }
        .form-row input[type=text], .form-row input[type=url] { flex:1; min-width:200px; padding:.75rem 1rem; background:#1a1a1a; border:1px solid #333; border-radius:8px; color:#fff; font-size:.9rem; }
        .form-row input::placeholder { color:#555; }
        .btn-add { background:#fff; color:#111; padding:.75rem 1.5rem; border:none; border-radius:8px; font-weight:700; cursor:pointer; white-space:nowrap; }
        .btn-add:hover { background:#ddd; }

        /* Galería admin */
        .gallery-admin { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px,1fr)); gap:1rem; margin-top:1.5rem; }
        .g-item { background:#1a1a1a; border:1px solid #2a2a2a; border-radius:8px; overflow:hidden; }
        .g-item img { width:100%; height:140px; object-fit:cover; display:block; }
        .g-footer { padding:.5rem .7rem; display:flex; justify-content:space-between; align-items:center; }
        .g-footer span { font-size:.7rem; color:#666; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:120px; }

        /* Lista de vídeos */
        .video-list { display:grid; gap:1rem; margin-top:1.5rem; }
        .v-item { background:#1a1a1a; border:1px solid #2a2a2a; border-radius:8px; padding:1rem 1.2rem; display:flex; align-items:center; gap:1.2rem; }
        .v-thumb { width:100px; height:56px; background:#000; border-radius:4px; flex-shrink:0; overflow:hidden; }
        .v-thumb img { width:100%; height:100%; object-fit:cover; }
        .v-thumb video { width:100%; height:100%; object-fit:cover; }
        .v-info { flex:1; }
        .v-info strong { display:block; font-size:.9rem; margin-bottom:.2rem; }
        .v-info span { font-size:.75rem; color:#666; }
        .badge { display:inline-block; padding:.15rem .5rem; border-radius:4px; font-size:.7rem; font-weight:700; letter-spacing:.05em; margin-left:.5rem; }
        .badge-yt { background:#ff0000; color:#fff; }
        .badge-local { background:#333; color:#aaa; }

        .btn-del { background:#c0392b; color:#fff; border:none; border-radius:6px; padding:.35rem .8rem; font-size:.78rem; cursor:pointer; flex-shrink:0; }
        .btn-del:hover { background:#a93226; }

        .section-label { font-size:.75rem; letter-spacing:.1em; text-transform:uppercase; color:#666; margin-bottom:1rem; border-bottom:1px solid #2a2a2a; padding-bottom:.5rem; }
        .count-badge { background:#2a2a2a; color:#aaa; border-radius:20px; padding:.1rem .5rem; font-size:.75rem; margin-left:.4rem; }

        .empty { color:#555; text-align:center; padding:2.5rem; font-size:.9rem; }

        /* SEO info block */
        .seo-item { background:#1a1a1a; border-left:3px solid #444; border-radius:0 8px 8px 0; padding:1rem 1.2rem; margin-bottom:.8rem; }
        .seo-item h4 { font-size:.85rem; color:#ddd; margin-bottom:.3rem; }
        .seo-item p { font-size:.8rem; color:#888; line-height:1.5; }
        .seo-item .ok { color:#2ecc71; font-weight:700; }
        .seo-item .warn { color:#f39c12; font-weight:700; }
        .seo-item .req { color:#e74c3c; font-weight:700; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true): ?>
<div class="login-box">
    <h2>🐺 WolfFilms Admin</h2>
    <?php if ($mensaje): ?><div class="msg <?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
    <form method="POST">
        <input type="password" name="password" placeholder="Contraseña" autofocus>
        <button type="submit" class="btn-primary">Entrar</button>
    </form>
</div>

<?php else: ?>

<div class="header">
    <h1>🐺 WolfFilms — Panel de Administración</h1>
    <a href="?logout=1">Cerrar sesión</a>
</div>

<div class="container">

    <?php if ($mensaje): ?><div class="msg <?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="showTab('imagenes')">📷 Imágenes</div>
        <div class="tab" onclick="showTab('videos')">🎬 Vídeos (Reel)</div>
    </div>

    <!-- ════════════ TAB: IMÁGENES ════════════ -->
    <div id="tab-imagenes" class="tab-panel active">
        <p class="section-label">Subir nuevas imágenes <span class="count-badge"><?= count($imagenes_existentes) ?> actuales</span></p>

        <form method="POST" enctype="multipart/form-data">
            <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                <div class="icon">📁</div>
                <strong>Haz clic o arrastra las fotos aquí</strong>
                <p>JPG, PNG, GIF, WEBP — máximo 15 MB por imagen<br>
                   Vertical: 3:4 &nbsp;|&nbsp; Horizontal: 4:3</p>
                <input type="file" id="fileInput" name="imagenes[]" multiple accept="image/*"
                       onchange="mostrarArchivos(this)">
            </div>
            <div class="preview-list" id="filePreview"></div>
            <button type="submit" class="btn-submit" id="btnSubir" style="display:none">⬆ Subir imágenes</button>
        </form>

        <?php if (!empty($imagenes_existentes)): ?>
        <p class="section-label" style="margin-top:2rem">
            Galería actual — escribe una descripción para cada foto (ayuda al SEO)
        </p>
        <form method="POST">
        <div class="gallery-admin">
            <?php foreach ($imagenes_existentes as $img): ?>
            <div class="g-item">
                <img src="imagenes/<?= htmlspecialchars($img) ?>" alt="">
                <div class="g-footer" style="flex-direction:column; align-items:stretch; gap:.4rem; padding:.6rem .7rem;">
                    <input type="text"
                           name="alt[<?= htmlspecialchars($img) ?>]"
                           value="<?= htmlspecialchars($alt_texts[$img] ?? '') ?>"
                           placeholder="Ej: Boda en Aranjuez, retrato corporativo..."
                           style="width:100%;padding:.35rem .5rem;background:#222;border:1px solid #333;border-radius:5px;color:#ddd;font-size:.75rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:.68rem;color:#555;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:110px"><?= htmlspecialchars($img) ?></span>
                        <a href="?delete_img=<?= urlencode($img) ?>" class="btn-del"
                           onclick="return confirm('¿Eliminar esta imagen?')">✕</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:1.2rem">
            <button type="submit" name="save_alts" class="btn-submit">💾 Guardar descripciones</button>
        </div>
        </form>
        <?php else: ?>
            <div class="empty">No hay imágenes aún. Sube la primera arriba.</div>
        <?php endif; ?>
    </div>

    <!-- ════════════ TAB: VÍDEOS ════════════ -->
    <div id="tab-videos" class="tab-panel">

        <!-- Añadir YouTube -->
        <p class="section-label">Añadir vídeo de YouTube</p>
        <form method="POST">
            <div class="form-row">
                <input type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required>
                <input type="text" name="youtube_titulo" placeholder="Título (opcional)">
                <button type="submit" class="btn-add">+ Añadir</button>
            </div>
        </form>

        <!-- Subir vídeo local (Instagram, etc.) -->
        <p class="section-label" style="margin-top:2rem">Subir vídeo propio (MP4 desde Instagram, cámara, etc.)</p>
        <p style="color:#666; font-size:.82rem; margin-bottom:1rem;">
            Desde Instagram: en el post → ⋮ → <em>Descargar</em> → sube el MP4 aquí. Tamaño máximo: 500 MB.
        </p>
        <form method="POST" enctype="multipart/form-data">
            <div class="upload-zone" onclick="document.getElementById('videoInput').click()">
                <div class="icon">🎬</div>
                <strong>Haz clic para seleccionar el vídeo</strong>
                <p>MP4, WebM, MOV — máximo 500 MB</p>
                <input type="file" id="videoInput" name="video_local" accept="video/*"
                       onchange="mostrarVideo(this)">
            </div>
            <div class="preview-list" id="videoPreview"></div>
            <div class="form-row">
                <input type="text" name="video_titulo" placeholder="Título del vídeo (opcional)" style="margin-bottom:.5rem;">
            </div>
            <button type="submit" class="btn-submit" id="btnSubirVideo" style="display:none">⬆ Subir vídeo</button>
        </form>

        <!-- Lista de vídeos actuales -->
        <p class="section-label" style="margin-top:2.5rem">
            Vídeos en el Reel <span class="count-badge"><?= count($videos_lista) ?></span>
        </p>

        <?php if (empty($videos_lista)): ?>
            <div class="empty">No hay vídeos. Añade el primero arriba.</div>
        <?php else: ?>
        <div class="video-list">
            <?php foreach ($videos_lista as $i => $v): ?>
            <div class="v-item">
                <div class="v-thumb">
                    <?php if ($v['tipo'] === 'youtube'): ?>
                        <img src="https://img.youtube.com/vi/<?= htmlspecialchars($v['id']) ?>/mqdefault.jpg" alt="">
                    <?php else: ?>
                        <video src="videos/<?= htmlspecialchars($v['archivo']) ?>" muted></video>
                    <?php endif; ?>
                </div>
                <div class="v-info">
                    <strong>
                        <?= htmlspecialchars($v['titulo'] ?: ($v['tipo'] === 'youtube' ? $v['id'] : $v['archivo'])) ?>
                        <span class="badge <?= $v['tipo'] === 'youtube' ? 'badge-yt' : 'badge-local' ?>">
                            <?= $v['tipo'] === 'youtube' ? 'YouTube' : 'Local' ?>
                        </span>
                    </strong>
                    <span><?= $v['tipo'] === 'youtube'
                        ? 'youtube.com/watch?v=' . htmlspecialchars($v['id'])
                        : 'videos/' . htmlspecialchars($v['archivo']) ?></span>
                </div>
                <a href="?delete_video=<?= $i ?>" class="btn-del"
                   onclick="return confirm('¿Eliminar este vídeo?')">Eliminar</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- /tab-videos -->

</div><!-- /container -->

<script>
    function showTab(name) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    // Drag & drop imágenes
    const zone = document.getElementById('uploadZone');
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor='#fff'; });
    zone.addEventListener('dragleave', () => zone.style.borderColor='#333');
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.style.borderColor='#333';
        const dt = new DataTransfer();
        [...e.dataTransfer.files].forEach(f => dt.items.add(f));
        document.getElementById('fileInput').files = dt.files;
        mostrarArchivos(document.getElementById('fileInput'));
    });

    function mostrarArchivos(input) {
        const preview = document.getElementById('filePreview');
        const btn = document.getElementById('btnSubir');
        preview.innerHTML = '';
        [...input.files].forEach(f => {
            const s = document.createElement('span'); s.textContent = f.name;
            preview.appendChild(s);
        });
        btn.style.display = input.files.length ? 'block' : 'none';
    }

    function mostrarVideo(input) {
        const preview = document.getElementById('videoPreview');
        const btn = document.getElementById('btnSubirVideo');
        preview.innerHTML = '';
        if (input.files[0]) {
            const s = document.createElement('span');
            s.textContent = input.files[0].name + ' (' + (input.files[0].size / 1024 / 1024).toFixed(1) + ' MB)';
            preview.appendChild(s);
            btn.style.display = 'block';
        }
    }
</script>

<?php endif; ?>
</body>
</html>
