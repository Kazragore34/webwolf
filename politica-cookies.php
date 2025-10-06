<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Cookies - WolfFilms</title>
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
        <section class="legal-page">
            <h1>POLÍTICA DE COOKIES</h1>
            
            <h2>¿QUÉ SON LAS COOKIES?</h2>
            <p>Las cookies y otras tecnologías similares tales como local shared objects, flash cookies o píxeles, son herramientas empleadas por los servidores Web para almacenar y recuperar información acerca de sus visitantes, así como para ofrecer un correcto funcionamiento del sitio.</p>
            
            <h2>TIPOS DE COOKIES</h2>
            <p>Según el "Servicio Ofrecido" de las cookies:</p>
            <ul>
                <li><strong>Cookies Técnicas:</strong> Son aquellas que, bien tratadas por nosotros o por terceros, resultan imprescindibles para que se pueda navegar por el sitio Web correctamente.</li>
                <li><strong>Cookies Analíticas:</strong> Son aquellas que, bien tratadas por nosotros o por terceros, nos permiten cuantificar el número de usuarios y así realizar la medición y análisis estadístico de la utilización y comportamiento que hacen los usuarios del servicio ofertado.</li>
                <li><strong>Cookies de Personalización:</strong> Son aquellas que, bien tratadas por nosotros o por terceros, permiten al usuario acceder al servicio con algunas características predefinadas en función de una serie de criterios en el terminal del usuario.</li>
                <li><strong>Cookies de Funcionalidades:</strong> Tienen como objetivo adaptar el sitio web a las preferencias de visualización.</li>
                <li><strong>Cookies Publicitarias:</strong> Son aquellas que, bien tratadas por nosotros o por terceros, nos permiten mostrar productos y/o servicios al visitar alguna página web externa previo consentimiento expreso del usuario.</li>
            </ul>
            
            <h2>COOKIES DE TERCEROS</h2>
            <p>Utilizamos estas cookies (llamadas cookies de terceros) para enseñar en otros sitios web publicidad nuestra personalizada, nunca de terceras empresas. Se puede optar a no aceptar estas cookies en la configuración de su navegador.</p>
            
            <h2>ADMINISTRACIÓN DE LAS COOKIES</h2>
            <p>El registro de una cookie en su equipo está esencialmente sujeto a su voluntad, la cual puede expresar o modificar en cualquier momento sin cargo alguno a través de las opciones que le ofrece su navegador.</p>
            
            <h2>SISTEMAS DE DESACTIVACIÓN DE COOKIES</h2>
            <p>Para la desactivación de cookies y/o trazadores, puede consultar los siguientes sistemas:</p>
            <ul>
                <li>Chrome: chrome://settings/content/cookies</li>
                <li>Firefox: about:preferences#privacy</li>
                <li>Internet Explorer: Herramientas > Opciones de Internet > Privacidad > Configuración</li>
                <li>Safari: Preferencias > Privacidad</li>
                <li>Opera: Configuración > Avanzada > Cookies</li>
            </ul>
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