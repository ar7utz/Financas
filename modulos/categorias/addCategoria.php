<?php
session_start();
include '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

$usuario_id = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: formAddCategoria.php');
    exit;
}

$nome = trim($_POST['nome_categoria'] ?? '');

if ($nome === '') {
    header('Location: formAddCategoria.php?mensagem=nomeVazio');
    exit;
}

// Verifica se já existe para este usuário (case-insensitive)
$sql_check = "SELECT COUNT(*) AS cnt FROM categoria WHERE LOWER(nome_categoria) = LOWER(?) AND fk_user_id = ?";
if ($stmt = $conn->prepare($sql_check)) {
    $stmt->bind_param('si', $nome, $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = ($res->fetch_assoc()['cnt'] ?? 0) > 0;
    $stmt->close();

    if ($exists) {
        header('Location: formAddCategoria.php?mensagem=existe');
        exit;
    }
} else {
    header('Location: formAddCategoria.php?mensagem=erro');
    exit;
}

$sql = "INSERT INTO categoria (nome_categoria, fk_user_id) VALUES (?, ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('si', $nome, $usuario_id);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: ../usuario/perfil.php?mensagem=sucesso');
        exit;
    } else {
        $stmt->close();
        header('Location: formAddCategoria.php?mensagem=erro');
        exit;
    }
} else {
    header('Location: formAddCategoria.php?mensagem=erro');
    exit;
}
?>