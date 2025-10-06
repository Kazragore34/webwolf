<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Portafolio de Fotografía</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <!-- Iconos de Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=6Lei1N8rAAAAAG-BPtNosX1lKpAk1f5zD1dn38VE" async defer></script>
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
        <section class="contact-page">
            <div class="contact-header">
                <h1>CONTACTA CON NOSOTROS</h1>
                <div class="contact-subtitle">Queremos escucharte</div>
            </div>
            
            <div class="contact-content">
                <div class="contact-form">
                    <?php
                    // Mostrar mensaje de confirmación si existe
                    if (isset($_GET['mensaje'])) {
                        if ($_GET['mensaje'] == 'enviado') {
                            echo '<div class="mensaje-exito">Tu mensaje ha sido enviado correctamente. ¡Gracias por contactarnos!</div>';
                        } elseif ($_GET['mensaje'] == 'error') {
                            $detalle = htmlspecialchars($_GET['detalle'] ?? 'Hubo un error al enviar tu mensaje. Por favor, inténtalo de nuevo.');
                            echo '<div class="mensaje-error">' . $detalle . '</div>';
                        }
                    }
                    ?>
                    <p>Si estás interesado en nuestros servicios de fotografía y video, rellena nuestro formulario y nos pondremos en contacto contigo lo antes posible.</p>
                    <p>¿Tienes un proyecto en mente? Contáctanos para colaborar.</p>
                    
                    <form action="procesar_contacto.php" method="POST" class="contact-form-fields">
                        <div class="form-group">
                            <input type="text" name="nombre" placeholder="Nombre" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Dirección de correo electrónico" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="telefono" placeholder="Teléfono" required>
                        </div>
                        <div class="form-group">
                            <textarea name="mensaje" placeholder="Mensaje" rows="5" required></textarea>
                        </div>
                        
                        <!-- Casillas de consentimiento -->
                        <div class="consentimiento">
                            <label>
                                <input type="checkbox" name="consentimiento_tratamiento" required>
                                He leído y acepto la <a href="politica-privacidad.php">Política de Privacidad</a> y autorizo el tratamiento de mis datos personales para la finalidad indicada. (Obligatorio)
                            </label>
                        </div>
                        
                        <div class="consentimiento">
                            <label>
                                <input type="checkbox" name="consentimiento_publicidad">
                                Consiento el envío de comunicaciones comerciales sobre productos y servicios de Ángel Fragoso Sánchez. (Opcional)
                            </label>
                        </div>
                        
                        <!-- Botón para verificar reCAPTCHA -->
                        <button type="button" id="verify-recaptcha" class="btn-verify">Verificar reCAPTCHA</button>
                        
                        <!-- Campo oculto para reCAPTCHA -->
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        
                        <!-- Botón de enviar (deshabilitado hasta que se verifique reCAPTCHA) -->
                        <button type="submit" class="submit-btn" id="submit-button" disabled>Enviar</button>
                    </form>
                </div>
                
                <div class="contact-info">
                    <h3>Ubicación:</h3>
                    <p>Madrid, España<br>
                    Disponibles para viajar por toda Europa</p>
                    
                    <h3>Teléfono:</h3>
                    <p>(+34) 628 55 82 25</p>
                    
                    <h3>Correo Electrónico:</h3>
                    <p>angelsanchez@wolffilms.es</p>
                    
                    <h3>Instagram:</h3>
                    <p><a href="https://www.instagram.com/wolfsanchez_/" target="_blank">@wolfsanchez_</a></p>
                </div>
            </div>
            
            <!-- Información de protección de datos (movida aquí, debajo del formulario) -->
            <div class="protection-info">
                <h3>INFORMACIÓN PROTECCIÓN DE DATOS:</h3>
                <p>Los datos de carácter personal que nos facilite a través de los formularios de esta web serán tratados conforme a lo dispuesto en el Reglamento (UE) 2016/679, de 27 de abril, General de Protección de Datos (RGPD), y la Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales y garantía de los derechos digitales (LOPDGDD).</p>
                <p>Responsable del tratamiento: Ángel Fragoso Sánchez</p>
                <p>Finalidad del tratamiento: Gestionar su solicitud de contacto y prestarle el servicio solicitado.</p>
                <p>Legitimación: La ejecución de medidas precontractuales a su solicitud.</p>
                <p>Destinatarios: No se cederán datos a terceros salvo obligación legal.</p>
                <p>Derechos: Puede ejercer los derechos de acceso, rectificación, supresión, portabilidad y oposición/limitación del tratamiento de sus datos dirigiéndose a la dirección del responsable.</p>
                <p>Para más información sobre el tratamiento de sus datos y los derechos que le amparan, puede acceder a nuestra <a href="politica-privacidad.php">Política de Privacidad</a>.</p>
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

    <!-- Popup de confirmación -->
    <div id="popup" class="popup">
        <div class="popup-content">
            <span class="close">&times;</span>
            <div id="popup-message"></div>
        </div>
    </div>

    <script src="js/scripts.js"></script>
    <script>
        // Inicializa los iconos de Lucide
        lucide.createIcons();
        
        // Mostrar popup si hay mensaje de éxito o error
        <?php
        if (isset($_GET['mensaje'])) {
            $mensaje = $_GET['mensaje'];
            $detalle = isset($_GET['detalle']) ? addslashes($_GET['detalle']) : '';
            
            if ($mensaje == 'enviado') {
                echo "showPopup('Mensaje enviado exitosamente', 'success');";
            } elseif ($mensaje == 'error') {
                echo "showPopup('" . ($detalle ? $detalle : 'Hubo un error al enviar tu mensaje') . "', 'error');";
            }
        }
        ?>
        
        // Función para mostrar el popup
        function showPopup(message, type) {
            const popup = document.getElementById('popup');
            const popupMessage = document.getElementById('popup-message');
            
            popupMessage.innerHTML = '<div class="popup-' + type + '">' + message + '</div>';
            popup.style.display = 'block';
            
            // Cerrar popup después de 5 segundos
            setTimeout(() => {
                popup.style.display = 'none';
            }, 5000);
        }
        
        // Cerrar popup al hacer clic en la X
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('popup').style.display = 'none';
        });
        
        // Cerrar popup al hacer clic fuera del contenido
        window.addEventListener('click', function(event) {
            const popup = document.getElementById('popup');
            if (event.target === popup) {
                popup.style.display = 'none';
            }
        });
        
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
        
        // Configurar reCAPTCHA
        grecaptcha.ready(function() {
            // Botón para verificar reCAPTCHA
            document.getElementById('verify-recaptcha').addEventListener('click', function() {
                grecaptcha.execute('6Lei1N8rAAAAAG-BPtNosX1lKpAk1f5zD1dn38VE', {action: 'contact_form'})
                    .then(function(token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        // Habilitar el botón de enviar
                        document.getElementById('submit-button').disabled = false;
                        // Cambiar el texto del botón
                        document.getElementById('verify-recaptcha').textContent = 'reCAPTCHA Verificado';
                        document.getElementById('verify-recaptcha').classList.add('btn-verified');
                    })
                    .catch(function(error) {
                        console.error('Error al generar el token de reCAPTCHA:', error);
                        alert('Hubo un error al verificar reCAPTCHA. Por favor, inténtalo de nuevo.');
                    });
            });
        });
    </script>
    
    <?php include 'chatbot.php'; ?>
</body>
</html>