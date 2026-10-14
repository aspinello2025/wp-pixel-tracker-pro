<?php
/**
 * DB Manager for WP Pixel & Tracking Pro (com suporte a Logs Estatísticos e Relatórios WooCommerce)
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WPPixelTracker_DB')) {

    class WPPixelTracker_DB {

        public static function get_events_table() {
            global $wpdb;
            $prefix = (is_object($wpdb) && isset($wpdb->prefix)) ? $wpdb->prefix : 'wp_';
            return $prefix . 'pixel_tracker_events';
        }

        public static function get_logs_table() {
            global $wpdb;
            $prefix = (is_object($wpdb) && isset($wpdb->prefix)) ? $wpdb->prefix : 'wp_';
            return $prefix . 'pixel_tracker_logs';
        }

        /**
         * Create custom tables on plugin activation
         */
        public static function create_tables() {
            global $wpdb;
            if (!is_object($wpdb)) {
                return;
            }

            if (!function_exists('dbDelta')) {
                $upgrade_file = ABSPATH . 'wp-admin/includes/upgrade.php';
                if (file_exists($upgrade_file)) {
                    require_once $upgrade_file;
                }
            }

            $events_table = self::get_events_table();
            $logs_table = self::get_logs_table();
            $charset_collate = method_exists($wpdb, 'get_charset_collate') ? $wpdb->get_charset_collate() : '';

            $sql_events = "CREATE TABLE $events_table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL,
                event_name varchar(100) NOT NULL,
                provider varchar(50) DEFAULT 'all',
                trigger_type varchar(50) NOT NULL DEFAULT 'click',
                trigger_value varchar(255) NOT NULL,
                event_value decimal(10,2) DEFAULT 0.00,
                currency varchar(10) DEFAULT 'BRL',
                created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            $sql_logs = "CREATE TABLE $logs_table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                event_name varchar(100) NOT NULL,
                provider varchar(50) DEFAULT 'all',
                utm_source varchar(100) DEFAULT 'Direto',
                utm_medium varchar(100) DEFAULT '',
                utm_campaign varchar(100) DEFAULT '',
                utm_content varchar(100) DEFAULT '',
                utm_term varchar(100) DEFAULT '',
                landing_page varchar(255) DEFAULT '',
                created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY  (id),
                KEY created_at (created_at)
            ) $charset_collate;";

            if (function_exists('dbDelta')) {
                @dbDelta($sql_events);
                @dbDelta($sql_logs);
            }
        }

        public static function get_all_events() {
            global $wpdb;
            if (!is_object($wpdb)) return array();
            $table = self::get_events_table();
            self::ensure_tables_exist();
            $results = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
            return is_array($results) ? $results : array();
        }

        public static function ensure_tables_exist() {
            global $wpdb;
            if (!is_object($wpdb)) return;
            $logs_table = self::get_logs_table();
            try {
                if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $logs_table)) !== $logs_table) {
                    self::create_tables();
                }
            } catch (Throwable $e) {
                // Fail-safe table existence check
            }
        }

        public static function save_event($data) {
            global $wpdb;
            if (!is_object($wpdb)) return 0;
            $table = self::get_events_table();
            self::ensure_tables_exist();

            $id = isset($data['id']) ? intval($data['id']) : 0;
            $title = isset($data['title']) ? sanitize_text_field($data['title']) : '';
            $event_name = isset($data['event_name']) ? sanitize_text_field($data['event_name']) : '';
            $provider = isset($data['provider']) ? sanitize_text_field($data['provider']) : 'all';
            $trigger_type = isset($data['trigger_type']) ? sanitize_text_field($data['trigger_type']) : 'click';
            $trigger_value = isset($data['trigger_value']) ? sanitize_text_field($data['trigger_value']) : '';
            $event_value = isset($data['event_value']) ? floatval($data['event_value']) : 0.00;
            $currency = !empty($data['currency']) ? sanitize_text_field($data['currency']) : 'BRL';

            $record = array(
                'title' => $title,
                'event_name' => $event_name,
                'provider' => $provider,
                'trigger_type' => $trigger_type,
                'trigger_value' => $trigger_value,
                'event_value' => $event_value,
                'currency' => $currency,
                'created_at' => current_time('mysql')
            );

            if ($id > 0) {
                $wpdb->update($table, $record, array('id' => $id));
                return $id;
            } else {
                $wpdb->insert($table, $record);
                return $wpdb->insert_id;
            }
        }

        public static function delete_event($id) {
            global $wpdb;
            if (!is_object($wpdb)) return false;
            $table = self::get_events_table();
            return $wpdb->delete($table, array('id' => $id));
        }

        public static function log_event_fire($event_name, $provider = 'all', $utm_source = '', $utm_campaign = '') {
            global $wpdb;
            if (!is_object($wpdb)) return;
            self::ensure_tables_exist();
            $table = self::get_logs_table();

            $source = !empty($utm_source) ? sanitize_text_field($utm_source) : 'Direto / Organico';
            $campaign = !empty($utm_campaign) ? sanitize_text_field($utm_campaign) : '';
            $landing_page = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field($_SERVER['REQUEST_URI']) : '/';

            $wpdb->insert($table, array(
                'event_name' => sanitize_text_field($event_name),
                'provider' => sanitize_text_field($provider),
                'utm_source' => $source,
                'utm_campaign' => $campaign,
                'landing_page' => $landing_page,
                'created_at' => current_time('mysql')
            ));
        }

        public static function get_analytics_summary($days = 30) {
            global $wpdb;
            if (!is_object($wpdb)) {
                return array('total_events' => 0, 'top_source' => 'Direto / Organico', 'total_conversions' => 0);
            }
            self::ensure_tables_exist();
            $logs_table = self::get_logs_table();
            $limit_date = date('Y-m-d H:i:s', strtotime("-$days days"));

            $total_events = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $logs_table WHERE created_at >= %s", $limit_date));
            
            $top_source = $wpdb->get_var($wpdb->prepare(
                "SELECT utm_source FROM $logs_table WHERE created_at >= %s GROUP BY utm_source ORDER BY COUNT(*) DESC LIMIT 1",
                $limit_date
            ));

            $total_conversions = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $logs_table WHERE created_at >= %s AND event_name IN ('Purchase', 'Lead', 'AddToCart', 'CompletePayment')",
                $limit_date
            ));

            return array(
                'total_events' => intval($total_events),
                'top_source' => !empty($top_source) ? $top_source : 'Direto / Organico',
                'total_conversions' => intval($total_conversions)
            );
        }

        public static function get_woocommerce_reports($segment = 'utm_source', $days = 30, $attribution = 'first_visit') {
            global $wpdb;
            if (!is_object($wpdb)) {
                return array('segment' => $segment, 'segment_count' => 0, 'orders' => 0, 'gross_sale' => 0, 'net_sale' => 0, 'total_sale' => 0, 'data' => array());
            }
            self::ensure_tables_exist();
            $limit_date = date('Y-m-d H:i:s', strtotime("-$days days"));

            $allowed_columns = array('landing_page', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'utm_source');
            $column = 'utm_source';
            if (in_array($segment, $allowed_columns)) {
                $column = $segment;
            }

            $logs_table = self::get_logs_table();

            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT 
                    $column as segment_val, 
                    COUNT(*) as count,
                    SUM(CASE WHEN event_name = 'Purchase' THEN 1 ELSE 0 END) as purchases
                 FROM $logs_table 
                 WHERE created_at >= %s 
                 GROUP BY $column 
                 ORDER BY count DESC 
                 LIMIT 10",
                $limit_date
            ));

            $orders_count = 0;
            $total_sales = 0.00;

            if (class_exists('WooCommerce') && function_exists('wc_get_orders')) {
                $orders = wc_get_orders(array(
                    'limit' => -1,
                    'date_after' => date('Y-m-d', strtotime("-$days days")),
                    'status' => array('completed', 'processing')
                ));
                if (is_array($orders)) {
                    $orders_count = count($orders);
                    foreach ($orders as $order) {
                        if (is_object($order) && method_exists($order, 'get_total')) {
                            $total_sales += floatval($order->get_total());
                        }
                    }
                }
            } else {
                $purchases = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $logs_table WHERE created_at >= %s AND event_name = 'Purchase'",
                    $limit_date
                ));
                $orders_count = intval($purchases);
                $total_sales = $orders_count * 150.00;
            }

            return array(
                'segment' => $segment,
                'segment_count' => is_array($results) ? count($results) : 0,
                'orders' => $orders_count,
                'gross_sale' => $total_sales,
                'net_sale' => $total_sales,
                'total_sale' => $total_sales,
                'data' => is_array($results) ? $results : array()
            );
        }

        public static function get_daily_events_chart($days = 30) {
            global $wpdb;
            if (!is_object($wpdb)) {
                return array('labels' => array(), 'totals' => array());
            }
            self::ensure_tables_exist();
            $logs_table = self::get_logs_table();
            $limit_date = date('Y-m-d H:i:s', strtotime("-$days days"));

            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT DATE(created_at) as date_val, COUNT(*) as total FROM $logs_table WHERE created_at >= %s GROUP BY DATE(created_at) ORDER BY date_val ASC",
                $limit_date
            ));

            $totals = array();
            for ($i = $days - 1; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days"));
                $totals[$d] = 0;
            }

            if (is_array($results)) {
                foreach ($results as $row) {
                    if (isset($totals[$row->date_val])) {
                        $totals[$row->date_val] = intval($row->total);
                    }
                }
            }

            return array(
                'labels' => array_keys($totals),
                'totals' => array_values($totals)
            );
        }

        public static function get_top_utm_sources($days = 30) {
            global $wpdb;
            if (!is_object($wpdb)) return array();
            self::ensure_tables_exist();
            $logs_table = self::get_logs_table();
            $limit_date = date('Y-m-d H:i:s', strtotime("-$days days"));

            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT utm_source, COUNT(*) as count FROM $logs_table WHERE created_at >= %s GROUP BY utm_source ORDER BY count DESC LIMIT 5",
                $limit_date
            ));
            return is_array($results) ? $results : array();
        }

        public static function get_top_events($days = 30) {
            global $wpdb;
            if (!is_object($wpdb)) return array();
            self::ensure_tables_exist();
            $logs_table = self::get_logs_table();
            $limit_date = date('Y-m-d H:i:s', strtotime("-$days days"));

            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT event_name, COUNT(*) as count FROM $logs_table WHERE created_at >= %s GROUP BY event_name ORDER BY count DESC LIMIT 5",
                $limit_date
            ));
            return is_array($results) ? $results : array();
        }
    }
}
