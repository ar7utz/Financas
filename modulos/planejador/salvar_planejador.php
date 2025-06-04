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
    $criado_em = date('Y-m-d');
    $horario_criado = date('H:i:s');

    $stmt = $conn->prepare('SELECT * FROM planejador WHERE razao = ? AND usuario_id = ?');
    $stmt->bind_param('si', $razao, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $sql = "INSERT INTO planejador (usuario_id, razao, preco_meta, capital, quanto_tempo_quero_pagar, quanto_quero_pagar_mes, criado_em, horario_criado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isdidsss', $usuario_id, $razao, $preco_meta, $capital, $quanto_tempo_quero_pagar, $quanto_quero_pagar_mes, $criado_em, $horario_criado);

        if ($stmt->execute()) {
            $_SESSION['status_cadastro'] = true;
            header('Location: page.php?mensagem=metaAdicionada');
            exit;
        } else {
            $_SESSION['status_cadastro'] = false;
            header("Location: ../planejador/page.php?mensagem=ErroMeta");
        }
    } else {
        $_SESSION['status_cadastro'] = false;
        header("Location: ../planejador/page.php?mensagem=MesmoNomeMeta");
    }
}

$conn->close();

?>
