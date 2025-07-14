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

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor_aplicado = floatval($_POST['valor_aplicado'] ?? 0);
    $descricao = $_POST['descricao'] ?? '';
    $data = date('Y-m-d');
    $hora = $_POST['hora_local'] ?? date('H:i:s');

    // Atualiza o capital da meta (opcional, se quiser somar o valor aplicado)
    if ($valor_aplicado > 0) {
        $novo_capital = $meta['capital'] + $valor_aplicado;
        $sql_update = "UPDATE planejador SET capital = ? WHERE id = ? AND usuario_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('dii', $novo_capital, $meta_id, $usuario_id);
        $stmt_update->execute();
    }

    // Salva a movimentação
    $tipo = 'aplicacao';
    $descricao_mov = $descricao ? $descricao : "Aplicação de R$ " . number_format($valor_aplicado, 2, ',', '.');
    $sql_mov = "INSERT INTO movimentacoes (usuario_id, meta_id, tipo, descricao, valor, data, hora) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_mov = $conn->prepare($sql_mov);
    $stmt_mov->bind_param('iissdss', $usuario_id, $meta_id, $tipo, $descricao_mov, $valor_aplicado, $data, $hora);
    $stmt_mov->execute();

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

    <script> /*máscara de valor*/
        function mascaraValor(input) {
            let v = input.value.replace(/\D/g, ''); // Remove tudo que não for dígito
            v = (v / 100).toFixed(2) + ''; // Divide por 100 e fixa 2 casas decimais
            v = v.replace('.', ','); // Troca ponto por vírgula
            v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.'); // Adiciona pontos a cada milhar
            input.value = v;
        }

        // Aplica a máscara em todos os inputs do tipo number (ou text, se você trocar)
        document.querySelectorAll('input[data-mascara-valor]').forEach(function(input) {
            input.type = 'text'; // Troca para text para permitir máscara
            input.addEventListener('input', function() {
                mascaraValor(this);
            });
            // Opcional: formata ao carregar valor inicial
            mascaraValor(input);
        });
    </script>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const agora = new Date();
            // Formata para HH:MM:SS
            const horaLocal = agora.toLocaleTimeString('pt-BR', { hour12: false });
            document.getElementById('hora_local').value = horaLocal;
        });
        </script>
</body>
</html>