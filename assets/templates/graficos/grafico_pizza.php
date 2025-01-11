<?php
require_once '../../assets/bd/conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../modulos/login/login.php');
    exit;
}

$usuario_id = $_SESSION['user_id'];

$sql = "
    SELECT c.nome_categoria AS categoria, COALESCE(SUM(t.valor), 0) AS total, t.tipo
    FROM categoria c
    LEFT JOIN transacoes t ON t.categoria_id = c.id AND t.usuario_id = ?
    GROUP BY c.nome_categoria, t.tipo
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$entradas = [];
$saidas = [];

while ($row = $result->fetch_assoc()) {
    $categoria = $row['categoria'];
    $total = (float)$row['total'];
    $tipo = $row['tipo']; // 1: entrada, 2: saída

    if ($tipo === 1) { // Entrada
        $entradas[$categoria] = $total;
    } elseif ($tipo === 2) { // Saída
        $saidas[$categoria] = $total;
    }
}

// Preparar categorias e valores
$categorias_entradas = json_encode(array_keys($entradas));
$valores_entradas = json_encode(array_values($entradas));
$categorias_saidas = json_encode(array_keys($saidas));
$valores_saidas = json_encode(array_values($saidas));
?>

<body class="">

    <!-- Gráfico de Entradas -->
    <div class="w-1/2 mb-8">
        <h2 class="text-center text-lg font-semibold mb-4">Entradas</h2>
        <canvas id="graficoEntradas"></canvas>
    </div>

    <!-- Gráfico de Saídas -->
    <div class="w-1/2">
        <h2 class="text-center text-lg font-semibold mb-4">Saídas</h2>
        <canvas id="graficoSaidas"></canvas>
    </div>

    <script>
        // Dados do PHP para os gráficos
        const categoriasEntradas = <?php echo $categorias_entradas; ?>;
        const valoresEntradas = <?php echo $valores_entradas; ?>;
        const categoriasSaidas = <?php echo $categorias_saidas; ?>;
        const valoresSaidas = <?php echo $valores_saidas; ?>;

        // Gráfico de Entradas
        const ctxEntradas = document.getElementById('graficoEntradas').getContext('2d');
        new Chart(ctxEntradas, {
            type: 'pie',
            data: {
                labels: categoriasEntradas,
                datasets: [{
                    label: 'Entradas',
                    data: valoresEntradas,
                    backgroundColor: [
                        '#4caf50', '#2196f3', '#ff9800', '#e91e63', '#9c27b0'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                return `${context.label}: $${value.toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Saídas
        const ctxSaidas = document.getElementById('graficoSaidas').getContext('2d');
        new Chart(ctxSaidas, {
            type: 'pie',
            data: {
                labels: categoriasSaidas,
                datasets: [{
                    label: 'Saídas',
                    data: valoresSaidas,
                    backgroundColor: [
                        '#f44336', '#03a9f4', '#ffc107', '#8bc34a', '#673ab7'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                return `${context.label}: $${value.toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
