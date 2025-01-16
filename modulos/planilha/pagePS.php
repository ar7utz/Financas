<?php
require_once '../../assets/bd/conexao.php';
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <title>Planilha Financeira</title>
</head>
<body>
    <?php require_once '../../assets/templates/navbar.php' ?>

    <div class="container mx-auto p-4">
        <!-- Botão "Criar Planilha" no topo -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Suas Planilhas</h1>
            <a href="./planilha.php">
                <button class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">
                    Criar uma Planilha
                </button>
            </a>
        </div>

        <!-- Seção de Modelos de Planilhas -->
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
                <a href="../../assets/planilhas/planilha-de-controle-financeiro.xlsx">baixar planilha</a>
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
    </div>

    <!-- Modal de Edição da Planilha -->
    <div id="modalPlanilha" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6">
            <h2 class="text-2xl font-bold mb-4">Editando: <span id="modeloSelecionado"></span></h2>
            <div class="overflow-x-auto">
                <table id="tabelaPlanilha" class="min-w-full bg-white border border-gray-300">
                    <!-- Cabeçalho e linhas serão preenchidos dinamicamente -->
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
        // Função para abrir o modal com o modelo de planilha
        function abrirModelo(modelo) {
            let dados = [];

            // Dados do modelo selecionado
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

            // Exibir o modal
            document.getElementById('modalPlanilha').classList.remove('hidden');
            document.getElementById('modeloSelecionado').textContent = modelo;

            // Preencher a tabela com os dados
            const tabela = document.getElementById('tabelaPlanilha');
            tabela.innerHTML = ''; // Limpar tabela anterior

            dados.forEach((linha, index) => {
                const tr = document.createElement('tr');
                linha.forEach((celula, i) => {
                    const td = document.createElement(index === 0 ? 'th' : 'td');
                    td.textContent = celula;
                    td.setAttribute('contenteditable', index !== 0); // Permitir edição, exceto no cabeçalho
                    tr.appendChild(td);
                });
                tabela.appendChild(tr);
            });
        }

        // Função para fechar o modal
        function fecharModal() {
            document.getElementById('modalPlanilha').classList.add('hidden');
        }

        // Função para exportar a planilha para Excel
        function exportarParaExcel() {
            const tabela = document.getElementById('tabelaPlanilha');
            const ws = XLSX.utils.table_to_sheet(tabela);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Planilha");
            XLSX.writeFile(wb, "planilha_editada.xlsx");
        }

        // Função para exportar a planilha para PDF
        function exportarParaPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            const tabela = document.getElementById('tabelaPlanilha');
            const rows = tabela.querySelectorAll('tr');

            let data = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                data.push([...cells].map(cell => cell.textContent));
            });

            doc.autoTable({
                head: [data[0]],
                body: data.slice(1),
            });

            doc.save("planilha_editada.pdf");
        }
    </script>
</body>
</html>