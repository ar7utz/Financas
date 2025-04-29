<?php
include_once('../../assets/bd/conexao.php');

$query = isset($_GET['query']) ? $_GET['query'] : '';
$usuario_id = $_GET['usuario_id'];

if ($query) {
    $sql = "
        SELECT t.*, c.nome_categoria AS categoria_nome
        FROM transacoes t
        LEFT JOIN categoria c ON t.categoria_id = c.id
        WHERE t.usuario_id = ? AND t.descricao LIKE ?
        ORDER BY t.data DESC";

    $stmt = $conn->prepare($sql);
    $searchQuery = '%' . $query . '%';
    $stmt->bind_param('is', $usuario_id, $searchQuery);
} else {
    $sql = "
        SELECT t.*, c.nome_categoria AS categoria_nome
        FROM transacoes t
        LEFT JOIN categoria c ON t.categoria_id = c.id
        WHERE t.usuario_id = ?
        ORDER BY t.data DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $usuario_id);
}

$stmt->execute();
$resultado = $stmt->get_result();

$transacoes = $resultado->fetch_all(MYSQLI_ASSOC);

echo json_encode($transacoes);
?>