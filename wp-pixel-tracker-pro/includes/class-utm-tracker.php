<?php
/**
 * UTM Tracker & Cookie Persistence Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WPPixelTracker_UTM')) {

    class WPPixelTracker_UTM {

        private static $utm_keys = array(
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'fbadid', 'gadid', 'ttadid'
        );

        public function __construct() {
            add_action('init', array($this, 'capture_utms_to_cookies'));
            add_action('woocommerce_checkout_update_order_meta', array($this, 'save_utms_to_order'), 10, 2);
            add_action('add_meta_boxes', array($this, 'register_order_utm_metabox'));
        }

        /**
         * Capture UTM parameters from URL and save into 30-day Cookies
         */
        public function capture_utms_to_cookies() {
            if (is_admin()) {
                return;
            }

            $expiry = time() + (30 * 86400); // 30 days
            $cookie_domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';

            foreach (self::$utm_keys as $key) {
                if (isset($_GET[$key]) && !empty($_GET[$key])) {
                    $val = sanitize_text_field($_GET[$key]);
                    if (!headers_sent()) {
                        @setcookie('wpps_' . $key, $val, $expiry, '/', $cookie_domain, is_ssl(), false);
                    }
                    $_COOKIE['wpps_' . $key] = $val;
                }
            }
        }

        /**
         * Save saved UTM cookies to WooCommerce Order Meta on purchase
         */
        public function save_utms_to_order($order_id, $data = null) {
            foreach (self::$utm_keys as $key) {
                $cookie_name = 'wpps_' . $key;
                if (isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])) {
                    $val = sanitize_text_field($_COOKIE[$cookie_name]);
                    update_post_meta($order_id, '_wpps_' . $key, $val);
                }
            }
        }

        /**
         * Register WooCommerce Order Admin Metabox
         */
        public function register_order_utm_metabox() {
            if (!class_exists('WooCommerce')) {
                return;
            }

            $screen = 'shop_order';
            try {
                if (function_exists('wc_get_container') && class_exists('Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')) {
                    $container = wc_get_container();
                    if ($container && method_exists($container, 'has') && $container->has('Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')) {
                        $hpos = $container->get('Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController');
                        if ($hpos && method_exists($hpos, 'custom_orders_table_usage_is_enabled') && $hpos->custom_orders_table_usage_is_enabled()) {
                            if (function_exists('wc_get_page_screen_id')) {
                                $screen = wc_get_page_screen_id('shop-order');
                            }
                        }
                    }
                }
            } catch (Throwable $e) {
                $screen = 'shop_order';
            }

            add_meta_box(
                'wpps_order_utm_metabox',
                'Origem da Campanha (Rastreamento de UTMs)',
                array($this, 'render_order_utm_metabox'),
                $screen,
                'side',
                'high'
            );
        }

        /**
         * Render Order UTM Metabox in Admin
         */
        public function render_order_utm_metabox($post_or_order) {
            $order_id = 0;

            if (is_object($post_or_order)) {
                if (method_exists($post_or_order, 'get_id')) {
                    $order_id = $post_or_order->get_id();
                } elseif (isset($post_or_order->ID)) {
                    $order_id = $post_or_order->ID;
                }
            }

            if (!$order_id) return;

            $source = get_post_meta($order_id, '_wpps_utm_source', true);
            $medium = get_post_meta($order_id, '_wpps_utm_medium', true);
            $campaign = get_post_meta($order_id, '_wpps_utm_campaign', true);
            $term = get_post_meta($order_id, '_wpps_utm_term', true);
            $content = get_post_meta($order_id, '_wpps_utm_content', true);

            ?>
            <div class="wpps-order-utm-box" style="font-size:12px; line-height:1.5;">
                <p><strong>UTM Source:</strong> <?php echo !empty($source) ? esc_html($source) : '<span style="color:#94a3b8;">Direto / Nenhum</span>'; ?></p>
                <p><strong>UTM Medium:</strong> <?php echo !empty($medium) ? esc_html($medium) : '<span style="color:#94a3b8;">-</span>'; ?></p>
                <p><strong>UTM Campaign:</strong> <?php echo !empty($campaign) ? esc_html($campaign) : '<span style="color:#94a3b8;">-</span>'; ?></p>
                <p><strong>UTM Term:</strong> <?php echo !empty($term) ? esc_html($term) : '<span style="color:#94a3b8;">-</span>'; ?></p>
                <p><strong>UTM Content:</strong> <?php echo !empty($content) ? esc_html($content) : '<span style="color:#94a3b8;">-</span>'; ?></p>
            </div>
            <?php
        }
    }
}
