<?php
if (!defined('ABSPATH')) exit;
/** @var array $supplies */
/** @var string $search */
/** @var int $paged */
/** @var int $totalPages */
/** @var int $totalFiltered */
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <div>
                <span class="be-badge be-badge-success">Módulo Gastronomia</span>
                <h1 style="font-size:22px; margin:6px 0 0; font-weight:800;">🧂 Insumos & Matérias-Primas</h1>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="admin.php?page=be-csv-import" class="be-pill-btn">📥 Importar Maya</a>
                <button type="button" class="be-btn-primary" onclick="openSupplyModal()">➕ Novo Insumo</button>
            </div>
        </div>

        <form method="get" style="display:flex; gap:8px; margin-bottom:16px;">
            <input type="hidden" name="page" value="be-supplies">
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Buscar por insumo ou categoria..." style="flex:1; padding:8px 12px; border:1px solid var(--be-border); border-radius:6px;">
            <button type="submit" class="be-btn-primary" style="padding: 8px 16px;">Buscar</button>
            <?php if (!empty($search)): ?>
                <a href="admin.php?page=be-supplies" class="be-pill-btn" style="text-decoration:none; display:inline-flex; align-items:center;">Limpar</a>
            <?php endif; ?>
        </form>

        <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid var(--be-border); margin-bottom: 16px;">
            <thead>
                <tr>
                    <th style="width: 30%;">Nome do Insumo</th>
                    <th style="width: 15%;">Categoria</th>
                    <th style="width: 15%;">Embalagem Compra</th>
                    <th style="width: 15%;">Custo Compra (R$)</th>
                    <th style="width: 10%;">Custo / Uso</th>
                    <th style="width: 15%; text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($supplies)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:24px; color:var(--be-text-muted);">Nenhum insumo encontrado. Cadastre um novo ou importe do Maya.</td></tr>
                <?php else: ?>
                    <?php foreach ($supplies as $s): 
                        $pkgCost = (float)$s->pkg_cost;
                        $pkgSize = (float)$s->pkg_size;
                        $unit = (string)$s->unit_type;
                        $useUnit = (string)$s->use_unit;
                        
                        $normSize = $pkgSize;
                        if (($unit === 'kg' && $useUnit === 'g') || ($unit === 'L' && $useUnit === 'ml')) {
                            $normSize = $pkgSize * 1000.0;
                        }
                        $unitCost = $normSize > 0 ? ($pkgCost / $normSize) : 0.0;
                        $jsonData = esc_attr(json_encode($s));
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($s->name); ?></strong>
                                <?php if (!empty($s->allergens)): ?>
                                    <small style="display:block; color:#dc2626;">⚠️ <?php echo esc_html($s->allergens); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><span class="be-badge"><?php echo esc_html($s->category); ?></span></td>
                            <td><?php echo esc_html($s->pkg_size . ' ' . $s->unit_type); ?></td>
                            <td><strong>R$ <?php echo number_format($pkgCost, 2, ',', '.'); ?></strong></td>
                            <td><span class="be-badge be-badge-success">R$ <?php echo number_format($unitCost, 4, ',', '.'); ?> / <?php echo esc_html($useUnit); ?></span></td>
                            <td style="text-align: right; white-space: nowrap;">
                                <button type="button" class="be-action-btn be-btn-edit" onclick='openSupplyModal(<?php echo $jsonData; ?>)' title="Editar insumo">✏️ Editar</button>
                                <button type="button" class="be-action-btn be-btn-del" onclick="deleteSupply(<?php echo (int)$s->id; ?>)" title="Excluir insumo">🗑️</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginação -->
        <?php if ($totalPages > 1): ?>
            <div class="tablenav" style="display:flex; justify-content:space-between; align-items:center; padding: 8px 0;">
                <span style="font-size:13px; color:var(--be-text-muted);">
                    Mostrando <?php echo count($supplies); ?> de <?php echo $totalFiltered; ?> insumos (Página <?php echo $paged; ?> de <?php echo $totalPages; ?>)
                </span>
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links([
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo; Anterior',
                        'next_text' => 'Próxima &raquo;',
                        'total' => $totalPages,
                        'current' => $paged,
                    ]);
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Edição de Insumo -->
<div id="be-supply-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:99999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
    <div style="background:#fff; border-radius:12px; width:520px; max-width:92%; max-height:90vh; overflow-y:auto; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
            <h2 style="font-size:18px; font-weight:800; margin:0;" id="supply-modal-title">Editar Insumo</h2>
            <button type="button" onclick="closeSupplyModal()" style="background:none; border:none; font-size:22px; cursor:pointer; color:var(--be-text-muted);">&times;</button>
        </div>

        <form id="be-supply-form">
            <input type="hidden" id="modal_sup_id" value="0">
            
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Nome da Matéria-Prima / Insumo *</label>
                <input type="text" id="modal_sup_name" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" required placeholder="Ex: Chocolate Nobre Meio Amargo">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Categoria</label>
                    <input type="text" id="modal_sup_category" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" placeholder="Ex: Chocolates, Secos, Laticínios">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Custo Compra (R$) *</label>
                    <input type="number" step="0.01" id="modal_sup_cost" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; font-weight:700; color:var(--be-primary);" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Tamanho Embalagem</label>
                    <input type="number" step="0.001" id="modal_sup_size" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Unid. Compra</label>
                    <select id="modal_sup_unit" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;">
                        <option value="kg">kg (Quilos)</option>
                        <option value="g">g (Gramas)</option>
                        <option value="L">L (Litros)</option>
                        <option value="ml">ml (Mililitros)</option>
                        <option value="un">un (Unidade)</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Perda Técnica (%)</label>
                    <input type="number" step="0.5" id="modal_sup_loss" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" value="0">
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Alérgenos / Restrições (Para Etiquetas)</label>
                <input type="text" id="modal_sup_allergens" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" placeholder="Ex: Contém Leite e Soja, Contém Glúten">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="be-pill-btn" onclick="closeSupplyModal()">Cancelar</button>
                <button type="button" class="be-btn-primary" onclick="saveSupplyModal()">Salvar Insumo 💾</button>
            </div>
        </form>
    </div>
</div>

<style>
.be-action-btn {
    border: 1px solid var(--be-border);
    background: #fff;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}
.be-btn-edit { color: var(--be-accent); }
.be-btn-edit:hover { background: #eff6ff; border-color: var(--be-accent); }
.be-btn-del { color: var(--be-danger); }
.be-btn-del:hover { background: #fef2f2; border-color: var(--be-danger); }
</style>

<script>
function openSupplyModal(s = null) {
    if (s) {
        document.getElementById('supply-modal-title').innerText = 'Editar Insumo: ' + s.name;
        document.getElementById('modal_sup_id').value = s.id;
        document.getElementById('modal_sup_name').value = s.name || '';
        document.getElementById('modal_sup_category').value = s.category || 'Geral';
        document.getElementById('modal_sup_cost').value = parseFloat(s.pkg_cost) || 0;
        document.getElementById('modal_sup_size').value = parseFloat(s.pkg_size) || 1;
        document.getElementById('modal_sup_unit').value = s.unit_type || 'g';
        document.getElementById('modal_sup_loss').value = parseFloat(s.loss_pct) || 0;
        document.getElementById('modal_sup_allergens').value = s.allergens || '';
    } else {
        document.getElementById('supply-modal-title').innerText = 'Novo Insumo';
        document.getElementById('modal_sup_id').value = 0;
        document.getElementById('modal_sup_name').value = '';
        document.getElementById('modal_sup_category').value = 'Geral';
        document.getElementById('modal_sup_cost').value = '';
        document.getElementById('modal_sup_size').value = 1;
        document.getElementById('modal_sup_unit').value = 'g';
        document.getElementById('modal_sup_loss').value = 0;
        document.getElementById('modal_sup_allergens').value = '';
    }

    const modal = document.getElementById('be-supply-modal');
    modal.style.display = 'flex';
}

function closeSupplyModal() {
    document.getElementById('be-supply-modal').style.display = 'none';
}

function saveSupplyModal() {
    const name = document.getElementById('modal_sup_name').value.trim();
    if (!name) return alert('O nome do insumo é obrigatório.');

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_update_supply',
        id: document.getElementById('modal_sup_id').value,
        name: name,
        category: document.getElementById('modal_sup_category').value,
        pkg_cost: document.getElementById('modal_sup_cost').value,
        pkg_size: document.getElementById('modal_sup_size').value,
        unit_type: document.getElementById('modal_sup_unit').value,
        loss_pct: document.getElementById('modal_sup_loss').value,
        allergens: document.getElementById('modal_sup_allergens').value,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) {
            window.location.reload();
        } else {
            alert(res.data?.message || 'Erro ao salvar insumo.');
        }
    });
}

function deleteSupply(id) {
    if (!confirm('Deseja realmente remover este insumo?')) return;
    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_delete_supply',
        id: id,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir insumo.');
    });
}
</script>