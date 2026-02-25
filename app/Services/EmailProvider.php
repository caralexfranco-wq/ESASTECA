<?php

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class EmailProvider
{
    public function send(string $to, string $subject, string $html): array
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'response' => 'email inválido'];
        }

        if (!(bool) filter_var(env('MAIL_ENABLED', 'true'), FILTER_VALIDATE_BOOL)) {
            return ['ok' => true, 'response' => 'mail deshabilitado'];
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->Port = (int) env('MAIL_PORT', 587);
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USER');
            $mail->Password = env('MAIL_PASS');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
            $mail->setFrom(env('MAIL_FROM'), env('MAIL_FROM_NAME', 'Expedientes'));
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->send();
            return ['ok' => true, 'response' => 'enviado'];
        } catch (Exception $e) {
            return ['ok' => false, 'response' => $e->getMessage()];
        }
    }
}
