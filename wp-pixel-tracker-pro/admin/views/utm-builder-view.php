<?php
/**
 * UTM Builder & Templates de Campanhas (100% em Português do Brasil)
 */

if (!defined('ABSPATH')) {
    exit;
}

$site_url = site_url('/');
?>

<div class="wpps-wrap">
    <div class="wpps-header">
        <div class="wpps-header-title">
            <h2><span class="dashicons dashicons-tag"></span> UTM Builder & Templates de Rastreamento</h2>
            <p>Gere parâmetros de UTM para suas campanhas e mantenha a origem rastreada até a finalização da compra.</p>
        </div>
    </div>

    <!-- Seção 1: Templates de UTMs -->
    <div class="wpps-box mb-4">
        <div class="wpps-box-header">
            <h3><span class="dashicons dashicons-layout"></span> Templates de UTMs Pré-Configurados</h3>
        </div>
        <div class="wpps-box-body">
            <div class="wpps-template-item mb-3">
                <label><strong>Meta (Facebook)</strong> - Parâmetros de URL:</label>
                <div class="wpps-copy-input-box">
                    <input type="text" readonly value="utm_source=facebook&utm_medium=paid&utm_campaign={{campaign.name}}&utm_term={{adset.name}}&utm_content={{ad.name}}&fbadid={{ad.id}}" class="wpps-input-text font-mono wpps-template-val" />
                    <button type="button" class="wpps-btn wpps-btn-secondary wpps-copy-template-btn" title="Copiar Template Meta">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                </div>
            </div>

            <div class="wpps-template-item mb-3">
                <label><strong>Google Ads</strong> - Template de Rastreamento:</label>
                <div class="wpps-copy-input-box">
                    <input type="text" readonly value="{lpurl}?utm_source=google&utm_medium=paid&utm_campaign={campaignid}&utm_content={adgroupid}&utm_term={keyword}&gadid={creative}" class="wpps-input-text font-mono wpps-template-val" />
                    <button type="button" class="wpps-btn wpps-btn-secondary wpps-copy-template-btn" title="Copiar Template Google Ads">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                </div>
            </div>

            <div class="wpps-template-item mb-3">
                <label><strong>TikTok</strong> - Parâmetros de URL:</label>
                <div class="wpps-copy-input-box">
                    <input type="text" readonly value="?utm_source=tiktok&utm_medium=paid&utm_campaign=__CAMPAIGN_NAME__&utm_term=__AID_NAME__&utm_content=__CID_NAME__&ttadid=__CID__" class="wpps-input-text font-mono wpps-template-val" />
                    <button type="button" class="wpps-btn wpps-btn-secondary wpps-copy-template-btn" title="Copiar Template TikTok">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                </div>
            </div>

            <div class="wpps-template-item mb-3">
                <label><strong>Pinterest</strong> - Parâmetros de URL:</label>
                <div class="wpps-copy-input-box">
                    <input type="text" readonly value="?utm_source=pinterest&utm_medium=paid&utm_campaign={campaign_name}&utm_term={adgroup_name}&utm_content={creative_id}&padid={adid}" class="wpps-input-text font-mono wpps-template-val" />
                    <button type="button" class="wpps-btn wpps-btn-secondary wpps-copy-template-btn" title="Copiar Template Pinterest">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                </div>
            </div>

            <div class="wpps-template-item">
                <label><strong>Bing</strong> - Parâmetros de URL:</label>
                <div class="wpps-copy-input-box">
                    <input type="text" readonly value="{lpurl}?utm_source=bing&utm_medium=paid&utm_campaign={campaign}&utm_content={AdGroupId}&utm_term={AdGroup}&bingid={CampaignId}" class="wpps-input-text font-mono wpps-template-val" />
                    <button type="button" class="wpps-btn wpps-btn-secondary wpps-copy-template-btn" title="Copiar Template Bing">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção 2: Construtor Interativo de UTMs -->
    <div class="wpps-box">
        <div class="wpps-box-header">
            <h3><span class="dashicons dashicons-edit"></span> Construtor Interativo de UTMs</h3>
        </div>
        <div class="wpps-box-body">
            <div class="wpps-form-group">
                <label for="wpps-builder-url">URL do Seu Site:</label>
                <input type="text" id="wpps-builder-url" value="<?php echo esc_url($site_url); ?>" class="wpps-input-text wpps-builder-trigger" />
            </div>

            <div class="wpps-form-group">
                <label for="wpps-builder-source">utm_source (Origem):</label>
                <input type="text" id="wpps-builder-source" placeholder="ex: facebook, google, newsletter" class="wpps-input-text wpps-builder-trigger" />
            </div>

            <div class="wpps-form-group">
                <label for="wpps-builder-medium">utm_medium (Meio):</label>
                <input type="text" id="wpps-builder-medium" placeholder="ex: paid, cpc, bio, email" class="wpps-input-text wpps-builder-trigger" />
            </div>

            <div class="wpps-form-group">
                <label for="wpps-builder-campaign">utm_campaign (Campanha):</label>
                <input type="text" id="wpps-builder-campaign" placeholder="ex: lancamento_julho, black_friday" class="wpps-input-text wpps-builder-trigger" />
            </div>

            <div class="wpps-form-group">
                <label for="wpps-builder-content">utm_content (Conteúdo):</label>
                <input type="text" id="wpps-builder-content" placeholder="ex: anuncio_video_01, banner_azul" class="wpps-input-text wpps-builder-trigger" />
            </div>

            <div class="wpps-form-group">
                <label for="wpps-builder-term">utm_term (Termo):</label>
                <input type="text" id="wpps-builder-term" placeholder="ex: palavra_chave, publico_lookalike" class="wpps-input-text wpps-builder-trigger" />
            </div>

            <hr style="margin: 20px 0; border: none; border-top: 1px solid #e2e8f0;" />

            <!-- Saída: URL Final com UTMs -->
            <div class="wpps-form-group">
                <label>URL Final com UTMs:</label>
                <div class="wpps-copy-input-box">
                    <input type="text" readonly id="wpps-output-full-url" value="<?php echo esc_url($site_url); ?>" class="wpps-input-text font-mono" style="background:#f1f5f9;" />
                    <button type="button" class="wpps-btn wpps-btn-secondary wpps-copy-output-btn" data-target="#wpps-output-full-url" title="Copiar URL completa">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                </div>
            </div>

            <!-- Saída: Apenas os Parâmetros -->
            <div class="wpps-form-group">
                <label>Apenas os Parâmetros de UTM:</label>
                <div class="wpps-copy-input-box">
                    <input type="text" readonly id="wpps-output-params-only" value="" class="wpps-input-text font-mono" style="background:#f1f5f9;" />
                    <button type="button" class="wpps-btn wpps-btn-secondary wpps-copy-output-btn" data-target="#wpps-output-params-only" title="Copiar Apenas Parâmetros">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
