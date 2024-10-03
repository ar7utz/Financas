<header class="bg-tollens p-4 flex justify-between items-center">
    <a href="javascript:window.history.back();">
        <button class="bg-gray-400 text-white py-2 px-4 rounded">Voltar</button>
    </a>
    <div class="text-center justify-center items-center">
        <h1 class="text-white text-2xl text-center font-bold">Finstash</h1>
    </div>
    <div class="space-x-2">
        <a href="../usuario/perfil.php">
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Meu Perfil</button>
        </a>
        <button id="abrirModal" class="bg-gray-400 text-white py-2 px-4 rounded">Sair</button>
    </div>

    <div id="modalConfirmarLogout" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded-md text-center">
            <p class="mb-4">Tem certeza de que deseja sair?</p>
            <div class="flex justify-center space-x-4">
                <a href="../login/logout.php">
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