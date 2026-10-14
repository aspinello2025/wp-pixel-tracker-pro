/**
 * Admin Panel JavaScript Logic for WP Pixel & Tracking Pro
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initAnalyticsCharts();
        initSaveSettings();
        initCustomEventsModal();
        initLicenseActivation();
        initUTMBuilderJS();
        initClearLogs();
    });

    function initClearLogs() {
        $('#wpps-clear-logs').on('click', function(e) {
            e.preventDefault();
            if (confirm('Tem certeza de que deseja apagar todos os relatórios e estatísticas? Esta ação não pode ser desfeita.')) {
                var btn = $(this);
                btn.text('Limpando...');
                $.post(WPPixelVars.ajax_url, { action: 'wpps_clear_logs' }, function(res) {
                    if (res.success) {
                        alert(res.data.message);
                        location.reload();
                    } else {
                        alert('Erro ao apagar logs.');
                        btn.text('Zerar Relatórios');
                    }
                });
            }
        });
    }

    /**
     * Analytics Charts Initialization (Chart.js)
     */
    function initAnalyticsCharts() {
        if (!window.WPPixelAnalyticsData) return;

        var data = window.WPPixelAnalyticsData;

        // 1. Line Chart: Daily Events
        var dailyCanvas = document.getElementById('wppsEventsChart');
        if (dailyCanvas && data.dailyChart) {
            new Chart(dailyCanvas, {
                type: 'line',
                data: {
                    labels: data.dailyChart.labels,
                    datasets: [{
                        label: 'Disparo de Eventos',
                        data: data.dailyChart.totals,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // 2. Donut Chart: Top UTM Sources
        var sourcesCanvas = document.getElementById('wppsUtmSourcesChart');
        if (sourcesCanvas && data.sources) {
            var srcLabels = data.sources.map(function(item) { return item.utm_source; });
            var srcCounts = data.sources.map(function(item) { return item.count; });

            new Chart(sourcesCanvas, {
                type: 'doughnut',
                data: {
                    labels: srcLabels.length > 0 ? srcLabels : ['Direto / Organico'],
                    datasets: [{
                        data: srcCounts.length > 0 ? srcCounts : [1],
                        backgroundColor: ['#6366f1', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // 3. Bar Chart: Top Events Fired
        var eventsCanvas = document.getElementById('wppsTopEventsChart');
        if (eventsCanvas && data.events) {
            var evLabels = data.events.map(function(item) { return item.event_name; });
            var evCounts = data.events.map(function(item) { return item.count; });

            new Chart(eventsCanvas, {
                type: 'bar',
                data: {
                    labels: evLabels,
                    datasets: [{
                        label: 'Quantidade',
                        data: evCounts,
                        backgroundColor: '#8b5cf6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }
    }

    /**
     * UTM Builder Page Live Calculations & Copy Listeners
     */
    function initUTMBuilderJS() {
        $('.wpps-copy-template-btn').on('click', function() {
            var btn = $(this);
            var text = btn.siblings('input').val();
            copyToClipboard(text, btn);
        });

        $('.wpps-copy-output-btn').on('click', function() {
            var btn = $(this);
            var targetSelector = btn.attr('data-target');
            var text = $(targetSelector).val();
            copyToClipboard(text, btn);
        });

        $('.wpps-builder-trigger').on('input keyup change', function() {
            updateUTMOutputs();
        });
    }

    function updateUTMOutputs() {
        var url = $('#wpps-builder-url').val();
        if (!url) {
            $('#wpps-output-full-url').val('');
            $('#wpps-output-params-only').val('');
            return;
        }

        url = url.trim();
        var source = $('#wpps-builder-source').val().trim();
        var medium = $('#wpps-builder-medium').val().trim();
        var campaign = $('#wpps-builder-campaign').val().trim();
        var content = $('#wpps-builder-content').val().trim();
        var term = $('#wpps-builder-term').val().trim();

        var params = [];
        if (source) params.push('utm_source=' + encodeURIComponent(source));
        if (medium) params.push('utm_medium=' + encodeURIComponent(medium));
        if (campaign) params.push('utm_campaign=' + encodeURIComponent(campaign));
        if (content) params.push('utm_content=' + encodeURIComponent(content));
        if (term) params.push('utm_term=' + encodeURIComponent(term));

        var queryString = params.join('&');

        if (queryString) {
            var separator = url.indexOf('?') !== -1 ? '&' : '?';
            $('#wpps-output-full-url').val(url + separator + queryString);
            $('#wpps-output-params-only').val(separator + queryString);
        } else {
            $('#wpps-output-full-url').val(url);
            $('#wpps-output-params-only').val('');
        }
    }

    function copyToClipboard(text, btn) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(function() {
            var origHtml = btn.html();
            btn.html('<span class="dashicons dashicons-yes" style="color:#10b981;"></span>');
            setTimeout(function() {
                btn.html(origHtml);
            }, 1500);
        });
    }

    /**
     * 1. Save Settings Form AJAX
     */
    function initSaveSettings() {
        $('#wpps-save-settings-btn').on('click', function(e) {
            e.preventDefault();

            var btn = $(this);
            var formData = $('#wpps-settings-form').serialize();
            formData += '&action=wpps_save_settings&nonce=' + WPPixelVars.nonce;

            btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Salvando...');

            $.post(WPPixelVars.ajax_url, formData, function(res) {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Salvar Configurações');

                if (res.success) {
                    alert(res.data.message);
                    location.reload();
                } else {
                    alert(res.data.message || 'Erro ao salvar as configurações.');
                }
            }).fail(function() {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Salvar Configurações');
                alert('Erro de conexão ao salvar.');
            });
        });
    }

    /**
     * 2. Custom Events Modal & Actions
     */
    function initCustomEventsModal() {
        $('#wpps-open-event-modal').on('click', function(e) {
            e.preventDefault();
            resetEventForm();
            $('#wpps-event-modal-title').text('Criar Evento Customizado');
            $('#wpps-event-modal').css('display', 'flex');
        });

        $('#wpps-event-modal-close, #wpps-event-cancel').on('click', function() {
            $('#wpps-event-modal').hide();
        });

        $('.wpps-modal-overlay').on('click', function(e) {
            if ($(e.target).hasClass('wpps-modal-overlay')) {
                $(this).hide();
            }
        });

        $('#wpps-event-form').on('submit', function(e) {
            e.preventDefault();

            var btn = $('#wpps-event-submit');
            var formData = $(this).serialize();
            formData += '&action=wpps_save_custom_event&nonce=' + WPPixelVars.nonce;

            btn.prop('disabled', true).text('Salvando...');

            $.post(WPPixelVars.ajax_url, formData, function(res) {
                btn.prop('disabled', false).text('Salvar Evento');

                if (res.success) {
                    alert(res.data.message);
                    location.reload();
                } else {
                    alert(res.data.message || 'Erro ao salvar o evento.');
                }
            });
        });

        $('.wpps-edit-event-btn').on('click', function() {
            var rawData = $(this).attr('data-event');
            if (!rawData) return;

            var ev = JSON.parse(rawData);
            resetEventForm();

            $('#wpps-event-modal-title').text('Editar Evento: ' + ev.title);
            $('#wpps-event-id').val(ev.id);
            $('#wpps-event-title').val(ev.title);
            $('#wpps-event-name').val(ev.event_name);
            $('#wpps-event-provider').val(ev.provider);
            $('#wpps-event-trigger-type').val(ev.trigger_type);
            $('#wpps-event-trigger-value').val(ev.trigger_value);
            $('#wpps-event-value').val(ev.event_value);
            $('#wpps-event-currency').val(ev.currency);

            $('#wpps-event-modal').css('display', 'flex');
        });

        $('.wpps-delete-event-btn').on('click', function() {
            var btn = $(this);
            var id = btn.attr('data-id');
            var title = btn.attr('data-title');

            if (!confirm('Deseja realmente excluir o evento "' + title + '"?')) return;

            $.post(WPPixelVars.ajax_url, {
                action: 'wpps_delete_custom_event',
                nonce: WPPixelVars.nonce,
                id: id
            }, function(res) {
                if (res.success) {
                    btn.closest('tr').fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert(res.data.message || 'Erro ao excluir.');
                }
            });
        });
    }

    function resetEventForm() {
        $('#wpps-event-form')[0].reset();
        $('#wpps-event-id').val('0');
    }

    /**
     * 3. License Activation
     */
    function initLicenseActivation() {
        $('#wpps-license-form').on('submit', function(e) {
            e.preventDefault();

            var key = $('#wpps-license-key-input').val().trim();
            var btn = $('#wpps-activate-btn');

            if (!key) {
                alert('Digite a chave de ativação.');
                return;
            }

            btn.prop('disabled', true).text('Validando Chave...');

            $.post(WPPixelVars.ajax_url, {
                action: 'wpps_activate_license',
                nonce: WPPixelVars.nonce,
                license_key: key
            }, function(res) {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-lock"></span> Validar e Ativar Plugin');

                if (res.success) {
                    alert(res.data.message);
                    location.reload();
                } else {
                    alert(res.data.message || 'Erro ao validar licença.');
                }
            });
        });
    }

})(jQuery);
