<script>
    let dynamicChart = null;

        async function updateChart() {
            const chartType = document.getElementById('chartType').value;
            const filterType = document.getElementById('filterType').value;
            const timeRange = document.getElementById('timeRange').value;

            try {
                const response = await fetch(`/Financas/assets/templates/graficos/endpoint_gfc.php?filterType=${filterType}&timeRange=${timeRange}`);
                if (!response.ok) {
                    throw new Error(`Erro na requisição: ${response.status}`);
                }
                const data = await response.json();

                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Processar os dados recebidos
                let labels = [];
                let dataset = [];

                if (filterType === 'categoria') {
                    if (data.data.categoria && Object.keys(data.data.categoria).length > 0) {
                        labels = Object.keys(data.data.categoria);
                        dataset = labels.map(label => {
                            const valores = data.data.categoria[label];
                            return Object.values(valores).reduce((acc, curr) => acc + Number(curr), 0);
                        });
                    }
                } else if (filterType === 'receitas') {
                    if (data.data.receitas && data.data.receitas.length > 0) {
                        labels = data.data.receitas.map(item => item.mes);
                        dataset = data.data.receitas.map(item => Number(item.total));
                    }
                } else if (filterType === 'despesas') {
                    if (data.data.despesas && data.data.despesas.length > 0) {
                        labels = data.data.despesas.map(item => item.mes);
                        dataset = data.data.despesas.map(item => Number(item.total));
                    }
                }

                // Se não houver dados, exibe mensagem e não tenta criar o gráfico
                if (labels.length === 0 || dataset.length === 0) {
                    if (dynamicChart) dynamicChart.destroy();
                    document.getElementById('dynamicChart').getContext('2d').clearRect(0, 0, 400, 200);
                    console.log('Nenhum dado encontrado para o filtro selecionado.');
                    return;
                }

                // Atualizar ou criar o gráfico
                const ctx = document.getElementById('dynamicChart').getContext('2d');
                if (dynamicChart) {
                    dynamicChart.destroy();
                }

                // Defina o tipo de gráfico corretamente
                const chartTypeToUse = chartType;

                dynamicChart = new Chart(ctx, {
                    type: chartTypeToUse,
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: filterType === 'categoria' ? 'Categorias' : filterType === 'receitas' ? 'Receitas' : 'Despesas',
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
            } catch (error) {
                console.error('Erro ao buscar os dados:', error);
                console.log('Erro ao buscar os dados do gráfico.');
            }
        }

        // Atualiza o gráfico ao clicar no botão
        window.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('updateChart');
            if (btn) {
                btn.addEventListener('click', updateChart);
            }
            updateChart();
        });

</script>
