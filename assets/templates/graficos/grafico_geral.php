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
    SELECT c.nome_categoria AS categoria, SUM(t.valor) AS total, t.tipo
    FROM transacoes t
    JOIN categoria c ON t.categoria_id = c.id
    WHERE t.usuario_id = ?
    GROUP BY c.nome_categoria, t.tipo
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$categorias = [];
$entradas = [];
$saidas = [];

while ($row = $result->fetch_assoc()) {
    $categoria = $row['categoria'];
    $total = (float)$row['total'];
    $tipo = $row['tipo']; // 1: entrada, 2: saída

    if (!in_array($categoria, $categorias)) {
        $categorias[] = $categoria;
    }

    if ($tipo === 1) { // Entrada
        $entradas[$categoria] = $total;
    } elseif ($tipo === 2) { // Saída
        $saidas[$categoria] = $total;
    }
}

$valores_entradas = [];
$valores_saidas = [];
foreach ($categorias as $categoria) {
    $valores_entradas[] = $entradas[$categoria] ?? 1;
    $valores_saidas[] = $saidas[$categoria] ?? 1;
}

// Converter dados para JSON
$categorias_json = json_encode($categorias);
$valores_entradas_json = json_encode($valores_entradas);
$valores_saidas_json = json_encode($valores_saidas);
?>


<body class="">

    <div class="w-3/4">
        <canvas id="graficoTransacoes"></canvas>
    </div>

    <script>
        const categorias = <?php echo $categorias_json; ?>;
        const valoresEntradas = <?php echo $valores_entradas_json; ?>;
        const valoresSaidas = <?php echo $valores_saidas_json; ?>;

        // Gráfico de Barras com Entradas e Saídas
        const ctx = document.getElementById('graficoTransacoes').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: categorias, // Categorias no eixo X
                datasets: [
                    {
                        label: 'Entradas',
                        data: valoresEntradas,
                        backgroundColor: '#4caf50', // Verde para entradas
                        borderColor: '#4caf50',
                        borderWidth: 1
                    },
                    {
                        label: 'Saídas',
                        data: valoresSaidas,
                        backgroundColor: '#f44336', // Vermelho para saídas
                        borderColor: '#f44336',
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
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                return `${context.dataset.label}: $${value.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Categorias'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Valores ($)'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
