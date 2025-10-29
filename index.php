<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/output.css">

    <link rel="shortcut icon" href="assets/logo/cube_logo_no_background.ico" type="image/x-icon">

    <link rel="stylesheet" href="./node_modules/toastify-js/src/toastify.css">
    <script src="./node_modules/toastify-js/src/toastify.js"></script>

    <title>Finstash</title>
</head>
<body>
    
    <?php // Notificações do Toastify
        if (isset($_GET['mensagem'])) {
            echo "<script>
                window.onload = function() {
                    switch ('" . $_GET['mensagem'] . "') {
                        case 'Logout':
                            Toastify({
                                text: 'Logout efetuado com sucesso!',
                                duration: 3000,
                                close: true,
                                gravity: 'bottom',
                                position: 'right',
                                backgroundColor: '#28a745', // cor verde para sucesso
                            }).showToast();
                            break;
                        case 'EmailRecSenhaSucesso':
                            Toastify({
                                text: 'E-mail enviado com sucesso!',
                                duration: 3000,
                                close: true,
                                gravity: 'top',
                                position: 'right',
                                backgroundColor: '#28a745',
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
    
    <main>
        <div id="navbar_1" class="bg-white p-2 ml-4 mr-4 mt-4 justify-evenly flex items-center relative shadow-lg rounded-full">
            <div id="logo_nav" class="items-center text-left">
                <img src="assets/logo/cube_logo_no_background.png" class="w-16 h-auto">
            </div>

            <div id="btns_login" class="flex justify-end basis-6/12 text-black mr-5">
                <div class="flex p-4 space-x-4">
                    <div id="btn_login" class="">
                        <a href="modulos/login/login.php" rel="noopener noreferrer"><button class="hover:underline">Login</button></a>
                    </div>
                    <div id="btn_cadastro" class="hover:underline hover:cursor-pointer">
                        <a href="modulos/cadastro/cadastro.php" rel="noopener noreferrer"><button class="hover:underline">Cadastre-se</button></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center mt-10 mb-10">
            <h1 class="text-4xl font-bold mb-6 text-center">Bem-vindo ao Finstash</h1>
            <p class="text-lg text-center max-w-2xl">Sua plataforma completa para gerenciamento financeiro, planejamento de investimentos e controle de despesas. Comece agora a organizar suas finanças pessoais com facilidade e segurança!</p>
            <a href="modulos/login/login.php" rel="noopener noreferrer">
                <button class="mt-6 bg-tollens text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-300">Comece Agora</button>
            </a>
        </div>

        <div class="bg-gray-100 p-6 rounded-lg shadow-md max-w-4xl mx-auto mb-10">
            <h2 class="text-2xl font-bold mb-4 text-center">Sobre o Finstash</h2>
            <p class="text-gray-700 mb-4">O Finstash é uma plataforma inovadora projetada para ajudar você a gerenciar suas finanças pessoais de maneira eficiente e inteligente. Com uma interface amigável e recursos avançados, o Finstash oferece tudo o que você precisa para controlar seus gastos, planejar investimentos e alcançar seus objetivos financeiros.</p>
            <p class="text-gray-700 mb-4">Nosso sistema de planejamento financeiro permite que você crie orçamentos personalizados, acompanhe suas despesas em tempo real e receba insights valiosos sobre seus hábitos de consumo. Além disso, com a integração de inteligência artificial, o Finstash pode sugerir investimentos adequados ao seu perfil financeiro, ajudando você a maximizar seus retornos.</p>
            <p class="text-gray-700">Junte-se a milhares de usuários satisfeitos que já estão transformando suas vidas financeiras com o Finstash. Cadastre-se hoje mesmo e comece a trilhar o caminho para a liberdade financeira!</p>
        </div>


    </main>
</body>
</html>
