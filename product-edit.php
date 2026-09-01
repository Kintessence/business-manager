<?php
if (!defined('ABSPATH')) exit;
/** @var object|null $product */
/** @var array $items */
/** @var array $supplies */
/** @var array $recipesMap */
/** @var float $cMin */
/** @var array $profile */

$roles = [
    1 => ['name' => 'Produto foco', 'desc' => 'Carro-chefe; o item principal pelo qual o negócio é reconhecido.'],
    2 => ['name' => 'Abridor de carteira', 'desc' => 'Produto mais acessível para facilitar a compra de um cliente novo.'],
    3 => ['name' => 'Gerador de caixa', 'desc' => 'Produto com boa margem e alta saída para sustentar os custos fixos.'],
    4 => ['name' => 'Aumentador de pedido', 'desc' => 'Item de impulso ("vou levar esse também") para elevar o ticket médio.'],
    5 => ['name' => 'Experimentação', 'desc' => 'Formato menor para o cliente experimentar antes de compras maiores.'],
];
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <a href="admin.php?page=be-products" style="font-size:12px; text-decoration:none; color:var(--be-accent); font-weight:700;">← Voltar ao Catálogo</a>
                <h1 style="font-size:22px; margin:4px 0 0; font-weight:800;">
                    <?php echo $product ? 'Editar Produto: ' . esc_html($product->name) : 'Novo Produto Comercial'; ?>
                </h1>
            </div>
            <button type="button" class="be-btn-primary" onclick="saveProduct()">Salvar Produto 💾</button>
        </div>

        <form id="be-product-form">
            <input type="hidden" id="product_id" value="<?php echo esc_attr((string)($product->id ?? 0)); ?>">

            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Nome do Produto Final</label>
                    <input type="text" id="prod_name" value="<?php echo esc_attr($product->name ?? ''); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">SKU / Código</label>
                    <input type="text" id="prod_sku" value="<?php echo esc_attr($product->sku ?? ''); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Categoria</label>
                    <input type="text" id="prod_category" value="<?php echo esc_attr($product->category ?? 'Geral'); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;">
                </div>
            </div>

            <!-- Seletor de Função Estratégica -->
            <div style="margin-bottom: 20px; background:#f8fafc; border:1px solid var(--be-border); padding:14px; border-radius:8px;">
                <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px; color:#1e293b;">🎯 Função Estratégica no Cardápio *</label>
                <select id="prod_strategic_role" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" onchange="updateRoleDesc(this)">
                    <?php foreach ($roles as $rId => $rInfo): ?>
                        <option value="<?php echo $rId; ?>" data-desc="<?php echo esc_attr($rInfo['desc']); ?>" <?php selected((int)($product->strategic_role ?? 1), $rId); ?>>
                            <?php echo esc_html($rInfo['name']); ?> — <?php echo esc_html($rInfo['desc']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small id="role_desc_helper" style="display:block; margin-top:6px; color:var(--be-text-muted); font-size:12px;"></small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Tempo Montagem / Embalagem (min)</label>
                    <input type="number" id="prod_time" value="<?php echo esc_attr((string)($product->production_time_min ?? 15)); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" oninput="calcProductPricing()">
                    <small style="color:var(--be-text-muted);">Custo/min: R$ <?php echo number_format($cMin, 4, ',', '.'); ?></small>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Margem Líquida Pretendida (%)</label>
                    <input type="number" step="0.5" id="prod_margin" value="<?php echo esc_attr((string)($product->target_margin ?? $profile['margin'] ?? 25)); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" oninput="calcProductPricing()">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Preço Praticado de Venda (R$)</label>
                    <input type="number" step="0.05" id="prod_final_price" value="<?php echo esc_attr((string)($product->final_price ?? '')); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; font-weight:700; color:var(--be-primary);" placeholder="Preço final">
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin: 20px 0 8px;">
                <h3 style="font-size:15px; font-weight:700; margin:0;">📦 Composição: Fichas Técnicas & Embalagens</h3>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="be-pill-btn" onclick="addProductRow('recipe')">➕ Ficha Técnica</button>
                    <button type="button" class="be-pill-btn" onclick="addProductRow('supply')">➕ Embalagem / Insumo</button>
                </div>
            </div>

            <table class="widefat" id="table-product-items" style="border:1px solid var(--be-border); border-radius:6px; margin-bottom: 12px;">
                <thead>
                    <tr>
                        <th style="width: 20%;">Tipo</th>
                        <th style="width: 45%;">Item</th>
                        <th style="width: 15%;">Quantidade</th>
                        <th style="width: 15%;">Subtotal</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody id="product-items-body">
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $it): ?>
                            <tr class="prod-row" data-type="<?php echo esc_attr($it->item_type); ?>">
                                <td><span class="be-badge"><?php echo $it->item_type === 'recipe' ? 'Ficha Técnica' : 'Embalagem'; ?></span></td>
                                <td>
                                    <select class="prod-item-select" style="width:100%;" onchange="calcProductPricing()">
                                        <?php if ($it->item_type === 'recipe'): ?>
                                            <?php foreach ($recipesMap as $r): ?>
                                                <option value="<?php echo (int)$r['id']; ?>" data-cost="<?php echo esc_attr((string)$r['portion_cost']); ?>" <?php selected($r['id'], $it->item_id); ?>>
                                                    <?php echo esc_html($r['name'] . ' (R$ ' . number_format($r['portion_cost'], 2, ',', '.') . '/' . $r['yield_unit'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <?php foreach ($supplies as $s): ?>
                                                <option value="<?php echo (int)$s->id; ?>" data-cost="<?php echo esc_attr((string)$s->pkg_cost); ?>" <?php selected($s->id, $it->item_id); ?>>
                                                    <?php echo esc_html($s->name . ' (R$ ' . number_format((float)$s->pkg_cost, 2, ',', '.') . '/' . $s->unit_type . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </td>
                                <td><input type="number" step="0.1" class="prod-item-qty" value="<?php echo esc_attr((string)$it->quantity); ?>" style="width:100%;" oninput="calcProductPricing()"></td>
                                <td><strong class="prod-item-subtotal">R$ 0,00</strong></td>
                                <td><button type="button" class="button button-small" onclick="this.closest('tr').remove(); calcProductPricing();">✕</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>

    <!-- Painel Raio-X -->
    <div class="be-dark-panel">
        <h3 style="margin: 0 0 4px; font-size: 16px; font-weight: 700;">🎯 Simulador & Formação de Preço</h3>
        <div class="be-metrics-grid">
            <div class="be-metric-card">
                <div class="val" id="kpi-prod-cmv">R$ 0,00</div>
                <div class="lbl">Custos Diretos (CMV + Emb.)</div>
            </div>
            <div class="be-metric-card">
                <div class="val" id="kpi-prod-labor">R$ 0,00</div>
                <div class="lbl">Mão de Obra de Acabamento</div>
            </div>
            <div class="be-metric-card">
                <div class="val" id="kpi-prod-total-cost" style="color:#f59e0b !important;">R$ 0,00</div>
                <div class="lbl">Custo Total de Fabricação</div>
            </div>
            <div class="be-metric-card">
                <div class="val" id="kpi-prod-suggested-price" style="color:#10b981 !important;">R$ 0,00</div>
                <div class="lbl">Preço de Venda Sugerido</div>
            </div>
        </div>
    </div>
</div>

<script>
const cMin = <?php echo (float)$cMin; ?>;
const recipesMap = <?php echo json_encode(array_values($recipesMap)); ?>;
const availableSupplies = <?php echo json_encode($supplies); ?>;

function addProductRow(type) {
    const tbody = document.getElementById('product-items-body');
    const tr = document.createElement('tr');
    tr.className = 'prod-row';
    tr.setAttribute('data-type', type);

    let options = '';
    if (type === 'recipe') {
        if (recipesMap.length === 0) return alert('Nenhuma ficha técnica cadastrada.');
        options = recipesMap.map(r => `<option value="${r.id}" data-cost="${r.portion_cost}">${r.name} (R$ ${r.portion_cost.toFixed(2).replace('.', ',')}/${r.yield_unit})</option>`).join('');
    } else {
        if (availableSupplies.length === 0) return alert('Nenhum insumo cadastrado.');
        options = availableSupplies.map(s => `<option value="${s.id}" data-cost="${s.pkg_cost}">${s.name} (R$ ${parseFloat(s.pkg_cost).toFixed(2).replace('.', ',')})</option>`).join('');
    }

    const badgeLabel = type === 'recipe' ? 'Ficha Técnica' : 'Embalagem';

    tr.innerHTML = `
        <td><span class="be-badge">${badgeLabel}</span></td>
        <td><select class="prod-item-select" style="width:100%;" onchange="calcProductPricing()">${options}</select></td>
        <td><input type="number" step="1" class="prod-item-qty" value="1" style="width:100%;" oninput="calcProductPricing()"></td>
        <td><strong class="prod-item-subtotal">R$ 0,00</strong></td>
        <td><button type="button" class="button button-small" onclick="this.closest('tr').remove(); calcProductPricing();">✕</button></td>
    `;
    tbody.appendChild(tr);
    calcProductPricing();
}

function calcProductPricing() {
    let directCosts = 0;
    document.querySelectorAll('.prod-row').forEach(row => {
        const select = row.querySelector('.prod-item-select');
        const qty = parseFloat(row.querySelector('.prod-item-qty').value) || 0;
        const opt = select.options[select.selectedIndex];
        const cost = parseFloat(opt.getAttribute('data-cost')) || 0;
        const sub = qty * cost;

        row.querySelector('.prod-item-subtotal').innerText = 'R$ ' + sub.toFixed(2).replace('.', ',');
        directCosts += sub;
    });

    const timeMin = parseFloat(document.getElementById('prod_time').value) || 0;
    const laborCost = timeMin * cMin;
    const totalCost = directCosts + laborCost;

    const margin = parseFloat(document.getElementById('prod_margin').value) || 25;
    const deductions = margin + 6.0 + 3.5;
    const divisor = Math.max(0.05, (100 - deductions) / 100);
    const suggestedPrice = totalCost > 0 ? (totalCost / divisor) : 0;

    const finalPriceInput = document.getElementById('prod_final_price');
    if (!finalPriceInput.value || parseFloat(finalPriceInput.value) === 0) {
        finalPriceInput.placeholder = 'Sugerido: R$ ' + suggestedPrice.toFixed(2).replace('.', ',');
    }

    document.getElementById('kpi-prod-cmv').innerText = 'R$ ' + directCosts.toFixed(2).replace('.', ',');
    document.getElementById('kpi-prod-labor').innerText = 'R$ ' + laborCost.toFixed(2).replace('.', ',');
    document.getElementById('kpi-prod-total-cost').innerText = 'R$ ' + totalCost.toFixed(2).replace('.', ',');
    document.getElementById('kpi-prod-suggested-price').innerText = 'R$ ' + suggestedPrice.toFixed(2).replace('.', ',');
}

function saveProduct() {
    const name = document.getElementById('prod_name').value.trim();
    if (!name) return alert('Informe o nome do produto.');

    const items = [];
    document.querySelectorAll('.prod-row').forEach(row => {
        items.push({
            item_type: row.getAttribute('data-type'),
            item_id: row.querySelector('.prod-item-select').value,
            quantity: parseFloat(row.querySelector('.prod-item-qty').value) || 1
        });
    });

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_save_product',
        id: document.getElementById('product_id').value,
        name: name,
        sku: document.getElementById('prod_sku').value,
        category: document.getElementById('prod_category').value,
        strategic_role: document.getElementById('prod_strategic_role').value,
        production_time_min: document.getElementById('prod_time').value,
        target_margin: document.getElementById('prod_margin').value,
        final_price: parseFloat(document.getElementById('prod_final_price').value) || 0,
        items: items,
        nonce: beSettings.nonce
    }, res => {
        if (res.success) window.location.href = res.data.redirect;
        else alert(res.data?.message || 'Erro ao gravar produto.');
    });
}

document.addEventListener('DOMContentLoaded', calcProductPricing);
</script>