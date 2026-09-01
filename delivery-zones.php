<?php
if (!defined('ABSPATH')) exit;
/** @var array $zones */
?>
<div class="wrap be-wrap">
    <div class="be-card" style="max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <span class="be-badge be-badge-success">Logística & Frete</span>
                <h1 style="font-size: 22px; margin: 6px 0 0; font-weight: 800;">Zonas de Entrega & Valores Fixos</h1>
                <p style="color: var(--be-text-muted); font-size: 13px; margin: 4px 0 0;">Cadastre os bairros atendidos e os respectivos valores fixos de entrega.</p>
            </div>
            <button type="button" class="be-btn-primary" onclick="openZoneModal()">+ Nova Zona</button>
        </div>

        <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid var(--be-border);">
            <thead>
                <tr>
                    <th style="width: 55%; text-align: left;">Bairro / Região</th>
                    <th style="width: 25%; text-align: center;">Taxa de Entrega (R$)</th>
                    <th style="width: 20%; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($zones)): ?>
                    <tr><td colspan="3" style="text-align: center; padding: 24px; color: var(--be-text-muted);">Nenhuma zona cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($zones as $z): 
                        $jsonData = esc_attr(json_encode($z));
                    ?>
                        <tr>
                            <td style="text-align: left;"><strong><?php echo esc_html($z->name); ?></strong></td>
                            <td style="text-align: center;">
                                <strong style="color: var(--be-primary);">
                                    <?php echo (float)$z->fee > 0 ? 'R$ ' . number_format((float)$z->fee, 2, ',', '.') : 'Grátis / Retirada'; ?>
                                </strong>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="be-action-btn be-btn-edit" onclick='openZoneModal(<?php echo $jsonData; ?>)'>Editar</button>
                                <button type="button" class="be-action-btn be-btn-del" onclick="deleteZone(<?php echo (int)$z->id; ?>)">✕</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Edição de Zona -->
<div id="be-zone-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:99999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
    <div style="background:#fff; border-radius:12px; width:440px; max-width:92%; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.15);">
        <h3 style="font-size:18px; font-weight:800; margin:0 0 16px;" id="zone-modal-title">Nova Zona de Entrega</h3>
        <form id="form-zone" onsubmit="return false;">
            <input type="hidden" id="zone_id" value="0">
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Nome do Bairro ou Região *</label>
                <input type="text" id="zone_name" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" required placeholder="Ex: Praia da Costa">
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Taxa de Entrega (R$) *</label>
                <input type="number" step="0.5" min="0" id="zone_fee" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; font-weight:700;" value="0.00" required>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="be-pill-btn" onclick="closeZoneModal()">Cancelar</button>
                <button type="button" class="be-btn-primary" onclick="saveZone()">Salvar Zona</button>
            </div>
        </form>
    </div>
</div>

<script>
function openZoneModal(z = null) {
    if (z) {
        document.getElementById('zone-modal-title').innerText = 'Editar Zona de Entrega';
        document.getElementById('zone_id').value = z.id;
        document.getElementById('zone_name').value = z.name || '';
        document.getElementById('zone_fee').value = parseFloat(z.fee) || 0;
    } else {
        document.getElementById('zone-modal-title').innerText = 'Nova Zona de Entrega';
        document.getElementById('zone_id').value = '0';
        document.getElementById('zone_name').value = '';
        document.getElementById('zone_fee').value = '0.00';
    }
    document.getElementById('be-zone-modal').style.display = 'flex';
}

function closeZoneModal() {
    document.getElementById('be-zone-modal').style.display = 'none';
}

function saveZone() {
    const name = document.getElementById('zone_name').value.trim();
    if (!name) return alert('Informe o nome da zona/bairro.');

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_save_delivery_zone',
        id: document.getElementById('zone_id').value,
        name: name,
        fee: document.getElementById('zone_fee').value,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao salvar.');
    });
}

function deleteZone(id) {
    if (!confirm('Deseja excluir esta zona de entrega?')) return;
    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_delete_delivery_zone',
        id: id,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir.');
    });
}
</script>