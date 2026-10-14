<?php
/**
 * Pixel Settings View for WP Pixel & Tracking Pro (com Super Pack)
 */

if (!defined('ABSPATH')) {
    exit;
}

$fb_id = get_option('wpps_fb_pixel_id', '');
$fb_capi_token = get_option('wpps_fb_capi_token', '');
$fb_test_code = get_option('wpps_fb_test_code', '');
$ga4_id = get_option('wpps_ga4_id', '');
$tiktok_id = get_option('wpps_tiktok_pixel_id', '');
$gads_id = get_option('wpps_gads_id', '');
$reddit_id = get_option('wpps_reddit_pixel_id', '');
$pinterest_id = get_option('wpps_pinterest_tag_id', '');
$bing_id = get_option('wpps_bing_uet_id', '');
$enable_edd = get_option('wpps_enable_edd', 'yes');
$enable_amp = get_option('wpps_enable_amp', 'no');
$auto_track_forms = get_option('wpps_auto_track_forms', 'yes');
$auto_track_signup = get_option('wpps_auto_track_signup', 'yes');
$auto_track_login = get_option('wpps_auto_track_login', 'yes');
$custom_thankyou_urls = get_option('wpps_custom_thankyou_urls', '');
$head_scripts = get_option('wpps_head_scripts', '');
$footer_scripts = get_option('wpps_footer_scripts', '');
?>

<div class="wpps-wrap">
    <div class="wpps-header">
        <div class="wpps-header-title">
            <h2><span class="dashicons dashicons-chart-line"></span> WP Pixel & Tracking Pro (Super Pack v1.5.0)</h2>
            <p>Gerencie Múltiplos Pixels, Meta CAPI, Páginas de Obrigado Customizadas e Suporte Multilíngue WPML.</p>
        </div>
        <div class="wpps-header-actions">
            <button type="button" class="wpps-btn wpps-btn-primary" id="wpps-save-settings-btn">
                <span class="dashicons dashicons-saved"></span> Salvar Configurações
            </button>
        </div>
    </div>

    <form id="wpps-settings-form">
        <div class="wpps-grid">
            <!-- Meta / Facebook Pixel & CAPI Card -->
            <div class="wpps-card wpps-col-12">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-facebook"></span> Meta Pixel & Conversions API (CAPI Server-Side)</h3>
                    <div style="display: flex; gap: 8px;">
                        <span class="wpps-status-pill <?php echo !empty($fb_id) ? 'wpps-status-on' : 'wpps-status-off'; ?>">
                            Pixel JS: <?php echo !empty($fb_id) ? 'Ativo' : 'Inativo'; ?>
                        </span>
                        <span class="wpps-status-pill <?php echo !empty($fb_capi_token) ? 'wpps-status-on' : 'wpps-status-off'; ?>">
                            Meta CAPI Server-Side: <?php echo !empty($fb_capi_token) ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </div>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-grid">
                        <div class="wpps-form-group wpps-col-6">
                            <label for="fb_pixel_id">Meta Pixel IDs (Suporte a Múltiplos Pixels):</label>
                            <input type="text" id="fb_pixel_id" name="fb_pixel_id" value="<?php echo esc_attr($fb_id); ?>" placeholder="ex: 123456789, 987654321" class="wpps-input-text font-mono" />
                            <small class="text-muted">Separe múltiplos IDs de Pixels por vírgula para disparo simultâneo.</small>
                        </div>

                        <div class="wpps-form-group wpps-col-6">
                            <label for="fb_test_code">Código de Teste CAPI (Opcional):</label>
                            <input type="text" id="fb_test_code" name="fb_test_code" value="<?php echo esc_attr($fb_test_code); ?>" placeholder="ex: TEST12345" class="wpps-input-text font-mono" />
                            <small class="text-muted">Código para testar eventos no Gerenciador de Eventos.</small>
                        </div>

                        <div class="wpps-form-group wpps-col-12">
                            <label for="fb_capi_token">Meta CAPI Access Token (Token de Acesso da API de Conversões):</label>
                            <textarea id="fb_capi_token" name="fb_capi_token" rows="3" class="wpps-textarea font-mono" placeholder="Cole o token de acesso CAPI do Meta aqui..."><?php echo esc_textarea($fb_capi_token); ?></textarea>
                            <small class="text-muted">Gere seu Token em: Gerenciador de Eventos > Configurações > API de Conversões > Gerar token de acesso.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Analytics 4 Card -->
            <div class="wpps-card">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-analytics"></span> Google Analytics 4 (GA4)</h3>
                    <span class="wpps-status-pill <?php echo !empty($ga4_id) ? 'wpps-status-on' : 'wpps-status-off'; ?>">
                        <?php echo !empty($ga4_id) ? 'Ativo' : 'Inativo'; ?>
                    </span>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-group">
                        <label for="ga4_id">GA4 Measurement IDs (Múltiplos IDs):</label>
                        <input type="text" id="ga4_id" name="ga4_id" value="<?php echo esc_attr($ga4_id); ?>" placeholder="ex: G-XXXXXXXXXX, G-YYYYYYYYYY" class="wpps-input-text font-mono" />
                        <small class="text-muted">IDs de medição do GA4 separados por vírgula.</small>
                    </div>
                </div>
            </div>

            <!-- TikTok Pixel Card -->
            <div class="wpps-card">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-video-alt3"></span> TikTok Pixel</h3>
                    <span class="wpps-status-pill <?php echo !empty($tiktok_id) ? 'wpps-status-on' : 'wpps-status-off'; ?>">
                        <?php echo !empty($tiktok_id) ? 'Ativo' : 'Inativo'; ?>
                    </span>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-group">
                        <label for="tiktok_pixel_id">TikTok Pixel IDs (Múltiplos IDs):</label>
                        <input type="text" id="tiktok_pixel_id" name="tiktok_pixel_id" value="<?php echo esc_attr($tiktok_id); ?>" placeholder="ex: C1234567890ABCDEF" class="wpps-input-text font-mono" />
                        <small class="text-muted">IDs do TikTok Pixel separados por vírgula.</small>
                    </div>
                </div>
            </div>

            <!-- Google Ads Card -->
            <div class="wpps-card wpps-col-12">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-google"></span> Google Ads Remarketing</h3>
                    <span class="wpps-status-pill <?php echo !empty($gads_id) ? 'wpps-status-on' : 'wpps-status-off'; ?>">
                        <?php echo !empty($gads_id) ? 'Ativo' : 'Inativo'; ?>
                    </span>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-group">
                        <label for="gads_id">Google Ads Conversion ID:</label>
                        <input type="text" id="gads_id" name="gads_id" value="<?php echo esc_attr($gads_id); ?>" placeholder="ex: AW-123456789" class="wpps-input-text font-mono" />
                        <small class="text-muted">ID de conversão e remarketing do Google Ads.</small>
                    </div>
                </div>
            </div>

            <!-- OPÇÕES EXTRAS: Reddit Pixel -->
            <div class="wpps-card">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-share-alt"></span> Complemento do Reddit (Reddit Pixel)</h3>
                    <span class="wpps-status-pill <?php echo !empty($reddit_id) ? 'wpps-status-on' : 'wpps-status-off'; ?>">
                        <?php echo !empty($reddit_id) ? 'Ativo' : 'Inativo'; ?>
                    </span>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-group">
                        <label for="wpps_reddit_pixel_id">Reddit Pixel IDs (Múltiplos IDs):</label>
                        <input type="text" id="wpps_reddit_pixel_id" name="wpps_reddit_pixel_id" value="<?php echo esc_attr($reddit_id); ?>" placeholder="ex: t2_xxxxx, a2_yyyyy" class="wpps-input-text font-mono" />
                        <small class="text-muted">IDs do Reddit Pixel separados por vírgula.</small>
                    </div>
                </div>
            </div>

            <!-- OPÇÕES EXTRAS: Pinterest Tag -->
            <div class="wpps-card">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-tag"></span> Complemento do Pinterest (Pinterest Tag)</h3>
                    <span class="wpps-status-pill <?php echo !empty($pinterest_id) ? 'wpps-status-on' : 'wpps-status-off'; ?>">
                        <?php echo !empty($pinterest_id) ? 'Ativo' : 'Inativo'; ?>
                    </span>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-group">
                        <label for="wpps_pinterest_tag_id">Pinterest Tag IDs (Múltiplos IDs):</label>
                        <input type="text" id="wpps_pinterest_tag_id" name="wpps_pinterest_tag_id" value="<?php echo esc_attr($pinterest_id); ?>" placeholder="ex: 261234567890" class="wpps-input-text font-mono" />
                        <small class="text-muted">IDs da Tag do Pinterest separados por vírgula.</small>
                    </div>
                </div>
            </div>

            <!-- OPÇÕES EXTRAS: Bing / Microsoft Advertising UET -->
            <div class="wpps-card wpps-col-12">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-cloud"></span> Complemento do Bing (Microsoft Advertising UET)</h3>
                    <span class="wpps-status-pill <?php echo !empty($bing_id) ? 'wpps-status-on' : 'wpps-status-off'; ?>">
                        <?php echo !empty($bing_id) ? 'Ativo' : 'Inativo'; ?>
                    </span>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-group">
                        <label for="wpps_bing_uet_id">Bing UET Tag IDs (Múltiplos IDs):</label>
                        <input type="text" id="wpps_bing_uet_id" name="wpps_bing_uet_id" value="<?php echo esc_attr($bing_id); ?>" placeholder="ex: 12345678, 87654321" class="wpps-input-text font-mono" />
                        <small class="text-muted">IDs da Tag UET do Bing/Microsoft separados por vírgula.</small>
                    </div>
                </div>
            </div>

            <!-- E-Commerce & Downloads (WooCommerce & EDD) -->
            <div class="wpps-card wpps-col-12">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-cart"></span> Integração E-Commerce: WooCommerce & EDD</h3>
                    <div style="display: flex; gap: 8px;">
                        <span class="wpps-status-pill wpps-status-on">WooCommerce: Ativo</span>
                        <span class="wpps-status-pill <?php echo $enable_edd === 'yes' ? 'wpps-status-on' : 'wpps-status-off'; ?>">EDD: <?php echo $enable_edd === 'yes' ? 'Ativo' : 'Inativo'; ?></span>
                    </div>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-grid">
                        <div class="wpps-form-group wpps-col-6">
                            <label for="wpps_enable_edd">Rastreamento Easy Digital Downloads (EDD):</label>
                            <select id="wpps_enable_edd" name="wpps_enable_edd">
                                <option value="yes" <?php selected($enable_edd, 'yes'); ?>>Ativado (Rastrear Downloads & Vendas EDD)</option>
                                <option value="no" <?php selected($enable_edd, 'no'); ?>>Desativado</option>
                            </select>
                            <small class="text-muted">Rastreia automaticamente visualizações de produtos e compras finalizadas no Easy Digital Downloads.</small>
                        </div>
                        <div class="wpps-form-group wpps-col-6">
                            <label for="wpps_enable_amp">Suporte AMP (Accelerated Mobile Pages):</label>
                            <select id="wpps_enable_amp" name="wpps_enable_amp">
                                <option value="yes" <?php selected($enable_amp, 'yes'); ?>>Ativado</option>
                                <option value="no" <?php selected($enable_amp, 'no'); ?>>Desativado</option>
                            </select>
                            <small class="text-muted">Injeta automaticamente pixels em versões móveis aceleradas (AMP).</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Super Pack: Valores de Parâmetros Dinâmicos -->
            <div class="wpps-card wpps-col-12">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-editor-code"></span> Super Pacote: Valores de Parâmetros Dinâmicos & WPML</h3>
                </div>
                <div class="wpps-card-body">
                    <p class="wpps-help-text">Você pode utilizar tags dinâmicas em eventos customizados ou scripts que serão substituídas automaticamente em tempo real:</p>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div><code>{post_title}</code> - Título da página/post</div>
                        <div><code>{post_id}</code> - ID do post</div>
                        <div><code>{post_type}</code> - Tipo de post (post, page, download, etc)</div>
                        <div><code>{category}</code> - Categoria principal</div>
                        <div><code>{product_price}</code> - Preço do produto (Woo/EDD)</div>
                        <div><code>{product_sku}</code> - SKU do produto</div>
                        <div><code>{user_id}</code> - ID do usuário logado</div>
                        <div><code>{user_email}</code> - E-mail do usuário logado</div>
                        <div><code>{language}</code> - Idioma atual (WPML / Polylang)</div>
                        <div><code>{page_url}</code> - URL completa da página</div>
                    </div>
                </div>
            </div>

            <!-- Automatic Events Tracking -->
            <div class="wpps-card wpps-col-12">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-admin-generic"></span> Rastreamento Automático de Eventos</h3>
                    <div style="display: flex; gap: 8px;">
                        <span class="wpps-status-pill wpps-status-on">Ativo</span>
                    </div>
                </div>
                <div class="wpps-card-body">
                    <p class="wpps-help-text">Ative ou desative o rastreamento automático de ações-chave dos visitantes (Login, Cadastro e Formulários).</p>
                    <div class="wpps-form-grid">
                        <div class="wpps-form-group wpps-col-4">
                            <label for="wpps_auto_track_forms">Track Forms (Envios de Formulários)</label>
                            <select id="wpps_auto_track_forms" name="wpps_auto_track_forms">
                                <option value="yes" <?php selected($auto_track_forms, 'yes'); ?>>Ativado</option>
                                <option value="no" <?php selected($auto_track_forms, 'no'); ?>>Desativado</option>
                            </select>
                        </div>
                        <div class="wpps-form-group wpps-col-4">
                            <label for="wpps_auto_track_signup">Track User Signup (Novos Cadastros)</label>
                            <select id="wpps_auto_track_signup" name="wpps_auto_track_signup">
                                <option value="yes" <?php selected($auto_track_signup, 'yes'); ?>>Ativado</option>
                                <option value="no" <?php selected($auto_track_signup, 'no'); ?>>Desativado</option>
                            </select>
                        </div>
                        <div class="wpps-form-group wpps-col-4">
                            <label for="wpps_auto_track_login">Track User Login (Acesso de Usuários)</label>
                            <select id="wpps_auto_track_login" name="wpps_auto_track_login">
                                <option value="yes" <?php selected($auto_track_login, 'yes'); ?>>Ativado</option>
                                <option value="no" <?php selected($auto_track_login, 'no'); ?>>Desativado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Super Pack: WooCommerce Custom Thank You Pages -->
            <div class="wpps-card wpps-col-12">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-cart"></span> WooCommerce Custom Thank You Pages (Super Pack)</h3>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-group">
                        <label for="custom_thankyou_urls">URLs de Páginas de Agradecimento Personalizadas (uma por linha):</label>
                        <textarea id="custom_thankyou_urls" name="custom_thankyou_urls" rows="3" class="wpps-textarea font-mono" placeholder="ex: /obrigado-especial&#10;/compra-confirmada"><?php echo esc_textarea($custom_thankyou_urls); ?></textarea>
                        <small class="text-muted">Informe URLs personalizadas para disparar o evento de `Purchase` / `CompletePayment` automaticamente quando o cliente for redirecionado.</small>
                    </div>
                </div>
            </div>

            <!-- Custom Head Scripts Card -->
            <div class="wpps-card wpps-col-12">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-editor-code"></span> Scripts Personalizados no &lt;head&gt;</h3>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-group">
                        <label for="head_scripts">Códigos HTML / JavaScript para injeção no &lt;head&gt;:</label>
                        <textarea id="head_scripts" name="head_scripts" rows="4" class="wpps-textarea font-mono" placeholder="&lt;script&gt; // Seu código aqui &lt;/script&gt;"><?php echo esc_textarea($head_scripts); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Custom Footer Scripts Card -->
            <div class="wpps-card wpps-col-12">
                <div class="wpps-card-header">
                    <h3><span class="dashicons dashicons-editor-code"></span> Scripts Personalizados no &lt;footer&gt;</h3>
                </div>
                <div class="wpps-card-body">
                    <div class="wpps-form-group">
                        <label for="footer_scripts">Códigos HTML / JavaScript para injeção no &lt;footer&gt;:</label>
                        <textarea id="footer_scripts" name="footer_scripts" rows="4" class="wpps-textarea font-mono" placeholder="&lt;script&gt; // Seu código de chat ou widget aqui &lt;/script&gt;"><?php echo esc_textarea($footer_scripts); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
