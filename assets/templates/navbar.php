<?php $url_base = 'http://' . $_SERVER['HTTP_HOST'] . '/financas';

// Captura a URL da página anterior
$url_anterior = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $url_base;
$pagina_atual = basename($_SERVER['PHP_SELF']);

$esconder_botao_voltar = ($pagina_atual == 'hp_login.php');
?>

<header class="bg-tollens p-4 flex justify-between items-center">
    <?php if (!$esconder_botao_voltar): ?>
        <a href="<?php echo $url_anterior; ?>">
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Voltar</button>
        </a>
    <?php endif; ?>

    <div class="text-center justify-center items-center">
        <div class="text-white text-2xl text-center font-bold">
            <img src="<?php echo $url_base ?>/assets/logo/preview.svg" alt="Logo" width="60" height="70"> 
        </div>
    </div>
    <div class="space-x-2">
        <a href="<?php $url_base ?>../usuario/perfil.php">
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Meu Perfil</button>
        </a>
        <button id="abrirModal" class="bg-gray-400 text-white py-2 px-4 rounded">Sair</button>
    </div>

    <div id="modalConfirmarLogout" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded-md text-center">
            <p class="mb-4">Tem certeza de que deseja sair?</p>
            <div class="flex justify-center space-x-4">
                <a href="<?php $url_base ?>../login/logout.php">
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