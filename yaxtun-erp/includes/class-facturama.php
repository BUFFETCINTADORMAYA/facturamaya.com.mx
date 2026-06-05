<?php
/**
 * Integración con Facturama API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Yaxtun_ERP_Facturama {
    private $usuario;
    private $contrasena;
    private $api_url = 'https://api.facturama.mx/api';
    private $auth_header;

    public function __construct($usuario = null, $contrasena = null) {
        $this->usuario = $usuario ?? get_option('yaxtun_facturama_usuario');
        $this->contrasena = $contrasena ?? get_option('yaxtun_facturama_contrasena');
        
        if ($this->usuario && $this->contrasena) {
            $this->auth_header = base64_encode($this->usuario . ':' . $this->contrasena);
        }
    }

    public function test_conexion() {
        try {
            $response = wp_remote_get(
                $this->api_url . '/Catalogos/Estados',
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . $this->auth_header,
                        'Content-Type' => 'application/json'
                    ),
                    'timeout' => 15
                )
            );

            if (is_wp_error($response)) {
                return array('error' => 'Error de conexión: ' . $response->get_error_message());
            }

            $status = wp_remote_retrieve_response_code($response);
            
            if ($status !== 200) {
                return array('error' => 'Error de autenticación. Verifica usuario y contraseña');
            }

            // Guardar credenciales si la conexión es exitosa
            update_option('yaxtun_facturama_usuario', $this->usuario);
            update_option('yaxtun_facturama_contrasena', $this->contrasena);
            update_option('yaxtun_facturama_conectado', '1');

            return array(
                'success' => true,
                'mensaje' => 'Conexión exitosa con Facturama',
                'usuario' => $this->usuario
            );
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    public function obtener_timbres() {
        try {
            $response = wp_remote_get(
                $this->api_url . '/Account/Disponibles',
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . $this->auth_header,
                        'Content-Type' => 'application/json'
                    ),
                    'timeout' => 15
                )
            );

            if (is_wp_error($response)) {
                return array('error' => $response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            return $data;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    public function crear_cfdi($datos_factura) {
        try {
            $cfdi_data = $this->preparar_datos_cfdi($datos_factura);

            $response = wp_remote_post(
                $this->api_url . '/Cfdi',
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . $this->auth_header,
                        'Content-Type' => 'application/json'
                    ),
                    'body' => wp_json_encode($cfdi_data),
                    'timeout' => 30
                )
            );

            if (is_wp_error($response)) {
                return array('error' => $response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['Id'])) {
                return array(
                    'success' => true,
                    'id_cfdi' => $data['Id'],
                    'folio_fiscal' => $data['Folio'],
                    'serie' => $data['Serie']
                );
            }

            return array('error' => 'Error al crear CFDI');
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    private function preparar_datos_cfdi($datos_factura) {
        $empresa = array(
            'Rfc' => get_option('yaxtun_rfc_empresa'),
            'Nombre' => get_option('yaxtun_nombre_empresa'),
            'RegimenFiscal' => '601'
        );

        $cliente = get_option('yaxtun_cliente_actual_' . $datos_factura['cliente_id'], array());

        $conceptos = array();
        $items = isset($datos_factura['items']) ? $datos_factura['items'] : array();
        
        foreach ($items as $item) {
            $conceptos[] = array(
                'Cantidad' => floatval($item['cantidad']),
                'Descripcion' => $item['concepto'],
                'ValorUnitario' => floatval($item['precio']),
                'Importe' => floatval($item['total']),
                'ClaveProdServ' => '01010101'
            );
        }

        return array(
            'Emisor' => $empresa,
            'Receptor' => array(
                'Rfc' => isset($cliente['rfc']) ? $cliente['rfc'] : 'XAXX010101000',
                'Nombre' => isset($cliente['nombre']) ? $cliente['nombre'] : 'Cliente General'
            ),
            'Conceptos' => $conceptos,
            'Total' => floatval($datos_factura['total']),
            'Moneda' => 'MXN'
        );
    }

    public function obtener_cfdi($id_cfdi) {
        try {
            $response = wp_remote_get(
                $this->api_url . '/Cfdi/' . $id_cfdi,
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . $this->auth_header,
                        'Content-Type' => 'application/json'
                    ),
                    'timeout' => 15
                )
            );

            if (is_wp_error($response)) {
                return array('error' => $response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            return json_decode($body, true);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}

?>