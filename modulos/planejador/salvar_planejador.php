<?php
session_start();

include ('../../assets/bd/conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['erro'] = "Você precisa estar logado para criar uma meta.";
        header('Location: ../login/login.php');
        exit;
    }

    $usuario_id = $_SESSION['user_id'];

    $razao = $_POST['razao'] ?? '';
    $preco_meta_raw = $_POST['preco_meta'] ?? '0';
    $capital_raw = $_POST['capital'] ?? '0';
    $quanto_tempo_quero_pagar = $_POST['quanto_tempo_quero_pagar'] ?? 0;
    $quanto_quero_pagar_mes_raw = $_POST['quanto_quero_pagar_mes'] ?? '0';
    $criado_em = date('Y-m-d');
    $horario_criado = date('H:i:s');

    function parseBrazilianFloat($str) {
        $s = trim((string)$str);
        if ($s === '') return 0.0;
        $s = str_replace("\xc2\xa0", '', $s);
        $s = str_replace([' ', "\t"], '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^\d\.]/', '', $s);
        if ($s === '') return 0.0;
        return floatval($s);
    }

    $preco_meta = parseBrazilianFloat($preco_meta_raw);
    $capital = parseBrazilianFloat($capital_raw);
    $quanto_quero_pagar_mes = parseBrazilianFloat($quanto_quero_pagar_mes_raw);
    $quanto_tempo_quero_pagar = intval($quanto_tempo_quero_pagar);

    if ($razao === '') {
        $_SESSION['status_cadastro'] = false;
        header("Location: ../planejador/page.php?mensagem=NomeObrigatorio");
        exit;
    }

    $stmt = $conn->prepare('SELECT * FROM planejador WHERE razao = ? AND usuario_id = ?');
    $stmt->bind_param('si', $razao, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 0) {
        $sql = "INSERT INTO planejador (usuario_id, razao, preco_meta, capital, quanto_tempo_quero_pagar, quanto_quero_pagar_mes, criado_em, horario_criado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $_SESSION['status_cadastro'] = false;
            header("Location: ../planejador/page.php?mensagem=ErroPrepare");
            exit;
        }

        $stmt->bind_param('isddidss', $usuario_id, $razao, $preco_meta, $capital, $quanto_tempo_quero_pagar, $quanto_quero_pagar_mes, $criado_em, $horario_criado);

        if ($stmt->execute()) {
            $new_meta_id = $conn->insert_id;

            $tipo = 'criada';
            $descricao = "Meta '$razao' criada.";
            $sql_mov = "INSERT INTO movimentacoes (usuario_id, meta_id, tipo, descricao, data, hora) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_mov = $conn->prepare($sql_mov);
            if ($stmt_mov) {
                $stmt_mov->bind_param('iissss', $usuario_id, $new_meta_id, $tipo, $descricao, $criado_em, $horario_criado);
                $stmt_mov->execute();
                $stmt_mov->close();
            }

            $stmt->close();
            $_SESSION['status_cadastro'] = true;
            header('Location: page.php?mensagem=metaAdicionada');
            exit;
        } else {
            $stmt->close();
            $_SESSION['status_cadastro'] = false;
            header("Location: ../planejador/page.php?mensagem=ErroMeta");
            exit;
        }
    } else {
        $_SESSION['status_cadastro'] = false;
        header("Location: ../planejador/page.php?mensagem=MesmoNomeMeta");
        exit;
    }
}

$conn->close();
?>
