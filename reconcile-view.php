<?php
if (!defined('ABSPATH')) exit;
/** @var object|null $load */
/** @var array $items */
/** @var array $products */

$isNew = empty($load);
?>
<div class="wrap be-wrap">
    <div class="be-card" style="max-width: 800px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <a href="admin.php?page=be-street-sales" style="font-size:12px; text-decoration:none; color:var(--be-accent); font-weight:700;">← Voltar para Cargas</a>
                <h1 style="font-size:22px; margin:4px 0 0; font-weight:800;">
                    <?php echo $isNew ? '➕ Nova Saída de Carga (Pronta-Entrega)' : '⚖️ Acerto e Fechamento Diário de Carga'; ?>
                </h1>
            </div>
        </div>

        <?php if ($isNew): ?>
            <!-- Formulario de Criacao de Carga -->
            <form id="form-new-load">
                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Nome do Vendedor / Rota *</label>
                        <input type="text" id="load_seller" placeholder="Ex: Lucas (Caixa 01 - Praia da Costa)" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" required>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Data de Saída</label>
                        <input type="date" id="load_date" value="<?php echo esc_attr(current_time('Y-m-d')); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" required>
                    </div>
                </div>

                <h3 style="font-size:15px; font-weight:700; margin:16px 0 8px;">Itens Carregados na Caixa / Isopor:</h3>
                <table class="widefat" style="border:1px solid var(--be-border); border-radius:6px; margin-bottom: 16px;">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th style="width: 25%;">Preço Unitário (R$)</th>
                            <th style="width: 25%;">Qtd Carregada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $idx => $p): ?>
                            <tr class="item-load-row">
                                <td>
                                    <strong><?php echo esc_html($p->name); ?></strong>
                                    <input type="hidden" class="p_id" value="<?php echo (int)$p->id; ?>">
                                </td>
                                <td>
                                    <input type="number" step="0.5" class="p_price" value="<?php echo esc_attr((string)$p->final_price); ?>" style="width:100%; padding:6px; border:1px solid var(--be-border); border-radius:4px;">
                                </td>
                                <td>
                                    <input type="number" step="1" min="0" class="p_qty" value="0" style="width:100%; padding:6px; border:1px solid var(--be-border); border-radius:4px; font-weight:700;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="button" class="be-btn-primary" onclick="saveNewLoad()">Liberar Saída de Carga 🚀</button>
            </form>

        <?php else: ?>
            <!-- Formulario de Fechamento / Acerto de Caixa -->
            <div style="background:#f8fafc; border:1px solid var(--be-border); padding:14px; border-radius:8px; margin-bottom:18px;">
                <strong>Vendedor / Rota:</strong> <?php echo esc_html($load->seller_name); ?> | <strong>Data:</strong> <?php echo esc_html($load->load_date); ?>
            </div>

            <form id="form-close-load">
                <input type="hidden" id="close_load_id" value="<?php echo (int)$load->id; ?>">
                
                <h3 style="font-size:15px; font-weight:700; margin:0 0 8px;">1. Conferência das Sobras / Retornos:</h3>
                <table class="widefat" style="border:1px solid var(--be-border); border-radius:6px; margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th style="text-align:center;">Saída</th>
                            <th style="text-align:center; width:20%;">Retorno (Sobras)</th>
                            <th style="text-align:center;">Qtd Vendida</th>
                            <th style="text-align:right;">Subtotal Esperado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalExpected = 0.0;
                        foreach ($items as $it): 
                            $sold = max(0, $it->initial_qty - $it->returned_qty);
                            $sub = $sold * $it->unit_price;
                            $totalExpected += $sub;
                        ?>
                            <tr class="rec-row" data-initial="<?php echo (int)$it->initial_qty; ?>" data-price="<?php echo (float)$it->unit_price; ?>">
                                <td><strong><?php echo esc_html($it->product_name); ?></strong></td>
                                <td style="text-align:center;"><?php echo (int)$it->initial_qty; ?> un</td>
                                <td>
                                    <input type="number" step="1" min="0" max="<?php echo (int)$it->initial_qty; ?>" name="returns[<?php echo (int)$it->id; ?>]" class="ret-input" value="<?php echo (int)$it->returned_qty; ?>" style="width:100%; text-align:center; padding:6px; border:1px solid var(--be-border); border-radius:4px;" oninput="recalcReconciliation()" <?php echo $load->status === 'closed' ? 'disabled' : ''; ?>>
                                </td>
                                <td style="text-align:center;"><strong class="sold-display"><?php echo $sold; ?> un</strong></td>
                                <td style="text-align:right;"><strong class="sub-display" style="color:var(--be-primary);">R$ <?php echo number_format($sub, 2, ',', '.'); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="background:#eff6ff; border:1px solid #bfdbfe; padding:16px; border-radius:8px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:700; color:#1e40af;">Faturamento Total Esperado na Carga:</span>
                    <span style="font-size:24px; font-weight:800; color:var(--be-primary);" id="total-expected-badge">R$ <?php echo number_format($totalExpected, 2, ',', '.'); ?></span>
                </div>

                <h3 style="font-size:15px; font-weight:700; margin:0 0 8px;">2. Valores Apurados & Entregues:</h3>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">💵 Dinheiro Físico (R$)</label>
                        <input type="number" step="0.01" id="cash_received" value="<?php echo esc_attr((string)$load->cash_received); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; font-weight:700;" oninput="recalcReconciliation()" <?php echo $load->status === 'closed' ? 'disabled' : ''; ?>>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">⚡ PIX Recebido (R$)</label>
                        <input type="number" step="0.01" id="pix_received" value="<?php echo esc_attr((string)$load->pix_received); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; font-weight:700;" oninput="recalcReconciliation()" <?php echo $load->status === 'closed' ? 'disabled' : ''; ?>>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">💳 Cartão / Maquininha (R$)</label>
                        <input type="number" step="0.01" id="card_received" value="<?php echo esc_attr((string)$load->card_received); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; font-weight:700;" oninput="recalcReconciliation()" <?php echo $load->status === 'closed' ? 'disabled' : ''; ?>>
                    </div>
                </div>

                <!-- Box de Diferenca / Quebra de Caixa -->
                <div id="box-diff" style="padding:14px; border-radius:8px; margin-bottom:20px; font-weight:700;"></div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Observações do Fechamento</label>
                    <textarea id="close_notes" rows="2" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" <?php echo $load->status === 'closed' ? 'disabled' : ''; ?>><?php echo esc_textarea($load->notes ?? ''); ?></textarea>
                </div>

                <?php if ($load->status === 'open'): ?>
                    <button type="button" class="be-btn-primary" onclick="closeLoad()">Concluir Acerto de Caixa 🔒</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function saveNewLoad() {
    const seller = document.getElementById('load_seller').value.trim();
    if (!seller) return alert('Informe o nome do vendedor.');

    const items = [];
    document.querySelectorAll('.item-load-row').forEach(row => {
        const qty = parseInt(row.querySelector('.p_qty').value) || 0;
        if (qty > 0) {
            items.push({
                product_id: row.querySelector('.p_id').value,
                price: parseFloat(row.querySelector('.p_price').value) || 0,
                qty: qty
            });
        }
    });

    if (items.length === 0) return alert('Carregue pelo menos 1 produto na carga.');

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_save_seller_load',
        seller_name: seller,
        load_date: document.getElementById('load_date').value,
        items: items,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.href = res.data.redirect;
        else alert(res.data?.message || 'Erro ao gerar carga.');
    });
}

function recalcReconciliation() {
    let totalExpected = 0;
    document.querySelectorAll('.rec-row').forEach(row => {
        const initial = parseInt(row.getAttribute('data-initial')) || 0;
        const price = parseFloat(row.getAttribute('data-price')) || 0;
        const ret = parseInt(row.querySelector('.ret-input').value) || 0;
        const sold = Math.max(0, initial - ret);
        const sub = sold * price;

        row.querySelector('.sold-display').innerText = sold + ' un';
        row.querySelector('.sub-display').innerText = 'R$ ' + sub.toFixed(2).replace('.', ',');
        totalExpected += sub;
    });

    document.getElementById('total-expected-badge').innerText = 'R$ ' + totalExpected.toFixed(2).replace('.', ',');

    const cash = parseFloat(document.getElementById('cash_received').value) || 0;
    const pix = parseFloat(document.getElementById('pix_received').value) || 0;
    const card = parseFloat(document.getElementById('card_received').value) || 0;
    const totalHand = cash + pix + card;
    const diff = totalHand - totalExpected;

    const box = document.getElementById('box-diff');
    if (Math.abs(diff) < 0.01) {
        box.style.background = '#f0fdf4';
        box.style.color = '#166534';
        box.innerText = '✅ Caixa 100% Batido! Total entregue confere perfeitamente com as vendas apuradas.';
    } else if (diff < 0) {
        box.style.background = '#fef2f2';
        box.style.color = '#991b1b';
        box.innerText = '⚠️ Quebra / Faltando no Caixa: R$ ' + Math.abs(diff).toFixed(2).replace('.', ',');
    } else {
        box.style.background = '#eff6ff';
        box.style.color = '#1e40af';
        box.innerText = 'ℹ️ Sobra no Caixa: R$ ' + diff.toFixed(2).replace('.', ',');
    }
}

function closeLoad() {
    if (!confirm('Deseja realmente encerrar e fechar o caixa desta carga?')) return;

    const returns = {};
    document.querySelectorAll('.rec-row').forEach(row => {
        const input = row.querySelector('.ret-input');
        const name = input.getAttribute('name').replace('returns[', '').replace(']', '');
        returns[name] = parseInt(input.value) || 0;
    });

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_close_seller_load',
        load_id: document.getElementById('close_load_id').value,
        cash_received: document.getElementById('cash_received').value,
        pix_received: document.getElementById('pix_received').value,
        card_received: document.getElementById('card_received').value,
        notes: document.getElementById('close_notes').value,
        returns: returns,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.href = res.data.redirect;
        else alert(res.data?.message || 'Erro ao fechar caixa.');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.rec-row')) recalcReconciliation();
});
</script>