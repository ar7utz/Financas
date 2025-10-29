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
        include_once ('../../assets/templates/navbar.php');
    ?>

    <div class="w-4/5 mx-auto">
        <div class="flex flex-col md:flex-row mt-8 bg-white p-6 rounded-lg shadow-md">
            <div class="w-full md:w-1/3 flex flex-col items-center justify-center">
                <!-- Div da foto - lado esquerdo -->
                <div>
                    <img src="../../assets/uploads/<?php echo $usuario['foto'] ? htmlspecialchars($usuario['foto']) : '../../assets/uploads/foto_default.png'; ?>"
                     alt="Foto de perfil"
                     class=" w-64 h-64 rounded-full object-cover">
                </div>
            </div>
            <!-- Div das informações - lado direito -->
            <div class="w-full md:w-2/3 flex flex-col justify-center mt-5">
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
                
                <!-- Filtro cagetoria -->
                <div class="flex flex-col">
                    <div>
                        <label for="FiltroCategoria" class="mb-1 block">Minhas categorias:</label>

                        <div class="relative">
                            <div class="flex items-center gap-2">
                                <select id="FiltroCategoria" name="FiltroCategoria" aria-label="Minhas categorias"
                                    class="flex-1 bg-white border border-gray-300 rounded-md p-2 pr-10 text-gray-700 focus:outline-none focus:ring-2 focus:ring-tollens">
                                    <option value="">Minhas categorias criadas</option>
                                    <?php
                                    $sql_categoria = "SELECT id, nome_categoria FROM categoria WHERE fk_user_id = ? ORDER BY nome_categoria ASC";
                                    $stmt_categoria = $conn->prepare($sql_categoria);
                                    $stmt_categoria->bind_param('i', $usuario_id);
                                    $stmt_categoria->execute();
                                    $resultado_categoria = $stmt_categoria->get_result();

                                    while ($categoria = $resultado_categoria->fetch_assoc()): ?>
                                        <option value="<?php echo $categoria['id']; ?>">
                                            <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                                        </option>
                                    <?php endwhile; 
                                    $stmt_categoria->close();
                                    ?>
                                </select>

                                <!-- Ícone de editar ao lado do select -->
                                <a href="../categorias/listaCategoria.php" title="Gerenciar minhas categorias"
                                   class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-200"
                                   aria-label="Editar categorias">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </a>
                            </div>
                         </div>

                        <div class=" gap-2 mt-2">
                            <a href="../categorias/formAddCategoria.php"><button class="bg-tollens text-white px-3 py-1 rounded">Inserir categoria +</button></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-center items-center align-middle mb-5">
            <a href="./editar_usuario.php"><button class="bg-blue-500 text-white mt-4 px-4 py-2 rounded-md">Editar Informações</button></a>
        </div>
    </div>
</body>

</html>