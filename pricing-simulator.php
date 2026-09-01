<?php
if (!defined('ABSPATH')) exit;
/** @var array $products */
/** @var array $recipesList */
/** @var array $profile */
/** @var array $defaultChannels */
/** @var float $cMin */
?>
<div class="wrap be-wrap">
    <div class="be-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <span class="be-badge be-badge-success">Inteligência Financeira</span>
                <h1 style="font-size: 24px; margin: 6px 0 0; font-weight: 800;">🧭 Simulador Multicanal & Diagnóstico de Margem</h1>
                <p style="color: var(--be-text-muted); margin: 4px 0 0; font-size: 13px;">Simule o preço de venda e teste o impacto real das taxas de cartão, delivery e aplicativos antes de vender.</p>
            </div>
            <a href="admin.php?page=be-products" class="be-pill-btn" style="text-decoration:none;">🏷️ Ir para Catálogo</a>
        </div>

        <!-- Seletor Rápido de Item -->
        <div style="background: #f8fafc; border: 1px solid var(--be-border); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--be-text-muted); margin-bottom: 6px;">
                📋 Carregar Item Cadastrado para Análise:
            </label>
            <select id="sim_item_picker" style="width: 100%; max-width: 500px; padding: 8px; border: 1px solid var(--be-border); border-radius: 6px; font-weight: 600;" onchange="loadSelectedItem(this)">
                <option value="0" data-cost="0" data-price="0">-- Simulação Manual / Custo Avulso --</option>
                
                <?php if (!empty($products)): ?>
                    <optgroup label="🏷️ Produtos Comerciais">
                        <?php foreach ($products as $p): ?>
                            <option value="prod_<?php echo $p->id; ?>" data-cost="<?php echo esc_attr((string)($p->final_price * 0.5)); ?>" data-price="<?php echo esc_attr((string)$p->final_price); ?>">
                                <?php echo esc_html($p->name); ?> (Preço Atual: R$ <?php echo number_format((float)$p->final_price, 2, ',', '.'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>

                <?php if (!empty($recipesList)): ?>
                    <optgroup label="📋 Fichas Técnicas (Receitas)">
                        <?php foreach ($recipesList as $r): ?>
                            <option value="rec_<?php echo $r['id']; ?>" data-cost="<?php echo esc_attr((string)$r['portion_cost']); ?>" data-price="0">
                                <?php echo esc_html($r['name']); ?> (Custo/Porção: R$ <?php echo number_format((float)$r['portion_cost'], 2, ',', '.'); ?> / <?php echo esc_html($r['yield_unit']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 400px 1fr; gap: 24px; align-items: start;">
            
            <!-- Coluna 1: Parâmetros & Slider -->
            <div style="background: #fff; border: 1px solid var(--be-border); padding: 20px; border-radius: 8px;">
                <h3 style="font-size: 16px; font-weight: 800; margin-top: 0; margin-bottom: 14px;">1. Parâmetros do Produto</h3>

                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Custo Direto de Fabricação (R$) *</label>
                    <input type="number" step="0.01" id="sim_base_cost" value="12.50" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px; font-weight:700;" oninput="recalcSimulator()">
                    <small style="color:var(--be-text-muted); font-size:11px;">CMV + Embalagens + Rateio de Mão de Obra</small>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Margem Líquida Alvo no Bolso (%) *</label>
                    <input type="number" step="0.5" id="sim_target_margin" value="<?php echo esc_attr((string)($profile['margin'] ?? 25)); ?>" style="width:100%; padding:8px; border:1px solid var(--be-border); border-radius:6px;" oninput="recalcSimulator()">
                    <small style="color:var(--be-text-muted); font-size:11px;">Percentual limpo que pertence à empresa</small>
                </div>

                <hr style="border:0; border-top:1px solid var(--be-border); margin:18px 0;">

                <h3 style="font-size: 16px; font-weight: 800; margin: 0 0 14px;">2. Teste do Preço de Venda</h3>

                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Preço Planejado de Etiqueta (R$)</label>
                    <input type="number" step="0.50" id="sim_price_input" value="25.00" style="width:100%; padding:10px; border:1px solid var(--be-border); border-radius:6px; font-weight:800; font-size:20px; color:var(--be-primary);" oninput="syncFromInput(this.value)">
                    
                    <div style="margin-top: 10px;">
                        <input type="range" id="sim_price_slider" min="1" max="100" step="0.50" value="25.00" style="width:100%; cursor:pointer;" oninput="syncFromSlider(this.value)">
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid var(--be-border); padding: 12px; border-radius: 6px; text-align: center;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted); text-transform: uppercase;">Markup Aplicado</span>
                    <div id="sim_markup_tag" style="font-size: 22px; font-weight: 800; color: var(--be-accent); margin-top: 2px;">2.00x</div>
                    <small id="sim_markup_desc" style="color: var(--be-text-muted); font-size: 11px;">Preço é 2.0x o custo de fabricação</small>
                </div>
            </div>

            <!-- Coluna 2: Diagnóstico Multicanal -->
            <div>
                <div style="background: #fff; border: 1px solid var(--be-border); padding: 20px; border-radius: 8px; margin-bottom: 16px;">
                    <h3 style="font-size: 16px; font-weight: 800; margin-top: 0; margin-bottom: 4px;">💡 Comportamento nos Canais de Venda</h3>
                    <p style="color: var(--be-text-muted); font-size: 13px; margin: 0 0 16px;">
                        Veja como o preço simulado de <strong id="sim_price_badge" style="color: var(--be-primary);">R$ 25,00</strong> se comporta em cada canal:
                    </p>

                    <table class="widefat striped" style="border: 1px solid var(--be-border); border-radius: 6px; overflow: hidden;">
                        <thead>
                            <tr>
                                <th>Canal de Venda</th>
                                <th>Taxa (%)</th>
                                <th>Preço Ideal p/ Meta</th>
                                <th>Sobra Líquida (R$)</th>
                                <th>Margem Real</th>
                            </tr>
                        </thead>
                        <tbody id="sim_channels_body">
                            <!-- Injetado via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Box de Diagnóstico Sintético -->
                <div id="sim_diag_box" style="padding: 16px 20px; border-radius: 8px; border-left: 4px solid var(--be-success); background: #f0fdf4;">
                    <strong id="sim_diag_title" style="font-size: 14px; color: #166534;">✅ Margem Saudável</strong>
                    <p id="sim_diag_text" style="margin: 6px 0 0; font-size: 13px; color: #1e293b; line-height: 1.4;">
                        O preço cobre os custos e retém margem positiva em todos os canais de venda configurados.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
const defaultChannels = <?php echo json_encode($defaultChannels); ?>;

function loadSelectedItem(sel) {
    const opt = sel.options[sel.selectedIndex];
    const cost = parseFloat(opt.getAttribute('data-cost')) || 0;
    const price = parseFloat(opt.getAttribute('data-price')) || 0;

    if (cost > 0) {
        document.getElementById('sim_base_cost').value = cost.toFixed(2);
    }
    if (price > 0) {
        document.getElementById('sim_price_input').value = price.toFixed(2);
        document.getElementById('sim_price_slider').value = price.toFixed(2);
    } else {
        // Sugere preço inicial baseado na margem
        const margin = parseFloat(document.getElementById('sim_target_margin').value) || 25;
        const divisor = Math.max(0.05, (100 - margin) / 100);
        const suggested = cost > 0 ? (cost / divisor) : 25;
        document.getElementById('sim_price_input').value = suggested.toFixed(2);
        document.getElementById('sim_price_slider').value = suggested.toFixed(2);
    }

    recalcSimulator();
}

function syncFromSlider(val) {
    document.getElementById('sim_price_input').value = parseFloat(val).toFixed(2);
    recalcSimulator();
}

function syncFromInput(val) {
    const num = parseFloat(val) || 0;
    document.getElementById('sim_price_slider').value = num;
    recalcSimulator();
}

function recalcSimulator() {
    const cost = parseFloat(document.getElementById('sim_base_cost').value) || 0;
    const targetMargin = parseFloat(document.getElementById('sim_target_margin').value) || 25;
    let price = parseFloat(document.getElementById('sim_price_input').value) || 0;

    // Atualiza slider limites
    const maxVal = Math.max(50, Math.ceil(cost * 4));
    document.getElementById('sim_price_slider').max = maxVal;

    document.getElementById('sim_price_badge').innerText = 'R$ ' + price.toFixed(2).replace('.', ',');

    // Markup
    const markup = cost > 0 ? (price / cost) : 0;
    document.getElementById('sim_markup_tag').innerText = markup.toFixed(2) + 'x';
    document.getElementById('sim_markup_desc').innerText = `Preço é ${markup.toFixed(1)}x o custo de fabricação`;

    const tbody = document.getElementById('sim_channels_body');
    tbody.innerHTML = '';

    let lowestMargin = 100;
    let worstChannel = '';

    defaultChannels.forEach(ch => {
        const fee = parseFloat(ch.fee) || 0;
        
        // 1. Preço ideal pelo Markup Divisor
        const deductions = targetMargin + fee;
        const divisor = Math.max(0.05, (100 - deductions) / 100);
        const idealPrice = cost > 0 ? (cost / divisor) : 0;

        // 2. Sobra real no bolso com o preço testado
        const feeAmount = price * (fee / 100);
        const netPocket = price - cost - feeAmount;
        const realMargin = price > 0 ? ((netPocket / price) * 100) : 0;

        if (realMargin < lowestMargin) {
            lowestMargin = realMargin;
            worstChannel = ch.name;
        }

        let badgeStyle = 'background:#d1fae5; color:#065f46;';
        if (netPocket <= 0 || realMargin < 5) {
            badgeStyle = 'background:#fee2e2; color:#991b1b;';
        } else if (realMargin < targetMargin) {
            badgeStyle = 'background:#fef3c7; color:#92400e;';
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${ch.name}</strong></td>
            <td>${fee.toFixed(1)}%</td>
            <td><strong style="color:var(--be-text-muted);">R$ ${idealPrice.toFixed(2).replace('.', ',')}</strong></td>
            <td><strong>R$ ${netPocket.toFixed(2).replace('.', ',')}</strong></td>
            <td>
                <span style="padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700; ${badgeStyle}">
                    ${realMargin.toFixed(1)}%
                </span>
            </td>
        `;
        tbody.appendChild(tr);
    });

    // Diagnóstico
    const diagBox = document.getElementById('sim_diag_box');
    const diagTitle = document.getElementById('sim_diag_title');
    const diagText = document.getElementById('sim_diag_text');

    if (lowestMargin < 0) {
        diagBox.style.background = '#fef2f2';
        diagBox.style.borderLeftColor = '#ef4444';
        diagTitle.innerText = '🚨 Alerta: Prejuízo em Canais de Alta Taxa';
        diagTitle.style.color = '#991b1b';
        diagText.innerHTML = `Cobrando <strong>R$ ${price.toFixed(2).replace('.', ',')}</strong>, você perde dinheiro em canais como <strong>${worstChannel}</strong>. Pratique um preço diferenciado para delivery!`;
    } else if (lowestMargin < targetMargin) {
        diagBox.style.background = '#fffbeb';
        diagBox.style.borderLeftColor = '#f59e0b';
        diagTitle.innerText = '⚠️ Margem Comprimida em Plataformas';
        diagTitle.style.color = '#92400e';
        diagText.innerHTML = `O preço cobre os custos, mas em canais com comissão alta (como ${worstChannel}) sua margem fica abaixo da meta de ${targetMargin}%.`;
    } else {
        diagBox.style.background = '#f0fdf4';
        diagBox.style.borderLeftColor = '#10b981';
        diagTitle.innerText = '✅ Margem Blindada em Todos os Canais';
        diagTitle.style.color = '#166534';
        diagText.innerHTML = `Excelente! O preço absorve até a taxa do canal mais caro e retém o lucro líquido desejado no seu caixa.`;
    }
}

document.addEventListener('DOMContentLoaded', recalcSimulator);
</script>