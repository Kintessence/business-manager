<?php
if (!defined('ABSPATH')) exit;
/** @var array $products */

$roles = [
    1 => ['label' => '🎯 Produto foco', 'badge' => '#dbeafe', 'text' => '#1e40af'],
    2 => ['label' => '🚪 Abridor de carteira', 'badge' => '#fef3c7', 'text' => '#92400e'],
    3 => ['label' => '💰 Gerador de caixa', 'badge' => '#d1fae5', 'text' => '#065f46'],
    4 => ['label' => '➕ Aumentador de pedido', 'badge' => '#ede9fe', 'text' => '#5b21b6'],
    5 => ['label' => '🧪 Experimentação', 'badge' => '#ffedd5', 'text' => '#9a3412'],
];
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <span class="be-badge be-badge-success">Catálogo Comercial</span>
                <h1 style="font-size:22px; margin:6px 0 0; font-weight:800;">🏷️ Produtos Finais & Precificação</h1>
            </div>
            <a href="admin.php?page=be-products&action=new" class="be-btn-primary" style="text-decoration:none;">➕ Novo Produto Final</a>
        </div>

        <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid var(--be-border);">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Função Estratégica</th>
                    <th>Tempo Montagem</th>
                    <th>Margem Alvo</th>
                    <th>Preço Praticado</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:24px; color:var(--be-text-muted);">Nenhum produto cadastrado no catálogo.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): 
                        $roleId = (int)($p->strategic_role ?? 1);
                        $roleInfo = $roles[$roleId] ?? $roles[1];
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($p->name); ?></strong>
                                <?php if (!empty($p->sku)): ?><small style="display:block; color:var(--be-text-muted);">SKU: <?php echo esc_html($p->sku); ?></small><?php endif; ?>
                            </td>
                            <td>
                                <span style="background:<?php echo $roleInfo['badge']; ?>; color:<?php echo $roleInfo['text']; ?>; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">
                                    <?php echo esc_html($roleInfo['label']); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($p->production_time_min . ' min'); ?></td>
                            <td><?php echo esc_html($p->target_margin . '%'); ?></td>
                            <td><strong style="color:var(--be-primary); font-size:15px;">R$ <?php echo number_format((float)$p->final_price, 2, ',', '.'); ?></strong></td>
                            <td style="text-align: right;">
                                <a href="admin.php?page=be-products&action=edit&id=<?php echo (int)$p->id; ?>" class="button button-small">Editar</a>
                                <button type="button" class="button button-small button-link-delete" onclick="deleteProduct(<?php echo (int)$p->id; ?>)">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteProduct(id) {
    if (!confirm('Deseja realmente excluir este produto?')) return;
    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_delete_product',
        id: id,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir.');
    });
}
</script>