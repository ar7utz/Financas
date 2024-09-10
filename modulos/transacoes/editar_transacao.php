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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
    <link rel="shortcut icon" href="../../dinheiro.ico"/>
    <title>Editar transação</title>
</head>
<body>
    <div class="nav_top">
        <a class="btn_voltar" href="../dashboard/dashboard.php">Voltar</a>
    </div>
    <div class="pageTitle">
        <h1>Editar Transação</h1>
    </div>
    <form action="update_transacao.php" method="POST" class="form_editar">
        <input type="hidden" name="transacao_id" value="<?php echo $transacao['id']; ?>">
        <label for="descricao">Descrição:</label>
        <input type="text" id="descricao" name="descricao" value="<?php echo $transacao['descricao']; ?>">
        <label for="valor">Valor:</label>
        <input type="number" id="valor" name="valor" value="<?php echo $transacao['valor']; ?>">
        <label for="data">Data:</label>
        <input type="date" id="data" name="data" value="<?php echo $transacao['data']; ?>">
        <div class="buttons">
            <button type="submit" name="editar_transacao">Salvar Alterações</button>
        </div>
    </form>
</body>
</html>

