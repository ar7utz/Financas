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
    <title>Finstash - Meu Perfil</title>
</head>
<body>
    <?php
        include_once ('../../assets/templates/navbar.php')
    ?>
    <div class="">
        <div class=""> <!--lado esquerdo da tela, foto de perfil-->
            <h1>foto.png</h1>
        </div>
        <div class=""> <!--lado direito da tela, informações-->
            <label for="nome">Nome:</label>
            <input type="text" disabled value="<?php echo ['nome'];?>">
        </div>
    </div>
</body>
</html>