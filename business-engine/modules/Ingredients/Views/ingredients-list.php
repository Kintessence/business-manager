<?php
if (!defined('ABSPATH')) exit;
/**
 * @var \BusinessEngine\Ingredients\DTOs\IngredientDTO[] $ingredients
 * @var string[] $categories
 * @var string $search
 * @var string $category
 * @var int $recipesCount
 */

$pkgTypes = ['Lata', 'Caixa', 'Pacote', 'Garrafa', 'Pote', 'Barra', 'Saco', 'Unidade'];
?>

<div class="be-wrap">
    
    <!-- Cabeçalho Oficial -->
    <div class="be-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; width: 100%;">
        <div class="be-header-title">
            <h1><?php echo esc_html(be_term('supplies_plural')); ?></h1>
            <p>Cadastre os insumos e embalagens de compra para alimentar o custo real de uso nas fichas técnicas.</p>
        </div>
        <div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=be-recipes')); ?>" class="be-pill-btn" style="height: 38px; padding: 0 16px; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; text-decoration: none; font-weight: 700;">
                <span><?php echo esc_html(be_term('recipes_plural')); ?></span>
                <span class="be-badge be-badge-info" style="font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: 800; background: #dbeafe; color: #1e40af;">
                    <?php echo esc_html((string)$recipesCount); ?>
                </span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros e Busca Universal -->
    <div class="be-card" style="padding: 12px 16px; margin-bottom: 16px;">
        <form method="get" class="be-toolbar-standard">
            <input type="hidden" name="page" value="be-ingredients">
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(be_term('search_supplies_ph')); ?>" class="be-search-input">
            
            <select name="category" class="be-filter-select">
                <option value=""><?php echo esc_html(be_term('all_categories_filter')); ?></option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo esc_attr($cat); ?>" <?php selected($category, $cat); ?>><?php echo esc_html($cat); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="be-btn-primary">Filtrar</button>
            <?php if (!empty($search) || !empty($category)): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=be-ingredients')); ?>" class="be-pill-btn">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Barra Superior da Tabela -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; width: 100%;">
        <div>
            <span style="font-size: 13px; font-weight: 600; color: var(--be-text-muted);" id="lbl-total-supplies">
                <?php echo count($ingredients); ?> <?php echo esc_html(strtolower(be_term('supplies_singular'))); ?>(s) cadastrado(s)
            </span>
        </div>
        <div>
            <button type="button" class="be-btn-primary" id="be-btn-add-supply" style="height: 34px; font-size: 13px;">
                + Novo <?php echo esc_html(be_term('supplies_singular')); ?>
            </button>
        </div>
    </div>

    <!-- Tabela Grade de Insumos com Porcentagens Fixas -->
    <form id="be-supplies-form">
        <div class="be-card" style="padding: 0;">
            <table class="be-interactive-table" style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed;">
                <thead>
                    <tr style="background: var(--be-bg); border-bottom: 1px solid var(--be-border-subtle); color: var(--be-text-muted);">
                        <th class="col-align-left" style="padding: 10px 14px; width: 28%;"><?php echo esc_html(be_term('col_name')); ?></th>
                        <th style="padding: 10px 8px; width: 16%;"><?php echo esc_html(be_term('col_category')); ?></th>
                        <th style="padding: 10px 8px; width: 12%;">Apresentação</th>
                        <th style="padding: 10px 8px; width: 13%;">Conteúdo Compra</th>
                        <th style="padding: 10px 8px; width: 11%;">Custo Pago</th>
                        <th style="padding: 10px 8px; width: 12%;">Custo / Unid. Uso</th>
                        <th style="padding: 10px 10px; width: 8%;"><?php echo esc_html(be_term('col_actions')); ?></th>
                    </tr>
                </thead>
                <tbody id="be-supplies-tbody">
                    <?php if (empty($ingredients)): ?>
                        <tr id="row-empty">
                            <td colspan="7" style="text-align: center; padding: 36px; color: var(--be-text-muted);">
                                Nenhum registro encontrado. Clique em "+ Novo <?php echo esc_html(be_term('supplies_singular')); ?>" acima para começar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ingredients as $idx => $item): ?>
                            <tr class="be-supply-row" data-index="<?php echo $idx; ?>" data-category="<?php echo esc_attr($item->category); ?>" style="border-bottom: 1px solid var(--be-border-subtle);">
                                <td class="col-align-left" style="padding: 6px 14px; vertical-align: middle;">
                                    <div class="be-input-zone">
                                        <input type="text" name="items[<?php echo $idx; ?>][name]" value="<?php echo esc_attr($item->name); ?>" class="be-modal-input inp-name" required style="width: 100%; font-weight: 600;" placeholder="Ex: Leite Condensado">
                                        <input type="hidden" name="items[<?php echo $idx; ?>][id]" value="<?php echo esc_attr((string)$item->id); ?>">
                                        <input type="hidden" name="items[<?php echo $idx; ?>][loss_pct]" value="<?php echo esc_attr((string)$item->lossPct); ?>" class="inp-loss">
                                        <input type="hidden" name="items[<?php echo $idx; ?>][allergens]" value="<?php echo esc_attr($item->allergens); ?>" class="inp-allergens">
                                        
                                        <div class="be-hover-trigger-zone">
                                            <button type="button" class="btn-insert-contextual" onclick="insertRowAfter(this.closest('tr'))">+ Inserir item</button>
                                        </div>
                                    </div>
                                    <?php if ($item->lossPct > 0): ?>
                                        <small style="display:block; color: var(--be-danger); font-size: 11px; margin-top: 2px;">Perda: <?php echo number_format($item->lossPct, 1, ',', '.'); ?>%</small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 6px 8px; vertical-align: middle;">
                                    <select name="items[<?php echo $idx; ?>][category]" class="be-modal-input sel-cat" style="width: 100%;">
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo esc_attr($cat); ?>" <?php selected($item->category, $cat); ?>><?php echo esc_html($cat); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="padding: 6px 8px; vertical-align: middle;">
                                    <select name="items[<?php echo $idx; ?>][pkg_type]" class="be-modal-input sel-pkg-type" style="width: 100%;">
                                        <?php foreach ($pkgTypes as $pkg): ?>
                                            <option value="<?php echo esc_attr($pkg); ?>" <?php selected($item->pkgType, $pkg); ?>><?php echo esc_html($pkg); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="padding: 6px 8px; vertical-align: middle;">
                                    <div style="display: flex; gap: 4px; align-items: center; justify-content: center;">
                                        <input type="number" step="any" min="0.001" name="items[<?php echo $idx; ?>][pkg_size]" value="<?php echo esc_attr((string)$item->pkgSize); ?>" class="be-modal-input inp-size" style="width: 65px; text-align: center; font-weight: 600; padding: 0 4px !important;" required>
                                        <select name="items[<?php echo $idx; ?>][unit_type]" class="be-modal-input sel-pkg-unit" style="width: 55px; padding: 0 4px !important;">
                                            <option value="g" <?php selected($item->unitType, 'g'); ?>>g</option>
                                            <option value="kg" <?php selected($item->unitType, 'kg'); ?>>kg</option>
                                            <option value="ml" <?php selected($item->unitType, 'ml'); ?>>ml</option>
                                            <option value="L" <?php selected($item->unitType, 'L'); ?>>L</option>
                                            <option value="un" <?php selected($item->unitType, 'un'); ?>>un</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="items[<?php echo $idx; ?>][use_unit]" value="<?php echo esc_attr($item->useUnit); ?>" class="inp-use-unit">
                                </td>
                                <td style="padding: 6px 8px; vertical-align: middle; text-align: center;">
                                    <div style="display: inline-flex; gap: 4px; align-items: center;">
                                        <span style="font-size: 11px; color: var(--be-text-muted); font-weight: 600;">R$</span>
                                        <input type="number" step="0.10" min="0" name="items[<?php echo $idx; ?>][pkg_cost]" value="<?php echo esc_attr(number_format($item->pkgCost, 2, '.', '')); ?>" class="be-modal-input inp-cost" style="width: 75px; font-weight: 700; text-align: right; padding: 0 6px !important;" required>
                                    </div>
                                </td>
                                <td style="padding: 6px 8px; vertical-align: middle; text-align: center;">
                                    <strong class="lbl-unit-cost" style="color: var(--be-accent); font-size: 12.5px;">
                                        R$ <?php echo number_format($item->unitCostCalculated, 4, ',', '.'); ?> / <?php echo esc_html($item->useUnit); ?>
                                    </strong>
                                </td>
                                <td style="padding: 6px 10px; text-align: center; vertical-align: middle;">
                                    <div class="be-actions-cell">
                                        <button type="button" class="be-icon-btn btn-open-modal" title="Ficha Técnica & Perdas">
                                            <svg viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.8 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/></svg>
                                        </button>
                                        <button type="button" class="be-icon-btn be-icon-btn-del btn-del-supply" data-id="<?php echo esc_attr((string)$item->id); ?>" title="Excluir">
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

        <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
            <button type="submit" class="be-btn-primary" style="height: 42px; padding: 0 28px; font-size: 14px;">
                Salvar <?php echo esc_html(be_term('supplies_plural')); ?>
            </button>
        </div>
    </form>
</div>

<!-- Modal de Ficha Avançada & Perdas Sanitárias -->
<div id="be-supply-advanced-modal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="be-card" style="width: 520px; max-width: 92%; margin-bottom: 0; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--be-border-subtle); padding-bottom: 10px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--be-primary);" id="adv-modal-title">Ficha Avançada do Item</h3>
            <button type="button" onclick="closeAdvModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--be-text-muted);">&times;</button>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px;">Perda Técnica / Descarte (%)</label>
            <input type="number" step="0.5" min="0" max="99" id="modal_inp_loss" class="be-modal-input" style="width: 100%;">
            <div style="margin-top: 8px; background: var(--be-bg); border: 1px solid var(--be-border-subtle); border-radius: 6px; padding: 8px 10px; font-size: 11px; color: var(--be-text-muted);">
                <strong>Tabela Referencial:</strong> Abacaxi (40%), Morango (5%), Banana (30%), Aparas de massa/chocolate (3%).
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px;">Alérgenos & Restrições (ANVISA)</label>
            <input type="text" id="modal_inp_allergens" class="be-modal-input" style="width: 100%;" placeholder="Ex: Contém Lactose, Contém Glúten, Contém Ovos, Nozes">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="be-pill-btn" onclick="closeAdvModal()">Cancelar</button>
            <button type="button" class="be-btn-primary" onclick="saveAdvModal()" style="height: 36px;">Aplicar à Linha</button>
        </div>
    </div>
</div>

<!-- Template de Linha Nova -->
<template id="tpl-supply-row">
    <tr class="be-supply-row be-row-new" data-index="__INDEX__" data-category="Ingrediente" style="border-bottom: 1px solid var(--be-border-subtle);">
        <td class="col-align-left" style="padding: 6px 14px; vertical-align: middle;">
            <div class="be-input-zone">
                <input type="text" name="items[__INDEX__][name]" class="be-modal-input inp-name" required style="width: 100%; font-weight: 600;" placeholder="Digite para buscar sugestão...">
                <input type="hidden" name="items[__INDEX__][id]" value="">
                <input type="hidden" name="items[__INDEX__][loss_pct]" value="0" class="inp-loss">
                <input type="hidden" name="items[__INDEX__][allergens]" value="" class="inp-allergens">
                
                <div class="be-hover-trigger-zone">
                    <button type="button" class="btn-insert-contextual" onclick="insertRowAfter(this.closest('tr'))">+ Inserir item</button>
                </div>
            </div>
        </td>
        <td style="padding: 6px 8px; vertical-align: middle;">
            <select name="items[__INDEX__][category]" class="be-modal-input sel-cat" style="width: 100%;">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td style="padding: 6px 8px; vertical-align: middle;">
            <select name="items[__INDEX__][pkg_type]" class="be-modal-input sel-pkg-type" style="width: 100%;">
                <?php foreach ($pkgTypes as $pkg): ?>
                    <option value="<?php echo esc_attr($pkg); ?>"><?php echo esc_html($pkg); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td style="padding: 6px 8px; vertical-align: middle;">
            <div style="display: flex; gap: 4px; align-items: center; justify-content: center;">
                <input type="number" step="any" min="0.001" name="items[__INDEX__][pkg_size]" value="1000" class="be-modal-input inp-size" style="width: 65px; text-align: center; font-weight: 600; padding: 0 4px !important;" required>
                <select name="items[__INDEX__][unit_type]" class="be-modal-input sel-pkg-unit" style="width: 55px; padding: 0 4px !important;">
                    <option value="g" selected>g</option>
                    <option value="kg">kg</option>
                    <option value="ml">ml</option>
                    <option value="L">L</option>
                    <option value="un">un</option>
                </select>
            </div>
            <input type="hidden" name="items[__INDEX__][use_unit]" value="g" class="inp-use-unit">
        </td>
        <td style="padding: 6px 8px; vertical-align: middle; text-align: center;">
            <div style="display: inline-flex; gap: 4px; align-items: center;">
                <span style="font-size: 11px; color: var(--be-text-muted); font-weight: 600;">R$</span>
                <input type="number" step="0.10" min="0" name="items[__INDEX__][pkg_cost]" value="10.00" class="be-modal-input inp-cost" style="width: 75px; font-weight: 700; text-align: right; padding: 0 6px !important;" required>
            </div>
        </td>
        <td style="padding: 6px 8px; vertical-align: middle; text-align: center;">
            <strong class="lbl-unit-cost" style="color: var(--be-accent); font-size: 12.5px;">R$ 0,0100 / g</strong>
        </td>
        <td style="padding: 6px 10px; text-align: center; vertical-align: middle;">
            <div class="be-actions-cell">
                <button type="button" class="be-icon-btn btn-open-modal" title="Ficha Técnica & Perdas">
                    <svg viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.8 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/></svg>
                </button>
                <button type="button" class="be-icon-btn be-icon-btn-del btn-del-supply" title="Excluir">
                    <svg viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
                </button>
            </div>
        </td>
    </tr>
</template>

<style>
.be-input-zone { position: relative; }
.be-hover-trigger-zone { position: absolute; bottom: -10px; left: 0; width: 140px; height: 16px; z-index: 10; pointer-events: auto; }
.be-hover-trigger-zone .btn-insert-contextual { position: absolute; bottom: 0; left: 4px; background: var(--be-accent); color: #ffffff; border: none; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; opacity: 0; pointer-events: none; transition: all 0.15s ease; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.15); white-space: nowrap; }
.be-hover-trigger-zone:hover .btn-insert-contextual { opacity: 1; pointer-events: auto; }
.be-hover-trigger-zone .btn-insert-contextual:hover { background: var(--be-accent-hover); }
</style>

<script>
let rowIndex = document.querySelectorAll('.be-supply-row').length;
let currentModalRow = null;

function updateCounter() {
    const total = document.querySelectorAll('.be-supply-row').length;
    document.getElementById('lbl-total-supplies').innerText = total + ' <?php echo esc_js(strtolower(be_term('supplies_singular'))); ?>(s) cadastrado(s)';
}

function calcRowUnitCost(row) {
    const size    = parseFloat(row.querySelector('.inp-size')?.value) || 1;
    const cost    = parseFloat(row.querySelector('.inp-cost')?.value) || 0;
    const loss    = Math.min(99, Math.max(0, parseFloat(row.querySelector('.inp-loss')?.value) || 0));
    const pkgUnit = row.querySelector('.sel-pkg-unit')?.value || 'g';
    const useUnit = (pkgUnit === 'kg' || pkgUnit === 'g') ? 'g' : ((pkgUnit === 'L' || pkgUnit === 'ml') ? 'ml' : 'un');

    row.querySelector('.inp-use-unit').value = useUnit;

    let factor = 1.0;
    if (pkgUnit === 'kg' && useUnit === 'g') factor = 0.001;
    else if (pkgUnit === 'L' && useUnit === 'ml') factor = 0.001;

    const baseCost = (cost / size) * factor;
    const corrFactor = loss > 0 ? (1.0 / (1.0 - (loss / 100.0))) : 1.0;
    const finalUnitCost = baseCost * corrFactor;

    const lbl = row.querySelector('.lbl-unit-cost');
    if (lbl) {
        lbl.innerText = 'R$ ' + finalUnitCost.toFixed(4).replace('.', ',') + ' / ' + useUnit;
    }
}

function bindRowEvents(row) {
    row.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', () => calcRowUnitCost(row));
        el.addEventListener('change', () => calcRowUnitCost(row));
    });

    const nameInput = row.querySelector('.inp-name');
    if (nameInput) {
        nameInput.addEventListener('blur', function() {
            if (row.classList.contains('be-row-new') && this.value.trim() === '') {
                row.remove();
                updateCounter();
            }
        });
    }

    const modalBtn = row.querySelector('.btn-open-modal');
    if (modalBtn) {
        modalBtn.addEventListener('click', function() {
            currentModalRow = row;
            const name = row.querySelector('.inp-name').value || 'Item';
            document.getElementById('adv-modal-title').innerText = 'Ficha: ' + name;
            document.getElementById('modal_inp_loss').value = row.querySelector('.inp-loss').value || 0;
            document.getElementById('modal_inp_allergens').value = row.querySelector('.inp-allergens').value || '';
            document.getElementById('be-supply-advanced-modal').style.display = 'flex';
        });
    }

    const delBtn = row.querySelector('.btn-del-supply');
    if (delBtn) {
        delBtn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if (id) {
                if (!confirm('Deseja excluir este item?')) return;
                jQuery.post(beSettings.ajaxUrl, { action: 'be_delete_ingredient', id: id, nonce: beSettings.nonce }, function(res) {
                    if (res.success) {
                        row.remove();
                        updateCounter();
                    } else {
                        alert(res.data?.message || 'Erro ao excluir.');
                    }
                });
            } else {
                row.remove();
                updateCounter();
            }
        });
    }
}

function createRowElement() {
    const tpl = document.getElementById('tpl-supply-row');
    const html = tpl.innerHTML.replace(/__INDEX__/g, rowIndex++);
    const tempDiv = document.createElement('tbody');
    tempDiv.innerHTML = html;
    const newRow = tempDiv.firstElementChild;
    bindRowEvents(newRow);
    calcRowUnitCost(newRow);
    return newRow;
}

function insertRowAfter(targetRow) {
    const newRow = createRowElement();
    targetRow.after(newRow);
    updateCounter();
    newRow.querySelector('.inp-name').focus();
}

document.getElementById('be-btn-add-supply').addEventListener('click', function() {
    const emptyRow = document.getElementById('row-empty');
    if (emptyRow) emptyRow.remove();

    const tbody = document.getElementById('be-supplies-tbody');
    const newRow = createRowElement();
    tbody.prepend(newRow);

    updateCounter();
    newRow.querySelector('.inp-name').focus();
});

function closeAdvModal() {
    document.getElementById('be-supply-advanced-modal').style.display = 'none';
    currentModalRow = null;
}

function saveAdvModal() {
    if (currentModalRow) {
        currentModalRow.querySelector('.inp-loss').value = document.getElementById('modal_inp_loss').value || 0;
        currentModalRow.querySelector('.inp-allergens').value = document.getElementById('modal_inp_allergens').value || '';
        calcRowUnitCost(currentModalRow);
    }
    closeAdvModal();
}

document.querySelectorAll('.be-supply-row').forEach(row => {
    bindRowEvents(row);
    calcRowUnitCost(row);
});

document.getElementById('be-supplies-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = jQuery(this).serialize();
    jQuery.post(beSettings.ajaxUrl, data + '&action=be_save_bulk_ingredients&nonce=' + beSettings.nonce, function(res) {
        if (res.success) {
            alert('Salvo com sucesso!');
            window.location.reload();
        } else {
            alert('Erro ao salvar: ' + (res.data?.message || 'Tente novamente.'));
        }
    });
});
</script>