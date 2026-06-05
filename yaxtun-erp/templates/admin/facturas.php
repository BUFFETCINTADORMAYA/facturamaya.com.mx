<?php
/**
 * Template: Gestión de Facturas
 */

if (!defined('ABSPATH')) {
    exit;
}

$factura_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'listar';

?>

<div class="yaxtun-container">
    <div class="yaxtun-header">
        <h1>📄 Facturas</h1>
        <a href="<?php echo admin_url('admin.php?page=yaxtun-erp-facturas&action=nueva'); ?>" class="btn btn-success">+ Nueva Factura</a>
    </div>

    <?php if ($action === 'listar'): ?>
        <!-- Listado de Facturas -->
        <div class="facturas-list">
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>Total (MXN)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        global $wpdb;
                        $facturas = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT * FROM {$wpdb->prefix}yaxtun_facturas WHERE user_id = %d ORDER BY fecha_emision DESC",
                                get_current_user_id()
                            )
                        );

                        if (!empty($facturas)):
                            foreach ($facturas as $factura):
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html($factura->folio); ?></strong></td>
                            <td><?php echo esc_html(date('d/m/Y', strtotime($factura->fecha_emision))); ?></td>
                            <td><?php echo esc_html('Cliente ' . $factura->cliente_id); ?></td>
                            <td>$<?php echo number_format($factura->subtotal, 2); ?></td>
                            <td>$<?php echo number_format($factura->iva, 2); ?></td>
                            <td><strong>$<?php echo number_format($factura->total, 2); ?></strong></td>
                            <td>
                                <span class="badge badge-<?php echo $factura->estado; ?>">
                                    <?php echo ucfirst(esc_html($factura->estado)); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=yaxtun-erp-facturas&action=editar&id=' . $factura->id); ?>" class="btn btn-sm btn-info">Editar</a>
                                <div class="dropdown" style="display: inline-block;">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">Exportar</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#" onclick="exportar(<?php echo $factura->id; ?>, 'PDF')">PDF</a>
                                        <a class="dropdown-item" href="#" onclick="exportar(<?php echo $factura->id; ?>, 'XLSX')">Excel</a>
                                        <a class="dropdown-item" href="#" onclick="exportar(<?php echo $factura->id; ?>, 'XML')">XML (SAT)</a>
                                        <a class="dropdown-item" href="#" onclick="exportar(<?php echo $factura->id; ?>, 'CSV')">CSV</a>
                                        <a class="dropdown-item" href="#" onclick="exportar(<?php echo $factura->id; ?>, 'ZIP')">ZIP Completo</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php
                            endforeach;
                        else:
                            echo '<tr><td colspan="8" style="text-align: center;">No hay facturas registradas</td></tr>';
                        endif;
                    ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($action === 'nueva' || $action === 'editar'): ?>
        <!-- Formulario Nueva/Editar Factura -->
        <div class="factura-form">
            <?php
                $factura = null;
                if ($action === 'editar' && $factura_id):
                    global $wpdb;
                    $factura = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}yaxtun_facturas WHERE id = %d",
                            $factura_id
                        )
                    );
                endif;
            ?>

            <form method="POST" id="formulario-factura" class="yaxtun-form">
                <div class="form-section">
                    <h3>📋 Datos de la Factura</h3>
                    
                    <?php if ($factura): ?>
                        <div class="form-group">
                            <label>Folio</label>
                            <input type="text" class="form-control" value="<?php echo esc_attr($factura->folio); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Emisión</label>
                            <input type="date" class="form-control" name="fecha_emision" value="<?php echo esc_attr($factura->fecha_emision); ?>" required>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label>Fecha de Emisión</label>
                            <input type="date" class="form-control" name="fecha_emision" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Cliente</label>
                        <select class="form-control" name="cliente_id" required>
                            <option value="">-- Seleccionar Cliente --</option>
                            <?php
                                global $wpdb;
                                $clientes = $wpdb->get_results(
                                    $wpdb->prepare(
                                        "SELECT * FROM {$wpdb->prefix}yaxtun_clientes WHERE user_id = %d",
                                        get_current_user_id()
                                    )
                                );
                                foreach ($clientes as $cliente):
                                    $selected = ($factura && $factura->cliente_id == $cliente->id) ? 'selected' : '';
                                    echo '<option value="' . $cliente->id . '" ' . $selected . '>' . esc_html($cliente->nombre) . '</option>';
                                endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3>💰 Detalles de Facturación</h3>
                    <div class="items-list" id="items-list">
                        <?php
                            $items = $factura ? json_decode($factura->datos_json, true) : array();
                            if (!empty($items)):
                                foreach ($items as $index => $item):
                        ?>
                            <div class="item-row" data-index="<?php echo $index; ?>">
                                <input type="text" placeholder="Concepto" class="form-control" name="items[<?php echo $index; ?>][concepto]" value="<?php echo esc_attr($item['concepto'] ?? ''); ?>" required>
                                <input type="number" placeholder="Cantidad" class="form-control" name="items[<?php echo $index; ?>][cantidad]" value="<?php echo esc_attr($item['cantidad'] ?? 1); ?>" required>
                                <input type="number" placeholder="Precio" class="form-control" name="items[<?php echo $index; ?>][precio]" value="<?php echo esc_attr($item['precio'] ?? 0); ?>" step="0.01" required>
                                <input type="number" placeholder="Total" class="form-control" name="items[<?php echo $index; ?>][total]" value="<?php echo esc_attr($item['total'] ?? 0); ?>" step="0.01" readonly>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removerItem(this)">Quitar</button>
                            </div>
                        <?php
                                endforeach;
                            endif;
                        ?>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="agregarItem()">+ Agregar Línea</button>
                </div>

                <div class="form-section">
                    <h3>📊 Totales</h3>
                    <div class="totales-grid">
                        <div class="form-group">
                            <label>Subtotal (MXN)</label>
                            <input type="number" class="form-control" id="subtotal" name="subtotal" value="<?php echo $factura ? esc_attr($factura->subtotal) : 0; ?>" step="0.01" readonly>
                        </div>
                        <div class="form-group">
                            <label>IVA 16% (MXN)</label>
                            <input type="number" class="form-control" id="iva" name="iva" value="<?php echo $factura ? esc_attr($factura->iva) : 0; ?>" step="0.01" readonly>
                        </div>
                        <div class="form-group">
                            <label><strong>TOTAL (MXN)</strong></label>
                            <input type="number" class="form-control total-input" id="total" name="total" value="<?php echo $factura ? esc_attr($factura->total) : 0; ?>" step="0.01" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select class="form-control" name="estado">
                        <option value="borrador" <?php echo ($factura && $factura->estado === 'borrador') ? 'selected' : ''; ?>>Borrador</option>
                        <option value="enviada" <?php echo ($factura && $factura->estado === 'enviada') ? 'selected' : ''; ?>>Enviada</option>
                        <option value="pagada" <?php echo ($factura && $factura->estado === 'pagada') ? 'selected' : ''; ?>>Pagada</option>
                        <option value="cancelada" <?php echo ($factura && $factura->estado === 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Factura</button>
                    <a href="<?php echo admin_url('admin.php?page=yaxtun-erp-facturas'); ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
function agregarItem() {
    const itemsList = document.getElementById('items-list');
    const index = itemsList.children.length;
    const html = `
        <div class="item-row" data-index="${index}">
            <input type="text" placeholder="Concepto" class="form-control" name="items[${index}][concepto]" required>
            <input type="number" placeholder="Cantidad" class="form-control" name="items[${index}][cantidad]" value="1" required>
            <input type="number" placeholder="Precio" class="form-control" name="items[${index}][precio]" value="0" step="0.01" required>
            <input type="number" placeholder="Total" class="form-control" name="items[${index}][total]" value="0" step="0.01" readonly>
            <button type="button" class="btn btn-danger btn-sm" onclick="removerItem(this)">Quitar</button>
        </div>
    `;
    itemsList.insertAdjacentHTML('beforeend', html);
    calcularTotales();
}

function removerItem(btn) {
    btn.closest('.item-row').remove();
    calcularTotales();
}

function calcularTotales() {
    const items = document.querySelectorAll('.item-row');
    let subtotal = 0;

    items.forEach(item => {
        const cantidad = parseFloat(item.querySelector('input[name*="[cantidad]"]').value) || 0;
        const precio = parseFloat(item.querySelector('input[name*="[precio]"]').value) || 0;
        const total = cantidad * precio;
        item.querySelector('input[name*="[total]"]').value = total.toFixed(2);
        subtotal += total;
    });

    const iva = subtotal * 0.16;
    const total = subtotal + iva;

    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('iva').value = iva.toFixed(2);
    document.getElementById('total').value = total.toFixed(2);
}

document.addEventListener('change', function(e) {
    if (e.target.matches('.item-row input[type="number"]')) {
        calcularTotales();
    }
});

function exportar(facturaId, formato) {
    window.location.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=yaxtun_exportar_factura&factura_id=' + facturaId + '&formato=' + formato + '&nonce=<?php echo wp_create_nonce('yaxtun_export'); ?>';
}
</script>