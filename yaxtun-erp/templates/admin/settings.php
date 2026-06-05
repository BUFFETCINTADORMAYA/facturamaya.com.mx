<?php
/**
 * Template: Configuración del Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="yaxtun-container">
    <div class="yaxtun-header">
        <h1>⚙️ Configuración Yaxtun ERP</h1>
    </div>

    <div class="settings-form">
        <form method="POST" class="yaxtun-form">
            <div class="settings-section">
                <h3>🏢 Datos de la Empresa</h3>
                <div class="form-group">
                    <label>Nombre de la Empresa</label>
                    <input type="text" class="form-control" name="nombre_empresa" value="<?php echo esc_attr(get_option('yaxtun_nombre_empresa')); ?>">
                </div>
                <div class="form-group">
                    <label>RFC</label>
                    <input type="text" class="form-control" name="rfc_empresa" value="<?php echo esc_attr(get_option('yaxtun_rfc_empresa')); ?>" placeholder="XXX000000XXX">
                </div>
                <div class="form-group">
                    <label>Domicilio</label>
                    <textarea class="form-control" name="domicilio_empresa"><?php echo esc_textarea(get_option('yaxtun_domicilio_empresa')); ?></textarea>
                </div>
            </div>

            <div class="settings-section">
                <h3>🔑 API Keys - Proveedores IA</h3>
                
                <div class="api-provider">
                    <h4>Claude / Anthropic</h4>
                    <div class="form-group">
                        <label>API Key de Claude</label>
                        <input type="password" class="form-control" name="claude_api_key" value="<?php echo esc_attr(get_option('yaxtun_claude_api_key')); ?>" placeholder="sk-ant-...">
                        <small>Obtén tu clave en: https://console.anthropic.com/</small>
                    </div>
                </div>

                <div class="api-provider">
                    <h4>Google Gemini</h4>
                    <div class="form-group">
                        <label>API Key de Gemini</label>
                        <input type="password" class="form-control" name="gemini_api_key" value="<?php echo esc_attr(get_option('yaxtun_gemini_api_key')); ?>" placeholder="AIzaSy...">
                        <small>Obtén tu clave en: https://makersuite.google.com/</small>
                    </div>
                </div>

                <div class="api-provider">
                    <h4>OpenAI (Whisper - Transcripción de Voz)</h4>
                    <div class="form-group">
                        <label>API Key de OpenAI</label>
                        <input type="password" class="form-control" name="openai_api_key" value="<?php echo esc_attr(get_option('yaxtun_openai_key')); ?>" placeholder="sk-...">
                        <small>Obtén tu clave en: https://platform.openai.com/</small>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h3>🔐 Permisos y Seguridad</h3>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="permitir_clientes_ia" <?php checked(get_option('yaxtun_permitir_clientes_ia'), '1'); ?>>
                        Permitir que los clientes usen IA
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="permitir_tecnicos_ia" <?php checked(get_option('yaxtun_permitir_tecnicos_ia'), '1'); ?>>
                        Permitir que los técnicos usen IA
                    </label>
                </div>
                <div class="form-group">
                    <label>Restricción de IA para Clientes</label>
                    <select class="form-control" name="restriccion_ia_clientes">
                        <option value="ninguna">Sin restricciones</option>
                        <option value="propia_empresa">Solo datos de su empresa</option>
                        <option value="estricta">Solo consultas generales</option>
                    </select>
                </div>
            </div>

            <div class="settings-section">
                <h3>📊 Configuración de Facturas</h3>
                <div class="form-group">
                    <label>Moneda (para facturas)</label>
                    <select class="form-control" name="moneda">
                        <option value="MXN" selected>MXN - Pesos Mexicanos</option>
                        <option value="USD">USD - Dólares Estadounidenses</option>
                        <option value="EUR">EUR - Euros</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>IVA por defecto (%)</label>
                    <input type="number" class="form-control" name="iva_default" value="<?php echo esc_attr(get_option('yaxtun_iva_default', '16')); ?>" step="0.01" min="0" max="100">
                </div>
            </div>

            <div class="settings-section">
                <h3>🌐 Compatibilidad Hostinger</h3>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="modo_compatible_hostinger" <?php checked(get_option('yaxtun_modo_compatible_hostinger'), '1'); ?>>
                        Activar modo compatible (optimiza para Hostinger)
                    </label>
                </div>
                <div class="form-group">
                    <label>Límite de memoria (MB)</label>
                    <input type="number" class="form-control" name="memory_limit" value="<?php echo esc_attr(get_option('yaxtun_memory_limit', '256')); ?>" min="64" max="512">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Configuración</button>
            </div>
        </form>
    </div>
</div>

<style>
.settings-section {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.api-provider {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.api-provider:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}
</style>