<?php
require_once '../../vendor/autoload.php';
require_once '../../assets/bd/conexao.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../../vendor/phpmailer/phpmailer/src/Exception.php';

$user_email = $_POST['email_recup_senha'];
$verification_code = rand(100000, 999999);

$sql = "SELECT id FROM user WHERE email = '$user_email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_id = $row['id'];
    $stmt = $conn->prepare("INSERT INTO reset_senha_codigo (user_id, code) VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE code = ?");
    $stmt->bind_param("iss", $user_id, $verification_code, $verification_code);
    $stmt->execute();
} else {
    die('E-mail não encontrado!');
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'finstashgroup@gmail.com';
    $mail->Password   = 'ymms unvw wcug cpio';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet = 'UTF-8';

    // Configuração do e-mail
    $mail->setFrom('finstashgroup@gmail.com', 'noreply@finstash');
    $mail->addAddress($user_email, 'nome do abençoado'); // E-mail do destinatário

    $mail->isHTML(true);
    $mail->Subject = 'Recuperação de senha';
    $mail->Body    = "Olá, <br> Utilize o código de verificação: <b>$verification_code</b>.<br>
                      Ou clique <a href='./valida_codigo.php?email=$user_email'>aqui</a> para redefinir sua senha.";
    $mail->AltBody = "Seu código de verificação é: $verification_code. Acesse: <a href='./valida_codigo.php?email=$user_email' a<a/>"; //usar quando o site estiver hospedado

    $mail->send();
    header("Location: ../../index.php?mensagem=EmailRecSenhaSucesso");
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
}
