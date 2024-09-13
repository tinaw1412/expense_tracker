<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

class Mail
{
    public static function send($to, $subject, $body)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;//Enable verbose debug output
            $mail->isSMTP();//Send using SMTP
            $mail->SMTPAuth = true;//Enable SMTP authentication

            $mail->Host = $_ENV['mail_host'];//Set the SMTP server to send through
            $mail->Username = $_ENV['mail_user'];//SMTP username
            $mail->Password = $_ENV['mail_password'];//SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; 
            
            $mail->addAddress($to);

            $mail->isHTML(true);//Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;

            return $mail->send();
        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }
    }
}
