<?php
session_start();

include '../../assets/bd/conexao.php';

date_default_timezone_set('America/Sao_Paulo'); //vou resolver isso 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id'];

    $descricao = $_POST['descricao'];
    $valor = str_replace(',', '.', $_POST['valor']); //Substitui vírgula por ponto
    $valor = (float)$valor;
    $data = $_POST['data'];
    $categoria_id = $_POST['categoria_id'];

    $tipo = $_POST['tipo']; //Obter o tipo de transação (positivo ou negativo)

    if (empty($descricao) || empty($valor) || empty($data) || empty($categoria_id)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Todos os campos são obrigatórios.']);
        exit;
    }

    if ($tipo === 'negativo') {
        $valor = -$valor;
        $tipo = 2; // Tipo 2 para transações negativas
    } else {
        $tipo = 1; // Tipo 1 para transações positivas
    }

    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $sql = "INSERT INTO transacoes (descricao, valor, data, tipo, usuario_id, categoria_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sdsiii', $descricao, $valor, $data, $tipo, $usuario_id, $categoria_id);
        $stmt->execute();
        header("Location: ../dashboard/dashboard.php?mensagem=sucesso");
    } else {
        header("location: ../dashboard/dasboard.php?mensagem=erroTransacao");
        exit;
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método de solicitação inválido. Use POST.']);
}

$conn->close();

?>
<script src="../../assets/js/main.js"></script>
