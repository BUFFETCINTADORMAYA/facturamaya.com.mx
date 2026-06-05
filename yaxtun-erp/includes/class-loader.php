<?php
/**
 * Autoloader para las clases del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Yaxtun_ERP_Loader {
    public static function autoload($class) {
        if (strpos($class, 'Yaxtun_ERP') === false) {
            return;
        }

        $class_path = YAXTUN_ERP_PLUGIN_DIR . 'includes/';
        $class_file = strtolower(str_replace('_', '-', $class)) . '.php';
        $file = $class_path . $class_file;

        if (file_exists($file)) {
            require_once $file;
        }
    }
}

spl_autoload_register(array('Yaxtun_ERP_Loader', 'autoload'));

?>