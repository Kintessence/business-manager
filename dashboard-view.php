<?php
if (!defined('ABSPATH')) exit;
/** @var array $metrics */
/** @var array $alerts */
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <span class="be-badge be-badge-success">Motor Operacional Conectado</span>
                <h1 style="font-size: 24px; margin: 6px 0 0; font-weight: 800;">Painel de Controle & Gestor Virtual</h1>
                <p style="color: var(--be-text-muted); margin: 4px 0 0; font-size: 13px;">Visão executiva e cruzamento de custos com o histórico real de vendas.</p>
            </div>
            <a href="admin.php?page=business-engine" class="be-pill-btn" style="text-decoration:none;">⚙️ Recalibrar Onboarding</a>
        </div>

        <!-- Grade de Indicadores Principais -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 24px;">
            <div style="background: #f8fafc; border: 1px solid var(--be-border); padding: 18px; border-radius: 10px;">
                <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase;">Faturamento Total</span>
                <div style="font-size: 24px; font-weight: 800; color: var(--be-primary); margin-top: 4px;">
                    R$ <?php echo number_format($metrics['paid_revenue'], 2, ',', '.'); ?>
                </div>
                <small style="color: var(--be-text-muted);"><?php echo (int)$metrics['total_orders']; ?> pedidos importados</small>
            </div>

            <div style="background: #f8fafc; border: 1px solid var(--be-border); padding: 18px; border-radius: 10px;">
                <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase;">Ticket Médio Real</span>
                <div style="font-size: 24px; font-weight: 800; color: var(--be-accent); margin-top: 4px;">
                    R$ <?php echo number_format($metrics['avg_ticket'], 2, ',', '.'); ?>
                </div>
                <small style="color: var(--be-text-muted);">por pedido realizado</small>
            </div>

            <div style="background: #f8fafc; border: 1px solid var(--be-border); padding: 18px; border-radius: 10px;">
                <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase;">Custo Fixo + Pró-labore</span>
                <div style="font-size: 24px; font-weight: 800; color: #0284c7; margin-top: 4px;">
                    R$ <?php echo number_format($metrics['total_structural'], 2, ',', '.'); ?>
                </div>
                <small style="color: var(--be-text-muted);">compromisso mensal</small>
            </div>

            <div style="background: #f8fafc; border: 1px solid var(--be-border); padding: 18px; border-radius: 10px;">
                <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase;">Ponto de Equilíbrio / Mês</span>
                <div style="font-size: 24px; font-weight: 800; color: #d97706; margin-top: 4px;">
                    R$ <?php echo number_format($metrics['breakeven_monthly'], 2, ',', '.'); ?>
                </div>
                <small style="color: var(--be-text-muted);">faturamento mínimo alvo</small>
            </div>
        </div>

        <!-- Grade de Resumo Operacional dos Módulos -->
        <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">Ativos Operacionais no Sistema</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 24px;">
            <a href="admin.php?page=be-supplies" style="text-decoration: none; color: inherit; background: #fff; border: 1px solid var(--be-border); padding: 14px; border-radius: 8px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='var(--be-primary)'" onmouseout="this.style.borderColor='var(--be-border)'">
                <span style="font-size: 12px; font-weight: 700; color: var(--be-text-muted);">🧂 Insumos</span>
                <div style="font-size: 20px; font-weight: 800; color: var(--be-text-main); margin-top: 2px;"><?php echo (int)$metrics['total_supplies']; ?> itens</div>
            </a>
            <a href="admin.php?page=be-recipes" style="text-decoration: none; color: inherit; background: #fff; border: 1px solid var(--be-border); padding: 14px; border-radius: 8px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='var(--be-primary)'" onmouseout="this.style.borderColor='var(--be-border)'">
                <span style="font-size: 12px; font-weight: 700; color: var(--be-text-muted);">📋 Fichas Técnicas</span>
                <div style="font-size: 20px; font-weight: 800; color: var(--be-text-main); margin-top: 2px;"><?php echo (int)$metrics['total_recipes']; ?> receitas</div>
            </a>
            <a href="admin.php?page=be-products" style="text-decoration: none; color: inherit; background: #fff; border: 1px solid var(--be-border); padding: 14px; border-radius: 8px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='var(--be-primary)'" onmouseout="this.style.borderColor='var(--be-border)'">
                <span style="font-size: 12px; font-weight: 700; color: var(--be-text-muted);">🏷️ Produtos Finais</span>
                <div style="font-size: 20px; font-weight: 800; color: var(--be-text-main); margin-top: 2px;"><?php echo (int)$metrics['total_products']; ?> precificados</div>
            </a>
            <a href="admin.php?page=be-customers" style="text-decoration: none; color: inherit; background: #fff; border: 1px solid var(--be-border); padding: 14px; border-radius: 8px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='var(--be-primary)'" onmouseout="this.style.borderColor='var(--be-border)'">
                <span style="font-size: 12px; font-weight: 700; color: var(--be-text-muted);">👥 Clientes (CRM)</span>
                <div style="font-size: 20px; font-weight: 800; color: var(--be-text-main); margin-top: 2px;"><?php echo (int)$metrics['total_customers']; ?> cadastros</div>
            </a>
        </div>

        <!-- Mural do Gestor Virtual -->
        <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">🧠 Diagnóstico do Gestor Virtual</h3>
        <?php if (empty($alerts)): ?>
            <div style="background: #f0fdf4; border-left: 4px solid var(--be-success); padding: 14px 18px; border-radius: 6px; color: #166534;">
                <strong>Tudo sob controle!</strong> Não há pendências críticas ou anomalias operacionais no momento.
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach ($alerts as $al): 
                    $border = $al['type'] === 'warning' ? '#f59e0b' : ($al['type'] === 'success' ? '#10b981' : '#3b82f6');
                    $bg = $al['type'] === 'warning' ? '#fffbeb' : ($al['type'] === 'success' ? '#f0fdf4' : '#eff6ff');
                    $textColor = $al['type'] === 'warning' ? '#92400e' : ($al['type'] === 'success' ? '#166534' : '#1e40af');
                ?>
                    <div style="background: <?php echo $bg; ?>; border-left: 4px solid <?php echo $border; ?>; padding: 14px 18px; border-radius: 6px; color: <?php echo $textColor; ?>;">
                        <strong><?php echo esc_html($al['icon'] . ' ' . $al['title']); ?>:</strong> <?php echo esc_html($al['text']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>