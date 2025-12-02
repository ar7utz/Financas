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
    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">
    <title>Adicionar categoria</title>
</head>
<body>
    <?php include_once('../../assets/templates/navbar.php'); ?>

    <div class="w-4/5 mx-auto flex items-center justify-center align-middle">
        <form action="./addCategoria.php" method="POST">
            <div class="mt-14 bg-white p-6 rounded-lg shadow-md ">
                <div class="flex flex-row mt-12 mb-8">
                    <label for="nome">Nome da Categoria:</label>
                    <input class="border p-2 w-full rounded-md" type="text" id="nome_categoria" name="nome_categoria" required>
                </div>

                <div class="flex items-center justify-center">
                    <button type="submit" class="bg-tollens text-white mt-4 px-4 py-2 rounded-md">Adicionar Categoria</button>
                </div>
            </div>
        </form>
    </div>

</body>
</html>