<?php
session_start();
require_once('../../assets/bd/conexao.php');

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
    $senha = $usuario['senha'];
} else {
    echo "Utilizador não encontrado.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Finstash - Editar Perfil</title>
</head>
<body>
    
    <?php include_once('../../assets/templates/navbar.php'); ?>

    <form action="update_usuario.php" method="POST" enctype="multipart/form-data" class="w-4/5 mx-auto">
        <div class="w-4/5 mx-auto">
            <div class="flex flex-row mt-8">
                <!-- Div da foto - lado esquerdo -->
                <div class="w-4/12 flex flex-col items-center">
                    <img id="preview" src="../../assets/uploads/<?php echo $usuario['foto'] ? htmlspecialchars($usuario['foto']) : 'foto_default.png'; ?>" alt="Foto de perfil" class="w-32 h-32 rounded-full object-cover">
                    <input type="hidden" name="foto_antiga" value="<?php echo htmlspecialchars($usuario['foto']); ?>">
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" class="hidden">
                    <button type="button" class="bg-blue-500 text-white mt-4 px-4 py-2 rounded-md" onclick="document.getElementById('foto_perfil').click()">
                        Alterar Foto
                    </button>
                </div>
    
                <!-- Div das informações - lado direito -->
                <div class="w-8/12 flex flex-col justify-center">
                    <div class="mb-4">
                        <label for="nome">Nome:</label>
                        <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($nome); ?>" class="border p-2 w-full rounded-md">
                    </div>
                    <div class="mb-4">
                        <label for="username">Nome de Usuário:</label>
                        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" class="border p-2 w-full rounded-md">
                    </div>
                    <div class="mb-4">
                        <label for="telefone">Telefone:</label>
                        <input type="text" name="telefone" id="telefone" value="<?php echo htmlspecialchars($telefone); ?>" class="border p-2 w-full rounded-md"  oninput="mascaraTelefone(); if(this.value.length > 15) this.value = this.value.slice(0, 15)">
                    </div>
                    <div class="mb-4">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" class="border p-2 w-full rounded-md">
                    </div>
                    <div class="relative mb-4">
                        <label for="senha" class="block text-sm font-medium text-gray-700">Senha: (opcional)</label>
                        <div class="relative">
                            <input id="senha" name="senha" type="password" class="border border-gray-300 p-2 w-full rounded-md pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Digite sua senha">
                            <span id="toggleSenha" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer">
                                <img id="iconeSenha" src="../../assets/img/Exibir_senha.png" alt="Exibir senha" class="h-6 w-6">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="mt-4 text-center">
            <button type="submit" class="w-48 h-8 rounded-md bg-slate-500 text-white">Salvar Alterações</button>
        </div>
    </form>


    <div class="fixed bottom-0 left-1/2 transform -translate-x-1/2 mb-4 mt-2">
        <a href="perfil.php"><button class="w-48 h-8 rounded-md bg-slate-500 text-white">Descartar Alterações</button></a>
    </div>

    <script>
        const inputFoto = document.getElementById('foto_perfil');
        const preview = document.getElementById('preview');

        // Exibir preview da foto selecionada
        inputFoto.addEventListener('change', () => {
            const file = inputFoto.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <script> //Exibir/Ocultar a senha
        const inputSenha = document.getElementById('senha');
        const toggleSenha = document.getElementById('toggleSenha');
        const iconeSenha = document.getElementById('iconeSenha');

        // Alternar visibilidade da senha
        toggleSenha.addEventListener('click', () => {
            if (inputSenha.type === 'password') {
                inputSenha.type = 'text'// Exibir senha
                iconeSenha.src = '../../assets/img/Ocultar_senha.png';
                iconeSenha.alt = 'Ocultar senha';
            } else {
                inputSenha.type = 'password';
                iconeSenha.src = '../../assets/img/Exibir_senha.png';
                iconeSenha.alt = 'Exibir senha';
            }
        });
    </script>

    <script> /*máscara de telefone*/
        function mascaraTelefone() {
            const input = document.getElementById('telefone');
            let value = input.value.replace(/\D/g, "");
            if (value.length > 11) value = value.slice(0, 11);
            value = value.replace(/^(\d{2})(\d)/g, "($1) $2");
            value = value.replace(/(\d{5})(\d)/, "$1-$2");
            input.value = value;
        }
    </script>
</body>
</html>