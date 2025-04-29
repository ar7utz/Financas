<?php
include_once('../../assets/bd/conexao.php');

$ano = isset($_GET['ano']) ? $_GET['ano'] : null;
$mes = isset($_GET['mes']) ? $_GET['mes'] : null;
$usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : null;

if (!$usuario_id) {
    echo json_encode(["erro" => "Usuário não autenticado"]);
    exit;
}

// Buscar meses disponíveis do ano selecionado
if ($ano && !$mes) {
    $sql = "SELECT DISTINCT MONTH(data) AS mes 
            FROM transacoes 
            WHERE usuario_id = ? AND YEAR(data) = ? 
            ORDER BY mes ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $usuario_id, $ano);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $meses = [];
    while ($row = $resultado->fetch_assoc()) {
        $meses[] = ['mes' => str_pad($row['mes'], 2, '0', STR_PAD_LEFT)]; // Garante formato '01', '02', etc.
    }

    echo json_encode($meses);
    exit;
}

// Buscar transações do ano e mês selecionados
if ($mes) {
    list($anoSelecionado, $mesSelecionado) = explode('-', $mes);

    $sql = "SELECT t.*, c.nome_categoria AS categoria_nome
            FROM transacoes t
            LEFT JOIN categoria c ON t.categoria_id = c.id
            WHERE t.usuario_id = ? AND YEAR(t.data) = ? AND MONTH(t.data) = ?
            ORDER BY t.data DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $usuario_id, $anoSelecionado, $mesSelecionado);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $transacoes = $resultado->fetch_all(MYSQLI_ASSOC);

    echo json_encode($transacoes);
    exit;
}

echo json_encode(["erro" => "Parâmetros inválidos"]);
?>
