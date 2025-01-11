<?php $url_base = 'http://' . $_SERVER['HTTP_HOST'] . '/financas';

// Captura a URL da página anterior
$url_anterior = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $url_base;
$pagina_atual = basename($_SERVER['PHP_SELF']);

$esconder_botao_menu = ($pagina_atual == 'hp_login.php');
?>

<header class="bg-tollens p-4 flex items-center relative">

    <button id="btnMenu" class="bg-blue-500 text-white py-2 px-4 rounded">
        <img src="../../assets/img/icone_menu.png" alt="Menu" width="20px" height="20px">
    </button>

    <div id="menuLateral" class="fixed top-0 left-0 h-screen w-64 bg-gray-800 text-white transform -translate-x-full transition-transform duration-300 z-50" >
        <div class="p-4">
            <h2 class="text-xl font-bold mb-4">Menu Lateral</h2>
            <ul class="space-y-3">
                <li><a href="../../modulos/dashboard/hp_login.php" class="hover:underline">Home page</a></li>
                <li><a href="../../modulos/dashboard/dashboard.php" class="hover:underline">Dashboard</a></li>
                <li><a href="../../modulos/planejador/page.php" class="hover:underline">Planejador</a></li>
                <li><a href="../../modulos/planilha/pagePS.php" class="hover:underline">Planilha</a></li>
            </ul>

            <!-- Botão para fechar o menu -->
            <button id="btnFechar" class="bg-red-500 text-white mt-6 py-2 px-4 rounded">
                Fechar Menu
            </button>
        </div>
    </div>

    <div id="menuOverlay" class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 hidden z-40"></div>

    <div class="absolute inset-0 flex justify-center items-center pointer-events-none">
        <img src="../../assets/logo/cube-logo.svg" alt="Logo" class="w-14 h-14">
    </div>

    <div class="ml-auto space-x-2">
        <a href="<?php $url_base ?>../usuario/perfil.php" rel="noopener noreferrer">
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Meu Perfil</button>
        </a>
        <button id="abrirModal" class="bg-gray-400 text-white py-2 px-4 rounded">Sair</button>
    </div>

    <div id="modalConfirmarLogout" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded-md text-center">
            <p class="mb-4">Tem certeza de que deseja sair?</p>
            <div class="flex justify-center space-x-4">
                <a href="<?php $url_base ?>../login/logout.php" rel="noopener noreferrer">
                    <button id="confirmarLogout" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Confirmar</button>
                </a>
                <button id="cancelarLogout" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
            </div>
        </div>
    </div>
</header>

<script>
    // Função para abrir o modal ao clicar no botão de sair
    document.getElementById('abrirModal').addEventListener('click', function() {
        document.getElementById('modalConfirmarLogout').classList.remove('hidden');
    });

    // Função para fechar o modal ao clicar no botão cancelar
    document.getElementById('cancelarLogout').addEventListener('click', function() {
        document.getElementById('modalConfirmarLogout').classList.add('hidden');
    });

    // Fechar modal clicando fora da caixa
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('modalConfirmarLogout');
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
</script>

<script> //limpar o url
    const url = new URL(window.location);
    url.searchParams.delete('mensagem'); // Remove o parâmetro 'mensagem'
    window.history.replaceState(null, '', url); // Atualiza a URL sem recarregar a página
</script>

<script> //script menu
        const btnMenu = document.getElementById('btnMenu');
        const btnFechar = document.getElementById('btnFechar');
        const menuLateral = document.getElementById('menuLateral');
        const menuOverlay = document.getElementById('menuOverlay');

        // Função para abrir o menu
        btnMenu.addEventListener('click', () => {
            menuLateral.classList.remove('-translate-x-full');
            menuOverlay.classList.remove('hidden');
        });

        // Função para fechar o menu
        btnFechar.addEventListener('click', () => {
            menuLateral.classList.add('-translate-x-full');
            menuOverlay.classList.add('hidden');
        });

        // Fechar o menu ao clicar no overlay
        menuOverlay.addEventListener('click', () => {
            menuLateral.classList.add('-translate-x-full');
            menuOverlay.classList.add('hidden');
        });
    </script>