<?php //testar o funcionamento desse arquivo
session_start();
require_once('../../assets/bd/conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id'];
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $foto_antiga = $_POST['foto_antiga']; // Nome da foto antiga
    $foto_nova = null;

    // Verificar se foi enviada uma nova foto
    if (!empty($_FILES['foto_perfil']['name']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extensao, $extensoes_permitidas)) {
            $_SESSION['erro'] = "Formato de arquivo inválido. Apenas JPG, JPEG, PNG e GIF são permitidos.";
            header('Location: editar_usuario.php');
            exit;
        }

        $novo_nome = uniqid('foto_', true) . '.' . $extensao;
        $destino = '../../assets/uploads/' . $novo_nome;

        // Mover o arquivo para a pasta de uploads
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $destino)) {
            $foto_nova = $novo_nome;

            // Deletar a foto antiga (se não for a padrão)
            if ($foto_antiga && $foto_antiga !== 'foto_default.png') {
                unlink('../../assets/uploads/' . $foto_antiga);
            }
        } else {
            $_SESSION['erro'] = "Erro ao salvar a nova foto.";
            header('Location: editar_usuario.php');
            exit;
        }
    } else {
        $foto_nova = $foto_antiga; // Se nenhuma foto foi enviada, mantém a antiga
    }

    // Atualizar os dados no banco
    $sql = "UPDATE user SET nome = ?, username = ?, telefone = ?, email = ?, foto = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssi', $nome, $username, $telefone, $email, $foto_nova, $usuario_id);

    if ($stmt->execute()) {
        $_SESSION['sucesso'] = "Perfil atualizado com sucesso.";
        header('Location: perfil_usuario.php');
    } else {
        $_SESSION['erro'] = "Erro ao atualizar o perfil.";
        header('Location: editar_usuario.php');
    }

    $stmt->close();
    $conn->close();
}
?>
