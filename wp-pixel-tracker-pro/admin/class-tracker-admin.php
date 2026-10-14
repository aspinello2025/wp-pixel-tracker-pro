<?php
/**
 * Admin Panel Controller for WP Pixel & Tracking Pro (com Super Pack)
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WPPixelTracker_Admin')) {

    class WPPixelTracker_Admin {

        public function __construct() {
            add_action('admin_menu', array($this, 'register_menu_pages'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

            // AJAX Hooks
            add_action('wp_ajax_wpps_save_settings', array($this, 'ajax_save_settings'));
            add_action('wp_ajax_wpps_save_custom_event', array($this, 'ajax_save_custom_event'));
            add_action('wp_ajax_wpps_delete_custom_event', array($this, 'ajax_delete_custom_event'));
            add_action('wp_ajax_wpps_clear_logs', array($this, 'ajax_clear_logs'));
            add_action('wp_ajax_wpps_log_event', array($this, 'ajax_log_event_fire'));
            add_action('wp_ajax_nopriv_wpps_log_event', array($this, 'ajax_log_event_fire'));
        }

        public function register_menu_pages() {
            add_menu_page(
                'Pixel Tracker',
                'Pixel Tracker',
                'manage_options',
                'wp-pixel-tracker',
                array($this, 'render_dashboard_page'),
                'dashicons-chart-line',
                31
            );

            add_submenu_page(
                'wp-pixel-tracker',
                'Dashboard Analytics - Pixel Tracker',
                'Painel & Gráficos',
                'manage_options',
                'wp-pixel-tracker',
                array($this, 'render_dashboard_page')
            );

            add_submenu_page(
                'wp-pixel-tracker',
                'Configurar Pixels - Pixel Tracker',
                'Configurar Pixels',
                'manage_options',
                'wp-pixel-tracker-config',
                array($this, 'render_settings_page')
            );

            add_submenu_page(
                'wp-pixel-tracker',
                'UTM Builder - Pixel Tracker',
                'UTM Builder',
                'manage_options',
                'wp-pixel-tracker-utm',
                array($this, 'render_utm_page')
            );

            add_submenu_page(
                'wp-pixel-tracker',
                'Eventos Customizados - Pixel Tracker',
                'Eventos Customizados',
                'manage_options',
                'wp-pixel-tracker-events',
                array($this, 'render_events_page')
            );

            add_submenu_page(
                'wp-pixel-tracker',
                'Licença & Ativação - Pixel Tracker',
                'Licença & Ativação',
                'manage_options',
                'wp-pixel-tracker-license',
                array($this, 'render_license_page')
            );
        }

        public function enqueue_assets($hook) {
            if (strpos($hook, 'wp-pixel-tracker') === false) {
                return;
            }

            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', array(), '4.4.1', true);

            wp_enqueue_style('wpps-admin-style', WP_PIXEL_TRACKER_URL . 'admin/assets/css/tracker-admin-style.css', array(), WP_PIXEL_TRACKER_VERSION);
            wp_enqueue_script('wpps-admin-script', WP_PIXEL_TRACKER_URL . 'admin/assets/js/tracker-admin-script.js', array('jquery', 'chart-js'), WP_PIXEL_TRACKER_VERSION, true);

            wp_localize_script('wpps-admin-script', 'WPPixelVars', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wpps_admin_nonce')
            ));
        }

        public function render_dashboard_page() {
            if (!$this->check_activation()) return;

            // Handle Direct PHP Clear Logs Action (Guaranteed to work even without JS/AJAX)
            if (isset($_GET['wpps_action']) && $_GET['wpps_action'] === 'clear_logs') {
                if (check_admin_referer('wpps_clear_logs_action') && current_user_can('manage_options')) {
                    if (class_exists('WPPixelTracker_DB')) {
                        global $wpdb;
                        $logs_table = WPPixelTracker_DB::get_logs_table();
                        $wpdb->query("DELETE FROM $logs_table");
                    }
                    echo '<div class="notice notice-success is-dismissible" style="margin-left:0; margin-bottom: 20px;"><p><strong>✓ Relatórios e estatísticas foram zerados com sucesso!</strong></p></div>';
                }
            }

            require_once WP_PIXEL_TRACKER_DIR . 'admin/views/dashboard-view.php';
        }

        public function render_settings_page() {
            if (!$this->check_activation()) return;
            require_once WP_PIXEL_TRACKER_DIR . 'admin/views/pixel-settings-view.php';
        }

        public function render_utm_page() {
            if (!$this->check_activation()) return;
            require_once WP_PIXEL_TRACKER_DIR . 'admin/views/utm-builder-view.php';
        }

        public function render_events_page() {
            if (!$this->check_activation()) return;
            require_once WP_PIXEL_TRACKER_DIR . 'admin/views/custom-events-view.php';
        }

        public function render_license_page() {
            if (function_exists('wpps_check_license_status')) {
                wpps_check_license_status(true);
            }
            require_once WP_PIXEL_TRACKER_DIR . 'admin/views/license-view.php';
        }

        private function check_activation() {
            if (!get_option('wp_pixel_tracker_is_activated')) {
                require_once WP_PIXEL_TRACKER_DIR . 'admin/views/license-view.php';
                return false;
            }
            return true;
        }

        public function ajax_clear_logs() {
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => 'Permissão negada.'));
            }
            if (class_exists('WPPixelTracker_DB')) {
                global $wpdb;
                $logs_table = WPPixelTracker_DB::get_logs_table();
                $wpdb->query("DELETE FROM $logs_table");
                wp_send_json_success(array('message' => 'Logs apagados com sucesso!'));
            }
            wp_send_json_error(array('message' => 'Erro ao apagar logs.'));
        }

        public function ajax_log_event_fire() {
            $event_name = isset($_POST['event_name']) ? sanitize_text_field($_POST['event_name']) : '';
            $provider = isset($_POST['provider']) ? sanitize_text_field($_POST['provider']) : 'all';
            $utm_source = isset($_POST['utm_source']) ? sanitize_text_field($_POST['utm_source']) : '';
            $utm_campaign = isset($_POST['utm_campaign']) ? sanitize_text_field($_POST['utm_campaign']) : '';

            if (!empty($event_name) && class_exists('WPPixelTracker_DB')) {
                WPPixelTracker_DB::log_event_fire($event_name, $provider, $utm_source, $utm_campaign);
                wp_send_json_success();
            }
            wp_send_json_error();
        }

        public function ajax_save_settings() {
            check_ajax_referer('wpps_admin_nonce', 'nonce');

            if (!current_user_can('manage_options') || !get_option('wp_pixel_tracker_is_activated')) {
                wp_send_json_error(array('message' => 'Permissão negada ou licença inativa.'));
            }

            $fb_id = isset($_POST['fb_pixel_id']) ? sanitize_text_field($_POST['fb_pixel_id']) : '';
            $fb_capi_token = isset($_POST['fb_capi_token']) ? trim(sanitize_textarea_field($_POST['fb_capi_token'])) : '';
            $fb_test_code = isset($_POST['fb_test_code']) ? sanitize_text_field($_POST['fb_test_code']) : '';
            $ga4_id = isset($_POST['ga4_id']) ? sanitize_text_field($_POST['ga4_id']) : '';
            $tiktok_id = isset($_POST['tiktok_pixel_id']) ? sanitize_text_field($_POST['tiktok_pixel_id']) : '';
            $gads_id = isset($_POST['gads_id']) ? sanitize_text_field($_POST['gads_id']) : '';
            $reddit_id = isset($_POST['wpps_reddit_pixel_id']) ? sanitize_text_field($_POST['wpps_reddit_pixel_id']) : '';
            $pinterest_id = isset($_POST['wpps_pinterest_tag_id']) ? sanitize_text_field($_POST['wpps_pinterest_tag_id']) : '';
            $bing_id = isset($_POST['wpps_bing_uet_id']) ? sanitize_text_field($_POST['wpps_bing_uet_id']) : '';
            $enable_edd = isset($_POST['wpps_enable_edd']) ? sanitize_text_field($_POST['wpps_enable_edd']) : 'yes';
            $enable_amp = isset($_POST['wpps_enable_amp']) ? sanitize_text_field($_POST['wpps_enable_amp']) : 'no';
            $auto_track_forms = isset($_POST['wpps_auto_track_forms']) ? sanitize_text_field($_POST['wpps_auto_track_forms']) : 'yes';
            $auto_track_signup = isset($_POST['wpps_auto_track_signup']) ? sanitize_text_field($_POST['wpps_auto_track_signup']) : 'yes';
            $auto_track_login = isset($_POST['wpps_auto_track_login']) ? sanitize_text_field($_POST['wpps_auto_track_login']) : 'yes';
            $custom_thankyou = isset($_POST['custom_thankyou_urls']) ? sanitize_textarea_field(wp_unslash($_POST['custom_thankyou_urls'])) : '';
            $head_scripts = isset($_POST['head_scripts']) ? stripslashes($_POST['head_scripts']) : '';
            $footer_scripts = isset($_POST['footer_scripts']) ? stripslashes($_POST['footer_scripts']) : '';

            update_option('wpps_fb_pixel_id', $fb_id);
            update_option('wpps_fb_capi_token', $fb_capi_token);
            update_option('wpps_fb_test_code', $fb_test_code);
            update_option('wpps_ga4_id', $ga4_id);
            update_option('wpps_tiktok_pixel_id', $tiktok_id);
            update_option('wpps_gads_id', $gads_id);
            update_option('wpps_reddit_pixel_id', $reddit_id);
            update_option('wpps_pinterest_tag_id', $pinterest_id);
            update_option('wpps_bing_uet_id', $bing_id);
            update_option('wpps_enable_edd', $enable_edd);
            update_option('wpps_enable_amp', $enable_amp);
            update_option('wpps_auto_track_forms', $auto_track_forms);
            update_option('wpps_auto_track_signup', $auto_track_signup);
            update_option('wpps_auto_track_login', $auto_track_login);
            update_option('wpps_custom_thankyou_urls', $custom_thankyou);
            update_option('wpps_head_scripts', $head_scripts);
            update_option('wpps_footer_scripts', $footer_scripts);

            wp_send_json_success(array('message' => 'Configurações salvas com sucesso!'));
        }

        public function ajax_save_custom_event() {
            check_ajax_referer('wpps_admin_nonce', 'nonce');

            if (!current_user_can('manage_options') || !get_option('wp_pixel_tracker_is_activated')) {
                wp_send_json_error(array('message' => 'Permissão negada ou licença inativa.'));
            }

            if (!class_exists('WPPixelTracker_DB')) {
                wp_send_json_error(array('message' => 'Erro interno de banco de dados.'));
            }

            $id = WPPixelTracker_DB::save_event($_POST);
            if ($id) {
                wp_send_json_success(array('message' => 'Evento customizado salvo com sucesso!', 'id' => $id));
            } else {
                wp_send_json_error(array('message' => 'Erro ao salvar o evento.'));
            }
        }

        public function ajax_delete_custom_event() {
            check_ajax_referer('wpps_admin_nonce', 'nonce');

            if (!current_user_can('manage_options') || !get_option('wp_pixel_tracker_is_activated')) {
                wp_send_json_error(array('message' => 'Permissão negada ou licença inativa.'));
            }

            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id > 0 && class_exists('WPPixelTracker_DB')) {
                WPPixelTracker_DB::delete_event($id);
                wp_send_json_success(array('message' => 'Evento excluído com sucesso!'));
            } else {
                wp_send_json_error(array('message' => 'ID inválido.'));
            }
        }
    }
}
