<?php
/**
 * License Activation View for WP Pixel & Tracking Pro (WooCommerce Software License Manager)
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_activated = get_option('wp_pixel_tracker_is_activated');
$saved_key = get_option('wp_pixel_tracker_activated_key', '');
$license_status = get_option('wp_pixel_tracker_license_status', 'inactive');
$current_domain = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field($_SERVER['SERVER_NAME']) : '';
?>

<div class="wpps-wrap">
    <div class="wpps-header">
        <div class="wpps-header-title">
            <h2><span class="dashicons dashicons-key"></span> Licença & Ativação</h2>
            <p>Gerenciamento de licença individual via WooCommerce para o WP Pixel & Tracking Pro.</p>
        </div>
    </div>

    <div class="wpps-box" style="max-width: 600px; margin: 0 auto;">
        <div class="wpps-box-header">
            <h3><span class="dashicons dashicons-shield"></span> Status da Licença</h3>
        </div>
        <div class="wpps-box-body">
            <div class="wpps-license-status-box text-center py-4">
                <?php if ($is_activated): ?>
                    <div class="wpps-status-badge wpps-status-active mb-3">
                        <span class="dashicons dashicons-yes-alt"></span> LICENÇA ATIVA E VERIFICADA
                    </div>
                    <p class="text-muted">Seu plugin WP Pixel & Tracking Pro está validado pelo servidor WooCommerce e liberado para este domínio.</p>

                    <div class="wpps-form-group mt-3" style="max-width: 440px; margin: 0 auto; text-align: left;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Domínio Registrado:</label>
                        <input type="text" readonly value="<?php echo esc_attr($current_domain); ?>" class="wpps-input-text font-mono" style="background: #f8fafc; font-size: 13px;" />
                    </div>

                    <div class="wpps-form-group mt-3" style="max-width: 440px; margin: 0 auto; text-align: left;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Chave em Uso:</label>
                        <input type="text" readonly value="<?php echo esc_attr($saved_key); ?>" class="wpps-input-text font-mono text-center" style="background: #f8fafc; font-weight: 700; font-size: 14px; letter-spacing: 1px;" />
                    </div>

                    <div class="mt-4 pt-2" style="border-top: 1px solid #e2e8f0; max-width: 440px; margin-left: auto; margin-right: auto;">
                        <button type="button" id="wpps-deactivate-btn" class="wpps-btn wpps-btn-danger w-100" style="background: #ef4444; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                            <span class="dashicons dashicons-dismiss" style="vertical-align: middle; margin-right: 4px;"></span> Desativar Licença neste Domínio
                        </button>
                        <p class="text-muted mt-2" style="font-size: 12px;">Deseja transferir sua licença para outro site? Clique em desativar para liberar a vaga no WooCommerce.</p>
                    </div>

                <?php else: ?>
                    <?php if ('expired' === $license_status): ?>
                        <div class="wpps-status-badge wpps-status-inactive mb-3" style="background: #fee2e2; color: #991b1b;">
                            <span class="dashicons dashicons-clock"></span> LICENÇA EXPIRADA
                        </div>
                        <p class="text-muted">Sua assinatura ou período de licença expirou no WooCommerce. Por favor, renove sua licença para continuar disparando pixels.</p>
                    <?php else: ?>
                        <div class="wpps-status-badge wpps-status-inactive mb-3">
                            <span class="dashicons dashicons-warning"></span> ATIVAÇÃO PENDENTE
                        </div>
                        <p class="text-muted">Por favor, cole sua Chave de Licença individual gerada pelo WooCommerce para ativar o rastreamento.</p>
                    <?php endif; ?>

                    <form id="wpps-license-form" class="mt-4" style="max-width: 440px; margin: 0 auto;">
                        <div class="wpps-form-group">
                            <label for="wpps-license-key-input" style="font-weight: 600; display: block; margin-bottom: 6px; text-align: left;">Chave de Licença (WooCommerce):</label>
                            <input type="text" id="wpps-license-key-input" name="license_key" placeholder="Ex: 6a3e94dca1d0c5..." required class="wpps-input-text text-center font-mono" style="font-size: 14px; font-weight: 700; letter-spacing: 1px;" autocomplete="off" />
                        </div>
                        <button type="submit" id="wpps-activate-btn" class="wpps-btn wpps-btn-primary wpps-btn-lg w-100 mt-3">
                            <span class="dashicons dashicons-lock"></span> Validar e Ativar Plugin
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
