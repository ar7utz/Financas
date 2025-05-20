<?php
session_start();
include_once '../../../bd/conexao.php';

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$filterType = isset($_GET['filterType']) ? $_GET['filterType'] : 'categorias';
$timeRange = isset($_GET['timeRange']) ? $_GET['timeRange'] : 'mensal';

$data = [];

// Define o intervalo de tempo
switch ($timeRange) {
    case 'semanal':
        $dateCondition = "YEARWEEK(data) = YEARWEEK(NOW())";
        break;
    case 'mensal':
        $dateCondition = "MONTH(data) = MONTH(NOW()) AND YEAR(data) = YEAR(NOW())";
        break;
    case 'trimestral':
        $dateCondition = "QUARTER(data) = QUARTER(NOW()) AND YEAR(data) = YEAR(NOW())";
        break;
    case 'semestral':
        $dateCondition = "PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM NOW()), EXTRACT(YEAR_MONTH FROM data)) <= 6";
        break;
    case 'anual':
        $dateCondition = "YEAR(data) = YEAR(NOW())";
        break;
    default:
        $dateCondition = "MONTH(data) = MONTH(NOW()) AND YEAR(data) = YEAR(NOW())";
}

// Consulta os dados com base no filtro
if ($filterType === 'categorias') {
    $query = "SELECT categoria, tipo, SUM(valor) AS total 
              FROM transacoes 
              WHERE usuario_id = ? AND $dateCondition 
              GROUP BY categoria, tipo";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[$row['categoria']][$row['tipo']] = $row['total'];
    }
    $data['categorias'] = $categorias;
} elseif ($filterType === 'receitas' || $filterType === 'despesas') {
    $tipo = $filterType === 'receitas' ? 'positivo' : 'negativo';
    $query = "SELECT MONTHNAME(data) AS mes, SUM(valor) AS total 
              FROM transacoes 
              WHERE usuario_id = ? AND tipo = ? AND $dateCondition 
              GROUP BY MONTH(data)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $usuario_id, $tipo);
    $stmt->execute();
    $result = $stmt->get_result();

    $transacoes = [];
    while ($row = $result->fetch_assoc()) {
        $transacoes[] = $row;
    }
    $data[$filterType] = $transacoes;
}

echo json_encode(['data' => $data]);
?>