<?php
session_start();
include ('../../assets/bd/conexao.php');

if(isset($_GET['id'])) {
    $transacao_id = $_GET['id'];

    $sql = "SELECT * FROM transacoes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $transacao_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0) {
        $transacao = $resultado->fetch_assoc();
    } else {
        echo "Transação não encontrada.";
        exit;
    }
} else {
    echo "ID da transação não especificado.";
    exit;
}
?>
