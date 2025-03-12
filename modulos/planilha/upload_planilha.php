<?php
session_start();
require_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['planilha'])) {
    $usuario_id = $_SESSION['user_id'];
    $arquivo = $_FILES['planilha'];

    // Diretório de upload
    $diretorio_upload = '../../assets/uploads/planilhas/';
    if (!is_dir($diretorio_upload)) {
        mkdir($diretorio_upload, 0777, true);
    }

    // Nome do arquivo
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $novo_nome = 'planilha_' . time() . '.' . $extensao;
    $caminho_arquivo = $diretorio_upload . $novo_nome;

    // Verifica extensão permitida
    $extensoes_permitidas = ['xls', 'xlsx'];
    if (!in_array($extensao, $extensoes_permitidas)) {
        $_SESSION['erro'] = "Apenas arquivos XLS e XLSX são permitidos.";
        header('Location: planilhas.php');
        exit;
    }

    // Move o arquivo e salva no banco
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
        $sql = "INSERT INTO planilhas (usuario_id, nome_arquivo, data_criacao) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $usuario_id, $novo_nome);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['sucesso'] = "Planilha importada com sucesso!"; 
            header('Location: pagePS.php?mensagem=PlanilhaImportadaSucesso');
        } else {
            $_SESSION['erro'] = "Erro ao salvar a planilha no banco de dados.";
            header('Location: pagePS.php?mensagem=PlanilhaImportadaErroBD');
        }
    } else {
        $_SESSION['erro'] = "Erro ao salvar a planilha.";
        header('Location: pagePS.php?mensagem=PlanilhaImportadaErro');
    }

    header('Location: pagePS.php');
    exit;
}
?>
