<?php
session_start();
include '../../assets/bd/conexao.php';

if (isset($_SESSION['user_id'])) {
    $usuario_id = intval($_SESSION['user_id']);
} else {
    header('Location: ../../index.php');
    exit;
}

// Buscar categorias do usuário
$sql_categoria = "SELECT id, nome_categoria FROM categoria WHERE fk_user_id = ? ORDER BY nome_categoria ASC";
$stmt_categoria = $conn->prepare($sql_categoria);
$stmt_categoria->bind_param('i', $usuario_id);
$stmt_categoria->execute();
$resultado_categoria = $stmt_categoria->get_result();
$categorias = $resultado_categoria->fetch_all(MYSQLI_ASSOC);
$stmt_categoria->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">
    <title>Finstash - Gerenciar Categorias</title>
</head>
<body>
    <?php include_once('../../assets/templates/navbar.php');?>

    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-semibold mt-8 mb-4 text-center">Minhas categorias</h1>

        <ul id="minhasCategoriasList" class="space-y-2">
            <?php if (count($categorias) === 0): ?>
                <li class="text-gray-500">Nenhuma categoria encontrada.</li>
            <?php else: ?>
                <?php foreach ($categorias as $cat): ?>
                    <li class="category-item flex items-center justify-between bg-white p-3 rounded shadow-sm"
                        data-id="<?php echo $cat['id']; ?>">
                        <div class="flex items-center gap-3">
                            <span class="cat-name text-gray-800"><?php echo htmlspecialchars($cat['nome_categoria']); ?></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                    class="btn-edit text-blue-600 hover:text-blue-800 p-2 rounded"
                                    title="Editar"
                                    onclick="event.stopPropagation();">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                            </button>

                            <button type="button"
                                    class="btn-delete text-red-600 hover:text-red-800 p-2 rounded"
                                    title="Excluir"
                                    onclick="event.stopPropagation(); confirmDeleteCategoria(<?php echo $cat['id']; ?>);">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button>

                            <button type="button"
                                    class="btn-confirm hidden text-green-600 hover:text-green-800 p-2 rounded"
                                    title="Confirmar"
                                    onclick="event.stopPropagation();">
                                <i class="fa fa-check" aria-hidden="true"></i>
                            </button>

                            <button type="button"
                                    class="btn-cancel hidden text-gray-600 hover:text-gray-800 p-2 rounded"
                                    title="Cancelar"
                                    onclick="event.stopPropagation();">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Modal de Exclusão -->
    <div id="modalConfirmarExclusaoCategoria" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded-md text-center">
            <p class="mb-4">Tem certeza de que deseja excluir esta categoria? Essa ação pode afetar transações vinculadas.</p>
            <div class="flex justify-center space-x-4">
                <button id="confirmarExcluirCategoria" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Confirmar</button>
                <button id="cancelarExcluirCategoria" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('minhasCategoriasList');
    let currentEditing = null;

    list.querySelectorAll('.category-item').forEach(li => {
        const btnEdit = li.querySelector('.btn-edit');
        const btnDelete = li.querySelector('.btn-delete');
        const btnConfirm = li.querySelector('.btn-confirm');
        const btnCancel = li.querySelector('.btn-cancel');
        const spanName = li.querySelector('.cat-name');

        btnEdit.addEventListener('click', function (e) {
            e.stopPropagation();
            if (currentEditing && currentEditing !== li) {
                cancelEdit(currentEditing);
            }
            enterEdit(li);
        });

        btnCancel.addEventListener('click', function (e) {
            e.stopPropagation();
            cancelEdit(li);
        });

        btnConfirm.addEventListener('click', function (e) {
            e.stopPropagation();
            const input = li.querySelector('input.cat-input');
            if (!input) return;
            const novoNome = input.value.trim();
            if (novoNome === '') {
                alert('Nome da categoria não pode ficar vazio.');
                input.focus();
                return;
            }
            const id = li.getAttribute('data-id');

            btnConfirm.disabled = true;
            fetch('updateCategoria.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, nome_categoria: novoNome })
            })
            .then(r => r.json())
            .then(res => {
                btnConfirm.disabled = false;
                if (res.success) {
                    spanName.textContent = res.nome;
                    exitEdit(li);
                } else {
                    alert(res.message || 'Erro ao atualizar categoria.');
                }
            })
            .catch(err => {
                btnConfirm.disabled = false;
                console.error(err);
                alert('Erro de rede ao atualizar categoria.');
            });
        });

        li.addEventListener('click', function () {
        });
    });

    function enterEdit(li) {
        const spanName = li.querySelector('.cat-name');
        const currentText = spanName.textContent;
        spanName.classList.add('hidden');
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'cat-input border rounded px-2 py-1 text-sm';
        input.value = currentText;
        input.style.minWidth = '200px';
        spanName.parentNode.insertBefore(input, spanName);
        input.focus();
        input.select();

        li.querySelector('.btn-edit').classList.add('hidden');
        li.querySelector('.btn-delete').classList.add('hidden');
        li.querySelector('.btn-confirm').classList.remove('hidden');
        li.querySelector('.btn-cancel').classList.remove('hidden');

        currentEditing = li;
    }

    function cancelEdit(li) {
        const input = li.querySelector('input.cat-input');
        const spanName = li.querySelector('.cat-name');
        if (input) input.remove();
        spanName.classList.remove('hidden');

        li.querySelector('.btn-edit').classList.remove('hidden');
        li.querySelector('.btn-delete').classList.remove('hidden');
        li.querySelector('.btn-confirm').classList.add('hidden');
        li.querySelector('.btn-cancel').classList.add('hidden');

        if (currentEditing === li) currentEditing = null;
    }

    function exitEdit(li) {
        const input = li.querySelector('input.cat-input');
        const spanName = li.querySelector('.cat-name');
        if (input) input.remove();
        spanName.classList.remove('hidden');

        li.querySelector('.btn-edit').classList.remove('hidden');
        li.querySelector('.btn-delete').classList.remove('hidden');
        li.querySelector('.btn-confirm').classList.add('hidden');
        li.querySelector('.btn-cancel').classList.add('hidden');

        if (currentEditing === li) currentEditing = null;
    }

    const modal = document.getElementById('modalConfirmarExclusaoCategoria');
    const confirmarBtn = document.getElementById('confirmarExcluirCategoria');
    const cancelarBtn = document.getElementById('cancelarExcluirCategoria');
    let idToDelete = null;

    window.confirmDeleteCategoria = function (id) {
        idToDelete = id;
        modal.classList.remove('hidden');
    };

    confirmarBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (!idToDelete) return;
        window.location.href = 'deleteCategoria.php?id=' + encodeURIComponent(idToDelete);
    });

    cancelarBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        modal.classList.add('hidden');
        idToDelete = null;
    });

    modal.addEventListener('click', function outsideClick(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
            idToDelete = null;
        }
    });

});
</script>
</body>
</html>