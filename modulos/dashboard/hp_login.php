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

    <link rel="stylesheet" href="../../node_modules/toastify-js/src/toastify.css">
    <script src="../../node_modules/toastify-js/src/toastify.js"></script>
    
    <title>Bem Vindo</title>
</head>

<body>
    <?php include_once('../../assets/templates/navbar.php') ?>
    <?php
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
    <div class="flex w-full">
        <div class="justify-start">
            <p>climatempo</p>
        </div>
        <div class="justify-end" id="localTime">
            00:00:00H
        </div>
    </div>
    <?php include_once('../../assets/templates/navbar_lateral.php') ?>

    <div class="flex">
        <!-- Sidebar preta -->
        <?php include_once('../../assets/templates/navbar_lateral.php') ?>

    <script>

    </script>

    <script>//Função para buscar e exibir o horário local
        function atualizarHorario() {
            fetch('http://worldtimeapi.org/api/timezone/America/Sao_Paulo')
                .then(response => response.json())
                .then(data => {
                    const timeString = new Date(data.datetime).toLocaleTimeString();
                    document.getElementById('localTime').innerText = timeString;
                })
                .catch(error => console.error('Erro ao buscar o horário:', error));
        }

        //Atualizar o horário a cada segundo
        setInterval(atualizarHorario, 1000);
    </script>
</body>

</html>