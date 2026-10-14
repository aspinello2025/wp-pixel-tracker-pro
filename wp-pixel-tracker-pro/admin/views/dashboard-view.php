<?php
/**
 * Dashboard Analytics & Relatórios WooCommerce (100% em Português do Brasil)
 */

if (!defined('ABSPATH')) {
    exit;
}

$days = isset($_GET['period']) ? intval($_GET['period']) : 30;
$segment = isset($_GET['segment']) ? sanitize_text_field($_GET['segment']) : 'utm_source';
$attribution = isset($_GET['attribution']) ? sanitize_text_field($_GET['attribution']) : 'first_visit';

$wc_reports = WPPixelTracker_DB::get_woocommerce_reports($segment, $days, $attribution);
$daily_chart = WPPixelTracker_DB::get_daily_events_chart($days);
$top_sources = WPPixelTracker_DB::get_top_utm_sources($days);
$top_events = WPPixelTracker_DB::get_top_events($days);

$currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : 'R$';

$segment_labels = array(
    'traffic_source' => 'Origem do Tráfego',
    'landing_page'   => 'Página de Entrada',
    'utm_source'     => 'utm_source',
    'utm_medium'     => 'utm_medium',
    'utm_campaign'   => 'utm_campaign',
    'utm_content'    => 'utm_content',
    'utm_term'       => 'utm_term'
);
?>

<div class="wpps-wrap">
    <div class="wpps-header mb-4">
        <div class="wpps-header-title">
            <h2><span class="dashicons dashicons-chart-area"></span> WP Pixel & Tracking Pro - Relatórios de Atribuição</h2>
            <p>Relatórios de origens de tráfego, ROI de campanhas e inteligência de vendas do seu e-commerce.</p>
        </div>
        <div class="wpps-header-actions">
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp-pixel-tracker&wpps_action=clear_logs'), 'wpps_clear_logs_action'); ?>" class="wpps-btn wpps-btn-danger" style="background-color: #dc3232; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;" onclick="return confirm('Tem certeza de que deseja apagar todos os relatórios e zerar os dados?');">
                <span class="dashicons dashicons-trash"></span> Zerar Relatórios
            </a>
        </div>
    </div>

    <!-- Banner Header em PT-BR -->
    <div class="wpps-reports-banner mb-3">
        <h3>Relatórios WooCommerce & Atribuição de Tráfego</h3>
    </div>

    <!-- Barra de Botões de Filtro de Segmento (100% PT-BR) -->
    <div class="wpps-segment-filter-bar mb-4">
        <a href="<?php echo admin_url('admin.php?page=wp-pixel-tracker&segment=traffic_source&period=' . $days . '&attribution=' . $attribution); ?>" class="wpps-segment-btn <?php echo $segment === 'traffic_source' ? 'active' : ''; ?>">Origem do Tráfego</a>
        <a href="<?php echo admin_url('admin.php?page=wp-pixel-tracker&segment=landing_page&period=' . $days . '&attribution=' . $attribution); ?>" class="wpps-segment-btn <?php echo $segment === 'landing_page' ? 'active' : ''; ?>">Página de Entrada</a>
        <a href="<?php echo admin_url('admin.php?page=wp-pixel-tracker&segment=utm_source&period=' . $days . '&attribution=' . $attribution); ?>" class="wpps-segment-btn <?php echo $segment === 'utm_source' ? 'active' : ''; ?>">utm_source</a>
        <a href="<?php echo admin_url('admin.php?page=wp-pixel-tracker&segment=utm_medium&period=' . $days . '&attribution=' . $attribution); ?>" class="wpps-segment-btn <?php echo $segment === 'utm_medium' ? 'active' : ''; ?>">utm_medium</a>
        <a href="<?php echo admin_url('admin.php?page=wp-pixel-tracker&segment=utm_campaign&period=' . $days . '&attribution=' . $attribution); ?>" class="wpps-segment-btn <?php echo $segment === 'utm_campaign' ? 'active' : ''; ?>">utm_campaign</a>
        <a href="<?php echo admin_url('admin.php?page=wp-pixel-tracker&segment=utm_content&period=' . $days . '&attribution=' . $attribution); ?>" class="wpps-segment-btn <?php echo $segment === 'utm_content' ? 'active' : ''; ?>">utm_content</a>
        <a href="<?php echo admin_url('admin.php?page=wp-pixel-tracker&segment=utm_term&period=' . $days . '&attribution=' . $attribution); ?>" class="wpps-segment-btn <?php echo $segment === 'utm_term' ? 'active' : ''; ?>">utm_term</a>
    </div>

    <!-- Linha de Métricas Financeiras em PT-BR -->
    <div class="wpps-financial-metrics-line text-center mb-4">
        <span><strong><?php echo esc_html(isset($segment_labels[$segment]) ? $segment_labels[$segment] : $segment); ?>:</strong> <?php echo $wc_reports['segment_count']; ?></span>
        <span class="dot-separator">•</span>
        <span><strong>Pedidos:</strong> <?php echo number_format_i18n($wc_reports['orders']); ?></span>
        <span class="dot-separator">•</span>
        <span><strong>Vendas Brutas:</strong> <?php echo $currency_symbol . ' ' . number_format($wc_reports['gross_sale'], 2, ',', '.'); ?></span>
        <span class="dot-separator">•</span>
        <span><strong>Vendas Líquidas:</strong> <?php echo $currency_symbol . ' ' . number_format($wc_reports['net_sale'], 2, ',', '.'); ?></span>
        <span class="dot-separator">•</span>
        <span><strong>Vendas Totais:</strong> <?php echo $currency_symbol . ' ' . number_format($wc_reports['total_sale'], 2, ',', '.'); ?></span>
    </div>

    <!-- Barra de Filtros Dropdown em PT-BR -->
    <div class="wpps-dropdown-filters-bar mb-3">
        <select id="wpps-filter-period" onchange="location = this.value;" class="wpps-select-sm">
            <option value="<?php echo admin_url('admin.php?page=wp-pixel-tracker&period=7&segment=' . $segment . '&attribution=' . $attribution); ?>" <?php selected($days, 7); ?>>Últimos 7 dias</option>
            <option value="<?php echo admin_url('admin.php?page=wp-pixel-tracker&period=30&segment=' . $segment . '&attribution=' . $attribution); ?>" <?php selected($days, 30); ?>>Últimos 30 dias</option>
            <option value="<?php echo admin_url('admin.php?page=wp-pixel-tracker&period=90&segment=' . $segment . '&attribution=' . $attribution); ?>" <?php selected($days, 90); ?>>Últimos 90 dias</option>
        </select>

        <select id="wpps-filter-attribution" onchange="location = this.value;" class="wpps-select-sm">
            <option value="<?php echo admin_url('admin.php?page=wp-pixel-tracker&attribution=first_visit&period=' . $days . '&segment=' . $segment); ?>" <?php selected($attribution, 'first_visit'); ?>>Primeira Visita (Primeiro Clique)</option>
            <option value="<?php echo admin_url('admin.php?page=wp-pixel-tracker&attribution=last_visit&period=' . $days . '&segment=' . $segment); ?>" <?php selected($attribution, 'last_visit'); ?>>Última Visita (Último Clique)</option>
        </select>
    </div>

    <!-- Gráfico Interativo -->
    <div class="wpps-box mb-4">
        <div class="wpps-box-header">
            <h3><span class="dashicons dashicons-chart-line"></span> Tráfego & Eventos ao Longo do Tempo</h3>
        </div>
        <div class="wpps-box-body">
            <div style="position: relative; height: 320px; width: 100%;">
                <canvas id="wppsEventsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabela de Detalhamento em PT-BR -->
    <div class="wpps-box">
        <div class="wpps-box-header">
            <h3><span class="dashicons dashicons-list-view"></span> Detalhamento por <?php echo esc_html(isset($segment_labels[$segment]) ? $segment_labels[$segment] : $segment); ?></h3>
        </div>
        <div class="wpps-box-body p-0">
            <table class="wpps-table">
                <thead>
                    <tr>
                        <th>Valor do Segmento</th>
                        <th>Total de Eventos / Cliques</th>
                        <th>Vendas Totais Estimadas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($wc_reports['data'])): ?>
                        <tr>
                            <td colspan="3" class="text-center py-4">Nenhum dado registrado para este segmento no período selecionado.</td>
                        </tr>
                    <?php else: foreach ($wc_reports['data'] as $row): ?>
                          <tr>
                              <td><strong><?php echo !empty($row->segment_val) ? esc_html($row->segment_val) : 'Direto / Orgânico'; ?></strong></td>
                              <td>
                                  <span class="wpps-badge wpps-badge-info"><?php echo number_format_i18n($row->count); ?> Eventos</span>
                                  <span class="wpps-badge wpps-badge-success"><?php echo number_format_i18n($row->purchases); ?> Compras</span>
                              </td>
                              <td><code><?php echo $currency_symbol . ' ' . number_format($row->purchases * 150.00, 2, ',', '.'); ?></code></td>
                          </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.WPPixelAnalyticsData = {
    dailyChart: <?php echo json_encode($daily_chart); ?>,
    sources: <?php echo json_encode($top_sources); ?>,
    events: <?php echo json_encode($top_events); ?>
};
</script>
