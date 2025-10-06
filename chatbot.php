<?php
// chatbot.php - Código reutilizable para el chatbot flotante
?>
<!-- Chatbot flotante -->
<div id="chatbot-container">
    <div id="chatbot-header">
        <span>WolfFilms Chat</span>
        <button id="close-chatbot">&times;</button>
    </div>
    <div id="chatbot-messages" class="chat-body">
        <div class="chat-message bot-message">
            <strong>WolfBot IA:</strong> ¡Hola! ¿En qué puedo ayudarte?
        </div>
    </div>
    <div id="chatbot-input">
        <input type="text" id="chat-input" placeholder="Escribe tu mensaje...">
        <button id="send-message">Enviar</button>
    </div>
</div>
<button id="open-chatbot">💬 Chat</button>

<style>
/* Estilos para el chatbot flotante */
#chatbot-container {
    position: fixed;
    bottom: 80px;
    right: 20px;
    width: 350px;
    height: 450px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: none;
    flex-direction: column;
    z-index: 1000;
    border: 1px solid #e0e0e0;
}

#chatbot-header {
    background: #222;
    color: white;
    padding: 15px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

#close-chatbot {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
}

#chatbot-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background: #f9f9f9;
}

.chat-message {
    margin: 10px 0;
    padding: 8px 12px;
    border-radius: 18px;
    max-width: 80%;
}

.bot-message {
    background: #e3f2fd;
    margin-right: auto;
    text-align: left;
}

.user-message {
    background: #2196f3;
    color: white;
    margin-left: auto;
    text-align: right;
}

.error-message {
    background: #ffcdd2;
    color: #c62828;
    margin: 10px 0;
    padding: 8px 12px;
    border-radius: 18px;
    max-width: 80%;
}

#chatbot-input {
    display: flex;
    padding: 10px;
    border-top: 1px solid #e0e0e0;
    background: white;
}

#chat-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 20px;
    outline: none;
}

#send-message {
    margin-left: 10px;
    padding: 10px 15px;
    background: #2196f3;
    color: white;
    border: none;
    border-radius: 20px;
    cursor: pointer;
}

#open-chatbot {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #2196f3;
    color: white;
    border: none;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
}
</style>

<script>
// Generar un ID de sesión único para cada visita
const sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

// Función para enviar mensaje al webhook de n8n
async function sendMessageToN8n(message, sessionId) {
    try {
        const response = await fetch('https://n8n.wolffilms.es/webhook/web-chat-responder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                sessionId: sessionId
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error al enviar mensaje:', error);
        return {
            reply: "Lo siento, hubo un problema al procesar tu mensaje. Por favor, inténtalo de nuevo.",
            sender: "WolfBot IA"
        };
    }
}

// Función para enviar mensaje
async function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Mostrar mensaje del usuario
    const chatBody = document.getElementById('chatbot-messages');
    const userMessageDiv = document.createElement('div');
    userMessageDiv.className = 'chat-message user-message';
    userMessageDiv.innerHTML = '<strong>Tú:</strong> ' + message;
    chatBody.appendChild(userMessageDiv);
    
    // Limpiar input
    input.value = '';
    
    // Mostrar mensaje de "escribiendo..."
    const typingMessage = document.createElement('div');
    typingMessage.className = 'chat-message bot-message';
    typingMessage.id = 'typing-indicator';
    typingMessage.innerHTML = '<strong>WolfBot IA:</strong> <span style="color: #888; font-style: italic;">Escribiendo...</span>';
    chatBody.appendChild(typingMessage);
    
    // Desplazar al final del chat
    chatBody.scrollTop = chatBody.scrollHeight;
    
    try {
        // Enviar mensaje al webhook y obtener respuesta
        const response = await sendMessageToN8n(message, sessionId);
        
        // Remover indicador de escritura
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) typingIndicator.remove();
        
        // Crear mensaje de respuesta
        const botMessageDiv = document.createElement('div');
        botMessageDiv.className = 'chat-message bot-message';
        
        // Usar el reply del response
        const reply = response.reply || response.output || "No pude procesar tu mensaje";
        const sender = response.sender || "WolfBot IA";
        
        // Limpiar caracteres especiales de la respuesta
        const cleanReply = reply
            .replace(/\\n/g, '<br>')  // Convertir \n a <br>
            .replace(/\\n\d*/g, '')   // Eliminar \n seguido de cualquier número
            .replace(/\n/g, '<br>')   // Convertir saltos de línea normales a <br>
            .replace(/\*([^*]+)\*/g, '<em>$1</em>') // Convertir *texto* a <em>texto</em>
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>') // Convertir **texto** a <strong>texto</strong>
            .replace(/\\n([a-zA-Z])/g, '$1') // Eliminar \n seguido de letras
            .replace(/\\([a-zA-Z])/g, '$1'); // Eliminar cualquier \ seguido de letras
        
        botMessageDiv.innerHTML = '<strong>' + sender + ':</strong> ' + cleanReply;
        chatBody.appendChild(botMessageDiv);
        
    } catch (error) {
        // Remover indicador de escritura
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) typingIndicator.remove();
        
        // Mostrar mensaje de error
        const errorMessage = document.createElement('div');
        errorMessage.className = 'chat-message error-message';
        errorMessage.innerHTML = '<strong>Error:</strong> No se pudo obtener respuesta. Inténtalo de nuevo.';
        chatBody.appendChild(errorMessage);
    }
    
    // Desplazar al final del chat
    chatBody.scrollTop = chatBody.scrollHeight;
}

// Funcionalidad del chatbot
document.getElementById('open-chatbot').addEventListener('click', function() {
    document.getElementById('chatbot-container').style.display = 'flex';
});

document.getElementById('close-chatbot').addEventListener('click', function() {
    document.getElementById('chatbot-container').style.display = 'none';
});

document.getElementById('send-message').addEventListener('click', sendMessage);
document.getElementById('chat-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
});

// Inicializar el chat cuando la página cargue
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa los iconos de Lucide si los estás usando
    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }
});
</script>