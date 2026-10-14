<?php
/**
 * Meta Conversions API (CAPI) Server-Side Sender for WP Pixel & Tracking Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WPPixelTracker_CAPI')) {

    class WPPixelTracker_CAPI {

        private static $api_version = 'v18.0';

        /**
         * Send Event Server-Side to Meta Graph API
         */
        public static function send_event($event_name, $custom_data = array(), $event_id = '') {
            $pixel_id = get_option('wpps_fb_pixel_id');
            $access_token = get_option('wpps_fb_capi_token');
            $test_event_code = get_option('wpps_fb_test_code');

            if (empty($pixel_id) || empty($access_token)) {
                return false;
            }

            $user_data = array(
                'client_ip_address' => self::get_client_ip(),
                'client_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : ''
            );

            if (function_exists('is_user_logged_in') && is_user_logged_in()) {
                $user = wp_get_current_user();
                if ($user && isset($user->user_email) && !empty($user->user_email)) {
                    $user_data['em'] = array(hash('sha256', strtolower(trim($user->user_email))));
                }
            }

            $event_data = array(
                'event_name' => $event_name,
                'event_time' => time(),
                'action_source' => 'website',
                'event_source_url' => self::get_current_url(),
                'user_data' => $user_data
            );

            if (!empty($event_id)) {
                $event_data['event_id'] = $event_id;
            }

            if (!empty($custom_data)) {
                $event_data['custom_data'] = $custom_data;
            }

            $payload = array(
                'data' => array($event_data)
            );

            if (!empty($test_event_code)) {
                $payload['test_event_code'] = $test_event_code;
            }

            $url = "https://graph.facebook.com/" . self::$api_version . "/{$pixel_id}/events?access_token={$access_token}";

            $response = wp_remote_post($url, array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => json_encode($payload),
                'timeout' => 5,
                'blocking' => false
            ));

            return !is_wp_error($response);
        }

        private static function get_client_ip() {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                return sanitize_text_field(trim($ips[0]));
            } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
                return sanitize_text_field($_SERVER['REMOTE_ADDR']);
            }
            return '127.0.0.1';
        }

        private static function get_current_url() {
            $protocol = (function_exists('is_ssl') && is_ssl()) ? 'https://' : 'http://';
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            return $protocol . $host . $uri;
        }
    }
}
