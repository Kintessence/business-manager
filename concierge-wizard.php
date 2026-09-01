<?php
if (!defined('ABSPATH')) exit;
/** @var array $profile */
?>
<div class="wrap be-wrap">
    <div class="be-card" id="be-wizard-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <span class="be-badge">Porteiro Virtual • Calibração</span>
                <h1 style="font-size: 22px; margin: 6px 0 0; font-weight: 800;">Vamos calibrar o motor da sua empresa</h1>
            </div>
            <span style="font-size: 13px; font-weight: 700; color: var(--be-text-muted);" id="be-step-counter">Passo 1 de 5</span>
        </div>

        <div id="be-step-1" class="be-step-pane">
            <h2 style="font-size: 17px; margin-bottom: 8px;">1. Qual é o nicho principal da sua operação?</h2>
            <p style="color: var(--be-text-muted); margin-bottom: 16px;">Isso ajusta automaticamente as unidades de medida, termos e benchmarks de margem.</p>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="be-pill-btn" onclick="selectNiche('gastronomy')">🍰 Confeitaria & Gastronomia</button>
                <button type="button" class="be-pill-btn" onclick="selectNiche('crafts')">🎨 Artesanato & Ateliê</button>
                <button type="button" class="be-pill-btn" onclick="selectNiche('services')">💼 Prestação de Serviços</button>
            </div>
        </div>

        <div id="be-step-2" class="be-step-pane" style="display: none;">
            <h2 style="font-size: 17px; margin-bottom: 8px;">2. Quanto você precisa retirar por mês (Pró-labore)?</h2>
            <p style="color: var(--be-text-muted); margin-bottom: 12px;">Seu trabalho braçal e de gestão deve ser remunerado antes de calcular o lucro.</p>
            <input type="number" id="be_salary" value="<?php echo esc_attr($profile['salary']); ?>" style="padding: 10px; font-size: 16px; border-radius: 6px; border: 1px solid var(--be-border); width: 220px;" step="100">
            <div style="display: flex; gap: 8px; margin-top: 10px;">
                <button type="button" class="be-pill-btn" onclick="setVal('be_salary', 2000)">R$ 2.000</button>
                <button type="button" class="be-pill-btn" onclick="setVal('be_salary', 3000)">R$ 3.000</button>
                <button type="button" class="be-pill-btn" onclick="setVal('be_salary', 5000)">R$ 5.000</button>
            </div>
            <div class="be-concierge-hint" id="hint-step-2">
                💡 <strong>Dica do Gestor:</strong> A maioria das confeiteiras artesanais inicia com um pró-labore entre R$ 2.500 e R$ 3.500.
            </div>
        </div>

        <div id="be-step-3" class="be-step-pane" style="display: none;">
            <h2 style="font-size: 17px; margin-bottom: 8px;">3. Estrutura de Custos Fixos Mensais (R$)</h2>
            <p style="color: var(--be-text-muted); margin-bottom: 12px;">Contas que chegam vendendo ou não (Luz, Gás, MEI, Internet, Desgaste de Equipamento).</p>
            <input type="number" id="be_fixed" value="<?php echo esc_attr($profile['fixed_costs']); ?>" style="padding: 10px; font-size: 16px; border-radius: 6px; border: 1px solid var(--be-border); width: 220px;" step="50">
            <div style="display: flex; gap: 8px; margin-top: 10px;">
                <button type="button" class="be-pill-btn" onclick="setVal('be_fixed', 450)">R$ 450 (Produção Caseira)</button>
                <button type="button" class="be-pill-btn" onclick="setVal('be_fixed', 900)">R$ 900 (Ateliê Dedicado)</button>
            </div>
            <div class="be-concierge-hint" id="hint-step-3">
                💡 <strong>Atenção:</strong> Se você cozinha em casa, inclua pelo menos R$ 150 de fração de energia e 1 botijão de gás (R$ 120).
            </div>
        </div>

        <div id="be-step-4" class="be-step-pane" style="display: none;">
            <h2 style="font-size: 17px; margin-bottom: 8px;">4. Ritmo Produtivo Real</h2>
            <p style="color: var(--be-text-muted); margin-bottom: 12px;">Quantas horas efetivas de mão na massa e dias de produção por semana?</p>
            <div style="display: flex; gap: 15px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700;">Horas / Dia</label>
                    <input type="number" id="be_hours" value="<?php echo esc_attr($profile['hours_day']); ?>" style="padding: 8px; border: 1px solid var(--be-border); border-radius: 6px; width: 100px;" step="0.5">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700;">Dias / Semana</label>
                    <input type="number" id="be_days" value="<?php echo esc_attr($profile['days_week']); ?>" style="padding: 8px; border: 1px solid var(--be-border); border-radius: 6px; width: 100px;" min="1" max="7">
                </div>
            </div>
        </div>

        <div id="be-step-5" class="be-step-pane" style="display: none;">
            <h2 style="font-size: 17px; margin-bottom: 8px;">5. Margem Líquida Alvo (%)</h2>
            <p style="color: var(--be-text-muted); margin-bottom: 12px;">Lucro limpo para a empresa reinvestir e formar reserva.</p>
            <input type="number" id="be_margin" value="<?php echo esc_attr($profile['margin']); ?>" style="padding: 10px; font-size: 16px; border-radius: 6px; border: 1px solid var(--be-border); width: 120px;" step="1"> %
            <div style="display: flex; gap: 8px; margin-top: 10px;">
                <button type="button" class="be-pill-btn" onclick="setVal('be_margin', 20)">20% (Volume)</button>
                <button type="button" class="be-pill-btn" onclick="setVal('be_margin', 25)">25% (Padrão Ouro)</button>
                <button type="button" class="be-pill-btn" onclick="setVal('be_margin', 35)">35% (Exclusivo)</button>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 30px; border-top: 1px solid var(--be-border); padding-top: 20px;">
            <button type="button" class="be-pill-btn" id="be-prev-btn" onclick="prevStep()" style="display:none;">← Voltar</button>
            <div></div>
            <button type="button" class="be-btn-primary" id="be-next-btn" onclick="nextStep()">Avançar →</button>
        </div>
    </div>

    <div class="be-dark-panel">
        <h3 style="margin: 0 0 4px; font-size: 16px; font-weight: 700;">📊 Raio-X do Motor Operacional (Em Tempo Real)</h3>
        <p style="color: #94a3b8; font-size: 13px; margin: 0 0 10px;">Estes coeficientes alimentam todas as fichas técnicas e precificadores.</p>
        <div class="be-metrics-grid">
            <div class="be-metric-card">
                <div class="val" id="kpi-cmin">R$ 0,4615</div>
                <div class="lbl">Custo por Minuto</div>
            </div>
            <div class="be-metric-card">
                <div class="val" id="kpi-chour">R$ 27,69</div>
                <div class="lbl">Custo da Hora Produtiva</div>
            </div>
            <div class="be-metric-card">
                <div class="val" id="kpi-markup">2.85x</div>
                <div class="lbl">Markup Multiplicador Base</div>
            </div>
            <div class="be-metric-card">
                <div class="val" id="kpi-breakeven">R$ 5.538,00</div>
                <div class="lbl">Ponto de Equilíbrio / Mês</div>
            </div>
        </div>
    </div>
</div>

<script>
let step = 1;
let idleTimer;

function resetIdle() {
    clearTimeout(idleTimer);
    document.querySelectorAll('.be-concierge-hint').forEach(el => el.classList.remove('visible'));
    idleTimer = setTimeout(() => {
        const hint = document.getElementById('hint-step-' + step);
        if (hint) hint.classList.add('visible');
    }, 8000);
}

document.addEventListener('mousemove', resetIdle);
document.addEventListener('keypress', resetIdle);
resetIdle();

function setVal(id, val) {
    document.getElementById(id).value = val;
    recalcMetrics();
}

function selectNiche(niche) {
    nextStep();
}

function recalcMetrics() {
    const salary = parseFloat(document.getElementById('be_salary')?.value || 3000);
    const fixed = parseFloat(document.getElementById('be_fixed')?.value || 600);
    const hours = parseFloat(document.getElementById('be_hours')?.value || 6);
    const days = parseFloat(document.getElementById('be_days')?.value || 5);
    const margin = parseFloat(document.getElementById('be_margin')?.value || 25);

    const monthlyHours = (days * hours * 4.333) * 0.85;
    const totalCost = salary + fixed;
    const cHour = monthlyHours > 0 ? (totalCost / monthlyHours) : 0;
    const cMin = cHour / 60;

    const divisor = Math.max(0.05, (100 - (margin + 6 + 3.5)) / 100);
    const markup = 1 / divisor;
    const breakeven = totalCost / 0.65;

    document.getElementById('kpi-cmin').innerText = 'R$ ' + cMin.toFixed(4).replace('.', ',');
    document.getElementById('kpi-chour').innerText = 'R$ ' + cHour.toFixed(2).replace('.', ',');
    document.getElementById('kpi-markup').innerText = markup.toFixed(2) + 'x';
    document.getElementById('kpi-breakeven').innerText = 'R$ ' + breakeven.toFixed(2).replace('.', ',');
}

function nextStep() {
    if (step < 5) {
        document.getElementById('be-step-' + step).style.display = 'none';
        step++;
        document.getElementById('be-step-' + step).style.display = 'block';
        document.getElementById('be-step-counter').innerText = 'Passo ' + step + ' de 5';
        document.getElementById('be-prev-btn').style.display = 'inline-block';
        if (step === 5) document.getElementById('be-next-btn').innerText = 'Concluir Onboarding 🎉';
        recalcMetrics();
        resetIdle();
    } else {
        alert('Onboarding concluído! Redirecionando para o Dashboard com seus dados calibrados.');
        window.location.href = 'admin.php?page=be-dashboard';
    }
}

function prevStep() {
    if (step > 1) {
        document.getElementById('be-step-' + step).style.display = 'none';
        step--;
        document.getElementById('be-step-' + step).style.display = 'block';
        document.getElementById('be-step-counter').innerText = 'Passo ' + step + ' de 5';
        if (step === 1) document.getElementById('be-prev-btn').style.display = 'none';
        document.getElementById('be-next-btn').innerText = 'Avançar →';
        resetIdle();
    }
}
</script>
