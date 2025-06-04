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
    <link rel="shortcut icon" href="../../assets/logo/cube-logo.svg" type="image/x-icon">

    <link rel="stylesheet" href="../../node_modules/toastify-js/src/toastify.css">
    <script src="../../node_modules/toastify-js/src/toastify.js"></script>

    <title>Finstash - Metas</title>
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
                <div class="bg-white p-4 rounded shadow transition-transform transform hover:-translate-y-3 hover:shadow-lg hover:shadow-gray-500/50">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-lg font-bold"><?php echo htmlspecialchars($meta['razao']); ?></h2>
                        <button 
                            class="flex bg-tollens text-white py-2 px-4 rounded hover:bg-slate-500"
                            onclick="abrirModalExcluirMeta(<?php echo $meta['id']; ?>)"
                            type="button"
                        >
                            Excluir meta
                        </button>
                    </div>
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

            <?php
                //toastify para mensagem adicionada com sucesso
                if (isset($_GET['mensagem'])) {
                    echo "<script>
                        window.onload = function() {
                            switch ('" . $_GET['mensagem'] . "') {
                                case 'metaAdicionada':
                                    Toastify({
                                        text: 'Meta adicionada com sucesso!',
                                        duration: 3000,
                                        close: true,
                                        gravity: 'top',
                                        position: 'right',
                                        backgroundColor: '#28a745', // verde
                                    }).showToast();
                                    break;
                                case 'metaExcluida':
                                    Toastify({
                                        text: 'Meta excluída com sucesso!',
                                        duration: 3000,
                                        close: true,
                                        gravity: 'top',
                                        position: 'right',
                                        backgroundColor: '#dc3545', // vermelho
                                    }).showToast();
                                    break;
                                case 'metaEditada':
                                    Toastify({
                                        text: 'Meta editada com sucesso!',
                                        duration: 3000,
                                        close: true,
                                        gravity: 'top',
                                        position: 'right',
                                        backgroundColor: '#007bff', // azul
                                    }).showToast();
                                    break;
                                case 'erroTransacao':
                                    Toastify({
                                        text: 'Erro ao adicionar a transação!',
                                        duration: 3000,
                                        close: true,
                                        gravity: 'top',
                                        position: 'right',
                                        backgroundColor: '#dc3545', // cor vermelha para erro
                                    }).showToast();
                                    break;
                                case 'SuceEdit':
                                    Toastify({
                                        text: 'Sucesso ao editar o item',
                                        duration: 3000,
                                        close: true,
                                        gravity: 'top',
                                        position: 'right',
                                        backgroundColor: '#dc3545', // cor amarela para aviso
                                    }).showToast();
                                    break;
                                case 'excluirTransacao':
                                    Toastify({
                                        text: 'Transação excluída com sucesso',
                                        duration: 3000,
                                        close: true,
                                        gravity: 'top',
                                        position: 'right',
                                        backgroundColor: '#dc3545', // cor amarela para aviso
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
                            url.searchParams.delete('mensagem');
                            window.history.replaceState(null, '', url);
                        }
                    </script>";
                } 
            ?>
            
        </div>

        <!-- Modal de Confirmação de Exclusão (único na página) -->
        <div id="modalExcluirMeta" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-md text-center">
                <p class="mb-4">Tem certeza de que deseja excluir esta meta?</p>
                <div class="flex justify-center space-x-4">
                    <button id="confirmarExcluirNota" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Confirmar</button>
                    <button id="cancelarExcluirNota" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
                </div>
            </div>
        </div>
    </div>


    <script>
    let metaIdParaExcluir = null;

    // Função para abrir o modal e guardar o id da meta
    function abrirModalExcluirMeta(id) {
        metaIdParaExcluir = id;
        document.getElementById('modalExcluirMeta').classList.remove('hidden');
    }

    // Função para cancelar a exclusão e fechar o modal
    document.getElementById('cancelarExcluirNota').addEventListener('click', function() {
        document.getElementById('modalExcluirMeta').classList.add('hidden');
        metaIdParaExcluir = null;
    });

    // Função para confirmar a exclusão
    document.getElementById('confirmarExcluirNota').addEventListener('click', function() {
        if (metaIdParaExcluir) {
            window.location.href = `../planejador/excluir_meta.php?id=${metaIdParaExcluir}`;
        }
    });

    // Fechar modal clicando fora da caixa
    document.getElementById('modalExcluirMeta').addEventListener('click', function(event) {
        if (event.target === this) {
            this.classList.add('hidden');
            metaIdParaExcluir = null;
        }
    });
    </script>

</body>
</html>
