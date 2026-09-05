<?php
if (!defined('ABSPATH')) exit;
/**
 * @var \BusinessEngine\Recipes\DTOs\RecipeDTO[] $recipes
 * @var string[] $categories
 * @var \BusinessEngine\Ingredients\DTOs\IngredientDTO[] $supplies
 * @var \BusinessEngine\BusinessProfile\DTOs\BusinessProfileDTO $profile
 * @var string $search
 * @var string $category
 * @var int $productsCount
 */

$yieldUnits = ['porções', 'unidades', 'g', 'kg', 'ml', 'L', 'fatias', 'copos'];
$defaultCategories = ['Massas', 'Recheios', 'Coberturas', 'Montagens', 'Geral'];
$allCategories = array_unique(array_merge($defaultCategories, $categories));
?>

<div class="be-wrap">
    
    <!-- Cabeçalho Oficial -->
    <div class="be-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; width: 100%;">
        <div class="be-header-title">
            <h1><?php echo esc_html(be_term('recipes_plural')); ?></h1>
            <p>Crie as bases, massas e recheios somando os insumos consumidos ao tempo de produção.</p>
        </div>
        <div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=be-products')); ?>" class="be-pill-btn" style="height: 38px; padding: 0 16px; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; text-decoration: none; font-weight: 700;">
                <span><?php echo esc_html(be_term('products_plural')); ?></span>
                <span class="be-badge be-badge-info" style="font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: 800; background: #dbeafe; color: #1e40af;">
                    <?php echo esc_html((string)$productsCount); ?>
                </span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros e Busca Universal -->
    <div class="be-card" style="padding: 12px 16px; margin-bottom: 16px;">
        <form method="get" class="be-toolbar-standard">
            <input type="hidden" name="page" value="be-recipes">
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(be_term('search_recipes_ph')); ?>" class="be-search-input">
            
            <select name="category" class="be-filter-select">
                <option value=""><?php echo esc_html(be_term('all_categories_filter')); ?></option>
                <?php foreach ($allCategories as $cat): ?>
                    <option value="<?php echo esc_attr($cat); ?>" <?php selected($category, $cat); ?>><?php echo esc_html($cat); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="be-btn-primary">Filtrar</button>
            <?php if (!empty($search) || !empty($category)): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=be-recipes')); ?>" class="be-pill-btn">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Barra Superior da Tabela -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; width: 100%;">
        <div>
            <span style="font-size: 13px; font-weight: 600; color: var(--be-text-muted);" id="lbl-total-recipes">
                <?php echo count($recipes); ?> <?php echo esc_html(strtolower(be_term('recipes_singular'))); ?>(s) cadastrada(s)
            </span>
        </div>
        <div>
            <button type="button" class="be-btn-primary" onclick="openRecipeModal()" style="height: 34px; font-size: 13px;">
                + Nova <?php echo esc_html(be_term('recipes_singular')); ?>
            </button>
        </div>
    </div>

    <!-- Tabela Grade de Fichas Técnicas com Mesmas Porcentagens -->
    <div class="be-card" style="padding: 0;">
        <table class="be-interactive-table" style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed;">
            <thead>
                <tr style="background: var(--be-bg); border-bottom: 1px solid var(--be-border-subtle); color: var(--be-text-muted);">
                    <th class="col-align-left" style="padding: 10px 14px; width: 28%;"><?php echo esc_html(be_term('col_name')); ?></th>
                    <th style="padding: 10px 10px; width: 16%;"><?php echo esc_html(be_term('col_category')); ?></th>
                    <th style="padding: 10px 10px; width: 14%;"><?php echo esc_html(be_term('col_yield')); ?> Lote</th>
                    <th style="padding: 10px 10px; width: 11%;"><?php echo esc_html(be_term('col_prep_time')); ?></th>
                    <th style="padding: 10px 10px; width: 11%;">Custo Lote</th>
                    <th style="padding: 10px 10px; width: 12%;">Custo Unitário</th>
                    <th style="padding: 10px 10px; width: 8%;"><?php echo esc_html(be_term('col_actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recipes)): ?>
                    <tr id="row-empty-recipes">
                        <td colspan="7" style="text-align: center; padding: 36px; color: var(--be-text-muted);">
                            Nenhum registro cadastrado ainda. Clique em "+ Nova <?php echo esc_html(be_term('recipes_singular')); ?>" para começar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recipes as $r): ?>
                        <tr id="rec-row-<?php echo (int)$r->id; ?>" style="border-bottom: 1px solid var(--be-border-subtle);">
                            <td class="col-align-left" style="padding: 6px 14px; vertical-align: middle;">
                                <input type="text" class="be-modal-input rec-inline-name" value="<?php echo esc_attr($r->name); ?>" style="width: 100%; font-weight: 600;" onblur="quickUpdateRecipe(<?php echo (int)$r->id; ?>)" onkeydown="if(event.key==='Enter') this.blur();">
                            </td>
                            <td style="padding: 6px 10px; vertical-align: middle;">
                                <select class="be-modal-input rec-inline-cat" style="width: 100%;" onchange="quickUpdateRecipe(<?php echo (int)$r->id; ?>)">
                                    <?php foreach ($allCategories as $cat): ?>
                                        <option value="<?php echo esc_attr($cat); ?>" <?php selected($r->category, $cat); ?>><?php echo esc_html($cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding: 6px 10px; vertical-align: middle; text-align: center;">
                                <div style="display: inline-flex; gap: 4px; align-items: center; justify-content: center;">
                                    <input type="number" step="0.1" min="0.1" class="be-modal-input rec-inline-qty" value="<?php echo esc_attr((string)$r->yieldQty); ?>" style="width: 65px; text-align: right; font-weight: 600;" onblur="quickUpdateRecipe(<?php echo (int)$r->id; ?>)" onkeydown="if(event.key==='Enter') this.blur();">
                                    <select class="be-modal-input rec-inline-unit" style="min-width: 75px;" onchange="quickUpdateRecipe(<?php echo (int)$r->id; ?>)">
                                        <?php foreach ($yieldUnits as $u): ?>
                                            <option value="<?php echo esc_attr($u); ?>" <?php selected($r->yieldUnit, $u); ?>><?php echo esc_html($u); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                            <td style="padding: 10px 10px; vertical-align: middle; text-align: center; color: var(--be-text-muted);">
                                <?php echo (int)$r->prepTimeMinutes > 0 ? (int)$r->prepTimeMinutes . ' min' : '—'; ?>
                            </td>
                            <td style="padding: 10px 10px; vertical-align: middle; text-align: center; font-weight: 600;">
                                R$ <?php echo number_format($r->totalCost, 2, ',', '.'); ?>
                            </td>
                            <td style="padding: 10px 10px; vertical-align: middle; text-align: center;">
                                <strong class="rec-unit-cost-val" style="color: var(--be-accent); font-size: 13px;">
                                    R$ <?php echo number_format($r->unitCost, 4, ',', '.'); ?>
                                </strong>
                            </td>
                            <td style="padding: 10px 10px; text-align: center; vertical-align: middle;">
                                <div class="be-actions-cell">
                                    <button type="button" class="be-icon-btn" onclick="openRecipeModal(<?php echo (int)$r->id; ?>)" title="Editar">
                                        <svg viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.8 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/></svg>
                                    </button>
                                    <button type="button" class="be-icon-btn be-icon-btn-del" onclick="deleteRecipe(<?php echo (int)$r->id; ?>)" title="Excluir">
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

<!-- Modal Construtor de Ficha Técnica -->
<div id="be-recipe-modal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="be-card" style="width: 760px; max-width: 95%; max-height: 90vh; overflow-y: auto; overflow-x: hidden; margin-bottom: 0; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--be-border-subtle); padding-bottom: 12px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--be-primary);" id="modal-recipe-title">Nova <?php echo esc_html(be_term('recipes_singular')); ?></h2>
            <button type="button" onclick="closeRecipeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--be-text-muted);">&times;</button>
        </div>

        <form id="be-recipe-builder-form" style="margin: 0; width: 100%; box-sizing: border-box;">
            <input type="hidden" name="recipe[id]" id="rec_id" value="">

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Nome da <?php echo esc_html(be_term('recipes_singular')); ?> *</label>
                    <input type="text" name="recipe[name]" id="rec_name" class="be-modal-input" required placeholder="Ex: Brigadeiro Tradicional Base" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);"><?php echo esc_html(be_term('col_category')); ?></label>
                    <select name="recipe[category]" id="rec_category" class="be-modal-input" style="width: 100%;">
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);"><?php echo esc_html(be_term('col_yield')); ?> do Lote *</label>
                    <input type="number" step="0.1" min="0.1" name="recipe[yield_qty]" id="rec_yield_qty" value="1.0" class="be-modal-input" style="width: 100%; font-weight: 600;" required>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Unidade de <?php echo esc_html(be_term('col_yield')); ?></label>
                    <select name="recipe[yield_unit]" id="rec_yield_unit" class="be-modal-input" style="width: 100%;">
                        <?php foreach ($yieldUnits as $u): ?>
                            <option value="<?php echo esc_attr($u); ?>"><?php echo esc_html($u); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">
                        <?php echo esc_html(be_term('col_prep_time')); ?> (min)
                        <span class="be-help-tip" data-tip="Minutos dedicados à execução. Multiplica pelo custo do minuto (Cmin) calibrado no Setup.">?</span>
                    </label>
                    <input type="number" step="1" min="0" name="recipe[prep_time_minutes]" id="rec_prep_time" value="0" class="be-modal-input" style="width: 100%; font-weight: 600;">
                </div>
            </div>

            <!-- Grade de Insumos da Receita -->
            <div style="margin-bottom: 20px; width: 100%; box-sizing: border-box;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--be-primary);"><?php echo esc_html(be_term('supplies_plural')); ?> Utilizados</strong>
                    <button type="button" class="be-pill-btn" onclick="addRecipeIngredientRow(0, 1, 'g', 0, true)">+ Adicionar <?php echo esc_html(be_term('supplies_singular')); ?></button>
                </div>

                <div style="border: 1px solid var(--be-border-subtle); border-radius: 6px; overflow: hidden; width: 100%; box-sizing: border-box;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed;" id="tbl-recipe-items">
                        <thead>
                            <tr style="background: var(--be-bg); border-bottom: 1px solid var(--be-border-subtle); color: var(--be-text-muted);">
                                <th style="padding: 8px 12px; width: 42%; text-align: left; vertical-align: middle;"><?php echo esc_html(be_term('supplies_singular')); ?></th>
                                <th style="padding: 8px 12px; width: 30%; text-align: left; vertical-align: middle;">Qtd & Unidade</th>
                                <th style="padding: 8px 12px; width: 20%; text-align: right; vertical-align: middle;">Subtotal</th>
                                <th style="padding: 8px 6px; width: 8%; text-align: center; vertical-align: middle;"></th>
                            </tr>
                        </thead>
                        <tbody id="recipe-items-tbody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Resumo Financeiro da Ficha: 3 Colunas Centralizadas -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; background: var(--be-bg); border: 1px solid var(--be-border-subtle); border-radius: 8px; padding: 14px; margin-bottom: 20px; text-align: center; box-sizing: border-box; width: 100%;">
                <div style="border-right: 1px solid var(--be-border-subtle);">
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;"><?php echo esc_html(be_term('col_direct_cost')); ?></span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--be-primary);" id="lbl_rec_supplies_cost">R$ 0,00</div>
                </div>
                <div style="border-right: 1px solid var(--be-border-subtle);">
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Mão de Obra (Tempo)</span>
                    <div style="font-size: 18px; font-weight: 800; color: #16a34a;" id="lbl_rec_labor_cost">R$ 0,00</div>
                </div>
                <div>
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Custo por Porção</span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--be-accent);" id="lbl_rec_unit_cost">R$ 0,00</div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="be-pill-btn" onclick="closeRecipeModal()">Cancelar</button>
                <button type="submit" class="be-btn-primary">Salvar <?php echo esc_html(be_term('recipes_singular')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
const costPerMinute = <?php echo (float)$profile->costPerMinute; ?>;
const availableSupplies = <?php echo json_encode(array_map(fn($s) => [
    'id'        => $s->id,
    'name'      => $s->name,
    'unit_cost' => $s->unitCostCalculated,
    'pkg_cost'  => $s->pkgCost,
    'pkg_size'  => $s->pkgSize,
    'pkg_type'  => $s->pkgType,
    'use_unit'  => $s->useUnit
], $supplies)); ?>;

let recipeItemIndex = 0;

document.getElementById('rec_name').addEventListener('input', function() {
    const isEdit = document.getElementById('rec_id').value !== '';
    const titlePrefix = isEdit ? 'Editar <?php echo esc_js(be_term('recipes_singular')); ?>' : 'Nova <?php echo esc_js(be_term('recipes_singular')); ?>';
    const val = this.value.trim();
    document.getElementById('modal-recipe-title').innerText = val ? `${titlePrefix}: ${val}` : titlePrefix;
});

function quickUpdateRecipe(id) {
    const row = document.getElementById('rec-row-' + id);
    if (!row) return;

    const nameVal = row.querySelector('.rec-inline-name').value.trim();
    const catVal = row.querySelector('.rec-inline-cat').value;
    const qtyVal = parseFloat(row.querySelector('.rec-inline-qty').value) || 1.0;
    const unitVal = row.querySelector('.rec-inline-unit').value;

    if (!nameVal) return;

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_quick_update_recipe',
        id: id,
        name: nameVal,
        category: catVal,
        yield_qty: qtyVal,
        yield_unit: unitVal,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) {
            row.querySelector('.rec-unit-cost-val').innerText = res.data.unit_cost_formatted;
            row.classList.add('be-row-updated');
            setTimeout(() => row.classList.remove('be-row-updated'), 1000);
        } else {
            alert(res.data?.message || 'Erro ao atualizar.');
        }
    });
}

function cleanupEmptyRecipeRows() {
    document.querySelectorAll('.rec-item-row.rec-row-new').forEach(row => {
        const sel = row.querySelector('.rec-sel-supply');
        if (!sel || !sel.value) row.remove();
    });
    recalcRecipeLive();
}

function openRecipeModal(id = 0) {
    recipeItemIndex = 0;
    document.getElementById('recipe-items-tbody').innerHTML = '';
    document.getElementById('rec_id').value = '';
    document.getElementById('be-recipe-builder-form').reset();

    if (id > 0) {
        document.getElementById('modal-recipe-title').innerText = 'Carregando <?php echo esc_js(be_term('recipes_singular')); ?>...';
        jQuery.get(beSettings.ajaxUrl, { action: 'be_get_recipe_details', id: id, nonce: beSettings.nonce }, function(res) {
            if (res.success) {
                const r = res.data.recipe;
                document.getElementById('rec_id').value = r.id;
                document.getElementById('rec_name').value = r.name;
                document.getElementById('rec_category').value = r.category;
                document.getElementById('rec_yield_qty').value = r.yield_qty;
                document.getElementById('rec_yield_unit').value = r.yield_unit;
                document.getElementById('rec_prep_time').value = r.prep_time_minutes;

                document.getElementById('modal-recipe-title').innerText = 'Editar <?php echo esc_js(be_term('recipes_singular')); ?>: ' + r.name;

                (res.data.items || []).forEach(item => {
                    addRecipeIngredientRow(item.supply_id, item.quantity, item.unit, item.unit_cost_snapshot, false);
                });
                recalcRecipeLive();
            }
        });
    } else {
        document.getElementById('modal-recipe-title').innerText = 'Nova <?php echo esc_js(be_term('recipes_singular')); ?>';
        addRecipeIngredientRow(0, 1, 'g', 0, true);
        recalcRecipeLive();
    }

    document.getElementById('be-recipe-modal').style.display = 'flex';
}

function closeRecipeModal() {
    cleanupEmptyRecipeRows();
    document.getElementById('be-recipe-modal').style.display = 'none';
}

function addRecipeIngredientRow(selectedSupplyId = 0, qty = 1, selectedUnit = 'g', unitCostSnap = 0, isNew = false) {
    cleanupEmptyRecipeRows();

    const tbody = document.getElementById('recipe-items-tbody');
    const idx = recipeItemIndex++;

    let options = '<option value="">Selecione um item...</option>';
    availableSupplies.forEach(s => {
        const isSel = (s.id == selectedSupplyId) ? 'selected' : '';
        options += `<option value="${s.id}" data-cost="${s.unit_cost}" data-pkgcost="${s.pkg_cost}" data-unit="${s.use_unit}" data-pkgtype="${s.pkg_type}" ${isSel}>${s.name} (R$ ${s.unit_cost.toFixed(4)}/${s.use_unit})</option>`;
    });

    const tr = document.createElement('tr');
    tr.className = 'rec-item-row' + (isNew ? ' rec-row-new' : '');
    tr.style.borderBottom = '1px solid var(--be-border-subtle)';
    tr.innerHTML = `
        <td style="padding: 6px 12px; vertical-align: middle;">
            <select name="items[${idx}][supply_id]" class="be-modal-input rec-sel-supply" style="width: 100%;" required>
                ${options}
            </select>
            <input type="hidden" name="items[${idx}][unit_cost_snapshot]" class="rec-inp-snap" value="${unitCostSnap}">
            <input type="hidden" name="items[${idx}][subtotal_cost]" class="rec-inp-subtotal" value="0">
        </td>
        <td style="padding: 6px 12px; vertical-align: middle;">
            <div style="display: flex; gap: 6px; align-items: center;">
                <input type="number" step="any" min="0.001" name="items[${idx}][quantity]" value="${qty || 1}" class="be-modal-input rec-inp-qty" style="width: 80px !important; padding: 0 6px !important; font-weight: 600; text-align: right;" required>
                <select name="items[${idx}][unit]" class="be-modal-input rec-sel-unit" style="min-width: 65px; flex: 1;">
                    <option value="g" ${selectedUnit === 'g' ? 'selected' : ''}>g</option>
                    <option value="kg" ${selectedUnit === 'kg' ? 'selected' : ''}>kg</option>
                    <option value="ml" ${selectedUnit === 'ml' ? 'selected' : ''}>ml</option>
                    <option value="L" ${selectedUnit === 'L' ? 'selected' : ''}>L</option>
                    <option value="un" ${selectedUnit === 'un' ? 'selected' : ''}>un</option>
                    <option value="pkg" ${selectedUnit === 'pkg' ? 'selected' : ''}>Embalagem (cheia)</option>
                </select>
            </div>
        </td>
        <td style="padding: 6px 12px; vertical-align: middle; text-align: right;">
            <strong class="rec-lbl-subtotal" style="color: var(--be-primary); font-size: 13px;">R$ 0,00</strong>
        </td>
        <td style="padding: 6px 6px; text-align: center; vertical-align: middle;">
            <button type="button" class="be-icon-btn be-icon-btn-del" onclick="this.closest('tr').remove(); recalcRecipeLive();">
                <svg viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);

    tr.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', recalcRecipeLive);
        el.addEventListener('change', recalcRecipeLive);
    });

    const sel = tr.querySelector('.rec-sel-supply');
    sel.addEventListener('change', function() {
        if (this.value) tr.classList.remove('rec-row-new');
        const opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) return;
        const unit = opt.getAttribute('data-unit') || 'g';
        const pkgType = opt.getAttribute('data-pkgtype') || 'Embalagem';
        
        const unitSel = tr.querySelector('.rec-sel-unit');
        const pkgOpt = unitSel.querySelector('option[value="pkg"]');
        if (pkgOpt) pkgOpt.innerText = pkgType + ' (cheia)';
        
        unitSel.value = unit;
        recalcRecipeLive();
    });

    if (selectedSupplyId > 0) {
        const opt = sel.options[sel.selectedIndex];
        if (opt) {
            const pkgType = opt.getAttribute('data-pkgtype') || 'Embalagem';
            const pkgOpt = tr.querySelector('.rec-sel-unit option[value="pkg"]');
            if (pkgOpt) pkgOpt.innerText = pkgType + ' (cheia)';
        }
    }

    if (isNew) {
        setTimeout(() => sel.focus(), 50);
    }
}

document.getElementById('be-recipe-modal').addEventListener('mousedown', function(e) {
    const targetRow = e.target.closest('.rec-item-row');
    document.querySelectorAll('.rec-item-row.rec-row-new').forEach(row => {
        if (row !== targetRow) {
            const sel = row.querySelector('.rec-sel-supply');
            if (!sel || !sel.value) {
                row.remove();
                recalcRecipeLive();
            }
        }
    });
});

function recalcRecipeLive() {
    let suppliesTotal = 0;

    document.querySelectorAll('.rec-item-row').forEach(row => {
        const sel = row.querySelector('.rec-sel-supply');
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;

        const unitCost = parseFloat(opt.getAttribute('data-cost')) || 0;
        const pkgCost  = parseFloat(opt.getAttribute('data-pkgcost')) || 0;
        const qty      = parseFloat(row.querySelector('.rec-inp-qty').value) || 0;
        const chosenUnit = row.querySelector('.rec-sel-unit').value;

        let subtotal = 0;
        if (chosenUnit === 'pkg') {
            subtotal = pkgCost * qty;
        } else if (chosenUnit === 'kg' || chosenUnit === 'L') {
            subtotal = (unitCost * 1000) * qty;
        } else {
            subtotal = unitCost * qty;
        }

        row.querySelector('.rec-inp-snap').value = unitCost;
        row.querySelector('.rec-inp-subtotal').value = subtotal.toFixed(4);
        row.querySelector('.rec-lbl-subtotal').innerText = 'R$ ' + subtotal.toFixed(2).replace('.', ',');

        suppliesTotal += subtotal;
    });

    const prepMin = Math.max(0, parseInt(document.getElementById('rec_prep_time').value) || 0);
    const laborTotal = prepMin * costPerMinute;
    const yieldQty = Math.max(0.0001, parseFloat(document.getElementById('rec_yield_qty').value) || 1.0);
    const grandTotal = suppliesTotal + laborTotal;
    const unitCost = grandTotal / yieldQty;

    document.getElementById('lbl_rec_supplies_cost').innerText = 'R$ ' + suppliesTotal.toFixed(2).replace('.', ',');
    document.getElementById('lbl_rec_labor_cost').innerText = 'R$ ' + laborTotal.toFixed(2).replace('.', ',');
    document.getElementById('lbl_rec_unit_cost').innerText = 'R$ ' + unitCost.toFixed(4).replace('.', ',');
}

document.querySelectorAll('#be-recipe-builder-form input, #be-recipe-builder-form select').forEach(el => {
    el.addEventListener('input', recalcRecipeLive);
    el.addEventListener('change', recalcRecipeLive);
});

document.getElementById('be-recipe-builder-form').addEventListener('submit', function(e) {
    e.preventDefault();
    cleanupEmptyRecipeRows();
    const data = jQuery(this).serialize();
    jQuery.post(beSettings.ajaxUrl, data + '&action=be_save_recipe&nonce=' + beSettings.nonce, function(res) {
        if (res.success) {
            alert('Ficha técnica salva com sucesso!');
            window.location.reload();
        } else {
            alert('Erro ao salvar: ' + (res.data?.message || 'Tente novamente.'));
        }
    });
});

function deleteRecipe(id) {
    if (!confirm('Deseja excluir esta ficha técnica?')) return;
    jQuery.post(beSettings.ajaxUrl, { action: 'be_delete_recipe', id: id, nonce: beSettings.nonce }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir.');
    });
}
</script>