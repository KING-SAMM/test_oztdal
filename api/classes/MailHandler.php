<?php

// class MailHandler {
//     public static function sendEmail($email, $letterHeadPath) {
//         $to = $email;
//         $subject = "New User Registration";
//         $message = "A new user has registered.";
//         $headers = "From: no-reply@yourdomain.com";

//         $file = file_get_contents($letterHeadPath);
//         $boundary = md5(rand());

//         $headers .= "\r\nMIME-Version: 1.0\r\n" .
//                     "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"";

//         $body = "--" . $boundary . "\r\n" .
//                 "Content-Type: text/plain; charset=ISO-8859-1\r\n" .
//                 "Content-Transfer-Encoding: 7bit\r\n" .
//                 "\r\n" .
//                 $message . "\r\n" .
//                 "--" . $boundary . "\r\n" .
//                 "Content-Type: application/pdf; name=\"" . basename($letterHeadPath) . "\"\r\n" .
//                 "Content-Transfer-Encoding: base64\r\n" .
//                 "Content-Disposition: attachment; filename=\"" . basename($letterHeadPath) . "\"\r\n" .
//                 "\r\n" .
//                 chunk_split(base64_encode($file)) . "\r\n" .
//                 "--" . $boundary . "--";

//         mail($to, $subject, $body, $headers);
//     }
// }


require __DIR__ . DIRECTORY_SEPARATOR . '..'  . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..'  . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..'  . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHandler {
    // private $email = "kcsamm11@gmail.com";
    
    public static function sendEmail($email, $communityName, $pdfPath) {
        $mail = new PHPMailer(true);
        
        try {
            // SMTP Configuration (example using Gmail’s SMTP)
            $mail->isSMTP();
            $mail->Host       = 'mail.oztdal.com.ng'; // Set your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'no-reply@oztdal.com.ng'; // SMTP username
            $mail->Password   = '09[J8U4xzKyt'; // SMTP password
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            // $mail->Port       = 587;
            $mail->Port       = 465;

            // Email Settings
            $mail->setFrom('no-reply@oztdal.com.ng', 'OZTDAL');
            $mail->addAddress('info@oztdal.com.ng'); // Add a recipient
            $mail->addAddress($email); // Add a recipient

            // Attach the PDF
            if (file_exists($pdfPath)) {
                $mail->addAttachment($pdfPath, basename($pdfPath)); // Attach file and name it
            } else {
                throw new Exception("Attachment file not found: " . $pdfPath);
            }

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = "New Registration: " . $communityName . " Community";
            $mail->Body = "A new community has registered. Please see the attached document.";
            // $mail->AltBody = strip_tags($body); // Fallback for non-HTML email clients

            // Send email
            if ($mail->send()) {
                return ['success' => 'Form details has been forwarded'];
            }
            
        } catch (Exception $e) {
            return ['error' => 'Form details could not be forwarded: {$mail->ErrorInfo}'];
        }
    }
}


