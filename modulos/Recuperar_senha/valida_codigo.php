<?php

require_once '../../assets/bd/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = $_POST['email'];
    $verification_code = $_POST['verification_code'];

    // Verifique se o código está correto
    $sql = "SELECT user_id FROM reset_senha_codigo 
            WHERE code = '$verification_code' 
            AND user_id = (SELECT id FROM user WHERE email = '$user_email')";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Código correto, redirecionar para a página de redefinição de senha
        header("Location: ./ ?email=$user_email");
        exit;
    } else {
        echo "Código inválido ou expirado.";
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Digite seu e-mail" required>
    <input type="text" name="verification_code" placeholder="Digite o código enviado" required>
    <button type="submit">Validar Código</button>
</form>