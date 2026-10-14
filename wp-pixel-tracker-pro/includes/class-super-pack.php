<?php
/**
 * Super Pack Features Engine for WP Pixel & Tracking Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WPPixelTracker_SuperPack')) {

    class WPPixelTracker_SuperPack {

        public function __construct() {
            add_action('add_meta_boxes', array($this, 'register_page_pixel_metabox'));
            add_action('save_post', array($this, 'save_page_pixel_metabox'));
        }

        /**
         * Register Post/Page Metabox for disabling pixels per page
         */
        public function register_page_pixel_metabox() {
            $post_types = get_post_types(array('public' => true), 'names');
            if (is_array($post_types)) {
                foreach ($post_types as $type) {
                    add_meta_box(
                        'wpps_page_pixel_metabox',
                        'WP Pixel & Tracking Pro - Controle de Página',
                        array($this, 'render_page_pixel_metabox'),
                        $type,
                        'side',
                        'default'
                    );
                }
            }
        }

        public function render_page_pixel_metabox($post) {
            if (!is_object($post) || !isset($post->ID)) return;
            $disabled = get_post_meta($post->ID, '_wpps_disable_pixels', true);
            wp_nonce_field('wpps_page_pixel_nonce', 'wpps_page_pixel_nonce_field');
            ?>
            <div class="wpps-metabox-content" style="font-size:12px;">
                <label style="display:block; margin-bottom:6px;">
                    <input type="checkbox" name="wpps_disable_pixels" value="1" <?php checked($disabled, '1'); ?> />
                    <strong>Desativar disparo de Pixels nesta página</strong>
                </label>
                <p class="description" style="color:#64748b; font-size:11px;">Marque para impedir a injeção do Meta Pixel, GA4, TikTok e scripts nesta URL.</p>
            </div>
            <?php
        }

        public function save_page_pixel_metabox($post_id) {
            if (!isset($_POST['wpps_page_pixel_nonce_field']) || !wp_verify_nonce($_POST['wpps_page_pixel_nonce_field'], 'wpps_page_pixel_nonce')) {
                return;
            }
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            if (!current_user_can('edit_post', $post_id)) return;

            $val = isset($_POST['wpps_disable_pixels']) ? '1' : '0';
            update_post_meta($post_id, '_wpps_disable_pixels', $val);
        }

        /**
         * Check if pixels are disabled for current page
         */
        public static function is_pixels_disabled_for_page() {
            if (function_exists('is_singular') && is_singular()) {
                $post_id = get_the_ID();
                if ($post_id && get_post_meta($post_id, '_wpps_disable_pixels', true) === '1') {
                    return true;
                }
            }
            return false;
        }

        /**
         * Get Current Language (WPML / Polylang / WP)
         */
        public static function get_current_language() {
            if (defined('ICL_LANGUAGE_CODE')) {
                return ICL_LANGUAGE_CODE;
            } elseif (function_exists('pll_current_language')) {
                return pll_current_language();
            }
            return function_exists('get_locale') ? get_locale() : 'pt_BR';
        }

        /**
         * Parse Dynamic Parameters ({post_title}, {post_id}, {user_id}, {user_email}, {user_name}, {post_type}, {category}, {product_price}, {product_sku}, etc.)
         */
        public static function parse_dynamic_tags($text) {
            if (empty($text)) return $text;

            $post_title = (function_exists('is_singular') && is_singular()) ? get_the_title() : '';
            $post_id = (function_exists('is_singular') && is_singular()) ? get_the_ID() : '';
            $post_type = (function_exists('is_singular') && is_singular()) ? get_post_type() : '';
            $category = '';

            if (function_exists('is_singular') && is_singular() && $post_id) {
                $categories = get_the_category($post_id);
                if (!empty($categories) && is_array($categories)) {
                    $category = $categories[0]->name;
                }
            }

            $user_id = function_exists('get_current_user_id') ? get_current_user_id() : 0;
            $user_email = '';
            $user_name = '';

            if ($user_id && function_exists('wp_get_current_user')) {
                $current_user = wp_get_current_user();
                if (is_object($current_user)) {
                    $user_email = $current_user->user_email;
                    $user_name = $current_user->display_name;
                }
            }

            $product_price = '';
            $product_sku = '';
            if ($post_id && function_exists('wc_get_product')) {
                $product = wc_get_product($post_id);
                if ($product && is_object($product)) {
                    $product_price = method_exists($product, 'get_price') ? $product->get_price() : '';
                    $product_sku = method_exists($product, 'get_sku') ? $product->get_sku() : '';
                }
            }

            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $scheme = (function_exists('is_ssl') && is_ssl()) ? 'https://' : 'http://';

            $replacements = array(
                '{post_title}'    => $post_title,
                '{post_id}'       => $post_id,
                '{post_type}'     => $post_type,
                '{category}'      => $category,
                '{user_id}'       => $user_id,
                '{user_email}'    => $user_email,
                '{user_name}'     => $user_name,
                '{product_price}' => $product_price,
                '{product_sku}'   => $product_sku,
                '{language}'      => self::get_current_language(),
                '{page_url}'      => $scheme . $host . $uri
            );

            return str_replace(array_keys($replacements), array_values($replacements), $text);
        }

        /**
         * Check if current page is a Custom WooCommerce Thank You page
         */
        public static function is_custom_thank_you_page() {
            $custom_urls = get_option('wpps_custom_thankyou_urls', '');
            if (empty($custom_urls)) {
                return false;
            }

            $urls = array_filter(array_map('trim', explode("\n", $custom_urls)));
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $current_path = parse_url($uri, PHP_URL_PATH);

            if (empty($current_path)) return false;

            foreach ($urls as $url) {
                $clean_url = trim((string) parse_url($url, PHP_URL_PATH), '/');
                $clean_current = trim((string) $current_path, '/');
                
                // Never treat empty or root path as a thank you page
                if (empty($clean_url)) {
                    continue;
                }

                if ($clean_current === $clean_url || strpos($clean_current, $clean_url) !== false) {
                    return true;
                }
            }

            return false;
        }
    }
}
