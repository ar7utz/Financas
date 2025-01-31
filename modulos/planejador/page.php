<?php
session_start();
include_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$usuario_id = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT * FROM planejador WHERE usuario_id = ? ORDER BY criado_em DESC');
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">

    <link rel="stylesheet" href="../../node_modules/toastify-js/src/toastify.css">
    <script src="../../node_modules/toastify-js/src/toastify.js"></script>

    <title>Planejador</title>
</head>

<?php require_once '../../assets/templates/navbar.php'; ?>

<body class=" min-h-screen">

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Meus Planejamentos/Metas</h1>
        <a href="../planejador/planner.php"><button class="bg-tollens text-white py-2 px-4 rounded hover:bg-green-500">+ Criar um </button></a>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 justify-center">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($planejamento = $result->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">
                            <?= htmlspecialchars($planejamento['razao']) ?>
                        </h2>
                        <p class="text-gray-600">
                            <strong>Preço da Meta:</strong> $<?= number_format($planejamento['preco_meta'], 2, ',', '.') ?>
                        </p>
                        <p class="text-gray-600">
                            <strong>Capital Atual:</strong> $<?= number_format($planejamento['capital'], 2, ',', '.') ?>
                        </p>
                        <p class="text-gray-600">
                            <strong>Tempo para Pagar:</strong> <?= $planejamento['quanto_tempo_quero_pagar'] ?> meses
                        </p>
                        <p class="text-gray-600">
                            <strong>Pagamento Mensal:</strong> $<?= number_format($planejamento['quanto_quero_pagar_mes'], 2, ',', '.') ?>
                        </p>
                        <p class="text-gray-500 text-sm mt-2">
                            <strong>Criado em:</strong> <?= date('d/m/Y H:i', strtotime($planejamento['criado_em'])) ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-700 text-center col-span-3">Você ainda não criou nenhum planejamento.</p>
            <?php endif; ?>
        </div>

        <?php // Notificações do Toastify
        if (isset($_GET['mensagem'])) {
            echo "<script>
                window.onload = function() {
                    switch ('" . $_GET['mensagem'] . "') {
                        case 'MetaCriadoSucesso':
                            Toastify({
                                text: 'Meta criada com sucesso!',
                                duration: 3000,
                                close: true,
                                gravity: 'bottom',
                                position: 'right',
                                backgroundColor: '#28a745', // cor verde para sucesso
                            }).showToast();
                            break;
                        case 'ErroMeta':
                            Toastify({
                                text: 'Erro ao criar a meta',
                                duration: 3000,
                                close: true,
                                gravity: 'top',
                                position: 'right',
                                backgroundColor: '#28a745',
                            }).showToast();
                            break;
                        case 'MesmoNomeMeta':
                        Toastify({
                            text: 'Já existe uma meta com esse nome!',
                            duration: 3000,
                            close: true,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#28a745',
                        }).showToast();
                        break;
                        default:
                            Toastify({
                                text: 'Ação desconhecida!',
                                duration: 3000,
                                close: true,
                                gravity: 'top',
                                position: 'right',
                                backgroundColor: '#6c757d', // cor cinza para ação desconhecida
                            }).showToast();
                            break;
                    }
                            
                    // Limpar a URL após exibir o Toastify
                    const url = new URL(window.location);
                    url.searchParams.delete('mensagem'); // Remove o parâmetro 'mensagem'
                    window.history.replaceState(null, '', url); // Atualiza a URL sem recarregar a página
                }
            </script>";
            }
        ?>
    </div>
</body>
</html>
