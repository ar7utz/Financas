<?php
session_start();
include_once '../../assets/bd/conexao.php';

// Função para obter os dados de entradas e saídas
function obterDados($filtro) {
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
    <title>Bem Vindo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Incluindo Chart.js -->
</head>
<body>
    <?php include_once('../../assets/templates/navbar.php') ?>
    <h1>clima e horario</h1>
    <?php include_once('../../assets/templates/navbar_lateral.php') ?>

    <div id="cards" class="p-4">
        <div>
            <!-- Dropdown para escolher o filtro -->
            <label for="filtro">Filtrar por:</label>
            <select id="filtro" class="border p-2">
                <option value="semanal">Semanal</option>
                <option value="mensal">Mensal</option>
                <option value="anual">Anual</option>
            </select>
        </div>

        <!-- Gráfico -->
        <div class="mt-4">
            <canvas id="myChart"></canvas>
        </div>
    </div>

    <script>
        // Dados PHP para JavaScript
        const dadosPHP = <?php echo json_encode($dados); ?>;

        // Função para criar o gráfico com Chart.js
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

        // Criar o gráfico inicial
        criarGrafico(dadosPHP.entradas, dadosPHP.saidas);

        // Atualizar o gráfico ao mudar o filtro
        document.getElementById('filtro').addEventListener('change', function() {
            const filtro = this.value;
            window.location.href = "?filtro=" + filtro;
        });
    </script>
</body>
</html>
