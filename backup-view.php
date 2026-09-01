<?php
if (!defined('ABSPATH')) exit;
/** @var string $status */
/** @var string $msg */
?>
<div class="wrap be-wrap">
    <div class="be-card" style="max-width: 860px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <span class="be-badge be-badge-success">Segurança & Continuidade</span>
                <h1 style="font-size: 24px; margin: 6px 0 0; font-weight: 800;">💾 Backup & Restauração do Banco</h1>
                <p style="color: var(--be-text-muted); font-size: 13px; margin: 4px 0 0;">Exporte uma cópia completa dos seus dados ou restaure um backup anterior com segurança.</p>
            </div>
        </div>

        <?php if (!empty($msg)): ?>
            <?php if ($status === 'success'): ?>
                <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #166534; font-weight: 600;">
                    ✅ <?php echo esc_html($msg); ?>
                </div>
            <?php else: ?>
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #991b1b; font-weight: 600;">
                    ❌ <?php echo esc_html($msg); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px;">
            
            <!-- Card Exportar -->
            <div style="background: #f8fafc; border: 1px solid var(--be-border); padding: 22px; border-radius: 10px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 32px; margin-bottom: 8px;">📤</div>
                    <h3 style="font-size: 16px; font-weight: 800; margin: 0 0 6px;">Exportar Backup Completo</h3>
                    <p style="font-size: 13px; color: var(--be-text-muted); line-height: 1.4; margin: 0 0 16px;">
                        Gera um arquivo JSON estruturado com todos os <strong>Insumos</strong>, <strong>Fichas Técnicas</strong>, <strong>Produtos</strong>, <strong>Clientes</strong>, <strong>Pedidos</strong>, <strong>Taxas de Frete</strong> e <strong>Custos Fixos</strong>.
                    </p>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="be_export_backup">
                    <?php wp_nonce_field('be_export_nonce', '_nonce'); ?>
                    <button type="submit" class="be-btn-primary" style="width: 100%; padding: 10px 0; font-size: 14px; text-align: center;">
                        Baixar Backup (.json) 💾
                    </button>
                </form>
            </div>

            <!-- Card Importar / Restaurar -->
            <div style="background: #f8fafc; border: 1px solid var(--be-border); padding: 22px; border-radius: 10px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 32px; margin-bottom: 8px;">📥</div>
                    <h3 style="font-size: 16px; font-weight: 800; margin: 0 0 6px;">Restaurar Banco de Dados</h3>
                    <p style="font-size: 13px; color: var(--be-text-muted); line-height: 1.4; margin: 0 0 16px;">
                        Selecione um arquivo <code>.json</code> de backup gerado anteriormente para reidratar todas as tabelas em caso de migração de servidor ou restauração.
                    </p>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" onsubmit="return confirm('ATENÇÃO: A restauração substituirá os dados atuais pelos dados do arquivo. Deseja continuar?');">
                    <input type="hidden" name="action" value="be_restore_backup">
                    <?php wp_nonce_field('be_restore_nonce', '_nonce'); ?>
                    <input type="file" name="backup_file" accept=".json,application/json" required style="width: 100%; margin-bottom: 12px; font-size: 12px;">
                    <button type="submit" class="be-pill-btn" style="width: 100%; padding: 10px 0; font-size: 14px; text-align: center; background: #0f172a; color: #fff; border-color: #0f172a;">
                        Restaurar Backup 🔄
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>