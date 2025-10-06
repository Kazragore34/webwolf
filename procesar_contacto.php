<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y limpiar los datos recibidos
    $nombre = trim($_POST['nombre'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $telefono = trim($_POST['telefono'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $consentimiento_tratamiento = isset($_POST['consentimiento_tratamiento']) ? true : false;
    $consentimiento_publicidad = isset($_POST['consentimiento_publicidad']) ? true : false;
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    // Validar que los campos obligatorios no estén vacíos y que se haya dado consentimiento
    if (empty($nombre) || !$email || empty($mensaje) || !$consentimiento_tratamiento) {
        header("Location: contacto.php?mensaje=error&detalle=Por favor completa todos los campos obligatorios y acepta la política de privacidad");
        exit();
    }
    
    // Validar reCAPTCHA
    $recaptcha_secret = '6Lei1N8rAAAAANk1_ZtNvcdu3LbCUTo6KdEinhcB'; // Clave secreta de reCAPTCHA
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $recaptcha_options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
            'content' => http_build_query($recaptcha_data)
        ]
    ];
    
    $context = stream_context_create($recaptcha_options);
    $recaptcha_result = file_get_contents($recaptcha_url, false, $context);
    $recaptcha_json = json_decode($recaptcha_result, true);
    
    if (!$recaptcha_json['success']) {
        header("Location: contacto.php?mensaje=error&detalle=Verificación reCAPTCHA fallida. Por favor, inténtalo de nuevo.");
        exit();
    }
    
    // Configurar los datos del correo
    $destinatario = "angelsanchez@wolffilms.es";
    $asunto = "Nuevo mensaje de contacto de $nombre";
    $cuerpo = "Nombre: $nombre\nEmail: $email\nTeléfono: $telefono\nMensaje: $mensaje\nConsentimiento publicidad: " . ($consentimiento_publicidad ? "Sí" : "No");
    $headers = "From: $email";
    
    // Enviar el correo
    if (mail($destinatario, $asunto, $cuerpo, $headers)) {
        header("Location: contacto.php?mensaje=enviado");
        exit();
    } else {
        header("Location: contacto.php?mensaje=error&detalle=Hubo un problema al enviar tu mensaje");
        exit();
    }
} else {
    header("Location: contacto.php");
    exit();
}
?>