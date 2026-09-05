<?php
if (!defined('ABSPATH')) exit;
/**
 * @var \BusinessEngine\Customers\DTOs\CustomerDTO[] $customers
 * @var string $search
 * @var string $channel
 * @var int $ordersCount
 */

$channelsMap = [
    'whatsapp'  => 'WhatsApp Direto',
    'instagram' => 'Instagram',
    'indicacao' => 'Indicação / Boca a Boca',
    'rua'       => 'Venda de Rua / Pronta Entrega',
    'outros'    => 'Outro Canal',
];
?>

<div class="be-wrap">
    
    <div class="be-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; width: 100%;">
        <div class="be-header-title">
            <h1><?php echo esc_html(be_term('customers_plural')); ?></h1>
            <p>Gerencie sua base de compradores, contatos e preferências para alimentar os pedidos e vendas.</p>
        </div>
        <div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=be-orders')); ?>" class="be-pill-btn" style="height: 38px; padding: 0 16px; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; text-decoration: none; font-weight: 700;">
                <span><?php echo esc_html(be_term('orders_plural')); ?></span>
                <span class="be-badge be-badge-info" style="font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: 800; background: #dbeafe; color: #1e40af;">
                    <?php echo esc_html((string)$ordersCount); ?>
                </span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros e Busca Universal (.be-toolbar-standard) -->
    <div class="be-card" style="padding: 12px 16px; margin-bottom: 16px;">
        <form method="get" class="be-toolbar-standard">
            <input type="hidden" name="page" value="be-customers">
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(be_term('search_customers_ph')); ?>" class="be-search-input">
            
            <select name="channel" class="be-filter-select">
                <option value="">Todos os Canais de Origem</option>
                <?php foreach ($channelsMap as $k => $lbl): ?>
                    <option value="<?php echo esc_attr($k); ?>" <?php selected($channel, $k); ?>><?php echo esc_html($lbl); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="be-btn-primary">Filtrar</button>
            <?php if (!empty($search) || !empty($channel)): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=be-customers')); ?>" class="be-pill-btn">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; width: 100%;">
        <div>
            <span style="font-size: 13px; font-weight: 600; color: var(--be-text-muted);" id="lbl-total-customers">
                <?php echo count($customers); ?> <?php echo esc_html(strtolower(be_term('customers_singular'))); ?>(s) cadastrado(s)
            </span>
        </div>
        <div>
            <button type="button" class="be-btn-primary" onclick="openCustomerModal()" style="height: 34px; font-size: 13px;">
                + Novo <?php echo esc_html(be_term('customers_singular')); ?>
            </button>
        </div>
    </div>

    <!-- Tabela Grade com Porcentagens Fixas e Cabeçalhos Centralizados -->
    <div class="be-card" style="padding: 0;">
        <table class="be-interactive-table" style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed;">
            <thead>
                <tr style="background: var(--be-bg); border-bottom: 1px solid var(--be-border-subtle); color: var(--be-text-muted);">
                    <th class="col-align-left" style="padding: 10px 14px; width: 28%;">Nome do Cliente</th>
                    <th style="padding: 10px 10px; width: 20%;">WhatsApp / Telefone</th>
                    <th style="padding: 10px 10px; width: 16%;">Canal de Origem</th>
                    <th style="padding: 10px 10px; width: 20%;">Bairro / Localidade</th>
                    <th style="padding: 10px 10px; width: 8%;">Contato</th>
                    <th style="padding: 10px 10px; width: 8%;"><?php echo esc_html(be_term('col_actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr id="row-empty-customers">
                        <td colspan="6" style="text-align: center; padding: 36px; color: var(--be-text-muted);">
                            Nenhum cliente cadastrado ainda. Clique em "+ Novo <?php echo esc_html(be_term('customers_singular')); ?>" para começar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                        <tr id="cust-row-<?php echo (int)$c->id; ?>" style="border-bottom: 1px solid var(--be-border-subtle);">
                            <td class="col-align-left" style="padding: 6px 14px; vertical-align: middle;">
                                <input type="text" class="be-modal-input cust-inline-name" value="<?php echo esc_attr($c->name); ?>" style="width: 100%; font-weight: 600;" onblur="quickUpdateCustomer(<?php echo (int)$c->id; ?>)" onkeydown="if(event.key==='Enter') this.blur();">
                            </td>
                            <td style="padding: 6px 10px; vertical-align: middle;">
                                <input type="text" class="be-modal-input cust-inline-phone" value="<?php echo esc_attr($c->phone); ?>" placeholder="DD9XXXXXXXX" style="width: 100%; text-align: center;" onblur="quickUpdateCustomer(<?php echo (int)$c->id; ?>)" onkeydown="if(event.key==='Enter') this.blur();">
                            </td>
                            <td style="padding: 6px 10px; vertical-align: middle;">
                                <select class="be-modal-input cust-inline-channel" style="width: 100%;" onchange="quickUpdateCustomer(<?php echo (int)$c->id; ?>)">
                                    <?php foreach ($channelsMap as $k => $label): ?>
                                        <option value="<?php echo esc_attr($k); ?>" <?php selected($c->channel, $k); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding: 6px 10px; vertical-align: middle;">
                                <input type="text" class="be-modal-input cust-inline-neighborhood" value="<?php echo esc_attr($c->neighborhood); ?>" placeholder="Bairro..." style="width: 100%; text-align: center;" onblur="quickUpdateCustomer(<?php echo (int)$c->id; ?>)" onkeydown="if(event.key==='Enter') this.blur();">
                            </td>
                            <td style="padding: 6px 10px; text-align: center; vertical-align: middle;">
                                <?php if (!empty($c->phone)): ?>
                                    <a href="https://wa.me/55<?php echo esc_attr($c->phone); ?>" target="_blank" class="be-pill-btn" style="padding: 4px 8px; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 4px;" title="Conversar no WhatsApp">
                                        <span>💬</span>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--be-text-muted); font-size: 11px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px 10px; text-align: center; vertical-align: middle;">
                                <div class="be-actions-cell">
                                    <button type="button" class="be-icon-btn" onclick="openCustomerModal(<?php echo (int)$c->id; ?>)" title="Editar Ficha Completa">
                                        <svg viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.8 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/></svg>
                                    </button>
                                    <button type="button" class="be-icon-btn be-icon-btn-del" onclick="deleteCustomer(<?php echo (int)$c->id; ?>)" title="Excluir">
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

<!-- Modal Construtor de Cliente -->
<div id="be-customer-modal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="be-card" style="width: 720px; max-width: 95%; max-height: 90vh; overflow-y: auto; overflow-x: hidden; margin-bottom: 0; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); box-sizing: border-box;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--be-border-subtle); padding-bottom: 12px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--be-primary);" id="modal-customer-title">Novo Cliente</h2>
            <button type="button" onclick="closeCustomerModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--be-text-muted);">&times;</button>
        </div>

        <form id="be-customer-builder-form">
            <input type="hidden" name="customer[id]" id="cust_id" value="">

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Nome do Cliente *</label>
                    <input type="text" name="customer[name]" id="cust_name" class="be-modal-input" required placeholder="Ex: Maria Silva" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">WhatsApp / Telefone</label>
                    <input type="text" name="customer[phone]" id="cust_phone" class="be-modal-input" placeholder="27999999999" style="width: 100%;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Canal de Origem</label>
                    <select name="customer[channel]" id="cust_channel" class="be-modal-input" style="width: 100%;">
                        <?php foreach ($channelsMap as $k => $label): ?>
                            <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Bairro / Região</label>
                    <input type="text" name="customer[neighborhood]" id="cust_neighborhood" class="be-modal-input" placeholder="Ex: Praia da Costa" style="width: 100%;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Endereço Completo de Entrega</label>
                <input type="text" name="customer[address]" id="cust_address" class="be-modal-input" placeholder="Rua, número, complemento, ponto de referência..." style="width: 100%;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px; color: var(--be-primary);">Anotações, Restrições ou Preferências</label>
                <textarea name="customer[notes]" id="cust_notes" class="be-modal-input" style="width: 100%; height: 75px; padding: 8px 10px; resize: vertical;" placeholder="Ex: Alérgica a nozes. Prefere doces com menos açúcar."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="be-pill-btn" onclick="closeCustomerModal()">Cancelar</button>
                <button type="submit" class="be-btn-primary">Salvar Cliente</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('cust_name').addEventListener('input', function() {
    const isEdit = document.getElementById('cust_id').value !== '';
    const prefix = isEdit ? 'Editar Cliente' : 'Novo Cliente';
    const val = this.value.trim();
    document.getElementById('modal-customer-title').innerText = val ? `${prefix}: ${val}` : prefix;
});

function quickUpdateCustomer(id) {
    const row = document.getElementById('cust-row-' + id);
    if (!row) return;

    const nameVal = row.querySelector('.cust-inline-name').value.trim();
    const phoneVal = row.querySelector('.cust-inline-phone').value.trim();
    const neighVal = row.querySelector('.cust-inline-neighborhood').value.trim();
    const chanVal  = row.querySelector('.cust-inline-channel').value;

    if (!nameVal) return;

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_quick_update_customer',
        id: id,
        name: nameVal,
        phone: phoneVal,
        neighborhood: neighVal,
        channel: chanVal,
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

function openCustomerModal(id = 0) {
    document.getElementById('cust_id').value = '';
    document.getElementById('be-customer-builder-form').reset();

    if (id > 0) {
        document.getElementById('modal-customer-title').innerText = 'Carregando Cliente...';
        jQuery.get(beSettings.ajaxUrl, { action: 'be_get_customer_details', id: id, nonce: beSettings.nonce }, function(res) {
            if (res.success) {
                const c = res.data.customer;
                document.getElementById('cust_id').value = c.id;
                document.getElementById('cust_name').value = c.name;
                document.getElementById('cust_phone').value = c.phone;
                document.getElementById('cust_channel').value = c.channel;
                document.getElementById('cust_neighborhood').value = c.neighborhood;
                document.getElementById('cust_address').value = c.address;
                document.getElementById('cust_notes').value = c.notes;

                document.getElementById('modal-customer-title').innerText = 'Editar Cliente: ' + c.name;
            }
        });
    } else {
        document.getElementById('modal-customer-title').innerText = 'Novo Cliente';
    }

    document.getElementById('be-customer-modal').style.display = 'flex';
}

function closeCustomerModal() {
    document.getElementById('be-customer-modal').style.display = 'none';
}

document.getElementById('be-customer-builder-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = jQuery(this).serialize();
    jQuery.post(beSettings.ajaxUrl, data + '&action=be_save_customer&nonce=' + beSettings.nonce, function(res) {
        if (res.success) {
            alert('Cliente salvo com sucesso!');
            window.location.reload();
        } else {
            alert('Erro ao salvar: ' + (res.data?.message || 'Tente novamente.'));
        }
    });
});

function deleteCustomer(id) {
    if (!confirm('Deseja excluir este cliente?')) return;
    jQuery.post(beSettings.ajaxUrl, { action: 'be_delete_customer', id: id, nonce: beSettings.nonce }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir.');
    });
}
</script>