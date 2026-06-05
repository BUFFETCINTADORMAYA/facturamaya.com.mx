<?php
/**
 * Template: Interfaz de IA Assistant
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="yaxtun-container">
    <div class="yaxtun-header">
        <h1>🤖 Asistente IA Yaxtun</h1>
        <p>Conversación inteligente - Claude, Anthropic, Gemini</p>
    </div>

    <div class="ia-interface">
        <!-- Panel de Configuración de IA -->
        <div class="ia-config-panel">
            <h3>⚙️ Seleccionar Proveedor IA</h3>
            <div class="ia-providers">
                <button class="provider-btn active" data-provider="claude">
                    <span class="icon">🧠</span>
                    <span class="name">Claude</span>
                    <span class="status">Activo</span>
                </button>
                <button class="provider-btn" data-provider="anthropic">
                    <span class="icon">🤖</span>
                    <span class="name">Anthropic</span>
                    <span class="status">Configurar</span>
                </button>
                <button class="provider-btn" data-provider="gemini">
                    <span class="icon">✨</span>
                    <span class="name">Gemini</span>
                    <span class="status">Configurar</span>
                </button>
            </div>

            <div class="ia-controls">
                <label class="toggle-switch">
                    <input type="checkbox" id="voice-toggle" checked>
                    <span class="slider"></span>
                    <span class="label-text">🎤 Activar Voz (Whisper)</span>
                </label>
                <label class="toggle-switch">
                    <input type="checkbox" id="listening-toggle">
                    <span class="slider"></span>
                    <span class="label-text">👂 Escuchando</span>
                </label>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="ia-chat-container">
            <div class="chat-header">
                <h3 id="current-provider">Claude - Conversación Segura</h3>
                <span class="context-label" id="context-label">Contexto: General</span>
            </div>

            <div class="chat-messages" id="chat-messages">
                <div class="message system-message">
                    <div class="message-content">
                        <p><strong>Bienvenido al Asistente IA de Yaxtun</strong></p>
                        <p>Soy tu asistente profesional. Puedo ayudarte con:</p>
                        <ul>
                            <li>✅ Consultas sobre tus facturas y órdenes</li>
                            <li>✅ Análisis de datos de la empresa</li>
                            <li>✅ Asistencia en procesos administrativos</li>
                            <li>✅ Respuestas conversacionales sobre la empresa</li>
                        </ul>
                        <p><small>Tengo restricciones de seguridad. Solo puedo hablar sobre temas de tu empresa.</small></p>
                    </div>
                </div>
            </div>

            <div class="chat-input-area">
                <div class="input-group">
                    <input type="text" id="user-message" class="form-control" placeholder="Escribe tu pregunta aquí..." autocomplete="off">
                    <button id="send-btn" class="btn btn-primary">Enviar</button>
                    <button id="voice-btn" class="btn btn-secondary" title="Grabar voz">🎤</button>
                </div>
                <div id="voice-indicator" class="voice-indicator hidden">
                    <span class="recording-dot"></span>
                    <span>Grabando...</span>
                </div>
            </div>
        </div>

        <!-- Historial y Controles -->
        <div class="ia-sidebar">
            <div class="sidebar-section">
                <h4>📋 Contexto de Consulta</h4>
                <select id="context-select" class="form-control">
                    <option value="general">General</option>
                    <option value="facturas">Facturas</option>
                    <option value="ordenes">Órdenes de Trabajo</option>
                    <option value="clientes">Clientes</option>
                    <option value="reportes">Reportes</option>
                    <option value="admin">Administración</option>
                </select>
            </div>

            <div class="sidebar-section">
                <h4>💾 Historial</h4>
                <div id="conversation-history" class="history-list">
                    <p class="empty-state">Sin historial aún</p>
                </div>
            </div>

            <div class="sidebar-section">
                <h4>🔐 Sesión</h4>
                <button class="btn btn-sm btn-warning" onclick="limpiarHistorial()">Limpiar Historial</button>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-danger" style="display: block; margin-top: 10px;">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</div>

<style>
.ia-interface {
    display: grid;
    grid-template-columns: 250px 1fr 250px;
    gap: 20px;
    height: 600px;
    margin-top: 20px;
}

.ia-config-panel {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    overflow-y: auto;
}

.ia-providers {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.provider-btn {
    padding: 10px;
    border: 2px solid #ddd;
    border-radius: 6px;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}

.provider-btn.active {
    border-color: #0066cc;
    background: #e6f0ff;
}

.provider-btn .icon {
    font-size: 24px;
}

.ia-controls {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.toggle-switch {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.slider {
    width: 40px;
    height: 20px;
    background: #ccc;
    border-radius: 10px;
    position: relative;
    transition: 0.3s;
}

input[type="checkbox"]:checked + .slider {
    background: #4CAF50;
}

.ia-chat-container {
    display: flex;
    flex-direction: column;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.chat-header {
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background: #f9f9f9;
}

.message {
    margin-bottom: 15px;
    display: flex;
    animation: slideIn 0.3s ease-in;
}

.message.user-message {
    justify-content: flex-end;
}

.message.user-message .message-content {
    background: #0066cc;
    color: white;
}

.message.system-message .message-content {
    background: #e8f0fe;
    color: #000;
}

.message-content {
    padding: 10px 15px;
    border-radius: 8px;
    max-width: 80%;
    word-wrap: break-word;
}

.chat-input-area {
    padding: 15px;
    border-top: 1px solid #ddd;
    background: white;
}

.input-group {
    display: flex;
    gap: 10px;
}

.input-group input {
    flex: 1;
}

.voice-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
    color: #ff6b6b;
    font-weight: bold;
}

.recording-dot {
    width: 12px;
    height: 12px;
    background: #ff6b6b;
    border-radius: 50%;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.ia-sidebar {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    overflow-y: auto;
}

.sidebar-section {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.sidebar-section:last-child {
    border-bottom: none;
}

.history-list {
    max-height: 200px;
    overflow-y: auto;
}

.history-item {
    padding: 8px;
    border-left: 3px solid #0066cc;
    margin-bottom: 8px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 12px;
    background: #f5f5f5;
}

.history-item:hover {
    background: #eeeeee;
}

.empty-state {
    text-align: center;
    color: #999;
    font-size: 12px;
    padding: 20px 0;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
let currentProvider = 'claude';
let isListening = false;

document.querySelectorAll('.provider-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.provider-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentProvider = this.dataset.provider;
        document.getElementById('current-provider').textContent = this.querySelector('.name').textContent + ' - Conversación Segura';
    });
});

document.getElementById('send-btn').addEventListener('click', function() {
    const message = document.getElementById('user-message').value.trim();
    if (message) {
        agregarMensaje(message, 'user');
        enviarAlIA(message);
        document.getElementById('user-message').value = '';
    }
});

document.getElementById('user-message').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('send-btn').click();
    }
});

document.getElementById('voice-btn').addEventListener('click', function() {
    if (!isListening) {
        iniciarGrabacion();
    } else {
        detenerGrabacion();
    }
});

function agregarMensaje(texto, tipo) {
    const chatMessages = document.getElementById('chat-messages');
    const div = document.createElement('div');
    div.className = 'message ' + (tipo === 'user' ? 'user-message' : 'system-message');
    div.innerHTML = '<div class="message-content">' + escapeHtml(texto) + '</div>';
    chatMessages.appendChild(div);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function enviarAlIA(pregunta) {
    const context = document.getElementById('context-select').value;
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=yaxtun_ia_respuesta&proveedor=' + currentProvider + '&pregunta=' + encodeURIComponent(pregunta) + '&contexto=' + context + '&nonce=<?php echo wp_create_nonce('yaxtun_ia_nonce'); ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            agregarMensaje(data.respuesta, 'system');
        } else {
            agregarMensaje('Error: ' + (data.error || 'Error desconocido'), 'system');
        }
    })
    .catch(error => {
        agregarMensaje('Error de conexión: ' + error.message, 'system');
    });
}

function iniciarGrabacion() {
    // Implementación de grabación de audio con Web Audio API
    document.getElementById('voice-indicator').classList.remove('hidden');
    document.getElementById('voice-btn').textContent = '⏹️';
    isListening = true;
}

function detenerGrabacion() {
    document.getElementById('voice-indicator').classList.add('hidden');
    document.getElementById('voice-btn').textContent = '🎤';
    isListening = false;
}

function limpiarHistorial() {
    if (confirm('¿Deseas limpiar el historial de conversación?')) {
        document.getElementById('chat-messages').innerHTML = '<div class="message system-message"><div class="message-content"><p>Historial limpiado</p></div></div>';
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>