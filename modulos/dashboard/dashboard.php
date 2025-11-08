<?php
session_start();
include_once('../../assets/bd/conexao.php');

$base_url = "../../../Financas"; //url base 

$filtro = isset($_POST['filtro']) ? $_POST['filtro'] : 'data-desc';
$usuario_id = $_SESSION['user_id']; // Usuário logado

$order_by = '';
//$where_clause = '';

// Define a ordenação com base no filtro selecionado
switch ($filtro) {
    case 'valor-positivo':
        $order_by = "tipo = 1";
        break;
    case 'valor-negativo':
        $order_by = "tipo = 2";
        break;
    case 'categoria':
        $order_by = 'categoria_id';
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

// Obter os meses das transações
$sql_meses = "SELECT DISTINCT DATE_FORMAT(data, '%Y-%m') AS mes FROM transacoes WHERE usuario_id = ? ORDER BY mes DESC";
$stmt_meses = $conn->prepare($sql_meses);
$stmt_meses->bind_param('i', $usuario_id);
$stmt_meses->execute();
$resultado_meses = $stmt_meses->get_result();
$meses = $resultado_meses->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../assets/css/output.css">
    <link rel="stylesheet" href="../../node_modules/toastify-js/src/toastify.css">
    <script src="../../node_modules/toastify-js/src/toastify.js"></script>

    <!-- jsPDF para PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <!-- SheetJS para Excel -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <!-- Ícone -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

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
        <div class="flex flex-col gap-2 mb-4">
            <!-- Saldo -->
            <div class="order-1 bg-white p-4 rounded-lg shadow-md text-center w-full mb-2">
                <p class="font-bold text-tollens">SALDO</p>
                <p class="text-xl font-semibold"><?php echo 'R$ '. number_format($saldo, 2); ?></p>
            </div>
            <div class="order-2 flex w-full gap-2 lg:w-auto">
                <!-- Entradas -->
                <div class="bg-white p-4 rounded-lg shadow-md w-1/2 text-center flex-1 lg:w-32 lg:mr-2">
                    <p class="font-bold text-green-600">Entradas</p>
                    <p class="text-xl font-semibold"><?php echo 'R$ '. number_format($entradas, 2); ?></p>
                </div>
                <!-- Saídas -->
                <div class="bg-white p-4 rounded-lg shadow-md w-1/2 text-center flex-1 lg:w-32 lg:ml-2">
                    <p class="font-bold text-red-600">Saídas</p>
                    <p class="text-xl font-semibold"><?php echo 'R$ '. number_format($saidas, 2); ?></p>
                </div>
            </div>
        </div>

            <!-- Histórico -->
            <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex flex-row relative justify-between items-center">
                <div class="relative">
                    <button id="btnExportar" class="bg-tollens text-white py-2 px-4 rounded hover:bg-green-500 mb-4">Exportar ▼</button>
                    <!-- dropdown posicionado abaixo do botão -->
                    <div id="exportOptions" class="hidden absolute right-0 top-full mt-2 w-48 bg-white border rounded shadow-md z-10">
                        <button onclick="exportarPDF()" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Exportar PDF</button>
                        <button onclick="exportarExcel()" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Exportar Excel</button>
                    </div>
                </div>

                <div class="flex justify-center mb-4">
                    <button id="abrirModalAddTransacao" class="bg-tollens text-white py-2 px-4 rounded-lg hover:bg-purple-500 w-full max-w-xs">
                        + Nova Transação
                    </button>
                </div>
            </div>

            <h3 class="text-2xl font-bold mb-4 text-center">Histórico</h3>

            <!-- DIV FILTRO -->
            <div class="p-4 mb-8 mt-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between cursor-pointer lg:hidden" onclick="toggleFiltros()">
                    <h3 class="text-lg font-bold">Filtros</h3>
                    <span id="iconeFiltros" class=" text-2xl transition-transform duration-200"><i class="fa fa-chevron-down"></i></span>
                </div>

                <div class="hidden lg:flex lg:items-center lg:justify-start">
                    <h3 class="text-lg font-bold">Filtros</h3>
                </div>

                <div id="filtrosContainer" class="hidden mt-2 flex-col gap-2 lg:flex lg:flex-row lg:justify-center">
                    <div class="w-full lg:flex-row">
                        <div class="w-full lg:w-auto">
                            <label for="Procurar" class="w-full lg:w-auto font-semibold">Procurar:</label>
                            <input type="text" id="filtroSearch" name="filtroSearch" placeholder="Procurar" class="border border-gray-300 rounded p-2 w-full mb-1">
                        </div>
                    </div>

                    <form id="filterForm" method="POST" action="" onchange="document.getElementById('filterForm').submit()" class="w-full lg:w-auto">
                        <label for="filter" class="block font-semibold">Filtrar por:</label>
                        <select id="filter" name="filtro" class="border border-gray-300 rounded p-2 w-full lg:w-auto">
                            <option value="data-desc" <?php echo ($filtro == 'data-desc') ? 'selected' : ''; ?>>Data (Mais recentes)</option>
                            <option value="data-asc" <?php echo ($filtro == 'data-asc') ? 'selected' : ''; ?>>Data (Mais antigos)</option>
                            <option value="valor-desc" <?php echo ($filtro == 'valor-desc') ? 'selected' : ''; ?>>Valor (Maior para menor)</option>
                            <option value="valor-asc" <?php echo ($filtro == 'valor-asc') ? 'selected' : ''; ?>>Valor (Menor para maior)</option>
                            <option value="categoria" <?php echo ($filtro == 'categoria') ? 'selected' : ''; ?>> Categoria </option>
                            <option value="descricao-asc" <?php echo ($filtro == 'descricao-asc') ? 'selected' : ''; ?>>Descrição (A-Z)</option>
                            <option value="descricao-desc" <?php echo ($filtro == 'descricao-desc') ? 'selected' : ''; ?>>Descrição (Z-A)</option>
                        </select>
                    </form>

                    <div class="w-full flex flex-col gap-2 lg:flex-row lg:items-center lg:gap-4 lg:w-auto">
                        <div class="w-full lg:w-auto">
                            <label for="ano" class="block font-semibold">Filtrar por Ano:</label>
                            <select id="ano" name="ano" class="border border-gray-300 rounded p-2 w-full lg:w-auto">
                                <option value="">Selecionar Ano</option>
                                <?php 
                                $sql_anos = "SELECT DISTINCT YEAR(data) AS ano FROM transacoes WHERE usuario_id = ? ORDER BY ano DESC";
                                $stmt_anos = $conn->prepare($sql_anos);
                                $stmt_anos->bind_param('i', $usuario_id);
                                $stmt_anos->execute();
                                $resultado_anos = $stmt_anos->get_result();
                                $anos = $resultado_anos->fetch_all(MYSQLI_ASSOC);
                                foreach ($anos as $ano): ?>
                                    <option value="<?php echo $ano['ano']; ?>"><?php echo $ano['ano']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="w-full lg:w-auto">
                            <label for="mes" class="block font-semibold">Mês:</label>
                            <select id="mes" name="mes" class="border border-gray-300 rounded p-2 w-full lg:w-auto" disabled>
                                <option value="">Selecionar Mês</option>
                            </select>
                        </div>
                    </div>

                    <div class="w-full lg:w-auto">
                        <label for="FiltroCategoria" class="block font-semibold">Categoria:</label>
                        <div class="flex items-center gap-2">
                            <select id="FiltroCategoria" name="FiltroCategoria" class="border border-gray-300 rounded p-2 w-full lg:w-auto">
                                <option value="">Selecionar Categoria</option>
                                <?php 
                                // Busca as categorias que possuem transações
                                $sql_categoria = "
                                    SELECT DISTINCT c.id, c.nome_categoria 
                                    FROM categoria c
                                    JOIN transacoes t ON c.id = t.categoria_id
                                    WHERE t.usuario_id = ?
                                    ORDER BY c.nome_categoria ASC
                                ";

                                $stmt_categoria = $conn->prepare($sql_categoria);
                                $stmt_categoria->bind_param('i', $usuario_id);
                                $stmt_categoria->execute();
                                $resultado_categoria = $stmt_categoria->get_result();

                                while ($categoria = $resultado_categoria->fetch_assoc()): ?>
                                    <option value="<?php echo $categoria['id']; ?>">
                                        <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <!-- Botão X para limpar filtros -->
                            <button id="limparFiltrosBtn" type="button" title="Limpar filtros"
                                class="ml-2 inline-flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-200">
                                &times;
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Transações -->
            <div id="transacoesContainer">
                <?php
                include('../../assets/bd/conexao.php');

                if (isset($_SESSION['user_id'])) {
                    $usuario_id = $_SESSION['user_id'];

                    // Paginação
                    $itensPorPagina = 10;
                    $paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
                    $offset = ($paginaAtual - 1) * $itensPorPagina;

                    // Conta total de transações
                    $sqlTotal = "SELECT COUNT(*) as total FROM transacoes WHERE usuario_id = ?";
                    $stmtTotal = $conn->prepare($sqlTotal);
                    $stmtTotal->bind_param('i', $usuario_id);
                    $stmtTotal->execute();
                    $resultTotal = $stmtTotal->get_result();
                    $totalTransacoes = intval($resultTotal->fetch_assoc()['total'] ?? 0);
                    $totalPaginas = $totalTransacoes > 0 ? ceil($totalTransacoes / $itensPorPagina) : 1;

                    $sql = "
                        SELECT t.*, c.nome_categoria AS categoria_nome
                        FROM transacoes t
                        LEFT JOIN categoria c ON t.categoria_id = c.id
                        WHERE t.usuario_id = ?
                        ORDER BY $order_by
                        LIMIT ? OFFSET ?
                    ";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('iii', $usuario_id, $itensPorPagina, $offset);
                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    // Verifica se há transações
                    if ($resultado->num_rows > 0) {
                        // Renderiza todas as transações como "cards" (mesmo layout em mobile, md e lg).
                        while ($row = $resultado->fetch_assoc()) {
                            // Formata a data corretamente
                            $data_original = $row['data'];
                            $data = DateTime::createFromFormat('Y-m-d', $data_original);
                            $data_formatada = $data !== false ? $data->format('d/m/Y') : "Data inválida";
                            $categoria_nome = htmlspecialchars($row['categoria_nome'] ?? 'Sem categoria');
                            $valor_class = ($row['valor'] >= 0) ? 'text-green-600' : 'text-red-600';
                            $valor_formatado = htmlspecialchars(number_format($row['valor'], 2, ',', '.'));
                            $descricao = htmlspecialchars($row['descricao']);
                            $id = intval($row['id']);

                            echo '<div class="bg-white p-4 rounded-lg shadow-lg mb-4 mobile-transacao cursor-pointer" data-id="' . $id . '">';

                            // Linha principal: descrição (esq) e valor (dir)
                            echo '  <div class="flex items-start justify-between">';
                            echo '    <div class="flex-1 pr-3">';
                            echo '      <div class="text-lg font-semibold text-gray-800 truncate">' . htmlspecialchars($row['descricao']) . '</div>';
                            echo '    </div>';
                            echo '    <div class="text-right ml-2">';
                            echo '      <div class="' . $valor_class . ' text-lg font-bold">' . htmlspecialchars(number_format($row['valor'], 2, ',', '.')) . '</div>';
                            echo '    </div>';
                            echo '  </div>';

                            // Segunda linha: data e categoria
                            echo '  <div class="flex justify-between text-sm text-gray-500 mt-2">';
                            echo '    <div>' . htmlspecialchars($data_formatada) . '</div>';
                            echo '    <div>' . $categoria_nome . '</div>';
                            echo '  </div>';

                            // ações: sempre ocultas por padrão (classe .actions). Toggle geral controla a visibilidade
                            echo '  <div class="mt-3 flex justify-end">';
                            echo '    <div class="actions hidden items-center space-x-2">';
                            echo '      <a href="../transacoes/page_editar.php?id=' . $id . '" rel="noopener noreferrer" onclick="event.stopPropagation();">';
                            echo '        <button class="bg-tollens text-white py-1 px-3 rounded hover:bg-purple-500"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                            echo '      </a>';
                            echo '      <a href="#" rel="noopener noreferrer" onclick="event.preventDefault();">';
                            echo '        <button type="button" class="bg-red-600 text-white py-1 px-3 rounded hover:bg-red-500" data-id="' . $id . '" onclick="event.stopPropagation(); abrirModalExcluir(' . $id . ');"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                            echo '      </a>';
                            echo '    </div>';
                            echo '  </div>';

                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-center text-gray-500">Nenhuma transação encontrada.</p>';
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

                            //Limpar a URL após exibir o Toastify
                            const url = new URL(window.location);
                            url.searchParams.delete('mensagem'); //Remove o parâmetro 'mensagem'
                            window.history.replaceState(null, '', url); //Atualiza a URL sem recarregar a página
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
                        <fieldset class="flex flex-row justify-center mb-4 gap-4">
                            <label class="block mb-2 cursor-pointer">
                                <input type="radio" name="tipo" value="positivo" required> Receita
                            </label>
                            <label class="block cursor-pointer">
                                <input type="radio" name="tipo" value="negativo" required> Despesa
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
                                    while ($row = $result->fetch_assoc()) {
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
    </main>

    <script> //Funções para abrir e fechar o modal de adicionar transação
        document.getElementById('abrirModalAddTransacao').addEventListener('click', function() {
            document.getElementById('AddTransacaoModal').classList.remove('hidden');
        });

        document.getElementById('fecharModalAdd').addEventListener('click', function() {
            document.getElementById('AddTransacaoModal').classList.add('hidden');
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

    <script> //filtro ano/mes
        document.getElementById('ano').addEventListener('change', function() {
            const anoSelecionado = this.value;
            const usuarioId = <?php echo $_SESSION['user_id']; ?>;
            const mesSelect = document.getElementById('mes');
        
            if (anoSelecionado) {
                fetch(`../transacoes/filtro_mes.php?ano=${anoSelecionado}&usuario_id=${usuarioId}`)
                    .then(response => response.json())
                    .then (data => {
                        mesSelect.innerHTML = '<option value="">Selecionar Mês</option>';
                    
                        if (data.length > 0) {
                            data.forEach(mes => {
                                let anoMes = `${anoSelecionado}-${mes.mes}`;
                                mesSelect.innerHTML += `<option value="${anoMes}">${new Date(anoSelecionado, parseInt(mes.mes) - 1).toLocaleDateString('pt-BR', { month: 'long' })}</option>`;
                            });
                            mesSelect.disabled = false;
                        } else {
                            mesSelect.disabled = true;
                        }
                    })
                    .catch(error => console.error('Erro ao buscar meses:', error));
            } else {
                mesSelect.innerHTML = '<option value="">Selecionar Mês</option>';
                mesSelect.disabled = true;
            }
        });

        // Carregar as transações ao selecionar o mês (atualizado para layout em card)
        document.getElementById('mes').addEventListener('change', function() {
            const mesSelecionado = this.value;
            const usuarioId = <?php echo $_SESSION['user_id']; ?>;
            const transacoesContainer = document.getElementById('transacoesContainer');

            if (mesSelecionado) {
                fetch(`../transacoes/filtro_mes.php?mes=${mesSelecionado}&usuario_id=${usuarioId}`)
                    .then(response => response.json())
                    .then(data => {
                        transacoesContainer.innerHTML = '';

                        if (data.length > 0) {
                            data.forEach(transacao => {
                                const dataFormatada = new Date(transacao.data).toLocaleDateString('pt-BR', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric'
                                });

                                const valorNum = Number(transacao.valor);
                                const valorFormatado = valorNum.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                const valorClass = valorNum >= 0 ? 'text-green-600' : 'text-red-600';
                                const categoria = transacao.categoria_nome ?? transacao.nome_categoria ?? 'Sem categoria';

                                transacoesContainer.innerHTML += `
                                    <div class="bg-white p-4 rounded-lg shadow-lg mb-4 mobile-transacao cursor-pointer" data-id="${transacao.id}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 pr-3">
                                                <div class="text-lg font-semibold text-gray-800 truncate">${transacao.descricao}</div>
                                            </div>
                                            <div class="text-right ml-2">
                                                <div class="${valorClass} text-lg font-bold">R$ ${valorFormatado}</div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-500 mt-2">
                                            <div>${dataFormatada}</div>
                                            <div>${categoria}</div>
                                        </div>
                                        <div class="mt-3 flex justify-end">
                                            <div class="actions hidden items-center space-x-2">
                                                <a href="../transacoes/page_editar.php?id=${transacao.id}" rel="noopener noreferrer" onclick="event.stopPropagation();">
                                                    <button class="bg-tollens text-white py-1 px-3 rounded hover:bg-purple-500"><i class="fa fa-pencil"></i></button>
                                                </a>
                                                <a href="#" rel="noopener noreferrer" onclick="event.stopPropagation(); abrirModalExcluir(${transacao.id})">
                                                    <button class="bg-red-600 text-white py-1 px-3 rounded hover:bg-red-500"><i class="fa fa-trash"></i></button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            transacoesContainer.innerHTML = '<p class="text-center text-gray-500">Nenhuma transação encontrada.</p>';
                        }
                    })
                    .catch(error => console.error('Erro ao buscar transações:', error));
            }
        });
    </script>

    <script> // filtro categoria (atualizado para layout em card)
        document.getElementById('FiltroCategoria').addEventListener('change', function() {
            const categoriaId = this.value;
            const usuarioId = <?php echo $_SESSION['user_id']; ?>;
            const transacoesContainer = document.getElementById('transacoesContainer');

            if (categoriaId) {
                fetch(`../transacoes/filtro_categoria.php?categoria_id=${categoriaId}`)
                    .then(response => response.json())
                    .then(data => {
                        transacoesContainer.innerHTML = '';

                        if (data.length > 0) {
                            data.forEach(transacao => {
                                const dataFormatada = new Date(transacao.data).toLocaleDateString('pt-BR');
                                const valorNum = Number(transacao.valor);
                                const valorFormatado = valorNum.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                const valorClass = valorNum >= 0 ? 'text-green-600' : 'text-red-600';
                                const categoria = transacao.nome_categoria ?? transacao.categoria_nome ?? 'Sem categoria';

                                transacoesContainer.innerHTML += `
                                    <div class="bg-white p-4 rounded-lg shadow-lg mb-4 mobile-transacao cursor-pointer" data-id="${transacao.id}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 pr-3">
                                                <div class="text-lg font-semibold text-gray-800 truncate">${transacao.descricao}</div>
                                            </div>
                                            <div class="text-right ml-2">
                                                <div class="${valorClass} text-lg font-bold">R$ ${valorFormatado}</div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-500 mt-2">
                                            <div>${dataFormatada}</div>
                                            <div>${categoria}</div>
                                        </div>
                                        <div class="mt-3 flex justify-end">
                                            <div class="actions hidden items-center space-x-2">
                                                <a href="../transacoes/page_editar.php?id=${transacao.id}" rel="noopener noreferrer" onclick="event.stopPropagation();">
                                                    <button class="bg-tollens text-white py-1 px-3 rounded hover:bg-purple-500"><i class="fa fa-pencil"></i></button>
                                                </a>
                                                <a href="#" abrirModalExcluir(${transacao.id})">
                                                    <button class="bg-red-600 text-white py-1 px-3 rounded hover:bg-red-500"><i class="fa fa-trash"></i></button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            transacoesContainer.innerHTML = '<p class="text-gray-600 text-center">Nenhuma transação encontrada para esta categoria.</p>';
                        }
                    })
                    .catch(error => console.error('Erro ao buscar transações:', error));
            }
        });
    </script>

    <script> // filtro search (atualizado para layout em card)
        document.getElementById('filtroSearch').addEventListener('input', function() {
            const searchQuery = this.value;
            const usuarioId = <?php echo $_SESSION['user_id']; ?>;
            const transacoesContainer = document.getElementById('transacoesContainer');

            function renderLista(data) {
                transacoesContainer.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(transacao => {
                        const dataFormatada = new Date(transacao.data).toLocaleDateString('pt-BR', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        });
                        const valorNum = Number(transacao.valor);
                        const valorFormatado = valorNum.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        const valorClass = valorNum >= 0 ? 'text-green-600' : 'text-red-600';
                        const categoria = transacao.categoria_nome ?? transacao.nome_categoria ?? 'Sem categoria';

                        transacoesContainer.innerHTML += `
                            <div class="bg-white p-4 rounded-lg shadow-lg mb-4 mobile-transacao cursor-pointer" data-id="${transacao.id}">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 pr-3">
                                        <div class="text-lg font-semibold text-gray-800 truncate">${transacao.descricao}</div>
                                    </div>
                                    <div class="text-right ml-2">
                                        <div class="${valorClass} text-lg font-bold">R$ ${valorFormatado}</div>
                                    </div>
                                </div>
                                <div class="flex justify-between text-sm text-gray-500 mt-2">
                                    <div>${dataFormatada}</div>
                                    <div>${categoria}</div>
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <div class="actions hidden items-center space-x-2">
                                        <a href="../transacoes/page_editar.php?id=${transacao.id}" rel="noopener noreferrer" onclick="event.stopPropagation();">
                                            <button class="bg-tollens text-white py-1 px-3 rounded hover:bg-purple-500"><i class="fa fa-pencil"></i></button>
                                        </a>
                                        <a href="#" rel="noopener noreferrer" abrirModalExcluir(${transacao.id})">
                                            <button class="bg-red-600 text-white py-1 px-3 rounded hover:bg-red-500"><i class="fa fa-trash"></i></button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    transacoesContainer.innerHTML = '<p class="text-center text-gray-500">Nenhuma transação encontrada com esse nome.</p>';
                }
            }

            if (searchQuery) {
                fetch(`../transacoes/filtro_search.php?query=${encodeURIComponent(searchQuery)}&usuario_id=${usuarioId}`)
                    .then(response => response.json())
                    .then(data => renderLista(data))
                    .catch(error => console.error('Erro ao buscar transações:', error));
            } else {
                // Buscar todas as transações quando a caixa de pesquisa estiver vazia
                fetch(`../transacoes/filtro_search.php?usuario_id=${usuarioId}`)
                    .then(response => response.json())
                    .then(data => renderLista(data))
                    .catch(error => console.error('Erro ao buscar transações:', error));
            }
        });
    </script>

    <script> //Exportar PDF e Excel
        document.getElementById('btnExportar').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('exportOptions').classList.toggle('hidden');
        });

        // Fecha o menu se clicar fora
        window.addEventListener('click', function(e) {
            if (!document.getElementById('btnExportar').contains(e.target) &&
                !document.getElementById('exportOptions').contains(e.target)) {
                document.getElementById('exportOptions').classList.add('hidden');
            }
        });

        // Função para exportar PDF
        function exportarPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            // Dados do usuário e extrato
            const saldo = "<?php echo number_format($saldo, 2, ',', '.'); ?>";
            const entradas = "<?php echo number_format($entradas, 2, ',', '.'); ?>";
            const saidas = "<?php echo number_format($saidas, 2, ',', '.'); ?>";
            const dataExport = new Date().toLocaleString('pt-BR');
            const usuario = "<?php echo htmlspecialchars($_SESSION['nome'] ?? 'Usuário Finstash'); ?>";
            const logoUrl = '../../assets/logo/cube_logo_no_background.png';

            // Dados das transações
            const transacoes = [
                <?php
                $resultado->data_seek(0);
                while ($row = $resultado->fetch_assoc()):
                    $data = DateTime::createFromFormat('Y-m-d', $row['data']);
                    $data_formatada = $data !== false ? $data->format('d/m/Y') : "Data inválida";
                    $categoria_nome = $row['categoria_nome'] ?? 'Sem categoria';
                ?>
                ,{
                    data: "<?php echo $data_formatada; ?>",
                    descricao: "<?php echo htmlspecialchars($row['descricao']); ?>",
                    categoria: "<?php echo htmlspecialchars($categoria_nome); ?>",
                    valor: "R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?>"
                },
                <?php endwhile; ?>
            ];

            // Carregar a logo e só então gerar o PDF
            const img = new Image();
            img.src = logoUrl;
            img.onload = function() {
                // Logo
                doc.addImage(img, 'PNG', 10, 10, 25, 25);

                // Título e cabeçalho
                doc.setFontSize(18);
                doc.setTextColor(33, 37, 41);
                doc.text("FINSTASH", 40, 18);
                doc.setFontSize(10);
                doc.text("EXTRATO DE CONTA", 40, 25);

                // Data/hora da exportação
                doc.setFontSize(9);
                doc.setTextColor(100);
                doc.text("Exportado em: " + dataExport, 150, 15, { align: "right" });

                // Dados do usuário
                doc.setFontSize(10);
                doc.setTextColor(33, 37, 41);
                doc.text("Usuário: " + usuario, 10, 40);

                // Saldo, Entradas, Saídas
                doc.setFontSize(11);
                doc.setTextColor(0, 0, 0);
                doc.text(`Saldo: R$ ${saldo}   |   Entradas: R$ ${entradas}   |   Saídas: R$ ${saidas}`, 10, 48);

                // Linha azul
                doc.setDrawColor(41, 128, 185);
                doc.setLineWidth(1.5);
                doc.line(10, 52, 200, 52);

                // Tabela de transações
                doc.autoTable({
                    startY: 56,
                    head: [[
                        "Data",
                        "Descrição",
                        "Categoria",
                        "Valor"
                    ]],
                    body: transacoes.map(t => [
                        t.data,
                        t.descricao,
                        t.categoria,
                        t.valor
                    ]),
                    styles: {
                        fontSize: 10,
                        cellPadding: 2,
                        valign: 'middle'
                    },
                    headStyles: {
                        fillColor: [41, 128, 185],
                        textColor: 255,
                        fontStyle: 'bold'
                    },
                    alternateRowStyles: {
                        fillColor: [240, 248, 255]
                    },
                    margin: { left: 10, right: 10 }
                });

                doc.save("extrato_finstash.pdf");
            };
        }

        // Função para exportar Excel
        function exportarExcel() {
            const wb = XLSX.utils.book_new();
            const ws_data = [
                ["Extrato Finstash"],
                ["Exportado em:", new Date().toLocaleString('pt-BR')],
                ["Saldo", "<?php echo number_format($saldo, 2, ',', '.'); ?>", "Entradas", "<?php echo number_format($entradas, 2, ',', '.'); ?>", "Saídas", "<?php echo number_format($saidas, 2, ',', '.'); ?>"],
                [],
                ["Data", "Descrição", "Categoria", "Valor"]
                <?php
                $resultado->data_seek(0);
                while ($row = $resultado->fetch_assoc()):
                    $data = DateTime::createFromFormat('Y-m-d', $row['data']);
                    $data_formatada = $data !== false ? $data->format('d/m/Y') : "Data inválida";
                    $categoria_nome = $row['categoria_nome'] ?? 'Sem categoria';
                ?>
                ,["<?php echo $data_formatada; ?>", "<?php echo htmlspecialchars($row['descricao']); ?>", "<?php echo htmlspecialchars($categoria_nome); ?>", "R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?>"]
                <?php endwhile; ?>
            ];
            const ws = XLSX.utils.aoa_to_sheet(ws_data);
            XLSX.utils.book_append_sheet(wb, ws, "Extrato");
            XLSX.writeFile(wb, "extrato_finstash.xlsx");
        }
        </script>

    <script>//Detecta o fuso horário local e preenche o campo oculto
        document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;
    </script>

    <div class="flex justify-center gap-2 mb-6">
        <?php if ($paginaAtual > 1): ?>
            <a href="?pagina=<?php echo $paginaAtual - 1; ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">&laquo; Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <a href="?pagina=<?php echo $i; ?>" class="px-3 py-1 rounded <?php echo $i == $paginaAtual ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($paginaAtual < $totalPaginas): ?>
            <a href="?pagina=<?php echo $paginaAtual + 1; ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Próxima &raquo;</a>
        <?php endif; ?>
    </div>

    <script>
        function toggleFiltros() {
            const container = document.getElementById('filtrosContainer');
            const icone = document.getElementById('iconeFiltros');
            container.classList.toggle('hidden');
            icone.classList.toggle('rotate-180');
        }

        window.addEventListener('resize', function() {
            const container = document.getElementById('filtrosContainer');
            if (window.innerWidth >= 1024) {
                container.classList.remove('hidden');
            } else {
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupCardToggles() {
                document.querySelectorAll('.mobile-transacao').forEach(function(item) {
                    if (item.dataset.hasListener) return;
                    item.dataset.hasListener = '1';

                    item.addEventListener('click', function(e) {
                        const actions = item.querySelector('.actions');
                        if (!actions) return;
                        actions.classList.toggle('hidden');
                        item.classList.toggle('bg-gray-50');
                    });

                    item.querySelectorAll('a, button').forEach(btn => {
                        btn.addEventListener('click', function(ev) { ev.stopPropagation(); });
                    });
                });
            }

            setupCardToggles();

            const transacoesContainer = document.getElementById('transacoesContainer');
            if (transacoesContainer) {
                const obs = new MutationObserver(() => setupCardToggles());
                obs.observe(transacoesContainer, { childList: true, subtree: true });
            }

            window.addEventListener('resize', function() {
                document.querySelectorAll('.actions').forEach(a => a.classList.add('hidden'));
                document.querySelectorAll('.mobile-transacao').forEach(t => t.classList.remove('bg-gray-50'));
            });
        });
    </script>

    <script>
        document.getElementById('limparFiltrosBtn').addEventListener('click', function () {
            // Campos a limpar
            const ids = ['filtroSearch', 'filter', 'ano', 'mes', 'FiltroCategoria'];

            ids.forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                if (el.tagName === 'SELECT' || el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    el.value = '';
                }
            });

            const mesSelect = document.getElementById('mes');
            if (mesSelect) {
                mesSelect.innerHTML = '<option value=\"\">Selecionar Mês</option>';
                mesSelect.disabled = true;
            }

            const path = window.location.pathname;
            window.location.href = path;
        });
    </script>

    <script>
        function formatCurrencyBR(value) {
            let digits = value.replace(/\D/g, '');
            if (digits === '') return '';

            while (digits.length < 3) {
                digits = '0' + digits;
            }

            const cents = digits.slice(-2);
            let intPart = digits.slice(0, -2);

            intPart = intPart.replace(/^0+(?!$)/, '');

            intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            return intPart + ',' + cents;
        }

        function attachCurrencyMask(input) {
            input.addEventListener('input', function (e) {
                const caretPos = input.selectionStart;
                const oldLength = input.value.length;

                const formatted = formatCurrencyBR(input.value);
                input.value = formatted;

                const newLength = formatted.length;
                const diff = newLength - oldLength;
                input.setSelectionRange(Math.max(0, caretPos + diff), Math.max(0, caretPos + diff));
            });

            input.addEventListener('blur', function () {
                if (input.value.trim() === '') {
                    input.value = '0,00';
                } else {
                    input.value = formatCurrencyBR(input.value);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[name="valor"]').forEach(function (inp) {
                attachCurrencyMask(inp);
                if (!inp.value || inp.value.trim() === '') inp.value = '0,00';
            });
        });
    </script>
</body>

</html>