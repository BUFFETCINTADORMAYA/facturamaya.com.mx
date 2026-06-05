<?php
/**
 * Archivo principal - Cargar todas las dependencias
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar clases principales
require_once YAXTUN_ERP_PLUGIN_DIR . 'includes/ajax-handlers.php';
require_once YAXTUN_ERP_PLUGIN_DIR . 'includes/class-auth.php';
require_once YAXTUN_ERP_PLUGIN_DIR . 'includes/class-facturama.php';

// Al activar el plugin
register_activation_hook(YAXTUN_ERP_PLUGIN_DIR . 'yaxtun-erp.php', function() {
    Yaxtun_ERP_Security::add_capabilities();
    
    // Crear usuario admin de prueba (opcional)
    if (!get_user_by('email', 'alejandrojimenezmtzz@gmail.com')) {
        Yaxtun_ERP_Auth::crear_usuario_erp(
            'alejandrojimenezmtzz@gmail.com',
            'Administrador',
            'administrator'
        );
    }
});

// AJAX para guardar configuración
add_action('wp_ajax_yaxtun_guardar_settings', function() {
    check_ajax_referer('yaxtun_settings_nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('error' => 'Permisos insuficientes'));
    }

    // Guardar opciones
    $opciones = array(
        'yaxtun_nombre_empresa',
        'yaxtun_rfc_empresa',
        'yaxtun_domicilio_empresa',
        'yaxtun_facturama_usuario',
        'yaxtun_facturama_contrasena',
        'yaxtun_claude_api_key',
        'yaxtun_gemini_api_key',
        'yaxtun_openai_key',
        'yaxtun_iva_default',
        'yaxtun_moneda',
        'yaxtun_modo_compatible_hostinger'
    );

    foreach ($opciones as $opcion) {
        if (isset($_POST[$opcion])) {
            if (strpos($opcion, 'api_key') !== false || strpos($opcion, 'contrasena') !== false) {
                // Encriptar datos sensibles
                update_option($opcion, wp_hash_password($_POST[$opcion]));
            } else {
                update_option($opcion, sanitize_text_field($_POST[$opcion]));
            }
        }
    }

    wp_send_json_success(array('mensaje' => 'Configuración guardada exitosamente'));
});

add_action('wp_ajax_nopriv_yaxtun_guardar_settings', 'wp_send_json_error');

?>