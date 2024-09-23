<?php
session_start();
include('../../assets/bd/conexao.php');

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'data-desc';
$usuario_id = $_SESSION['user_id'];
$order_by = 'data DESC';

$filter = isset($_GET['filtro']) ? $_GET['filtro'] : '';

switch ($filtro) {
    case 'data-asc':
        $order_by = 'data ASC';
        break;
    case 'data-desc':
        $order_by = 'data DESC';
        break;
    case 'valor-asc':
        $order_by = 'valor ASC';
        break;
    case 'valor-desc':
        $order_by = 'valor DESC';
        break;
    case 'descricao-asc':
        $order_by = 'descricao ASC';
        break;
    case 'descricao-desc':
        $order_by = 'descricao DESC';
        break;
}

$sql = "SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY $order_by";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Gerenciamento de Finanças</title>
</head>

<body class="bg-gray-100">

    <!-- Header -->
    <header class="bg-tollens p-4 flex justify-between items-center">
        <button class="bg-gray-400 text-white py-2 px-4 rounded">Voltar</button>
        <h1 class="text-white text-2xl text-center font-bold">Gerenciamento de Finanças</h1>
        <div class="space-x-2">
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Meu Perfil</button>
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Sair</button>
        </div>
    </header>

    <main class="p-6">

        <?php
        // Consultar o banco de dados para obter todas as transações
        $sql = "SELECT SUM(valor) AS total FROM transacoes WHERE usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $row = $resultado->fetch_assoc();
        $saldo = $row['total'];

        // Consultar o banco de dados para obter o total de entradas
        $sql = "SELECT SUM(valor) AS total FROM transacoes WHERE usuario_id = ? AND valor > 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $row = $resultado->fetch_assoc();
        $entradas = $row['total'];

        // Consultar o banco de dados para obter o total de saídas
        $sql = "SELECT SUM(valor) AS total FROM transacoes WHERE usuario_id = ? AND valor < 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $row = $resultado->fetch_assoc();
        $saidas = abs($row['total']); // Valor absoluto para garantir que seja positivo
        ?>

        <!--div transação-->
        <div class="flex items-center justify-center mb-8">
            <button id="modal" class="bg-tollens text-white justify-center py-2 px-4 rounded hover:bg-purple-500">
                + Nova Transação
            </button>
        </div>

        <div class="flex justify-center items-center">
            <div class="p-4 w-32 rounded-lg shadow-md text-center justify-end">
                <p class="font-bold text-tollens">SALDO</p>
                <p class="text-xl font-semibold"><?php echo number_format($saldo, 2); ?></p>
            </div>
        </div>

        <div class="bg-red">
            <!-- Entradas e Saídas -->
            <div class="flex justify-between items-center mb-8">
                <div class="bg-white p-4 rounded-lg shadow-md w-1/3 text-center">
                    <p class="font-bold text-green-600">Entradas</p>
                    <p class="text-xl font-semibold"><?php echo number_format($entradas, 2); ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md w-1/3 text-center">
                    <p class="font-bold text-red-600">Saídas</p>
                    <p class="text-xl font-semibold"><?php echo number_format($saidas, 2); ?></p>
                </div>
            </div>


            <!-- Histórico -->

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-bold mb-4">Histórico</h3>
                <div class="flex items-center mb-4">
                    <label for="filter" class="mr-2 font-semibold">Filtrar por:</label>
                    <select id="filter" class="border border-gray-300 rounded p-2">
                        <option value="data-asc" <?php echo ($filtro == 'data-asc') ? 'selected' : ''; ?>>Data (Mais antigos)</option>
                        <option value="data-desc" <?php echo ($filtro == 'data-desc') ? 'selected' : ''; ?>>Data (Mais recentes)</option>
                        <option value="valor-asc" <?php echo ($filtro == 'valor-asc') ? 'selected' : ''; ?>>Valor (Menor para maior)</option>
                        <option value="valor-desc" <?php echo ($filtro == 'valor-desc') ? 'selected' : ''; ?>>Valor (Maior para menor)</option>
                        <option value="descricao-asc" <?php echo ($filtro == 'descricao-asc') ? 'selected' : ''; ?>>Descrição (A-Z)</option>
                        <option value="descricao-desc" <?php echo ($filtro == 'descricao-desc') ? 'selected' : ''; ?>>Descrição (Z-A)</option>
                    </select>
                    <div class="filtro-nav">
                        <label for="filtroSearch"></label>
                        <input type="text" placeholder="Procurar" class="ml-4 border border-gray-300 rounded p-2 w-full max-w-xs">
                    </div>
                </div>

                <!-- Tabela de Transações -->
                <?php
                include('../../assets/bd/conexao.php');

                if (isset($_SESSION['user_id'])) {
                    $usuario_id = $_SESSION['user_id'];

                    $sql = "SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY data DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $usuario_id);
                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    // Verificar se há transações
                    if ($resultado->num_rows > 0) {
                        // Exibir as transações no histórico
                        while ($row = $resultado->fetch_assoc()) {
                            echo '<div class="bg-white p-4 rounded-lg shadow-lg">';
                            echo '<table class="w-full table-auto">';
                            echo '<tbody>';
                            echo '<tr class="border-t">';
                            echo '<td id="descricao" class="py-3 px-6 text-left">' . $row['descricao'] . '</td>';
                            echo '<td id="data" class="py-3 px-6 text-center">' . $row['data'] . '</td>';
                            echo '<td id="valor" class="py-3 px-6 text-right font-semibold">' . $row['valor'] . '</td>';
                            echo '<td class="py-3 px-6 text-right">';
                            echo '<button id="btn_editar" class="bg-purple-600 text-white py-1 px-3 rounded hover:bg-purple-500 mr-2"><a href="../../modulos/transacoes/editar_transacao.php?id=' . $row['id'] . '">Editar</a></button>';
                            echo '<button id="btn_excluir" class="bg-red-600 text-white py-1 px-3 rounded hover:bg-red-500" data-id="' . $row['id'] . '">Excluir</button>';
                            echo '</td>';
                            echo '</tr>';
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        }
                    } else {
                        echo '<li>Nenhuma transação encontrada.</li>';
                    }
                }
                ?>
            </div>
        </div>

        <form action="../transacoes/add_transacao.php" method="POST">
            <!-- Modal Overlay -->
            <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
                <div class="bg-white rounded-md shadow-lg p-8 text-center relative">
                    <h2 class="text-2xl mb-4">Nova Transação</h2>

                    <!-- Seleção do Tipo de Transação -->
                    <div class="mb-4">
                        <select name="tipo" required class="w-full p-2 border border-gray-300 rounded">
                            <option value="positivo">Transação Positiva</option>
                            <option value="negativo">Transação Negativa</option>
                        </select>
                    </div>

                    <!-- Formulário de Nova Transação -->
                    <form id="novaTransacaoForm" method="POST" action="adicionarTransacao.php">
                        <input type="text" name="descricao" placeholder="Descrição" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                        <input type="text" name="valor" placeholder="Valor" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                        <input type="date" name="data" required class="w-full p-2 mb-4 border border-gray-300 rounded">

                        <!-- Botões de Ação -->
                        <div class="flex justify-center space-x-4">
                            <button type="button" id="fecharModal" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
                            <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </form>

        <!-- Modal de Confirmação de Exclusão -->
        <div id="modalConfirmarExclusao" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-md text-center">
                <p class="mb-4">Tem certeza de que deseja excluir esta nota?</p>
                <div class="flex justify-center space-x-4">
                    <button id="confirmarExcluirNota" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Confirmar</button>
                    <button id="cancelarExcluirNota" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
                </div>
            </div>
        </div>


    </main>

    <script>
        document.getElementById('fecharModal').addEventListener('click', function() {
            document.getElementById('modal').classList.add('hidden');
        });

        // Função para mostrar o modal
        function abrirModal() {
            document.getElementById('modal').classList.remove('hidden');
        }

        // Função para fechar modal de exclusão
        document.getElementById('cancelarExcluirNota').addEventListener('click', function() {
            document.getElementById('modalConfirmarExclusao').classList.add('hidden');
        });
    </script>
</body>

</html>