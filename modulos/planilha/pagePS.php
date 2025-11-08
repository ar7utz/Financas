<?php
session_start();
require_once '../../assets/bd/conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

// ID do usuário logado (corrigido: definir antes de usar)
$usuario_id = intval($_SESSION['user_id']);

// Buscar planilhas do usuário
$sql = "SELECT id, nome_arquivo, data_criacao FROM planilhas WHERE usuario_id = ? ORDER BY data_criacao DESC";
$stmt = $conn->prepare($sql);
$planilhas = [];
if ($stmt) {
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $planilhas = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}

// CRIAR A BASE DE DADOS PARA SALVAR AS PLANILHAS //
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!//

// ID do usuário logado
//$usuario_id = $_SESSION['user_id'];
//
//// Consulta para buscar as planilhas do usuário
//$sql = "SELECT id, titulo, data_criacao FROM planilhas WHERE usuario_id = ?";
//$stmt = $conn->prepare($sql);
//$stmt->bind_param('i', $usuario_id);
//$stmt->execute();
//$result = $stmt->get_result();
//
//// Verifica se existem planilhas
//$planilhas = [];
//if ($result->num_rows > 0) {
//    while ($row = $result->fetch_assoc()) {
//        $planilhas[] = $row;
//    }
//} else {
//    $planilhas = null;
//}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">

    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">

    <script src="../../node_modules/toastify-js/src/toastify.css"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <title>Finstash - Planilha Financeira</title>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php require_once '../../assets/templates/navbar.php' ?>

    <?php //toastify
        if (isset($_GET['mensagem'])) {
            echo "<script>
                window.onload = function() {
                    switch ('" . $_GET['mensagem'] . "') {
                        case 'PlanilhaImportadaSucesso':
                            Toastify({
                                text: 'Login efetuado com sucesso!',
                                duration: 3000,
                                close: true,
                                gravity: 'top',
                                position: 'right',
                                backgroundColor: '#28a745', // cor verde para sucesso
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

                    // Limpa a URL após exibir o Toastify
                    const url = new URL(window.location);
                    url.searchParams.delete('mensagem'); //Remove o parâmetro 'mensagem'
                    window.history.replaceState(null, '', url); //Atualiza a URL sem recarregar a página
                }
            </script>";
        }
    ?>

    <div class="container mx-auto p-4">

        <!-- Seção de Modelos de Planilhas -->
        <h1 class="text-2xl font-bold text-center mb-6">Modelo de planilhas</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card 1: Orçamento Mensal -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-2">Orçamento Mensal</h2>
                <p class="text-gray-700 mb-4">
                    Controle suas receitas e despesas mensais de forma simples e eficiente.
                </p>
                <button onclick="abrirModelo('Orçamento Mensal')" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-500">
                    Abrir Modelo
                </button>
            </div>

            <!-- Card 2: Fluxo de Caixa -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-2">Fluxo de Caixa</h2>
                <p class="text-gray-700 mb-4">
                    Acompanhe entradas e saídas de dinheiro para um melhor controle financeiro.
                </p>
                <button onclick="abrirModelo('Fluxo de Caixa')" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-500">
                    Abrir Modelo
                </button>
            </div>

            <!-- Card 3: Controle de Gastos -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-2">Controle de Gastos</h2>
                <p class="text-gray-700 mb-4">
                    Registre e categorize seus gastos diários para uma análise detalhada.
                </p>
                <button onclick="abrirModelo('Controle de Gastos')" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-500">
                    Abrir Modelo
                </button>
            </div>
        </div>

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-center mt-4">Minhas Planilhas</h1>
                <div class="flex flex-col justify-center items-center">
                    <!-- Formulário de Upload -->
                    <form id="importForm" action="upload_planilha.php" method="POST" enctype="multipart/form-data" class="flex flex-col space-x-2 p-2">
                        <input id="importFile"
                               type="file"
                               name="planilha"
                               accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                               required
                               class="border p-2 rounded" />
                        <button id="importBtn" type="submit" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Importar Planilha</button>
                    </form>
                    <script>
                        (function(){
                            const form = document.getElementById('importForm');
                            const input = document.getElementById('importFile');
                            const allowedExt = ['.xls', '.xlsx'];
                            const allowedTypes = [
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                            ];

                            form.addEventListener('submit', function(e){
                                if (!input.files || !input.files.length) {
                                    e.preventDefault();
                                    alert('Selecione um arquivo Excel (.xls ou .xlsx).');
                                    return;
                                }
                                const file = input.files[0];
                                const name = (file.name || '').toLowerCase();
                                if (!allowedExt.some(ext => name.endsWith(ext))) {
                                    e.preventDefault();
                                    alert('Apenas arquivos Excel (.xls, .xlsx) são permitidos.');
                                    return;
                                }
                                if (file.type && !allowedTypes.includes(file.type)) {
                                    e.preventDefault();
                                    alert('Tipo de arquivo inválido. Por favor selecione um arquivo Excel.');
                                    return;
                                }
                            });
                        })();
                    </script>
                </div>
        </div>
            <!-- Lista de Planilhas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($planilhas)): ?>
                    <?php foreach ($planilhas as $planilha): ?>
                        <div class="bg-white p-4 rounded shadow">
                            <h2 class="text-lg font-bold truncate"> <?php echo htmlspecialchars($planilha['nome_arquivo']); ?> </h2>
                            <p class="text-gray-700 text-sm">Data: <?php echo date('d/m/Y', strtotime($planilha['data_criacao'])); ?></p>
                            <a href="../../assets/uploads/planilhas/ <?php echo htmlspecialchars($planilha['nome_arquivo']); ?>" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-500 mt-4 inline-block" download>
                                Baixar Planilha
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="w-full text-center col-span-1 md:col-span-2 lg:col-span-3">
                        <p class="text-gray-600 text-center">Nenhuma planilha importada ainda.</p>
                    </div>
                <?php endif; ?>
            </div>
    </div>

    <!-- Modal de Edição da Planilha -->
    <div id="modalPlanilha" class="fixed flex inset-0 bg-black bg-opacity-50 hidden justify-center items-center p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6">
            <h2 class="text-2xl font-bold mb-4">Editando: <span id="modeloSelecionado"></span></h2>
            <div class="overflow-x-auto">
                <table id="tabelaPlanilha" class="min-w-full bg-white border border-gray-300">
                </table>
            </div>
            <div class="mt-4">
                <button onclick="exportarParaExcel()" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">
                    Exportar para Excel
                </button>
                <button onclick="exportarParaPDF()" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500 ml-4">
                    Exportar para PDF
                </button>
                <button onclick="fecharModal()" class="bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-500 ml-4">
                    Fechar
                </button>
            </div>
        </div>
    </div>

    <script>
        function abrirModelo(modelo) {
            let dados = [];

            switch (modelo) {
                case 'Orçamento Mensal':
                    dados = [
                        ["Categoria", "Orçamento", "Gasto Real", "Diferença"],
                        ["Receitas", "", "", ""],
                        ["Salário", 5000, 5000, "=C2-B2"],
                        ["Renda Extra", 500, 300, "=C3-B3"],
                        ["Despesas", "", "", ""],
                        ["Moradia", 1500, 1550, "=C5-B5"],
                        ["Transporte", 400, 450, "=C6-B6"],
                        ["Alimentação", 800, 750, "=C7-B7"],
                        ["Lazer", 300, 350, "=C8-B8"],
                        ["Total", "=SOMA(B2:B3)", "=SOMA(C2:C3)", "=SOMA(D2:D8)"]
                    ];
                    break;

                case 'Fluxo de Caixa':
                    dados = [
                        ["Data", "Descrição", "Tipo", "Valor", "Saldo"],
                        ["01/10/2023", "Salário", "Receita", 5000, 5000],
                        ["02/10/2023", "Aluguel", "Despesa", 1500, 3500],
                        ["03/10/2023", "Supermercado", "Despesa", 200, 3300]
                    ];
                    break;

                case 'Controle de Gastos':
                    dados = [
                        ["Data", "Descrição", "Categoria", "Valor", "Acumulado"],
                        ["01/10/2023", "Supermercado", "Alimentação", 150, 150],
                        ["02/10/2023", "Combustível", "Transporte", 100, 250],
                        ["03/10/2023", "Restaurante", "Alimentação", 50, 300]
                    ];
                    break;
            }

            document.getElementById('modalPlanilha').classList.remove('hidden');
            document.getElementById('modeloSelecionado').textContent = modelo;

            const tabela = document.getElementById('tabelaPlanilha');
            tabela.innerHTML = '';

            dados.forEach((linha, index) => {
                const tr = document.createElement('tr');
                linha.forEach((celula, i) => {
                    const td = document.createElement(index === 0 ? 'th' : 'td');
                    td.textContent = celula;
                    td.setAttribute('contenteditable', index !== 0);
                    tr.appendChild(td);
                });
                tabela.appendChild(tr);
            });
        }

        function fecharModal() {
            document.getElementById('modalPlanilha').classList.add('hidden');
        }

        function exportarParaExcel() {
            const tabela = document.getElementById('tabelaPlanilha');
            const ws = XLSX.utils.table_to_sheet(tabela);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Planilha");
            XLSX.writeFile(wb, "planilha_editada.xlsx");
        }

        function exportarParaPDF() {
            const { jsPDF } = window.jspdf || {};

            if (!jsPDF) {
                console.error("A biblioteca jsPDF não foi carregada corretamente.");
                return;
            }
        
            const doc = new jsPDF();
        
            if (typeof doc.autoTable === 'undefined') {
                console.error("O plugin autoTable não está carregado corretamente.");
                return;
            }
        
            const tabela = document.getElementById('tabelaPlanilha');
            const rows = tabela.querySelectorAll('tr');
        
            let data = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                data.push([...cells].map(cell => cell.textContent.trim()));
            });
        
            doc.autoTable({
                head: [data[0]], // Cabeçalho da tabela
                body: data.slice(1), // Corpo da tabela
                theme: 'grid', // Estilo da tabela
                styles: { fontSize: 10 }, // Ajuste do tamanho da fonte
            });
        
            doc.save("planilha_editada.pdf");
        }
    </script>

    <script> //Toastify

    </script>
</body>
</html>