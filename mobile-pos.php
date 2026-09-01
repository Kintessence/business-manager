<?php
if (!defined('ABSPATH')) exit;
/** @var object $load */
/** @var array $items */
?>
<div class="wrap be-wrap">
    <div class="be-card" style="max-width: 500px; margin: 0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
            <div>
                <a href="admin.php?page=be-street-sales" style="font-size:12px; text-decoration:none; color:var(--be-accent); font-weight:700;">← Sair do PDV</a>
                <h2 style="font-size:18px; margin:4px 0 0; font-weight:800;">📱 PDV de Rua: <?php echo esc_html($load->seller_name); ?></h2>
            </div>
            <span class="be-badge be-badge-success">Carga #<?php echo (int)$load->id; ?></span>
        </div>

        <div style="background:#0f172a; color:#fff; padding:16px; border-radius:10px; text-align:center; margin-bottom:18px;">
            <span style="font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#94a3b8;">Total da Venda Atual</span>
            <div id="pos-total-display" style="font-size:32px; font-weight:800; color:#10b981; margin-top:2px;">R$ 0,00</div>
        </div>

        <h3 style="font-size:14px; font-weight:700; margin-bottom:10px;">Selecione os Produtos:</h3>
        <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px;">
            <?php foreach ($items as $it): ?>
                <div style="background:#f8fafc; border:1px solid var(--be-border); padding:12px; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <strong style="font-size:14px;"><?php echo esc_html($it->product_name); ?></strong>
                        <small style="display:block; color:var(--be-primary); font-weight:700;">R$ <?php echo number_format((float)$it->unit_price, 2, ',', '.'); ?></small>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <button type="button" class="button" onclick="alterPosQty(<?php echo (int)$it->product_id; ?>, -1, <?php echo (float)$it->unit_price; ?>)">-</button>
                        <span id="pos-qty-<?php echo (int)$it->product_id; ?>" style="font-size:16px; font-weight:800; width:24px; text-align:center;">0</span>
                        <button type="button" class="be-btn-primary" style="padding:4px 12px;" onclick="alterPosQty(<?php echo (int)$it->product_id; ?>, 1, <?php echo (float)$it->unit_price; ?>)">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 style="font-size:14px; font-weight:700; margin-bottom:10px;">Forma de Pagamento:</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:8px; margin-bottom:20px;">
            <button type="button" class="be-pill-btn pos-pay-btn active" style="padding:10px 0; text-align:center;" onclick="selectPay(this, 'pix')">⚡ PIX</button>
            <button type="button" class="be-pill-btn pos-pay-btn" style="padding:10px 0; text-align:center;" onclick="selectPay(this, 'cash')">💵 Dinheiro</button>
            <button type="button" class="be-pill-btn pos-pay-btn" style="padding:10px 0; text-align:center;" onclick="selectPay(this, 'card')">💳 Cartão</button>
        </div>

        <button type="button" class="be-btn-primary" style="width:100%; padding:14px; font-size:16px;" onclick="finishSale()">Confirmar Venda ✅</button>
    </div>
</div>

<script>
let cart = {};
let currentPay = 'pix';

function alterPosQty(pId, delta, price) {
    if (!cart[pId]) cart[pId] = { qty: 0, price: price };
    cart[pId].qty = Math.max(0, cart[pId].qty + delta);
    document.getElementById('pos-qty-' + pId).innerText = cart[pId].qty;
    recalcPosTotal();
}

function recalcPosTotal() {
    let total = 0;
    for (let pId in cart) {
        total += cart[pId].qty * cart[pId].price;
    }
    document.getElementById('pos-total-display').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
}

function selectPay(btn, method) {
    document.querySelectorAll('.pos-pay-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentPay = method;
}

function finishSale() {
    let total = 0;
    for (let pId in cart) total += cart[pId].qty * cart[pId].price;
    if (total <= 0) return alert('Selecione pelo menos 1 item para vender.');

    alert('Venda de R$ ' + total.toFixed(2).replace('.', ',') + ' (' + currentPay.toUpperCase() + ') registrada com sucesso!');
    for (let pId in cart) {
        cart[pId].qty = 0;
        document.getElementById('pos-qty-' + pId).innerText = '0';
    }
    recalcPosTotal();
}
</script>
<style>
.pos-pay-btn.active {
    background: var(--be-primary) !important;
    color: #fff !important;
    border-color: var(--be-primary) !important;
}
</style>