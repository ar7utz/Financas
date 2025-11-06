<?php
session_start();
include ('../../assets/bd/conexao.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Recupera o ID da meta
$meta_id = $_GET['id'] ?? $_POST['meta_id'] ?? null;
if (!$meta_id) {
    die('Meta não informada.');
}

// Busca os dados da meta
$sql = "SELECT * FROM planejador WHERE usuario_id = ? AND id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $usuario_id, $meta_id);
$stmt->execute();
$result = $stmt->get_result();
$meta = $result->fetch_assoc();

if (!$meta) {
    die('Meta não encontrada.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function parseBrazilianFloat($str) {
        $s = trim((string)$str);
        if ($s === '') return 0.0;
        $s = str_replace("\xc2\xa0", '', $s);
        $s = str_replace([' ', "\t"], '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^\d\.]/', '', $s);
        return $s === '' ? 0.0 : floatval($s);
    }

    $raw_valor = $_POST['valor_aplicado'] ?? '0';
    $valor_aplicado = parseBrazilianFloat($raw_valor);

    $descricao = $_POST['descricao'] ?? '';
    $data = date('Y-m-d');
    $hora = $_POST['hora_local'] ?? date('H:i:s');

    if ($valor_aplicado > 0) {
        $tipo = 'aplicacao';
        $descricao_mov = $descricao ? $descricao : "Aplicação de R$ " . number_format($valor_aplicado, 2, ',', '.');
        $sql_mov = "INSERT INTO movimentacoes (usuario_id, meta_id, tipo, descricao, valor, data, hora) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_mov = $conn->prepare($sql_mov);
        if ($stmt_mov) {
            $stmt_mov->bind_param('iissdss', $usuario_id, $meta_id, $tipo, $descricao_mov, $valor_aplicado, $data, $hora);
            $stmt_mov->execute();
            $stmt_mov->close();
        }
    }

    $sql_sum = "SELECT COALESCE(SUM(valor),0) AS total_aplicado FROM movimentacoes WHERE usuario_id = ? AND meta_id = ?";
    $stmt_sum = $conn->prepare($sql_sum);
    $stmt_sum->bind_param('ii', $usuario_id, $meta_id);
    $stmt_sum->execute();
    $res_sum = $stmt_sum->get_result();
    $row_sum = $res_sum->fetch_assoc();
    $stmt_sum->close();

    $total_aplicado = floatval($row_sum['total_aplicado']);

    $preco_meta = floatval(str_replace(',', '.', $meta['preco_meta']));
    $capital_inicial = floatval(str_replace(',', '.', $meta['capital']));
    $tempo_desejado = isset($meta['quanto_tempo_quero_pagar']) ? intval($meta['quanto_tempo_quero_pagar']) : 0;
    $valor_mensal_desejado = isset($meta['quanto_quero_pagar_mes']) ? floatval($meta['quanto_quero_pagar_mes']) : 0.0;

    $valor_restante = max($preco_meta - $capital_inicial - $total_aplicado, 0);

    $novo_valor_mensal = $valor_mensal_desejado;
    $novo_tempo = $tempo_desejado;

    if ($valor_mensal_desejado > 0) {
        $novo_tempo = $valor_restante > 0 ? (int) ceil($valor_restante / $valor_mensal_desejado) : 0;
    } else {
        $novo_tempo = $tempo_desejado;
    }

    if ($novo_tempo < 0) $novo_tempo = 0;
    if (!is_finite($novo_valor_mensal) || $novo_valor_mensal < 0) $novo_valor_mensal = 0.0;

    $sql_update_meta = "UPDATE planejador SET quanto_quero_pagar_mes = ?, quanto_tempo_quero_pagar = ? WHERE id = ? AND usuario_id = ?";
    $stmt_up = $conn->prepare($sql_update_meta);
    if ($stmt_up) {
        $stmt_up->bind_param('diii', $novo_valor_mensal, $novo_tempo, $meta_id, $usuario_id);
        $stmt_up->execute();
        $stmt_up->close();
    }

    header("Location: exibir_meta.php?id=$meta_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Editar Meta</title>
</head>
<body class="bg-gray-100">
    <?php require_once '../../assets/templates/navbar.php'; ?>

    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold text-center mb-6">Editar Meta: <span class="text-blue-600"><?php echo htmlspecialchars($meta['razao']); ?></span></h1>
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
            <input type="hidden" name="meta_id" value="<?php echo $meta_id; ?>">
            <div class="mb-4">
                <label class="block font-semibold mb-2" for="valor_aplicado">Valor aplicado a mais (R$):</label>
                <input type="number" step="0.01" min="0" name="valor_aplicado" id="valor_aplicado" class="w-full border rounded px-3 py-2" data-mascara-valor required>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-2" for="descricao">Descrição (opcional):</label>
                <input type="text" name="descricao" id="descricao" class="w-full border rounded px-3 py-2">
            </div>
            <input type="hidden" name="hora_local" id="hora_local">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">Salvar Aplicação</button>
        </form>
    </div>

    <script>
        function mascaraValor(input) {
            let v = input.value.replace(/\D/g, '');
            v = (v / 100).toFixed(2) + '';
            v = v.replace('.', ',');
            v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = v;
        }

        document.querySelectorAll('input[data-mascara-valor]').forEach(function(input) {
            input.type = 'text'; // Troca para text para permitir máscara
            input.addEventListener('input', function() {
                mascaraValor(this);
            });
            mascaraValor(input);
        });
    </script>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const agora = new Date();
            const horaLocal = agora.toLocaleTimeString('pt-BR', { hour12: false });
            document.getElementById('hora_local').value = horaLocal;
        });
        </script>
</body>
</html>