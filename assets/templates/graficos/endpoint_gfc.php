<?php
session_start();
include_once '../../assets/bd/conexao.php';

header('Content-Type: application/json');

// Testa conexão ANTES de qualquer coisa
if (!isset($conn) || !$conn) {
    echo json_encode(['error' => 'Falha na conexão com o banco de dados']);
    exit;
}

// Verifica se o usuário está logado
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
} elseif (isset($_SESSION['user_id'])) {
    $usuario_id = $_SESSION['user_id'];
} else {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$filterType = isset($_GET['filterType']) ? $_GET['filterType'] : 'categoria';
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
if ($filterType === 'categoria') {
    $query = "SELECT categoria, tipo, SUM(valor) AS total 
              FROM transacoes 
              WHERE usuario_id = ? AND $dateCondition 
              GROUP BY categoria, tipo";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['error' => 'Erro na query: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('i', $usuario_id);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Erro ao executar query: ' . $stmt->error]);
        exit;
    }
    $result = $stmt->get_result();

    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[$row['categoria']][$row['tipo']] = $row['total'];
    }
    $data['categoria'] = $categorias;
} elseif ($filterType === 'receitas' || $filterType === 'despesas') {
    $tipo = $filterType === 'receitas' ? 'positivo' : 'negativo';
    $query = "SELECT MONTHNAME(data) AS mes, SUM(valor) AS total 
              FROM transacoes 
              WHERE usuario_id = ? AND tipo = ? AND $dateCondition 
              GROUP BY MONTH(data)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['error' => 'Erro na query: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('is', $usuario_id, $tipo);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Erro ao executar query: ' . $stmt->error]);
        exit;
    }
    $result = $stmt->get_result();

    $transacoes = [];
    while ($row = $result->fetch_assoc()) {
        $transacoes[] = $row;
    }
    $data[$filterType] = $transacoes;
}

echo json_encode(['data' => $data]);
exit;
?>