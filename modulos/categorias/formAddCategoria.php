<?php
session_start();
include '../../assets/bd/conexao.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Adicionar categoria</title>
</head>
<body>
    <?php include_once('../../assets/templates/navbar.php'); ?>

    <div class="flex items-center justify-center align-middle h-screen">
        <form action="./addCategoria.php" method="POST">
            <div class="flex flex-row mb-8">
                <label for="nome">Nome da Categoria:</label>
                <input class="border p-2 w-full rounded-md" type="text" id="nome_categoria" name="nome_categoria" required>
            </div>

            <div class="flex items-center justify-center">
                <button type="submit">Adicionar Categoria</button>
            </div>
        </form>
    </div>

</body>
</html>