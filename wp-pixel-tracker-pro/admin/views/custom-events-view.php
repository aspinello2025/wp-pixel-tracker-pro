<?php
/**
 * Custom Events View for WP Pixel & Tracking Pro (100% em Português do Brasil)
 */

if (!defined('ABSPATH')) {
    exit;
}

$events = WPPixelTracker_DB::get_all_events();
?>

<div class="wpps-wrap">
    <div class="wpps-header">
        <div class="wpps-header-title">
            <h2><span class="dashicons dashicons-flag"></span> Criador Visual de Eventos Customizados</h2>
            <p>Dispare eventos de conversão por clique em botões, rolagem de página, tempo de permanência ou URLs específicas.</p>
        </div>
        <div class="wpps-header-actions">
            <button type="button" class="wpps-btn wpps-btn-primary" id="wpps-open-event-modal">+ Criar Novo Evento</button>
        </div>
    </div>

    <div class="wpps-box">
        <div class="wpps-box-body p-0">
            <table class="wpps-table">
                <thead>
                    <tr>
                        <th>Título da Regra</th>
                        <th>Nome do Evento</th>
                        <th>Tipo de Gatilho</th>
                        <th>Valor do Gatilho</th>
                        <th>Provedor</th>
                        <th>Valor de Conversão</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">Nenhum evento customizado criado. Clique em "+ Criar Novo Evento" para começar!</td>
                        </tr>
                    <?php else: foreach ($events as $ev): ?>
                        <tr>
                            <td><strong><?php echo esc_html($ev->title); ?></strong></td>
                            <td><span class="wpps-badge wpps-badge-purple"><?php echo esc_html($ev->event_name); ?></span></td>
                            <td>
                                <?php 
                                    $types = array('click' => 'Clique em Elemento', 'scroll' => 'Rolagem de Página (%)', 'delay' => 'Tempo de Permanência (s)', 'url' => 'Visualização de URL');
                                    echo isset($types[$ev->trigger_type]) ? $types[$ev->trigger_type] : $ev->trigger_type;
                                ?>
                            </td>
                            <td><code><?php echo esc_html($ev->trigger_value); ?></code></td>
                            <td><span class="wpps-badge wpps-badge-info"><?php echo esc_html(strtoupper($ev->provider)); ?></span></td>
                            <td>
                                <?php echo $ev->event_value > 0 ? esc_html($ev->currency . ' ' . number_format($ev->event_value, 2, ',', '.')) : '-'; ?>
                            </td>
                            <td class="text-right">
                                <button class="wpps-btn-icon wpps-edit-event-btn" data-event='<?php echo esc_attr(json_encode($ev)); ?>' title="Editar Evento">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button class="wpps-btn-icon wpps-delete-event-btn" data-id="<?php echo $ev->id; ?>" data-title="<?php echo esc_attr($ev->title); ?>" title="Excluir Evento">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Criar / Editar Evento Customizado -->
<div class="wpps-modal-overlay" id="wpps-event-modal">
    <div class="wpps-modal-content">
        <div class="wpps-modal-header">
            <h3 id="wpps-event-modal-title">Criar Evento Customizado</h3>
            <button class="wpps-modal-close" id="wpps-event-modal-close">&times;</button>
        </div>
        <form id="wpps-event-form">
            <input type="hidden" name="id" id="wpps-event-id" value="0" />

            <div class="wpps-form-grid">
                <div class="wpps-form-group wpps-col-12">
                    <label for="wpps-event-title">Título da Regra *</label>
                    <input type="text" name="title" id="wpps-event-title" placeholder="Ex: Clique no Botão de WhatsApp" required class="wpps-input-text" />
                </div>

                <div class="wpps-form-group wpps-col-6">
                    <label for="wpps-event-name">Nome do Evento de Conversão *</label>
                    <input type="text" name="event_name" id="wpps-event-name" placeholder="ex: Lead, Contact, Purchase, CustomClick" required class="wpps-input-text font-mono" />
                </div>

                <div class="wpps-form-group wpps-col-6">
                    <label for="wpps-event-provider">Provedor Destino</label>
                    <select name="provider" id="wpps-event-provider" class="wpps-select">
                        <option value="all">Todos os Pixels (Meta, GA4, TikTok)</option>
                        <option value="meta">Apenas Meta Pixel (Facebook)</option>
                        <option value="ga4">Apenas Google Analytics 4</option>
                        <option value="tiktok">Apenas TikTok Pixel</option>
                    </select>
                </div>

                <div class="wpps-form-group wpps-col-6">
                    <label for="wpps-event-trigger-type">Tipo de Gatilho (Trigger)</label>
                    <select name="trigger_type" id="wpps-event-trigger-type" class="wpps-select">
                        <option value="click">Clique em Elemento (Seletor CSS ou Classe)</option>
                        <option value="scroll">Rolagem de Página (Porcentagem %)</option>
                        <option value="delay">Tempo de Permanência (Segundos)</option>
                        <option value="url">Visualização de URL Específica</option>
                    </select>
                </div>

                <div class="wpps-form-group wpps-col-6">
                    <label for="wpps-event-trigger-value">Valor do Gatilho *</label>
                    <input type="text" name="trigger_value" id="wpps-event-trigger-value" placeholder="ex: .btn-whatsapp ou 50 ou 5" required class="wpps-input-text font-mono" />
                    <small id="wpps-trigger-help" class="text-muted">Informe o seletor CSS (ex: .btn-comprar), a porcentagem de scroll (ex: 50) ou o tempo em segundos.</small>
                </div>

                <div class="wpps-form-group wpps-col-6">
                    <label for="wpps-event-value">Valor da Conversão (Opcional)</label>
                    <input type="number" step="0.01" name="event_value" id="wpps-event-value" placeholder="0.00" class="wpps-input-text" />
                </div>

                <div class="wpps-form-group wpps-col-6">
                    <label for="wpps-event-currency">Moeda</label>
                    <select name="currency" id="wpps-event-currency" class="wpps-select">
                        <option value="BRL">BRL - Real Brasileiro</option>
                        <option value="USD">USD - Dólar Americano</option>
                        <option value="EUR">EUR - Euro</option>
                    </select>
                </div>
            </div>

            <div class="wpps-modal-footer">
                <button type="button" class="wpps-btn wpps-btn-secondary" id="wpps-event-cancel">Cancelar</button>
                <button type="submit" class="wpps-btn wpps-btn-primary" id="wpps-event-submit">Salvar Evento</button>
            </div>
        </form>
    </div>
</div>
