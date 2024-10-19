<?php
session_start();
include ('../../assets/bd/conexao.php');

// Verifica se o formulário foi enviado com os campos necessários
if (isset($_POST['id']) && isset($_POST['descricao']) && isset($_POST['valor']) && isset($_POST['data'])) {
    // Captura os dados enviados pelo formulário
    $transacao_id = $_POST['id'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];

    // Atualiza a transação no banco de dados
    $sql = "UPDATE transacoes SET descricao = ?, valor = ?, data = ? WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sdssi', $descricao, $valor, $data, $transacao_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        // Redireciona para a página de histórico ou exibe uma mensagem de sucesso
        header("Location: ../dashboard/dashboard.php");
        exit;
    } else {
        echo "Erro ao atualizar transação.";
    }
} else {
    echo "Campos obrigatórios não especificados.";
    exit;
}
?>
