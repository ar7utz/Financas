<?php
session_start();
include_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Buscar todas as metas do usuário
$sql = "SELECT * FROM planejador WHERE usuario_id = ? ORDER BY criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$metas = $result->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Metas</title>
</head>
<body>
    <?php require_once '../../assets/templates/navbar.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Suas Metas Financeiras</h1>

        <a href="./planner.php">
            <button class="bg-tollens text-white py-2 px-4 rounded hover:bg-green-500 mb-4">Criar +</button>
        </a>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($metas as $meta): ?>
                <div class="bg-white p-4 rounded shadow transition-transform transform hover:-translate-y-3 hover:shadow-lg hover:shadow-gray-500/50 cursor-help">
                    <h2 class="text-lg font-bold"> <?php echo htmlspecialchars($meta['razao']); ?> </h2>
                    <p><strong>Valor da Meta:</strong> R$ <?php echo number_format($meta['preco_meta'], 2, ',', '.'); ?></p>
                    <p><strong>Valor Atual:</strong> R$ <?php echo number_format($meta['capital'], 2, ',', '.'); ?></p>
                    <p><strong>Investimento Mensal:</strong> R$ <?php echo number_format($meta['quanto_quero_pagar_mes'], 2, ',', '.'); ?></p>
                    <a href="./exibir_meta.php?id=<?php echo $meta['id']; ?>">
                        <button class="bg-tollens text-white py-2 px-4 rounded hover:bg-green-500 mt-6">
                            Abrir
                        </button>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
