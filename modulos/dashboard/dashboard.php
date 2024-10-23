<?php
session_start();
include_once('../../assets/bd/conexao.php');


$base_url = "../../../Financas"; //url base

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'data-desc';
$usuario_id = $_SESSION['user_id']; // Usuário logado

$order_by = '';
// Define a ordenação com base no filtro selecionado
switch ($filtro) {
    case 'valor-positivo':
        $order_by = 'tipo = 1';
        break;
    case 'valor-negativo':
        $order_by = 'tipo = 2';
        break;
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
    default:
        $order_by = 'data DESC'; // Valor padrão se o filtro for inválido
        break;
}

// Consulta ao banco de dados com o filtro aplicado
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
    <link rel="stylesheet" href="../../node_modules/toastify-js/src/toastify.css">
    <script src="../../node_modules/toastify-js/src/toastify.js"></script>
    <title>Finstash - Gerenciamento de Finanças Pessoal</title>
</head>

<body class="bg-gray-100">

    <!-- Header -->
    <?php include_once('../../assets/templates/navbar.php') ?>

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
            <button id="abrirModalAddTransacao" class="bg-tollens text-white justify-center py-2 px-4 rounded hover:bg-purple-500">
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
                    <!--Filtro-->
                    <form id="filterForm" method="GET" action="" onchange="document.getElementById('filterForm').submit()">
                        <label for="filter" class="mr-2 font-semibold">Filtrar por:</label>
                        <select id="filter" name="filtro" class="border border-gray-300 rounded p-2">
                            <option value="data-asc" <?php echo ($filtro == 'data-asc') ? 'selected' : ''; ?>>Data (Mais antigos)</option>
                            <option value="data-desc" <?php echo ($filtro == 'data-desc') ? 'selected' : ''; ?>>Data (Mais recentes)</option>
                            <option value="valor-asc" <?php echo ($filtro == 'valor-asc') ? 'selected' : ''; ?>>Valor (Menor para maior)</option>
                            <option value="valor-desc" <?php echo ($filtro == 'valor-desc') ? 'selected' : ''; ?>>Valor (Maior para menor)</option>
                            <option value="valor-positivo" <?php echo ($filtro == 'valor-positivo') ? 'selected' : ''; ?>>Valor Positivo</option>
                            <option value="valor-negativo" <?php echo ($filtro == 'valor-negativo') ? 'selected' : ''; ?>>Valor Negativo</option>
                            <option value="descricao-asc" <?php echo ($filtro == 'descricao-asc') ? 'selected' : ''; ?>>Descrição (A-Z)</option>
                            <option value="descricao-desc" <?php echo ($filtro == 'descricao-desc') ? 'selected' : ''; ?>>Descrição (Z-A)</option>
                        </select>
                    </form>
                        
                        <div class="ml-4">
                            <input type="text" id="filtroSearch" name="filtroSearch" placeholder="Procurar" value="<?php echo isset($_GET['filtroSearch']) ? $_GET['filtroSearch'] : ''; ?>" class="border border-gray-300 rounded p-2 w-full max-w-xs">
                        </div>
                        
                        <button type="submit" class=" ml-4 bg-tollens text-white px-4 py-2 rounded hover:bg-purple-500">Filtrar</button>
                </div>

                <!-- Tabela de Transações -->
                <?php
                include('../../assets/bd/conexao.php');

                if (isset($_SESSION['user_id'])) {
                    $usuario_id = $_SESSION['user_id'];

                    $sql = "SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY $order_by";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $usuario_id);
                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    // Verifica se há transações
                    if ($resultado->num_rows > 0) {
                        // Exibe um título das colunas
                        echo '<div class="grid grid-cols-4 gap-4 items-center mb-2">';
                        echo '<div class="col-span-1 text-center font-bold">Descrição</div>';
                        echo '<div class="col-span-1 text-center font-bold">Data</div>';
                        echo '<div class="col-span-1 text-center font-bold">Valor</div>';
                        echo '<div class="col-span-1 text-center font-bold">Ações</div>';
                        echo '</div>';
                        // Exibir as transações no histórico

                        while ($row = $resultado->fetch_assoc()) { //loop para tratar a data e formatar corretamente
                            $data_original = $row['data'];

                            $data = DateTime::createFromFormat('Y-m-d', $data_original);

                            if ($data !== false) {
                                $data_formatada = $data->format('d/m/Y');
                            } else {
                                $data_formatada = "Data inválida";
                            }
                            echo '<div class="bg-white p-4 rounded-lg shadow-lg mb-4">';
                            echo '<div class="grid grid-cols-4 gap-4 items-center">';
                            echo '<div class="col-span-1 w-80 text-left truncate break-normal py-3 px-6">' . $row['descricao'] . '</div>';
                            echo '<div class="col-span-1 text-center truncate py-3 px-6">' . $data_formatada . '</div>';
                            echo '<div class="col-span-1 text-center font-semibold truncate py-3 px-6">' . $row['valor'] . '</div>';
                            echo '<div class="col-span-1 flex justify-end space-x-2 py-3 px-6">';
                            echo '<a href="#" onclick="abrirModalEditar(' . $row['id'] . ', \'' . $row['descricao'] . '\', \'' . $row['valor'] . '\', \'' . $row['data'] . '\', \'' . $row['tipo'] . '\', \'' . $row['categoria_id'] .'\')"><button id="btn_editar" class="bg-tollens text-white py-1 px-3 rounded hover:bg-purple-500">Editar</button></a>';
                            echo '<a href="#" onclick="abrirModalExcluir(' . $row['id'] . ')"> <button class="bg-red-600 text-white py-1 px-3 rounded hover:bg-red-500" data-id="' . $row['id'] . '">Excluir</button></a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<li>Nenhuma transação encontrada.</li>';
                    }
                }
                
                //toastify para mensagem adicionada com sucesso
                if (isset($_GET['mensagem'])) {
                    echo "<script>
                        window.onload = function() {
                            switch ('" . $_GET['mensagem'] . "') {
                                case 'sucesso':
                                    Toastify({
                                        text: 'Transação adicionada com sucesso!',
                                        duration: 3000,
                                        close: true,
                                        gravity: 'top',
                                        position: 'right',
                                        backgroundColor: '#28a745', // cor verde para sucesso
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
                        }
                    </script>";
                }
                ?>
            </div>
        </div>

        <!-- MODAL -->


        <form action="../transacoes/add_transacao.php" method="POST">
            <!-- Modal para adicionar nova transação -->
            <div id="AddTransacaoModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
                <div class="bg-white rounded-md shadow-lg p-8 text-center relative">
                    <h2 class="text-2xl mb-4">Nova Transação</h2>

                    <!-- Formulário de Nova Transação -->
                    <form id="novaTransacaoForm" method="POST" action="../transacoes/add_transacao.php">
                        <fieldset class="mb-4">
                            <label class="block mb-2 cursor-pointer">
                                <input type="radio" name="tipo" value="positivo" required> Transação Positiva
                            </label>
                            <label class="block cursor-pointer">
                                <input type="radio" name="tipo" value="negativo" required> Transação Negativa
                            </label>
                        </fieldset>
                        <input type="text" name="descricao" placeholder="Descrição" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                        <input type="text" name="valor" placeholder="Valor" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                        <input type="date" name="data" required class="w-full p-2 mb-4 border border-gray-300 rounded">

                        <?php
                            $sql = "SELECT id, nome_categoria FROM categoria";
                            $result = $conn->query($sql);
                        ?>
                        <select name="categoria_id" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                            <option value="" disabled selected>Selecionar Categoria</option>
                                <?php
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "'>" . $row['nome_categoria'] . "</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>Nenhuma categoria encontrada</option>";
                                }
                                ?>
                        </select>

                        <input type="hidden" name="timezone" id="timezone">

                        <div class="flex justify-center space-x-4">
                            <button type="button" id="fecharModalAdd" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
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

        <?php
        include_once '../../assets/bd/conexao.php';

        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            // Query para buscar os dados da transação
            $query = "SELECT * FROM transacoes WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $transacao = $result->fetch_assoc();
                echo json_encode($transacao);
            } else {
                echo json_encode(['error' => 'Transação não encontrada']);
            }
        }
        ?>

        <!-- Modal editar transação -->
        <form action="../transacoes/editar_transacao.php" method="POST">
            <!-- Modal Overlay -->
            <div id="modalEditarTransacao" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
                <div class="bg-white rounded-md shadow-lg p-8 text-center relative">
                    <h2 class="text-2xl mb-4">Editar transação</h2>

                    <!-- Formulário de Edição -->
                    <form id="editarTransacaoForm" method="POST" action="../transacoes/editar_transacao.php">
                        <input type="hidden" id="idEditar" name="id" value="<?php echo $transacao_id; ?>">
                        <input type="text" id="descricaoEditar" name="descricao" placeholder="Descrição" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                        <input type="text" id="valorEditar" name="valor" placeholder="Valor" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                        <input type="date" id="dataEditar" name="data" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                        
                        <select id="categoriaEditar" name="categoria_id" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                            <option value="" disabled selected>Selecionar Categoria</option>
                            <?php
                                // Consultar categorias do banco de dados
                                $sql = "SELECT id, nome_categoria FROM categoria";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "'>" . $row['nome_categoria'] . "</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>Nenhuma categoria encontrada</option>";
                                }
                            ?>
                        </select>

                        <div class="flex justify-center space-x-4">
                            <button type="button" id="fecharModalEditar" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
                            <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </form>

        <button id="notifyBtn" class="bg-tollens text-white p-4 rounded">Mostrar Notificação</button>


    </main>

    <script>//Funções para abrir e fechar o modal de adicionar transação
        document.getElementById('abrirModalAddTransacao').addEventListener('click', function() {
            document.getElementById('AddTransacaoModal').classList.remove('hidden');
        });

        document.getElementById('fecharModalAdd').addEventListener('click', function() {
            document.getElementById('AddTransacaoModal').classList.add('hidden');
        });
    </script>

    <script>//Funções para abrir e fechar o modal de edição
        function abrirModalEditar(id, descricao, valor, data, categoria_id) {
            // Definindo os valores no modal de edição
            document.getElementById('idEditar').value = id;
            document.getElementById('descricaoEditar').value = descricao;
            document.getElementById('valorEditar').value = valor;

            // Formatação da data no formato YYYY-MM-DD
            const dataFormatada = new Date(data).toISOString().split('T')[0];
            document.getElementById('dataEditar').value = dataFormatada;

            document.getElementById('categoriaEditar').value = categoria_id;

            // Abrir o modal de edição
            document.getElementById('modalEditarTransacao').classList.remove('hidden');
        }

        document.getElementById('fecharModalEditar').addEventListener('click', function() {
            document.getElementById('modalEditarTransacao').classList.add('hidden');
        });
    </script>

    <script>//Funções para abrir e fechar o modal de confirmação de exclusão
        function abrirModalExcluir(id) {
            document.getElementById('confirmarExcluirNota').onclick = function() {
                window.location.href = `../transacoes/excluir_transacao.php?id=${id}`;
            };
            document.getElementById('modalConfirmarExclusao').classList.remove('hidden');
        }
        //Função para cancelar a exclusão e fechar o modal
        document.getElementById('cancelarExcluirNota').addEventListener('click', function() {
            document.getElementById('modalConfirmarExclusao').classList.add('hidden');
        });
    </script>

    <script>//Fechar modais clicando fora da caixa
        window.addEventListener('click', function(event) {
            const modais = ['AddTransacaoModal', 'modalEditarTransacao', 'modalConfirmarExclusao'];
            modais.forEach(function(modalId) {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        });
    </script>

    <script> //Detecta o fuso horário local e preenche o campo oculto
        document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;
    </script>

    <script> //toastify
        import Toastify from '../../node_modules/toastify-js/src/toastify.js';  
        document.getElementById('notifyBtn').addEventListener('click', function() {
            Toastify({
                text: "Esta é uma notificação com Toastify!",
                duration: 3000, // duração em milissegundos
                close: true, // botão de fechar
                gravity: "top", // "top" ou "bottom"
                position: "right", // "left", "right", ou "center"
                backgroundColor: "#1133A6", // Cor personalizada
            }).showToast();
        });
    </script>


</body>

</html>