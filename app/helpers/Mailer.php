<?php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Helpers\Env;

class Mailer {
    public static function send($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = Env::get("MAIL_HOST");
            $mail->SMTPAuth   = true;
            $mail->Username   = Env::get("MAIL_USERNAME");
            $mail->Password   = Env::get("MAIL_PASSWORD");
            $port             = Env::get("MAIL_PORT");
            $mail->Port       = $port;
            
            if ($port == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(Env::get("MAIL_FROM"), Env::get("MAIL_FROM_NAME"));
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;

        } catch (Exception $e) {
            $error = "Mailer Error: " . $mail->ErrorInfo;
            error_log($error);
            return $error;
        }
    }
}
