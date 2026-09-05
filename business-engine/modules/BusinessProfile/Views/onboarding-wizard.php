<?php
if (!defined('ABSPATH')) exit;
/** @var \BusinessEngine\BusinessProfile\DTOs\BusinessProfileDTO $profile */

$daysMap = [
    'monday'    => 'Seg',
    'tuesday'   => 'Ter',
    'wednesday' => 'Qua',
    'thursday'  => 'Qui',
    'friday'    => 'Sex',
    'saturday'  => 'Sáb',
    'sunday'    => 'Dom',
];
?>

<div class="be-wrap">
    
    <!-- Cabeçalho Oficial Unificado com Atalho de Fluxo -->
    <div class="be-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; width: 100%;">
        <div class="be-header-title">
            <h1>Configuração Financeira & Capacidade</h1>
            <p>Mapeie o pró-labore, detalhe as despesas fixas e ajuste a disponibilidade semanal de cada integrante.</p>
        </div>
        <div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=be-ingredients')); ?>" class="be-pill-btn" style="height: 38px; padding: 0 16px; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; text-decoration: none; font-weight: 700;">
                <span>Insumos & Embalagens</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 24px; align-items: start;">
        
        <div class="be-card" style="padding: 24px;">
            <form id="be-profile-form">
                
                <!-- 1. Pró-Labore -->
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 6px; color: var(--be-primary);">
                        Pró-labore Mensal do Dono (R$)
                        <span class="be-help-tip" data-tip="O seu salário pelo trabalho operacional. O pró-labore é custo de mão de obra e deve ser pago antes de apurar o lucro da empresa.">?</span>
                    </label>
                    <input type="number" step="50" min="0" name="profile[owner_salary_target]" id="be_salary" value="<?php echo esc_attr((string)$profile->ownerSalaryTarget); ?>" class="be-modal-input" style="width: 100%; max-width: 280px; font-weight: 700; font-size: 14px !important;">
                    
                    <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                        <button type="button" class="be-pill-btn" onclick="setSalaryVal(2000)">R$ 2.000 (Início)</button>
                        <button type="button" class="be-pill-btn" onclick="setSalaryVal(3000)">R$ 3.000 (Médio)</button>
                        <button type="button" class="be-pill-btn" onclick="setSalaryVal(5000)">R$ 5.000 (Consolidado)</button>
                    </div>
                </div>

                <!-- 2. Custos Fixos com Detalhamento Expansível -->
                <div style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label style="font-weight: 700; font-size: 13px; color: var(--be-primary);">
                            Custos Fixos Operacionais (R$)
                            <span class="be-help-tip" data-tip="Contas recorrentes: gás, fração de energia, água, DAS do MEI, internet e manutenção de equipamentos.">?</span>
                        </label>
                        <button type="button" class="be-pill-btn" id="btn-toggle-expenses" onclick="toggleExpensesDetail()">
                            Detalhar Gastos
                        </button>
                    </div>
                    
                    <input type="number" step="10" min="0" name="profile[fixed_expenses_total]" id="be_fixed" value="<?php echo esc_attr((string)$profile->fixedExpensesTotal); ?>" class="be-modal-input" style="width: 100%; max-width: 280px; font-weight: 700; font-size: 14px !important;">
                    
                    <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                        <button type="button" class="be-pill-btn" onclick="applyFixedPreset('casa')">R$ 450 (Cozinha em Casa)</button>
                        <button type="button" class="be-pill-btn" onclick="applyFixedPreset('atelie')">R$ 950 (Ateliê Pequeno)</button>
                        <button type="button" class="be-pill-btn" onclick="applyFixedPreset('comercial')">R$ 2.200 (Ponto Comercial)</button>
                    </div>

                    <div id="be-expenses-breakdown" style="display: none; margin-top: 16px; background: var(--be-bg); border: 1px solid var(--be-border-subtle); border-radius: 8px; padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span style="font-size: 13px; font-weight: 700; color: var(--be-primary);">Composição das Contas Mensais</span>
                            <button type="button" class="be-pill-btn" onclick="addExpenseRow('', 0)">+ Incluir Gasto</button>
                        </div>
                        
                        <div id="be-expenses-list" style="display: flex; flex-direction: column; gap: 8px;"></div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px dashed var(--be-border-subtle);">
                            <span style="font-size: 12px; font-weight: 700; color: var(--be-text-muted);">Soma dos Itens Detalhados:</span>
                            <strong style="font-size: 13px; color: var(--be-primary);" id="lbl-breakdown-sum">R$ 0,00</strong>
                        </div>
                    </div>
                </div>

                <!-- 3. Equipe com Nomes Editáveis e Horas Individuais -->
                <div style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <label style="font-weight: 700; font-size: 13px; color: var(--be-primary);">
                            Equipe de Produção & Horas Individuais
                            <span class="be-help-tip" data-tip="Informe o nome e as horas de produção real de cada integrante. O sistema somará a capacidade de todos.">?</span>
                        </label>
                        <button type="button" class="be-pill-btn" onclick="addTeamMember()">+ Adicionar Integrante</button>
                    </div>

                    <div id="team-members-container">
                        <div class="be-member-card" id="member-row-1" style="background: var(--be-bg); border: 1px solid var(--be-border-subtle); border-radius: 8px; padding: 14px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <input type="text" class="be-member-name-input" value="Dono / Titular" placeholder="Nome do integrante...">
                                <div style="display: flex; gap: 6px;">
                                    <button type="button" class="be-pill-btn" style="padding: 2px 8px; font-size: 11px;" onclick="setMemberSchedule(1, 8, true)">8 horas (Seg a Sex)</button>
                                    <button type="button" class="be-pill-btn" style="padding: 2px 8px; font-size: 11px;" onclick="setMemberSchedule(1, 6, true)">6 horas (Seg a Sex)</button>
                                    <button type="button" class="be-pill-btn be-pill-btn-danger" style="padding: 2px 8px; font-size: 11px;" onclick="setMemberSchedule(1, 0, false)">Zerar</button>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;">
                                <?php foreach ($daysMap as $k => $label): ?>
                                    <div style="text-align: center;">
                                        <span style="font-size: 11px; font-weight: 700; display: block; color: var(--be-text-muted); margin-bottom: 4px;"><?php echo esc_html($label); ?></span>
                                        <input type="number" step="0.5" min="0" max="24" class="be-modal-input sched-input-m1" data-member="1" value="<?php echo ($k === 'saturday' || $k === 'sunday') ? '0' : '8'; ?>" style="text-align: center; width: 100%; font-weight: 700; padding: 0 4px !important;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="profile[production_staff_count]" id="be_staff" value="1">
                    <input type="hidden" name="profile[work_days_per_week]" id="be_days_week" value="5">
                    <input type="hidden" name="profile[work_hours_per_day]" id="be_hours_day" value="8">
                </div>

                <!-- 4. Margem e Taxas -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 6px; color: var(--be-primary);">
                            Margem Líquida Alvo (%)
                            <span class="be-help-tip" data-tip="Lucro limpo da empresa após salários e insumos pagos (Padrão: 20% a 30%).">?</span>
                        </label>
                        <input type="number" step="0.5" min="1" max="80" name="profile[target_net_margin]" id="be_margin" value="<?php echo esc_attr((string)$profile->targetNetMargin); ?>" class="be-modal-input" style="width: 100%; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 6px; color: var(--be-primary);">
                            Taxas Médias de Venda / Cartão (%)
                            <span class="be-help-tip" data-tip="Estimativa média das taxas de maquininha ou intermediadores de pagamento.">?</span>
                        </label>
                        <input type="number" step="0.1" min="0" max="30" name="profile[card_fee_percent]" id="be_card" value="<?php echo esc_attr((string)$profile->cardFeePercent); ?>" class="be-modal-input" style="width: 100%; font-weight: 700;">
                    </div>
                </div>

                <button type="submit" class="be-btn-primary" style="width: 100%; justify-content: center; height: 42px;">
                    Salvar e Calibrar Motor
                </button>
            </form>
        </div>

        <!-- Raio-X Escuro Suave em Tempo Real -->
        <div class="be-card" id="rx-panel" style="background: var(--be-primary); color: #fff; border: none; position: sticky; top: 20px; padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 12px; margin-bottom: 18px;">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; font-weight: 700;">Diagnóstico em Tempo Real</span>
                    <h2 style="color: #fff; font-size: 20px; margin: 2px 0 0; font-weight: 800;">Raio-X do Motor</h2>
                </div>
                <button type="button" class="be-pill-btn" id="btn-toggle-rx-tips" onclick="toggleRxTips()" style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.14); color: #e2e8f0; font-size: 11px;">
                    Ocultar explicações
                </button>
            </div>

            <div style="display: grid; gap: 12px;">
                <div class="rx-card-item">
                    <div style="font-size: 26px; font-weight: 800; color: #38bdf8; font-variant-numeric: tabular-nums;" id="rx_cmin">R$ 0,0000</div>
                    <div style="font-size: 11px; text-transform: uppercase; color: #cbd5e1; margin-top: 4px; font-weight: 700;">Custo por minuto de produção</div>
                    <div class="rx-tip-text">Representa quanto cada minuto de forno, bancada ou execução consome da sua estrutura fixa e pró-labore.</div>
                </div>

                <div class="rx-card-item">
                    <div style="font-size: 26px; font-weight: 800; color: #4ade80; font-variant-numeric: tabular-nums;" id="rx_chora">R$ 0,00</div>
                    <div style="font-size: 11px; text-transform: uppercase; color: #cbd5e1; margin-top: 4px; font-weight: 700;">Custo da hora produtiva</div>
                    <div class="rx-tip-text">Valor total necessário por hora trabalhada para pagar suas contas e o pró-labore mensal.</div>
                </div>

                <div class="rx-card-item">
                    <div style="font-size: 22px; font-weight: 800; color: #f8fafc; font-variant-numeric: tabular-nums;" id="rx_hours">0,0 horas</div>
                    <div style="font-size: 11px; text-transform: uppercase; color: #cbd5e1; margin-top: 4px; font-weight: 700;" id="rx_hours_desc">Capacidade total da equipe</div>
                    <div class="rx-tip-text">Total de horas úteis mensais considerando 85% de eficiência real de trabalho.</div>
                </div>

                <div class="rx-card-item">
                    <div style="font-size: 22px; font-weight: 800; color: #fbbf24; font-variant-numeric: tabular-nums;" id="rx_breakeven">R$ 0,00</div>
                    <div style="font-size: 11px; text-transform: uppercase; color: #cbd5e1; margin-top: 4px; font-weight: 700;">Ponto de equilíbrio mensal</div>
                    <div class="rx-tip-text">Faturamento mínimo que você precisa atingir todo mês apenas para pagar todos os custos sem ter prejuízo.</div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
let memberCount = 1;

const animatedValues = { cmin: 0, chora: 0, hours: 0, breakeven: 0 };

function animateTo(key, targetValue, duration, formatter) {
    const startValue = animatedValues[key];
    const startTime = performance.now();

    function update(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        const current = startValue + (targetValue - startValue) * ease;
        formatter(current);

        if (progress < 1) requestAnimationFrame(update);
        else {
            animatedValues[key] = targetValue;
            formatter(targetValue);
        }
    }
    requestAnimationFrame(update);
}

function toggleRxTips() {
    const panel = document.getElementById('rx-panel');
    const btn = document.getElementById('btn-toggle-rx-tips');
    const isDismissed = panel.classList.toggle('rx-tips-dismissed');
    btn.innerText = isDismissed ? 'Exibir explicações' : 'Ocultar explicações';
    localStorage.setItem('be_rx_tips_dismissed', isDismissed ? '1' : '0');
}

// Inicializar estado salvo das dicas do Raio-X
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('be_rx_tips_dismissed') === '1') {
        const panel = document.getElementById('rx-panel');
        const btn = document.getElementById('btn-toggle-rx-tips');
        if (panel && btn) {
            panel.classList.add('rx-tips-dismissed');
            btn.innerText = 'Exibir explicações';
        }
    }
});

const expensePresets = {
    casa: [
        { name: 'Energia elétrica (fração da produção)', val: 120 },
        { name: 'Gás de cozinha (botijão proporcional)', val: 100 },
        { name: 'Água e saneamento proporcional', val: 45 },
        { name: 'DAS / MEI (tributo fixo mensal)', val: 75 },
        { name: 'Internet e divulgação', val: 60 },
        { name: 'Manutenção e reserva de equipamentos', val: 50 }
    ],
    atelie: [
        { name: 'Aluguel do ateliê / espaço próprio', val: 400 },
        { name: 'Energia elétrica comercial', val: 180 },
        { name: 'Gás comercial / encanado', val: 130 },
        { name: 'Água e saneamento', val: 65 },
        { name: 'DAS / MEI', val: 75 },
        { name: 'Internet e ferramentas digitais', val: 100 }
    ],
    comercial: [
        { name: 'Aluguel do ponto comercial', val: 1200 },
        { name: 'Condomínio / IPTU proporcional', val: 200 },
        { name: 'Energia elétrica comercial', val: 320 },
        { name: 'Água e gás encanado', val: 200 },
        { name: 'Contabilidade e sistemas', val: 180 },
        { name: 'Internet comercial', val: 100 }
    ]
};

function toggleExpensesDetail() {
    const box = document.getElementById('be-expenses-breakdown');
    const btn = document.getElementById('btn-toggle-expenses');
    const isHidden = box.style.display === 'none';
    box.style.display = isHidden ? 'block' : 'none';
    btn.innerText = isHidden ? 'Ocultar Detalhes' : 'Detalhar Gastos';

    if (isHidden && document.querySelectorAll('.be-exp-item-row').length === 0) {
        applyFixedPreset('casa');
    }
}

function addExpenseRow(name = '', val = 0) {
    const list = document.getElementById('be-expenses-list');
    const row = document.createElement('div');
    row.className = 'be-exp-item-row';
    row.style.cssText = 'display: flex; gap: 8px; align-items: center;';
    row.innerHTML = `
        <input type="text" class="be-modal-input be-exp-name" value="${name}" placeholder="Ex: internet, gás, DAS MEI, aluguel..." style="flex: 1;">
        <div style="display: flex; align-items: center; gap: 4px; width: 130px;">
            <span style="font-size: 11px; font-weight: 700; color: var(--be-text-muted);">R$</span>
            <input type="number" step="5" min="0" class="be-modal-input be-exp-val" value="${val}" style="width: 100%; font-weight: 600; text-align: right;">
        </div>
        <button type="button" class="be-pill-btn be-pill-btn-danger" style="padding: 2px 8px; font-size: 11px;" onclick="this.closest('.be-exp-item-row').remove(); syncExpensesFromBreakdown();" title="Remover item">✕</button>
    `;
    list.appendChild(row);

    row.querySelectorAll('input').forEach(inp => {
        inp.addEventListener('input', syncExpensesFromBreakdown);
    });

    syncExpensesFromBreakdown();
}

function applyFixedPreset(presetKey) {
    const items = expensePresets[presetKey] || [];
    const list = document.getElementById('be-expenses-list');
    list.innerHTML = '';
    items.forEach(item => addExpenseRow(item.name, item.val));
    syncExpensesFromBreakdown();
}

function syncExpensesFromBreakdown() {
    let total = 0;
    document.querySelectorAll('.be-exp-val').forEach(inp => {
        total += parseFloat(inp.value) || 0;
    });
    document.getElementById('lbl-breakdown-sum').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
    document.getElementById('be_fixed').value = total;
    beRecalcLive();
}

function setSalaryVal(val) {
    document.getElementById('be_salary').value = val;
    beRecalcLive();
}

function addTeamMember() {
    memberCount++;
    const container = document.getElementById('team-members-container');
    const memberId = memberCount;

    const div = document.createElement('div');
    div.className = 'be-member-card';
    div.id = 'member-row-' + memberId;
    div.style.cssText = 'background: var(--be-bg); border: 1px solid var(--be-border-subtle); border-radius: 8px; padding: 14px; margin-bottom: 12px;';
    div.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <input type="text" class="be-member-name-input" value="Ajudante ${memberId}" placeholder="Nome do integrante...">
            <div style="display: flex; gap: 6px;">
                <button type="button" class="be-pill-btn" style="padding: 2px 8px; font-size: 11px;" onclick="setMemberSchedule(${memberId}, 8, true)">8 horas (Seg a Sex)</button>
                <button type="button" class="be-pill-btn" style="padding: 2px 8px; font-size: 11px;" onclick="setMemberSchedule(${memberId}, 4, true)">4 horas (Seg a Sex)</button>
                <button type="button" class="be-pill-btn be-pill-btn-danger" style="padding: 2px 8px; font-size: 11px;" onclick="removeMember(${memberId})">Remover</button>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;">
            <div style="text-align: center;"><span style="font-size: 11px; font-weight: 700; display: block; color: var(--be-text-muted); margin-bottom: 4px;">Seg</span><input type="number" step="0.5" min="0" max="24" class="be-modal-input sched-input-m${memberId}" value="0" style="text-align: center; width: 100%; font-weight: 700; padding: 0 4px !important;"></div>
            <div style="text-align: center;"><span style="font-size: 11px; font-weight: 700; display: block; color: var(--be-text-muted); margin-bottom: 4px;">Ter</span><input type="number" step="0.5" min="0" max="24" class="be-modal-input sched-input-m${memberId}" value="0" style="text-align: center; width: 100%; font-weight: 700; padding: 0 4px !important;"></div>
            <div style="text-align: center;"><span style="font-size: 11px; font-weight: 700; display: block; color: var(--be-text-muted); margin-bottom: 4px;">Qua</span><input type="number" step="0.5" min="0" max="24" class="be-modal-input sched-input-m${memberId}" value="0" style="text-align: center; width: 100%; font-weight: 700; padding: 0 4px !important;"></div>
            <div style="text-align: center;"><span style="font-size: 11px; font-weight: 700; display: block; color: var(--be-text-muted); margin-bottom: 4px;">Qui</span><input type="number" step="0.5" min="0" max="24" class="be-modal-input sched-input-m${memberId}" value="0" style="text-align: center; width: 100%; font-weight: 700; padding: 0 4px !important;"></div>
            <div style="text-align: center;"><span style="font-size: 11px; font-weight: 700; display: block; color: var(--be-text-muted); margin-bottom: 4px;">Sex</span><input type="number" step="0.5" min="0" max="24" class="be-modal-input sched-input-m${memberId}" value="0" style="text-align: center; width: 100%; font-weight: 700; padding: 0 4px !important;"></div>
            <div style="text-align: center;"><span style="font-size: 11px; font-weight: 700; display: block; color: var(--be-text-muted); margin-bottom: 4px;">Sáb</span><input type="number" step="0.5" min="0" max="24" class="be-modal-input sched-input-m${memberId}" value="0" style="text-align: center; width: 100%; font-weight: 700; padding: 0 4px !important;"></div>
            <div style="text-align: center;"><span style="font-size: 11px; font-weight: 700; display: block; color: var(--be-text-muted); margin-bottom: 4px;">Dom</span><input type="number" step="0.5" min="0" max="24" class="be-modal-input sched-input-m${memberId}" value="0" style="text-align: center; width: 100%; font-weight: 700; padding: 0 4px !important;"></div>
        </div>
    `;
    container.appendChild(div);

    div.querySelectorAll('input').forEach(el => {
        el.addEventListener('input', beRecalcLive);
    });

    beRecalcLive();
}

function removeMember(memberId) {
    const el = document.getElementById('member-row-' + memberId);
    if (el) {
        el.remove();
        beRecalcLive();
    }
}

function setMemberSchedule(memberId, val, workdaysOnly) {
    const inputs = document.querySelectorAll('.sched-input-m' + memberId);
    inputs.forEach((inp, idx) => {
        if (workdaysOnly && (idx === 5 || idx === 6)) inp.value = 0;
        else inp.value = val;
    });
    beRecalcLive();
}

function beRecalcLive() {
    const salary = parseFloat(document.getElementById('be_salary').value) || 0;
    const fixed  = parseFloat(document.getElementById('be_fixed').value) || 0;
    const card   = parseFloat(document.getElementById('be_card').value) || 0;

    const allMemberCards = document.querySelectorAll('.be-member-card');
    const totalStaff = allMemberCards.length;
    document.getElementById('be_staff').value = totalStaff;

    let totalTeamWeeklyHours = 0;
    allMemberCards.forEach(card => {
        card.querySelectorAll('input[type="number"]').forEach(inp => {
            totalTeamWeeklyHours += parseFloat(inp.value) || 0;
        });
    });

    const monthlyProductiveHours = Math.max(1.0, totalTeamWeeklyHours * 4.3333 * 0.85);
    const totalStructural = salary + fixed;
    const costPerHour = totalStructural / monthlyProductiveHours;
    const costPerMinute = costPerHour / 60.0;
    const breakEven = totalStructural / (Math.max(5.0, 100 - (card + 35)) / 100.0);

    animateTo('cmin', costPerMinute, 300, (val) => {
        document.getElementById('rx_cmin').innerText = 'R$ ' + val.toFixed(4).replace('.', ',');
    });

    animateTo('chora', costPerHour, 300, (val) => {
        document.getElementById('rx_chora').innerText = 'R$ ' + val.toFixed(2).replace('.', ',');
    });

    animateTo('hours', monthlyProductiveHours, 300, (val) => {
        document.getElementById('rx_hours').innerText = val.toFixed(1).replace('.', ',') + ' horas';
    });

    animateTo('breakeven', breakEven, 300, (val) => {
        document.getElementById('rx_breakeven').innerText = 'R$ ' + val.toFixed(2).replace('.', ',');
    });

    document.getElementById('rx_hours_desc').innerText = `${totalTeamWeeklyHours.toFixed(1)} horas por semana (${totalStaff} ${totalStaff > 1 ? 'pessoas' : 'pessoa'})`;
}

document.querySelectorAll('#be-profile-form input').forEach(el => {
    el.addEventListener('input', beRecalcLive);
});

document.getElementById('be_fixed').addEventListener('input', beRecalcLive);

document.addEventListener('DOMContentLoaded', beRecalcLive);

document.getElementById('be-profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = jQuery(this).serialize();
    jQuery.post(beSettings.ajaxUrl, data + '&action=be_save_profile&nonce=' + beSettings.nonce, function(res) {
        if (res.success) {
            alert('Configurações salvas e motor calibrado!');
        } else {
            alert('Erro ao salvar: ' + (res.data?.message || 'Tente novamente.'));
        }
    });
});
</script>