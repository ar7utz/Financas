<?php
session_start();

include ('../../assets/bd/conexao.php');

if (empty($_POST['login']) || empty($_POST['senha'])) {
    header('Location: ../index.php');
    exit;
}

$login = $_POST['login']; //Pode realizar a entrada com o email ou com o nome de usuário
$senha = $_POST['senha'];

$sql = "SELECT id FROM user WHERE (email = ? OR username = ?) and senha = ?";
$preparacao = $conn->prepare($sql);
$preparacao->bind_param('sss', $login, $login, $senha);
$preparacao->execute();
$resultado = $preparacao->get_result();

$usuario = $resultado->fetch_assoc();

if ($usuario) {
    $_SESSION['user_id'] = $usuario['id'];
    header("Location: ../dashboard/hp_login.php?mensagem=LoginSucesso");

    exit;
} else {
    $_SESSION['erro_login'] = "<span class='text-center text-red-600'> E-mail ou senha incorretos! </span>";
    header('Location: ../login/login.php');
    exit;
}

?>
