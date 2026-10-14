<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Universal GitHub Automatic Plugin Updater for WP Pixel & Tracking Pro
 */

// Hook to check for plugin updates from GitHub raw content
add_filter('pre_set_site_transient_update_plugins', 'wpps_check_github_plugin_update');
add_filter('site_transient_update_plugins', 'wpps_check_github_plugin_update');

function wpps_check_github_plugin_update($transient) {
    if (!is_object($transient)) {
        $transient = new stdClass();
    }

    if (!isset($transient->response) || !is_array($transient->response)) {
        $transient->response = array();
    }

    if (!isset($transient->no_update) || !is_array($transient->no_update)) {
        $transient->no_update = array();
    }

    // GitHub Configurations (ajuste conforme o seu usuário e nome do repositório no GitHub)
    $github_user   = 'aspinello2025';
    $github_repo   = 'wp-pixel-tracker-pro';
    $github_branch = 'main';

    // Raw URL pointing to the info.json on your GitHub repository with cache buster
    $remote_url = sprintf('https://raw.githubusercontent.com/%s/%s/%s/info.json?t=%d', $github_user, $github_repo, $github_branch, time());

    $response = wp_remote_get($remote_url, array(
        'timeout'   => 15,
        'sslverify' => false,
        'headers'   => array(
            'Accept' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        return $transient;
    }

    $body = wp_remote_retrieve_body($response);
    $info = json_decode($body);

    if (!$info || !is_object($info) || empty($info->version)) {
        return $transient;
    }

    // Determine the actual basename and version
    $plugin_file   = defined('WP_PIXEL_TRACKER_BASENAME') ? WP_PIXEL_TRACKER_BASENAME : plugin_basename(WP_PIXEL_TRACKER_DIR . 'wp-pixel-tracker-pro.php');
    $installed_ver = defined('WP_PIXEL_TRACKER_VERSION') ? WP_PIXEL_TRACKER_VERSION : '1.0.0';

    $obj              = new stdClass();
    $obj->id          = 'https://github.com/' . $github_user . '/' . $github_repo;
    $obj->slug        = 'wp-pixel-tracker-pro';
    $obj->plugin      = $plugin_file;
    $obj->new_version = $info->version;
    $obj->url         = !empty($info->url) ? $info->url : 'https://agenciaintegrar.com/loja/';

    // Add version as cache buster so GitHub CDN always serves the freshest zip
    $base_package     = !empty($info->download_url) ? $info->download_url : sprintf('https://raw.githubusercontent.com/%s/%s/%s/wp-pixel-tracker-pro.zip', $github_user, $github_repo, $github_branch);
    $obj->package     = add_query_arg('v', $info->version, $base_package);

    // Compare versions: if GitHub has a newer version, set in $transient->response
    if (version_compare($installed_ver, $info->version, '<')) {
        $transient->response[$plugin_file] = $obj;
    } else {
        $transient->no_update[$plugin_file] = $obj;
    }

    return $transient;
}

// Hook to display plugin details modal window in the plugin dashboard
add_filter('plugins_api', 'wpps_github_plugin_popup_details', 20, 3);
function wpps_github_plugin_popup_details($res, $action, $args) {
    if ('plugin_information' !== $action) {
        return $res;
    }

    if (empty($args->slug) || 'wp-pixel-tracker-pro' !== $args->slug) {
        return $res;
    }

    $github_user   = 'aspinello2025';
    $github_repo   = 'wp-pixel-tracker-pro';
    $github_branch = 'main';
    $remote_url    = sprintf('https://raw.githubusercontent.com/%s/%s/%s/info.json?t=%d', $github_user, $github_repo, $github_branch, time());

    $response = wp_remote_get($remote_url, array(
        'timeout'   => 15,
        'sslverify' => false
    ));

    if (is_wp_error($response)) {
        return $res;
    }

    $info = json_decode(wp_remote_retrieve_body($response));
    if (!$info || !is_object($info)) {
        return $res;
    }

    $res                = new stdClass();
    $res->name          = isset($info->name) ? $info->name : 'WP Pixel & Tracking Pro';
    $res->slug          = 'wp-pixel-tracker-pro';
    $res->version       = isset($info->version) ? $info->version : '1.7.0';
    $res->author        = '<a href="https://agenciaintegrar.com/loja/">Alexandre Spinello</a>';
    $res->homepage      = !empty($info->url) ? $info->url : 'https://agenciaintegrar.com/loja/';
    
    // Add version as cache buster so GitHub CDN always serves the freshest zip
    $base_download_link = !empty($info->download_url) ? $info->download_url : sprintf('https://raw.githubusercontent.com/%s/%s/%s/wp-pixel-tracker-pro.zip', $github_user, $github_repo, $github_branch);
    $res->download_link = add_query_arg('v', $res->version, $base_download_link);

    $res->sections = array(
        'description' => isset($info->sections->description) ? $info->sections->description : 'Gerenciador de Pixels profissional para WordPress e WooCommerce.',
        'changelog'   => isset($info->sections->changelog) ? $info->sections->changelog : 'Novas melhorias e correções da versão.'
    );

    return $res;
}

// Add check for updates link in plugins list
add_filter('plugin_row_meta', 'wpps_plugin_row_meta_links', 10, 2);
function wpps_plugin_row_meta_links($links, $file) {
    $current_file = defined('WP_PIXEL_TRACKER_BASENAME') ? WP_PIXEL_TRACKER_BASENAME : 'wp-pixel-tracker-pro/wp-pixel-tracker-pro.php';
    if ($file === $current_file || strpos($file, 'wp-pixel-tracker-pro.php') !== false) {
        $check_url = wp_nonce_url(admin_url('plugins.php?wpps_check_updates=1'), 'wpps_check_updates_nonce');
        $links[]   = '<a href="' . esc_url($check_url) . '" style="font-weight: bold; color: #2271b1;"><span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle; margin-right: 3px;"></span>Verificar Atualizações</a>';
    }
    return $links;
}

// Handle clicking check updates
add_action('admin_init', 'wpps_handle_check_updates_action');
function wpps_handle_check_updates_action() {
    if (!is_admin() || !current_user_can('update_plugins')) {
        return;
    }

    if (isset($_GET['wpps_check_updates']) && $_GET['wpps_check_updates'] === '1') {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wpps_check_updates_nonce')) {
            wp_die('Acesso negado.');
        }

        // Force check updates by deleting transients
        delete_site_transient('update_plugins');
        delete_transient('update_plugins');

        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_p_api_%' OR option_name LIKE '_transient_timeout_p_api_%'");

        wp_clean_plugins_cache();

        // Query update transient manually to populate response immediately
        if (function_exists('wpps_check_github_plugin_update')) {
            $transient = get_site_transient('update_plugins');
            $updated_transient = wpps_check_github_plugin_update($transient);
            set_site_transient('update_plugins', $updated_transient);
        }

        wp_safe_redirect(admin_url('plugins.php?wpps_updates_checked=1'));
        exit;
    }
}

// Display notice after checking updates
add_action('admin_notices', 'wpps_display_updates_checked_notice');
function wpps_display_updates_checked_notice() {
    global $pagenow;
    if ($pagenow === 'plugins.php' && isset($_GET['wpps_updates_checked']) && $_GET['wpps_updates_checked'] === '1') {
        echo '<div class="notice notice-success is-dismissible"><p><strong>WP Pixel & Tracking Pro:</strong> Verificação de atualizações concluída! Se houver uma nova versão disponível no servidor, ela aparecerá na lista de plugins abaixo para atualização com 1 clique.</p></div>';
    }
}
