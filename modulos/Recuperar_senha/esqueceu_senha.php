<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Recuperação de senha</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

    <form action="./recuperar_senha.php" method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">Recuperar senha</h1>
        </div>
        <div class="mb-4">
            <input type="text" placeholder="Digite seu email:" name="email_recup_senha" autocomplete="off" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <input type="submit" value="Recuperar" name="send_email_recup_senha" class="w-full px-4 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-600 cursor-pointer">
        </div>
    </form>
</body>
</html>