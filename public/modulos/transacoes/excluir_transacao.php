<?php
session_start();
include '../../assets/bd/conexao.php';

if (isset($_GET['id'])) {
    $transacaoId = $_GET['id'];
    
    // Preparar e executar a consulta DELETE para excluir a transação do banco de dados
    $sql = "DELETE FROM transacoes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $transacaoId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo 'Transação excluída com sucesso.';
    } else {
        echo 'Falha ao excluir a transação.';
    }
} else {
    echo 'ID da transação não fornecido.';
}
header("Location: ../dashboard/dashboard.php?mensagem=excluirTransacao");
?>