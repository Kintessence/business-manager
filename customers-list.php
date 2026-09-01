<?php
if (!defined('ABSPATH')) exit;
/** @var array $customers */
/** @var int $totalCustomers */
/** @var float $totalRevenue */
/** @var float $avgTicket */
/** @var string $search */
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <span class="be-badge be-badge-success">Base de Clientes</span>
                <h1 style="font-size:22px; margin:6px 0 0; font-weight:800;">CRM & Carteira de Clientes</h1>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="admin.php?page=be-csv-import" class="be-pill-btn">Importar Maya</a>
                <a href="admin.php?page=be-customers&action=new" class="be-btn-primary" style="text-decoration:none;">+ Novo Cliente</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <div style="background:#f8fafc; border:1px solid var(--be-border); padding:14px; border-radius:8px; text-align:center;">
                <span style="font-size:11px; font-weight:700; color:var(--be-text-muted); text-transform:uppercase;">Clientes Ativos</span>
                <div style="font-size:22px; font-weight:800; color:var(--be-primary); margin-top:2px;"><?php echo $totalCustomers; ?></div>
            </div>
            <div style="background:#f8fafc; border:1px solid var(--be-border); padding:14px; border-radius:8px; text-align:center;">
                <span style="font-size:11px; font-weight:700; color:var(--be-text-muted); text-transform:uppercase;">Volume de Compras</span>
                <div style="font-size:22px; font-weight:800; color:var(--be-accent); margin-top:2px;">R$ <?php echo number_format($totalRevenue, 2, ',', '.'); ?></div>
            </div>
            <div style="background:#f8fafc; border:1px solid var(--be-border); padding:14px; border-radius:8px; text-align:center;">
                <span style="font-size:11px; font-weight:700; color:var(--be-text-muted); text-transform:uppercase;">Ticket Médio / Cliente</span>
                <div style="font-size:22px; font-weight:800; color:#0284c7; margin-top:2px;">R$ <?php echo number_format($avgTicket, 2, ',', '.'); ?></div>
            </div>
        </div>

        <form method="get" style="display:flex; gap:8px; margin-bottom:16px;">
            <input type="hidden" name="page" value="be-customers">
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Buscar cliente por nome, telefone ou e-mail..." style="flex:1; padding:8px 12px; border:1px solid var(--be-border); border-radius:6px;">
            <button type="submit" class="be-btn-primary" style="padding: 8px 16px;">Buscar</button>
            <?php if (!empty($search)): ?>
                <a href="admin.php?page=be-customers" class="be-pill-btn" style="text-decoration:none; display:inline-flex; align-items:center;">Limpar</a>
            <?php endif; ?>
        </form>

        <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid var(--be-border);">
            <thead>
                <tr>
                    <th style="width: 25%; text-align: left;">Cliente</th>
                    <th style="width: 18%; text-align: center;">Contato</th>
                    <th style="width: 14%; text-align: center;">Desconto Padrão</th>
                    <th style="width: 12%; text-align: center;">Total Pedidos</th>
                    <th style="width: 15%; text-align: center;">LTV Acumulado</th>
                    <th style="width: 16%; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:24px; color:var(--be-text-muted);">Nenhum cliente cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): 
                        $cleanPhone = preg_replace('/\D/', '', (string)$c->phone);
                        if (strlen($cleanPhone) >= 10 && !str_starts_with($cleanPhone, '55')) {
                            $cleanPhone = '55' . $cleanPhone;
                        }
                        $hasWa = isset($c->has_whatsapp) ? (bool)$c->has_whatsapp : true;
                    ?>
                        <tr>
                            <td style="text-align: left;">
                                <strong><?php echo esc_html($c->name); ?></strong>
                                <?php if (!empty($c->address)): ?>
                                    <small style="display:block; color:var(--be-text-muted); font-size:11px;" title="<?php echo esc_attr($c->address); ?>">
                                        📍 <?php echo esc_html(mb_strimwidth((string)$c->address, 0, 40, '...')); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($c->phone)): ?>
                                    <span><?php echo esc_html($c->phone); ?></span>
                                    <?php if ($hasWa && !empty($cleanPhone)): ?>
                                        <a href="https://wa.me/<?php echo esc_attr($cleanPhone); ?>" target="_blank" style="text-decoration:none; margin-left:4px; color:#25d366; font-size:14px;" title="Conversar no WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:var(--be-text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ((float)$c->default_discount > 0): ?>
                                    <span class="be-badge be-badge-success"><?php echo number_format((float)$c->default_discount, 1, ',', '.'); ?>% OFF</span>
                                <?php else: ?>
                                    <span style="color:var(--be-text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <strong><?php echo (int)$c->orders_count; ?></strong>
                            </td>
                            <td style="text-align: center;">
                                <strong style="color:var(--be-primary); font-size:14px;">R$ <?php echo number_format((float)$c->amount_spent, 2, ',', '.'); ?></strong>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <button type="button" class="be-action-btn be-btn-view" onclick="openCustomerHistory('<?php echo esc_js($c->name); ?>', '<?php echo esc_js($c->phone); ?>')" title="Ver Histórico de Compras">
                                    <i class="fa-solid fa-clock-rotate-left"></i> Histórico
                                </button>
                                <a href="admin.php?page=be-customers&action=edit&id=<?php echo (int)$c->id; ?>" class="be-action-btn be-btn-edit">Editar</a>
                                <button type="button" class="be-action-btn be-btn-del" onclick="deleteCustomer(<?php echo (int)$c->id; ?>)">✕</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Extrato de Histórico de Compras do Cliente -->
<div id="be-cust-history-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:99999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
    <div style="background:#fff; border-radius:12px; width:680px; max-width:94%; max-height:90vh; overflow-y:auto; padding:26px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.15);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
            <div>
                <span class="be-badge be-badge-success">Extrato de Compras</span>
                <h2 style="font-size:18px; font-weight:800; margin:4px 0 0;" id="history-cust-title">Histórico do Cliente</h2>
            </div>
            <button type="button" onclick="closeCustomerHistory()" style="background:none; border:none; font-size:22px; cursor:pointer; color:var(--be-text-muted);">&times;</button>
        </div>

        <table class="widefat striped" style="border:1px solid var(--be-border); border-radius:6px; margin-bottom:16px;">
            <thead>
                <tr>
                    <th style="width: 15%; text-align: center;">Pedido</th>
                    <th style="width: 25%; text-align: left;">Data</th>
                    <th style="width: 20%; text-align: center;">Status</th>
                    <th style="width: 20%; text-align: center;">Pagamento</th>
                    <th style="width: 20%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody id="history-table-body">
                <tr><td colspan="5" style="text-align:center; padding:16px; color:var(--be-text-muted);">Carregando extrato...</td></tr>
            </tbody>
        </table>

        <div style="background:#f8fafc; border:1px solid var(--be-border); border-radius:8px; padding:14px; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; font-weight:700; color:var(--be-text-muted);">Total Acumulado pelo Cliente:</span>
            <span style="font-size:20px; font-weight:800; color:var(--be-primary);" id="history-total-spent">R$ 0,00</span>
        </div>
    </div>
</div>

<style>
.be-action-btn { border: 1px solid var(--be-border); background: #fff; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s ease; }
.be-btn-view { color: #0284c7; }
.be-btn-view:hover { background: #f0f9ff; border-color: #0284c7; }
.be-btn-edit { color: var(--be-accent); text-decoration: none; }
.be-btn-edit:hover { background: #eff6ff; border-color: var(--be-accent); }
.be-btn-del { color: #991b1b; }
.be-btn-del:hover { background: #fef2f2; border-color: #ef4444; }
</style>

<script>
function openCustomerHistory(name, phone) {
    document.getElementById('history-cust-title').innerText = 'Histórico: ' + name;
    document.getElementById('history-table-body').innerHTML = '<tr><td colspan="5" style="text-align:center; padding:16px; color:var(--be-text-muted);">Buscando pedidos...</td></tr>';
    document.getElementById('be-cust-history-modal').style.display = 'flex';

    jQuery.get(beSettings.ajaxUrl, {
        action: 'be_get_customer_history',
        name: name,
        phone: phone,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) {
            const orders = res.data.orders || [];
            let rows = '';
            let totalSpent = 0;

            if (orders.length > 0) {
                orders.forEach(o => {
                    const total = parseFloat(o.total_amount) || 0;
                    totalSpent += total;
                    const isPaid = (o.payment_status === 'paid' || o.payment_status === 'Pago');
                    const seq = o.sequential_id ? '#' + o.sequential_id : '#' + o.id;

                    rows += `
                        <tr>
                            <td style="text-align:center;"><strong>${seq}</strong></td>
                            <td style="text-align:left;">${o.order_date.substring(0, 10)}</td>
                            <td style="text-align:center;"><span class="be-badge">${o.production_status || 'entregue'}</span></td>
                            <td style="text-align:center;">
                                <span class="be-badge ${isPaid ? 'be-badge-success' : 'be-badge-warning'}">${isPaid ? 'Pago' : 'Pendente'}</span>
                            </td>
                            <td style="text-align:right;"><strong style="color:var(--be-primary);">R$ ${total.toFixed(2).replace('.', ',')}</strong></td>
                        </tr>
                    `;
                });
            } else {
                rows = '<tr><td colspan="5" style="text-align:center; padding:16px; color:var(--be-text-muted);">Nenhum pedido registrado para este cliente.</td></tr>';
            }
            document.getElementById('history-table-body').innerHTML = rows;
            document.getElementById('history-total-spent').innerText = 'R$ ' + totalSpent.toFixed(2).replace('.', ',');
        }
    });
}

function closeCustomerHistory() {
    document.getElementById('be-cust-history-modal').style.display = 'none';
}

function deleteCustomer(id) {
    if (!confirm('Deseja excluir este cliente?')) return;
    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_delete_customer',
        id: id,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir.');
    });
}
</script>