<?php
session_start();
include_once '../../assets/bd/conexao.php'

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Bem Vindo</title>
</head>
<body>
    <?php include_once('../../assets/templates/navbar.php') ?>
    <h1>clima e horario</h1>
    <?php include_once('../../assets/templates/navbar_lateral.php') ?>

    <div id="cards" class="">

    </div>
</body>
</html>