<?php
session_start();

include ('../../assets/bd/conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['erro'] = "Você precisa estar logado para criar uma meta.";
        header('Location: ../login/login.php');
        exit;
    }

    $usuario_id = $_SESSION['user_id'];

    $razao = $_POST['razao'];
    $preco_meta = $_POST['preco_meta'];
    $capital = $_POST['capital'];
    $quanto_tempo_quero_pagar = $_POST['quanto_tempo_quero_pagar'];
    $quanto_quero_pagar_mes = $_POST['quanto_quero_pagar_mes'];
    $criado_em = date('Y-m-d H:i:s');

    $stmt = $conn->prepare('SELECT * FROM planejador WHERE razao = ? AND usuario_id = ?');
    $stmt->bind_param('si', $razao, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $sql = "INSERT INTO planejador (usuario_id, razao, preco_meta, capital, quanto_tempo_quero_pagar, quanto_quero_pagar_mes, criado_em) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isdidss', $usuario_id, $razao, $preco_meta, $capital, $quanto_tempo_quero_pagar, $quanto_quero_pagar_mes, $criado_em);

        if ($stmt->execute()) {
            $_SESSION['status_cadastro'] = true;
            header('Location: ../dashboard/dashboard.php');
            exit;
        } else {
            $_SESSION['status_cadastro'] = false;
            $_SESSION['erro'] = "Erro ao salvar planejamento: " . $conn->error;
        }
    } else {
        $_SESSION['status_cadastro'] = false;
        $_SESSION['erro'] = "Já existe uma meta com este nome.";
    }
}

$conn->close();
?>
