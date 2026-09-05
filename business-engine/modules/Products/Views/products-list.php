<?php
if (!defined('ABSPATH')) exit;
/**
 * @var \BusinessEngine\Products\DTOs\ProductDTO[] $products
 * @var \BusinessEngine\Recipes\DTOs\RecipeDTO[] $recipes
 * @var \BusinessEngine\Ingredients\DTOs\IngredientDTO[] $packagingSupplies
 * @var \BusinessEngine\BusinessProfile\DTOs\BusinessProfileDTO $profile
 * @var string $search
 * @var string $role
 * @var int $ordersCount
 */

$rolesMap = [
    'isca'         => 'Produto Isca / Entrada',
    'carro_chefe'  => 'Carro-Chefe',
    'margem_alta'  => 'Margem Alta / Lucrativo',
    'combo'        => 'Combo / Kit Promocional',
    'sazonal'      => 'Sazonal / Edição Limitada',
];

$roleDescriptions = [
    'isca'         => ['title' => 'Produto Isca', 'desc' => 'Preço acessível para atrair novos clientes e abrir portas para itens mais lucrativos.'],
    'carro_chefe'  => ['title' => 'Carro-Chefe', 'desc' => 'O item mais vendido e equilibrado do cardápio. Garante volume constante e paga a estrutura fixa.'],
    'margem_alta'  => ['title' => 'Margem Alta', 'desc' => 'Item de valor agregado formulado para entregar margem líquida superior à média.'],
    'combo'        => ['title' => 'Combo / Kit', 'desc' => 'Agrupamento estratégico para aumentar o ticket médio e combinar giros.'],
    'sazonal'      => ['title' => 'Sazonal', 'desc' => 'Disponibilidade temporária com margem protegida e apelo de urgência.'],
];

$targetNetMargin = (float)($profile->targetNetMargin ?? 25.0);
$cardFeePercent  = (float)($profile->cardFeePercent ?? 3.5);
$taxRatePercent  = (float)($profile->taxRatePercent ?? 4.0);
?>

<div class="be-wrap">
    
    <!-- Cabeçalho Oficial -->
    <div class="be-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; width: 100%;">
        <div class="be-header-title">
            <h1><?php echo esc_html(be_term('products_plural')); ?></h1>
            <p>Monte os produtos finais combinando receitas, embalagens e tempo de montagem para formar o preço de venda.</p>
        </div>
        <div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=be-orders')); ?>" class="be-pill-btn" style="height: 38px; padding: 0 16px; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; text-decoration: none; font-weight: 700;">
                <span>Histórico de Pedidos</span>
                <span class="be-badge be-badge-info" style="font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: 800; background: #dbeafe; color: #1e40af;">
                    <?php echo esc_html((string)$ordersCount); ?>
                </span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros e Busca Universal -->
    <div class="be-card" style="padding: 12px 16px; margin-bottom: 16px;">
        <form method="get" class="be-toolbar-standard">
            <input type="hidden" name="page" value="be-products">
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(be_term('search_products_ph')); ?>" class="be-search-input">
            
            <select name="role" class="be-filter-select">
                <option value=""><?php echo esc_html(be_term('all_roles_filter')); ?></option>
                <?php foreach ($rolesMap as $key => $lbl): ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($role, $key); ?>><?php echo esc_html($lbl); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="be-btn-primary">Filtrar</button>
            <?php if (!empty($search) || !empty($role)): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=be-products')); ?>" class="be-pill-btn">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Barra Superior da Tabela -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; width: 100%;">
        <div>
            <span style="font-size: 13px; font-weight: 600; color: var(--be-text-muted);" id="lbl-total-products">
                <?php echo count($products); ?> <?php echo esc_html(strtolower(be_term('products_singular'))); ?>(s) no cardápio
            </span>
        </div>
        <div>
            <button type="button" class="be-btn-primary" onclick="openProductModal()" style="height: 34px; font-size: 13px;">
                + Novo <?php echo esc_html(be_term('products_singular')); ?>
            </button>
        </div>
    </div>

    <!-- Tabela Grade de Produtos com Mesmas Porcentagens -->
    <div class="be-card" style="padding: 0;">
        <table class="be-interactive-table" style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed;">
            <thead>
                <tr style="background: var(--be-bg); border-bottom: 1px solid var(--be-border-subtle); color: var(--be-text-muted);">
                    <th class="col-align-left" style="padding: 10px 14px; width: 28%;"><?php echo esc_html(be_term('col_name')); ?></th>
                    <th style="padding: 10px 10px; width: 16%;">Papel Estratégico</th>
                    <th style="padding: 10px 10px; width: 16%;"><?php echo esc_html(be_term('col_direct_cost')); ?></th>
                    <th style="padding: 10px 10px; width: 16%;"><?php echo esc_html(be_term('col_sale_price')); ?></th>
                    <th style="padding: 10px 10px; width: 16%;"><?php echo esc_html(be_term('col_margin')); ?></th>
                    <th style="padding: 10px 10px; width: 8%;"><?php echo esc_html(be_term('col_actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr id="row-empty-products">
                        <td colspan="6" style="text-align: center; padding: 36px; color: var(--be-text-muted);">
                            Nenhum registro cadastrado ainda. Clique em "+ Novo <?php echo esc_html(be_term('products_singular')); ?>" para começar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): 
                        $marginPct = ($p->finalPrice > 0) ? (($p->finalPrice - $p->directCost) / $p->finalPrice) * 100 : 0;
                    ?>
                        <tr id="prod-row-<?php echo (int)$p->id; ?>" style="border-bottom: 1px solid var(--be-border-subtle);">
                            <td class="col-align-left" style="padding: 6px 14px; vertical-align: middle;">
                                <input type="text" class="be-modal-input prod-inline-name" value="<?php echo esc_attr($p->name); ?>" style="width: 100%; font-weight: 600;" onblur="quickUpdateProduct(<?php echo (int)$p->id; ?>)" onkeydown="if(event.key==='Enter') this.blur();">
                            </td>
                            <td style="padding: 6px 10px; vertical-align: middle;">
                                <select class="be-modal-input prod-inline-role" style="width: 100%;" onchange="quickUpdateProduct(<?php echo (int)$p->id; ?>)">
                                    <?php foreach ($rolesMap as $k => $label): ?>
                                        <option value="<?php echo esc_attr($k); ?>" <?php selected($p->strategicRole, $k); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding: 10px 10px; vertical-align: middle; text-align: center; font-weight: 600; color: var(--be-text-muted);">
                                R$ <?php echo number_format($p->directCost, 2, ',', '.'); ?>
                            </td>
                            <td style="padding: 6px 10px; vertical-align: middle; text-align: center;">
                                <div style="display: inline-flex; gap: 4px; align-items: center; justify-content: center;">
                                    <span style="font-size: 11px; color: var(--be-text-muted); font-weight: 600;">R$</span>
                                    <input type="number" step="0.50" min="0" class="be-modal-input prod-inline-price" value="<?php echo esc_attr(number_format($p->finalPrice, 2, '.', '')); ?>" style="width: 85px; font-weight: 700; text-align: right;" onblur="quickUpdateProduct(<?php echo (int)$p->id; ?>)" onkeydown="if(event.key==='Enter') this.blur();">
                                </div>
                            </td>
                            <td style="padding: 10px 10px; vertical-align: middle; text-align: center;">
                                <strong style="color: <?php echo ($marginPct >= 30) ? '#16a34a' : '#ea580c'; ?>; font-size: 13px;">
                                    <?php echo number_format($marginPct, 1, ',', '.'); ?>%
                                </strong>
                            </td>
                            <td style="padding: 10px 10px; text-align: center; vertical-align: middle;">
                                <div class="be-actions-cell">
                                    <button type="button" class="be-icon-btn" onclick="openProductModal(<?php echo (int)$p->id; ?>)" title="Editar Composição">
                                        <svg viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.8 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/></svg>
                                    </button>
                                    <button type="button" class="be-icon-btn be-icon-btn-del" onclick="deleteProduct(<?php echo (int)$p->id; ?>)" title="Excluir">
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

<!-- Modal Construtor de Produto Comercial -->
<div id="be-product-modal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="be-card" style="width: 860px; max-width: 95%; max-height: 90vh; overflow-y: auto; overflow-x: hidden; margin-bottom: 0; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--be-border-subtle); padding-bottom: 12px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--be-primary);" id="modal-product-title">Novo <?php echo esc_html(be_term('products_singular')); ?></h2>
            <button type="button" onclick="closeProductModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--be-text-muted);">&times;</button>
        </div>

        <form id="be-product-builder-form">
            <input type="hidden" name="product[id]" id="prod_id" value="">

            <div style="display: grid; grid-template-columns: 1.5fr 1.5fr; gap: 14px; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Nome do <?php echo esc_html(be_term('products_singular')); ?> *</label>
                    <input type="text" name="product[name]" id="prod_name" class="be-modal-input" required placeholder="Ex: Bolo de Pote Ninho com Nutella 250ml" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Papel Estratégico</label>
                    <select name="product[strategic_role]" id="prod_role" class="be-modal-input" style="width: 100%;">
                        <?php foreach ($rolesMap as $k => $label): ?>
                            <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Guia Completo dos 5 Papéis -->
            <div style="margin-bottom: 18px; background: var(--be-bg); border: 1px solid var(--be-border-subtle); border-radius: 8px; padding: 12px;">
                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--be-text-muted); display: block; margin-bottom: 8px;">Guia de Engenharia de Cardápio (Clique para selecionar):</span>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 8px;">
                    <?php foreach ($roleDescriptions as $rk => $rdata): ?>
                        <div class="role-guide-card" id="guide-card-<?php echo esc_attr($rk); ?>" onclick="selectRoleByCard('<?php echo esc_attr($rk); ?>')" style="padding: 8px; border-radius: 6px; border: 1px solid var(--be-border-subtle); background: #fff; font-size: 11.5px; cursor: pointer; user-select: none; transition: all 0.15s ease;">
                            <strong style="display: block; color: var(--be-primary); margin-bottom: 2px; font-size: 12px; pointer-events: none;"><?php echo esc_html($rdata['title']); ?></strong>
                            <span style="color: var(--be-text-muted); line-height: 1.3; display: block; font-size: 11px; pointer-events: none;"><?php echo esc_html($rdata['desc']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Grade de Composição Híbrida -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--be-primary);">Composição do <?php echo esc_html(be_term('products_singular')); ?></strong>
                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="be-pill-btn" onclick="addProductItemRow('recipe', 0, 1, 0, true)">+ Adicionar <?php echo esc_html(be_term('recipes_singular')); ?></button>
                        <button type="button" class="be-pill-btn" onclick="addProductItemRow('supply', 0, 1, 0, true)">+ Adicionar Embalagem</button>
                    </div>
                </div>

                <div style="border: 1px solid var(--be-border-subtle); border-radius: 6px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed;" id="tbl-product-items">
                        <thead>
                            <tr style="background: var(--be-bg); border-bottom: 1px solid var(--be-border-subtle); color: var(--be-text-muted);">
                                <th style="padding: 8px 12px; width: 22%; text-align: left;">Tipo</th>
                                <th style="padding: 8px 12px; width: 40%; text-align: left;">Item Composto</th>
                                <th style="padding: 8px 12px; width: 16%; text-align: center;">Qtd / Porções</th>
                                <th style="padding: 8px 12px; width: 14%; text-align: right;">Subtotal</th>
                                <th style="padding: 8px 8px; width: 8%; text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody id="product-items-tbody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Painel de Precificação e Markup Divisor -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; background: var(--be-bg); border: 1px solid var(--be-border-subtle); border-radius: 8px; padding: 14px; margin-bottom: 20px; text-align: center;">
                <div style="border-right: 1px solid var(--be-border-subtle);">
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;"><?php echo esc_html(be_term('col_direct_cost')); ?></span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--be-primary);" id="lbl_prod_direct_cost">R$ 0,00</div>
                </div>
                <div style="border-right: 1px solid var(--be-border-subtle);">
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Preço Sugerido (Markup)</span>
                    <div style="font-size: 18px; font-weight: 800; color: #16a34a;" id="lbl_prod_suggested_price">R$ 0,00</div>
                </div>
                <div>
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;"><?php echo esc_html(be_term('col_sale_price')); ?> Final</span>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 4px; margin-top: 2px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--be-accent);">R$</span>
                        <input type="number" step="0.50" min="0" name="product[final_price]" id="prod_final_price" class="be-modal-input" style="width: 90px; text-align: right; font-weight: 800; font-size: 15px !important;" required>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="be-pill-btn" onclick="closeProductModal()">Cancelar</button>
                <button type="submit" class="be-btn-primary">Salvar <?php echo esc_html(be_term('products_singular')); ?></button>
            </div>
        </form>
    </div>
</div>

<style>
.role-guide-card:hover {
    border-color: var(--be-accent) !important;
    background: #f1f5f9 !important;
    transform: translateY(-1px);
}
.role-guide-card.active-role-card {
    border-color: var(--be-accent) !important;
    background: var(--be-accent-subtle) !important;
    box-shadow: 0 0 0 1px var(--be-accent);
}
</style>

<script>
const targetNetMargin = <?php echo $targetNetMargin; ?>;
const cardFeePercent  = <?php echo $cardFeePercent; ?>;
const taxRatePercent  = <?php echo $taxRatePercent; ?>;

const availableRecipes = <?php echo json_encode(array_map(fn($r) => [
    'id'        => (int)$r->id,
    'name'      => $r->name,
    'unit_cost' => (float)$r->unitCost,
    'yield_unit'=> $r->yieldUnit
], $recipes)); ?>;

const availablePackaging = <?php echo json_encode(array_map(fn($s) => [
    'id'        => (int)$s->id,
    'name'      => $s->name,
    'unit_cost' => (float)$s->unitCostCalculated,
    'use_unit'  => $s->useUnit
], $packagingSupplies)); ?>;

let productItemIndex = 0;

function highlightActiveRole(roleKey) {
    document.querySelectorAll('.role-guide-card').forEach(c => c.classList.remove('active-role-card'));
    const active = document.getElementById('guide-card-' + roleKey);
    if (active) active.classList.add('active-role-card');
}

function selectRoleByCard(roleKey) {
    const sel = document.getElementById('prod_role');
    if (sel) {
        sel.value = roleKey;
        highlightActiveRole(roleKey);
    }
}

document.getElementById('prod_role').addEventListener('change', function() {
    highlightActiveRole(this.value);
});

document.getElementById('prod_name').addEventListener('input', function() {
    const isEdit = document.getElementById('prod_id').value !== '';
    const titlePrefix = isEdit ? 'Editar <?php echo esc_js(be_term('products_singular')); ?>' : 'Novo <?php echo esc_js(be_term('products_singular')); ?>';
    const val = this.value.trim();
    document.getElementById('modal-product-title').innerText = val ? `${titlePrefix}: ${val}` : titlePrefix;
});

function quickUpdateProduct(id) {
    const row = document.getElementById('prod-row-' + id);
    if (!row) return;

    const nameVal = row.querySelector('.prod-inline-name').value.trim();
    const roleVal = row.querySelector('.prod-inline-role').value;
    const priceVal = parseFloat(row.querySelector('.prod-inline-price').value) || 0.0;

    if (!nameVal) return;

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_quick_update_product',
        id: id,
        name: nameVal,
        strategic_role: roleVal,
        final_price: priceVal,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) {
            row.classList.add('be-row-updated');
            setTimeout(() => row.classList.remove('be-row-updated'), 1000);
        } else {
            alert(res.data?.message || 'Erro ao atualizar.');
        }
    });
}

function cleanupEmptyProductRows() {
    document.querySelectorAll('.prod-item-row.prod-row-new').forEach(row => {
        const sel = row.querySelector('.prod-sel-target');
        if (!sel || !sel.value) row.remove();
    });
    recalcProductLive();
}

function openProductModal(id = 0) {
    productItemIndex = 0;
    document.getElementById('product-items-tbody').innerHTML = '';
    document.getElementById('prod_id').value = '';
    document.getElementById('be-product-builder-form').reset();

    if (id > 0) {
        document.getElementById('modal-product-title').innerText = 'Carregando <?php echo esc_js(be_term('products_singular')); ?>...';
        jQuery.get(beSettings.ajaxUrl, { action: 'be_get_product_details', id: id, nonce: beSettings.nonce }, function(res) {
            if (res.success) {
                const p = res.data.product;
                document.getElementById('prod_id').value = p.id;
                document.getElementById('prod_name').value = p.name;
                document.getElementById('prod_role').value = p.strategic_role;
                highlightActiveRole(p.strategic_role);
                document.getElementById('prod_final_price').value = parseFloat(p.final_price).toFixed(2);

                document.getElementById('modal-product-title').innerText = 'Editar <?php echo esc_js(be_term('products_singular')); ?>: ' + p.name;

                const items = res.data.items || [];
                if (items.length > 0) {
                    items.forEach(item => {
                        addProductItemRow(item.item_type, parseInt(item.item_id), parseFloat(item.quantity), parseFloat(item.unit_cost_snapshot), false);
                    });
                } else {
                    addProductItemRow('recipe', 0, 1, 0, true);
                }
                recalcProductLive();
            }
        });
    } else {
        document.getElementById('modal-product-title').innerText = 'Novo <?php echo esc_js(be_term('products_singular')); ?>';
        highlightActiveRole('carro_chefe');
        addProductItemRow('recipe', 0, 1, 0, true);
        recalcProductLive();
    }

    document.getElementById('be-product-modal').style.display = 'flex';
}

function closeProductModal() {
    cleanupEmptyProductRows();
    document.getElementById('be-product-modal').style.display = 'none';
}

function addProductItemRow(itemType = 'recipe', selectedItemId = 0, qty = 1, unitCostSnap = 0, isNew = false) {
    cleanupEmptyProductRows();

    const tbody = document.getElementById('product-items-tbody');
    const idx = productItemIndex++;
    const itemsPool = (itemType === 'recipe') ? availableRecipes : availablePackaging;

    let options = `<option value="">Selecione ${itemType === 'recipe' ? 'uma receita' : 'uma embalagem'}...</option>`;
    itemsPool.forEach(i => {
        const isSel = (parseInt(i.id) === parseInt(selectedItemId)) ? 'selected' : '';
        options += `<option value="${i.id}" data-cost="${i.unit_cost}" ${isSel}>${i.name} (R$ ${i.unit_cost.toFixed(4)})</option>`;
    });

    const tr = document.createElement('tr');
    tr.className = 'prod-item-row' + (isNew ? ' prod-row-new' : '');
    tr.style.borderBottom = '1px solid var(--be-border-subtle)';
    tr.innerHTML = `
        <td style="padding: 6px 12px; vertical-align: middle;">
            <select name="items[${idx}][item_type]" class="be-modal-input prod-sel-type" style="width: 100%;">
                <option value="recipe" ${itemType === 'recipe' ? 'selected' : ''}>Receita / Ficha</option>
                <option value="supply" ${itemType === 'supply' ? 'selected' : ''}>Embalagem</option>
            </select>
            <input type="hidden" name="items[${idx}][unit_cost_snapshot]" class="prod-inp-snap" value="${unitCostSnap}">
            <input type="hidden" name="items[${idx}][subtotal_cost]" class="prod-inp-subtotal" value="0">
        </td>
        <td style="padding: 6px 12px; vertical-align: middle;">
            <select name="items[${idx}][item_id]" class="be-modal-input prod-sel-target" style="width: 100%;" required>
                ${options}
            </select>
        </td>
        <td style="padding: 6px 12px; vertical-align: middle; text-align: center;">
            <input type="number" step="any" min="0.001" name="items[${idx}][quantity]" value="${qty || 1}" class="be-modal-input prod-inp-qty" style="width: 75px; text-align: right; font-weight: 600;" required>
        </td>
        <td style="padding: 6px 12px; vertical-align: middle; text-align: right;">
            <strong class="prod-lbl-subtotal" style="color: var(--be-primary); font-size: 13px;">R$ 0,00</strong>
        </td>
        <td style="padding: 6px 8px; text-align: center; vertical-align: middle;">
            <button type="button" class="be-icon-btn be-icon-btn-del" onclick="this.closest('tr').remove(); recalcProductLive();">
                <svg viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);

    tr.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', recalcProductLive);
        el.addEventListener('change', recalcProductLive);
    });

    const typeSel = tr.querySelector('.prod-sel-type');
    typeSel.addEventListener('change', function() {
        const pool = (this.value === 'recipe') ? availableRecipes : availablePackaging;
        const targetSel = tr.querySelector('.prod-sel-target');
        let newOpts = `<option value="">Selecione ${this.value === 'recipe' ? 'uma receita' : 'uma embalagem'}...</option>`;
        pool.forEach(i => {
            newOpts += `<option value="${i.id}" data-cost="${i.unit_cost}">${i.name} (R$ ${i.unit_cost.toFixed(4)})</option>`;
        });
        targetSel.innerHTML = newOpts;
        recalcProductLive();
    });

    const targetSel = tr.querySelector('.prod-sel-target');
    targetSel.addEventListener('change', function() {
        if (this.value) tr.classList.remove('prod-row-new');
        recalcProductLive();
    });

    if (isNew) {
        setTimeout(() => targetSel.focus(), 50);
    }
}

document.getElementById('be-product-modal').addEventListener('mousedown', function(e) {
    const targetRow = e.target.closest('.prod-item-row');
    document.querySelectorAll('.prod-item-row.prod-row-new').forEach(row => {
        if (row !== targetRow) {
            const sel = row.querySelector('.prod-sel-target');
            if (!sel || !sel.value) {
                row.remove();
                recalcProductLive();
            }
        }
    });
});

function recalcProductLive() {
    let directCost = 0;

    document.querySelectorAll('.prod-item-row').forEach(row => {
        const sel = row.querySelector('.prod-sel-target');
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;

        const unitCost = parseFloat(opt.getAttribute('data-cost')) || 0;
        const qty = parseFloat(row.querySelector('.prod-inp-qty').value) || 0;
        const subtotal = unitCost * qty;

        row.querySelector('.prod-inp-snap').value = unitCost;
        row.querySelector('.prod-inp-subtotal').value = subtotal.toFixed(4);
        row.querySelector('.prod-lbl-subtotal').innerText = 'R$ ' + subtotal.toFixed(2).replace('.', ',');

        directCost += subtotal;
    });

    const totalDeductionsPct = targetNetMargin + cardFeePercent + taxRatePercent;
    const markupDivisor = Math.max(0.1, (100.0 - totalDeductionsPct) / 100.0);
    const suggestedPrice = directCost > 0 ? (directCost / markupDivisor) : 0;

    document.getElementById('lbl_prod_direct_cost').innerText = 'R$ ' + directCost.toFixed(2).replace('.', ',');
    document.getElementById('lbl_prod_suggested_price').innerText = 'R$ ' + suggestedPrice.toFixed(2).replace('.', ',');

    const priceField = document.getElementById('prod_final_price');
    if (!priceField.value || parseFloat(priceField.value) === 0) {
        priceField.value = suggestedPrice.toFixed(2);
    }
}

document.querySelectorAll('#be-product-builder-form input').forEach(el => {
    el.addEventListener('input', recalcProductLive);
});

document.getElementById('be-product-builder-form').addEventListener('submit', function(e) {
    e.preventDefault();
    cleanupEmptyProductRows();
    const data = jQuery(this).serialize();
    jQuery.post(beSettings.ajaxUrl, data + '&action=be_save_product&nonce=' + beSettings.nonce, function(res) {
        if (res.success) {
            alert('Salvo com sucesso!');
            window.location.reload();
        } else {
            alert('Erro ao salvar: ' + (res.data?.message || 'Tente novamente.'));
        }
    });
});

function deleteProduct(id) {
    if (!confirm('Deseja excluir este produto comercial?')) return;
    jQuery.post(beSettings.ajaxUrl, { action: 'be_delete_product', id: id, nonce: beSettings.nonce }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir.');
    });
}
</script>