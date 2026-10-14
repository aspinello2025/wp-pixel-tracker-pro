<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Software License Manager (SLM) Integration for WooCommerce
 * Conecta o WP Pixel & Tracking Pro ao servidor de licenças WooCommerce
 */

// Software License Manager Configurations
if (!defined('WP_PIXEL_TRACKER_SLM_SECRET_KEY')) {
    define('WP_PIXEL_TRACKER_SLM_SECRET_KEY', 'LHE_SLM_6a3e94dca1d0c5.76166265'); // Chave secreta SLM do WooCommerce
}
if (!defined('WP_PIXEL_TRACKER_SLM_SERVER_URL')) {
    define('WP_PIXEL_TRACKER_SLM_SERVER_URL', 'https://agenciaintegrar.com');     // URL da sua loja com WooCommerce + SLM
}
if (!defined('WP_PIXEL_TRACKER_SLM_ITEM_REFERENCE')) {
    define('WP_PIXEL_TRACKER_SLM_ITEM_REFERENCE', 'WP Pixel & Tracking Pro');   // Referência exata do produto no SLM
}
if (!defined('WP_PIXEL_TRACKER_MASTER_BYPASS_KEY')) {
    define('WP_PIXEL_TRACKER_MASTER_BYPASS_KEY', 'PIXEL-MASTER-DEV-UNLIMITED-9999'); // Chave master de desenvolvedor
}

// AJAX handler to activate license
add_action('wp_ajax_wpps_activate_license', 'wpps_activate_license_handler');
function wpps_activate_license_handler() {
    // Check security nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpps_admin_nonce')) {
        wp_send_json_error(array('message' => 'Falha na verificação de segurança (nonce inválido).'));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permissão insuficiente.'));
    }

    $license_key = isset($_POST['license_key']) ? sanitize_text_field(trim($_POST['license_key'])) : '';

    if (empty($license_key)) {
        wp_send_json_error(array('message' => 'A chave de licença não pode estar vazia.'));
    }

    // Check for Master Developer Bypass key
    if (defined('WP_PIXEL_TRACKER_MASTER_BYPASS_KEY') && !empty(WP_PIXEL_TRACKER_MASTER_BYPASS_KEY) && $license_key === WP_PIXEL_TRACKER_MASTER_BYPASS_KEY) {
        update_option('wp_pixel_tracker_is_activated', 1);
        update_option('wp_pixel_tracker_activated_key', $license_key);
        update_option('wp_pixel_tracker_license_status', 'active');
        delete_transient('wpps_license_check_lock');
        wp_send_json_success(array('message' => 'Licença Master de Desenvolvedor ativada com sucesso! Sem limites de domínio e sem expiração.'));
    }

    // Determine domain for SLM activation
    if (is_multisite()) {
        $domain = site_url();
    } else {
        $domain = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field($_SERVER['SERVER_NAME']) : '';
    }

    // API query parameters
    $api_params = array(
        'slm_action'        => 'slm_activate',
        'secret_key'        => WP_PIXEL_TRACKER_SLM_SECRET_KEY,
        'license_key'       => $license_key,
        'registered_domain' => $domain,
        'item_reference'    => urlencode(WP_PIXEL_TRACKER_SLM_ITEM_REFERENCE),
    );

    // Send query to the license manager server
    $url = add_query_arg($api_params, WP_PIXEL_TRACKER_SLM_SERVER_URL);
    $response = wp_remote_get($url, array('timeout' => 20, 'sslverify' => false));

    // Check for connection error
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Falha de conexão com o servidor de licenças WooCommerce: ' . $response->get_error_message()));
    }

    // Decode response
    $license_data = json_decode(wp_remote_retrieve_body($response));

    if (!$license_data) {
        wp_send_json_error(array('message' => 'Resposta inválida do servidor de licenças.'));
    }

    // Check response outcome
    if (isset($license_data->result) && 'success' === $license_data->result) {
        update_option('wp_pixel_tracker_is_activated', 1);
        update_option('wp_pixel_tracker_activated_key', $license_key);
        update_option('wp_pixel_tracker_license_status', 'active');
        delete_transient('wpps_license_check_lock'); // Clear cache lock
        
        $message = !empty($license_data->message) ? $license_data->message : 'Sua licença foi ativada com sucesso! O WP Pixel & Tracking Pro agora está ativo.';
        wp_send_json_success(array('message' => $message));
    } else {
        $message = !empty($license_data->message) ? $license_data->message : 'Chave de licença inválida. Por favor, verifique a chave recebida no WooCommerce.';
        wp_send_json_error(array('message' => $message));
    }
}

// AJAX handler to deactivate license
add_action('wp_ajax_wpps_deactivate_license', 'wpps_deactivate_license_handler');
function wpps_deactivate_license_handler() {
    // Check security nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpps_admin_nonce')) {
        wp_send_json_error(array('message' => 'Falha na verificação de segurança (nonce inválido).'));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permissão insuficiente.'));
    }

    $license_key = get_option('wp_pixel_tracker_activated_key', '');

    if (!empty($license_key)) {
        if (is_multisite()) {
            $domain = site_url();
        } else {
            $domain = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field($_SERVER['SERVER_NAME']) : '';
        }

        // API parameters to deactivate
        $api_params = array(
            'slm_action'        => 'slm_deactivate',
            'secret_key'        => WP_PIXEL_TRACKER_SLM_SECRET_KEY,
            'license_key'       => $license_key,
            'registered_domain' => $domain,
            'item_reference'    => urlencode(WP_PIXEL_TRACKER_SLM_ITEM_REFERENCE),
        );

        $url = add_query_arg($api_params, WP_PIXEL_TRACKER_SLM_SERVER_URL);
        wp_remote_get($url, array('timeout' => 20, 'sslverify' => false)); // Best effort
    }

    // Always clear locally
    update_option('wp_pixel_tracker_is_activated', 0);
    delete_option('wp_pixel_tracker_activated_key');
    update_option('wp_pixel_tracker_license_status', 'inactive');
    delete_transient('wpps_license_check_lock');
    
    wp_send_json_success(array('message' => 'Licença desativada com sucesso deste domínio!'));
}

/**
 * Periodically verifies the license key status with the license server.
 * Uses a transient lock to limit API hits to once every 24 hours.
 */
function wpps_check_license_status($force = false) {
    $license_key = get_option('wp_pixel_tracker_activated_key', '');

    // Bypass verification if it is the Master Key
    if (defined('WP_PIXEL_TRACKER_MASTER_BYPASS_KEY') && !empty(WP_PIXEL_TRACKER_MASTER_BYPASS_KEY) && $license_key === WP_PIXEL_TRACKER_MASTER_BYPASS_KEY) {
        update_option('wp_pixel_tracker_is_activated', 1);
        update_option('wp_pixel_tracker_license_status', 'active');
        return;
    }

    if (empty($license_key)) {
        update_option('wp_pixel_tracker_is_activated', 0);
        update_option('wp_pixel_tracker_license_status', 'inactive');
        return;
    }

    // Verify transient lock to prevent server overhead (unless forced)
    if (!$force && get_transient('wpps_license_check_lock')) {
        return;
    }

    // Determine domain
    if (is_multisite()) {
        $domain = site_url();
    } else {
        $domain = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field($_SERVER['SERVER_NAME']) : '';
    }

    // Check parameters
    $api_params = array(
        'slm_action'        => 'slm_check',
        'secret_key'        => WP_PIXEL_TRACKER_SLM_SECRET_KEY,
        'license_key'       => $license_key,
        'registered_domain' => $domain,
        'item_reference'    => urlencode(WP_PIXEL_TRACKER_SLM_ITEM_REFERENCE),
    );

    $url = add_query_arg($api_params, WP_PIXEL_TRACKER_SLM_SERVER_URL);
    $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));

    if (!is_wp_error($response)) {
        $license_data = json_decode(wp_remote_retrieve_body($response));
        if ($license_data) {
            if (isset($license_data->result) && 'success' === $license_data->result) {
                $status = isset($license_data->status) ? $license_data->status : '';
                if ('active' === $status) {
                    update_option('wp_pixel_tracker_is_activated', 1);
                    update_option('wp_pixel_tracker_license_status', 'active');
                } elseif ('expired' === $status) {
                    update_option('wp_pixel_tracker_is_activated', 0);
                    update_option('wp_pixel_tracker_license_status', 'expired');
                } else {
                    update_option('wp_pixel_tracker_is_activated', 0);
                    update_option('wp_pixel_tracker_license_status', 'inactive');
                }
            } else {
                // If license key was deleted or blocked on server
                update_option('wp_pixel_tracker_is_activated', 0);
                update_option('wp_pixel_tracker_license_status', 'inactive');
            }
        }
    }

    // Set lock transient for 24 hours
    set_transient('wpps_license_check_lock', true, DAY_IN_SECONDS);
}

// Hook check on admin_init for periodic verification
add_action('admin_init', 'wpps_check_license_on_admin');
function wpps_check_license_on_admin() {
    if (current_user_can('manage_options')) {
        wpps_check_license_status();
    }
}
