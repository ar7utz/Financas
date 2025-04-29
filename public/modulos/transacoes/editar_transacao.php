<?php
session_start();
include ('../../assets/bd/conexao.php');

if (isset($_POST['id']) && isset($_POST['descricao']) && isset($_POST['valor']) && isset($_POST['data']) && isset($_POST['categoria_id'])) {
    // Captura os dados enviados pelo formulário
    $transacao_id = $_POST['id'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $categoria_id = $_POST['categoria_id'];

    //Atualiza a transação no banco de dados
    $sql = "UPDATE transacoes SET descricao = ?, valor = ?, data = ?, categoria_id = ? WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sdsiii', $descricao, $valor, $data, $categoria_id, $transacao_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        //Redireciona para o dashboard
        header("Location: ../dashboard/dashboard.php?mensagem=SuceEdit");
        exit;
    } else {
        echo "Erro ao atualizar transação.";
    }
} else {
    echo "Campos obrigatórios não especificados.";
    exit;
}
?>