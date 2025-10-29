<?php

session_start();
header('Content-Type: application/json');
include '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

$usuario_id = intval($_SESSION['user_id']);

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;
$nome = isset($input['nome_categoria']) ? trim($input['nome_categoria']) : '';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}
if ($nome === '') {
    echo json_encode(['success' => false, 'message' => 'Nome da categoria não pode ficar vazio.']);
    exit;
}

$sql_check = "SELECT id FROM categoria WHERE id = ? AND fk_user_id = ?";
if ($stmt = $conn->prepare($sql_check)) {
    $stmt->bind_param('ii', $id, $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Categoria não encontrada ou sem permissão.']);
        exit;
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Erro no banco.']);
    exit;
}

$sql_dup = "SELECT COUNT(*) AS cnt FROM categoria WHERE LOWER(nome_categoria) = LOWER(?) AND fk_user_id = ? AND id <> ?";
if ($stmt = $conn->prepare($sql_dup)) {
    $nome_lower = $nome;
    $stmt->bind_param('sii', $nome_lower, $usuario_id, $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $cnt = $res->fetch_assoc()['cnt'] ?? 0;
    $stmt->close();
    if ($cnt > 0) {
        echo json_encode(['success' => false, 'message' => 'Já existe uma categoria com esse nome.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Erro no banco.']);
    exit;
}

$sql_update = "UPDATE categoria SET nome_categoria = ? WHERE id = ? AND fk_user_id = ?";
if ($stmt = $conn->prepare($sql_update)) {
    $stmt->bind_param('sii', $nome, $id, $usuario_id);
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'nome' => $nome]);
        exit;
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Erro no banco.']);
    exit;
}
?>