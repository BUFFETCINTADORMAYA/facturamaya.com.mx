<?php
/**
 * Gestión de seguridad y permisos
 */

if (!defined('ABSPATH')) {
    exit;
}

class Yaxtun_ERP_Security {
    
    public static function add_capabilities() {
        $admin_role = get_role('administrator');
        
        if ($admin_role) {
            $admin_role->add_cap('manage_facturas');
            $admin_role->add_cap('manage_ordenes');
            $admin_role->add_cap('view_reportes');
            $admin_role->add_cap('use_ia');
            $admin_role->add_cap('manage_usuarios_ia');
            $admin_role->add_cap('control_empresa');
        }

        // Crear rol de cliente
        add_role(
            'yaxtun_cliente',
            'Cliente Yaxtun',
            array(
                'read' => true,
                'use_ia' => true,
                'view_own_facturas' => true,
                'view_own_ordenes' => true
            )
        );

        // Crear rol de técnico
        add_role(
            'yaxtun_tecnico',
            'Técnico Yaxtun',
            array(
                'read' => true,
                'manage_ordenes' => true,
                'create_ordenes' => true,
                'use_ia' => true
            )
        );
    }

    public static function check_permission($capability) {
        if (!is_user_logged_in()) {
            return false;
        }
        return current_user_can($capability);
    }

    public static function verify_nonce($nonce, $action = 'yaxtun_erp_nonce') {
        return wp_verify_nonce($nonce, $action);
    }

    public static function sanitize_input($input, $type = 'text') {
        switch ($type) {
            case 'email':
                return sanitize_email($input);
            case 'url':
                return esc_url($input);
            case 'int':
                return intval($input);
            case 'float':
                return floatval($input);
            case 'textarea':
                return wp_kses_post($input);
            default:
                return sanitize_text_field($input);
        }
    }

    public static function escape_output($output, $type = 'html') {
        switch ($type) {
            case 'html':
                return wp_kses_post($output);
            case 'attr':
                return esc_attr($output);
            case 'url':
                return esc_url($output);
            case 'js':
                return wp_json_encode($output);
            default:
                return esc_html($output);
        }
    }

    public static function get_user_empresas($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return array();
        }

        $user_meta = get_user_meta($user_id, 'yaxtun_empresas', true);
        return is_array($user_meta) ? $user_meta : array();
    }
}

?>