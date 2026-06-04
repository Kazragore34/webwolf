<?php
// Contraseña de acceso - CAMBIA ESTO antes de subir al servidor
define('UPLOAD_PASSWORD', 'wolffilms2024');

session_start();

$mensaje = '';
$tipo_mensaje = '';

// Login
if (isset($_POST['password'])) {
    if ($_POST['password'] === UPLOAD_PASSWORD) {
        $_SESSION['admin_logged'] = true;
    } else {
        $mensaje = 'Contraseña incorrecta.';
        $tipo_mensaje = 'error';
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-upload.php');
    exit;
}

// Subida de imágenes
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {

    // Eliminar imagen
    if (isset($_GET['delete'])) {
        $archivo = basename($_GET['delete']);
        $ruta = 'imagenes/' . $archivo;
        if (file_exists($ruta) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $archivo)) {
            unlink($ruta);
            $mensaje = 'Imagen eliminada correctamente.';
            $tipo_mensaje = 'exito';
        }
    }

    // Subir imágenes
    if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
        $subidas_ok = 0;
        $subidas_error = 0;
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 10 * 1024 * 1024; // 10 MB

        foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmp) {
            $nombre_original = $_FILES['imagenes']['name'][$i];
            $tipo = $_FILES['imagenes']['type'][$i];
            $size = $_FILES['imagenes']['size'][$i];
            $error = $_FILES['imagenes']['error'][$i];

            if ($error !== UPLOAD_ERR_OK) { $subidas_error++; continue; }
            if (!in_array($tipo, $tipos_permitidos)) { $subidas_error++; continue; }
            if ($size > $max_size) { $subidas_error++; continue; }

            // Nombre seguro único
            $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
            $nombre_seguro = uniqid('img_') . '.' . $extension;
            $destino = 'imagenes/' . $nombre_seguro;

            if (move_uploaded_file($tmp, $destino)) {
                $subidas_ok++;
            } else {
                $subidas_error++;
            }
        }

        if ($subidas_ok > 0 && $subidas_error === 0) {
            $mensaje = "$subidas_ok imagen(es) subida(s) correctamente.";
            $tipo_mensaje = 'exito';
        } elseif ($subidas_ok > 0) {
            $mensaje = "$subidas_ok subida(s) correctamente, $subidas_error con error.";
            $tipo_mensaje = 'aviso';
        } else {
            $mensaje = 'No se pudo subir ninguna imagen. Comprueba el formato (JPG, PNG, GIF, WEBP) y el tamaño (máx. 10 MB).';
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener lista de imágenes actuales
$imagenes_existentes = [];
if (is_dir('imagenes/')) {
    foreach (scandir('imagenes/') as $f) {
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f)) {
            $imagenes_existentes[] = $f;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Imágenes - WolfFilms</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #111; color: #eee; min-height: 100vh; }

        .header {
            background: #1a1a1a;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
        }
        .header h1 { font-size: 1.3rem; color: #fff; }
        .header a { color: #aaa; text-decoration: none; font-size: 0.9rem; }
        .header a:hover { color: #fff; }

        .container { max-width: 1100px; margin: 0 auto; padding: 2rem; }

        .login-box {
            max-width: 380px;
            margin: 6rem auto;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 2.5rem;
            text-align: center;
        }
        .login-box h2 { margin-bottom: 1.5rem; font-size: 1.5rem; }
        .login-box input[type=password] {
            width: 100%;
            padding: 0.8rem 1rem;
            background: #222;
            border: 1px solid #444;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.85; }
        .btn-primary { background: #fff; color: #111; width: 100%; }
        .btn-danger { background: #c0392b; color: #fff; font-size: 0.8rem; padding: 0.4rem 0.8rem; }

        .upload-zone {
            background: #1a1a1a;
            border: 2px dashed #444;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
            margin-bottom: 2rem;
        }
        .upload-zone:hover, .upload-zone.drag-over { border-color: #fff; }
        .upload-zone p { color: #aaa; margin-top: 0.5rem; font-size: 0.9rem; }
        .upload-zone input[type=file] { display: none; }
        .upload-zone .icon { font-size: 3rem; margin-bottom: 0.5rem; }

        .file-preview { display: flex; flex-wrap: wrap; gap: 0.5rem; margin: 1rem 0; }
        .file-preview span {
            background: #222;
            border: 1px solid #444;
            border-radius: 6px;
            padding: 0.3rem 0.7rem;
            font-size: 0.85rem;
            color: #ccc;
        }

        .btn-upload {
            background: #fff;
            color: #111;
            padding: 0.9rem 2.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: none;
        }
        .btn-upload:hover { background: #ddd; }

        .mensaje {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .mensaje.exito { background: #1a3a1a; border: 1px solid #2ecc71; color: #2ecc71; }
        .mensaje.error  { background: #3a1a1a; border: 1px solid #e74c3c; color: #e74c3c; }
        .mensaje.aviso  { background: #3a2a1a; border: 1px solid #f39c12; color: #f39c12; }

        .gallery-admin {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .gallery-admin-item {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .gallery-admin-item img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            display: block;
        }
        .gallery-admin-item .item-footer {
            padding: 0.5rem 0.7rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .gallery-admin-item .item-footer span {
            font-size: 0.75rem;
            color: #888;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 130px;
        }

        .section-title {
            font-size: 1.1rem;
            color: #aaa;
            margin-bottom: 1rem;
            border-bottom: 1px solid #333;
            padding-bottom: 0.5rem;
        }
        .count-badge {
            background: #333;
            color: #fff;
            border-radius: 20px;
            padding: 0.15rem 0.6rem;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true): ?>

<div class="login-box">
    <h2>🔒 Acceso Restringido</h2>
    <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="password" name="password" placeholder="Contraseña" autofocus>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
</div>

<?php else: ?>

<div class="header">
    <h1>🐺 WolfFilms — Panel de Imágenes</h1>
    <a href="?logout=1">Cerrar sesión</a>
</div>

<div class="container">

    <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <!-- Zona de subida -->
    <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
        <div class="icon">📁</div>
        <strong>Haz clic aquí o arrastra las imágenes</strong>
        <p>JPG, PNG, GIF, WEBP — máximo 10 MB por imagen</p>
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="file" id="fileInput" name="imagenes[]" multiple accept="image/*"
                   onchange="mostrarArchivos(this)">
        </form>
    </div>

    <div class="file-preview" id="filePreview"></div>
    <div style="text-align:center">
        <button class="btn-upload" id="btnSubir" onclick="document.getElementById('uploadForm').submit()">
            ⬆ Subir imágenes
        </button>
    </div>

    <!-- Galería actual -->
    <h2 class="section-title" style="margin-top:2.5rem">
        Imágenes en la galería
        <span class="count-badge"><?= count($imagenes_existentes) ?></span>
    </h2>

    <?php if (empty($imagenes_existentes)): ?>
        <p style="color:#666; text-align:center; padding:2rem">
            No hay imágenes aún. Sube la primera usando el panel de arriba.
        </p>
    <?php else: ?>
        <div class="gallery-admin">
            <?php foreach ($imagenes_existentes as $img): ?>
            <div class="gallery-admin-item">
                <img src="imagenes/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($img) ?>">
                <div class="item-footer">
                    <span><?= htmlspecialchars($img) ?></span>
                    <a href="?delete=<?= urlencode($img) ?>"
                       class="btn btn-danger"
                       onclick="return confirm('¿Eliminar esta imagen?')">✕</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    // Drag & drop
    const zone = document.getElementById('uploadZone');
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        const dt = new DataTransfer();
        [...e.dataTransfer.files].forEach(f => dt.items.add(f));
        document.getElementById('fileInput').files = dt.files;
        mostrarArchivos(document.getElementById('fileInput'));
    });

    function mostrarArchivos(input) {
        const preview = document.getElementById('filePreview');
        const btn = document.getElementById('btnSubir');
        preview.innerHTML = '';
        if (input.files.length > 0) {
            [...input.files].forEach(f => {
                const span = document.createElement('span');
                span.textContent = f.name;
                preview.appendChild(span);
            });
            btn.style.display = 'inline-block';
        } else {
            btn.style.display = 'none';
        }
    }
</script>

<?php endif; ?>
</body>
</html>
