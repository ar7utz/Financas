<?php
session_start();
include ('../../assets/bd/conexao.php');

$sql = "SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY $order_by";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Finanças</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <!-- Header -->
    <header class="bg-purple-700 p-4 flex justify-between items-center">
        <button class="bg-gray-400 text-white py-2 px-4 rounded">Voltar</button>
        <h1 class="text-white text-2xl font-bold">Gerenciamento de Finanças</h1>
        <div class="space-x-2">
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Meu Perfil</button>
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Sair</button>
        </div>
    </header>

    <main class="p-6">
        <div class="flex justify-between items-center mb-8">
            <button class="bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-500">
                + Nova Transação
            </button>
            <div class="text-center">
                <h2 class="text-xl font-bold">SALDO</h2>
                <p class="text-2xl font-semibold"></p> <!--refenrenciar saldo-->
            </div>
        </div>

        <!-- Entradas e Saídas -->
        <div class="flex justify-between items-center mb-8">
            <div class="bg-white p-4 rounded-lg shadow-md w-1/3 text-center">
                <p class="font-bold text-green-600">Entradas</p>
                <p class="text-xl font-semibold"><?php echo number_format($saldo, 2); ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md w-1/3 text-center">
                <p class="font-bold text-red-600"><?php echo number_format($entradas, 2); ?></p>
                <p class="text-xl font-semibold"><?php echo number_format($saidas, 2); ?></p>
            </div>
        </div>

        <!-- Histórico -->
        <?php
            include ('../../assets/bd/conexao.php');

            if (isset($_SESSION['user_id'])) {
              $usuario_id = $_SESSION['user_id'];
          
              $sql = "SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY data DESC";
              $stmt = $conn->prepare($sql);
              $stmt->bind_param('i', $usuario_id);
              $stmt->execute();
              $resultado = $stmt->get_result();
              
              // Verificar se há transações
              if ($resultado->num_rows > 0) {
                // Exibir as transações no histórico
                while ($row = $resultado->fetch_assoc()) {
                  echo '<li>';
                  echo '<span id="descricao" class="descricao">' . $row['descricao'] . '</span>';
                  echo '<span id="data" class="data">' . $row['data'] . '</span>';
                  echo '<span id="valor" class="valor">' . $row['valor'] . '</span>';
                  echo '<div>';
                  echo '<button id="" class="editar"><a href="../../modulos/transacoes/editar_transacao.php?id=' . $row['id'] . '">Editar</a></button>';
                  echo '<button id="" class="excluir" data-id="' . $row['id'] . '">Excluir</button>';
                  echo '</div>';
                  echo '</li>';
                }
              } else {
                echo '<li>Nenhuma transação encontrada.</li>';
              }
            }
        ?>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold mb-4">Histórico</h3>
            <div class="flex items-center mb-4">
                <label for="filter" class="mr-2 font-semibold">Filtrar por:</label>
                <select id="filter" class="border border-gray-300 rounded p-2">
                    <option>Data (Mais recentes)</option>
                </select>
                <input type="text" placeholder="Procurar" class="ml-4 border border-gray-300 rounded p-2 w-full max-w-xs">
            </div>

            <!-- Tabela de Transações -->
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="p-2 border-b">Descrição</th>
                        <th class="p-2 border-b">Data</th>
                        <th class="p-2 border-b">Valor</th>
                        <th class="p-2 border-b">Ações</th>
                    </tr>
                </thead>
            </table>
        </div>
    </main>

</body>
</html>
