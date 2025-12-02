<?php
include_once __DIR__ . '/../../bd/conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modulos/login/login.php');
    exit;
}

$usuario_id = intval($_SESSION['user_id']);

// tipo (entrada/saida)
$tipo  = isset($_GET['tipo'])  ? $_GET['tipo'] : 'entrada';

// nomes dos meses em Português
$meses_pt = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

// --- Carrega apenas os meses/anos que têm transações do usuário ---
$available_months = []; // [year => [month,...]]
$available_years = [];
$sqlAvailable = "SELECT DISTINCT YEAR(`data`) AS y, MONTH(`data`) AS m
                 FROM transacoes
                 WHERE usuario_id = ?
                 ORDER BY y DESC, m DESC";
$stmtAvail = $conn->prepare($sqlAvailable);
if ($stmtAvail) {
    $stmtAvail->bind_param('i', $usuario_id);
    $stmtAvail->execute();
    $resAvail = $stmtAvail->get_result();
    while ($r = $resAvail->fetch_assoc()) {
        $y = intval($r['y']); $m = intval($r['m']);
        if (!isset($available_months[$y])) $available_months[$y] = [];
        if (!in_array($m, $available_months[$y], true)) $available_months[$y][] = $m;
        if (!in_array($y, $available_years, true)) $available_years[] = $y;
    }
    $stmtAvail->close();
}

// fallback quando não houver transações
if (empty($available_years)) {
    $curY = intval(date('Y'));
    $curM = intval(date('n'));
    $available_years = [$curY];
    $available_months[$curY] = [$curM];
}

// define ano e mês selecionados (garantindo que sejam válidos dentro dos disponíveis)
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : $available_years[0];
if (!in_array($selected_year, $available_years, true)) $selected_year = $available_years[0];

if (!isset($available_months[$selected_year]) || empty($available_months[$selected_year])) {
    $selected_year = $available_years[0];
}

$selected_month = isset($_GET['month']) ? intval($_GET['month']) : $available_months[$selected_year][0];
if (!in_array($selected_month, $available_months[$selected_year], true)) $selected_month = $available_months[$selected_year][0];

$month = $selected_month;
$year  = $selected_year;

// gerar semanas do mês (1-7, 8-14, ...)
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$weeks = [];
$startDay = 1;
while ($startDay <= $daysInMonth) {
    $endDay = min($startDay + 6, $daysInMonth);
    $startDate = sprintf('%04d-%02d-%02d', $year, $month, $startDay);
    $endDate   = sprintf('%04d-%02d-%02d', $year, $month, $endDay);
    $weeks[] = [
        'label' => "dia {$startDay} ao dia {$endDay}",
        'start' => $startDate,
        'end'   => $endDate
    ];
    $startDay = $endDay + 1;
}

$monthStart = sprintf('%04d-%02d-01', $year, $month);
$monthEnd   = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

// --- Busca categorias que têm transações no período (independente do campo tipo) ---
// Isso evita dependência do valor de t.tipo que pode variar entre instalações.
$sqlCats = "SELECT DISTINCT c.id, c.nome_categoria
            FROM categoria c
            JOIN transacoes t ON t.categoria_id = c.id
            WHERE t.usuario_id = ? AND DATE(t.data) BETWEEN ? AND ?
            ORDER BY c.nome_categoria ASC";
$stmtCats = $conn->prepare($sqlCats);
$categorias = [];
if ($stmtCats) {
    $stmtCats->bind_param('iss', $usuario_id, $monthStart, $monthEnd);
    $stmtCats->execute();
    $resCats = $stmtCats->get_result();
    $categorias = $resCats ? $resCats->fetch_all(MYSQLI_ASSOC) : [];
    $stmtCats->close();
}

$hasData = !empty($categorias);
if (!$hasData) {
    $categorias = [
        ['id' => 0, 'nome_categoria' => 'Sem transações']
    ];
}

$labels = array_map(fn($w) => $w['label'], $weeks);
$datasets = [];

$valorCast = "(CASE WHEN valor LIKE '%,%' THEN CAST(REPLACE(REPLACE(CAST(valor AS CHAR),'.',''),',','.') AS DECIMAL(15,2)) ELSE CAST(REPLACE(CAST(valor AS CHAR),',','') AS DECIMAL(15,2)) END)";
if ($tipo === 'saida') {
    $sumExprForQuery = "COALESCE(SUM(CASE WHEN {$valorCast} < 0 THEN ABS({$valorCast}) ELSE 0 END),0)";
} else {
    $sumExprForQuery = "COALESCE(SUM(CASE WHEN {$valorCast} > 0 THEN {$valorCast} ELSE 0 END),0)";
}

function randomColor($i) {
    $palette = [
        '#1f77b4','#ff7f0e','#2ca02c','#d62728','#9467bd','#8c564b',
        '#e377c2','#7f7f7f','#bcbd22','#17becf'
    ];
    return $palette[$i % count($palette)];
}

foreach ($categorias as $i => $cat) {
    $values = [];

    $sqlSum = "SELECT {$sumExprForQuery} AS total
               FROM transacoes
               WHERE usuario_id = ? AND categoria_id = ? AND DATE(data) BETWEEN ? AND ?";

    $stmt = $conn->prepare($sqlSum);
    if (!$stmt) {
        // preenche zeros se prepare falhar
        foreach ($weeks as $w) $values[] = 0;
    } else {
        foreach ($weeks as $w) {
            if (intval($cat['id']) === 0) {
                $values[] = 0.0;
                continue;
            }
            $stmt->bind_param('iiss', $usuario_id, $cat['id'], $w['start'], $w['end']);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $total = isset($row['total']) ? floatval($row['total']) : 0.0;
            $values[] = $total;
        }
        $stmt->close();
    }

    // se não houver valores reais e já existiam dados reais, pula série
    $hasValue = array_reduce($values, fn($carry,$v) => $carry || ($v > 0), false);
    if (!$hasValue && $hasData) continue;

    $datasets[] = [
        'label' => $cat['nome_categoria'],
        'data' => $values,
        'fill' => false,
        'borderColor' => randomColor($i),
        'backgroundColor' => randomColor($i),
        'tension' => 0.3
    ];
}

// garante pelo menos uma série zero para evitar erro do Chart.js
if (empty($datasets)) {
    $datasets[] = [
        'label' => 'Sem transações',
        'data' => array_fill(0, count($labels), 0),
        'fill' => false,
        'borderColor' => '#cccccc',
        'backgroundColor' => '#cccccc',
        'tension' => 0.3
    ];
}

// calcula máximo para eixo Y (para sugestão)
$overallMax = 0.0;
foreach ($datasets as $ds) {
    foreach ($ds['data'] as $v) {
        if ($v > $overallMax) $overallMax = $v;
    }
}
if ($overallMax <= 0) $overallMax = 1;
else {
    $magnitude = pow(10, floor(log10($overallMax)));
    $overallMax = ceil($overallMax / $magnitude) * $magnitude;
}

/**
 * AJAX response: retorna labels, datasets e overallMax como JSON quando ?ajax=1
 * assim o JS pode atualizar apenas o gráfico sem recarregar a página.
 */
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'labels' => $labels,
        'datasets' => $datasets,
        'overallMax' => $overallMax
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

?>
<div class="grafico-mes max-w-5xl mx-auto p-4 bg-white rounded-lg shadow-lg mb-6" style="max-width: 920px;">
    <h1 class="text-center font-bold text-2xl mb-2">Gráfico de balanço mensal</h1>
    <div class="flex flex-wrap items-center justify-center gap-3 mb-4">
        <div class="flex items-center gap-2">
            <label for="gm_year" class="text-sm text-gray-700">Ano</label>
            <select id="gm_year" class="form-select border border-gray-300 rounded-md px-3 py-1 bg-white text-sm">
                <?php foreach ($available_years as $y): ?>
                    <option value="<?php echo $y?>" <?php if($y==$year) echo 'selected'?>><?php echo $y?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <label for="gm_month" class="text-sm text-gray-700">Mês</label>
            <select id="gm_month" class="form-select border border-gray-300 rounded-md px-3 py-1 bg-white text-sm">
                <?php
                    $monthsForYear = $available_months[$year] ?? [];
                    rsort($monthsForYear);
                    foreach ($monthsForYear as $m):
                        $mName = $meses_pt[$m] ?? strftime('%B', mktime(0,0,0,$m,1));
                ?>
                    <option value="<?php echo $m?>" <?php if($m==$month) echo 'selected'?>><?php echo $mName?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <label for="gm_tipo" class="text-sm text-gray-700">Tipo</label>
            <select id="gm_tipo" class="form-select border border-gray-300 rounded-md px-3 py-1 bg-white text-sm">
                <option value="entrada" <?php if($tipo==='entrada') echo 'selected'?>>Entrada</option>
                <option value="saida" <?php if($tipo==='saida') echo 'selected'?>>Saída</option>
            </select>
        </div>

        <button id="gm_atualizar" class="hidden ml-2 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-md shadow-sm">
            Atualizar
        </button>
    </div>

    <div class="chart-wrapper bg-white rounded-md p-3" style="height:24rem; box-sizing:border-box;">
        <canvas id="graficoMesCanvas" class="w-full h-full block"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    const initialLabels = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
    const initialDatasets = <?php echo json_encode($datasets, JSON_UNESCAPED_UNICODE); ?>;
    const initialOverallMax = <?php echo json_encode($overallMax); ?>;

    const ctx = document.getElementById('graficoMesCanvas').getContext('2d');
    if (window.__graficoMesInstance) window.__graficoMesInstance.destroy();

    window.__graficoMesInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: initialLabels,
            datasets: initialDatasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 0 }, // evita animações que causem "pulo"
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, usePointStyle: true, padding: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            // preferir valor numérico cru/parced (Chart.js v3+)
                            let raw = context.raw;
                            if (raw === undefined && context.parsed && typeof context.parsed.y !== 'undefined') raw = context.parsed.y;
                            if (raw === undefined) {
                                // fallback: normalizar string "3.000,00" -> "3000.00"
                                const s = String(context.formattedValue || '0').replace(/\./g,'').replace(',', '.');
                                raw = Number(s);
                            }
                            const v = (typeof raw === 'number' && !isNaN(raw)) ? raw : 0;
                            return context.dataset.label + ': R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }
            },
            layout: { padding: { top: 6, right: 8, bottom: 48, left: 8 } },
            scales: {
                x: {
                    ticks: { color: '#374151' },
                    title: { display: false }
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: initialOverallMax,
                    title: { display: true, text: 'Valor (R$)', color: '#374151' },
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 0 });
                        },
                        color: '#374151'
                    }
                }
            }
        }
    });

    // elementos
    const availableMonths = <?php echo json_encode($available_months, JSON_UNESCAPED_UNICODE); ?>;
    const mesesPt = <?php echo json_encode($meses_pt, JSON_UNESCAPED_UNICODE); ?>;
    const selYear = document.getElementById('gm_year');
    const selMonth = document.getElementById('gm_month');
    const selTipo = document.getElementById('gm_tipo');
    const btnAtualizar = document.getElementById('gm_atualizar');

    selYear.addEventListener('change', function(){
        const y = this.value;
        const months = availableMonths[y] || [];
        selMonth.innerHTML = '';
        months.sort((a,b)=>b-a);
        months.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m;
            opt.text = mesesPt[m] || new Date(0, m-1).toLocaleString('pt-BR', { month: 'long' });
            selMonth.appendChild(opt);
        });
    });

    async function atualizarGrafico() {
        const ajaxEndpoint = '/Financas/assets/templates/graficos/grafico_mes.php';
        const url = new URL(ajaxEndpoint, window.location.origin);
        url.searchParams.set('ajax', '1');
        url.searchParams.set('year', selYear.value);
        url.searchParams.set('month', selMonth.value);
        url.searchParams.set('tipo', selTipo.value);

        try {
            const res = await fetch(url.toString(), { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Erro na resposta: ' + res.status);
            const json = await res.json();

            if (window.__graficoMesInstance) {
                window.__graficoMesInstance.data.labels = json.labels;
                window.__graficoMesInstance.data.datasets = json.datasets;
                if (window.__graficoMesInstance.options && window.__graficoMesInstance.options.scales && window.__graficoMesInstance.options.scales.y) {
                    window.__graficoMesInstance.options.scales.y.suggestedMax = json.overallMax;
                }
                // atualiza sem animação para evitar "pulos" de layout
                window.__graficoMesInstance.update('none');
            }
        } catch (err) {
            console.error('Erro ao atualizar gráfico:', err);
        }
    }

    btnAtualizar.addEventListener('click', function(e){
        e.preventDefault();
        atualizarGrafico();
    });

    selTipo.addEventListener('change', atualizarGrafico);
    selMonth.addEventListener('change', atualizarGrafico);
})();
</script>
