<?php
/**
 * Plugin Name: Yaxtun ERP Professional
 * Plugin URI: https://yaxtun.com.mx
 * Description: Sistema ERP profesional con IA integrada (Claude, Anthropic, Gemini)
 * Version: 2.0.0
 * Author: BUFFETCINTADORMAYA
 * Author URI: https://facturamaya.com.mx
 * License: MIT
 * Text Domain: yaxtun-erp
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('YAXTUN_ERP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YAXTUN_ERP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YAXTUN_ERP_VERSION', '2.0.0');
define('YAXTUN_ERP_TEXT_DOMAIN', 'yaxtun-erp');

// Cargar archivos necesarios
require_once YAXTUN_ERP_PLUGIN_DIR . 'includes/class-loader.php';
require_once YAXTUN_ERP_PLUGIN_DIR . 'includes/class-database.php';
require_once YAXTUN_ERP_PLUGIN_DIR . 'includes/class-security.php';
require_once YAXTUN_ERP_PLUGIN_DIR . 'includes/class-admin.php';
require_once YAXTUN_ERP_PLUGIN_DIR . 'includes/class-api-ia.php';
require_once YAXTUN_ERP_PLUGIN_DIR . 'includes/class-facturas.php';

// Inicializar plugin
class Yaxtun_ERP {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }

    public function activate() {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            wp_die('Este plugin requiere PHP 7.4 o superior');
        }
        $database = new Yaxtun_ERP_Database();
        $database->create_tables();
    }

    public function deactivate() {
        // Limpiar cron jobs si existen
        wp_clear_scheduled_hook('yaxtun_erp_cleanup');
    }

    public function init() {
        load_plugin_textdomain(YAXTUN_ERP_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function add_admin_menu() {
        add_menu_page(
            'Yaxtun ERP',
            'Yaxtun ERP',
            'manage_options',
            'yaxtun-erp-dashboard',
            array('Yaxtun_ERP_Admin', 'render_dashboard'),
            'dashicons-chart-bar',
            25
        );

        add_submenu_page(
            'yaxtun-erp-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'yaxtun-erp-dashboard',
            array('Yaxtun_ERP_Admin', 'render_dashboard')
        );

        add_submenu_page(
            'yaxtun-erp-dashboard',
            'Facturas',
            'Facturas',
            'manage_facturas',
            'yaxtun-erp-facturas',
            array('Yaxtun_ERP_Facturas', 'render_facturas')
        );

        add_submenu_page(
            'yaxtun-erp-dashboard',
            'Órdenes de Trabajo',
            'Órdenes de Trabajo',
            'manage_ordenes',
            'yaxtun-erp-ordenes',
            array('Yaxtun_ERP_Admin', 'render_ordenes')
        );

        add_submenu_page(
            'yaxtun-erp-dashboard',
            'IA Assistant',
            'IA Assistant',
            'use_ia',
            'yaxtun-erp-ia',
            array('Yaxtun_ERP_API_IA', 'render_ia_interface')
        );

        add_submenu_page(
            'yaxtun-erp-dashboard',
            'Configuración',
            'Configuración',
            'manage_options',
            'yaxtun-erp-settings',
            array('Yaxtun_ERP_Admin', 'render_settings')
        );
    }

    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'yaxtun-erp') !== false) {
            wp_enqueue_style('yaxtun-admin-css', YAXTUN_ERP_PLUGIN_URL . 'assets/css/admin.css', array(), YAXTUN_ERP_VERSION);
            wp_enqueue_script('yaxtun-admin-js', YAXTUN_ERP_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), YAXTUN_ERP_VERSION, true);
            wp_localize_script('yaxtun-admin-js', 'YaxtunERP', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('yaxtun_erp_nonce'),
                'user_role' => current_user_can('manage_options') ? 'admin' : 'client'
            ));
        }
    }

    public function enqueue_frontend_scripts() {
        wp_enqueue_style('yaxtun-frontend-css', YAXTUN_ERP_PLUGIN_URL . 'assets/css/frontend.css', array(), YAXTUN_ERP_VERSION);
        wp_enqueue_script('yaxtun-frontend-js', YAXTUN_ERP_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), YAXTUN_ERP_VERSION, true);
    }
}

// Instanciar plugin
Yaxtun_ERP::get_instance();

?>