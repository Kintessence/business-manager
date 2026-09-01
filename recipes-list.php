<?php
if (!defined('ABSPATH')) exit;
/** @var array $recipes */
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <span class="be-badge be-badge-success">Módulo Gastronomia</span>
                <h1 style="font-size:22px; margin:6px 0 0; font-weight:800;">📋 Fichas Técnicas & Receitas</h1>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="admin.php?page=be-csv-import" class="be-pill-btn">📥 Importar Maya</a>
                <a href="admin.php?page=be-recipes&action=new" class="be-btn-primary" style="text-decoration:none;">➕ Nova Ficha Técnica</a>
            </div>
        </div>

        <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid var(--be-border);">
            <thead>
                <tr>
                    <th>Nome da Receita</th>
                    <th>Rendimento</th>
                    <th>Tempo Total</th>
                    <th>Ingredientes</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recipes)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:24px; color:var(--be-text-muted);">Nenhuma ficha técnica cadastrada. Clique no botão acima ou importe do Maya.</td></tr>
                <?php else: ?>
                    <?php foreach ($recipes as $r): ?>
                        <tr>
                            <td><strong><?php echo esc_html($r->name); ?></strong></td>
                            <td><?php echo esc_html($r->yield_qty . ' ' . $r->yield_unit); ?></td>
                            <td><?php echo esc_html(($r->prep_time_min + $r->bake_time_min) . ' min'); ?></td>
                            <td><span class="be-badge"><?php echo esc_html($r->total_ingredients); ?> insumos</span></td>
                            <td style="text-align: right;">
                                <a href="admin.php?page=be-recipes&action=edit&id=<?php echo (int)$r->id; ?>" class="button button-small">Editar</a>
                                <button type="button" class="button button-small button-link-delete" onclick="deleteRecipe(<?php echo (int)$r->id; ?>)">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteRecipe(id) {
    if (!confirm('Deseja realmente excluir esta ficha técnica?')) return;
    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_delete_recipe',
        id: id,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir.');
    });
}
</script>