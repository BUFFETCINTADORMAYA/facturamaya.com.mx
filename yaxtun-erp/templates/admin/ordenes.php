<?php
/**
 * Template: Gestión de Órdenes de Trabajo
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="yaxtun-container">
    <div class="yaxtun-header">
        <h1>📋 Órdenes de Trabajo</h1>
        <a href="#" class="btn btn-success" onclick="nuevaOrden()">+ Nueva Orden</a>
    </div>

    <div class="ordenes-list">
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    global $wpdb;
                    $ordenes = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}yaxtun_ordenes WHERE user_id = %d ORDER BY fecha_creacion DESC",
                            get_current_user_id()
                        )
                    );

                    if (!empty($ordenes)):
                        foreach ($ordenes as $orden):
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($orden->numero_orden); ?></strong></td>
                        <td><?php echo esc_html(date('d/m/Y', strtotime($orden->fecha_creacion))); ?></td>
                        <td><?php echo esc_html('Cliente ' . $orden->cliente_id); ?></td>
                        <td><?php echo esc_html(substr($orden->descripcion, 0, 50)) . '...'; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $orden->estado; ?>">
                                <?php echo ucfirst(esc_html($orden->estado)); ?>
                            </span>
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info" onclick="editarOrden(<?php echo $orden->id; ?>)">Editar</a>
                            <a href="#" class="btn btn-sm btn-secondary" onclick="cerrarOrden(<?php echo $orden->id; ?>)">Cerrar</a>
                        </td>
                    </tr>
                <?php
                        endforeach;
                    else:
                        echo '<tr><td colspan="6" style="text-align: center;">No hay órdenes registradas</td></tr>';
                    endif;
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function nuevaOrden() {
    alert('Funcionalidad de nueva orden en desarrollo');
}

function editarOrden(id) {
    alert('Editar orden ' + id);
}

function cerrarOrden(id) {
    if (confirm('¿Deseas cerrar esta orden?')) {
        alert('Orden cerrada');
    }
}
</script>