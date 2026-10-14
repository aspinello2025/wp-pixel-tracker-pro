<?php
/**
 * Plugin Name: WP Pixel & Tracking Pro (Limpo)
 * Plugin URI:  https://github.com/aspinello2025/wp-pixel-tracker
 * Description: Gerenciador de Pixels profissional para WordPress & WooCommerce (Meta Pixel CAPI, GA4, TikTok, Google Ads, Múltiplos Pixels, WPML, Desativação por Página, Custom Thank You Pages e Módulo Super Pack).
 * Version:     1.7.0
 * Author:      Alexandre Spinello
 * Text Domain: wp-pixel-tracker-pro
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_PIXEL_TRACKER_VERSION')) {
    define('WP_PIXEL_TRACKER_VERSION', '1.7.0');
}
if (!defined('WP_PIXEL_TRACKER_DIR')) {
    define('WP_PIXEL_TRACKER_DIR', plugin_dir_path(__FILE__));
}
if (!defined('WP_PIXEL_TRACKER_URL')) {
    define('WP_PIXEL_TRACKER_URL', plugin_dir_url(__FILE__));
}
if (!defined('WP_PIXEL_TRACKER_BASENAME')) {
    define('WP_PIXEL_TRACKER_BASENAME', plugin_basename(__FILE__));
}

// Load includes safely
$wpps_includes = array(
    WP_PIXEL_TRACKER_DIR . 'includes/class-tracker-db.php',
    WP_PIXEL_TRACKER_DIR . 'includes/class-capi.php',
    WP_PIXEL_TRACKER_DIR . 'includes/class-utm-tracker.php',
    WP_PIXEL_TRACKER_DIR . 'includes/class-super-pack.php',
    WP_PIXEL_TRACKER_DIR . 'includes/class-pixel-injector.php',
    WP_PIXEL_TRACKER_DIR . 'includes/updater.php',
    WP_PIXEL_TRACKER_DIR . 'includes/licensing.php'
);

foreach ($wpps_includes as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

if (function_exists('is_admin') && is_admin()) {
    $admin_file = WP_PIXEL_TRACKER_DIR . 'admin/class-tracker-admin.php';
    if (file_exists($admin_file)) {
        require_once $admin_file;
    }
}

// Safe Activation Hook - Minimal activation to prevent unexpected errors
if (function_exists('register_activation_hook')) {
    register_activation_hook(__FILE__, 'wpps_activate_plugin_clean');
}

if (!function_exists('wpps_activate_plugin_clean')) {
    function wpps_activate_plugin_clean() {
        if (function_exists('add_option') && function_exists('get_option')) {
            if (get_option('wp_pixel_tracker_is_activated') === false) {
                add_option('wp_pixel_tracker_is_activated', 0);
            }
        }
    }
}

// Initialize on plugins_loaded
if (!function_exists('wp_pixel_tracker_init_clean')) {
    function wp_pixel_tracker_init_clean() {
        if (class_exists('WPPixelTracker_UTM')) {
            new WPPixelTracker_UTM();
        }
        if (class_exists('WPPixelTracker_SuperPack')) {
            new WPPixelTracker_SuperPack();
        }
        if (class_exists('WPPixelTracker_Injector')) {
            new WPPixelTracker_Injector();
        }
        if (function_exists('is_admin') && is_admin() && class_exists('WPPixelTracker_Admin')) {
            new WPPixelTracker_Admin();
        }
    }
}

if (function_exists('add_action')) {
    add_action('plugins_loaded', 'wp_pixel_tracker_init_clean');
}
