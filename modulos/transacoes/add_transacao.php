<?php
session_start();

include '../../assets/bd/conexao.php';

date_default_timezone_set('America/Sao_Paulo'); //vou resolver isso 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id'];

    $descricao = trim($_POST['descricao']);
    $valor_raw = trim($_POST['valor']);
    $data = $_POST['data'];
    $categoria_id = $_POST['categoria_id'];

    $tipo = $_POST['tipo']; // positivo ou negativo

    // Normaliza o valor recebido (aceita formatos: "3.000,00", "3000", "3000.00", "1.234")
    $valor_raw = str_replace(['R$', ' '], ['', ''], $valor_raw);

    if (strpos($valor_raw, ',') !== false) {
        // Formato BR: milhares com '.' e decimal com ','
        $valor_normalized = str_replace('.', '', $valor_raw);
        $valor_normalized = str_replace(',', '.', $valor_normalized);
    } else {
        // Formato EN ou inteiro: remove quaisquer vírgulas residuais
        $valor_normalized = str_replace(',', '', $valor_raw);
    }

    // Remove tudo que não seja dígito, sinal negativo ou ponto
    $valor_normalized = preg_replace('/[^\d\.\-]/', '', $valor_normalized);

    // Converte para float
    $valor = (float) $valor_normalized;

    if (empty($descricao) || $valor === 0.0 || empty($data) || empty($categoria_id)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Todos os campos são obrigatórios.']);
        exit;
    }

    if ($tipo === 'negativo') {
        if ($valor > 0) $valor = -$valor;
        $tipo_db = 2; // Tipo 2 para transações negativas
    } else {
        $tipo_db = 1; // Tipo 1 para transações positivas
    }

    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $sql = "INSERT INTO transacoes (descricao, valor, data, tipo, usuario_id, categoria_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sdsiii', $descricao, $valor, $data, $tipo_db, $usuario_id, $categoria_id);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: ../dashboard/dashboard.php?mensagem=sucesso");
        exit;
    } else {
        header("location: ../dashboard/dasboard.php?mensagem=erroTransacao");
        exit;
    }
    
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método de solicitação inválido. Use POST.']);
}

$conn->close();

?>
<script src="../../assets/js/main.js"></script>
