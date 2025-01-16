<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>LOGIN</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="absolute top-4 left-4">
        <a href="../../index.php" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Voltar</a>
    </div>

    <form action="logon.php" method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">LOGIN</h1>
        </div>
        <div class="mb-4">
            <input type="text" placeholder="Email ou usuário" name="login" autocomplete="off" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-2">
            <input type="password" placeholder="Senha" name="senha" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-2">
            <a href="../Recuperar_senha/esqueceu_senha.php">Esqueceu a senha?</a>
        </div>
        <div>
            <input type="submit" value="Login"
                class="w-full px-4 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-600 cursor-pointer">
        </div>
    </form>
</body>
</html>