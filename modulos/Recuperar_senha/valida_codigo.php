<?php

require_once '../../assets/bd/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = $_POST['email'];
    $verification_code = $_POST['verification_code'];

    // Verifique se o código está correto
    $sql = "SELECT user_id FROM reset_senha_codigo 
            WHERE code = '$verification_code' 
            AND user_id = (SELECT id FROM user WHERE email = '$user_email')";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Código correto, redirecionar para a página de redefinição de senha
        header("Location: ./ ?email=$user_email");
        exit;
    } else {
        echo "Código inválido ou expirado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../assets/css/output.css">
    <title>Validar código</title>
</head>
<body>
<form method="POST" class="flex items-center justify-center align-middle m-auto min-h-screen">
    <div class="flex flex-col items-center text-left bg-slate-400 rounded p-4 w-80 shadow-lg">
        <div class="flex flex-col w-full mb-4">
            <label for="email" class="mb-1 text-left text-gray-800 font-medium">Email:</label>
            <input 
        </div>
        <div class="flex flex-col w-full mb-4">
            <label for="verification_code" class="mb-1 text-left text-gray-800 font-medium">Código de verificação</label>
            <input type="text" name="verification_code" placeholder="Digite o código enviado" required class="border-b-2 border-gray-400 focus:border-blue-600 outline-none bg-transparent px-2 py-1 transition-all duration-200">
        </div>
        <button 
            type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200">
            Validar Código
        </button>
    </div>
</form>
</body>
</html>
