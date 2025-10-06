<?php

// 1. Obtener el cuerpo de la solicitud
$raw_input = file_get_contents("php://input");

// 2. Guardar la solicitud en el log
file_put_contents("log.txt", "--- Nuevo Mensaje ---\n" . $raw_input . "\n\n", FILE_APPEND);

// 3. Decodificar el JSON
$data = json_decode($raw_input, true);

// 4. Extraer el número del remitente (¡LÓGICA MEJORADA!)
$from = null; // Inicia la variable como nula

// Accede al objeto 'key' de forma segura
$key_data = $data['data']['messages']['key'] ?? null;

if ($key_data) {
    // Si 'participant' existe (es un grupo), úsalo. Si no (es un DM), usa 'remoteJid'.
    $from = $key_data['participant'] ?? $key_data['remoteJid'] ?? null;
}

// 5. Guardar en el log qué número se extrajo
file_put_contents("log.txt", "Número extraído ('from'): " . ($from ?? 'NULO O NO ENCONTRADO') . "\n", FILE_APPEND);

// 6. Lista de números a bloquear
$bloqueados = [
    "519865483211@s.whatsapp.net" // Asegúrate que el número a bloquear esté aquí
];

// 7. Verificar si se extrajo un número y si está en la lista de bloqueados
if ($from && in_array($from, $bloqueados)) {
    // Si está bloqueado, registra la decisión y detiene el script
    file_put_contents("log.txt", "Decisión: BLOQUEADO. No se reenvía a n8n.\n\n", FILE_APPEND);
    http_response_code(200);
    echo "Mensaje de $from bloqueado.";
    exit();
}

// 8. Si no está bloqueado, reenviar
file_put_contents("log.txt", "Decisión: NÚMERO PERMITIDO. Reenviando a n8n...\n\n", FILE_APPEND);

$n8nWebhook = 'https://n8n.wolffilms.es/webhook/f474262a-2787-4061-a7ed-89d941d066ea';
$ch = curl_init($n8nWebhook);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_input);
curl_exec($ch);
curl_close($ch);

echo "Reenviado a n8n";

?>