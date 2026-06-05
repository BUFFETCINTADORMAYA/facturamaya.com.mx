<?php
/**
 * Template: Dashboard Principal
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="yaxtun-container">
    <div class="yaxtun-header">
        <h1>📊 Dashboard Yaxtun ERP</h1>
        <p>Bienvenido al sistema de gestión integral</p>
    </div>

    <div class="dashboard-grid">
        <!-- Tarjeta Facturas -->
        <div class="dashboard-card">
            <div class="card-icon">📄</div>
            <h3>Facturas</h3>
            <div class="card-stats">
                <?php
                    global $wpdb;
                    $total_facturas = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}yaxtun_facturas WHERE user_id = %d",
                            get_current_user_id()
                        )
                    );
                    echo '<p class="stat-number">' . intval($total_facturas) . '</p>';
                    echo '<p class="stat-label">Total de facturas</p>';
                ?>
            </div>
            <a href="<?php echo admin_url('admin.php?page=yaxtun-erp-facturas'); ?>" class="btn btn-primary">Administrar</a>
        </div>

        <!-- Tarjeta Órdenes -->
        <div class="dashboard-card">
            <div class="card-icon">📋</div>
            <h3>Órdenes de Trabajo</h3>
            <div class="card-stats">
                <?php
                    $total_ordenes = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}yaxtun_ordenes WHERE user_id = %d AND estado = 'abierta'",
                            get_current_user_id()
                        )
                    );
                    echo '<p class="stat-number">' . intval($total_ordenes) . '</p>';
                    echo '<p class="stat-label">Órdenes abiertas</p>';
                ?>
            </div>
            <a href="<?php echo admin_url('admin.php?page=yaxtun-erp-ordenes'); ?>" class="btn btn-primary">Administrar</a>
        </div>

        <!-- Tarjeta IA Assistant -->
        <div class="dashboard-card">
            <div class="card-icon">🤖</div>
            <h3>Asistente IA</h3>
            <div class="card-stats">
                <p class="stat-label">Claude, Anthropic, Gemini</p>
                <p class="stat-subtext">Conversacional y seguro</p>
            </div>
            <a href="<?php echo admin_url('admin.php?page=yaxtun-erp-ia'); ?>" class="btn btn-primary">Usar IA</a>
        </div>

        <!-- Tarjeta Configuración -->
        <div class="dashboard-card">
            <div class="card-icon">⚙️</div>
            <h3>Configuración</h3>
            <div class="card-stats">
                <p class="stat-label">Ajustes del sistema</p>
                <p class="stat-subtext">API Keys, empresa, permisos</p>
            </div>
            <?php if (current_user_can('manage_options')): ?>
                <a href="<?php echo admin_url('admin.php?page=yaxtun-erp-settings'); ?>" class="btn btn-primary">Configurar</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="dashboard-charts" style="margin-top: 40px;">
        <div class="chart-container">
            <h3>Facturación del Mes</h3>
            <canvas id="chartFacturacion"></canvas>
        </div>
        <div class="chart-container">
            <h3>Órdenes por Estado</h3>
            <canvas id="chartOrdenes"></canvas>
        </div>
    </div>

    <!-- Sesión del Usuario -->
    <div class="user-session" style="margin-top: 30px;">
        <p>Usuario: <strong><?php echo esc_html(wp_get_current_user()->display_name); ?></strong></p>
        <p>Rol: <strong><?php echo esc_html(implode(', ', wp_get_current_user()->roles)); ?></strong></p>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-danger">Cerrar Sesión</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Facturación
    var ctx1 = document.getElementById('chartFacturacion').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
            datasets: [{
                label: 'Ingresos (MXN)',
                data: [12000, 19000, 15000, 22000],
                borderColor: '#0066cc',
                backgroundColor: 'rgba(0, 102, 204, 0.1)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // Gráfico de Órdenes
    var ctx2 = document.getElementById('chartOrdenes').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Abiertas', 'En Proceso', 'Cerradas'],
            datasets: [{
                data: [5, 8, 12],
                backgroundColor: [
                    '#ff9800',
                    '#2196F3',
                    '#4CAF50'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>