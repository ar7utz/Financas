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
    <title>Meta <?php echo htmlspecialchars ($capital) ?></title> <!--ajustar para colocar o objetivo da meta-->
</head>
<body>
    <?php require_once '../../assets/templates/navbar.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Sua Meta Financeira</h1>
        
        <div class="">
            <h1> <?php echo $nome ?> </h1>
        </div>

        <p><strong>Objetivo:</strong> <?php echo htmlspecialchars($meta['razao']); ?></p>
        <p><strong>Valor da Meta:</strong> R$ <?php echo number_format($preco_meta, 2, ',', '.'); ?></p>
        <p><strong>Valor Atual:</strong> R$ <?php echo number_format($capital, 2, ',', '.'); ?></p>
        <p><strong>Investimento Mensal:</strong> R$ <?php echo number_format($quanto_quero_pagar_mes, 2, ',', '.'); ?></p>
        <p><strong>Tempo Necessário:</strong> <?php echo is_numeric($meses_necessarios) ? "$meses_necessarios meses" : $meses_necessarios; ?></p>
        
        <h2 class="text-xl font-bold mt-6">Dicas para Acelerar</h2>
        <ul class="list-disc ml-5">
            <?php foreach ($dicas as $dica) {
                echo "<li>$dica</li>";
            } ?>
        </ul>
        
        <h2 class="text-xl font-bold mt-6">Opções de Investimento</h2>
        <ul class="list-disc ml-5">
            <?php foreach ($investimentos as $titulo => $descricao) {
                echo "<li><strong>$titulo:</strong> $descricao</li>";
            } ?>
        </ul>
    </div>
</body>
</html>
