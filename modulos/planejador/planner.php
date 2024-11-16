<?php
session_start();
include_once '../../assets/bd/conexao.php'
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <title>Planejador</title>
</head>
<body>
    <?php include_once('../../assets/templates/navbar.php') ?>

    <button id="notifyBtn" class="bg-tollens text-white p-4 rounded">Mostrar Notificação</button>

    <script> //toastify 
        document.getElementById('notifyBtn').addEventListener('click', function() {
            Toastify({
                text: "Esta é uma notificação com Toastify!",
                duration: 3000, //duração em milissegundos
                close: true, //botão de fechar
                gravity: "bottom", //"top" ou "bottom"
                position: "right", 
                backgroundColor: "#1133A6",
            }).showToast();
        });
    </script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</body>
</html>