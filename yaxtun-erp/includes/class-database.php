<?php
/**
 * Gestión de base de datos
 */

if (!defined('ABSPATH')) {
    exit;
}

class Yaxtun_ERP_Database {
    private $wpdb;
    private $charset_collate;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    public function create_tables() {
        $this->create_facturas_table();
        $this->create_ordenes_table();
        $this->create_clientes_table();
        $this->create_usuarios_ia_table();
        $this->create_conversaciones_ia_table();
    }

    private function create_facturas_table() {
        $table_name = $this->wpdb->prefix . 'yaxtun_facturas';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            cliente_id bigint(20) NOT NULL,
            folio varchar(50) NOT NULL UNIQUE,
            fecha_emision datetime DEFAULT CURRENT_TIMESTAMP,
            fecha_modificacion datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            fecha_pagado datetime,
            subtotal decimal(10, 2) NOT NULL,
            iva decimal(10, 2) NOT NULL,
            total decimal(10, 2) NOT NULL,
            estado varchar(20) DEFAULT 'borrador',
            formato_exportacion varchar(50),
            xml_sat longtext,
            pdf_file longtext,
            datos_json longtext,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY cliente_id (cliente_id),
            KEY estado (estado),
            UNIQUE KEY folio (folio)
        ) $this->charset_collate;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function create_ordenes_table() {
        $table_name = $this->wpdb->prefix . 'yaxtun_ordenes';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            cliente_id bigint(20) NOT NULL,
            numero_orden varchar(50) NOT NULL UNIQUE,
            fecha_creacion datetime DEFAULT CURRENT_TIMESTAMP,
            fecha_cierre datetime,
            descripcion longtext,
            estado varchar(20) DEFAULT 'abierta',
            observaciones longtext,
            datos_json longtext,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY cliente_id (cliente_id),
            KEY estado (estado)
        ) $this->charset_collate;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function create_clientes_table() {
        $table_name = $this->wpdb->prefix . 'yaxtun_clientes';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20),
            nombre varchar(255) NOT NULL,
            email varchar(100),
            telefono varchar(20),
            rfc varchar(13),
            razon_social varchar(255),
            domicilio longtext,
            ciudad varchar(100),
            estado varchar(100),
            codigo_postal varchar(10),
            tipo_cliente varchar(20) DEFAULT 'general',
            fecha_registro datetime DEFAULT CURRENT_TIMESTAMP,
            datos_json longtext,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY email (email)
        ) $this->charset_collate;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function create_usuarios_ia_table() {
        $table_name = $this->wpdb->prefix . 'yaxtun_usuarios_ia';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            proveedor_ia varchar(50) NOT NULL,
            api_key varchar(500) NOT NULL,
            activo tinyint(1) DEFAULT 1,
            fecha_agregada datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_proveedor (user_id, proveedor_ia)
        ) $this->charset_collate;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function create_conversaciones_ia_table() {
        $table_name = $this->wpdb->prefix . 'yaxtun_conversaciones_ia';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            proveedor_ia varchar(50),
            contexto varchar(255),
            pregunta longtext NOT NULL,
            respuesta longtext,
            transcripcion_voz longtext,
            fecha_creacion datetime DEFAULT CURRENT_TIMESTAMP,
            tokens_usados int(11),
            costo decimal(8, 4),
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY fecha_creacion (fecha_creacion)
        ) $this->charset_collate;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function get_results($query) {
        return $this->wpdb->get_results($query);
    }

    public function get_row($query) {
        return $this->wpdb->get_row($query);
    }

    public function insert($table, $data) {
        return $this->wpdb->insert($this->wpdb->prefix . $table, $data);
    }

    public function update($table, $data, $where) {
        return $this->wpdb->update($this->wpdb->prefix . $table, $data, $where);
    }

    public function delete($table, $where) {
        return $this->wpdb->delete($this->wpdb->prefix . $table, $where);
    }
}

?>