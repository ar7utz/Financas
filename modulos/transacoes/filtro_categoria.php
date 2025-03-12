<?php
session_start();
require_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["erro" => "Usuário não autenticado"]);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : null;

if (!$categoria_id) {
    echo json_encode(["erro" => "Categoria inválida"]);
    exit;
}

$sql = "
    SELECT t.id, t.descricao, t.valor, t.data, c.nome_categoria 
    FROM transacoes t
    JOIN categoria c ON t.categoria_id = c.id
    WHERE t.usuario_id = ? AND t.categoria_id = ?
    ORDER BY t.data DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $usuario_id, $categoria_id);
$stmt->execute();
$resultado = $stmt->get_result();
$transacoes = $resultado->fetch_all(MYSQLI_ASSOC);

echo json_encode($transacoes);
?>
