<?php
session_start();

include ('../../assets/bd/conexao.php');

if (empty($_POST['email']) || empty($_POST['senha'])) {
    header('Location: ../index.php');
    exit;
}

$email = $_POST['email'];
$senha = $_POST['senha'];

$sql = "SELECT id FROM user WHERE email = ? and senha = ?";
$preparacao = $conn->prepare($sql);
$preparacao->bind_param('ss', $email, $senha);
$preparacao->execute();
$resultado = $preparacao->get_result();

$usuario = $resultado->fetch_assoc();

if ($usuario) {
    $_SESSION['user_id'] = $usuario['id'];
    header("Location: ../dashboard/hp_login.php");

    exit;
} else {
    $_SESSION['erro_login'] = "<span class='text-center text-red-600'> E-mail ou senha incorretos! </span>";
    header('Location: ../login/login.php');
    exit;
}

?>
