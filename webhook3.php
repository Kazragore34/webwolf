<?php

// 1. Obtener el cuerpo de la solicitud
$raw_input = file_get_contents("php://input");
file_put_contents("log.txt", "--- Nuevo Mensaje ---\n" . $raw_input . "\n\n", FILE_APPEND);

// 2. Decodificar el JSON
$data = json_decode($raw_input, true);

// 3. Extraer datos clave del mensaje
$from = null;
$from_me = true; // Asumimos que es nuestro por defecto para seguridad
$key_data = $data['data']['messages']['key'] ?? null;

if ($key_data) {
    $from = $key_data['participant'] ?? $key_data['remoteJid'] ?? null;
    // Comprobamos si el mensaje es nuestro o de otra persona
    $from_me = $key_data['fromMe'] ?? true;
}

// 4. Lista de números permitidos
$permitidos = [
    "34722539447@s.whatsapp.net",
    "519674952331@s.whatsapp.net"
];

// 5. Condición FINAL: El remitente debe estar permitido Y el mensaje NO debe ser nuestro
if ($from && in_array($from, $permitidos) && $from_me === false) {
    file_put_contents("log.txt", "Decisión: MENSAJE ENTRANTE PERMITIDO ($from). Disparando webhook a n8n...\n\n", FILE_APPEND);

    $n8nWebhook = 'https://n8n.wolffilms.es/webhook/f474262a-2787-4061-a7ed-89d941d066ea';

    // --- cURL "Disparar y Olvidar" para no causar timeouts ---
    $ch = curl_init($n8nWebhook);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_input);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    // Usamos un timeout de 2 segundos. Es suficiente para enviar sin fallar.
    curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    curl_exec($ch);
    curl_close($ch);

    // Respondemos a Wasender para que no reintente
    http_response_code(200);
    echo "Webhook reenviado a n8n.";

} else {
    // Si el número no está permitido o el mensaje es nuestro, lo ignoramos
    $reason = $from_me ? "MENSAJE PROPIO" : "NÚMERO NO PERMITIDO";
    file_put_contents("log.txt", "Decisión: IGNORADO. Razón: $reason ($from).\n\n", FILE_APPEND);
    http_response_code(200);
    echo "Mensaje ignorado.";
    exit();
}
?>