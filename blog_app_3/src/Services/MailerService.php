<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$bodyText = file_get_contents("D:/Projects/data/sample.txt");
$appPasssword = file_get_contents("D:/Projects/data/gmail_app_password.txt");
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['GMAIL_USER'];
    $mail->Password = $appPasssword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($_ENV['GMAIL_USER']);
    $mail->addAddress($_ENV['RECEIVER_EMAIL']);

    $mail->Subject = 'Sample Email from PHP';
    $mail->Body = "Sample email body from PHP";

    $mail->send();
    echo "Email has been sent successfully!";
} catch (Exception $e) {
    echo "Email could not be sent. Error: {$mail->ErrorInfo}";
}
?>