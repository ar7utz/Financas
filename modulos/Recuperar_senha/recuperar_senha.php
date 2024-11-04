<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email_recup_senha'])) {
    // Configurações iniciais
    $apiKey = 'mlsn.3d3a06d9deca8df71c1e50fd43822d5772380668184d85a8d1465c41998a0fc3'; 
    $email = filter_var($_POST['email_recup_senha'], FILTER_SANITIZE_EMAIL);

    // Verifica se o e-mail é válido
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        // Link para recuperação de senha (personalize com seu domínio e lógica de token)
        $token = bin2hex(random_bytes(50));  // Gera um token seguro
        $resetLink = "http://localhost/financas/modulos/Recuperar_senha/redefinir_senha.php?token=$token&email=$email";

        // Conteúdo do e-mail
        $subject = "Recuperação de Senha";
        $message = "
        <h2>Olá,</h2>
        <p>Recebemos uma solicitação para redefinir a sua senha.</p>
        <p>Clique no link abaixo para redefinir a sua senha:</p>
        <a href='$resetLink'>Redefinir minha senha</a>
        <p>Se você não solicitou a recuperação de senha, ignore este e-mail.</p>
        <p>Atenciosamente, <br> Sua Equipe</p>
        ";
        
        // Configurações do cabeçalho
        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];

        // Dados da requisição para o MailerSend
        $data = [
            'from' => [
                'email' => 'no-reply@seudominio.com',
                'name' => 'Suporte'
            ],
            'to' => [
                ['email' => $email]
            ],
            'subject' => $subject,
            'html' => $message
        ];

        // Inicializa o cURL
        $ch = curl_init('https://api.mailersend.com/v1/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Executa e processa a resposta
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Verificação da resposta
        if ($httpCode === 202) {
            echo "<p>Um e-mail de recuperação de senha foi enviado com sucesso para $email.</p>";
        } else {
            echo "<p>Erro ao enviar o e-mail. Por favor, tente novamente.</p>";
        }
    } else {
        echo "<p>Por favor, insira um e-mail válido.</p>";
    }
}
?>
