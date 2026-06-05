<?php
/**
 * Gestión de facturas con SAT compatible y múltiples formatos
 */

if (!defined('ABSPATH')) {
    exit;
}

class Yaxtun_ERP_Facturas {
    private $db;
    private $moneda = 'MXN';
    private $formatos_soportados = array('PDF', 'XLSX', 'XML', 'CSV', 'JSON', 'ZIP', 'HTML', 'ODS');

    public function __construct() {
        $this->db = new Yaxtun_ERP_Database();
    }

    public static function render_facturas() {
        if (!current_user_can('manage_facturas')) {
            wp_die('No tienes permisos para acceder a esta página');
        }
        
        include YAXTUN_ERP_PLUGIN_DIR . 'templates/admin/facturas.php';
    }

    public function crear_factura($datos) {
        try {
            // Validar datos
            $datos_validados = $this->validar_datos_factura($datos);
            
            if (is_wp_error($datos_validados)) {
                return $datos_validados;
            }

            // Generar folio único
            $folio = $this->generar_folio();
            $datos_validados['folio'] = $folio;
            $datos_validados['user_id'] = get_current_user_id();
            $datos_validados['fecha_emision'] = current_time('mysql');

            // Insertar en base de datos
            global $wpdb;
            $insert = $wpdb->insert(
                $wpdb->prefix . 'yaxtun_facturas',
                $datos_validados
            );

            if (!$insert) {
                return new WP_Error('db_error', 'Error al guardar la factura');
            }

            return array(
                'success' => true,
                'factura_id' => $wpdb->insert_id,
                'folio' => $folio,
                'mensaje' => 'Factura creada exitosamente'
            );
        } catch (Exception $e) {
            return new WP_Error('exception', $e->getMessage());
        }
    }

    public function obtener_factura($factura_id) {
        global $wpdb;
        
        $resultado = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}yaxtun_facturas WHERE id = %d",
                $factura_id
            )
        );

        return $resultado;
    }

    public function actualizar_factura($factura_id, $datos) {
        $datos_validados = $this->validar_datos_factura($datos);
        
        if (is_wp_error($datos_validados)) {
            return $datos_validados;
        }

        $datos_validados['fecha_modificacion'] = current_time('mysql');

        global $wpdb;
        $update = $wpdb->update(
            $wpdb->prefix . 'yaxtun_facturas',
            $datos_validados,
            array('id' => $factura_id)
        );

        if ($update === false) {
            return new WP_Error('db_error', 'Error al actualizar la factura');
        }

        return array(
            'success' => true,
            'mensaje' => 'Factura actualizada exitosamente'
        );
    }

    public function exportar_factura($factura_id, $formato = 'PDF') {
        $formato = strtoupper($formato);

        if (!in_array($formato, $this->formatos_soportados)) {
            return new WP_Error('formato_invalido', "Formato {$formato} no soportado");
        }

        $factura = $this->obtener_factura($factura_id);
        
        if (!$factura) {
            return new WP_Error('not_found', 'Factura no encontrada');
        }

        switch ($formato) {
            case 'PDF':
                return $this->exportar_pdf($factura);
            case 'XLSX':
                return $this->exportar_xlsx($factura);
            case 'XML':
                return $this->exportar_xml_sat($factura);
            case 'CSV':
                return $this->exportar_csv($factura);
            case 'JSON':
                return $this->exportar_json($factura);
            case 'ZIP':
                return $this->exportar_zip_completo($factura);
            case 'HTML':
                return $this->exportar_html($factura);
            case 'ODS':
                return $this->exportar_ods($factura);
            default:
                return new WP_Error('formato_no_implementado', "Exportación a {$formato} no implementada");
        }
    }

    private function exportar_pdf($factura) {
        // Implementación de generación de PDF
        try {
            require_once YAXTUN_ERP_PLUGIN_DIR . 'vendors/dompdf/autoload.inc.php';
            
            $html = $this->generar_html_factura($factura);
            $dompdf = new Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();

            $filename = 'Factura_' . $factura->folio . '_' . date('Y-m-d') . '.pdf';
            
            return array(
                'success' => true,
                'contenido' => $dompdf->output(),
                'filename' => $filename
            );
        } catch (Exception $e) {
            return new WP_Error('pdf_error', 'Error generando PDF: ' . $e->getMessage());
        }
    }

    private function exportar_xlsx($factura) {
        try {
            require_once YAXTUN_ERP_PLUGIN_DIR . 'vendors/phpoffice/spreadsheet/autoload.php';
            
            $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezado
            $sheet->setCellValue('A1', 'FACTURA');
            $sheet->setCellValue('A2', 'Folio: ' . $factura->folio);
            $sheet->setCellValue('A3', 'Fecha: ' . $factura->fecha_emision);
            $sheet->setCellValue('A4', 'Moneda: ' . $this->moneda);
            
            // Datos de la factura
            $row = 6;
            $sheet->setCellValue('A' . $row, 'Concepto');
            $sheet->setCellValue('B' . $row, 'Cantidad');
            $sheet->setCellValue('C' . $row, 'Precio Unitario');
            $sheet->setCellValue('D' . $row, 'Total');

            $datos = json_decode($factura->datos_json, true);
            if (is_array($datos)) {
                foreach ($datos as $item) {
                    $row++;
                    $sheet->setCellValue('A' . $row, isset($item['concepto']) ? $item['concepto'] : '');
                    $sheet->setCellValue('B' . $row, isset($item['cantidad']) ? $item['cantidad'] : '');
                    $sheet->setCellValue('C' . $row, isset($item['precio']) ? $item['precio'] : '');
                    $sheet->setCellValue('D' . $row, isset($item['total']) ? $item['total'] : '');
                }
            }

            $row += 2;
            $sheet->setCellValue('C' . $row, 'Subtotal:');
            $sheet->setCellValue('D' . $row, $factura->subtotal);
            
            $row++;
            $sheet->setCellValue('C' . $row, 'IVA (16%):');
            $sheet->setCellValue('D' . $row, $factura->iva);
            
            $row++;
            $sheet->setCellValue('C' . $row, 'TOTAL:');
            $sheet->setCellValue('D' . $row, $factura->total);

            $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'Factura_' . $factura->folio . '_' . date('Y-m-d') . '.xlsx';
            
            $output = ob_get_clean();
            ob_start();
            $writer->save('php://output');
            $contenido = ob_get_clean();

            return array(
                'success' => true,
                'contenido' => $contenido,
                'filename' => $filename
            );
        } catch (Exception $e) {
            return new WP_Error('xlsx_error', 'Error generando XLSX: ' . $e->getMessage());
        }
    }

    private function exportar_xml_sat($factura) {
        // Generar XML compatible con SAT
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Comprobante/>');
        
        $xml->addAttribute('version', '4.0');
        $xml->addAttribute('Moneda', $this->moneda);
        $xml->addAttribute('Fecha', $factura->fecha_emision);
        $xml->addAttribute('Folio', $factura->folio);
        $xml->addAttribute('Serie', 'SAT');
        
        // Agregar información del emisor y receptor
        $emisor = $xml->addChild('Emisor');
        $emisor->addAttribute('Rfc', get_option('yaxtun_rfc_empresa', 'XXX000000XXX'));
        $emisor->addAttribute('Nombre', get_option('yaxtun_nombre_empresa', 'Empresa'));
        
        $receptor = $xml->addChild('Receptor');
        $receptor->addAttribute('Rfc', isset($factura->cliente_rfc) ? $factura->cliente_rfc : 'XAXX010101000');
        $receptor->addAttribute('UsoCFDI', '601');
        
        // Conceptos
        $conceptos = $xml->addChild('Conceptos');
        $datos = json_decode($factura->datos_json, true);
        if (is_array($datos)) {
            foreach ($datos as $item) {
                $concepto = $conceptos->addChild('Concepto');
                $concepto->addAttribute('Cantidad', isset($item['cantidad']) ? $item['cantidad'] : 1);
                $concepto->addAttribute('Descripcion', isset($item['concepto']) ? $item['concepto'] : '');
                $concepto->addAttribute('ValorUnitario', isset($item['precio']) ? $item['precio'] : 0);
                $concepto->addAttribute('Importe', isset($item['total']) ? $item['total'] : 0);
            }
        }
        
        // Totales
        $totales = $xml->addChild('Totales');
        $totales->addAttribute('Subtotal', $factura->subtotal);
        $totales->addAttribute('ImpuestosTraslados', $factura->iva);
        $totales->addAttribute('Total', $factura->total);

        $filename = 'Factura_' . $factura->folio . '_' . date('Y-m-d') . '.xml';
        
        return array(
            'success' => true,
            'contenido' => $xml->asXML(),
            'filename' => $filename
        );
    }

    private function exportar_csv($factura) {
        $csv = "FACTURA," . $factura->folio . "\n";
        $csv .= "Fecha," . $factura->fecha_emision . "\n";
        $csv .= "Moneda," . $this->moneda . "\n\n";
        $csv .= "Concepto,Cantidad,Precio Unitario,Total\n";
        
        $datos = json_decode($factura->datos_json, true);
        if (is_array($datos)) {
            foreach ($datos as $item) {
                $csv .= isset($item['concepto']) ? $item['concepto'] : '' . ",";
                $csv .= isset($item['cantidad']) ? $item['cantidad'] : '' . ",";
                $csv .= isset($item['precio']) ? $item['precio'] : '' . ",";
                $csv .= isset($item['total']) ? $item['total'] : '' . "\n";
            }
        }
        
        $csv .= "\nSubtotal," . $factura->subtotal . "\n";
        $csv .= "IVA (16%)," . $factura->iva . "\n";
        $csv .= "TOTAL," . $factura->total . "\n";

        $filename = 'Factura_' . $factura->folio . '_' . date('Y-m-d') . '.csv';
        
        return array(
            'success' => true,
            'contenido' => $csv,
            'filename' => $filename
        );
    }

    private function exportar_json($factura) {
        $datos = array(
            'folio' => $factura->folio,
            'fecha_emision' => $factura->fecha_emision,
            'moneda' => $this->moneda,
            'subtotal' => $factura->subtotal,
            'iva' => $factura->iva,
            'total' => $factura->total,
            'estado' => $factura->estado,
            'items' => json_decode($factura->datos_json, true)
        );

        $filename = 'Factura_' . $factura->folio . '_' . date('Y-m-d') . '.json';
        
        return array(
            'success' => true,
            'contenido' => wp_json_encode($datos, JSON_PRETTY_PRINT),
            'filename' => $filename
        );
    }

    private function exportar_html($factura) {
        $html = $this->generar_html_factura($factura);
        $filename = 'Factura_' . $factura->folio . '_' . date('Y-m-d') . '.html';
        
        return array(
            'success' => true,
            'contenido' => $html,
            'filename' => $filename
        );
    }

    private function exportar_ods($factura) {
        // Implementación ODS similar a XLSX pero con formato ODS
        try {
            require_once YAXTUN_ERP_PLUGIN_DIR . 'vendors/phpoffice/spreadsheet/autoload.php';
            
            $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Datos similares a XLSX
            $sheet->setCellValue('A1', 'FACTURA');
            $sheet->setCellValue('A2', 'Folio: ' . $factura->folio);
            $sheet->setCellValue('A3', 'Fecha: ' . $factura->fecha_emision);
            $sheet->setCellValue('A4', 'Moneda: ' . $this->moneda);
            
            $row = 6;
            $sheet->setCellValue('A' . $row, 'Concepto');
            $sheet->setCellValue('B' . $row, 'Cantidad');
            $sheet->setCellValue('C' . $row, 'Precio Unitario');
            $sheet->setCellValue('D' . $row, 'Total');

            $datos = json_decode($factura->datos_json, true);
            if (is_array($datos)) {
                foreach ($datos as $item) {
                    $row++;
                    $sheet->setCellValue('A' . $row, isset($item['concepto']) ? $item['concepto'] : '');
                    $sheet->setCellValue('B' . $row, isset($item['cantidad']) ? $item['cantidad'] : '');
                    $sheet->setCellValue('C' . $row, isset($item['precio']) ? $item['precio'] : '');
                    $sheet->setCellValue('D' . $row, isset($item['total']) ? $item['total'] : '');
                }
            }

            $writer = new PhpOffice\PhpSpreadsheet\Writer\Ods($spreadsheet);
            $filename = 'Factura_' . $factura->folio . '_' . date('Y-m-d') . '.ods';
            
            ob_start();
            $writer->save('php://output');
            $contenido = ob_get_clean();

            return array(
                'success' => true,
                'contenido' => $contenido,
                'filename' => $filename
            );
        } catch (Exception $e) {
            return new WP_Error('ods_error', 'Error generando ODS: ' . $e->getMessage());
        }
    }

    private function exportar_zip_completo($factura) {
        try {
            // Crear archivo ZIP con todos los formatos
            require_once YAXTUN_ERP_PLUGIN_DIR . 'vendors/PHPZip/PHPZip.php';
            
            $zip = new ZipArchive();
            $temp_file = wp_tempnam('yaxtun_', '.zip');
            
            if ($zip->open($temp_file, ZipArchive::CREATE) !== true) {
                return new WP_Error('zip_error', 'No se pudo crear el archivo ZIP');
            }
            
            // Agregar todos los formatos
            foreach (array('PDF', 'XLSX', 'XML', 'CSV', 'JSON', 'HTML', 'ODS') as $formato) {
                $resultado = $this->exportar_factura($factura->id, $formato);
                if (isset($resultado['contenido'])) {
                    $zip->addFromString($resultado['filename'], $resultado['contenido']);
                }
            }
            
            $zip->close();
            
            $contenido = file_get_contents($temp_file);
            unlink($temp_file);
            
            $filename = 'Factura_' . $factura->folio . '_' . date('Y-m-d') . '_Completa.zip';
            
            return array(
                'success' => true,
                'contenido' => $contenido,
                'filename' => $filename
            );
        } catch (Exception $e) {
            return new WP_Error('zip_error', 'Error generando ZIP: ' . $e->getMessage());
        }
    }

    private function generar_html_factura($factura) {
        $html = '<html><head><meta charset="UTF-8"><style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }';
        $html .= '.total { font-weight: bold; font-size: 18px; }';
        $html .= '</style></head><body>';
        
        $html .= '<h1>FACTURA</h1>';
        $html .= '<p><strong>Folio:</strong> ' . esc_html($factura->folio) . '</p>';
        $html .= '<p><strong>Fecha:</strong> ' . esc_html($factura->fecha_emision) . '</p>';
        $html .= '<p><strong>Moneda:</strong> ' . esc_html($this->moneda) . '</p>';
        
        $html .= '<table>';
        $html .= '<tr><th>Concepto</th><th>Cantidad</th><th>Precio</th><th>Total</th></tr>';
        
        $datos = json_decode($factura->datos_json, true);
        if (is_array($datos)) {
            foreach ($datos as $item) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html(isset($item['concepto']) ? $item['concepto'] : '') . '</td>';
                $html .= '<td>' . esc_html(isset($item['cantidad']) ? $item['cantidad'] : '') . '</td>';
                $html .= '<td>' . esc_html(isset($item['precio']) ? $item['precio'] : '') . '</td>';
                $html .= '<td>' . esc_html(isset($item['total']) ? $item['total'] : '') . '</td>';
                $html .= '</tr>';
            }
        }
        
        $html .= '</table>';
        $html .= '<p class="total">Subtotal: ' . esc_html($factura->subtotal) . ' ' . esc_html($this->moneda) . '</p>';
        $html .= '<p class="total">IVA (16%): ' . esc_html($factura->iva) . ' ' . esc_html($this->moneda) . '</p>';
        $html .= '<p class="total">TOTAL: ' . esc_html($factura->total) . ' ' . esc_html($this->moneda) . '</p>';
        
        $html .= '</body></html>';
        
        return $html;
    }

    private function validar_datos_factura($datos) {
        $errores = array();
        
        if (empty($datos['cliente_id'])) {
            $errores[] = 'Cliente requerido';
        }
        
        if (empty($datos['subtotal']) || !is_numeric($datos['subtotal'])) {
            $errores[] = 'Subtotal inválido';
        }
        
        if (empty($datos['iva']) || !is_numeric($datos['iva'])) {
            $errores[] = 'IVA inválido';
        }
        
        if (empty($datos['total']) || !is_numeric($datos['total'])) {
            $errores[] = 'Total inválido';
        }
        
        if (!empty($errores)) {
            return new WP_Error('validacion_error', implode(', ', $errores));
        }
        
        return array(
            'cliente_id' => intval($datos['cliente_id']),
            'subtotal' => floatval($datos['subtotal']),
            'iva' => floatval($datos['iva']),
            'total' => floatval($datos['total']),
            'estado' => isset($datos['estado']) ? sanitize_text_field($datos['estado']) : 'borrador',
            'datos_json' => isset($datos['items']) ? wp_json_encode($datos['items']) : '{}'
        );
    }

    private function generar_folio() {
        global $wpdb;
        
        $year = date('Y');
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}yaxtun_facturas WHERE YEAR(fecha_emision) = %d",
                $year
            )
        );
        
        $folio = 'F' . $year . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        return $folio;
    }
}

?>