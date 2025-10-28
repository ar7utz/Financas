<?php
session_start();

include_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Pegar o ID do utilizador logado da sessão
$usuario_id = $_SESSION['user_id'];

// Preparar a consulta para pegar os dados do utilizador
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar se encontrou o utilizador
if ($resultado->num_rows > 0) {
    // Extrair os dados do utilizador
    $usuario = $resultado->fetch_assoc();
    $nome = $usuario['nome'];
    $username = $usuario['username'];
    $telefone = $usuario['telefone'];
    $email = $usuario['email'];
    $foto = $usuario['foto'];
} else {
    echo "Utilizador não encontrado.";
    exit;
}

// Buscar o total investido na tabela movimentacoes
$totalInvestido = 0.0;
$sqlInvest = "SELECT SUM(valor) as total FROM movimentacoes WHERE usuario_id = ? AND tipo = 'aplicacao'";
$stmtInvest = $conn->prepare($sqlInvest);
$stmtInvest->bind_param('i', $usuario_id);
$stmtInvest->execute();
$resultInvest = $stmtInvest->get_result();
if ($rowInvest = $resultInvest->fetch_assoc()) {
    $totalInvestido = $rowInvest['total'] ? $rowInvest['total'] : 0.0;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link rel="shortcut icon" href="../../assets/logo/cube-logo.svg" type="image/x-icon">
    <title>Finstash - Meu Perfil</title>
</head>

<body class="bg-gray-100">
    <?php
        include_once('../../assets/templates/navbar.php')
    ?>

    <div class="w-4/5 mx-auto">
        <div class="flex flex-col md:flex-row mt-8 bg-white p-6 rounded-lg shadow-md">
            <div class="w-full md:w-1/3 flex flex-col items-center justify-center">
                <!-- Div da foto - lado esquerdo -->
                <div>
                    <img src="../../assets/uploads/<?php echo $usuario['foto'] ? htmlspecialchars($usuario['foto']) : '../../assets/uploads/foto_default.png'; ?>"
                     alt="Foto de perfil"
                     class=" w-64 h-64 rounded-full object-cover">
                    <!-- <label class="bg-blue-500 text-white mt-4 px-4 py-2 rounded-md">Foto de perfil</label> -->
                </div>
            </div>
            <!-- Div das informações - lado direito -->
            <div class="w-full md:w-2/3 flex flex-col justify-center mt-5">
                <!-- Responsividade Nome e User-->
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div class="flex flex-col">
                        <label for="nome">Nome:</label>
                        <input type="text" value="<?php echo htmlspecialchars($nome);?>" class="border p-2  rounded-md" disabled>
                    </div>
                    <div>
                        <label for="">Nome de usuário</label>
                        <input type="text" value="<?php echo htmlspecialchars($username);?>" class="border p-2 w-full rounded-md" disabled>
                    </div>
                </div>
            
                <div class="flex flex-col">
                    <div>
                        <label for="">Telefone</label>
                        <input type="text" value="<?php echo htmlspecialchars($telefone);?>" class="border p-2 w-full rounded-md" disabled>
                    </div>
                </div>

                <div class="flex flex-col">
                    <div>
                        <label for="">Email:</label>
                        <input type="text" value="<?php echo htmlspecialchars($email);?>" class="border p-2 w-full rounded-md" disabled>
                    </div>
                </div>

                <div class="flex flex-col">
                    <div>
                        <label for="">Total Investido até o momento:</label>
                        <input type="text" value="<?php echo number_format($totalInvestido, 2, ',', '.');?>" class="border p-2 w-full rounded-md" disabled>
                    </div>
                </div>

                <div class="flex justify-center">
                    <a href="./editar_usuario.php"><button class="bg-blue-500 text-white mt-4 px-4 py-2 rounded-md">Editar Informações</button></a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>