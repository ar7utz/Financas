<?php
session_start();
require_once('../../assets/bd/conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login']; // Pode ser email ou username
    $senha = $_POST['senha'];
    
    if (empty($login) || empty($senha)) {
        $_SESSION['erro_login'] = "<span class='text-center text-red-600'> Login e senha são obrigatórios! </span>";
        header('Location: ../login/login.php');
        exit;
    }

    // Buscar o usuário pelo email ou username
    $sql = "SELECT id, nome, foto, senha FROM user WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar se a senha está correta
        if (password_verify($senha, $user['senha'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['foto'] = $user['foto'] ? $user['foto'] : 'foto_default.png';

            header("Location: ../dashboard/hp_login.php?mensagem=LoginSucesso");
            exit;
        } else {
            $_SESSION['erro_login'] = "<span class='text-center text-red-600'> E-mail ou senha incorretos! </span>";
            header('Location: ../login/login.php?mensagem=ErroLogin');
            exit;
        }
    } else {
        $_SESSION['erro_login'] = "<span class='text-center text-red-600'> Usuário não encontrado! </span>";
        header('Location: ../login/login.php?mensagem=UserNotFound');
        exit;
    }

    $stmt->close();
    $conn->close();
}
