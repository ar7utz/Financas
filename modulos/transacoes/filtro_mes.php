<?php
include_once('../../assets/bd/conexao.php');

$mes = $_GET['mes'];
$usuario_id = $_GET['usuario_id'];

$sql = "
    SELECT t.*, c.nome_categoria AS categoria_nome
    FROM transacoes t
    LEFT JOIN categoria c ON t.categoria_id = c.id
    WHERE t.usuario_id = ? AND DATE_FORMAT(t.data, '%Y-%m') = ?
    ORDER BY t.data DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $usuario_id, $mes);
$stmt->execute();
$resultado = $stmt->get_result();

$transacoes = $resultado->fetch_all(MYSQLI_ASSOC);

echo json_encode($transacoes);
?>