<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/output.css">

    <link rel="stylesheet" href="./node_modules/toastify-js/src/toastify.css">
    <script src="./node_modules/toastify-js/src/toastify.js"></script>
    <title>Index</title>
</head>
<body>
    <?php
        if (isset($_GET['mensagem'])) {
            echo "<script>
                window.onload = function() {
                    switch ('" . $_GET['mensagem'] . "') {
                        case 'Logout':
                            Toastify({
                                text: 'Logout efetuado com sucesso!',
                                duration: 3000,
                                close: true,
                                gravity: 'top',
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
        <div id="navbar_1" class="flex flex-row w-full h-12 justify-center items-center bg-black">
            <div id="logo_nav" class="basis-6/12 items-center text-left ml-5 text-white">
                logo.img
            </div>
            <div id="btns_login" class="flex basis-6/12 justify-end bg-black text-white">
                <div id="btn_login" class="mr-5">
                    <a href="modulos/login/login.php"><button>Login</button></a>
                </div>
                <div id="btn_cadastro" class="mr-5 hover:bg-slate-50">
                    <a href="modulos/cadastro/cadastro.php"><button>Cadastre-se</button></a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
