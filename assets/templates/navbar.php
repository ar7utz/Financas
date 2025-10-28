<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../../assets/bd/conexao.php');

$url_base = 'http://' . $_SERVER['HTTP_HOST'] . '/financas';

// Captura a URL da página anterior
$url_anterior = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $url_base;
$pagina_atual = basename($_SERVER['PHP_SELF']);

$esconder_botao_menu = ($pagina_atual == 'hp_login.php');

$nome = 'Usuário';
$foto = 'foto_default.png';

// Verifica se o usuário está logado
if (isset($_SESSION['user_id'])) {
    $usuario_id = $_SESSION['user_id'];

    // Buscar nome e foto do usuário no banco
    $sql_usuario = "SELECT nome, foto FROM user WHERE id = ?";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param('i', $usuario_id);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();

    if ($result_usuario->num_rows > 0) {
        $usuario = $result_usuario->fetch_assoc();
        $nome = htmlspecialchars($usuario['nome']);
        $foto = !empty($usuario['foto']) ? htmlspecialchars($usuario['foto']) : 'foto_default.png';

        // Armazena os dados na sessão
        $_SESSION['nome'] = $nome;
        $_SESSION['foto'] = $foto;
    }
    $stmt_usuario->close();
}

function formatarNome($nomeCompleto) {
    $partes = explode(' ', trim($nomeCompleto)); // Divide o nome em partes
    if (count($partes) > 1) {
        return $partes[0] . ' ' . $partes[1]; // Retorna os dois primeiros nomes
    }
    return $partes[0]; // Retorna apenas o primeiro nome se houver apenas um
}

$nomeExibido = formatarNome($nome);

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<header class="bg-white p-2 ml-4 mr-4 mt-4 justify-evenly flex items-center relative shadow-lg rounded-full">

    <?php if (!$esconder_botao_menu): ?>
        <button id="btnMenu" class="bg-white text-white py-2 px-4 ml-2 rounded hover:shadow-lg focus:outline-none transition duration-300">
            <img src="../../assets/img/icone_menu.png" alt="Menu" width="20px" height="20px">
        </button>
    <?php endif; ?>

    <div id="menuLateral" class="fixed top-0 left-0 h-screen w-64 bg-ghostwhite text-black transform -translate-x-full transition-transform duration-300 z-50" >
        <div class="p-4">
            <div class="flex flex-row items-center justify-between mb-6">
                <img src="../logo/cube_logo_no_background.png" alt="">
                <h2 class="text-xl font-bold">Finstash</h2>

                <button id="btnFechar" class="text-black rounded justify-end-safe">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>

            <ul class="space-y-3">
                <li><a href="../../modulos/dashboard/hp_login.php" class="hover:underline">Home page</a></li>
                <li><a href="../../modulos/dashboard/dashboard.php" class="hover:underline">Dashboard</a></li>
                <li><a href="../../modulos/planejador/page.php" class="hover:underline">Planejador</a></li>
                <li><a href="../../modulos/planilha/pagePS.php" class="hover:underline">Planilha</a></li>
            </ul>
        </div>
    </div>

    <div id="menuOverlay" class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 hidden z-40"></div>
    
    <!--logo-->
    <div class="absolute inset-0 flex justify-center items-center pointer-events-none">
        <img src="../../assets/logo/cube-logo.svg" alt="Logo" class="w-14 h-14">
    </div>

    <div class="ml-auto relative">
        <button id="dropdownToggle" class="flex items-center space-x-2 bg-white text-kansai py-2 px-3 lg:px-4 rounded focus:outline-none relative">
            <img src="<?php echo '../../assets/uploads/' . $foto; ?>" alt="Foto do Usuário" class="w-8 h-8 rounded-full object-cover" />
            <span class="hidden lg:inline-block font-medium"><?php echo htmlspecialchars($nomeExibido); ?></span>
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown -->
        <div id="dropdownMenu" class="hidden absolute right-0 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10">
            <a href="<?php echo $url_base; ?>./modulos/usuario/perfil.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Meu Perfil</a>
            <button id="abrirModalLogout" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Sair</button>
        </div>
    </div>


    <div id="modalConfirmarLogout" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded-md text-center">
            <p class="mb-4">Tem certeza de que deseja sair?</p>
            <div class="flex justify-center space-x-4">
                <a href="<?php echo $url_base; ?>../modulos/login/logout.php" rel="noopener noreferrer">
                    <button id="confirmarLogout" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Confirmar</button>
                </a>
                <button id="cancelarLogout" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
            </div>
        </div>
    </div>
</header>

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
    if (btnMenu) {
        btnMenu.addEventListener('click', () => {
            menuLateral.classList.remove('-translate-x-full');
            menuOverlay.classList.remove('hidden');
        });
    }
    // Função para fechar o menu
    if (btnFechar) {
        btnFechar.addEventListener('click', () => {
            menuLateral.classList.add('-translate-x-full');
            menuOverlay.classList.add('hidden');
        });
    }
    if (menuOverlay) {
        menuOverlay.addEventListener('click', () => {
            menuLateral.classList.add('-translate-x-full');
            menuOverlay.classList.add('hidden');
        });
    }
</script>

<script>
    const dropdownToggle = document.getElementById('dropdownToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const abrirModalLogout = document.getElementById('abrirModalLogout');
    const modalConfirmarLogout = document.getElementById('modalConfirmarLogout');
    const cancelarLogout = document.getElementById('cancelarLogout');
    // Alternar visibilidade do dropdown
    dropdownToggle.addEventListener('click', () => {
        dropdownMenu.classList.toggle('hidden');
    });
    // Abrir o modal de logout
    abrirModalLogout.addEventListener('click', () => {
        modalConfirmarLogout.classList.remove('hidden');
        dropdownMenu.classList.add('hidden'); // Fecha o dropdown ao abrir o modal
    });
    // Fechar o modal de logout
    cancelarLogout.addEventListener('click', () => {
        modalConfirmarLogout.classList.add('hidden');
    });
    // Fechar o dropdown ao clicar fora dele
    window.addEventListener('click', (e) => {
        if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
    // Fechar o modal ao clicar fora dele
    window.addEventListener('click', (e) => {
        if (e.target === modalConfirmarLogout) {
            modalConfirmarLogout.classList.add('hidden');
        }
    });
</script>