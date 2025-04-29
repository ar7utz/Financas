<?php
require_once '../../assets/bd/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = $_POST['email'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Atualizar a senha do usuário
    $sql = "UPDATE user SET password = '$new_password' WHERE email = '$user_email'";
    if ($conn->query($sql) === TRUE) {
        echo "Senha alterada com sucesso!";
    } else {
        echo "Erro ao alterar senha: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Finstash - Redefinir senha</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

    <form action="./recuperar_senha.php" method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">Recuperar senha</h1>
        </div>
        <div class="mb-4">
            <input type="password" placeholder="Digite sua nova senha:" name="nova_senha" autocomplete="off" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <input type="password" placeholder="Confirme sua nova senha:" name="confirm_nova_senha" autocomplete="off" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <input type="submit" onclick="return validarSenha()" value="Redefinir senha" name="redefinir_senha" class="w-full px-4 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-600 cursor-pointer">
        </div>
    </form>

    <script> //script para verificar se as senhas são iguais
        function validarSenha() {
          senha = document.getElementsByName('nova_senha').value;
          senhaC = document.getElementsByName('confirm_nova_senha').value;
                
          if (nova_senha != confirm_nova_senha) {
            confirm_nova_senha.setCustomValidity("Senhas diferentes!");
            return false;
          } else {
            return true;
          }
        }
    </script>
</body>
</html>