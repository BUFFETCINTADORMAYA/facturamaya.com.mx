<?php
/**
 * Gestor de autenticación y sesiones
 */

if (!defined('ABSPATH')) {
    exit;
}

class Yaxtun_ERP_Auth {
    
    public static function init() {
        add_action('init', array(__CLASS__, 'check_local_session'));
        add_action('wp_logout', array(__CLASS__, 'handle_logout'));
    }

    public static function check_local_session() {
        if (!is_admin()) {
            return;
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }

        $device_id = self::get_device_id();
        $is_local = self::es_dispositivo_local();

        if ($is_local) {
            // No requerir contraseña en dispositivo local del admin
            set_transient('yaxtun_admin_local_' . $user_id, '1', HOUR_IN_SECONDS);
        } else {
            // Verificar PIN en otros dispositivos
            if (isset($_POST['yaxtun_pin'])) {
                $pin = sanitize_text_field($_POST['yaxtun_pin']);
                if (self::verificar_pin($user_id, $pin)) {
                    set_transient('yaxtun_pin_verificado_' . $user_id . '_' . $device_id, '1', DAY_IN_SECONDS);
                }
            }
        }
    }

    public static function es_dispositivo_local() {
        $ip = self::get_client_ip();
        return in_array($ip, array('127.0.0.1', '::1', $_SERVER['SERVER_ADDR'] ?? ''));
    }

    public static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public static function get_device_id() {
        if (isset($_COOKIE['yaxtun_device_id'])) {
            return $_COOKIE['yaxtun_device_id'];
        }
        
        $device_id = wp_generate_uuid4();
        setcookie('yaxtun_device_id', $device_id, time() + (365 * DAY_IN_SECONDS), '/');
        return $device_id;
    }

    public static function generar_pin($user_id) {
        $pin = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        update_user_meta($user_id, 'yaxtun_pin', wp_hash_password($pin));
        update_user_meta($user_id, 'yaxtun_pin_generado_en', current_time('mysql'));
        
        return $pin;
    }

    public static function verificar_pin($user_id, $pin) {
        $pin_hash = get_user_meta($user_id, 'yaxtun_pin', true);
        
        if (empty($pin_hash)) {
            return false;
        }

        return wp_check_password($pin, $pin_hash);
    }

    public static function crear_usuario_erp($email, $nombre, $rol = 'administrator') {
        $user = get_user_by('email', $email);
        
        if ($user) {
            return array('error' => 'El usuario ya existe');
        }

        // Generar contraseña segura
        $password = wp_generate_password(24, true, true);
        
        $user_id = wp_create_user($email, $password, $email);

        if (is_wp_error($user_id)) {
            return array('error' => $user_id->get_error_message());
        }

        $user = new WP_User($user_id);
        $user->set_role($rol);

        // Generar PIN inicial
        $pin = self::generar_pin($user_id);

        return array(
            'success' => true,
            'user_id' => $user_id,
            'email' => $email,
            'password' => $password,
            'pin' => $pin,
            'mensaje' => 'Usuario creado exitosamente'
        );
    }

    public static function handle_logout() {
        $user_id = get_current_user_id();
        delete_transient('yaxtun_admin_local_' . $user_id);
    }
}

// Inicializar
Yaxtun_ERP_Auth::init();

?>