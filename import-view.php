<?php
if (!defined('ABSPATH')) exit;
/** @var string $status */
/** @var string $message */
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <span class="be-badge">Importador em Lote</span>
        <h1 style="font-size: 22px; margin: 8px 0 16px; font-weight: 800;">📥 Importar Dados do Maya para o Business Engine</h1>
        
        <?php if (!empty($message)): ?>
            <?php if ($status === 'success'): ?>
                <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #065f46;">
                    <strong>✅ <?php echo esc_html($message); ?></strong>
                </div>
            <?php else: ?>
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #991b1b;">
                    <strong>❌ <?php echo esc_html($message); ?></strong>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('be_import_nonce', '_nonce'); ?>
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px;">1. Selecione o Tipo de Arquivo Maya:</label>
                <select name="import_type" style="padding: 8px; border-radius: 6px; border: 1px solid var(--be-border);">
                    <option value="products">Produtos Comerciais (products_maya.csv)</option>
                    <option value="orders">Pedidos Históricos (pedidos_maya.csv)</option>
                    <option value="customers">Clientes (clientes_maya.csv)</option>
                    <option value="recipes">Fichas Técnicas (recipes_maya.csv)</option>
                    <option value="supplies">Insumos (suplies_maya.csv)</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px;">2. Escolha o Arquivo CSV / TSV:</label>
                <input type="file" name="csv_file" accept=".csv,.tsv,text/csv,text/plain,text/tab-separated-values" required>
            </div>

            <button type="submit" class="be-btn-primary">Processar Importação Segura 🚀</button>
        </form>
    </div>
</div>