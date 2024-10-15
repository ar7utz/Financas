<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Cadastro de Usuário</title>

    <style>
        /*remove o text decoration do type number*/
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield; /*para firefox*/
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <!-- Botão de Voltar -->
    <div class="absolute top-4 left-4">
        <a href="../../index.php" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Voltar</a>
    </div>

    <form action="cadastrar.php" method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">Cadastro de Usuário</h1>
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-gray-700 font-semibold">Nome Completo:</label>
            <input type="text" placeholder="Nome" name="nome" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-gray-700 font-semibold">Nome de Usuário:</label>
            <input type="text" placeholder="Usuário" name="username" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-gray-700 font-semibold">E-mail:</label>
            <input type="email" placeholder="E-mail" name="email" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-gray-700 font-semibold">Telefone:</label>
            <input type="text" placeholder="(00) 00000-0000" id="telefone" name="telefone" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 decoration-none"
                oninput="mascaraTelefone(); if(this.value.length > 15) this.value = this.value.slice(0, 15)">
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-gray-700 font-semibold">Senha:</label>
            <input type="password" placeholder="Senha" name="senha" required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-gray-700 font-semibold">Escolher foto de perfil:</label>
            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" class="border p-2 w-full">
        </div>

        <div>
            <input type="submit" value="Cadastrar"
                class="w-full px-4 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-600 cursor-pointer">
        </div>
    </form>





    <script>
        /*máscara de telefone*/
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
