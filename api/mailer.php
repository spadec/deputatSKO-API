<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
// Load Composer's autoloader
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
class mailer
{
    public function sendMessage($adress, $messsage, $subject){
        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;// Enable verbose debug output
            $mail->isSMTP();// Send using SMTP
            $mail->Host       = 'smtp.gmail.com';// Set the SMTP server to send through
            $mail->SMTPAuth   = true;// Enable SMTP authentication
            $mail->Username   = 'paul.starkov@gmail.com';// SMTP username
            $mail->Password   = 'spadecrte';// SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;// Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port       = 587;// TCP port to connect to
            //Recipients
            $mail->setFrom('paul.starkov@gmail.com', 'test');
            $mail->addAddress($adress);// Add a recipient
            // Content
            $mail->isHTML(true);// Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $messsage;
           return $mail->send();
           // echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
