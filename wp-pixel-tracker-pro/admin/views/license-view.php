<?php
/**
 * License Activation View for WP Pixel & Tracking Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_activated = get_option('wp_pixel_tracker_is_activated');
$saved_key = get_option('wp_pixel_tracker_activated_key', '');
?>

<div class="wpps-wrap">
    <div class="wpps-header">
        <div class="wpps-header-title">
            <h2><span class="dashicons dashicons-key"></span> Licença & Ativação</h2>
            <p>Insira sua Chave de Ativação Exclusiva para desbloquear o disparo de pixels e criador de eventos.</p>
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
                    <p class="text-muted">Seu plugin WP Pixel & Tracking Pro está totalmente ativado e pronto para disparar eventos.</p>
                    <div class="wpps-form-group mt-3" style="max-width: 400px; margin: 0 auto;">
                        <label>Chave em Uso:</label>
                        <input type="text" readonly value="<?php echo esc_attr($saved_key); ?>" class="wpps-input-text text-center font-mono" />
                    </div>
                <?php else: ?>
                    <div class="wpps-status-badge wpps-status-inactive mb-3">
                        <span class="dashicons dashicons-warning"></span> ATIVAÇÃO PENDENTE
                    </div>
                    <p class="text-muted">Por favor, cole sua Chave de Ativação Exclusiva abaixo para ativar todas as funcionalidades do plugin.</p>

                    <form id="wpps-license-form" class="mt-4" style="max-width: 440px; margin: 0 auto;">
                        <div class="wpps-form-group">
                            <label for="wpps-license-key-input">Chave de Ativação (License Key):</label>
                            <input type="text" id="wpps-license-key-input" name="license_key" placeholder="PIXELTRACKER-PRO-XXXX-XXXX-XXXX" required class="wpps-input-text text-center font-mono" style="font-size: 15px; font-weight: 700; letter-spacing: 1px;" />
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
