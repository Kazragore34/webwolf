<?php
$videos = [];
if (file_exists('videos.json')) {
    $datos = json_decode(file_get_contents('videos.json'), true);
    if (is_array($datos)) $videos = $datos;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reel — WolfFilms | Fotografía y Vídeo Profesional Madrid</title>
    <meta name="description" content="Reel profesional de WolfFilms. Trabajos de fotografía y vídeo de Ángel Sánchez en Madrid y disponible para toda Europa.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <style>
        .reel-page { padding: 6rem 5% 4rem; max-width: 1100px; margin: 0 auto; }

        .reel-header { text-align: center; margin-bottom: 4rem; }
        .reel-header h1 { font-family: 'Urban Shadow', sans-serif; font-size: 2.5rem; color: #222; margin-bottom: 0.5rem; }
        .reel-header p { color: #888; font-size: 1rem; letter-spacing: 0.05em; }

        /* Grid de vídeos */
        .videos-grid { display: grid; gap: 3rem; }

        .video-card { background: #111; border-radius: 4px; overflow: hidden; }
        .video-card-title {
            padding: 1rem 1.2rem 0.8rem;
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #777;
        }

        /* YouTube: ratio 16/9, sin barras */
        .yt-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            background: #000;
            overflow: hidden;
        }
        .yt-wrapper iframe,
        .yt-wrapper .yt-player-div {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            border: none;
        }

        /* Vídeo local */
        .local-video {
            width: 100%;
            display: block;
            background: #000;
            max-height: 620px;
        }

        /* Overlay para ocultar el logo de YouTube al cargar */
        .yt-overlay {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 60px;
            background: linear-gradient(to bottom, #000 0%, transparent 100%);
            pointer-events: none;
            z-index: 2;
        }

        @media (max-width: 768px) {
            .reel-header h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <a href="index.php" class="logo-link">
                    <div class="logo-container">
                        <img src="logowolf.png" alt="WolfFilms logo" class="logo-img">
                    </div>
                </a>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="reel.php" class="nav-link">Reel</a></li>
                    <li class="nav-item"><a href="contacto.php" class="nav-link">Contacto</a></li>
                </ul>
                <div class="social-links">
                    <a href="https://www.instagram.com/wolfsanchez_/" target="_blank" class="social-link">
                        <i data-lucide="instagram" class="social-icon"></i>
                    </a>
                </div>
                <div class="menu-toggle">
                    <i data-lucide="list" class="menu-icon"></i>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="reel-page">
            <div class="reel-header">
                <h1>Reel</h1>
                <p>Trabajo en movimiento</p>
            </div>

            <?php if (empty($videos)): ?>
                <p style="text-align:center;color:#888">Próximamente...</p>
            <?php else: ?>
            <div class="videos-grid">
                <?php foreach ($videos as $i => $v): ?>
                <?php if ($v['tipo'] === 'youtube'): ?>
                    <div class="video-card">
                        <?php if (!empty($v['titulo'])): ?>
                            <div class="video-card-title"><?= htmlspecialchars($v['titulo']) ?></div>
                        <?php endif; ?>
                        <div class="yt-wrapper">
                            <div class="yt-overlay"></div>
                            <div class="yt-player-div" id="yt-<?= $i ?>" data-id="<?= htmlspecialchars($v['id']) ?>"></div>
                        </div>
                    </div>
                <?php elseif ($v['tipo'] === 'local'): ?>
                    <div class="video-card">
                        <?php if (!empty($v['titulo'])): ?>
                            <div class="video-card-title"><?= htmlspecialchars($v['titulo']) ?></div>
                        <?php endif; ?>
                        <video class="local-video" controls playsinline preload="metadata"
                               poster="<?= htmlspecialchars($v['poster'] ?? '') ?>">
                            <source src="videos/<?= htmlspecialchars($v['archivo']) ?>" type="video/mp4">
                        </video>
                    </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="contact-info">
            <h3>Contacto</h3>
            <p>¿Tienes un proyecto en mente? Contáctame para colaborar.</p>
            <div class="contact-details">
                <div class="contact-item"><strong>Email</strong><span>angelsanchez@wolffilms.es</span></div>
                <div class="contact-item"><strong>Teléfono</strong><span>+34 628 55 82 25</span></div>
                <div class="contact-item"><strong>Ubicación</strong><span>España, Madrid</span></div>
            </div>
        </div>
        <div class="legal-links">
            <a href="aviso-legal.php">Aviso Legal</a>
            <a href="politica-privacidad.php">Política de Privacidad</a>
            <a href="politica-cookies.php">Política de Cookies</a>
        </div>
    </footer>

    <!-- Cookies (igual que el resto de páginas) -->
    <div id="cookie-consent" class="cookie-consent">
        <div class="cookie-content">
            <h3>Uso de Cookies</h3>
            <p>Este sitio web utiliza cookies propias y de terceros para mejorar nuestros servicios.</p>
            <div class="cookie-buttons">
                <button id="accept-cookies" class="btn-accept">Aceptar</button>
                <button id="reject-cookies" class="btn-reject">Rechazar</button>
                <button id="configure-cookies" class="btn-configure">Configurar</button>
            </div>
        </div>
    </div>
    <div id="cookie-config" class="cookie-config">
        <div class="cookie-config-content">
            <span id="close-config" class="close-config">&times;</span>
            <h3>Configuración de Cookies</h3>
            <div class="cookie-options">
                <div class="cookie-option">
                    <label><input type="checkbox" id="necessary-cookies" checked disabled> Cookies necesarias (SIEMPRE ACTIVAS)</label>
                </div>
                <div class="cookie-option"><label><input type="checkbox" id="performance-cookies"> Cookies de Rendimiento</label></div>
                <div class="cookie-option"><label><input type="checkbox" id="functionality-cookies"> Cookies de Funcionalidad</label></div>
                <div class="cookie-option"><label><input type="checkbox" id="targeting-cookies"> Cookies Dirigidas</label></div>
                <div class="cookie-option"><label><input type="checkbox" id="social-cookies"> Cookies de Redes Sociales</label></div>
            </div>
            <button id="save-cookies" class="btn-save">Guardar configuración</button>
        </div>
    </div>

    <!-- YouTube IFrame API: carga limpia sin controles visibles -->
    <script>
        var ytPlayers = {};
        var ytQueue = [];

        window.onYouTubeIframeAPIReady = function() {
            ytQueue.forEach(function(item) {
                ytPlayers[item.id] = new YT.Player(item.el, {
                    videoId: item.videoId,
                    playerVars: {
                        controls:        0,
                        modestbranding:  1,
                        rel:             0,
                        iv_load_policy:  3,
                        playsinline:     1,
                        enablejsapi:     1,
                        origin:          window.location.origin,
                        showinfo:        0,
                        disablekb:       0,
                        fs:              1
                    },
                    events: {
                        onReady: function(e) {
                            e.target.setPlaybackQuality('hd1080');
                        },
                        onStateChange: function(e) {
                            // Si el vídeo empieza a reproducirse, forzar máxima calidad
                            if (e.data === YT.PlayerState.PLAYING) {
                                e.target.setPlaybackQuality('hd1080');
                            }
                        }
                    }
                });
            });
        };

        document.querySelectorAll('.yt-player-div').forEach(function(el) {
            ytQueue.push({ el: el, id: el.id, videoId: el.dataset.id });
        });
    </script>
    <script src="https://www.youtube.com/iframe_api" async></script>

    <script src="js/scripts.js"></script>
    <script>
        lucide.createIcons();

        // Cookies
        document.getElementById('accept-cookies').addEventListener('click', function() {
            document.getElementById('cookie-consent').style.display = 'none';
            localStorage.setItem('cookiesConfigured', 'true');
        });
        document.getElementById('reject-cookies').addEventListener('click', function() {
            document.getElementById('cookie-consent').style.display = 'none';
            localStorage.setItem('cookiesConfigured', 'true');
        });
        document.getElementById('configure-cookies').addEventListener('click', function() {
            document.getElementById('cookie-consent').style.display = 'none';
            document.getElementById('cookie-config').style.display = 'block';
        });
        document.getElementById('close-config').addEventListener('click', function() {
            document.getElementById('cookie-config').style.display = 'none';
        });
        document.getElementById('save-cookies').addEventListener('click', function() {
            document.getElementById('cookie-config').style.display = 'none';
            localStorage.setItem('cookiesConfigured', 'true');
        });
        window.addEventListener('load', function() {
            if (localStorage.getItem('cookiesConfigured') === 'true') {
                document.getElementById('cookie-consent').style.display = 'none';
            }
        });
    </script>
    <?php include 'chatbot.php'; ?>
</body>
</html>
