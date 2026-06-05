<?php
/**
 * AJAX Handlers para el plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

// Respuesta de IA
add_action('wp_ajax_yaxtun_ia_respuesta', 'yaxtun_ia_respuesta_handler');
add_action('wp_ajax_nopriv_yaxtun_ia_respuesta', 'yaxtun_ia_respuesta_handler');

function yaxtun_ia_respuesta_handler() {
    check_ajax_referer('yaxtun_ia_nonce', 'nonce');
    
    if (!current_user_can('use_ia')) {
        wp_send_json_error(array('error' => 'Permisos insuficientes'));
    }

    $proveedor = sanitize_text_field($_POST['proveedor'] ?? 'claude');
    $pregunta = sanitize_textarea_field($_POST['pregunta'] ?? '');
    $contexto = sanitize_text_field($_POST['contexto'] ?? 'general');

    if (empty($pregunta)) {
        wp_send_json_error(array('error' => 'Pregunta vacía'));
    }

    $ia_api = new Yaxtun_ERP_API_IA();
    $respuesta = $ia_api->obtener_respuesta_ia($pregunta, $contexto, $proveedor);

    if (isset($respuesta['error'])) {
        wp_send_json_error($respuesta);
    }

    wp_send_json_success($respuesta);
}

// Exportar factura
add_action('wp_ajax_yaxtun_exportar_factura', 'yaxtun_exportar_factura_handler');

function yaxtun_exportar_factura_handler() {
    check_ajax_referer('yaxtun_export', 'nonce');
    
    if (!current_user_can('manage_facturas')) {
        wp_send_json_error(array('error' => 'Permisos insuficientes'));
    }

    $factura_id = intval($_GET['factura_id'] ?? 0);
    $formato = sanitize_text_field($_GET['formato'] ?? 'PDF');

    $facturas = new Yaxtun_ERP_Facturas();
    $resultado = $facturas->exportar_factura($factura_id, $formato);

    if (isset($resultado['error'])) {
        wp_send_json_error($resultado);
    }

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $resultado['filename'] . '"');
    echo $resultado['contenido'];
    exit;
}

// Transcripción de audio
add_action('wp_ajax_yaxtun_transcribir_audio', 'yaxtun_transcribir_audio_handler');

function yaxtun_transcribir_audio_handler() {
    check_ajax_referer('yaxtun_audio', 'nonce');
    
    if (!current_user_can('use_ia')) {
        wp_send_json_error(array('error' => 'Permisos insuficientes'));
    }

    if (!isset($_FILES['audio'])) {
        wp_send_json_error(array('error' => 'Archivo de audio no encontrado'));
    }

    $file = $_FILES['audio'];
    $ia_api = new Yaxtun_ERP_API_IA();
    $resultado = $ia_api->transcribir_audio($file['tmp_name']);

    if (isset($resultado['error'])) {
        wp_send_json_error($resultado);
    }

    wp_send_json_success($resultado);
}

// Guardar factura
add_action('wp_ajax_yaxtun_guardar_factura', 'yaxtun_guardar_factura_handler');

function yaxtun_guardar_factura_handler() {
    check_ajax_referer('yaxtun_factura_nonce', 'nonce');
    
    if (!current_user_can('manage_facturas')) {
        wp_send_json_error(array('error' => 'Permisos insuficientes'));
    }

    $datos = array(
        'cliente_id' => intval($_POST['cliente_id'] ?? 0),
        'subtotal' => floatval($_POST['subtotal'] ?? 0),
        'iva' => floatval($_POST['iva'] ?? 0),
        'total' => floatval($_POST['total'] ?? 0),
        'estado' => sanitize_text_field($_POST['estado'] ?? 'borrador'),
        'items' => isset($_POST['items']) ? $_POST['items'] : array()
    );

    $facturas = new Yaxtun_ERP_Facturas();
    $resultado = $facturas->crear_factura($datos);

    if (isset($resultado['error'])) {
        wp_send_json_error($resultado);
    }

    wp_send_json_success($resultado);
}

// Conexión con Facturama
add_action('wp_ajax_yaxtun_test_facturama', 'yaxtun_test_facturama_handler');

function yaxtun_test_facturama_handler() {
    check_ajax_referer('yaxtun_settings_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('error' => 'Permisos insuficientes'));
    }

    $usuario = sanitize_text_field($_POST['facturama_usuario'] ?? '');
    $contrasena = sanitize_text_field($_POST['facturama_contrasena'] ?? '');

    if (empty($usuario) || empty($contrasena)) {
        wp_send_json_error(array('error' => 'Usuario y contraseña requeridos'));
    }

    $facturama = new Yaxtun_ERP_Facturama($usuario, $contrasena);
    $resultado = $facturama->test_conexion();

    if (isset($resultado['error'])) {
        wp_send_json_error($resultado);
    }

    wp_send_json_success($resultado);
}

?>