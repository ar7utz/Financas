<?php
include_once('../../assets/bd/conexao.php');

$ano = $_GET['ano'];
$usuario_id = $_GET['usuario_id'];

$sql = "
    SELECT t.*, c.nome_categoria AS categoria_nome
    FROM transacoes t
    LEFT JOIN categoria c ON t.categoria_id = c.id
    WHERE t.usuario_id = ? AND YEAR(t.data) = ?
    ORDER BY t.data DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $usuario_id, $ano);
$stmt->execute();
$resultado = $stmt->get_result();

$transacoes = $resultado->fetch_all(MYSQLI_ASSOC);

echo json_encode($transacoes);
?>