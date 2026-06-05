<?php
/**
 * Panel de administración
 */

if (!defined('ABSPATH')) {
    exit;
}

class Yaxtun_ERP_Admin {
    
    public static function render_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos para acceder a esta página');
                
        include YAXTUN_ERP_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }

    public static function render_ordenes() {
        if (!current_user_can('manage_ordenes')) {
            wp_die('No tienes permisos para acceder a esta página');
        }
        
        include YAXTUN_ERP_PLUGIN_DIR . 'templates/admin/ordenes.php';
    }

    public static function render_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos para acceder a esta página');
        }
        
        include YAXTUN_ERP_PLUGIN_DIR . 'templates/admin/settings.php';
    }
}

?>