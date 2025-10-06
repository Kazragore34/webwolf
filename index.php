<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portafolio de Fotografía</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <!-- Iconos de Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <a href="index.php" class="logo-link">
                    <div class="logo-container">
                        <img src="logowolf.png" alt="Logo wolf" class="logo-img">
                    </div>
                </a>
                
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="reel.php" class="nav-link">Reel</a>
                    </li>
                    <li class="nav-item">
                        <a href="contacto.php" class="nav-link">Contacto</a>
                    </li>
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
        <section id="inicio" class="hero">
            <div class="hero-content">
                <h2>Fotografía Profesional</h2>
                <p>Capturando momentos únicos con una visión artística</p>
            </div>
        </section>

        <section id="galeria" class="gallery">
            <h2>Galería de Trabajos</h2>
            <div class="gallery-grid" id="masonry-container">
                <?php
                $directorio = 'imagenes/';
                $imagenes = array();
                
                if (is_dir($directorio)) {
                    if ($gestor = opendir($directorio)) {
                        while (($archivo = readdir($gestor)) !== false) {
                            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $archivo)) {
                                $imagenes[] = $directorio . $archivo;
                            }
                        }
                        closedir($gestor);
                        
                        foreach ($imagenes as $imagen) {
                            $size = getimagesize($imagen);
                            $orientation = ($size[0] > $size[1]) ? 'landscape' : 'portrait';
                            
                            echo '<div class="gallery-item ' . $orientation . '">';
                            echo '<img src="' . htmlspecialchars($imagen) . '" alt="Imagen de portafolio">';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<p>La carpeta de imágenes no existe.</p>';
                }
                ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="contact-info">
            <h3>Contacto</h3>
            <p>¿Tienes un proyecto en mente? Contáctame para colaborar.</p>
            <div class="contact-details">
                <div class="contact-item">
                    <strong>Email</strong>
                    <span>angelsanchez@wolffilms.es</span>
                </div>
                <div class="contact-item">
                    <strong>Teléfono</strong>
                    <span>+34 628 55 82 25</span>
                </div>
                <div class="contact-item">
                    <strong>Ubicación</strong>
                    <span>España, Madrid</span>
                </div>
            </div>
        </div>
        
        <div class="legal-links">
            <a href="aviso-legal.php">Aviso Legal</a>
            <a href="politica-privacidad.php">Política de Privacidad</a>
            <a href="politica-cookies.php">Política de Cookies</a>
        </div>
    </footer>

    <!-- Ventana emergente de cookies -->
    <div id="cookie-consent" class="cookie-consent">
        <div class="cookie-content">
            <h3>Uso de Cookies</h3>
            <p>Este sitio web utiliza cookies propias y de terceros para mejorar nuestros servicios y mostrarle publicidad relacionada con sus preferencias mediante el análisis de sus hábitos de navegación.</p>
            <div class="cookie-buttons">
                <button id="accept-cookies" class="btn-accept">Aceptar</button>
                <button id="reject-cookies" class="btn-reject">Rechazar</button>
                <button id="configure-cookies" class="btn-configure">Configurar</button>
            </div>
        </div>
    </div>
    
    <!-- Ventana de configuración de cookies -->
    <div id="cookie-config" class="cookie-config">
        <div class="cookie-config-content">
            <span id="close-config" class="close-config">&times;</span>
            <h3>Configuración de Cookies</h3>
            <div class="cookie-options">
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="necessary-cookies" checked disabled>
                        Cookies necesarias - Cookies técnicas (SIEMPRE ACTIVAS)
                    </label>
                    <p>Estas cookies son necesarias para que el sitio web funcione y no se pueden desactivar en nuestros sistemas. Se configuran en respuesta a tus acciones realizadas al solicitar servicios.</p>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="performance-cookies">
                        Cookies de Rendimiento
                    </label>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="functionality-cookies">
                        Cookies de Funcionalidad
                    </label>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="targeting-cookies">
                        Cookies Dirigidas
                    </label>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="social-cookies">
                        Cookies de Redes Sociales
                    </label>
                </div>
            </div>
            <button id="save-cookies" class="btn-save">Guardar configuración</button>
        </div>
    </div>

    <script src="js/scripts.js"></script>
    <script>
        // Inicializa los iconos de Lucide
        lucide.createIcons();
        
        // Gestión de cookies
        document.getElementById('accept-cookies').addEventListener('click', function() {
            document.getElementById('cookie-consent').style.display = 'none';
            localStorage.setItem('cookiesAccepted', 'true');
            localStorage.setItem('cookiesConfigured', 'true'); // También marcamos como configurado
        });
        
        document.getElementById('reject-cookies').addEventListener('click', function() {
            document.getElementById('cookie-consent').style.display = 'none';
            localStorage.setItem('cookiesRejected', 'true');
            localStorage.setItem('cookiesConfigured', 'true'); // Marcamos como configurado
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
            // Guardar la configuración de cookies
            localStorage.setItem('cookiesConfigured', 'true');
            
            // Opcional: guardar qué cookies están activadas
            const performanceChecked = document.getElementById('performance-cookies').checked;
            const functionalityChecked = document.getElementById('functionality-cookies').checked;
            const targetingChecked = document.getElementById('targeting-cookies').checked;
            const socialChecked = document.getElementById('social-cookies').checked;
            
            localStorage.setItem('cookiesPerformance', performanceChecked);
            localStorage.setItem('cookiesFunctionality', functionalityChecked);
            localStorage.setItem('cookiesTargeting', targetingChecked);
            localStorage.setItem('cookiesSocial', socialChecked);
        });
        
        // Verificar si ya se aceptaron, rechazaron o configuraron las cookies
        window.addEventListener('load', function() {
            if (localStorage.getItem('cookiesConfigured') === 'true') {
                document.getElementById('cookie-consent').style.display = 'none';
            }
        });
    </script>
    
    <?php include 'chatbot.php'; ?>
</body>
</html>