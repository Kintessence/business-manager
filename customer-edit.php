<?php
if (!defined('ABSPATH')) exit;
/** @var object|null $customer */
$hasWhatsapp = isset($customer->has_whatsapp) ? (bool)$customer->has_whatsapp : true;
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <a href="admin.php?page=be-customers" style="font-size:12px; text-decoration:none; color:var(--be-accent); font-weight:700;">← Voltar para Clientes</a>
                <h1 style="font-size:22px; margin:4px 0 0; font-weight:800;">
                    <?php echo $customer ? 'Editar Cliente: ' . esc_html($customer->name) : 'Novo Cliente'; ?>
                </h1>
            </div>
            <button type="button" class="be-btn-primary" onclick="saveCustomer()">Salvar Cliente</button>
        </div>

        <form id="be-customer-form">
            <input type="hidden" id="customer_id" value="<?php echo esc_attr((string)($customer->id ?? 0)); ?>">

            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Nome Completo *</label>
                    <input type="text" id="cust_name" value="<?php echo esc_attr($customer->name ?? ''); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Telefone</label>
                    <input type="text" id="cust_phone" value="<?php echo esc_attr($customer->phone ?? ''); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" placeholder="27999999999">
                    <label style="font-size:11px; margin-top:4px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;">
                        <input type="checkbox" id="cust_has_wa" <?php checked($hasWhatsapp); ?>> Possui WhatsApp
                    </label>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Data de Aniversário</label>
                    <input type="date" id="cust_birthday" value="<?php echo esc_attr($customer->birthday ?? ''); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">E-mail</label>
                    <input type="email" id="cust_email" value="<?php echo esc_attr($customer->email ?? ''); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Instagram</label>
                    <input type="text" id="cust_instagram" value="<?php echo esc_attr($customer->instagram ?? ''); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" placeholder="@usuario">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Desconto Padrão (%)</label>
                    <input type="number" step="0.5" id="cust_discount" value="<?php echo esc_attr((string)($customer->default_discount ?? 0)); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; font-weight:700; color:var(--be-primary);" placeholder="Ex: 5.00">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Endereço de Entrega</label>
                <textarea id="cust_address" rows="2" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" placeholder="Rua, número, apto, bairro, cidade..."><?php echo esc_textarea($customer->address ?? ''); ?></textarea>
            </div>

            <div>
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Preferências ou Notas de Atendimento</label>
                <textarea id="cust_preferences" rows="3" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" placeholder="Ex: Alérgica a nozes..."><?php echo esc_textarea($customer->preferences ?? ''); ?></textarea>
            </div>
        </form>
    </div>
</div>

<script>
function saveCustomer() {
    const name = document.getElementById('cust_name').value.trim();
    if (!name) return alert('O nome do cliente é obrigatório.');

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_save_customer',
        id: document.getElementById('customer_id').value,
        name: name,
        phone: document.getElementById('cust_phone').value,
        has_whatsapp: document.getElementById('cust_has_wa').checked ? 1 : 0,
        birthday: document.getElementById('cust_birthday').value,
        email: document.getElementById('cust_email').value,
        instagram: document.getElementById('cust_instagram').value,
        default_discount: document.getElementById('cust_discount').value,
        address: document.getElementById('cust_address').value,
        preferences: document.getElementById('cust_preferences').value,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.href = res.data.redirect;
        else alert(res.data?.message || 'Erro ao salvar cliente.');
    });
}
</script>