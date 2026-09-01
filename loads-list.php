<?php
if (!defined('ABSPATH')) exit;
/** @var array $loads */
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <span class="be-badge be-badge-success">Pronta-Entrega & Venda Externa</span>
                <h1 style="font-size:22px; margin:6px 0 0; font-weight:800;">🛵 Cargas de Rua & Acerto Diário</h1>
            </div>
            <a href="admin.php?page=be-street-sales&action=new" class="be-btn-primary" style="text-decoration:none;">➕ Nova Carga de Saída</a>
        </div>

        <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid var(--be-border);">
            <thead>
                <tr>
                    <th>Carga / Vendedor</th>
                    <th>Data de Saída</th>
                    <th>Status</th>
                    <th>Valores Apurados (Dinheiro / PIX / Cartão)</th>
                    <th style="text-align: right;">Ações Operacionais</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($loads)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:24px; color:var(--be-text-muted);">Nenhuma carga de rua cadastrada. Registre a saída de produtos para a equipe.</td></tr>
                <?php else: ?>
                    <?php foreach ($loads as $l): ?>
                        <tr>
                            <td>
                                <strong>🛵 <?php echo esc_html($l->seller_name); ?></strong>
                                <small style="display:block; color:var(--be-text-muted);">ID Carga #<?php echo (int)$l->id; ?></small>
                            </td>
                            <td><?php echo esc_html($l->load_date); ?></td>
                            <td>
                                <?php if ($l->status === 'open'): ?>
                                    <span class="be-badge be-badge-warning">Em Rota (Aberta)</span>
                                <?php else: ?>
                                    <span class="be-badge be-badge-success">Encerrada & Acertada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($l->status === 'closed'): ?>
                                    <strong style="color:var(--be-primary);">R$ <?php echo number_format((float)($l->cash_received + $l->pix_received + $l->card_received), 2, ',', '.'); ?></strong>
                                    <small style="display:block; color:var(--be-text-muted);">💵 R$ <?php echo number_format((float)$l->cash_received, 2, ',', '.'); ?> | ⚡ PIX R$ <?php echo number_format((float)$l->pix_received, 2, ',', '.'); ?> | 💳 R$ <?php echo number_format((float)$l->card_received, 2, ',', '.'); ?></small>
                                <?php else: ?>
                                    <span style="color:var(--be-text-muted);">Aguardando encerramento da rota</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <?php if ($l->status === 'open'): ?>
                                    <a href="admin.php?page=be-street-sales&action=pos&id=<?php echo (int)$l->id; ?>" class="button button-small" style="color:#0284c7; font-weight:700;">📱 PDV Mobile</a>
                                    <a href="admin.php?page=be-street-sales&action=reconcile&id=<?php echo (int)$l->id; ?>" class="be-btn-primary" style="text-decoration:none; padding:4px 10px; font-size:12px;">⚖️ Acertar Caixa</a>
                                <?php else: ?>
                                    <a href="admin.php?page=be-street-sales&action=reconcile&id=<?php echo (int)$l->id; ?>" class="button button-small">Ver Fechamento</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>