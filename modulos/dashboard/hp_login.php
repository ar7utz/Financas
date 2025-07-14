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

    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">

    <script src="../../node_modules/chart.js/dist/chart.umd.js"></script>

    <link rel="stylesheet" href="../../node_modules/toastify-js/src/toastify.css">
    <script src="../../node_modules/toastify-js/src/toastify.js"></script>
    
    <title>Finstash - Bem Vindo</title>
</head>

<body class="p-0 margin-0">
    <?php include_once('../../assets/templates/navbar.php') ?>

    <?php //toastify
        if (isset($_GET['mensagem'])) {
            echo "<script>
                window.onload = function() {
                    switch ('" . $_GET['mensagem'] . "') {
                        case 'LoginSucesso':
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

    <div class="flex w-full items-center justify-between p-4">
        <!-- Clima -->
        <div class="flex w-52 h-16 items-center justify-center bg-kansai rounded-md p-2">
            <p class="font-medium" id="weatherDescription">Carregando clima...</p>
            <img id="weatherIcon" alt="Ícone do clima" style="width: 40px; height: 40px; margin-left: 10px;">
        </div>
        
        <!-- Horário -->
        <div class="flex w-48 h-16 items-center justify-center bg-kansai rounded-md p-2">
            <div class="font-medium" id="localTime">
                00:00H
            </div>
        </div>
    </div>

    <?php echo ('id do usuario: ') . $usuario_id; ?>

    <div class="flex">
        <!-- Sidebar preta -->
        <?php include_once('../../assets/templates/navbar_lateral.php') ?>
        <div class="flex justify-center w-4/6">
            <?php
            // Conecte ao banco se necessário
            include_once('../../assets/bd/conexao.php');
            $usuario_id = $_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? null;

            // Verifica se o usuário tem transações
            $temTransacao = false;
            if ($usuario_id) {
                $sql = "SELECT COUNT(*) as total FROM transacoes WHERE usuario_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $usuario_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $temTransacao = $row['total'] > 0;
            }

            if ($temTransacao) {
                include_once('../../assets/templates/graficos/grafico_pizza.php');
            } else {
                echo '<div class="text-center text-gray-600 text-lg font-semibold p-8">Você ainda não possui nenhuma transação registrada.</div>';
            }
            ?>
        </div>
    </div>

    <div class="flex flex-col items-center align-middle justify-between p-4 bg-gray-100 rounded-md mb-6">
        <form id="graficoForm" class="flex flex-col md:flex-row gap-4 mb-6 items-center">
            <div>
                <label for="chartType" class="mr-2 font-semibold">Tipo de Gráfico:</label>
                <select id="chartType" class="border rounded px-2 py-1">
                    <option value="pie">Pizza</option>
                    <option value="bar">Barra</option>
                    <option value="line">Linha</option>
                </select>
            </div>
            <div>
                <label for="filterType" class="mr-2 font-semibold">Filtro:</label>
                <select id="filterType" class="border rounded px-2 py-1">
                    <option value="categoria">Categoria</option>
                    <option value="receitas">Receitas</option>
                    <option value="despesas">Despesas</option>
                </select>
            </div>
            <div>
                <label for="timeRange" class="mr-2 font-semibold">Período:</label>
                <select id="timeRange" class="border rounded px-2 py-1">
                    <option value="mensal">Mensal</option>
                    <option value="semanal">Semanal</option>
                    <option value="trimestral">Trimestral</option>
                    <option value="semestral">Semestral</option>
                    <option value="anual">Anual</option>
                </select>
            </div>
            <button id="updateChart" type="button" class="bg-tollens text-white px-4 py-2 rounded hover:bg-green-500">Atualizar Gráfico</button>
        </form>
    </div>


    <div class="flex flex-col items-center align-middle justify-between p-4 rounded-md mb-6 bg-slate-400">
        <canvas id="dynamicChart" width="400" height="200"></canvas>
        <?php include_once('../../assets/templates/graficos/graficos_extend.php'); ?>
    </div>


    <script> //Função para buscar e exibir o horário local
        function atualizarHorario() {
            const apiKey = 'LQDWDPYI57PP';
            const url = `http://api.timezonedb.com/v2.1/get-time-zone?key=${apiKey}&format=json&by=zone&zone=America/Sao_Paulo`;
        
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'OK') {
                        const timeString = new Date(data.formatted).toLocaleTimeString();
                        document.getElementById('localTime').innerText = timeString;
                    } else {
                        console.error('Erro ao buscar o horário:', data.message);
                    }
                })
                .catch(error => console.error('Erro ao buscar o horário:', error));
        }

        // Atualizar o horário a cada segundo
        setInterval(atualizarHorario, 1000);

        window.onload = atualizarHorario;
    </script>



    <script> //api de clima tempo
        const apiKey = 'fa72d24f3537d09a7c4a3fe63b902d32';

        // Função para buscar clima local
        function buscarClima(lat, lon) {
            const url = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&units=metric&lang=pt_br&appid=${apiKey}`;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const descricao = data.weather[0].description; // Descrição do clima
                    const icone = data.weather[0].icon; // Ícone do clima
                    const temperatura = Math.round(data.main.temp); // Temperatura
                    const umidade = data.main.humidity; // Umidade
                    const vento = data.wind.speed; // Velocidade do vento

                    // Função para deixar a primeira letra maiúscula
                    const descricaoCapitalizada = descricao.charAt(0).toUpperCase() + descricao.slice(1);

                    // Atualizar o HTML 
                    //Outras propriedades - Umidade: ${umidade}% - Vento: ${vento} km/h
                    document.getElementById('weatherDescription').textContent = `${descricaoCapitalizada} - ${temperatura}°C`;
                    document.getElementById('weatherIcon').src = `https://openweathermap.org/img/wn/${icone}@2x.png`;
                })
                .catch(error => console.error('Erro ao buscar clima:', error));
        }

        // Função para obter a localização do usuário
        function obterLocalizacao() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude, longitude } = position.coords;
                        buscarClima(latitude, longitude);
                    },
                    (error) => {
                        console.error('Erro ao obter localização:', error);
                        document.getElementById('weatherDescription').textContent = 'Localização indisponível';
                    }
                );
            } else {
                document.getElementById('weatherDescription').textContent = 'Geolocalização não suportada';
            }
        }

        // Chamar a função para obter o clima ao carregar a página
        window.onload = function () {
            obterLocalizacao();
        };
    </script>

</body>

</html>