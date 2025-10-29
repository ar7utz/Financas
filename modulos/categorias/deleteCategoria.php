<?php
session_start();
include '../../assets/bd/conexao.php';

if (isset($_GET['id'])) {
    $transacaoId = $_GET['id'];
    
    // Preparar e executar a consulta DELETE para excluir a transação do banco de dados
    $sql = "DELETE FROM categoria WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $transacaoId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo 'Categoria excluída com sucesso.';
    } else {
        echo 'Falha ao excluir a categoria.';
    }
} else {
    echo 'ID da categoria não fornecido.';
}
header("Location: "); // passar perfil
?>