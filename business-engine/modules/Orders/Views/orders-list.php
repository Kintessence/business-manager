<?php
if (!defined('ABSPATH')) exit;
/**
 * @var array $orders
 * @var \BusinessEngine\Customers\DTOs\CustomerDTO[] $customers
 * @var \BusinessEngine\Products\DTOs\ProductDTO[] $products
 * @var string $search
 * @var string $status
 */

$statusMap = [
    'pendente'    => ['label' => 'Orçamento / Pendente', 'color' => '#d97706', 'bg' => '#fef3c7'],
    'confirmado'  => ['label' => 'Confirmado',            'color' => '#2563eb', 'bg' => '#dbeafe'],
    'producao'    => ['label' => 'Em Produção',          'color' => '#7c3aed', 'bg' => '#f3e8ff'],
    'rota'        => ['label' => 'Pronto / Em Rota',     'color' => '#0891b2', 'bg' => '#cffafe'],
    'concluido'   => ['label' => 'Entregue / Concluído',  'color' => '#16a34a', 'bg' => '#dcfce7'],
    'cancelado'   => ['label' => 'Cancelado',             'color' => '#dc2626', 'bg' => '#fee2e2'],
];
?>

<div class="be-wrap">
    
    <!-- Cabeçalho Oficial -->
    <div class="be-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; width: 100%;">
        <div class="be-header-title">
            <h1><?php echo esc_html(be_term('orders_plural')); ?></h1>
            <p>Acompanhe os pedidos recebidos, prazos de entrega e controle o status operacional de produção.</p>
        </div>
        <div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=be-customers')); ?>" class="be-pill-btn" style="height: 38px; padding: 0 16px; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; text-decoration: none; font-weight: 700;">
                <span><?php echo esc_html(be_term('customers_plural')); ?></span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros e Busca Universal (.be-toolbar-standard) -->
    <div class="be-card" style="padding: 12px 16px; margin-bottom: 16px;">
        <form method="get" class="be-toolbar-standard">
            <input type="hidden" name="page" value="be-orders">
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(be_term('search_orders_ph')); ?>" class="be-search-input">
            
            <select name="status" class="be-filter-select">
                <option value="">Todos os Status</option>
                <?php foreach ($statusMap as $k => $info): ?>
                    <option value="<?php echo esc_attr($k); ?>" <?php selected($status, $k); ?>><?php echo esc_html($info['label']); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="be-btn-primary">Filtrar</button>
            <?php if (!empty($search) || !empty($status)): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=be-orders')); ?>" class="be-pill-btn">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Barra Superior da Tabela -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; width: 100%;">
        <div>
            <span style="font-size: 13px; font-weight: 600; color: var(--be-text-muted);" id="lbl-total-orders">
                <?php echo count($orders); ?> <?php echo esc_html(strtolower(be_term('orders_singular'))); ?>(s) registrado(s)
            </span>
        </div>
        <div>
            <button type="button" class="be-btn-primary" onclick="openOrderModal()" style="height: 34px; font-size: 13px;">
                + Novo <?php echo esc_html(be_term('orders_singular')); ?>
            </button>
        </div>
    </div>

    <!-- Tabela Grade de Pedidos com Mesmas Porcentagens Fixas -->
    <div class="be-card" style="padding: 0;">
        <table class="be-interactive-table" style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed;">
            <thead>
                <tr style="background: var(--be-bg); border-bottom: 1px solid var(--be-border-subtle); color: var(--be-text-muted);">
                    <th class="col-align-left" style="padding: 10px 14px; width: 28%;">Identificação & Cliente</th>
                    <th style="padding: 10px 10px; width: 16%;">Status de Produção</th>
                    <th style="padding: 10px 10px; width: 16%;">Data / Entrega</th>
                    <th style="padding: 10px 10px; width: 16%;">Forma Pagamento</th>
                    <th style="padding: 10px 10px; width: 16%;">Valor Total</th>
                    <th style="padding: 10px 10px; width: 8%;"><?php echo esc_html(be_term('col_actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr id="row-empty-orders">
                        <td colspan="6" style="text-align: center; padding: 36px; color: var(--be-text-muted);">
                            Nenhum pedido registrado ainda. Clique em "+ Novo <?php echo esc_html(be_term('orders_singular')); ?>" para começar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): 
                        $st = $statusMap[$o['status']] ?? ['label' => $o['status'], 'color' => '#64748b', 'bg' => '#f1f5f9'];
                    ?>
                        <tr id="order-row-<?php echo (int)$o['id']; ?>" style="border-bottom: 1px solid var(--be-border-subtle);">
                            <td class="col-align-left" style="padding: 8px 14px; vertical-align: middle;">
                                <div style="font-weight: 700; color: var(--be-primary); font-size: 13.5px;">
                                    <?php echo esc_html($o['customer_name'] ?: 'Cliente Não Informado'); ?>
                                </div>
                                <div style="font-size: 11.5px; color: var(--be-text-muted); margin-top: 2px;">
                                    <span style="font-family: monospace; font-weight: 600; color: var(--be-accent);"><?php echo esc_html($o['order_number']); ?></span>
                                    <?php if (!empty($o['customer_phone'])): ?>
                                        &bull; <?php echo esc_html($o['customer_phone']); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding: 6px 10px; vertical-align: middle; text-align: center;">
                                <select class="be-modal-input order-inline-status" style="width: 100%; font-weight: 700; color: <?php echo esc_attr($st['color']); ?>; background: <?php echo esc_attr($st['bg']); ?> !important;" onchange="quickUpdateOrderStatus(<?php echo (int)$o['id']; ?>, this.value)">
                                    <?php foreach ($statusMap as $k => $info): ?>
                                        <option value="<?php echo esc_attr($k); ?>" <?php selected($o['status'], $k); ?>><?php echo esc_html($info['label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding: 8px 10px; vertical-align: middle; text-align: center;">
                                <div style="font-weight: 600; color: var(--be-primary);">
                                    <?php echo !empty($o['delivery_date']) ? esc_html(date('d/m/Y', strtotime($o['delivery_date']))) : '—'; ?>
                                </div>
                                <small style="color: var(--be-text-muted); font-size: 11px;">
                                    <?php echo esc_html(ucfirst($o['delivery_type'])); ?> <?php echo !empty($o['delivery_time']) ? 'às ' . esc_html($o['delivery_time']) : ''; ?>
                                </small>
                            </td>
                            <td style="padding: 8px 10px; vertical-align: middle; text-align: center;">
                                <span class="be-badge" style="background: #f1f5f9; color: var(--be-primary); font-size: 11px;">
                                    <?php echo esc_html(strtoupper($o['payment_method'])); ?>
                                </span>
                                <span style="font-size: 11px; margin-left: 4px; color: <?php echo $o['is_paid'] ? '#16a34a' : '#dc2626'; ?>; font-weight: 700;">
                                    <?php echo $o['is_paid'] ? '(Pago)' : '(Pendente)'; ?>
                                </span>
                            </td>
                            <td style="padding: 8px 10px; vertical-align: middle; text-align: center;">
                                <strong style="color: var(--be-primary); font-size: 14px; font-variant-numeric: tabular-nums;">
                                    R$ <?php echo number_format((float)$o['total_amount'], 2, ',', '.'); ?>
                                </strong>
                            </td>
                            <td style="padding: 8px 10px; text-align: center; vertical-align: middle;">
                                <div class="be-actions-cell">
                                    <button type="button" class="be-icon-btn" onclick="openOrderModal(<?php echo (int)$o['id']; ?>)" title="Editar Pedido">
                                        <svg viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.8 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/></svg>
                                    </button>
                                    <button type="button" class="be-icon-btn be-icon-btn-del" onclick="deleteOrder(<?php echo (int)$o['id']; ?>)" title="Excluir">
                                        <svg viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Construtor de Pedidos -->
<div id="be-order-modal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="be-card" style="width: 860px; max-width: 95%; max-height: 90vh; overflow-y: auto; overflow-x: hidden; margin-bottom: 0; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); box-sizing: border-box;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--be-border-subtle); padding-bottom: 12px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--be-primary);" id="modal-order-title">Novo Pedido</h2>
            <button type="button" onclick="closeOrderModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--be-text-muted);">&times;</button>
        </div>

        <form id="be-order-builder-form">
            <input type="hidden" name="order[id]" id="ord_id" value="">

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Cliente Comprador *</label>
                    <select name="order[customer_id]" id="ord_customer" class="be-modal-input" style="width: 100%;" required>
                        <option value="">Selecione um cliente cadastrado...</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?php echo esc_attr((string)$c->id); ?>"><?php echo esc_html($c->name); ?> (<?php echo esc_html($c->phone ?: 'sem fone'); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Status Atual</label>
                    <select name="order[status]" id="ord_status" class="be-modal-input" style="width: 100%;">
                        <?php foreach ($statusMap as $k => $info): ?>
                            <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($info['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Data da Entrega</label>
                    <input type="date" name="order[delivery_date]" id="ord_date" class="be-modal-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Horário Previsto</label>
                    <input type="time" name="order[delivery_time]" id="ord_time" class="be-modal-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Tipo de Entrega</label>
                    <select name="order[delivery_type]" id="ord_type" class="be-modal-input" style="width: 100%;">
                        <option value="entrega">Entrega no Endereço</option>
                        <option value="retirada">Retirada no Balcão</option>
                    </select>
                </div>
            </div>

            <!-- Grade de Itens do Pedido -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--be-primary);">Itens do Pedido</strong>
                    <button type="button" class="be-pill-btn" onclick="addOrderItemRow(0, 1, 0, true)">+ Adicionar Produto</button>
                </div>

                <div style="border: 1px solid var(--be-border-subtle); border-radius: 6px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed;" id="tbl-order-items">
                        <thead>
                            <tr style="background: var(--be-bg); border-bottom: 1px solid var(--be-border-subtle); color: var(--be-text-muted);">
                                <th style="padding: 8px 12px; width: 50%; text-align: left;">Produto Comercial</th>
                                <th style="padding: 8px 12px; width: 16%; text-align: center;">Quantidade</th>
                                <th style="padding: 8px 12px; width: 16%; text-align: right;">Preço Unit.</th>
                                <th style="padding: 8px 12px; width: 14%; text-align: right;">Subtotal</th>
                                <th style="padding: 8px 6px; width: 4%; text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody id="order-items-tbody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Painel de Fechamento Financeiro: 4 Colunas -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; background: var(--be-bg); border: 1px solid var(--be-border-subtle); border-radius: 8px; padding: 14px; margin-bottom: 20px; text-align: center; box-sizing: border-box;">
                <div style="border-right: 1px solid var(--be-border-subtle);">
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Soma dos Itens</span>
                    <div style="font-size: 16px; font-weight: 800; color: var(--be-primary);" id="lbl_ord_subtotal">R$ 0,00</div>
                </div>
                <div style="border-right: 1px solid var(--be-border-subtle);">
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Frete / Entrega</span>
                    <input type="number" step="1" min="0" name="order[delivery_fee]" id="ord_delivery_fee" value="0.00" class="be-modal-input" style="width: 80px; text-align: right; font-weight: 700; margin: 0 auto;">
                </div>
                <div style="border-right: 1px solid var(--be-border-subtle);">
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Desconto</span>
                    <input type="number" step="1" min="0" name="order[discount_amount]" id="ord_discount" value="0.00" class="be-modal-input" style="width: 80px; text-align: right; font-weight: 700; margin: 0 auto;">
                </div>
                <div>
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Valor Total</span>
                    <div style="font-size: 18px; font-weight: 800; color: #16a34a;" id="lbl_ord_total">R$ 0,00</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Forma de Pagamento</label>
                    <select name="order[payment_method]" id="ord_payment" class="be-modal-input" style="width: 100%;">
                        <option value="pix">PIX</option>
                        <option value="cartao_credito">Cartão de Crédito</option>
                        <option value="cartao_debito">Cartão de Débito</option>
                        <option value="dinheiro">Dinheiro</option>
                    </select>
                </div>
                <div style="display: flex; align-items: center; padding-top: 20px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="order[is_paid]" id="ord_is_paid" value="1" style="width: 18px; height: 18px;">
                        <span>Pedido já está pago</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="be-pill-btn" onclick="closeOrderModal()">Cancelar</button>
                <button type="submit" class="be-btn-primary">Salvar Pedido</button>
            </div>
        </form>
    </div>
</div>

<script>
const availableProducts = <?php echo json_encode(array_map(fn($p) => [
    'id'    => (int)$p->id,
    'name'  => $p->name,
    'price' => (float)$p->finalPrice
], $products)); ?>;

let orderItemIndex = 0;

function quickUpdateOrderStatus(id, newStatus) {
    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_quick_update_order_status',
        id: id,
        status: newStatus,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) {
            window.location.reload();
        } else {
            alert(res.data?.message || 'Erro ao atualizar.');
        }
    });
}

function openOrderModal(id = 0) {
    orderItemIndex = 0;
    document.getElementById('order-items-tbody').innerHTML = '';
    document.getElementById('ord_id').value = '';
    document.getElementById('be-order-builder-form').reset();

    if (id > 0) {
        document.getElementById('modal-order-title').innerText = 'Editar Pedido';
        jQuery.get(beSettings.ajaxUrl, { action: 'be_get_order_details', id: id, nonce: beSettings.nonce }, function(res) {
            if (res.success) {
                const o = res.data.order;
                document.getElementById('ord_id').value = o.id;
                document.getElementById('ord_customer').value = o.customer_id;
                document.getElementById('ord_status').value = o.status;
                document.getElementById('ord_date').value = o.delivery_date;
                document.getElementById('ord_time').value = o.delivery_time;
                document.getElementById('ord_type').value = o.delivery_type;
                document.getElementById('ord_delivery_fee').value = o.delivery_fee;
                document.getElementById('ord_discount').value = o.discount_amount;
                document.getElementById('ord_payment').value = o.payment_method;
                document.getElementById('ord_is_paid').checked = (o.is_paid == 1);

                (res.data.items || []).forEach(item => {
                    addOrderItemRow(item.product_id, item.quantity, item.unit_price_snapshot, false);
                });
                recalcOrderLive();
            }
        });
    } else {
        document.getElementById('modal-order-title').innerText = 'Novo Pedido';
        addOrderItemRow(0, 1, 0, true);
        recalcOrderLive();
    }

    document.getElementById('be-order-modal').style.display = 'flex';
}

function closeOrderModal() {
    document.getElementById('be-order-modal').style.display = 'none';
}

function addOrderItemRow(selectedProdId = 0, qty = 1, unitPrice = 0, isNew = false) {
    const tbody = document.getElementById('order-items-tbody');
    const idx = orderItemIndex++;

    let options = '<option value="">Selecione um produto comercial...</option>';
    availableProducts.forEach(p => {
        const isSel = (parseInt(p.id) === parseInt(selectedProdId)) ? 'selected' : '';
        options += `<option value="${p.id}" data-price="${p.price}" ${isSel}>${p.name} (R$ ${p.price.toFixed(2)})</option>`;
    });

    const tr = document.createElement('tr');
    tr.className = 'order-item-row';
    tr.style.borderBottom = '1px solid var(--be-border-subtle)';
    tr.innerHTML = `
        <td style="padding: 6px 12px; vertical-align: middle;">
            <select name="items[${idx}][product_id]" class="be-modal-input ord-sel-product" style="width: 100%;" required>
                ${options}
            </select>
        </td>
        <td style="padding: 6px 12px; vertical-align: middle; text-align: center;">
            <input type="number" step="1" min="1" name="items[${idx}][quantity]" value="${qty || 1}" class="be-modal-input ord-inp-qty" style="width: 70px; text-align: center; font-weight: 700;" required>
        </td>
        <td style="padding: 6px 12px; vertical-align: middle; text-align: right;">
            <input type="number" step="0.10" min="0" name="items[${idx}][unit_price_snapshot]" value="${unitPrice || 0}" class="be-modal-input ord-inp-price" style="width: 85px; text-align: right; font-weight: 700;" required>
        </td>
        <td style="padding: 6px 12px; vertical-align: middle; text-align: right;">
            <strong class="ord-lbl-subtotal" style="color: var(--be-primary); font-size: 13px;">R$ 0,00</strong>
            <input type="hidden" name="items[${idx}][subtotal]" class="ord-inp-subtotal" value="0">
        </td>
        <td style="padding: 6px 6px; text-align: center; vertical-align: middle;">
            <button type="button" class="be-icon-btn be-icon-btn-del" onclick="this.closest('tr').remove(); recalcOrderLive();">
                <svg viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);

    const selProd = tr.querySelector('.ord-sel-product');
    const inpQty  = tr.querySelector('.ord-inp-qty');
    const inpPrice= tr.querySelector('.ord-inp-price');

    selProd.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            inpPrice.value = parseFloat(opt.getAttribute('data-price')) || 0;
            recalcOrderLive();
        }
    });

    inpQty.addEventListener('input', recalcOrderLive);
    inpPrice.addEventListener('input', recalcOrderLive);

    if (isNew) setTimeout(() => selProd.focus(), 50);
}

function recalcOrderLive() {
    let subtotal = 0;

    document.querySelectorAll('.order-item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.ord-inp-qty').value) || 0;
        const price = parseFloat(row.querySelector('.ord-inp-price').value) || 0;
        const lineTotal = qty * price;

        row.querySelector('.ord-inp-subtotal').value = lineTotal.toFixed(2);
        row.querySelector('.ord-lbl-subtotal').innerText = 'R$ ' + lineTotal.toFixed(2).replace('.', ',');
        subtotal += lineTotal;
    });

    const deliveryFee = parseFloat(document.getElementById('ord_delivery_fee').value) || 0;
    const discount = parseFloat(document.getElementById('ord_discount').value) || 0;
    const total = Math.max(0, (subtotal + deliveryFee) - discount);

    document.getElementById('lbl_ord_subtotal').innerText = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
    document.getElementById('lbl_ord_total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
}

document.getElementById('ord_delivery_fee').addEventListener('input', recalcOrderLive);
document.getElementById('ord_discount').addEventListener('input', recalcOrderLive);

document.getElementById('be-order-builder-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = jQuery(this).serialize();
    jQuery.post(beSettings.ajaxUrl, data + '&action=be_save_order&nonce=' + beSettings.nonce, function(res) {
        if (res.success) {
            alert('Pedido salvo com sucesso!');
            window.location.reload();
        } else {
            alert('Erro ao salvar: ' + (res.data?.message || 'Tente novamente.'));
        }
    });
});

function deleteOrder(id) {
    if (!confirm('Deseja excluir este pedido?')) return;
    jQuery.post(beSettings.ajaxUrl, { action: 'be_delete_order', id: id, nonce: beSettings.nonce }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir.');
    });
}
</script>