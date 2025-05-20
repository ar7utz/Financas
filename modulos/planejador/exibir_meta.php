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

// Calcular tempo necessário para alcançar a meta
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
        $tempo_formatado .= $meses_restantes . ' ' . ($meses_restantes > 1 ? 'meses' : 'meses');
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
                <a href="./editar_meta.php"><button>Editar meta</button></a>
            </div>
            <p><strong>Valor da Meta:</strong> R$ <?php echo number_format($preco_meta, 2, ',', '.'); ?></p>
            <p><strong>Valor Atual:</strong> R$ <?php echo number_format($capital, 2, ',', '.'); ?></p>
            <p><strong>Investimento Mensal:</strong> R$ <?php echo number_format($quanto_quero_pagar_mes, 2, ',', '.'); ?></p>
            <p><strong>Tempo Necessário:</strong> <?php echo is_numeric($tempo_formatado) ? "$tempo_formatado meses" : $tempo_formatado; ?></p>
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
    </div>

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
</body>
</html>
