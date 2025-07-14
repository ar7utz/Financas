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
    "Aumente seu aporte mensal investindo uma parte do seu salário.",
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

    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">
    <title>Finstash - Meta <?php echo htmlspecialchars($meta['razao']); ?></title>
</head>
<body class="bg-gray-100">
    <?php require_once '../../assets/templates/navbar.php'; ?>

    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6 text-center">Sua Meta Financeira: <span class="text-blue-600"><?php echo htmlspecialchars($meta['razao']); ?></span></h1>

        <!-- Informações da Meta -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Detalhes da Meta</h2>
            <div class="">
                <a href="./editar_meta.php?id=<?php echo $meta_id; ?>"><button>Adicionar movimentação</button></a>
            </div>
            <div class="flex flex-wrap gap-4 mt-4 items-center align-middle justify-center"> <!-- vou arrumar essa bomba -->
                <p class="flex w-48 h-16 items-center justify-center bg-kansai rounded-md p-4"><strong>Valor da Meta:</strong> R$ <?php echo number_format($preco_meta, 2, ',', '.'); ?></p>
                <p class="flex w-48 h-16 items-center justify-center bg-kansai rounded-md p-4"><strong>Valor de Entrada:</strong> R$ <?php echo number_format($capital, 2, ',', '.'); ?></p>
                <p class="flex w-48 h-16 items-center justify-center bg-kansai rounded-md p-4"><strong>Investimento Mensal Desejado:</strong> R$ <?php echo number_format($quanto_quero_pagar_mes, 2, ',', '.'); ?></p>
                <p class="flex w-48 h-16 items-center justify-center bg-kansai rounded-md p-4"><strong>Tempo que deseja pagar:</strong> <?php echo is_numeric($quanto_tempo_quero_pagar) ? $quanto_tempo_quero_pagar . ' meses' : 'Não informado'; ?></p>

                <!-- <div class="flex flex-row">
                    <p class="flex w-48 h-16 items-center justify-center bg-kansai rounded-md p-4"><strong>Pagando o valor mensal desejado, você levaria:</strong> <?php echo $tempo_formatado; ?></p>
                    <p class="flex w-48 h-18 items-center justify-center bg-kansai rounded-md p-2 "><strong class="mr-2">Você precisaria investir por mês: </strong>
                        <?php
                        if ($valor_necessario_por_mes !== null) {
                            echo 'R$ ' . number_format($valor_necessario_por_mes, 2, ',', '.');
                        } else {
                            echo 'Informe o tempo desejado para calcular.';
                        }
                        ?>
                        para pagar em <strong class="ml-2"> <?php echo is_numeric($quanto_tempo_quero_pagar) ? $quanto_tempo_quero_pagar . ' meses' : 'Não informado'; ?></strong></p>
                    </p>
                </div> -->

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

            <!-- Informações de Mercado da API -->
            <div id="marketData" class="mt-6">
                <h3 class="text-lg font-bold mb-2">Informações de Mercado</h3>
                <p class="text-gray-600">Carregando dados de mercado...</p>
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
            <div class="bg-white p-6 rounded-lg shadow-md mt-2">
                <h1 class="text-center font-bold text-lg mb-2">Histórico de movimentações da meta: <span class="text-blue-600"><?php echo htmlspecialchars($meta['razao']); ?></span></h1>
                <div class="flex flex-col">
                    <?php
                    $sql = "SELECT * FROM movimentacoes WHERE usuario_id = ? AND meta_id = ? ORDER BY data DESC, hora DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ii', $usuario_id, $meta_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                            
                    $temMovimentacao = false;
                    while ($row = $result->fetch_assoc()) {
                        $temMovimentacao = true;
                        echo "<div class='mb-2 p-2 border-b bg-slate-400 border-gray-300 rounded-md'>";
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
        // Função para buscar dados de mercado da API Polygon.io
        async function fetchMarketData() {
            const apiKey = 'qPI2YVLHqBzBCd4A44kTak0IBkGVFyea'; 
            const url = `https://api.polygon.io/v2/aggs/ticker/AAPL/prev?apiKey=${apiKey}`; // Exemplo com o ticker AAPL (Apple)

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

        // Chamar a função ao carregar a página
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

</body>
</html>
