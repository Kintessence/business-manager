<?php
if (!defined('ABSPATH')) exit;
/** @var array $orders */
/** @var int $totalOrders */
/** @var float $totalRevenue */
/** @var float $avgTicket */
/** @var string $search */
/** @var int $paged */
/** @var int $totalPages */
/** @var int $totalFiltered */
/** @var array $productsCatalog */
/** @var array $customersList */
/** @var array $deliveryZones */

$reasons = [
    'Aniversário', 'Batizado', 'Casamento', 'Chá de bebê', 'Chá revelação', 
    'Confraternização com amigos', 'Corporativo', 'Data do Varejo', 'Despedida de solteiro', 
    'Evento em família', 'Formatura', 'Lembrancinha', 'Mesversário', 'Natal', 
    'Noivado', 'Páscoa', 'Primeira Eucaristia', 'Pronta entrega', 'Revenda', 'Sessão fotográfica', 'Outros'
];

$prodStatusMap = [
    'orcamento'    => ['label' => 'Orçamento', 'class' => 'be-badge'],
    'agendado'     => ['label' => 'Agendado', 'class' => 'be-badge be-badge-info'],
    'em_producao'  => ['label' => 'Em Produção', 'class' => 'be-badge be-badge-warning'],
    'finalizado'   => ['label' => 'Finalizado', 'class' => 'be-badge be-badge-purple'],
    'entregue'     => ['label' => 'Entregue', 'class' => 'be-badge be-badge-success'],
    'cancelado'    => ['label' => 'Cancelado', 'class' => 'be-badge be-badge-danger'],
];
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <div>
                <span class="be-badge be-badge-success">Gestão de Vendas</span>
                <h1 style="font-size:22px; margin:6px 0 0; font-weight:800;">Pedidos & Encomendas</h1>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="admin.php?page=be-delivery-zones" class="be-pill-btn">🚚 Tabela de Frete</a>
                <a href="admin.php?page=be-csv-import" class="be-pill-btn">Importar Maya</a>
                <button type="button" class="be-btn-primary" onclick="openNewOrderModal()">+ Novo Pedido</button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <div style="background:#f8fafc; border:1px solid var(--be-border); padding:14px; border-radius:8px; text-align:center;">
                <span style="font-size:11px; font-weight:700; color:var(--be-text-muted); text-transform:uppercase;">Total de Pedidos</span>
                <div style="font-size:22px; font-weight:800; color:var(--be-primary); margin-top:2px;"><?php echo $totalOrders; ?></div>
            </div>
            <div style="background:#f8fafc; border:1px solid var(--be-border); padding:14px; border-radius:8px; text-align:center;">
                <span style="font-size:11px; font-weight:700; color:var(--be-text-muted); text-transform:uppercase;">Faturamento Bruto</span>
                <div style="font-size:22px; font-weight:800; color:var(--be-accent); margin-top:2px;">R$ <?php echo number_format($totalRevenue, 2, ',', '.'); ?></div>
            </div>
            <div style="background:#f8fafc; border:1px solid var(--be-border); padding:14px; border-radius:8px; text-align:center;">
                <span style="font-size:11px; font-weight:700; color:var(--be-text-muted); text-transform:uppercase;">Ticket Médio</span>
                <div style="font-size:22px; font-weight:800; color:#0284c7; margin-top:2px;">R$ <?php echo number_format($avgTicket, 2, ',', '.'); ?></div>
            </div>
        </div>

        <form method="get" style="display:flex; gap:8px; margin-bottom:16px;">
            <input type="hidden" name="page" value="be-orders">
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Buscar por cliente, pedido, motivo ou endereço..." style="flex:1; padding:8px 12px; border:1px solid var(--be-border); border-radius:6px;">
            <button type="submit" class="be-btn-primary" style="padding: 8px 16px;">Buscar</button>
            <?php if (!empty($search)): ?>
                <a href="admin.php?page=be-orders" class="be-pill-btn" style="text-decoration:none; display:inline-flex; align-items:center;">Limpar</a>
            <?php endif; ?>
        </form>

        <table class="widefat striped be-table-orders" style="border-radius: 8px; overflow: hidden; border: 1px solid var(--be-border); margin-bottom: 16px;">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">Pedido</th>
                    <th style="width: 32%; text-align: left;">Cliente & Entrega</th>
                    <th style="width: 14%; text-align: center;">Contato</th>
                    <th style="width: 11%; text-align: center;">Status</th>
                    <th style="width: 10%; text-align: center;">Pagamento</th>
                    <th style="width: 13%; text-align: center;">Total</th>
                    <th style="width: 14%; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:24px; color:var(--be-text-muted);">Nenhum pedido encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): 
                        $cleanPhone = preg_replace('/\D/', '', (string)$o->customer_phone);
                        if (strlen($cleanPhone) >= 10 && !str_starts_with($cleanPhone, '55')) {
                            $cleanPhone = '55' . $cleanPhone;
                        }
                        $pStatus = strtolower((string)($o->production_status ?? 'agendado'));
                        $pStatusInfo = $prodStatusMap[$pStatus] ?? $prodStatusMap['agendado'];
                        $isPaid = in_array(strtolower((string)$o->payment_status), ['paid', 'pago'], true) || ((float)$o->amount_paid >= (float)$o->total_amount && (float)$o->total_amount > 0);
                        $hasWa = isset($o->has_whatsapp) ? (bool)$o->has_whatsapp : true;
                        $totalVal = (float)$o->total_amount;
                        $paidVal = (float)$o->amount_paid;
                        $restVal = max(0.0, $totalVal - $paidVal);
                        
                        $isDelivery = in_array(strtolower((string)$o->order_type), ['entrega', 'delivery'], true) || !empty($o->delivery_address);
                        $scheduleDate = !empty($o->schedule_at) ? substr((string)$o->schedule_at, 0, 16) : substr((string)$o->order_date, 0, 10);
                    ?>
                        <tr>
                            <td style="text-align: center;"><strong><?php echo esc_html($o->sequential_id ?: '#' . $o->id); ?></strong></td>
                            <td style="text-align: left;">
                                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                                    <?php echo esc_html($o->customer_name); ?>
                                    <?php if (!empty($o->order_reason)): ?>
                                        <span style="color:#64748b; font-size:12px; font-weight:600;">(<?php echo esc_html($o->order_reason); ?>)</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 3px; line-height: 1.3;">
                                    <span style="font-weight: 600; color: <?php echo $isDelivery ? '#0284c7' : '#059669'; ?>;">
                                        <?php echo $isDelivery ? '🚚 Entrega' : '🏢 Retirada'; ?>
                                    </span>
                                    <span> • 🕒 <?php echo esc_html($scheduleDate); ?></span>
                                    <?php if ($isDelivery && !empty($o->delivery_address)): ?>
                                        <span title="<?php echo esc_attr($o->delivery_address); ?>"> • 📍 <?php echo esc_html(mb_strimwidth((string)$o->delivery_address, 0, 45, '...')); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($o->customer_phone)): ?>
                                    <span><?php echo esc_html($o->customer_phone); ?></span>
                                    <?php if ($hasWa && !empty($cleanPhone)): ?>
                                        <a href="https://wa.me/<?php echo esc_attr($cleanPhone); ?>?text=<?php echo urlencode('Olá ' . $o->customer_name . ', tudo bem? Sobre seu pedido ' . ($o->sequential_id ?: '#' . $o->id)); ?>" target="_blank" style="text-decoration:none; margin-left:4px; color:#25d366; font-size:14px;" title="Abrir WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:var(--be-text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="<?php echo esc_attr($pStatusInfo['class']); ?>" style="display:inline-block;">
                                    <?php echo esc_html($pStatusInfo['label']); ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($isPaid): ?>
                                    <span class="be-badge be-badge-success" style="display:inline-block;">Pago</span>
                                <?php else: ?>
                                    <span class="be-badge be-badge-warning" style="display:inline-block;">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($isPaid): ?>
                                    <strong style="color:#059669; font-size:14px;">R$ <?php echo number_format($totalVal, 2, ',', '.'); ?></strong>
                                <?php elseif ($paidVal > 0): ?>
                                    <span style="color:#059669; font-size:11px; font-weight:700; display:block;">+ R$ <?php echo number_format($paidVal, 2, ',', '.'); ?></span>
                                    <strong style="color:#dc2626; font-size:13px; display:block;">- R$ <?php echo number_format($restVal, 2, ',', '.'); ?></strong>
                                <?php else: ?>
                                    <strong style="color:#dc2626; font-size:14px;">- R$ <?php echo number_format($totalVal, 2, ',', '.'); ?></strong>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <button type="button" class="be-action-btn be-btn-view" onclick="printReceipt(<?php echo (int)$o->id; ?>)" title="Imprimir Comanda / Cupom">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                <button type="button" class="be-action-btn be-btn-view" onclick="quickViewOrder(<?php echo (int)$o->id; ?>)" title="Visualização Rápida">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                <button type="button" class="be-action-btn be-btn-edit" onclick="openOrderModal(<?php echo (int)$o->id; ?>)">Editar</button>
                                <button type="button" class="be-action-btn be-btn-del" onclick="deleteOrder(<?php echo (int)$o->id; ?>)" title="Excluir pedido">✕</button>
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
                    Mostrando <?php echo count($orders); ?> de <?php echo $totalFiltered; ?> pedidos (Página <?php echo $paged; ?> de <?php echo $totalPages; ?>)
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

<!-- Modal de Visualização Rápida (Olhinho) -->
<div id="be-quick-view-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:99999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
    <div style="background:#fff; border-radius:12px; width:580px; max-width:92%; max-height:90vh; overflow-y:auto; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.15);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
            <div>
                <span class="be-badge" id="qv-seq">#000</span>
                <h2 style="font-size:18px; font-weight:800; margin:4px 0 0;" id="qv-title">Visualização do Pedido</h2>
            </div>
            <div style="display:flex; gap:6px;">
                <button type="button" class="be-btn-primary" onclick="printFromQuickView()" style="padding:6px 12px; font-size:12px;"><i class="fa-solid fa-print"></i> Imprimir</button>
                <button type="button" onclick="closeQuickView()" style="background:none; border:none; font-size:22px; cursor:pointer; color:var(--be-text-muted);">&times;</button>
            </div>
        </div>

        <div style="background:#f8fafc; border:1px solid var(--be-border); border-radius:8px; padding:14px; margin-bottom:14px; font-size:13px; line-height:1.5;">
            <div><strong>Cliente:</strong> <span id="qv-customer"></span></div>
            <div><strong>Contato:</strong> <span id="qv-phone"></span></div>
            <div><strong>Tipo / Logística:</strong> <span id="qv-type" style="font-weight:700;"></span></div>
            <div><strong>Data Agendada:</strong> <span id="qv-schedule"></span></div>
            <div id="qv-address-box" style="margin-top:4px;"><strong>Endereço:</strong> <span id="qv-address"></span></div>
        </div>

        <h4 style="font-size:13px; font-weight:700; margin:0 0 6px;">Itens do Pedido:</h4>
        <table class="widefat striped" style="border:1px solid var(--be-border); border-radius:6px; margin-bottom:14px;">
            <thead>
                <tr>
                    <th style="text-align:left;">Item</th>
                    <th style="text-align:center; width:20%;">Qtd</th>
                    <th style="text-align:right; width:25%;">Subtotal</th>
                </tr>
            </thead>
            <tbody id="qv-items-body"></tbody>
        </table>

        <div id="qv-notes-box" style="background:#fffbeb; border:1px solid #fef3c7; padding:10px 12px; border-radius:6px; font-size:12px; margin-bottom:14px; display:none;">
            <strong>Observações:</strong> <span id="qv-notes"></span>
        </div>

        <div style="background:#0f172a; color:#fff; border-radius:8px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <span style="font-size:11px; text-transform:uppercase; color:#94a3b8;">Total do Pedido</span>
                <div id="qv-total" style="font-size:18px; font-weight:800; color:#10b981;">R$ 0,00</div>
            </div>
            <div style="text-align:right;">
                <span style="font-size:11px; text-transform:uppercase; color:#94a3b8;">Saldo Restante</span>
                <div id="qv-rest" style="font-size:16px; font-weight:800; color:#f87171;">R$ 0,00</div>
            </div>
        </div>
    </div>
</div>

<!-- Container Oculto de Impressão Térmica (80mm / 58mm) -->
<div id="be-thermal-receipt" class="printable-receipt" style="display:none;"></div>

<!-- Modal de Edicao / Criacao de Pedido com Busca Rápida de Produtos -->
<div id="be-order-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:99999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
    <div style="background:#fff; border-radius:12px; width:760px; max-width:95%; max-height:92vh; overflow-y:auto; padding:26px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.15);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
            <div>
                <span class="be-badge" id="modal-order-seq">#NOVO</span>
                <h2 style="font-size:20px; font-weight:800; margin:4px 0 0;" id="modal-title">Novo Pedido</h2>
            </div>
            <button type="button" onclick="closeOrderModal()" style="background:none; border:none; font-size:22px; cursor:pointer; color:var(--be-text-muted);">&times;</button>
        </div>

        <form id="be-modal-order-form">
            <input type="hidden" id="modal_order_id" value="0">
            
            <div style="display:grid; grid-template-columns: 2fr 1.2fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Nome do Cliente *</label>
                    <input type="text" id="modal_cust_name" list="customer_suggestions" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" required placeholder="Nome do comprador" onchange="autoFillCustomer(this.value)">
                    <datalist id="customer_suggestions">
                        <?php foreach ($customersList as $c): ?>
                            <option value="<?php echo esc_attr($c->name); ?>" data-phone="<?php echo esc_attr($c->phone); ?>" data-haswa="<?php echo esc_attr((string)($c->has_whatsapp ?? 1)); ?>" data-address="<?php echo esc_attr($c->address); ?>" data-discount="<?php echo esc_attr((string)$c->default_discount); ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Telefone</label>
                    <input type="text" id="modal_cust_phone" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" placeholder="27999999999">
                    <label style="font-size:11px; margin-top:4px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;">
                        <input type="checkbox" id="modal_has_wa" checked> Tem WhatsApp
                    </label>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Status</label>
                    <select id="modal_prod_status" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; font-weight:700;">
                        <option value="agendado">Agendado</option>
                        <option value="orcamento">Orçamento</option>
                        <option value="em_producao">Em Produção</option>
                        <option value="finalizado">Finalizado</option>
                        <option value="entregue">Entregue</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Motivo do Pedido (Opcional)</label>
                    <select id="modal_order_reason" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;">
                        <option value="">-- Selecionar Ocasião (Opcional) --</option>
                        <?php foreach ($reasons as $rs): ?>
                            <option value="<?php echo esc_attr($rs); ?>"><?php echo esc_html($rs); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Data & Hora Agendada</label>
                    <input type="datetime-local" id="modal_schedule_at" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;">
                </div>
            </div>

            <!-- Tabela de Produtos com Busca Rápida -->
            <div style="background:#f8fafc; border:1px solid var(--be-border); border-radius:8px; padding:12px; margin-bottom: 14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <strong style="font-size:13px; color:#1e293b;">Itens do Pedido</strong>
                    <div style="display:flex; gap:6px;">
                        <input type="text" id="quick_prod_search" list="catalog_fast_list" placeholder="🔍 Buscar doce/bolo..." style="padding:4px 8px; font-size:12px; border:1px solid var(--be-border); border-radius:6px; width:180px;" onchange="fastAddProduct(this)">
                        <datalist id="catalog_fast_list">
                            <?php foreach ($productsCatalog as $p): ?>
                                <option value="<?php echo esc_attr($p->name); ?>" data-id="<?php echo (int)$p->id; ?>" data-price="<?php echo esc_attr((string)$p->final_price); ?>">R$ <?php echo number_format((float)$p->final_price, 2, ',', '.'); ?></option>
                            <?php endforeach; ?>
                        </datalist>
                        <button type="button" class="be-pill-btn" onclick="addOrderItemRow()" style="font-size:11px;">+ Item</button>
                    </div>
                </div>
                <table class="widefat" style="border:1px solid var(--be-border); border-radius:6px; background:#fff;">
                    <thead>
                        <tr>
                            <th style="width: 50%; text-align: left;">Produto</th>
                            <th style="width: 15%; text-align: center;">Qtd</th>
                            <th style="width: 18%; text-align: center;">Unitário (R$)</th>
                            <th style="width: 12%; text-align: center;">Subtotal</th>
                            <th style="width: 5%; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody id="modal-order-items-body"></tbody>
                </table>
            </div>

            <!-- Logística -->
            <div style="background:#fff; border:1px solid var(--be-border); border-radius:8px; padding:12px; margin-bottom: 14px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:6px;">Logística & Entrega</label>
                <div style="display:flex; gap:20px; align-items:center; margin-bottom:8px;">
                    <label><input type="radio" name="order_type_radio" value="retirada" checked onchange="toggleDeliveryType(this.value)"> Cliente vai retirar</label>
                    <label><input type="radio" name="order_type_radio" value="entrega" onchange="toggleDeliveryType(this.value)"> Faremos a entrega</label>
                </div>

                <div id="delivery_zone_container" style="display:none; grid-template-columns: 2fr 1fr; gap:12px; margin-top:8px;">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px;">Bairro / Zona de Entrega</label>
                        <select id="modal_delivery_zone" style="width:100%; padding:6px; border:1px solid var(--be-border); border-radius:6px;" onchange="onZoneSelect(this)">
                            <option value="0" data-fee="0">-- Escolha a zona ou digite manual --</option>
                            <?php foreach ($deliveryZones as $z): ?>
                                <option value="<?php echo $z->id; ?>" data-fee="<?php echo esc_attr((string)$z->fee); ?>">
                                    <?php echo esc_html($z->name . ' (R$ ' . number_format((float)$z->fee, 2, ',', '.') . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; margin-bottom:2px;">Taxa de Entrega (R$)</label>
                        <input type="number" step="0.5" id="modal_delivery_fee" value="0.00" style="width:100%; padding:6px; border:1px solid var(--be-border); border-radius:6px; font-weight:700;" oninput="recalcOrderTotal()">
                    </div>
                </div>

                <div style="margin-top:8px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
                        <label style="font-size:11px; font-weight:700;">Endereço de Entrega:</label>
                        <button type="button" class="button button-small" onclick="saveAddressToCustomer()" style="font-size:11px; color:#0369a1;">Salvar endereço no cadastro do cliente</button>
                    </div>
                    <textarea id="modal_address" rows="2" style="width:100%; padding:6px; border:1px solid var(--be-border); border-radius:6px;" placeholder="Rua, número, apto, ponto de referência..."></textarea>
                </div>
            </div>

            <!-- Desconto e Pagamentos -->
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                <div style="background:#f8fafc; border:1px solid var(--be-border); padding:10px; border-radius:8px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Desconto</label>
                    <div style="display:flex; gap:10px; margin-bottom:6px; font-size:12px;">
                        <label><input type="radio" name="discount_type_radio" value="percent" onchange="recalcOrderTotal()"> Em %</label>
                        <label><input type="radio" name="discount_type_radio" value="fixed" checked onchange="recalcOrderTotal()"> Em R$</label>
                    </div>
                    <input type="number" step="0.1" id="modal_discount_val" value="0.00" style="width:100%; padding:6px; border:1px solid var(--be-border); border-radius:6px; font-weight:700;" oninput="recalcOrderTotal()">
                </div>

                <div style="background:#f8fafc; border:1px solid var(--be-border); padding:10px; border-radius:8px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Meio de Pagamento & Entrada</label>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:6px; margin-bottom:6px;">
                        <select id="modal_payment_method" style="width:100%; padding:6px; border:1px solid var(--be-border); border-radius:6px;">
                            <option value="pix">PIX</option>
                            <option value="cash">Dinheiro</option>
                            <option value="card_credit">Cartão de Crédito</option>
                            <option value="card_debit">Cartão de Débito</option>
                        </select>
                        <select id="modal_payment_status" style="width:100%; padding:6px; border:1px solid var(--be-border); border-radius:6px; font-weight:700;">
                            <option value="unpaid">Pendente</option>
                            <option value="paid">Pago</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; color:var(--be-text-muted);">Valor Já Pago / Entrada (R$):</label>
                        <input type="number" step="0.01" id="modal_amount_paid" value="0.00" style="width:100%; padding:6px; border:1px solid var(--be-border); border-radius:6px; font-weight:700; color:#059669;" oninput="recalcOrderTotal()">
                    </div>
                </div>
            </div>

            <!-- Resumo Financeiro Contábil -->
            <div style="background:#0f172a; color:#fff; border-radius:8px; padding:14px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700;">Valor Total</span>
                    <div id="modal-grand-total-display" style="font-size:24px; font-weight:800; color:#10b981;">R$ 0,00</div>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700;">Saldo Restante</span>
                    <div id="modal-rest-pay-display" style="font-size:20px; font-weight:800; color:#f87171;">R$ 0,00</div>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Observações e Detalhes</label>
                <textarea id="modal_notes" rows="2" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; background:#f8fafc;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="be-pill-btn" onclick="closeOrderModal()">Cancelar</button>
                <button type="button" class="be-btn-primary" onclick="saveModalOrder()">Salvar Pedido</button>
            </div>
        </form>
    </div>
</div>

<style>
.be-table-orders th, .be-table-orders td { vertical-align: middle !important; }
.be-action-btn { border: 1px solid var(--be-border); background: #fff; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s ease; }
.be-btn-view { color: #0284c7; }
.be-btn-view:hover { background: #f0f9ff; border-color: #0284c7; }
.be-btn-edit { color: var(--be-accent); }
.be-btn-edit:hover { background: #eff6ff; border-color: var(--be-accent); }
.be-btn-del { color: #991b1b; }
.be-btn-del:hover { background: #fef2f2; border-color: #ef4444; }

.be-badge-info { background: #e0f2fe !important; color: #0369a1 !important; }
.be-badge-purple { background: #ede9fe !important; color: #5b21b6 !important; }
.be-badge-danger { background: #fee2e2 !important; color: #991b1b !important; }

/* CSS Especial para Impressora Térmica 80mm / 58mm */
@media print {
    body * { visibility: hidden; }
    #be-thermal-receipt, #be-thermal-receipt * { visibility: visible; }
    #be-thermal-receipt {
        display: block !important;
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm;
        padding: 4mm;
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        line-height: 1.3;
        color: #000;
        background: #fff;
    }
    .thermal-title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 4px; }
    .thermal-divider { border-top: 1px dashed #000; margin: 6px 0; }
    .thermal-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
}
</style>

<script>
let currentQuickViewId = 0;
const productsCatalog = <?php echo json_encode($productsCatalog); ?>;
const customersList = <?php echo json_encode($customersList); ?>;

function fastAddProduct(input) {
    const val = input.value.trim().toLowerCase();
    const found = productsCatalog.find(p => p.name.toLowerCase() === val);
    if (found) {
        addOrderItemRow(found.id, found.name, 1, found.final_price);
        input.value = '';
        recalcOrderTotal();
    }
}

function quickViewOrder(orderId) {
    currentQuickViewId = orderId;
    const modal = document.getElementById('be-quick-view-modal');
    modal.style.display = 'flex';
    document.getElementById('qv-items-body').innerHTML = '<tr><td colspan="3" style="color:var(--be-text-muted); text-align:center;">Carregando...</td></tr>';

    jQuery.get(beSettings.ajaxUrl, {
        action: 'be_get_order_details',
        id: orderId,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) {
            const o = res.data.order;
            const items = res.data.items || [];

            document.getElementById('qv-seq').innerText = o.sequential_id ? '#' + o.sequential_id : '#' + o.id;
            document.getElementById('qv-title').innerText = 'Pedido de ' + o.customer_name + (o.order_reason ? ' (' + o.order_reason + ')' : '');
            document.getElementById('qv-customer').innerText = o.customer_name || '—';
            document.getElementById('qv-phone').innerText = o.customer_phone || '—';
            
            const isDelivery = (o.order_type === 'entrega' || o.order_type === 'delivery') || (o.delivery_address && o.delivery_address.trim() !== '');
            document.getElementById('qv-type').innerText = isDelivery ? '🚚 Faremos a Entrega' : '🏢 Cliente vai Retirar';
            document.getElementById('qv-schedule').innerText = o.schedule_at ? o.schedule_at.substring(0, 16) : o.order_date.substring(0, 10);
            
            if (isDelivery && o.delivery_address) {
                document.getElementById('qv-address-box').style.display = 'block';
                document.getElementById('qv-address').innerText = o.delivery_address;
            } else {
                document.getElementById('qv-address-box').style.display = 'none';
            }

            let itemsHtml = '';
            if (items.length > 0) {
                items.forEach(it => {
                    itemsHtml += `
                        <tr>
                            <td style="text-align:left;"><strong>${it.product_name}</strong></td>
                            <td style="text-align:center;">${it.quantity}x</td>
                            <td style="text-align:right;">R$ ${parseFloat(it.total_price).toFixed(2).replace('.', ',')}</td>
                        </tr>
                    `;
                });
            } else {
                itemsHtml = '<tr><td colspan="3" style="color:var(--be-text-muted); text-align:center;">Nenhum item discriminado.</td></tr>';
            }
            document.getElementById('qv-items-body').innerHTML = itemsHtml;

            if (o.notes && o.notes.trim() !== '') {
                document.getElementById('qv-notes-box').style.display = 'block';
                document.getElementById('qv-notes').innerText = o.notes;
            } else {
                document.getElementById('qv-notes-box').style.display = 'none';
            }

            const total = parseFloat(o.total_amount) || 0;
            const paid = parseFloat(o.amount_paid) || 0;
            const rest = Math.max(0, total - paid);

            document.getElementById('qv-total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
            document.getElementById('qv-rest').innerText = (rest > 0 ? '- R$ ' : 'R$ ') + rest.toFixed(2).replace('.', ',');
        }
    });
}

function closeQuickView() {
    document.getElementById('be-quick-view-modal').style.display = 'none';
}

function printFromQuickView() {
    if (currentQuickViewId > 0) printReceipt(currentQuickViewId);
}

function printReceipt(orderId) {
    jQuery.get(beSettings.ajaxUrl, {
        action: 'be_get_order_details',
        id: orderId,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) {
            const o = res.data.order;
            const items = res.data.items || [];
            const seq = o.sequential_id ? o.sequential_id : o.id;
            const isDelivery = (o.order_type === 'entrega' || o.order_type === 'delivery') || Boolean(o.delivery_address);
            const total = parseFloat(o.total_amount) || 0;
            const paid = parseFloat(o.amount_paid) || 0;
            const rest = Math.max(0, total - paid);
            const fee = parseFloat(o.delivery_fee) || 0;

            let itemsHtml = '';
            items.forEach(it => {
                itemsHtml += `
                    <div style="margin-bottom:3px;">
                        <div><strong>${it.product_name}</strong></div>
                        <div class="thermal-row" style="font-size:10px;">
                            <span>${parseFloat(it.quantity)} x R$ ${parseFloat(it.unit_price).toFixed(2).replace('.', ',')}</span>
                            <span>R$ ${parseFloat(it.total_price).toFixed(2).replace('.', ',')}</span>
                        </div>
                    </div>
                `;
            });

            const html = `
                <div class="thermal-title">AVULE</div>
                <div class="thermal-row">
                    <strong>Pedido #${seq}</strong>
                    <strong>${o.customer_name}</strong>
                </div>
                <div class="thermal-divider"></div>
                <div><strong>Data de entrega:</strong> ${o.schedule_at ? o.schedule_at.substring(0, 16) : o.order_date.substring(0, 10)}</div>
                <div><strong>Contato:</strong> ${o.customer_phone || '—'}</div>
                ${isDelivery ? `<div><strong>Endereço:</strong> ${o.delivery_address || '—'}</div>` : `<div><strong>Retirada no Local</strong></div>`}
                <div class="thermal-divider"></div>
                <div><strong>Status:</strong> ${o.production_status || 'Agendado'} | ${isPaidStatus(o) ? 'Pago' : 'Não pago'}</div>
                <div class="thermal-divider"></div>
                <div style="font-weight:bold; margin-bottom:4px;">Descrição dos itens:</div>
                ${itemsHtml}
                <div class="thermal-divider"></div>
                ${fee > 0 ? `<div class="thermal-row"><span>Taxa de entrega:</span><span>R$ ${fee.toFixed(2).replace('.', ',')}</span></div>` : ''}
                <div class="thermal-row" style="font-size:13px; font-weight:bold;">
                    <span>Total:</span>
                    <span>R$ ${total.toFixed(2).replace('.', ',')}</span>
                </div>
                <div class="thermal-row"><span>Valor pago:</span><span>R$ ${paid.toFixed(2).replace('.', ',')}</span></div>
                <div class="thermal-row" style="font-weight:bold;"><span>Valor pendente:</span><span>R$ ${rest.toFixed(2).replace('.', ',')}</span></div>
                ${o.notes ? `
                    <div class="thermal-divider"></div>
                    <div><strong>Observações:</strong></div>
                    <div style="white-space:pre-wrap;">${o.notes}</div>
                ` : ''}
                <div class="thermal-divider"></div>
                <div style="text-align:center; font-size:9px; margin-top:6px;">*** Comanda de Produção ***</div>
            `;

            const container = document.getElementById('be-thermal-receipt');
            container.innerHTML = html;
            window.print();
        }
    });
}

function isPaidStatus(o) {
    return (o.payment_status === 'paid' || o.payment_status === 'Pago' || (parseFloat(o.amount_paid) >= parseFloat(o.total_amount) && parseFloat(o.total_amount) > 0));
}

function toggleDeliveryType(val) {
    const container = document.getElementById('delivery_zone_container');
    container.style.display = (val === 'entrega') ? 'grid' : 'none';
    if (val === 'retirada') {
        document.getElementById('modal_delivery_fee').value = '0.00';
    }
    recalcOrderTotal();
}

function onZoneSelect(sel) {
    const opt = sel.options[sel.selectedIndex];
    const fee = parseFloat(opt.getAttribute('data-fee')) || 0;
    document.getElementById('modal_delivery_fee').value = fee.toFixed(2);
    recalcOrderTotal();
}

function autoFillCustomer(val) {
    const found = customersList.find(c => c.name.toLowerCase() === val.toLowerCase());
    if (found) {
        if (found.phone) document.getElementById('modal_cust_phone').value = found.phone;
        document.getElementById('modal_has_wa').checked = found.has_whatsapp !== undefined ? Boolean(parseInt(found.has_whatsapp)) : true;
        if (found.address) document.getElementById('modal_address').value = found.address;
        if (parseFloat(found.default_discount) > 0) {
            document.querySelector('input[name="discount_type_radio"][value="percent"]').checked = true;
            document.getElementById('modal_discount_val').value = parseFloat(found.default_discount).toFixed(1);
            recalcOrderTotal();
        }
    }
}

function saveAddressToCustomer() {
    const name = document.getElementById('modal_cust_name').value.trim();
    const address = document.getElementById('modal_address').value.trim();
    if (!name || !address) return alert('Preencha o nome do cliente e o endereço antes de salvar.');

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_update_customer_address',
        customer_name: name,
        address: address,
        nonce: beSettings.nonce
    }, function(res) {
        alert(res.data?.message || 'Endereço atualizado no cadastro!');
    });
}

function openNewOrderModal() {
    document.getElementById('modal_order_id').value = '0';
    document.getElementById('modal-order-seq').innerText = '#NOVO';
    document.getElementById('modal-title').innerText = 'Novo Pedido';
    document.getElementById('modal_cust_name').value = '';
    document.getElementById('modal_cust_phone').value = '';
    document.getElementById('modal_has_wa').checked = true;
    document.getElementById('modal_prod_status').value = 'agendado';
    document.getElementById('modal_order_reason').value = '';
    document.getElementById('modal_schedule_at').value = '';
    document.querySelector('input[name="order_type_radio"][value="retirada"]').checked = true;
    toggleDeliveryType('retirada');
    document.getElementById('modal_address').value = '';
    document.getElementById('modal_discount_val').value = '0.00';
    document.getElementById('modal_payment_method').value = 'pix';
    document.getElementById('modal_payment_status').value = 'unpaid';
    document.getElementById('modal_amount_paid').value = '0.00';
    document.getElementById('modal_notes').value = '';

    document.getElementById('modal-order-items-body').innerHTML = '';
    addOrderItemRow();
    recalcOrderTotal();

    document.getElementById('be-order-modal').style.display = 'flex';
}

function openOrderModal(orderId) {
    const modal = document.getElementById('be-order-modal');
    modal.style.display = 'flex';
    document.getElementById('modal-order-items-body').innerHTML = '<tr><td colspan="5" style="color:var(--be-text-muted); text-align:center;">Carregando dados...</td></tr>';

    jQuery.get(beSettings.ajaxUrl, {
        action: 'be_get_order_details',
        id: orderId,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) {
            const o = res.data.order;
            const items = res.data.items || [];

            document.getElementById('modal_order_id').value = o.id;
            document.getElementById('modal-order-seq').innerText = o.sequential_id ? '#' + o.sequential_id : '#' + o.id;
            document.getElementById('modal-title').innerText = 'Pedido de ' + o.customer_name;
            document.getElementById('modal_cust_name').value = o.customer_name || '';
            document.getElementById('modal_cust_phone').value = o.customer_phone || '';
            document.getElementById('modal_has_wa').checked = o.has_whatsapp !== undefined ? Boolean(parseInt(o.has_whatsapp)) : true;
            document.getElementById('modal_prod_status').value = o.production_status || 'agendado';
            document.getElementById('modal_order_reason').value = o.order_reason || '';
            document.getElementById('modal_schedule_at').value = o.schedule_at ? o.schedule_at.replace(' ', 'T').substring(0, 16) : '';

            const isDelivery = (o.order_type === 'entrega' || o.order_type === 'delivery');
            document.querySelector(`input[name="order_type_radio"][value="${isDelivery ? 'entrega' : 'retirada'}"]`).checked = true;
            toggleDeliveryType(isDelivery ? 'entrega' : 'retirada');
            document.getElementById('modal_delivery_fee').value = parseFloat(o.delivery_fee || 0).toFixed(2);
            document.getElementById('modal_address').value = o.delivery_address || '';

            const discType = o.discount_type || 'fixed';
            document.querySelector(`input[name="discount_type_radio"][value="${discType}"]`).checked = true;
            document.getElementById('modal_discount_val').value = parseFloat(o.discount_value || 0).toFixed(2);

            document.getElementById('modal_payment_method').value = o.payment_method || 'pix';
            document.getElementById('modal_payment_status').value = (o.payment_status === 'Pago' || o.payment_status === 'paid') ? 'paid' : 'unpaid';
            document.getElementById('modal_amount_paid').value = parseFloat(o.amount_paid || 0).toFixed(2);
            document.getElementById('modal_notes').value = o.notes || '';

            const tbody = document.getElementById('modal-order-items-body');
            tbody.innerHTML = '';

            if (items.length > 0) {
                items.forEach(it => {
                    addOrderItemRow(it.product_id, it.product_name, it.quantity, it.unit_price);
                });
            } else {
                addOrderItemRow();
            }
            recalcOrderTotal();
        }
    });
}

function addOrderItemRow(pId = 0, pName = '', qty = 1, uPrice = 0) {
    const tbody = document.getElementById('modal-order-items-body');
    const tr = document.createElement('tr');
    tr.className = 'order-item-row';

    let options = '<option value="0" data-price="0">-- Digitar item personalizado --</option>';
    productsCatalog.forEach(p => {
        const isSel = (p.id == pId) ? 'selected' : '';
        options += `<option value="${p.id}" data-price="${p.final_price}" ${isSel}>${p.name} (R$ ${parseFloat(p.final_price).toFixed(2).replace('.', ',')})</option>`;
    });

    tr.innerHTML = `
        <td style="text-align: left;">
            <select class="item-prod-select" style="width:100%; margin-bottom:4px;" onchange="onSelectProductChange(this)">
                ${options}
            </select>
            <input type="text" class="item-name-input" value="${pName}" placeholder="Nome do doce/bolo..." style="width:100%; padding:4px 6px; font-size:12px; border:1px solid var(--be-border); border-radius:4px; ${pId > 0 ? 'display:none;' : ''}">
        </td>
        <td style="text-align:center;">
            <input type="number" step="0.5" min="0.5" class="item-qty-input" value="${qty}" style="width:100%; padding:4px; text-align:center; border:1px solid var(--be-border); border-radius:4px;" oninput="recalcOrderTotal()">
        </td>
        <td style="text-align:center;">
            <input type="number" step="0.01" min="0" class="item-price-input" value="${parseFloat(uPrice).toFixed(2)}" style="width:100%; padding:4px; text-align:center; border:1px solid var(--be-border); border-radius:4px;" oninput="recalcOrderTotal()">
        </td>
        <td style="text-align:center; font-weight:700; color:var(--be-primary);" class="item-subtotal-display">
            R$ ${(qty * uPrice).toFixed(2).replace('.', ',')}
        </td>
        <td style="text-align:center;">
            <button type="button" style="background:none; border:none; color:#ef4444; cursor:pointer; font-weight:700; font-size:14px;" onclick="this.closest('tr').remove(); recalcOrderTotal();">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
}

function onSelectProductChange(sel) {
    const row = sel.closest('tr');
    const opt = sel.options[sel.selectedIndex];
    const pId = parseInt(sel.value) || 0;
    const price = parseFloat(opt.getAttribute('data-price')) || 0;
    const nameInput = row.querySelector('.item-name-input');
    const priceInput = row.querySelector('.item-price-input');

    if (pId > 0) {
        nameInput.value = opt.text.split(' (R$')[0];
        nameInput.style.display = 'none';
        priceInput.value = price.toFixed(2);
    } else {
        nameInput.style.display = 'block';
        nameInput.focus();
    }
    recalcOrderTotal();
}

function recalcOrderTotal() {
    let itemsSubtotal = 0;
    document.querySelectorAll('.order-item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.item-price-input').value) || 0;
        const sub = qty * price;
        row.querySelector('.item-subtotal-display').innerText = 'R$ ' + sub.toFixed(2).replace('.', ',');
        itemsSubtotal += sub;
    });

    const discType = document.querySelector('input[name="discount_type_radio"]:checked').value;
    const discVal = parseFloat(document.getElementById('modal_discount_val').value) || 0;
    let discountAmount = (discType === 'percent') ? (itemsSubtotal * (discVal / 100)) : discVal;
    discountAmount = Math.max(0, Math.min(itemsSubtotal, discountAmount));

    const orderType = document.querySelector('input[name="order_type_radio"]:checked').value;
    const deliveryFee = (orderType === 'entrega') ? (parseFloat(document.getElementById('modal_delivery_fee').value) || 0) : 0;

    const grandTotal = Math.max(0, (itemsSubtotal - discountAmount) + deliveryFee);
    const amountPaid = parseFloat(document.getElementById('modal_amount_paid').value) || 0;
    const restToPay = Math.max(0, grandTotal - amountPaid);

    document.getElementById('modal-grand-total-display').innerText = 'R$ ' + grandTotal.toFixed(2).replace('.', ',');
    document.getElementById('modal-rest-pay-display').innerText = (restToPay > 0 ? '- R$ ' : 'R$ ') + restToPay.toFixed(2).replace('.', ',');
}

function closeOrderModal() { document.getElementById('be-order-modal').style.display = 'none'; }

function saveModalOrder() {
    const custName = document.getElementById('modal_cust_name').value.trim();
    if (!custName) return alert('Informe o nome do cliente.');

    const items = [];
    document.querySelectorAll('.order-item-row').forEach(row => {
        const pId = parseInt(row.querySelector('.item-prod-select').value) || 0;
        let pName = row.querySelector('.item-name-input').value.trim();
        if (!pName && pId > 0) {
            pName = row.querySelector('.item-prod-select').options[row.querySelector('.item-prod-select').selectedIndex].text.split(' (R$')[0];
        }
        const qty = parseFloat(row.querySelector('.item-qty-input').value) || 1;
        const uPrice = parseFloat(row.querySelector('.item-price-input').value) || 0;

        if (pName || pId > 0) {
            items.push({
                product_id: pId,
                product_name: pName,
                quantity: qty,
                unit_price: uPrice
            });
        }
    });

    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_save_order',
        id: document.getElementById('modal_order_id').value,
        customer_name: custName,
        customer_phone: document.getElementById('modal_cust_phone').value,
        has_whatsapp: document.getElementById('modal_has_wa').checked ? 1 : 0,
        order_reason: document.getElementById('modal_order_reason').value,
        production_status: document.getElementById('modal_prod_status').value,
        schedule_at: document.getElementById('modal_schedule_at').value,
        order_type: document.querySelector('input[name="order_type_radio"]:checked').value,
        delivery_fee: document.getElementById('modal_delivery_fee').value,
        delivery_address: document.getElementById('modal_address').value,
        discount_type: document.querySelector('input[name="discount_type_radio"]:checked').value,
        discount_value: document.getElementById('modal_discount_val').value,
        payment_method: document.getElementById('modal_payment_method').value,
        payment_status: document.getElementById('modal_payment_status').value,
        amount_paid: document.getElementById('modal_amount_paid').value,
        notes: document.getElementById('modal_notes').value,
        items: items,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao salvar pedido.');
    });
}

function deleteOrder(id) {
    if (!confirm('Deseja excluir este pedido?')) return;
    jQuery.post(beSettings.ajaxUrl, {
        action: 'be_delete_order',
        id: id,
        nonce: beSettings.nonce
    }, function(res) {
        if (res.success) window.location.reload();
        else alert(res.data?.message || 'Erro ao excluir.');
    });
}
</script>