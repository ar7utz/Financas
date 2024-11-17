<?php
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../../vendor/phpmailer/phpmailer/src/Exception.php';

$mail = new PHPMailer(true);

$sender_email = $_POST['email_recup_senha'];

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'finstashgroup@gmail.com';
    $mail->Password   = 'kbvq zwhu zqfa wzuz';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Configuração do e-mail
    $mail->setFrom('finstashgroup@gmail.com', 'finstash');
    $mail->addAddress($sender_email, 'nome do abençoado'); // E-mail do destinatário

    $mail->isHTML(true);
    $mail->Subject = 'Recuperação de senha';
    $mail->Body    = 'Este é um teste do <b>PHPMailer</b>.';
    $mail->AltBody = 'Este é um teste do PHPMailer.';

    $mail->send();
    echo 'E-mail enviado com sucesso!';
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
}
