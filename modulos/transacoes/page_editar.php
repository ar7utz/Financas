<?php
session_start();

include_once '../../assets/bd/conexao.php';

if (!isset($_GET['id'])) {
    echo "ID da transação não foi fornecido.";
    exit;
}

$id_transacao = $_GET['id'];

$sql = "SELECT * FROM transacoes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_transacao);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $transacao = $result->fetch_assoc();
} else {
    echo "Transação não encontrada.";
    exit;
}

$sql_categorias = "SELECT id, nome_categoria FROM categoria";
$result_categorias = $conn->query($sql_categorias);
$categorias = [];
while ($categoria = $result_categorias->fetch_assoc()) {
    $categorias[] = $categoria;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Editar Transação</title>
</head>
<body>
    
    <?php include_once('../../assets/templates/navbar.php') ?>

    <!-- Formulário de Edição -->
    <form id="editarTransacaoForm" method="POST" action="editar_transacao.php">
        <div id="modalEditarTransacao" class="flex justify-center items-center">
            <div class="rounded-md p-8 text-center relative">
                <h2 class="text-2xl mb-4">Editar Transação</h2>

                <input type="hidden" id="idEditar" name="id" value="<?php echo $transacao['id']; ?>">
                <input type="text" id="descricaoEditar" name="descricao" placeholder="Descrição" required class="w-full p-2 mb-4 border border-gray-300 rounded" value="<?php echo htmlspecialchars($transacao['descricao']); ?>">
                <input type="text" id="valorEditar" name="valor" placeholder="Valor" required class="w-full p-2 mb-4 border border-gray-300 rounded" value="<?php echo number_format($transacao['valor'], 2, ',', '.'); ?>">
                <input type="date" id="dataEditar" name="data" required class="w-full p-2 mb-4 border border-gray-300 rounded" value="<?php echo $transacao['data']; ?>">

                <select id="categoriaEditar" name="categoria_id" required class="w-full p-2 mb-4 border border-gray-300 rounded">
                    <option value="" disabled>Selecionar Categoria</option>
                    <?php
                    foreach ($categorias as $categoria) {
                        $selected = ($categoria['id'] == $transacao['categoria_id']) ? "selected" : "";
                        echo "<option value='{$categoria['id']}' $selected>{$categoria['nome_categoria']}</option>";
                    }
                    ?>
                </select>

                <!--Adicionar carrosel para fotos/prints, etc...-->

                <div class="fixed bottom-0 left-0 w-full bg-white p-4 shadow-md flex justify-center items-center space-x-4">
                    <a href="../dashboard/dashboard.php">
                        <button type="button" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500">Cancelar</button>
                    </a>
                    <a href="../transacoes/editar_transacao.php">
                        <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Salvar</button>
                    </a>
                </div>
            </div>
        </div>
    </form>

</body>
</html>