<?php
/**
 * Pixel Injector & Front-End Event Dispatcher (com Múltiplos Pixels, CAPI, Custom Thank You & Super Pack)
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WPPixelTracker_Injector')) {

    class WPPixelTracker_Injector {

        public function __construct() {
            if (!get_option('wp_pixel_tracker_is_activated')) {
                return;
            }

            add_action('wp_head', array($this, 'inject_head_pixels'), 1);
            add_action('wp_footer', array($this, 'inject_footer_scripts'), 99);
            add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));

            // WooCommerce & Custom Thank You Hooks
            add_action('wp_head', array($this, 'track_woocommerce_view_content'), 5);
            add_action('woocommerce_thankyou', array($this, 'track_woocommerce_purchase'), 10, 1);
            add_action('wp_head', array($this, 'track_custom_thank_you_page'), 6);

            // Easy Digital Downloads (EDD) Hooks
            add_action('wp_head', array($this, 'track_edd_view_content'), 5);
            add_action('edd_receipt_post_content', array($this, 'track_edd_receipt'), 10, 1);

            // AMP Support Hooks
            add_action('amp_post_template_head', array($this, 'inject_amp_head_pixels'), 1);
        }

        public function enqueue_frontend_scripts() {
            if (class_exists('WPPixelTracker_SuperPack') && WPPixelTracker_SuperPack::is_pixels_disabled_for_page()) {
                return;
            }

            wp_enqueue_script('wp-pixel-frontend', WP_PIXEL_TRACKER_URL . 'assets/js/pixel-frontend-script.js', array('jquery'), WP_PIXEL_TRACKER_VERSION, true);

            $custom_events = class_exists('WPPixelTracker_DB') ? WPPixelTracker_DB::get_all_events() : array();

            wp_localize_script('wp-pixel-frontend', 'WPPixelFrontendVars', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'fb_enabled' => !empty(get_option('wpps_fb_pixel_id')),
                'ga4_enabled' => !empty(get_option('wpps_ga4_id')),
                'tiktok_enabled' => !empty(get_option('wpps_tiktok_pixel_id')),
                'reddit_enabled' => !empty(get_option('wpps_reddit_pixel_id')),
                'pinterest_enabled' => !empty(get_option('wpps_pinterest_tag_id')),
                'bing_enabled' => !empty(get_option('wpps_bing_uet_id')),
                'auto_track_forms' => get_option('wpps_auto_track_forms', 'yes'),
                'auto_track_signup' => get_option('wpps_auto_track_signup', 'yes'),
                'auto_track_login' => get_option('wpps_auto_track_login', 'yes'),
                'custom_events' => is_array($custom_events) ? $custom_events : array()
            ));
        }

        /**
         * Inject Pixels and Scripts into <head> (com suporte a Múltiplos Pixels)
         */
        public function inject_head_pixels() {
            if (class_exists('WPPixelTracker_SuperPack') && WPPixelTracker_SuperPack::is_pixels_disabled_for_page()) {
                return;
            }

            $raw_fb_ids = get_option('wpps_fb_pixel_id', '');
            $raw_ga4_ids = get_option('wpps_ga4_id', '');
            $raw_tiktok_ids = get_option('wpps_tiktok_pixel_id', '');
            $raw_reddit_ids = get_option('wpps_reddit_pixel_id', '');
            $raw_pinterest_ids = get_option('wpps_pinterest_tag_id', '');
            $raw_bing_ids = get_option('wpps_bing_uet_id', '');
            $gads_id = get_option('wpps_gads_id', '');
            $head_scripts = get_option('wpps_head_scripts', '');

            $fb_ids = array_filter(array_map('trim', explode(',', $raw_fb_ids)));
            $ga4_ids = array_filter(array_map('trim', explode(',', $raw_ga4_ids)));
            $tiktok_ids = array_filter(array_map('trim', explode(',', $raw_tiktok_ids)));
            $reddit_ids = array_filter(array_map('trim', explode(',', $raw_reddit_ids)));
            $pinterest_ids = array_filter(array_map('trim', explode(',', $raw_pinterest_ids)));
            $bing_ids = array_filter(array_map('trim', explode(',', $raw_bing_ids)));

            // 1. Meta / Facebook Pixel & CAPI (Múltiplos IDs)
            if (!empty($fb_ids)) {
                $event_id = 'pageview_' . uniqid();

                if (class_exists('WPPixelTracker_CAPI')) {
                    WPPixelTracker_CAPI::send_event('PageView', array(), $event_id);
                }
                ?>
<!-- Meta Pixel & CAPI Code by WP Pixel & Tracking Pro (Múltiplos Pixels) -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
<?php foreach ($fb_ids as $fid): ?>
fbq('init', '<?php echo esc_js($fid); ?>');
<?php endforeach; ?>
fbq('track', 'PageView', {}, {eventID: '<?php echo esc_js($event_id); ?>'});
</script>
<noscript>
<?php foreach ($fb_ids as $fid): ?>
<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr($fid); ?>&ev=PageView&noscript=1" />
<?php endforeach; ?>
</noscript>
                <?php
            }

            // 2. Google Analytics 4 (Múltiplos IDs)
            if (!empty($ga4_ids) || !empty($gads_id)) {
                $primary_id = !empty($ga4_ids) ? $ga4_ids[0] : $gads_id;
                ?>
<!-- Google tag (gtag.js) by WP Pixel & Tracking Pro -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($primary_id); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  <?php foreach ($ga4_ids as $gid): ?>
  gtag('config', '<?php echo esc_js($gid); ?>');
  <?php endforeach; ?>

  <?php if (!empty($gads_id)): ?>
  gtag('config', '<?php echo esc_js($gads_id); ?>');
  <?php endif; ?>
</script>
                <?php
            }

            // 3. TikTok Pixel (Múltiplos IDs)
            if (!empty($tiktok_ids)) {
                ?>
<!-- TikTok Pixel Code by WP Pixel & Tracking Pro -->
<script>
!function (w, d, t) {
  w.TiktokPixelObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","unset","on","load","addEvent","removeEvent"],ttq.setAndDefer=function(e,tt){e[tt]=function(){e.push([tt].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};

  <?php foreach ($tiktok_ids as $tid): ?>
  ttq.load('<?php echo esc_js($tid); ?>');
  <?php endforeach; ?>
  ttq.page();
}(window, document, 'ttq');
</script>
                <?php
            }

            // 4. Reddit Pixel (Múltiplos IDs)
            if (!empty($reddit_ids)) {
                ?>
<!-- Reddit Pixel Code by WP Pixel & Tracking Pro -->
<script>
!function(w,d){if(!w.rdt){var p=w.rdt=function(){p.sendEvent?p.sendEvent.apply(p,arguments):p.callQueue.push(arguments)};p.callQueue=[];var t=d.createElement("script");t.src="https://www.redditstatic.com/ads/pixel.js",t.async=!0;var s=d.getElementsByTagName("script")[0];s.parentNode.insertBefore(t,s)}}(window,document);
<?php foreach ($reddit_ids as $rid): ?>
rdt('init','<?php echo esc_js($rid); ?>');
<?php endforeach; ?>
rdt('track','PageVisit');
</script>
                <?php
            }

            // 5. Pinterest Tag (Múltiplos IDs)
            if (!empty($pinterest_ids)) {
                ?>
<!-- Pinterest Tag by WP Pixel & Tracking Pro -->
<script>
!function(e){if(!window.pintrk){window.pintrk = function () {
window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
n=window.pintrk;n.queue=[],n.version="3.0";var
t=document.createElement("script");t.async=!0,t.src=e;var
r=document.getElementsByTagName("script")[0];
r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");
<?php foreach ($pinterest_ids as $pid): ?>
pintrk('load', '<?php echo esc_js($pid); ?>');
<?php endforeach; ?>
pintrk('page');
</script>
<noscript>
<?php foreach ($pinterest_ids as $pid): ?>
<img height="1" width="1" style="display:none;" alt="" src="https://ct.pinterest.com/v3/?event=init&tid=<?php echo esc_attr($pid); ?>&noscript=1" />
<?php endforeach; ?>
</noscript>
                <?php
            }

            // 6. Bing / Microsoft Advertising UET Tag (Múltiplos IDs)
            if (!empty($bing_ids)) {
                ?>
<!-- Microsoft Advertising UET Tag by WP Pixel & Tracking Pro -->
<script>
(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"<?php echo esc_js($bing_ids[0]); ?>", enableAutoSpaTracking: true};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");
</script>
                <?php
            }

            // 7. Custom Head Scripts
            if (!empty($head_scripts)) {
                $parsed = class_exists('WPPixelTracker_SuperPack') ? WPPixelTracker_SuperPack::parse_dynamic_tags($head_scripts) : $head_scripts;
                echo "\n<!-- Custom Head Scripts -->\n" . $parsed . "\n<!-- End Custom Head Scripts -->\n";
            }
        }

        public function inject_amp_head_pixels() {
            if (get_option('wpps_enable_amp', 'no') !== 'yes') {
                return;
            }
            if (class_exists('WPPixelTracker_SuperPack') && WPPixelTracker_SuperPack::is_pixels_disabled_for_page()) {
                return;
            }

            $raw_fb_ids = get_option('wpps_fb_pixel_id', '');
            $raw_ga4_ids = get_option('wpps_ga4_id', '');
            $fb_ids = array_filter(array_map('trim', explode(',', $raw_fb_ids)));
            $ga4_ids = array_filter(array_map('trim', explode(',', $raw_ga4_ids)));

            if (!empty($ga4_ids)) {
                ?>
                <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
                <amp-analytics type="googleanalytics" id="analytics1">
                <script type="application/json">
                {
                    "vars": {
                        "account": "<?php echo esc_js($ga4_ids[0]); ?>"
                    },
                    "triggers": {
                        "trackPageview": {
                            "on": "visible",
                            "request": "pageview"
                        }
                    }
                }
                </script>
                </amp-analytics>
                <?php
            }

            if (!empty($fb_ids)) {
                ?>
                <amp-pixel src="https://www.facebook.com/tr?id=<?php echo esc_attr($fb_ids[0]); ?>&ev=PageView&noscript=1"></amp-pixel>
                <?php
            }
        }

        public function inject_footer_scripts() {
            if (class_exists('WPPixelTracker_SuperPack') && WPPixelTracker_SuperPack::is_pixels_disabled_for_page()) {
                return;
            }

            $footer_scripts = get_option('wpps_footer_scripts', '');
            if (!empty($footer_scripts)) {
                $parsed = class_exists('WPPixelTracker_SuperPack') ? WPPixelTracker_SuperPack::parse_dynamic_tags($footer_scripts) : $footer_scripts;
                echo "\n<!-- Custom Footer Scripts -->\n" . $parsed . "\n<!-- End Custom Footer Scripts -->\n";
            }
        }

        public function track_woocommerce_view_content() {
            if (class_exists('WPPixelTracker_SuperPack') && WPPixelTracker_SuperPack::is_pixels_disabled_for_page()) return;
            if (!function_exists('is_product') || !is_product()) return;

            global $post;
            if (!is_object($post) || !isset($post->ID)) return;
            if (!function_exists('wc_get_product')) return;

            $product = wc_get_product($post->ID);
            if (!$product || !is_object($product)) return;

            $price = method_exists($product, 'get_price') ? $product->get_price() : 0;
            $currency = function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'BRL';
            $name = method_exists($product, 'get_name') ? $product->get_name() : '';
            $sku = (method_exists($product, 'get_sku') && $product->get_sku()) ? $product->get_sku() : $post->ID;
            $event_id = 'view_content_' . $post->ID . '_' . time();

            $custom_data = array(
                'content_name' => $name,
                'content_ids' => array(strval($sku)),
                'content_type' => 'product',
                'value' => floatval($price),
                'currency' => $currency
            );

            if (class_exists('WPPixelTracker_CAPI')) {
                WPPixelTracker_CAPI::send_event('ViewContent', $custom_data, $event_id);
            }
            if (class_exists('WPPixelTracker_DB')) {
                WPPixelTracker_DB::log_event_fire('ViewContent', 'all', isset($_COOKIE['wpps_utm_source']) ? sanitize_text_field($_COOKIE['wpps_utm_source']) : 'Direto');
            }

            ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: '<?php echo esc_js($name); ?>',
            content_ids: ['<?php echo esc_js($sku); ?>'],
            content_type: 'product',
            value: <?php echo floatval($price); ?>,
            currency: '<?php echo esc_js($currency); ?>'
        }, {eventID: '<?php echo esc_js($event_id); ?>'});
    }

    if (typeof gtag !== 'undefined') {
        gtag('event', 'view_item', {
            currency: '<?php echo esc_js($currency); ?>',
            value: <?php echo floatval($price); ?>,
            items: [{ item_id: '<?php echo esc_js($sku); ?>', item_name: '<?php echo esc_js($name); ?>', price: <?php echo floatval($price); ?> }]
        });
    }

    if (typeof rdt !== 'undefined') {
        rdt('track', 'ViewContent', {
            itemCount: 1,
            value: <?php echo floatval($price); ?>,
            currency: '<?php echo esc_js($currency); ?>'
        });
    }

    if (typeof pintrk !== 'undefined') {
        pintrk('track', 'PageVisit', {
            product_id: '<?php echo esc_js($sku); ?>',
            product_name: '<?php echo esc_js($name); ?>',
            value: <?php echo floatval($price); ?>,
            currency: '<?php echo esc_js($currency); ?>'
        });
    }

    if (typeof window.uetq !== 'undefined') {
        window.uetq.push('event', 'view_item', {
            'revenue_value': <?php echo floatval($price); ?>,
            'currency': '<?php echo esc_js($currency); ?>'
        });
    }
});
</script>
            <?php
        }

        public function track_woocommerce_purchase($order_id) {
            if (class_exists('WPPixelTracker_SuperPack') && WPPixelTracker_SuperPack::is_pixels_disabled_for_page()) return;
            if (!$order_id) return;
            if (!function_exists('wc_get_order')) return;

            $order = wc_get_order($order_id);
            if (!$order || !is_object($order)) return;

            $total = method_exists($order, 'get_total') ? $order->get_total() : 0;
            $currency = method_exists($order, 'get_currency') ? $order->get_currency() : 'BRL';
            $event_id = 'purchase_order_' . $order_id;

            $custom_data = array(
                'value' => floatval($total),
                'currency' => $currency
            );

            if (class_exists('WPPixelTracker_CAPI')) {
                WPPixelTracker_CAPI::send_event('Purchase', $custom_data, $event_id);
            }
            if (class_exists('WPPixelTracker_DB')) {
                WPPixelTracker_DB::log_event_fire('Purchase', 'all', isset($_COOKIE['wpps_utm_source']) ? sanitize_text_field($_COOKIE['wpps_utm_source']) : 'Direto');
            }

            ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', {
            value: <?php echo floatval($total); ?>,
            currency: '<?php echo esc_js($currency); ?>'
        }, {eventID: '<?php echo esc_js($event_id); ?>'});
    }

    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase', {
            transaction_id: '<?php echo esc_js($order_id); ?>',
            value: <?php echo floatval($total); ?>,
            currency: '<?php echo esc_js($currency); ?>'
        });
    }

    if (typeof ttq !== 'undefined') {
        ttq.track('CompletePayment', {
            value: <?php echo floatval($total); ?>,
            currency: '<?php echo esc_js($currency); ?>'
        });
    }

    if (typeof rdt !== 'undefined') {
        rdt('track', 'Purchase', {
            transactionId: '<?php echo esc_js($order_id); ?>',
            value: <?php echo floatval($total); ?>,
            currency: '<?php echo esc_js($currency); ?>'
        });
    }

    if (typeof pintrk !== 'undefined') {
        pintrk('track', 'Checkout', {
            value: <?php echo floatval($total); ?>,
            order_quantity: 1,
            currency: '<?php echo esc_js($currency); ?>'
        });
    }

    if (typeof window.uetq !== 'undefined') {
        window.uetq.push('event', 'purchase', {
            'revenue_value': <?php echo floatval($total); ?>,
            'currency': '<?php echo esc_js($currency); ?>'
        });
    }
});
</script>
            <?php
        }

        public function track_custom_thank_you_page() {
            if (class_exists('WPPixelTracker_SuperPack') && WPPixelTracker_SuperPack::is_pixels_disabled_for_page()) return;
            if (class_exists('WPPixelTracker_SuperPack') && !WPPixelTracker_SuperPack::is_custom_thank_you_page()) return;

            $event_id = 'custom_purchase_' . time();
            if (class_exists('WPPixelTracker_CAPI')) {
                WPPixelTracker_CAPI::send_event('Purchase', array(), $event_id);
            }
            if (class_exists('WPPixelTracker_DB')) {
                WPPixelTracker_DB::log_event_fire('Purchase', 'all', isset($_COOKIE['wpps_utm_source']) ? sanitize_text_field($_COOKIE['wpps_utm_source']) : 'Direto');
            }

            ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', {}, {eventID: '<?php echo esc_js($event_id); ?>'});
    }
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase', {});
    }
    if (typeof ttq !== 'undefined') {
        ttq.track('CompletePayment', {});
    }
    if (typeof rdt !== 'undefined') {
        rdt('track', 'Purchase', {});
    }
    if (typeof pintrk !== 'undefined') {
        pintrk('track', 'Checkout', {});
    }
    if (typeof window.uetq !== 'undefined') {
        window.uetq.push('event', 'purchase', {});
    }
});
</script>
            <?php
        }

        /**
         * Easy Digital Downloads (EDD) View Content
         */
        public function track_edd_view_content() {
            if (get_option('wpps_enable_edd', 'yes') !== 'yes') return;
            if (class_exists('WPPixelTracker_SuperPack') && WPPixelTracker_SuperPack::is_pixels_disabled_for_page()) return;
            if (!function_exists('edd_get_download_price') || !is_singular('download')) return;

            global $post;
            if (!$post || !isset($post->ID)) return;

            $price = floatval(edd_get_download_price($post->ID));
            $currency = function_exists('edd_get_currency') ? edd_get_currency() : 'BRL';
            $title = get_the_title($post->ID);

            ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', { content_name: '<?php echo esc_js($title); ?>', content_ids: ['<?php echo esc_js($post->ID); ?>'], value: <?php echo $price; ?>, currency: '<?php echo esc_js($currency); ?>' });
    }
    if (typeof gtag !== 'undefined') {
        gtag('event', 'view_item', { currency: '<?php echo esc_js($currency); ?>', value: <?php echo $price; ?>, items: [{ item_id: '<?php echo esc_js($post->ID); ?>', item_name: '<?php echo esc_js($title); ?>' }] });
    }
    if (typeof rdt !== 'undefined') {
        rdt('track', 'ViewContent', { value: <?php echo $price; ?>, currency: '<?php echo esc_js($currency); ?>' });
    }
    if (typeof pintrk !== 'undefined') {
        pintrk('track', 'PageVisit', { product_id: '<?php echo esc_js($post->ID); ?>', product_name: '<?php echo esc_js($title); ?>', value: <?php echo $price; ?> });
    }
});
</script>
            <?php
        }

        /**
         * Easy Digital Downloads (EDD) Purchase / Receipt
         */
        public function track_edd_receipt($payment) {
            if (get_option('wpps_enable_edd', 'yes') !== 'yes') return;
            if (class_exists('WPPixelTracker_SuperPack') && WPPixelTracker_SuperPack::is_pixels_disabled_for_page()) return;

            $payment_id = is_object($payment) && isset($payment->ID) ? $payment->ID : (is_numeric($payment) ? $payment : 0);
            if (!$payment_id) return;

            $total = function_exists('edd_get_payment_amount') ? floatval(edd_get_payment_amount($payment_id)) : 0;
            $currency = function_exists('edd_get_payment_currency_code') ? edd_get_payment_currency_code($payment_id) : 'BRL';
            $event_id = 'edd_purchase_' . $payment_id;

            if (class_exists('WPPixelTracker_DB')) {
                WPPixelTracker_DB::log_event_fire('Purchase', 'all', isset($_COOKIE['wpps_utm_source']) ? sanitize_text_field($_COOKIE['wpps_utm_source']) : 'Direto');
            }

            ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', { value: <?php echo $total; ?>, currency: '<?php echo esc_js($currency); ?>' }, {eventID: '<?php echo esc_js($event_id); ?>'});
    }
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase', { transaction_id: '<?php echo esc_js($payment_id); ?>', value: <?php echo $total; ?>, currency: '<?php echo esc_js($currency); ?>' });
    }
    if (typeof ttq !== 'undefined') {
        ttq.track('CompletePayment', { value: <?php echo $total; ?>, currency: '<?php echo esc_js($currency); ?>' });
    }
    if (typeof rdt !== 'undefined') {
        rdt('track', 'Purchase', { transactionId: '<?php echo esc_js($payment_id); ?>', value: <?php echo $total; ?> });
    }
    if (typeof pintrk !== 'undefined') {
        pintrk('track', 'Checkout', { value: <?php echo $total; ?>, currency: '<?php echo esc_js($currency); ?>' });
    }
    if (typeof window.uetq !== 'undefined') {
        window.uetq.push('event', 'purchase', { 'revenue_value': <?php echo $total; ?>, 'currency': '<?php echo esc_js($currency); ?>' });
    }
});
</script>
            <?php
        }
    }
}
