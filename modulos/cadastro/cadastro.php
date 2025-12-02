<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">

    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../node_modules/toastify-js/src/toastify.css">
    <script src="../../node_modules/toastify-js/src/toastify.js"></script>

    <title>Finstash - Cadastro de Usuário</title>

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

    <form action="cadastrar.php" method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-md" enctype="multipart/form-data"> 
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
            <div class="mt-2">
                <img id="preview" src="#" alt="Preview da Foto" class="hidden">
            </div>
        </div>

        <div>
            <input type="submit" value="Cadastrar"
                class="w-full px-4 py-2 bg-tollens text-white font-semibold rounded hover:bg-blue-700 transition duration-300 cursor-pointer">
        </div>
    </form>

    <script> // Preview da imagem
        const inputFoto = document.getElementById('foto_perfil');
        const preview = document.getElementById('preview');

        inputFoto.addEventListener('change', () => {
            const file = inputFoto.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = (e) => {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                };

                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.classList.add('hidden');
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

<?php // Notificações do Toastify
    if (isset($_GET['mensagem'])) {
        echo "<script>
            window.onload = function() {
                switch ('" . $_GET['mensagem'] . "') {
                    case 'CadastroSucesso':
                        Toastify({
                            text: 'Usuário cadastrado com sucesso!',
                            duration: 3000,
                            close: true,
                            gravity: 'bottom',
                            position: 'right',
                            backgroundColor: '#28a745',
                        }).showToast();
                        break;
                    case 'SenhaObrigatoria':
                        Toastify({
                            text: 'A senha é obrigatória!',
                            duration: 3000,
                            close: true,
                            gravity: 'bottom',
                            position: 'right',
                            backgroundColor: '#28a745', // cor verde para sucesso
                        }).showToast();
                        break;
                    case 'ArquivosValidos':
                        Toastify({
                            text: 'Formato de arquivo inválido. Apenas JPG, JPEG, PNG e GIF são permitidos.',
                            duration: 3000,
                            close: true,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#FF0000',
                        }).showToast();
                        break;
                    case 'ErroFoto':
                        Toastify({
                            text: 'Erro ao salvar a foto.',
                            duration: 3000,
                            close: true,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#FF0000',
                        }).showToast();
                        break;
                    case 'ArquivosValidos':
                        Toastify({
                            text: 'Formato de arquivo inválido. Apenas JPG, JPEG, PNG e GIF são permitidos.',
                            duration: 3000,
                            close: true,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#FF0000',
                        }).showToast();
                        break;
                    case 'EmailJaEmUso':
                        Toastify({
                            text: 'O e-mail já está em uso.',
                            duration: 3000,
                            close: true,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#FF0000',
                        }).showToast();
                        break;
                    case 'ErroCadastroUser':
                        Toastify({
                            text: 'Erro ao cadastrar usuário.',
                            duration: 3000,
                            close: true,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#FF0000',
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
                        
                // Limpar a URL após exibir o Toastify
                const url = new URL(window.location);
                url.searchParams.delete('mensagem'); // Remove o parâmetro 'mensagem'
                window.history.replaceState(null, '', url); // Atualiza a URL sem recarregar a página
            }
        </script>";
    }
?>