/**
 * Front-end Custom Event Tracker & Universal Checkout UTM Propagator
 */

(function() {
    'use strict';

    if (typeof WPPixelFrontendVars === 'undefined') {
        return;
    }

    var utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'fbadid', 'gadid', 'ttadid'];

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Universal Checkout & Form UTM Propagation
        propagateUTMsToFormsAndLinks();

        // 2. Custom Event Listeners
        if (WPPixelFrontendVars.custom_events) {
            WPPixelFrontendVars.custom_events.forEach(function(ev) {
                setupTrigger(ev);
            });
        }

        // 3. Automatic Events
        initAutomaticEvents();
    });

    function initAutomaticEvents() {
        // Track Forms
        if (WPPixelFrontendVars.auto_track_forms === 'yes') {
            document.addEventListener('submit', function(e) {
                if (e.target.tagName === 'FORM') {
                    fireEvent({ event_name: 'Lead', provider: 'all', event_value: 0 });
                }
            });
        }
        
        // Track Login & Signup by sniffing form actions or names
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (form.tagName === 'FORM') {
                var action = form.getAttribute('action') || '';
                var id = form.id || '';
                var cls = form.className || '';
                var isLogin = action.indexOf('wp-login.php') !== -1 || id.indexOf('login') !== -1 || cls.indexOf('login') !== -1;
                var isSignup = action.indexOf('register') !== -1 || id.indexOf('register') !== -1 || cls.indexOf('register') !== -1;
                
                if (isLogin && WPPixelFrontendVars.auto_track_login === 'yes') {
                    fireEvent({ event_name: 'Login', provider: 'all', event_value: 0 });
                } else if (isSignup && WPPixelFrontendVars.auto_track_signup === 'yes') {
                    fireEvent({ event_name: 'CompleteRegistration', provider: 'all', event_value: 0 });
                }
            }
        });
    }

    /**
     * Get Cookie Helper
     */
    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        if (match) return decodeURIComponent(match[2]);
        return '';
    }

    /**
     * Get All Saved UTM Parameters
     */
    function getSavedUTMs() {
        var params = {};
        utmKeys.forEach(function(key) {
            var val = getCookie('wpps_' + key);
            if (!val) {
                try { val = localStorage.getItem('wpps_' + key) || ''; } catch(e) {}
            }
            if (val) {
                params[key] = val;
            }
        });
        return params;
    }

    /**
     * Propagate Saved UTMs to All Checkout Links and Form Fields
     */
    function propagateUTMsToFormsAndLinks() {
        var utms = getSavedUTMs();
        if (Object.keys(utms).length === 0) {
            return;
        }

        // A. Auto-Append Hidden Inputs to Forms
        var forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            for (var key in utms) {
                if (!form.querySelector('input[name="' + key + '"]')) {
                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = key;
                    hiddenInput.value = utms[key];
                    form.appendChild(hiddenInput);
                }
            }
        });

        // B. Auto-Append UTMs to External Checkout Links
        var externalCheckoutDomains = ['kiwify.com.br', 'hotmart.com', 'eduzz.com', 'payt.com.br', 'monetizze.com.br', 'perfectpay.com.br', 'cartpanda.com'];
        var links = document.querySelectorAll('a[href]');

        links.forEach(function(link) {
            var href = link.getAttribute('href');
            if (!href || href.indexOf('javascript:') === 0 || href.indexOf('#') === 0) return;

            var isCheckoutLink = externalCheckoutDomains.some(function(domain) {
                return href.indexOf(domain) !== -1;
            }) || href.indexOf('/checkout') !== -1 || href.indexOf('/buy') !== -1 || href.indexOf('pay.') !== -1;

            if (isCheckoutLink) {
                var queryParts = [];
                for (var key in utms) {
                    if (href.indexOf(key + '=') === -1) {
                        queryParts.push(encodeURIComponent(key) + '=' + encodeURIComponent(utms[key]));
                    }
                }
                if (queryParts.length > 0) {
                    var separator = href.indexOf('?') !== -1 ? '&' : '?';
                    link.setAttribute('href', href + separator + queryParts.join('&'));
                }
            }
        });
    }

    /**
     * Custom Triggers Engine
     */
    function setupTrigger(ev) {
        var type = ev.trigger_type;
        var val = ev.trigger_value;

        if (type === 'click' && val) {
            document.addEventListener('click', function(e) {
                if (e.target.matches(val) || e.target.closest(val)) {
                    fireEvent(ev);
                }
            });
        } else if (type === 'delay' && val) {
            var seconds = parseFloat(val) || 5;
            setTimeout(function() {
                fireEvent(ev);
            }, seconds * 1000);
        } else if (type === 'scroll' && val) {
            var targetPct = parseFloat(val) || 50;
            var fired = false;
            window.addEventListener('scroll', function() {
                if (fired) return;
                var scrollTop = window.scrollY || document.documentElement.scrollTop;
                var docHeight = document.documentElement.scrollHeight - window.innerHeight;
                if (docHeight > 0) {
                    var pct = (scrollTop / docHeight) * 100;
                    if (pct >= targetPct) {
                        fired = true;
                        fireEvent(ev);
                    }
                }
            });
        } else if (type === 'url' && val) {
            if (window.location.pathname.indexOf(val) !== -1 || window.location.href.indexOf(val) !== -1) {
                fireEvent(ev);
            }
        }
    }

    /**
     * Dispatch Event to Pixels & Log to Dashboard
     */
    function fireEvent(ev) {
        var name = ev.event_name;
        var provider = ev.provider || 'all';
        var value = parseFloat(ev.event_value) || 0;
        var currency = ev.currency || 'BRL';

        var params = {};
        if (value > 0) {
            params.value = value;
            params.currency = currency;
        }

        // 1. Meta / Facebook Pixel
        if ((provider === 'all' || provider === 'meta') && typeof fbq !== 'undefined') {
            fbq('trackCustom', name, params);
        }

        // 2. Google Analytics 4
        if ((provider === 'all' || provider === 'ga4') && typeof gtag !== 'undefined') {
            gtag('event', name, params);
        }

        // 3. TikTok Pixel
        if ((provider === 'all' || provider === 'tiktok') && typeof ttq !== 'undefined') {
            ttq.track(name, params);
        }

        // 4. Reddit Pixel
        if ((provider === 'all' || provider === 'reddit') && typeof rdt !== 'undefined') {
            rdt('track', 'Custom', { customEventName: name, value: value, currency: currency });
        }

        // 5. Pinterest Tag
        if ((provider === 'all' || provider === 'pinterest') && typeof pintrk !== 'undefined') {
            pintrk('track', 'custom', { event_name: name, value: value, currency: currency });
        }

        // 6. Bing / Microsoft UET Tag
        if ((provider === 'all' || provider === 'bing') && typeof window.uetq !== 'undefined') {
            window.uetq.push('event', name, { 'revenue_value': value, 'currency': currency });
        }

        // 7. Log to Dashboard Analytics (AJAX Ping)
        logEventToDashboard(name, provider);
    }

    function logEventToDashboard(name, provider) {
        if (typeof WPPixelFrontendVars === 'undefined' || !WPPixelFrontendVars.ajax_url) return;

        var utms = getSavedUTMs();
        var data = new FormData();
        data.append('action', 'wpps_log_event');
        data.append('event_name', name);
        data.append('provider', provider);
        data.append('utm_source', utms.utm_source || 'Direto');
        data.append('utm_campaign', utms.utm_campaign || '');

        navigator.sendBeacon ? navigator.sendBeacon(WPPixelFrontendVars.ajax_url, data) : jQuery.post(WPPixelFrontendVars.ajax_url, {
            action: 'wpps_log_event',
            event_name: name,
            provider: provider,
            utm_source: utms.utm_source || 'Direto',
            utm_campaign: utms.utm_campaign || ''
        });
    }

})();
