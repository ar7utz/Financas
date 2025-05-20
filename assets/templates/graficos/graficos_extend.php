<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos Dinâmicos</title>
    <script src="../../../../node_modules/chart.js/dist/chart.umd.js"></script>
    <link rel="stylesheet" href="../../../../assets/css/output.css">
</head>
<body class="bg-gray-100 p-6">

    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Gráficos Dinâmicos</h1>

        <!-- Filtros -->
        <div class="mb-6">
            <label for="chartType" class="block mb-2 font-medium">Escolha o tipo de gráfico:</label>
            <select id="chartType" class="border border-gray-300 rounded px-4 py-2">
                <option value="bar">Bar Chart</option>
                <option value="line">Line Chart</option>
                <option value="mixed">Mixed Chart</option>
            </select>

            <label for="filterType" class="block mt-4 mb-2 font-medium">Filtrar por:</label>
            <select id="filterType" class="border border-gray-300 rounded px-4 py-2">
                <option value="categorias">Categorias</option>
                <option value="receitas">Receitas</option>
                <option value="despesas">Despesas</option>
            </select>

            <label for="timeRange" class="block mt-4 mb-2 font-medium">Intervalo de tempo:</label>
            <select id="timeRange" class="border border-gray-300 rounded px-4 py-2">
                <option value="semanal">Semana</option>
                <option value="mensal">Mês</option>
                <option value="trimestral">Trimestre</option>
                <option value="semestral">Semestre</option>
                <option value="anual">Ano</option>
            </select>

            <button id="updateChart" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Atualizar Gráfico</button>
        </div>

        <!-- Canvas do Gráfico -->
        <canvas id="dynamicChart" width="400" height="200"></canvas>
    </div>

    <script>
        // Já implementado no arquivo graficos_extend.php
        fetch(`./endpoint_gfc.php?filterType=${filterType}&timeRange=${timeRange}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro na requisição: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
            
                // Processar os dados recebidos
                let labels = [];
                let dataset = [];
            
                if (filterType === 'categorias') {
                    labels = Object.keys(data.data.categorias);
                    dataset = labels.map(label => {
                        const valores = data.data.categorias[label];
                        return Object.values(valores).reduce((acc, curr) => acc + curr, 0); // Soma os valores por categoria
                    });
                } else if (filterType === 'receitas') {
                    labels = data.data.receitas.map(item => item.mes);
                    dataset = data.data.receitas.map(item => item.total);
                } else if (filterType === 'despesas') {
                    labels = data.data.despesas.map(item => item.mes);
                    dataset = data.data.despesas.map(item => item.total);
                }
            
                // Atualizar ou criar o gráfico
                const ctx = document.getElementById('dynamicChart').getContext('2d');
                if (dynamicChart) {
                    dynamicChart.destroy(); // Destruir o gráfico atual para recriar com novos dados
                }
            
                dynamicChart = new Chart(ctx, {
                    type: chartType, // Tipo de gráfico selecionado
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: filterType === 'categorias' ? 'Categorias' : filterType === 'receitas' ? 'Receitas' : 'Despesas',
                                data: dataset,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Gráfico de Transações do Usuário'
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Erro ao buscar os dados:', error));

    </script>
</body>
</html>
