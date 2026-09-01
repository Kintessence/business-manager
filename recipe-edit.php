<?php
if (!defined('ABSPATH')) exit;
/** @var object|null $recipe */
/** @var array $items */
/** @var array $supplies */
/** @var float $cMin */
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <a href="admin.php?page=be-recipes" style="font-size:12px; text-decoration:none; color:var(--be-accent); font-weight:700;">← Voltar para a lista</a>
                <h1 style="font-size:22px; margin:4px 0 0; font-weight:800;">
                    <?php echo $recipe ? 'Editar Ficha: ' . esc_html($recipe->name) : 'Nova Ficha Técnica'; ?>
                </h1>
            </div>
            <button type="button" class="be-btn-primary" onclick="saveRecipe()">Salvar Ficha Técnica 💾</button>
        </div>

        <form id="be-recipe-form">
            <input type="hidden" id="recipe_id" value="<?php echo esc_attr((string)($recipe->id ?? 0)); ?>">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 12px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Nome da Receita</label>
                    <input type="text" id="recipe_name" value="<?php echo esc_attr($recipe->name ?? ''); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Rendimento (Qtd)</label>
                    <input type="number" step="0.1" id="recipe_yield_qty" value="<?php echo esc_attr((string)($recipe->yield_qty ?? 1)); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" oninput="calcRecipeCosts()">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Unid. Rendimento</label>
                    <input type="text" id="recipe_yield_unit" value="<?php echo esc_attr($recipe->yield_unit ?? 'porções'); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Preparo (min)</label>
                    <input type="number" id="recipe_prep_time" value="<?php echo esc_attr((string)($recipe->prep_time_min ?? 0)); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" oninput="calcRecipeCosts()">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Forno/Cocção (min)</label>
                    <input type="number" id="recipe_bake_time" value="<?php echo esc_attr((string)($recipe->bake_time_min ?? 0)); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" oninput="calcRecipeCosts()">
                </div>
            </div>

            <h3 style="font-size:15px; font-weight:700; margin: 20px 0 8px;">🧂 Ingredientes & Insumos da Composição</h3>
            <table class="widefat" id="table-recipe-items" style="border:1px solid var(--be-border); border-radius:6px; margin-bottom: 12px;">
                <thead>
                    <tr>
                        <th style="width: 45%;">Insumo</th>
                        <th style="width: 20%;">Qtd. Utilizada</th>
                        <th style="width: 15%;">Unidade</th>
                        <th style="width: 15%;">Custo Insumo</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody id="recipe-items-body">
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $it): ?>
                            <tr class="recipe-row" data-supply-id="<?php echo (int)$it->supply_id; ?>">
                                <td>
                                    <select class="item-supply-select" style="width:100%;" onchange="updateRowUnit(this)">
                                        <?php foreach ($supplies as $s): ?>
                                            <option value="<?php echo (int)$s->id; ?>" 
                                                data-cost="<?php echo esc_attr((string)$s->pkg_cost); ?>" 
                                                data-size="<?php echo esc_attr((string)$s->pkg_size); ?>" 
                                                data-unit="<?php echo esc_attr($s->unit_type); ?>" 
                                                data-use-unit="<?php echo esc_attr($s->use_unit); ?>"
                                                <?php selected($s->id, $it->supply_id); ?>>
                                                <?php echo esc_html($s->name . ' (' . $s->pkg_size . ' ' . $s->unit_type . ' - R$ ' . number_format((float)$s->pkg_cost, 2, ',', '.') . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" class="item-qty" value="<?php echo esc_attr((string)$it->quantity); ?>" style="width:100%;" oninput="calcRecipeCosts()"></td>
                                <td><span class="item-unit-label be-badge"><?php echo esc_html($it->measure_type); ?></span></td>
                                <td><strong class="item-subtotal">R$ 0,00</strong></td>
                                <td><button type="button" class="button button-small" onclick="this.closest('tr').remove(); calcRecipeCosts();">✕</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="button" class="be-pill-btn" onclick="addRecipeRow()">➕ Adicionar Ingrediente</button>
        </form>
    </div>

    <!-- Painel de Resumo -->
    <div class="be-dark-panel">
        <h3 style="margin: 0 0 4px; font-size: 16px; font-weight: 700;">💰 Resumo de Custos da Ficha Técnica</h3>
        <div class="be-metrics-grid">
            <div class="be-metric-card">
                <div class="val" id="kpi-cost-ingredients">R$ 0,00</div>
                <div class="lbl">Custo dos Ingredientes</div>
            </div>
            <div class="be-metric-card">
                <div class="val" id="kpi-cost-time">R$ 0,00</div>
                <div class="lbl">Mão de Obra Operacional</div>
            </div>
            <div class="be-metric-card">
                <div class="val" id="kpi-cost-total" style="color:#f59e0b !important;">R$ 0,00</div>
                <div class="lbl">Custo Total do Lote</div>
            </div>
            <div class="be-metric-card">
                <div class="val" id="kpi-cost-unit" style="color:#10b981 !important;">R$ 0,00</div>
                <div class="lbl">Custo Unitário da Porção</div>
            </div>
        </div>
    </div>
</div>

<script>
const cMin = <?php echo (float)$cMin; ?>;
const availableSupplies = <?php echo json_encode($supplies); ?>;

function addRecipeRow() {
    if (availableSupplies.length === 0) return alert('Cadastre ou importe insumos primeiro.');
    const tbody = document.getElementById('recipe-items-body');
    const tr = document.createElement('tr');
    tr.className = 'recipe-row';
    let options = availableSupplies.map(s => `<option value="${s.id}" data-cost="${s.pkg_cost}" data-size="${s.pkg_size}" data-unit="${s.unit_type}" data-use-unit="${s.use_unit}">${s.name} (${s.pkg_size} ${s.unit_type})</option>`).join('');
    tr.innerHTML = `
        <td><select class="item-supply-select" style="width:100%;" onchange="updateRowUnit(this)">${options}</select></td>
        <td><input type="number" step="0.01" class="item-qty" value="100" style="width:100%;" oninput="calcRecipeCosts()"></td>
        <td><span class="item-unit-label be-badge">${availableSupplies[0].use_unit || 'g'}</span></td>
        <td><strong class="item-subtotal">R$ 0,00</strong></td>
        <td><button type="button" class="button button-small" onclick="this.closest('tr').remove(); calcRecipeCosts();">✕</button></td>
    `;
    tbody.appendChild(tr);
    calcRecipeCosts();
}

function updateRowUnit(select) {
    const opt = select.options[select.selectedIndex];
    select.closest('tr').querySelector('.item-unit-label').innerText = opt.getAttribute('data-use-unit') || 'g';
    calcRecipeCosts();
}

function calcRecipeCosts() {
    let totalIng = 0;
    document.querySelectorAll('.recipe-row').forEach(row => {
        const select = row.querySelector('.item-supply-select');
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const opt = select.options[select.selectedIndex];
        
        const cost = parseFloat(opt.getAttribute('data-cost')) || 0;
        const size = parseFloat(opt.getAttribute('data-size')) || 1;
        const unit = opt.getAttribute('data-unit');
        const useUnit = opt.getAttribute('data-use-unit');

        let normSize = size;
        if ((unit === 'kg' && useUnit === 'g') || (unit === 'L' && useUnit === 'ml')) normSize *= 1000;

        const sub = qty * (normSize > 0 ? cost / normSize : 0);
        row.querySelector('.item-subtotal').innerText = 'R$ ' + sub.toFixed(2).replace('.', ',');
        totalIng += sub;
    });

    const timeCost = ((parseFloat(document.getElementById('recipe_prep_time').value)||0) + (parseFloat(document.getElementById('recipe_bake_time').value)||0)) * cMin;
    const total = totalIng + timeCost;
    const yieldQty = Math.max(0.01, parseFloat(document.getElementById('recipe_yield_qty').value)||1);

    document.getElementById('kpi-cost-ingredients').innerText = 'R$ ' + totalIng.toFixed(2).replace('.', ',');
    document.getElementById('kpi-cost-time').innerText = 'R$ ' + timeCost.toFixed(2).replace('.', ',');
    document.getElementById('kpi-cost-total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
    document.getElementById('kpi-cost-unit').innerText = 'R$ ' + (total/yieldQty).toFixed(2).replace('.', ',');
}

function saveRecipe() {
    const name = document.getElementById('recipe_name').value.trim();
    if (!name) return alert('Informe o nome.');

    const items = [];
    document.querySelectorAll('.recipe-row').forEach(row => {
        items.push({
            supply_id: row.querySelector('.item-supply-select').value,
            quantity: parseFloat(row.querySelector('.item-qty').value) || 0,
            measure_type: row.querySelector('.item-unit-label').innerText
        });
    });

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_save_recipe',
        id: document.getElementById('recipe_id').value,
        name: name,
        yield_qty: document.getElementById('recipe_yield_qty').value,
        yield_unit: document.getElementById('recipe_yield_unit').value,
        prep_time_min: document.getElementById('recipe_prep_time').value,
        bake_time_min: document.getElementById('recipe_bake_time').value,
        items: items,
        nonce: beSettings.nonce
    }, res => {
        if (res.success) window.location.href = res.data.redirect;
        else alert(res.data?.message || 'Erro.');
    });
}
document.addEventListener('DOMContentLoaded', calcRecipeCosts);
</script>