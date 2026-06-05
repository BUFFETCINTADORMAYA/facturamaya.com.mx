<?php
/**
 * Integración con APIs de IA (Claude, Anthropic, Gemini)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Yaxtun_ERP_API_IA {
    private $proveedores = array('claude', 'anthropic', 'gemini');
    private $db;

    public function __construct() {
        $this->db = new Yaxtun_ERP_Database();
    }

    public static function render_ia_interface() {
        if (!current_user_can('use_ia')) {
            wp_die('No tienes permisos para usar la IA');
        }
        
        include YAXTUN_ERP_PLUGIN_DIR . 'templates/admin/ia-assistant.php';
    }

    public function obtener_respuesta_ia($pregunta, $contexto = '', $proveedor = 'claude') {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return array('error' => 'Usuario no autenticado');
        }

        // Obtener API key del usuario para el proveedor
        $api_key = $this->get_user_api_key($user_id, $proveedor);
        
        if (!$api_key) {
            return array('error' => "No hay API key configurada para {$proveedor}");
        }

        // Llamar a la IA apropiada
        switch ($proveedor) {
            case 'claude':
            case 'anthropic':
                $respuesta = $this->llamar_claude($pregunta, $api_key, $contexto);
                break;
            case 'gemini':
                $respuesta = $this->llamar_gemini($pregunta, $api_key, $contexto);
                break;
            default:
                $respuesta = array('error' => 'Proveedor no soportado');
        }

        // Guardar en historial
        if (!isset($respuesta['error'])) {
            $this->guardar_conversacion($user_id, $proveedor, $pregunta, $respuesta, $contexto);
        }

        return $respuesta;
    }

    private function llamar_claude($pregunta, $api_key, $contexto = '') {
        try {
            $instruccion_sistema = $this->get_system_prompt($contexto);
            
            $payload = array(
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 2048,
                'system' => $instruccion_sistema,
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $pregunta
                    )
                )
            );

            $response = wp_remote_post('https://api.anthropic.com/v1/messages', array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'x-api-key' => $api_key,
                    'anthropic-version' => '2023-06-01'
                ),
                'body' => wp_json_encode($payload),
                'timeout' => 30
            ));

            if (is_wp_error($response)) {
                return array('error' => $response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['content'][0]['text'])) {
                return array(
                    'respuesta' => $data['content'][0]['text'],
                    'tokens' => isset($data['usage']) ? $data['usage']['output_tokens'] : 0,
                    'proveedor' => 'claude'
                );
            }

            return array('error' => 'Error en respuesta de Claude');
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    private function llamar_gemini($pregunta, $api_key, $contexto = '') {
        try {
            $instruccion_sistema = $this->get_system_prompt($contexto);
            
            $payload = array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array(
                                'text' => $instruccion_sistema . "\n\n" . $pregunta
                            )
                        )
                    )
                ),
                'generationConfig' => array(
                    'maxOutputTokens' => 2048,
                    'temperature' => 0.7
                )
            );

            $response = wp_remote_post('https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=' . $api_key, array(
                'headers' => array(
                    'Content-Type' => 'application/json'
                ),
                'body' => wp_json_encode($payload),
                'timeout' => 30
            ));

            if (is_wp_error($response)) {
                return array('error' => $response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return array(
                    'respuesta' => $data['candidates'][0]['content']['parts'][0]['text'],
                    'tokens' => isset($data['usageMetadata']) ? $data['usageMetadata']['outputTokenCount'] : 0,
                    'proveedor' => 'gemini'
                );
            }

            return array('error' => 'Error en respuesta de Gemini');
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    private function get_system_prompt($contexto = '') {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $user_role = $user ? $user->roles[0] : 'usuario';

        $prompt = "Eres un asistente de IA profesional para el sistema ERP Yaxtun.\n";
        $prompt .= "Tu rol es ayudar con consultas sobre la empresa y sus operaciones.\n\n";

        // Restricciones según rol
        if ($user_role === 'administrator') {
            $prompt .= "Eres un asistente ADMINISTRATIVO. Tienes acceso a toda la información de la empresa.\n";
            $prompt .= "Puedes ayudar con: gestión de usuarios, configuraciones, reportes, análisis de datos, etc.\n";
        } else if ($user_role === 'yaxtun_tecnico') {
            $prompt .= "Eres un asistente TÉCNICO. Puedes consultar órdenes de trabajo y facturas.\n";
            $prompt .= "No tienes acceso a información confidencial de usuarios o configuraciones del sistema.\n";
        } else if ($user_role === 'yaxtun_cliente') {
            $prompt .= "Eres un asistente CLIENTE. Solo puedes consultar tus propias órdenes y facturas.\n";
            $prompt .= "No tienes acceso a información de otros clientes o empleados.\n";
        }

        $prompt .= "\nContexto: " . ($contexto ? $contexto : "General");
        $prompt .= "\nSiempre responde en forma conversacional pero profesional.\n";
        $prompt .= "Si el usuario hace una pregunta fuera del tema de la empresa, di educadamente que solo puedes ayudar con consultas de la empresa.\n";

        return $prompt;
    }

    private function guardar_conversacion($user_id, $proveedor, $pregunta, $respuesta, $contexto = '') {
        global $wpdb;
        
        $data = array(
            'user_id' => $user_id,
            'proveedor_ia' => $proveedor,
            'contexto' => $contexto,
            'pregunta' => $pregunta,
            'respuesta' => isset($respuesta['respuesta']) ? $respuesta['respuesta'] : '',
            'tokens_usados' => isset($respuesta['tokens']) ? $respuesta['tokens'] : 0,
            'fecha_creacion' => current_time('mysql')
        );

        $wpdb->insert($wpdb->prefix . 'yaxtun_conversaciones_ia', $data);
    }

    private function get_user_api_key($user_id, $proveedor) {
        global $wpdb;
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT api_key FROM {$wpdb->prefix}yaxtun_usuarios_ia WHERE user_id = %d AND proveedor_ia = %s AND activo = 1",
                $user_id,
                $proveedor
            )
        );

        return $result;
    }

    public function transcribir_audio($audio_file) {
        try {
            // Usar OpenAI Whisper para transcripción
            $user_id = get_current_user_id();
            $api_key = get_option('yaxtun_openai_key');

            if (!$api_key) {
                return array('error' => 'API key de OpenAI no configurada');
            }

            $response = wp_remote_post('https://api.openai.com/v1/audio/transcriptions', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key
                ),
                'body' => array(
                    'file' => new CurlFile($audio_file),
                    'model' => 'whisper-1'
                ),
                'timeout' => 60
            ));

            if (is_wp_error($response)) {
                return array('error' => $response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['text'])) {
                return array('transcripcion' => $data['text']);
            }

            return array('error' => 'Error en transcripción');
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}

?>