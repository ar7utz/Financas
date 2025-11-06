<?php
session_start();
require_once '../../assets/bd/conexao.php';

header('Content-Type: application/json');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Você precisa estar logado para realizar o teste.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'msg' => 'Requisição inválida.']);
    exit;
}

$r1 = $_POST['pergunta1'] ?? null;
$r2 = $_POST['pergunta2'] ?? null;
$r3 = $_POST['pergunta3'] ?? null;
$r4 = $_POST['pergunta4'] ?? null;
$r5 = $_POST['pergunta5'] ?? null;
$usuario_id = intval($_SESSION["user_id"]);

if (!$r1 || !$r2 || !$r3 || !$r4 || !$r5) {
    echo json_encode(['status' => 'erro', 'msg' => 'Responda todas as perguntas.']);
    exit;
}

$pontosMap = [
    'economizar' => 1, 'investir' => 3, 'gastar' => 2, 'pagarDividas' => 1, 'seguranca' => 1,
    'conservador' => 1, 'moderado' => 2, 'arrojado' => 3, 'especulativo' => 3, 'naoInvisto' => 1,
    'iniciante' => 1, 'intermediario' => 2, 'avancado' => 3, 'especialista' => 3, 'naoSei' => 1,
    'evito' => 1, 'gerencio' => 2, 'aceito' => 3, 'naoMeImporto' => 3, 'naoTenho' => 1,
    'planejo' => 3, 'naoPlanejo' => 1, 'dependo' => 1, 'jaAposentei' => 2
];

$total = 0;
foreach ([$r1, $r2, $r3, $r4, $r5] as $resp) {
    $total += isset($pontosMap[$resp]) ? $pontosMap[$resp] : 0;
}

if ($total >= 4 && $total <= 6) {
    $perfil = "Conservador";
} elseif ($total >= 7 && $total <= 9) {
    $perfil = "Moderado";
} else {
    $perfil = "Agressivo";
}

try {
    // inicia transação para garantir consistência
    $conn->begin_transaction();

    // insere na tabela respostas_perfil
    $insSql = "INSERT INTO respostas_perfil (usuario_id, pergunta1, pergunta2, pergunta3, pergunta4, pergunta5, pontuacao_total, perfil)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insSql);
    if (!$stmt) {
        throw new Exception("Erro ao preparar insert respostas_perfil: " . $conn->error);
    }
    if (!$stmt->bind_param("isssssis", $usuario_id, $r1, $r2, $r3, $r4, $r5, $total, $perfil)) {
        throw new Exception("Erro ao bind_param respostas_perfil: " . $stmt->error);
    }
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar insert respostas_perfil: " . $stmt->error);
    }
    $resposta_id = intval($conn->insert_id);
    $stmt->close();

    if ($resposta_id <= 0) {
        throw new Exception("ID da resposta inválido após insert.");
    }

    // tenta atualizar user.perfil_financeiro com o id recém inserido (respeita FK)
    $upSql = "UPDATE `user` SET perfil_financeiro = ? WHERE id = ?";
    $stmtUp = $conn->prepare($upSql);
    if (!$stmtUp) {
        throw new Exception("Erro ao preparar update user: " . $conn->error);
    }
    if (!$stmtUp->bind_param('ii', $resposta_id, $usuario_id)) {
        throw new Exception("Erro ao bind_param update user: " . $stmtUp->error);
    }
    if (!$stmtUp->execute()) {
        throw new Exception("Erro ao executar update user: " . $stmtUp->error);
    }
    $stmtUp->close();

    // commit
    $conn->commit();

    // atualiza sessão (nome do perfil e id da resposta para uso futuro)
    $_SESSION['perfil_financeiro'] = $perfil;
    $_SESSION['resposta_perfil_id'] = $resposta_id;

    echo json_encode([
        'status' => 'ok',
        'perfil' => $perfil,
        'pontos' => $total,
        'redirect' => '../planejador/page.php',
        'delay_seconds' => 10
    ]);
    exit;
} catch (Exception $e) {
    // rollback em caso de erro
    $conn->rollback();
    if ($isAjax) {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro ao processar o perfil: ' . $e->getMessage()]);
    } else {
        $_SESSION['erro'] = "Erro ao processar o perfil: " . $e->getMessage();
        header('Location: ../planejador/page.php?mensagem=ErroProcessarPerfil');
    }
    exit;
}
?>