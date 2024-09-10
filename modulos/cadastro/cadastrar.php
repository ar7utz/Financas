<?php
session_start();

include ('../../assets/bd/conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $telefone = $_POST['telefone'];

    // Verificar se o e-mail já está em uso
    $stmt = $conn->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Se não houver nenhum usuário com o mesmo e-mail, procede com o cadastro
    if ($result->num_rows === 0) {
        $sql = "INSERT INTO user (nome, email, telefone, senha) VALUES ('$nome', '$email', '$telefone', '$senha')";
        if($conn->query($sql) === true){
            $_SESSION['status_cadastro'] = true;
            header('Location: ../login/login.php');
            exit;
        } else {
            $_SESSION['status_cadastro'] = false;
            $_SESSION['erro'] = "Erro ao cadastrar usuário: " . $conn->error;
        }
    } else {
        $_SESSION['status_cadastro'] = false;
        $_SESSION['erro'] = "O e-mail já está em uso.";
    }
    
}

$sql = "INSERT INTO user (nome, email, telefone, senha) VALUES ('$nome', '$email', '$telefone', '$senha')";
if($conn->query ($sql) === true){
    $_SESSION['status_cadastro'] = true;
    header('Location: ../dashboard/hp_login.php');
}

$conn->close();
?>