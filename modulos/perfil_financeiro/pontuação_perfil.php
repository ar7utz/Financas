<?php
session_start();
require_once '../../assets/bd/conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Você precisa estar logado para realizar o teste.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r1 = $_POST['pergunta1'];
    $r2 = $_POST['pergunta2'];
    $r3 = $_POST['pergunta3'];
    $r4 = $_POST['pergunta4'];
    $r5 = $_POST['pergunta5'];
    $usuario_id = $_SESSION["user_id"];

    $pontosMap = [
        'economizar' => 1, 'investir' => 3, 'gastar' => 2, 'pagarDividas' => 1, 'seguranca' => 1,
        'conservador' => 1, 'moderado' => 2, 'arrojado' => 3, 'especulativo' => 3, 'naoInvisto' => 1,
        'iniciante' => 1, 'intermediario' => 2, 'avancado' => 3, 'especialista' => 3, 'naoSei' => 1,
        'evito' => 1, 'gerencio' => 2, 'aceito' => 3, 'naoMeImporto' => 3, 'naoTenho' => 1,
        'planejo' => 3, 'naoPlanejo' => 1, 'dependo' => 1, 'naoMeImporto' => 1, 'jaAposentei' => 2
    ];

    $total = $pontosMap[$r1] + $pontosMap[$r2] + $pontosMap[$r3] + $pontosMap[$r4] + $pontosMap[$r5];

    if ($total >= 4 && $total <= 6) {
        $perfil = "Conservador";
    } elseif ($total >= 7 && $total <= 9) {
        $perfil = "Moderado";
    } else {
        $perfil = "Agressivo";
    }

    $stmt = $conn->prepare("INSERT INTO respostas_perfil 
        (usuario_id, pergunta1, pergunta2, pergunta3, pergunta4, pergunta5, pontuacao_total, perfil) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssis", $usuario_id, $r1, $r2, $r3, $r4, $r5, $total, $perfil);
    $stmt->execute();

    echo json_encode([
        'status' => 'ok',
        'perfil' => $perfil,
        'pontos' => $total
    ]);
    exit;
}

echo json_encode(['status' => 'erro', 'msg' => 'Requisição inválida.']);
exit;
?>