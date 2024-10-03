<?php
session_start();
include_once '../../assets/bd/conexao.php';

// Função para obter os dados de entradas e saídas
function obterDados($filtro)
{
    global $conn;

    // Ajustar a query conforme o filtro (semana, mês, ano)
    if ($filtro == 'semanal') {
        $query = "SELECT SUM(valor) AS total, tipo FROM transacoes WHERE YEARWEEK(data) = YEARWEEK(NOW()) GROUP BY tipo";
    } elseif ($filtro == 'mensal') {
        $query = "SELECT SUM(valor) AS total, tipo FROM transacoes WHERE MONTH(data) = MONTH(NOW()) AND YEAR(data) = YEAR(NOW()) GROUP BY tipo";
    } elseif ($filtro == 'anual') {
        $query = "SELECT SUM(valor) AS total, tipo FROM transacoes WHERE YEAR(data) = YEAR(NOW()) GROUP BY tipo";
    }

    $result = mysqli_query($conn, $query);
    $dados = array('entradas' => 0, 'saidas' => 0);

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['tipo'] == 'positivo') {
            $dados['entradas'] = $row['total'];
        } elseif ($row['tipo'] == 'negativo') {
            $dados['saidas'] = $row['total'];
        }
    }

    return $dados;
}

// Filtro inicial (podes mudar este valor via JavaScript)
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'semanal';
$dados = obterDados($filtro);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <script src="../../node_modules/chart.js/dist/chart.umd.js"></script>
    <title>Bem Vindo</title>
</head>

<body>
    <?php include_once('../../assets/templates/navbar.php') ?>
    <h1>clima e horario</h1>
    <?php include_once('../../assets/templates/navbar_lateral.php') ?>

    <div class="flex">
        <!-- Sidebar preta -->
        <?php include_once('../../assets/templates/navbar_lateral.php') ?>

        <!-- Parte branca principal -->
        <div class="w-full p-4">
            <h1>Clima e Horário</h1>

            <div class="mb-4">
                <!-- Dropdown para escolher o filtro -->
                <label for="filtro">Filtrar por:</label>
                <select id="filtro" class="border p-2">
                    <option value="semanal">Semanal</option>
                    <option value="mensal">Mensal</option>
                    <option value="anual">Anual</option>
                </select>
            </div>

            <!-- Gráficos na parte branca -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Gráfico de Pizza -->
                <div class="bg-white p-4 shadow-md rounded">
                    <canvas id="graficoPizza"></canvas>
                </div>

                <!-- Gráfico de Linha -->
                <div class="bg-white p-4 shadow-md rounded">
                    <canvas id="graficoLinha"></canvas>
                </div>
            </div>

            <!-- Gráfico de Barras -->
            <div class="mt-4 bg-white p-4 shadow-md rounded">
                <canvas id="myChart"></canvas>
            </div> 
        </div>
    </div>

    <script>
        // Dados PHP para JavaScript
        const dadosPHP = <?php echo json_encode($dados); ?>;

        // Função para criar o gráfico de barras com Chart.js
        function criarGrafico(entradas, saidas) {
            const ctx = document.getElementById('myChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Entradas', 'Saídas'],
                    datasets: [{
                        label: 'Total',
                        data: [entradas, saidas],
                        backgroundColor: ['#36a2eb', '#ff6384'],
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Criar gráfico de pizza
        function criarGraficoPizza(entradas, saidas) {
            const ctx = document.getElementById('graficoPizza').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Entradas', 'Saídas'],
                    datasets: [{
                        label: 'Total',
                        data: [entradas, saidas],
                        backgroundColor: ['#36a2eb', '#ff6384'],
                    }]
                }
            });
        }

        // Criar gráfico de linha multi-eixo
        function criarGraficoLinha() {
            const ctx = document.getElementById('graficoLinha').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio'],
                    datasets: [{
                        label: 'Entradas',
                        data: [1000, 2000, 1500, 3000, 2500],
                        borderColor: '#36a2eb',
                        yAxisID: 'y'
                    }, {
                        label: 'Saídas',
                        data: [500, 1000, 800, 2000, 1200],
                        borderColor: '#ff6384',
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                        }
                    }
                }
            });
        }

        // Criar os gráficos iniciais
        criarGrafico(dadosPHP.entradas, dadosPHP.saidas);
        criarGraficoPizza(dadosPHP.entradas, dadosPHP.saidas);
        criarGraficoLinha();

        // Atualizar os gráficos ao mudar o filtro
        document.getElementById('filtro').addEventListener('change', function() {
            const filtro = this.value;
            window.location.href = "?filtro=" + filtro;
        });
    </script>
</body>

</html>