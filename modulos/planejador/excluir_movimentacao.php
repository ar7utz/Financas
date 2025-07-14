<?php
session_start();
include_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false]);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;
$meta_id = $_GET['meta_id'] ?? null;

if ($id && $meta_id) {
    $sql = "DELETE FROM movimentacoes WHERE id = ? AND usuario_id = ? AND meta_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $id, $usuario_id, $meta_id);
    $stmt->execute();
}
header("Location: exibir_meta.php?id=$meta_id&mensagem=excluido");
exit;