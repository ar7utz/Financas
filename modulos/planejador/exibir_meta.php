<?php
session_start();
include_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Recuperar o ID da meta a partir da URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p class='text-center text-red-500'>Meta não encontrada.</p>";
    exit;
}

$meta_id = $_GET['id'];

// Buscar a meta específica do usuário
$sql = "SELECT * FROM planejador WHERE usuario_id = ? AND id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $usuario_id, $meta_id);
$stmt->execute();
$result = $stmt->get_result();
$meta = $result->fetch_assoc();

if (!$meta) {
    echo "<p class='text-center text-red-500'>Meta não encontrada.</p>";
    exit;
}

$preco_meta = $meta['preco_meta'];
$capital = $meta['capital'];
$quanto_quero_pagar_mes = $meta['quanto_quero_pagar_mes'];
$quanto_tempo_quero_pagar = $meta['quanto_tempo_quero_pagar']; // Certifique-se que este campo existe no banco

// 1. Quanto precisa investir por mês para pagar no tempo desejado
if ($quanto_tempo_quero_pagar > 0) {
    $valor_necessario_por_mes = ($preco_meta - $capital) / $quanto_tempo_quero_pagar;
    $valor_necessario_por_mes = $valor_necessario_por_mes > 0 ? $valor_necessario_por_mes : 0;
} else {
    $valor_necessario_por_mes = null;
}

// 2. Quanto tempo levaria pagando o valor que quer investir por mês
if ($quanto_quero_pagar_mes > 0) {
    $meses_necessarios = ceil(($preco_meta - $capital) / $quanto_quero_pagar_mes);
} else {
    $meses_necessarios = 'Indefinido (defina um valor para investir mensalmente)';
}

// Formatação do tempo
if (is_numeric($meses_necessarios)) {
    $anos = floor($meses_necessarios / 12);
    $meses_restantes = $meses_necessarios % 12;
    $tempo_formatado = '';
    if ($anos > 0) {
        $tempo_formatado .= $anos . ' ano' . ($anos > 1 ? 's' : '');
    }
    if ($anos > 0 && $meses_restantes > 0) {
        $tempo_formatado .= ' e ';
    }
    if ($meses_restantes > 0) {
        $tempo_formatado .= $meses_restantes . ' meses';
    }
    if ($tempo_formatado === '') {
        $tempo_formatado = '0 meses';
    }
    $tempo_formatado .= " ($meses_necessarios meses)";
} else {
    $tempo_formatado = $meses_necessarios;
}

// Dicas para acelerar o objetivo
$dicas = [
    "Aumente seu aporte mensal investindo uma terço do seu salário.",
    "Evite gastos desnecessários e redirecione esse valor para o investimento.",
    "Escolha investimentos com maior rentabilidade para acelerar os ganhos.",
    "Considere fontes de renda extra para aumentar o capital investido.",
];

// Opções de investimentos em títulos públicos
$investimentos = [
    "Tesouro Selic" => "Ideal para quem quer segurança e liquidez diária.",
    "Tesouro Prefixado" => "Indicado para quem deseja garantir uma taxa de retorno fixa.",
    "Tesouro IPCA+" => "Protege contra a inflação e gera rendimentos acima do IPCA.",
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">

    <link rel="stylesheet" href="../../node_modules/toastify-js/src/toastify.css">
    <script src="../../node_modules/toastify-js/src/toastify.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">
    <title>Finstash - Meta - <?php echo htmlspecialchars($meta['razao']); ?></title>
</head>
<body class="bg-gray-100">
    <?php require_once '../../assets/templates/navbar.php'; ?>

    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6 text-center">Sua Meta Financeira: <span class="text-blue-600"><?php echo htmlspecialchars($meta['razao']); ?></span></h1>

        <!-- Informações da Meta -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold mb-4">Detalhes da Meta</h2>
                <a class="bg-slate-400 w-40 p-2 rounded-md" href="./editar_meta.php?id=<?php echo $meta_id; ?>"><button>Adicionar movimentação</button></a>
            </div>

            <div class="flex flex-wrap gap-4 mt-4 items-center align-middle justify-center text-center">
                <p id="weatherGlass" class="flex flex-col w-52 h-28 items-center justify-center shadow-lg bg-white rounded-md p-4">
                    <strong>Valor da Meta:</strong>
                    <span>R$ <?php echo number_format($preco_meta, 2, ',', '.'); ?></span>
                </p>
                <p id="weatherGlass1" class="flex flex-col w-52 h-28 items-center justify-center shadow-lg bg-white rounded-md p-4">
                    <strong>Valor de Entrada:</strong>
                    <span>R$ <?php echo number_format($capital, 2, ',', '.'); ?></span>
                </p>
                <p id="weatherGlass2" class="flex flex-col w-52 h-28 items-center justify-center shadow-lg bg-white rounded-md p-4">
                    <strong>Investimento Mensal Desejado:</strong>
                    <span>R$ <?php echo number_format($quanto_quero_pagar_mes, 2, ',', '.'); ?></span>
                </p>
                <p id="weatherGlass3" class="flex flex-col w-52 h-28 items-center justify-center shadow-lg bg-white rounded-md p-4">
                    <strong>Tempo que deseja pagar:</strong>
                    <span><?php echo is_numeric($quanto_tempo_quero_pagar) ? $quanto_tempo_quero_pagar . ' meses' : 'Não informado'; ?></span>
                </p>
            </div>

            <div class="flex flex-wrap gap-4 mt-4 items-center align-middle justify-center text-center">
                <p id="weatherGlass4" class="flex flex-col w-52 h-28 items-center justify-center shadow-lg bg-white rounded-md p-2">
                    <strong>Pagando o valor mensal desejado, você levaria:</strong>
                    <span><?php echo $tempo_formatado; ?></span>
                </p>
                <p id="weatherGlass5" class="flex flex-col w-52 h-28 items-center justify-center shadow-lg bg-white rounded-md p-8">
                    <strong>Você precisaria investir por mês:</strong>
                    <span>
                        <?php
                        if ($valor_necessario_por_mes !== null) {
                            echo 'R$ ' . number_format($valor_necessario_por_mes, 2, ',', '.');
                        } else {
                            echo 'Informe o tempo desejado para calcular.';
                        }
                        ?>
                        <br>
                        para pagar em<strong class="ml-2"><?php echo is_numeric($quanto_tempo_quero_pagar) ? $quanto_tempo_quero_pagar . ' meses' : 'Não informado'; ?></strong>
                    </span>
                </p>
            </div>
        </div>

        <!-- Dicas para Acelerar -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Dicas para Acelerar</h2>
            <ul class="list-disc ml-5">
                <?php foreach ($dicas as $dica): ?>
                    <li><?php echo $dica; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Informações de Mercado -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Opções e Dicas de Investimento</h2>
            <ul class="list-disc ml-5">
                <?php foreach ($investimentos as $titulo => $descricao): ?>
                    <li><strong><?php echo $titulo; ?>:</strong> <?php echo $descricao; ?></li>
                <?php endforeach; ?>
            </ul>

            <!-- Informações de Mercado da API Apple -->
            <div id="marketData" class="mt-6">
                <h3 class="text-lg font-bold mb-2">Informações de Mercado</h3>
                <p class="text-gray-600">Carregando dados de mercado...</p>
            </div>

            <!-- IPCA -->
            <div id="ipcaData" class="mt-8">
                <h3 class="text-lg font-bold mb-2">Índice de Preços ao Consumidor Amplo (IPCA)</h3>
                <canvas id="ipcaChart" height="60"></canvas>
                <p id="ipcaAtual" class="mt-2 text-gray-700"></p>
            </div>

            <!-- Títulos Tesouro IPCA+ -->
            <div id="ipcaTitulos" class="mt-4 bg-gray-50 p-4 rounded shadow">
                <h4 class="text-md font-bold mb-2">Títulos Tesouro IPCA+</h4>
                <div id="listaIpcaTitulos" class="text-sm text-gray-800">Carregando títulos IPCA...</div>
            </div>

            <!-- Bitcoin -->
            <div id="bitcoinData" class="mt-8">
                <h3 class="text-lg font-bold mb-2">Preço do Bitcoin (BTC)</h3>
                <canvas id="bitcoinChart" height="60"></canvas>
                <p id="bitcoinAtual" class="mt-2 text-gray-700"></p>
            </div>

            <!-- Cotação do Dólar -->
            <div id="dolarData" class="mt-8">
                <h3 class="text-lg font-bold mb-2">Cotação do Dólar (USD/BRL)</h3>
                <canvas id="dolarChart" height="60"></canvas>
                <p id="dolarAtual" class="mt-2 text-gray-700"></p>
            </div>

            <!-- Taxa Selic -->
            <div id="selicData" class="mt-8">
                <h3 class="text-lg font-bold mb-2">Taxa de Juros Selic</h3>
                <canvas id="selicChart" height="60"></canvas>
                <p id="selicAtual" class="mt-2 text-gray-700"></p>
            </div>

        </div>

        <!-- Modal de Confirmação de Exclusão -->
        <div id="modalConfirmarExclusaoMov" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-md text-center">
                <p class="mb-4">Tem certeza de que deseja excluir esta movimentação?</p>
                <div class="flex justify-center space-x-4">
                    <button id="confirmarExcluirMov" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Confirmar</button>
                    <button id="cancelarExcluirMov" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md mt-2">
            <div class="bg-white p-6 rounded-lg shadow-md mt-2 cursor-pointer">
                <div class="flex justify-between items-center" onclick="toggleMovimentacoes()">
                    <h1 class="text-center font-bold text-lg mb-2 flex-1">
                        Histórico de movimentações da meta: <span class="text-blue-600"><?php echo htmlspecialchars($meta['razao']); ?></span>
                    </h1>
                    <span id="iconeSeta" class="ml-4 text-2xl transition-transform duration-200"><i class="fa fa-chevron-down"></i></span>
                </div>
                <div id="movimentacoesContainer" class="flex flex-col mt-4 hidden">
                    <?php
                    $sql = "SELECT * FROM movimentacoes WHERE usuario_id = ? AND meta_id = ? ORDER BY data DESC, hora DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ii', $usuario_id, $meta_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $temMovimentacao = false;
                    while ($row = $result->fetch_assoc()) {
                        $temMovimentacao = true;
                        echo "<div class='mb-2 p-2 border-b cursor-pointer bg-slate-400 border-gray-300 rounded-md hover:bg-slate-500 transition-colors duration-200'>";
                        echo "<span class='tipo'>{$row['tipo']}</span> - ";
                        echo "<span class='descricao'>{$row['descricao']} - </span> ";
                        echo "<span class='valor'>R$ " . number_format($row['valor'], 2, ',', '.') . "</span> - ";
                        echo "<span class='data'>{$row['data']} - </span>";
                        echo "<span class='hora'>{$row['hora']}</span> ";
                        echo "<span class='excluir_mov' data-id='{$row['id']}' onclick='abrirModalExcluirMov({$row['id']})' style='float:right; cursor:pointer;' title='Excluir'><i class='fa fa-trash text-red-500 hover:text-red-700'></i></span> ";
                        echo "</div>";
                    }
                    if (!$temMovimentacao) {
                        echo "<p class='text-gray-600 text-center'>Nenhuma movimentação registrada.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($_GET['mensagem']) && $_GET['mensagem'] === 'excluido'): ?>
        <script src="../../node_modules/toastify-js/src/toastify.js"></script>
        <script>
        Toastify({
            text: "Movimentação excluída com sucesso!",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#16a34a",
            stopOnFocus: true
        }).showToast();
        </script>
        <?php endif; ?>

    <script>
        
        async function fetchMarketData() {
            const apiKey = 'qPI2YVLHqBzBCd4A44kTak0IBkGVFyea'; 
            const url = `https://api.polygon.io/v2/aggs/ticker/AAPL/prev?apiKey=${apiKey}`; //Exemplo com o ticker AAPL (Apple)

            try {
                const response = await fetch(url);
                const data = await response.json();

                if (data && data.results && data.results.length > 0) {
                    const marketData = data.results[0];
                    document.getElementById('marketData').innerHTML = `
                        <p><strong>Ticker:</strong> AAPL</p>
                        <p><strong>Preço de Fechamento:</strong> $${marketData.c}</p>
                        <p><strong>Alta do Dia:</strong> $${marketData.h}</p>
                        <p><strong>Baixa do Dia:</strong> $${marketData.l}</p>
                        <p><strong>Volume:</strong> ${marketData.v}</p>
                    `;
                } else {
                    document.getElementById('marketData').innerHTML = '<p class="text-gray-600">Nenhum dado de mercado disponível.</p>';
                }
            } catch (error) {
                console.error('Erro ao buscar dados de mercado:', error);
                document.getElementById('marketData').innerHTML = '<p class="text-red-500">Erro ao carregar dados de mercado.</p>';
            }
        }

        
        fetchMarketData();
    </script>

    <script>//Funções para abrir e fechar o modal de confirmação de exclusão 
        let idMovimentacaoParaExcluir = null;

        // Abrir modal ao clicar no ícone de lixo
        function abrirModalExcluirMov(id) {
            idMovimentacaoParaExcluir = id;
            document.getElementById('modalConfirmarExclusaoMov').classList.remove('hidden');
        }

        // Cancelar exclusão
        document.getElementById('cancelarExcluirMov').addEventListener('click', function() {
            idMovimentacaoParaExcluir = null;
            document.getElementById('modalConfirmarExclusaoMov').classList.add('hidden');
        });

        // Confirmar exclusão
        document.getElementById('confirmarExcluirMov').addEventListener('click', function() {
            if (idMovimentacaoParaExcluir) {
                window.location.href = `../planejador/excluir_movimentacao.php?id=${idMovimentacaoParaExcluir}&meta_id=<?php echo $meta_id; ?>`;
            }
        });
        </script>

    
    <script>
        function toggleMovimentacoes() {
            const container = document.getElementById('movimentacoesContainer');
            const icone = document.getElementById('iconeSeta');
            container.classList.toggle('hidden');
            icone.classList.toggle('rotate-180');
        }
    
    </script>

    <script>
    // Cotação do Dólar diária
    fetch('https://api.bcb.gov.br/dados/serie/bcdata.sgs.1/dados?formato=json&dataInicial=01/01/2025')
    .then(res => res.json())
    .then(data => {
        // Pega os últimos 30 dias
        const ultimos = data.slice(-30);
        const labels = ultimos.map(d => d.data);
        const valores = ultimos.map(d => parseFloat(d.valor.replace(',', '.')));
        document.getElementById('dolarAtual').innerText = 'Última cotação: R$ ' + valores[valores.length-1].toFixed(2);

        new Chart(document.getElementById('dolarChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Dólar (R$)',
                    data: valores,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.1)',
                    fill: true,
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    title: {
                        display: true,
                        text: 'Cotação diária do Dólar (últimos 30 dias)'
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Dia',
                            font: { size: 14 }
                        },
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 10,
                            font: { size: 12 }
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Valor (R$)',
                            font: { size: 14 }
                        },
                        ticks: {
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    });

    // Taxa Selic anual (acumulada no mês, mas exibindo como anual)
    fetch('https://api.bcb.gov.br/dados/serie/bcdata.sgs.4389/dados?formato=json&dataInicial=01/01/2025')
        .then(res => res.json())
        .then(data => {
            const ultimos = data.slice(-30);
            const labels = ultimos.map(d => d.data);
            const valores = ultimos.map(d => parseFloat(d.valor.replace(',', '.')));
            document.getElementById('selicAtual').innerText = 'Última taxa anual: ' + valores[valores.length-1].toFixed(2) + '% a.a.';
        
            new Chart(document.getElementById('selicChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Selic acumulada no mês (%)',
                        data: valores,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.1)',
                        fill: true,
                        tension: 0.2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true },
                        title: {
                            display: true,
                            text: 'Taxa Selic acumulada no mês (últimos 30 meses)'
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Data',
                                font: { size: 14 }
                            },
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 10,
                                font: { size: 12 }
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Taxa (%)',
                                font: { size: 14 }
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        });

    // IPCA mensal
    fetch('https://api.bcb.gov.br/dados/serie/bcdata.sgs.10844/dados?formato=json&dataInicial=01/01/2018')
    .then(res => res.json())
    .then(data => {
        const ultimos = data.slice(-30);
        const labels = ultimos.map(d => d.data);
        const valores = ultimos.map(d => parseFloat(d.valor.replace(',', '.')));
        document.getElementById('ipcaAtual').innerText = 'Último IPCA: ' + valores[valores.length-1].toFixed(2) + '%';

        new Chart(document.getElementById('ipcaChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'IPCA (%)',
                    data: valores,
                    borderColor: '#eab308',
                    backgroundColor: 'rgba(234,179,8,0.1)',
                    fill: true,
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    title: {
                        display: true,
                        text: 'IPCA mensal (últimos 30 meses)'
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Data',
                            font: { size: 14 }
                        },
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 10,
                            font: { size: 12 }
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'IPCA (%)',
                            font: { size: 14 }
                        },
                        ticks: {
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    });

    // Preço do Bitcoin (últimos 30 dias) usando CoinGecko
    fetch('https://api.coingecko.com/api/v3/coins/bitcoin/market_chart?vs_currency=brl&days=30')
    .then(res => res.json())
    .then(data => {
        const prices = data.prices; // [timestamp, price]
        const labels = prices.map(p => {
            const date = new Date(p[0]);
            return date.toLocaleDateString('pt-BR');
        });
        const valores = prices.map(p => p[1]);
        document.getElementById('bitcoinAtual').innerText = 'Último preço: R$ ' + valores[valores.length-1].toLocaleString('pt-BR', {minimumFractionDigits: 2});

        new Chart(document.getElementById('bitcoinChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Bitcoin (R$)',
                    data: valores,
                    borderColor: '#f7931a',
                    backgroundColor: 'rgba(247,147,26,0.1)',
                    fill: true,
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    title: {
                        display: true,
                        text: 'Preço do Bitcoin (últimos 30 dias)'
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Data',
                            font: { size: 14 }
                        },
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 10,
                            font: { size: 12 }
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Preço (R$)',
                            font: { size: 14 }
                        },
                        ticks: {
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    });

fetch('./proxy_tesouro_ipca.php')
    .then(res => res.json())
    .then(data => {
        
        let titulos = [];
        if (data && Array.isArray(data.response)) {
            titulos = data.response;
        } else if (Array.isArray(data)) {
            titulos = data;
        } else if (data.bondData) {
            titulos = data.bondData;
        }

        if (!titulos || titulos.length === 0) {
            document.getElementById('listaIpcaTitulos').innerHTML = 'Nenhum título encontrado no momento.';
            return;
        }

        let html = '<ul class="list-disc ml-5">';
        titulos.forEach(titulo => {
            html += `<li>
                <strong>${titulo.bondName}</strong> (${titulo.maturityDate})<br>
                <span>Tipo: ${titulo.bondType}</span><br>
                <span>Taxa Indicativa: ${titulo.interestRate}% a.a.</span><br>
                <span>Preço Unitário: R$ ${Number(titulo.unitPrice).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
            </li>`;
        });
        html += '</ul>';
        document.getElementById('listaIpcaTitulos').innerHTML = html;
    })
    .catch((err) => {
        document.getElementById('listaIpcaTitulos').innerHTML = '<span class="text-red-500">Erro ao carregar títulos: ' + err.message + '</span>';
        console.error('Erro ao buscar títulos:', err);
    });
    </script>

    <script>
        function addGlassHoverEffect(divId, corBase = '#FFD600', corFundo = 'rgba(255,255,255,0.15)') {
            const div = document.getElementById(divId);
            if (!div) return;
        
            div.addEventListener('mousemove', function(e) {
                const rect = div.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                div.style.background = `
                    radial-gradient(circle at ${x}px ${y}px, ${corBase}55 0%, ${corBase}22 40%, ${corFundo} 100%)
                `;
                div.style.transition = 'background 0.2s';
            });
        
            div.addEventListener('mouseleave', function() {
                div.style.background = '';
            });
        }

        // Aplique nas suas divs
        window.addEventListener('DOMContentLoaded', function() {
            addGlassHoverEffect('weatherGlass', '#1133A6', 'rgba(255,255,255,0.15)');
            addGlassHoverEffect('weatherGlass1', '#1133A6', 'rgba(255,255,255,0.15)');
            addGlassHoverEffect('weatherGlass2', '#1133A6', 'rgba(255,255,255,0.15)');
            addGlassHoverEffect('weatherGlass3', '#1133A6', 'rgba(255,255,255,0.15)');
            addGlassHoverEffect('weatherGlass4', '#1133A6', 'rgba(255,255,255,0.15)');
            addGlassHoverEffect('weatherGlass5', '#1133A6', 'rgba(255,255,255,0.15)');
        });
    </script>

</body>
</html>
