<?php
session_start();
include_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$usuario_id = $_SESSION['user_id'];

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
$quanto_tempo_quero_pagar = $meta['quanto_tempo_quero_pagar'];

if ($quanto_tempo_quero_pagar > 0) {
    $valor_necessario_por_mes = ($preco_meta - $capital) / $quanto_tempo_quero_pagar;
    $valor_necessario_por_mes = $valor_necessario_por_mes > 0 ? $valor_necessario_por_mes : 0;
} else {
    $valor_necessario_por_mes = null;
}

if ($quanto_quero_pagar_mes > 0) {
    $meses_necessarios = ceil(($preco_meta - $capital) / $quanto_quero_pagar_mes);
} else {
    $meses_necessarios = 'Indefinido (defina um valor para investir mensalmente)';
}

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

// -- Buscar e normalizar o perfil do usuário (mesma lógica usada em usuario/perfil.php) --
$perfil_text = null;
$sqlUser = "SELECT perfil_financeiro FROM `user` WHERE id = ? LIMIT 1";
if ($stmtUser = $conn->prepare($sqlUser)) {
    $stmtUser->bind_param('i', $usuario_id);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    $userRow = $resUser->fetch_assoc();
    $stmtUser->close();

    if ($userRow && isset($userRow['perfil_financeiro']) && $userRow['perfil_financeiro'] !== null && $userRow['perfil_financeiro'] !== '') {
        $pf = $userRow['perfil_financeiro'];
        if (is_numeric($pf)) {
            $sqlResp = "SELECT perfil FROM respostas_perfil WHERE id = ? LIMIT 1";
            if ($stmtResp = $conn->prepare($sqlResp)) {
                $stmtResp->bind_param('i', $pf);
                $stmtResp->execute();
                $resResp = $stmtResp->get_result();
                $rowResp = $resResp->fetch_assoc();
                $stmtResp->close();
                $perfil_text = $rowResp['perfil'] ?? null;
            }
        } else {
            $perfil_text = trim($pf) !== '' ? $pf : null;
        }
    }
}

// Normaliza para as três categorias esperadas (Conservador / Moderado / Agressivo)
if (!empty($perfil_text)) {
    $pfn = strtolower($perfil_text);
    if (str_contains($pfn, 'conserv')) $perfil_text = 'Conservador';
    elseif (str_contains($pfn, 'moder')) $perfil_text = 'Moderado';
    elseif (str_contains($pfn, 'agress') || str_contains($pfn, 'arroj')) $perfil_text = 'Agressivo';
    else $perfil_text = ucfirst($perfil_text);
}

$perfil_normalizado = is_string($perfil_text) ? strtolower($perfil_text) : null;

// Carrega sugestões conforme perfil (garante arquivo correto)
$sugestoes = [];

// mapa de perfil => arquivo
$mapSugestoes = [
    'conservador' => __DIR__ . '/../Sugestor/perfilConservador.php',
    'moderado'    => __DIR__ . '/../Sugestor/perfilModerado.php',
    'agressivo'   => __DIR__ . '/../Sugestor/perfilAgressivo.php',
    'arrojado'    => __DIR__ . '/../Sugestor/perfilAgressivo.php'
];

if ($perfil_normalizado) {
    // tenta encontrar o arquivo pelo nome do perfil
    $arquivo = null;
    foreach ($mapSugestoes as $key => $path) {
        if (str_contains($perfil_normalizado, $key)) {
            $arquivo = $path;
            break;
        }
    }
    // fallback se não encontrou por contains, tenta chave exata
    if ($arquivo === null && isset($mapSugestoes[$perfil_normalizado])) {
        $arquivo = $mapSugestoes[$perfil_normalizado];
    }

    // inclui o arquivo se existir, senão deixa vazio e registra no log de erro (opcional)
    if ($arquivo && file_exists($arquivo)) {
        $sugestoes = include $arquivo;
        if (!is_array($sugestoes)) $sugestoes = [];
    } else {
        // fallback seguro: tenta carregar conservador se existir
        $fallback = __DIR__ . '/../Sugestor/perfilConservador.php';
        if (file_exists($fallback)) {
            $sugestoes = include $fallback;
            if (!is_array($sugestoes)) $sugestoes = [];
        } else {
            $sugestoes = [];
        }
    }
} else {
    // usuário sem perfil definido -> não carregar sugestões, será exibida mensagem na view
    $sugestoes = [];
}

// substitui o bloco que buscava uma movimentação isolada pelo somatório de aplicações
$sqlMovimentacaoSum = "SELECT COALESCE(SUM(valor),0) AS total_aplicado FROM movimentacoes WHERE usuario_id = ? AND meta_id = ? AND tipo = 'aplicacao'";
$stmtMovSum = $conn->prepare($sqlMovimentacaoSum);
$stmtMovSum->bind_param('ii', $usuario_id, $meta_id);
$stmtMovSum->execute();
$resMovSum = $stmtMovSum->get_result();
$rowMovSum = $resMovSum->fetch_assoc();
$stmtMovSum->close();

$total_aplicado = floatval($rowMovSum['total_aplicado']); // Aporte total já feito pelo usuário nesta meta

if ($quanto_tempo_quero_pagar > 0) {
    $valor_restante = max($preco_meta - $capital - $total_aplicado, 0);
    $valor_necessario_por_mes = $valor_restante / $quanto_tempo_quero_pagar;
    $valor_necessario_por_mes = $valor_necessario_por_mes > 0 ? $valor_necessario_por_mes : 0;
} else {
    $valor_necessario_por_mes = null;
}

if ($quanto_quero_pagar_mes > 0) {
    $valor_restante2 = max($preco_meta - $capital - $total_aplicado, 0);
    $meses_necessarios = $valor_restante2 > 0 ? ceil($valor_restante2 / $quanto_quero_pagar_mes) : 0;
} else {
    $meses_necessarios = 'Indefinido (defina um valor para investir mensalmente)';
}

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
        <div class="mb-2">
            <?php if (!empty($perfil_text)): ?>
                Perfil financeiro: <strong><?php echo htmlspecialchars($perfil_text); ?></strong>
            <?php else: ?>
                Você ainda não definiu seu perfil financeiro.
                <a href="../usuario/perfil.php" class="text-blue-600 underline ml-2">Clique aqui para definir seu perfil</a>
            <?php endif; ?>
        </div>
        <!-- Informações da Meta -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold mb-4">Detalhes da Meta</h2>
                <a class="bg-slate-400 w-40 p-2 rounded-md text-center" href="./editar_meta.php?id=<?php echo $meta_id; ?>"><button>+ Movimentação</button></a>
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
                <!-- Atualiza exibição do "Aporte mensal atual depositado" -->
                <p id="weatherGlass6" class="flex flex-col w-52 h-28 items-center justify-center shadow-lg bg-white rounded-md p-4">
                    <strong>Aporte mensal atual depositado:</strong>
                    <span>R$ <?php echo number_format($total_aplicado, 2, ',', '.'); ?></span>
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

        <!-- Sugestão -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Dicas e sugestões para seu perfil</h2>

            <?php if (empty($sugestoes) && empty($perfil_text)): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded text-sm">
                    Você ainda não definiu seu perfil financeiro. <a href="../perfil_financeiro/page.php" class="text-blue-600 underline">Faça o teste</a> para receber sugestões personalizadas.
                </div>
            <?php else: ?>
                <div id="sugestoesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($sugestoes as $s): ?>
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-md font-semibold"><?php echo htmlspecialchars($s['titulo']); ?></h3>
                                    <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($s['descricao']); ?></p>
                                </div>
                                <div class="text-xs text-gray-400 ml-3"><?php echo htmlspecialchars($s['categoria'] ?? ''); ?></div>
                            </div>

                            <?php if (!empty($s['dicas'])): ?>
                                <ul class="mt-3 text-sm list-disc ml-5 text-gray-700">
                                    <?php foreach ($s['dicas'] as $d): ?>
                                        <li><?php echo htmlspecialchars($d); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if (!empty($s['link'])): ?>
                                <div class="mt-3">
                                    <a href="<?php echo htmlspecialchars($s['link']); ?>" target="_blank" class="text-blue-600 underline text-sm">Saiba mais</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!--Sugestor-->
        <!-- <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Opções e Dicas de Investimento</h2>
            <ul class="list-disc ml-5">
                <?php foreach ($investimentos as $titulo => $descricao): ?>
                    <li><strong><?php echo $titulo; ?>:</strong> <?php echo $descricao; ?></li>
                <?php endforeach; ?>
            </ul>
        </div> -->

        <!-- Informações de Mercado -->
        <!-- <div class="bg-white p-6 rounded-lg shadow-md"> -->
            <!-- <h2 class="text-xl font-bold mb-4">Opções e Dicas de Investimento</h2>
            <ul class="list-disc ml-5">
                <?php foreach ($investimentos as $titulo => $descricao): ?>
                    <li><strong><?php echo $titulo; ?>:</strong> <?php echo $descricao; ?></li>
                <?php endforeach; ?>
            </ul> -->

            <!-- Informações de Mercado da API Apple -->
            <!-- <div id="marketData" class="mt-6">
                <h3 class="text-lg font-bold mb-2">Informações de Mercado</h3>
                <p class="text-gray-600">Carregando dados de mercado...</p>
            </div> -->

            <!-- IPCA -->
            <!-- <div id="ipcaData" class="mt-8">
                <h3 class="text-lg font-bold mb-2">Índice de Preços ao Consumidor Amplo (IPCA)</h3>
                <canvas id="ipcaChart" height="60"></canvas>
                <p id="ipcaAtual" class="mt-2 text-gray-700"></p>
            </div> -->

            <!-- Títulos Tesouro IPCA+ -->
            <!-- <div id="ipcaTitulos" class="mt-4 bg-gray-50 p-4 rounded shadow">
                <h4 class="text-md font-bold mb-2">Títulos Tesouro IPCA+</h4>
                <div id="listaIpcaTitulos" class="text-sm text-gray-800">Carregando títulos IPCA...</div>
            </div> -->

            <!-- Bitcoin -->
            <!-- <div id="bitcoinData" class="mt-8">
                <h3 class="text-lg font-bold mb-2">Preço do Bitcoin (BTC)</h3>
                <canvas id="bitcoinChart" height="60"></canvas>
                <p id="bitcoinAtual" class="mt-2 text-gray-700"></p>
            </div> -->

            <!-- Cotação do Dólar -->
            <!-- <div id="dolarData" class="mt-8">
                <h3 class="text-lg font-bold mb-2">Cotação do Dólar (USD/BRL)</h3>
                <canvas id="dolarChart" height="60"></canvas>
                <p id="dolarAtual" class="mt-2 text-gray-700"></p>
            </div> -->

            <!-- Taxa Selic -->
            <!-- <div id="selicData" class="mt-8">
                <h3 class="text-lg font-bold mb-2">Taxa de Juros Selic</h3>
                <canvas id="selicChart" height="60"></canvas>
                <p id="selicAtual" class="mt-2 text-gray-700"></p>
            </div> -->

        <!-- </div> -->

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

//     // Taxa Selic anual (acumulada no mês, mas exibindo como anual)
//     fetch('https://api
//         .then(res => res.json())
//         .then(data => {
//             const ultimos = data.slice(-30);
//             const labels = ultimos.map(d => d.data);
//             const valores = ultimos.map(d => parseFloat(d.valor.replace(',', '.')));
//             document.getElementById('selicAtual').innerText = 'Última taxa anual: ' + valores[valores.length-1].toFixed(2) + '% a.a.';
        
//             new Chart(document.getElementById('selicChart').getContext('2d'), {
//                 type: 'line',
//                 data: {
//                     labels: labels,
//                     datasets: [{
//                         label: 'Selic acumulada no mês (%)',
//                         data: valores,
//                         borderColor: '#16a34a',
//                         backgroundColor: 'rgba(22,163,74,0.1)',
//                         fill: true,
//                         tension: 0.2
//                     }]
//                 },
//                 options: {
//                     responsive: true,
//                     plugins: {
//                         legend: { display: true },
//                         title: {
//                             display: true,
//                             text: 'Taxa Selic acumulada no mês (últimos 30 meses)'
//                         }
//                     },
//                     scales: {
//                         x: {
//                             display: true,
//                             title: {
//                                 display: true,
//                                 text: 'Data',
//                                 font: { size: 14 }
//                             },
//                             ticks: {
//                                 autoSkip: true,
//                                 maxTicksLimit: 10,
//                                 font: { size: 12 }
//                             }
//                         },
//                         y: {
//                             display: true,
//                             title: {
//                                 display: true,
//                                 text: 'Taxa (%)',
//                                 font: { size: 14 }
//                             },
//                             ticks: {
//                                 font: { size: 12 }
//                             }
//                         }
//                     }
//                 }
//             });
//         });

//     // IPCA mensal
//     fetch('https://api.bcb.gov.br/dados/serie/bcdata.sgs.10844/dados?formato=json&dataInicial=01/01/2018')
//     .then(res => res.json())
//     .then(data => {
//         const ultimos = data.slice(-30);
//         const labels = ultimos.map(d => d.data);
//         const valores = ultimos.map(d => parseFloat(d.valor.replace(',', '.')));
//         document.getElementById('ipcaAtual').innerText = 'Último IPCA: ' + valores[valores.length-1].toFixed(2) + '%';

//         new Chart(document.getElementById('ipcaChart').getContext('2d'), {
//             type: 'line',
//             data: {
//                 labels: labels,
//                 datasets: [{
//                     label: 'IPCA (%)',
//                     data: valores,
//                     borderColor: '#eab308',
//                     backgroundColor: 'rgba(234,179,8,0.1)',
//                     fill: true,
//                     tension: 0.2
//                 }]
//             },
//             options: {
//                 responsive: true,
//                 plugins: {
//                     legend: { display: true },
//                     title: {
//                         display: true,
//                         text: 'IPCA mensal (últimos 30 meses)'
//                     }
//                 },
//                 scales: {
//                     x: {
//                         display: true,
//                         title: {
//                             display: true,
//                             text: 'Data',
//                             font: { size: 14 }
//                         },
//                         ticks: {
//                             autoSkip: true,
//                             maxTicksLimit: 10,
//                             font: { size: 12 }
//                         }
//                     },
//                     y: {
//                         display: true,
//                         title: {
//                             display: true,
//                             text: 'IPCA (%)',
//                             font: { size: 14 }
//                         },
//                         ticks: {
//                             font: { size: 12 }
//                         }
//                     }
//                 }
//             });
//     });

//     // Preço do Bitcoin (últimos 30 dias) usando CoinGecko
//     fetch('https://api.coingecko.com/api/v3/coins/bitcoin/market_chart?vs_currency=brl&days=30')
//     .then(res => res.json())
//     .then(data => {
//         const prices = data.prices; // [timestamp, price]
//         const labels = prices.map(p => {
//             const date = new Date(p[0]);
//             return date.toLocaleDateString('pt-BR');
//         });
//         const valores = prices.map(p => p[1]);
//         document.getElementById('bitcoinAtual').innerText = 'Último preço: R$ ' + valores[valores.length-1].toLocaleString('pt-BR', {minimumFractionDigits: 2});

//         new Chart(document.getElementById('bitcoinChart').getContext('2d'), {
//             type: 'line',
//             data: {
//                 labels: labels,
//                 datasets: [{
//                     label: 'Bitcoin (R$)',
//                     data: valores,
//                     borderColor: '#f7931a',
//                     backgroundColor: 'rgba(247,147,26,0.1)',
//                     fill: true,
//                     tension: 0.2
//                 }]
//             },
//             options: {
//                 responsive: true,
//                 plugins: {
//                     legend: { display: true },
//                     title: {
//                         display: true,
//                         text: 'Preço do Bitcoin (últimos 30 dias)'
//                     }
//                 },
//                 scales: {
//                     x: {
//                         display: true,
//                         title: {
//                             display: true,
//                             text: 'Data',
//                             font: { size: 14 }
//                         },
//                         ticks: {
//                             autoSkip: true,
//                             maxTicksLimit: 10,
//                             font: { size: 12 }
//                         }
//                     },
//                     y: {
//                         display: true,
//                         title: {
//                             display: true,
//                             text: 'Preço (R$)',
//                             font: { size: 14 }
//                         },
//                         ticks: {
//                             font: { size: 12 }
//                         }
