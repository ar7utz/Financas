<?php
require_once '../../assets/bd/conexao.php';
session_start();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Planilha Financeira</title>
</head>
<body>
    <?php require_once '../../assets/templates/navbar.php' ?>

    <h1>Suas planilhas</h1>

    <a href="./planilha.php"><button class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">Criar uma planilha</button></a>
</body>
</html>