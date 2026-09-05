<?php
if (!defined('ABSPATH')) exit;
/** @var array $terms */
?>
<div class="be-wrap">
    <div class="be-header" style="margin-bottom: 20px;">
        <div class="be-header-title">
            <h1>Dicionário de Termos & Personalização</h1>
            <p>Altere os rótulos do sistema em um único lugar. As alterações refletem imediatamente nos menus laterais, títulos e buscas.</p>
        </div>
    </div>

    <form id="be-vocab-form">
        <div class="be-card" style="padding: 24px;">
            
            <!-- Módulos Ativos (1 a 4) -->
            <h2 style="font-size: 16px; font-weight: 800; margin: 0 0 16px; color: var(--be-primary);">Módulos do Motor & Produção</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Insumos (Singular)</label>
                    <input type="text" name="terms[supplies_singular]" value="<?php echo esc_attr($terms['supplies_singular']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Insumos (Menu Lateral / Plural)</label>
                    <input type="text" name="terms[supplies_plural]" value="<?php echo esc_attr($terms['supplies_plural']); ?>" class="be-modal-input" style="width: 100%;">
                </div>

                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Fichas Técnicas (Singular)</label>
                    <input type="text" name="terms[recipes_singular]" value="<?php echo esc_attr($terms['recipes_singular']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Fichas Técnicas (Menu Lateral / Plural)</label>
                    <input type="text" name="terms[recipes_plural]" value="<?php echo esc_attr($terms['recipes_plural']); ?>" class="be-modal-input" style="width: 100%;">
                </div>

                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Produtos Comerciais (Singular)</label>
                    <input type="text" name="terms[products_singular]" value="<?php echo esc_attr($terms['products_singular']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Produtos Comerciais (Menu Lateral / Plural)</label>
                    <input type="text" name="terms[products_plural]" value="<?php echo esc_attr($terms['products_plural']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
            </div>

            <!-- Próximos Módulos (5 a 7) -->
            <h2 style="font-size: 16px; font-weight: 800; margin: 0 0 16px; color: var(--be-primary); border-top: 1px solid var(--be-border-subtle); padding-top: 20px;">Módulos Comerciais & Vendas (Próximos Passos)</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Clientes (Singular)</label>
                    <input type="text" name="terms[customers_singular]" value="<?php echo esc_attr($terms['customers_singular']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Clientes (Menu Lateral / Plural)</label>
                    <input type="text" name="terms[customers_plural]" value="<?php echo esc_attr($terms['customers_plural']); ?>" class="be-modal-input" style="width: 100%;">
                </div>

                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Pedidos (Singular)</label>
                    <input type="text" name="terms[orders_singular]" value="<?php echo esc_attr($terms['orders_singular']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Pedidos (Menu Lateral / Plural)</label>
                    <input type="text" name="terms[orders_plural]" value="<?php echo esc_attr($terms['orders_plural']); ?>" class="be-modal-input" style="width: 100%;">
                </div>

                <div style="grid-column: span 2;">
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Venda de Rua / Pronta Entrega (Menu / Título)</label>
                    <input type="text" name="terms[street_sales_title]" value="<?php echo esc_attr($terms['street_sales_title']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
            </div>

            <!-- Textos de Placeholders de Busca -->
            <h2 style="font-size: 16px; font-weight: 800; margin: 0 0 16px; color: var(--be-primary); border-top: 1px solid var(--be-border-subtle); padding-top: 20px;">Placeholders de Busca</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Placeholder Insumos</label>
                    <input type="text" name="terms[search_supplies_ph]" value="<?php echo esc_attr($terms['search_supplies_ph']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Placeholder Fichas</label>
                    <input type="text" name="terms[search_recipes_ph]" value="<?php echo esc_attr($terms['search_recipes_ph']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:4px;">Placeholder Produtos</label>
                    <input type="text" name="terms[search_products_ph]" value="<?php echo esc_attr($terms['search_products_ph']); ?>" class="be-modal-input" style="width: 100%;">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" class="be-btn-primary" style="height: 40px; padding: 0 24px;">Salvar Vocabulário Global</button>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('be-vocab-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = jQuery(this).serialize();
    jQuery.post(beSettings.ajaxUrl, data + '&action=be_save_vocabulary&nonce=' + beSettings.nonce, function(res) {
        if (res.success) {
            alert('Vocabulário salvo! Os menus e telas foram atualizados.');
            window.location.reload();
        } else {
            alert('Erro ao salvar: ' + (res.data?.message || 'Tente novamente.'));
        }
    });
});
</script>