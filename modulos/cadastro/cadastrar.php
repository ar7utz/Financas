<?php
session_start();
require_once('../../assets/bd/conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $foto = null;
    
    // Validar se a senha foi informada
    if (!$senha) {
        $_SESSION['erro'] = "A senha é obrigatória.";
        header('Location: cadastro.php?mensagem=SenhaObrigatoria');
        exit;
    }

    // Processar o upload da foto
    if (!empty($_FILES['foto_perfil']['name']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    
        if (!in_array($extensao, $extensoes_permitidas)) {
            $_SESSION['erro'] = "Formato de arquivo inválido. Apenas JPG, JPEG, PNG e GIF são permitidos.";
            header('Location: cadastro.php?mensagem=ArquivosValidos');
            exit;
        }
    
        $novo_nome = uniqid('foto_', true) . '.' . $extensao;
        $destino = '../../assets/uploads/' . $novo_nome;
    
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $destino)) {
            $foto = $novo_nome;
        } else {
            $_SESSION['erro'] = "Erro ao salvar a foto.";
            header('Location: cadastro.php?mensagem=ErroFoto');
            exit;
        }
    }

    // Verificar se o e-mail já está cadastrado
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['erro'] = "O e-mail já está em uso.";
        header('Location: cadastro.php?mensagem=EmailJaEmUso');
        exit;
    }

    $sql = "INSERT INTO user (nome, username, email, telefone, senha, foto, perfil_financeiro)
            VALUES (?, ?, ?, ?, ?, ?, NULL)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['erro'] = "Erro ao preparar cadastro: " . $conn->error;
        header('Location: cadastro.php?mensagem=ErroCadastroUser');
        exit;
    }
    $stmt->bind_param('ssssss', $nome, $username, $email, $telefone, $senha, $foto);

    if ($stmt->execute()) {
        $_SESSION['status_cadastro'] = true;
        header('Location: ../login/login.php?mensagem=CadastroSucesso');
    } else {
        $_SESSION['erro'] = "Erro ao cadastrar usuário: " . $stmt->error;
        header('Location: cadastro.php?mensagem=ErroCadastroUser');
    }
    $stmt->close();
    $conn->close();
}
?>
