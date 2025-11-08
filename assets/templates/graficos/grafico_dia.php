<?php
include_once __DIR__ . '/../../bd/conexao.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<p>Faça login para ver o gráfico.</p>";
    exit;
}

$usuario_id = intval($_SESSION['user_id']);

// nomes dos meses em pt
$meses_pt = [
    1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',
    7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];

// --- meses/anos disponíveis (apenas onde usuário tem transações) ---
$available_months = []; $available_years = [];
$sqlAvailable = "SELECT DISTINCT YEAR(`data`) AS y, MONTH(`data`) AS m
                 FROM transacoes WHERE usuario_id = ? ORDER BY y DESC, m DESC";
if ($stmt = $conn->prepare($sqlAvailable)) {
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $y = intval($r['y']); $m = intval($r['m']);
        $available_months[$y] = $available_months[$y] ?? [];
        if (!in_array($m, $available_months[$y], true)) $available_months[$y][] = $m;
        if (!in_array($y, $available_years, true)) $available_years[] = $y;
    }
    $stmt->close();
}
if (empty($available_years)) {
    $curY = intval(date('Y')); $curM = intval(date('n'));
    $available_years = [$curY];
    $available_months[$curY] = [$curM];
}

// parâmetros de entrada (podem vir via ajax)
// tipo: entrada/saida
$tipo = (isset($_GET['tipo']) && $_GET['tipo'] === 'saida') ? 'saida' : 'entrada';

// ano/mês selecionados (somente nos disponíveis)
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : $available_years[0];
if (!in_array($selected_year, $available_years, true)) $selected_year = $available_years[0];

$monthsForYear = $available_months[$selected_year] ?? [intval(date('n'))];
rsort($monthsForYear);
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : ($monthsForYear[0] ?? intval(date('n')));
if (!in_array($selected_month, $monthsForYear, true)) $selected_month = $monthsForYear[0];

// Agora vamos montar os dias do mês selecionado: 1..N (N = dias do mês)
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year);
$labels = [];
$dayDates = []; // map index -> date YYYY-MM-DD
for ($d = 1; $d <= $daysInMonth; $d++) {
    $labels[] = (string)$d; // mostra 1..31
    $dayDates[] = sprintf('%04d-%02d-%02d', $selected_year, $selected_month, $d);
}
$monthStart = $dayDates[0];
$monthEnd   = $dayDates[count($dayDates)-1];

// expressão robusta para interpretar o campo `valor` (suporta formatos "1.234,56", "1234.56", "R$ 1.234,56", "(1.234,56)")
$valorCast = "(
    CASE
        WHEN TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CAST(valor AS CHAR),'R$',''),' ',''),'(', '-'),')',''), '−','-')) REGEXP ',' 
            THEN CAST(REPLACE(REPLACE(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CAST(valor AS CHAR),'R$',''),' ',''),'(', '-'),')',''), '−','-')) ,'.',''),',','.') AS DECIMAL(15,4))
        ELSE CAST(REPLACE(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CAST(valor AS CHAR),'R$',''),' ',''),'(', '-'),')',''), '−','-')) ,',','') AS DECIMAL(15,4))
    END
)";

// filtragem por tipo usando sinal do valor (entrada >0, saída <0)
if ($tipo === 'saida') {
    $sumExpr = "COALESCE(SUM(CASE WHEN {$valorCast} < 0 THEN ABS({$valorCast}) ELSE 0 END),0)";
} else {
    $sumExpr = "COALESCE(SUM(CASE WHEN {$valorCast} > 0 THEN {$valorCast} ELSE 0 END),0)";
}

// Busca agrupada por categoria e dia (somente mês/ano selecionado)
$sql = "SELECT c.id AS categoria_id, c.nome_categoria, DATE(t.data) AS dia,
               {$sumExpr} AS total
        FROM categoria c
        JOIN transacoes t ON t.categoria_id = c.id
        WHERE t.usuario_id = ? AND DATE(t.data) BETWEEN ? AND ?
        GROUP BY c.id, c.nome_categoria, DATE(t.data)
        ORDER BY c.nome_categoria, dia";
$rows = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('iss', $usuario_id, $monthStart, $monthEnd);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    $stmt->close();
}

// montar mapa categoria -> dia -> total
$map = [];
foreach ($rows as $r) {
    $cid = intval($r['categoria_id']);
    if (!isset($map[$cid])) $map[$cid] = ['nome' => $r['nome_categoria'], 'values' => []];
    $map[$cid]['values'][$r['dia']] = floatval($r['total']);
}

// palette
$palette = ['#1f77b4','#ff7f0e','#2ca02c','#d62728','#9467bd','#8c564b','#e377c2','#7f7f7f','#bcbd22','#17becf','#17a2b8','#007bff'];

// construir datasets: cada categoria => array de N dias (valores 0 se ausente)
$datasets = [];
$i = 0;
foreach ($map as $cid => $data) {
    $vals = []; $hasValue = false;
    foreach ($dayDates as $date) {
        $v = $data['values'][$date] ?? 0.0;
        $vals[] = $v;
        if ($v > 0) $hasValue = true;
    }
    // incluir apenas categorias com algum valor; se quiser sempre mostrar todas, comente a próxima linha
    if (!$hasValue) continue;

    $datasets[] = [
        'label' => $data['nome'],
        'data' => $vals,
        'fill' => false,
        'borderColor' => $palette[$i % count($palette)],
        'backgroundColor' => $palette[$i % count($palette)],
        'cubicInterpolationMode' => ($i % 2 === 0) ? 'default' : 'monotone',
        'tension' => ($i % 3) * 0.25,
        'pointRadius' => 3,
        'pointHoverRadius' => 5
    ];
    $i++;
}

// se não houver categorias com valores, cria placeholder (linha zerada) para evitar erro Chart.js
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

// calcula overallMax para sugerir limite do eixo Y
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

// resposta AJAX para atualizar só o gráfico
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
<div class="mx-auto" style="max-width:920px;">
    <div class="bg-white rounded-lg shadow-lg p-4">
        <h1 class="text-center font-bold text-2xl mb-2">Gráfico de balanço diário</h1>
        <div class="flex flex-wrap items-center justify-center gap-3 mb-4">
            <div class="flex items-center gap-2">
                <label for="gs_year" class="text-sm text-gray-700">Ano</label>
                <select id="gs_year" class="ml-2 block border border-gray-300 rounded-md px-3 py-1 bg-white text-sm">
                    <?php foreach ($available_years as $y): ?>
                        <option value="<?php echo $y?>" <?php if($y==$selected_year) echo 'selected'?>><?php echo $y?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label for="gs_month" class="text-sm text-gray-700">Mês</label>
                <select id="gs_month" class="ml-2 block border border-gray-300 rounded-md px-3 py-1 bg-white text-sm">
                    <?php
                        $monthsForYear = $available_months[$selected_year] ?? [];
                        rsort($monthsForYear);
                        foreach ($monthsForYear as $m):
                    ?>
                        <option value="<?php echo $m?>" <?php if($m==$selected_month) echo 'selected'?>><?php echo $meses_pt[$m] ?? $m?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label for="gs_tipo" class="text-sm text-gray-700">Tipo</label>
                <select id="gs_tipo" class="ml-2 block border border-gray-300 rounded-md px-3 py-1 bg-white text-sm">
                    <option value="entrada" <?php if($tipo==='entrada') echo 'selected'?>>Entrada</option>
                    <option value="saida" <?php if($tipo==='saida') echo 'selected'?>>Saída</option>
                </select>
            </div>

            <div class="hidden ml-auto flex items-center gap-2">
                <button id="gs_refresh" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-md shadow-sm">
                    Atualizar
                </button>
            </div>
        </div>

        <div class="bg-white rounded-md p-3" style="height:480px; box-sizing:border-box;">
            <canvas id="graficoSemanaCanvas" class="w-full h-full block"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    const initialLabels = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
    const initialDatasets = <?php echo json_encode($datasets, JSON_UNESCAPED_UNICODE); ?>;
    const initialOverallMax = <?php echo json_encode($overallMax); ?>;

    const ctx = document.getElementById('graficoSemanaCanvas').getContext('2d');
    if (window.__graficoSemana) window.__graficoSemana.destroy();
    window.__graficoSemana = new Chart(ctx, {
        type: 'line',
        data: { labels: initialLabels, datasets: initialDatasets },
        options: {
            responsive: true,
            maintainAspectRatio: false, // usamos container com altura fixa
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        padding: 12
                    }
                },
                 tooltip: {
                     callbacks: {
                         label: function(context) {
                             const v = Number(context.formattedValue || 0);
                             return context.dataset.label + ': R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                         }
                     }
                 }
             },
             layout: {
                // reserva um padding inferior para legenda (evita quebra de linha que empurra a página)
                padding: { top: 6, right: 8, bottom: 48, left: 8 }
             },
             scales: {
                 x: { title: { display: true, text: 'Dia do mês' } },
                 y: {
                     beginAtZero: true,
                     suggestedMax: initialOverallMax,
                     title: { display: true, text: 'Valor (R$)' },
                     ticks: { callback: v => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 0 }) }
                 }
             }
         }
     });

    const selYear = document.getElementById('gs_year');
    const selMonth = document.getElementById('gs_month');
    const selTipo = document.getElementById('gs_tipo');
    const btnRefresh = document.getElementById('gs_refresh');

    const availableMonths = <?php echo json_encode($available_months, JSON_UNESCAPED_UNICODE); ?>;
    const mesesPt = <?php echo json_encode($meses_pt, JSON_UNESCAPED_UNICODE); ?>;

    selYear.addEventListener('change', function(){
        const y = this.value;
        const months = availableMonths[y] || [];
        selMonth.innerHTML = '';
        months.sort((a,b)=>b-a);
        months.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m;
            opt.text = mesesPt[m] || m;
            selMonth.appendChild(opt);
        });
    });

    async function atualizarGrafico() {
        const ajaxEndpoint = '/Financas/assets/templates/graficos/grafico_dia.php';
        const url = new URL(ajaxEndpoint, window.location.origin);
        url.searchParams.set('ajax','1');
        url.searchParams.set('year', selYear.value);
        url.searchParams.set('month', selMonth.value);
        url.searchParams.set('tipo', selTipo.value);

        try {
            const res = await fetch(url.toString(), { credentials: 'same-origin' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();
            if (window.__graficoSemana) {
                window.__graficoSemana.data.labels = json.labels;
                window.__graficoSemana.data.datasets = json.datasets;
                if (window.__graficoSemana.options && window.__graficoSemana.options.scales && window.__graficoSemana.options.scales.y) {
                    window.__graficoSemana.options.scales.y.suggestedMax = json.overallMax;
                }
                window.__graficoSemana.update('none');
             }
        } catch (err) {
            console.error('Erro ao atualizar gráfico:', err);
        }
    }

    btnRefresh.addEventListener('click', function(e){ e.preventDefault(); atualizarGrafico(); });
    selMonth.addEventListener('change', atualizarGrafico);
    selTipo.addEventListener('change', atualizarGrafico);

})();
</script>