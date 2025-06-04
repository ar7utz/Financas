<?php
session_start();
include '../../assets/bd/conexao.php';

if (isset($_GET['id'])) {
    $metaId = $_GET['id'];
    
    // Preparar e executar a consulta DELETE para excluir a meta do banco de dados
    $sql = "DELETE FROM planejador WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $metaId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo 'Meta excluída com sucesso.';
    } else {
        echo 'Falha ao excluir a Meta.';
    }
} else {
    echo 'ID da Meta não fornecido.';
}
header('Location: page.php?mensagem=metaExcluida');
exit;
?>